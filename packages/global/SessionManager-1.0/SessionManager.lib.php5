<?php
// Package UserAgentManager is required for SessionManager
if (!class_exists('UserAgentManager'))
{
	die ('Error: Package "UserAgentManager" is required for SessionManager');
}

/**
 * SessionManager
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class SessionManager 
{
	
	const NONE = 0;
	const ACCEPT_DIFFERENT_IP = 1;
	
	/**
	 * Singelton instance of the SessionManager
	 * @var SessionManager 
	 */
	static protected $instance = null;
	
	/**
	 * Flag that indicates if this is a new session
	 * @var bool 
	 */
	protected $newSession = false;
	
	/**
	 * The name of the session
	 * @var string 
	 */
	protected $sessionName = "UID";
	
	/**
	 * The ID of the current session
	 * @var string 
	 */
	protected $sessionID = "";
	
	/**
	 * The (temporary) directory for the current session
	 * @var string 
	 */
	protected $sessionDirectoriesRoot = "";

	/**
	 * From all domains listed in this array, links containing session ids will be accepted without creating a new session
	 * @var string[] 
	 */
	protected $allowedRefererDomains = Array();
	
	/**
	 * The users agent
	 * @var UserAgent 
	 */
	protected $currentUserAgent = null;
	
	/**
	 * Options 
	 * @var int 
	 */
	protected $options = self::NONE;
	
	/**
	 * Constructor
	 * @param string $sessionDirectoriesRoot The folder where the session directories will be created in 
	 * @param string $sessionName Name of the session
	 */
	private function SessionManager($sessionDirectoriesRoot, $allowedRefererDomains=Array(), $sessionName="UID", $options = self::NONE)
	{
		$this->sessionDirectoriesRoot = $sessionDirectoriesRoot;
		$this->allowedRefererDomains = $allowedRefererDomains;
		$this->sessionName = $sessionName;
		$this->options = $options;
		$this->currentUserAgent = UserAgentManager::GetCurrentUserAgent();
	}
	
	/**
	 * Constructor
	 * @param string $sessionDirectoriesRoot The folder where the session directories will be created in 
	 * @param string $sessionName Name of the session
	 */
	static public function InitSession($sessionDirectoriesRoot, $allowedRefererDomains=Array(), $sessionName="UID", $options = self::NONE)
	{
		if (self::$instance==null)
		{
			self::$instance = new SessionManager($sessionDirectoriesRoot, $allowedRefererDomains, $sessionName, $options);
			self::$instance->StartSession();
		}
	}
	
	/**
	 * Return the singelton instance of the SessionManager
	 * @return SessionManager 
	 */
	static public function GetInstance()
	{
		return self::$instance;
	}

	/**
	 * Start the session
	 * @param string $sessionDirectoriesRoot The folder where the session directories will be created in 
	 * @param string $sessionName Name of the session
	 */
	protected function StartSession()
	{
		
		// start the session
		session_name($this->sessionName);
		session_start();
		$this->sessionID = session_id();
		// do some security checks...
		if (!self::CheckReferer() || !self::CheckUserAgent())
		{
			// security checks not passed -> create new session
			session_regenerate_id(false);
			$this->sessionID = session_id();
			// overwrite GET and POST uid to be sure the new SID is used
			$_GET[$this->sessionName] = $this->sessionID;
			$_POST[$this->sessionName] = $this->sessionID;
			$_REQUEST[$this->sessionName] = $this->sessionID;
			session_start();
			session_destroy();
			unset($_SESSION);
		}
		// check if session is allready initialized
		if (!isset($_SESSION["userAgent"]))
		{
			// new session -> initialize
			$_SESSION["userAgent"] = $this->currentUserAgent;
			$this->newSession = true;
		}
	}
	
	/**
	 * Destroy the current session 
	 */
	public function DestroySession()
	{
		session_destroy();
		self::$instance = null;
		unset($_SESSION);
	}
	
	/**
	 * Return if this is a new session
	 * @return bool 
	 */
	public function IsNewSession()
	{
		return $this->newSession;
	}
	
	/**
	 * Check if the referer is valid or if we have to start a new session (i.e. link from external website including a session id)
	 */
	protected function CheckReferer()
	{
		if ($_SERVER["HTTP_REFERER"]!="" && count($this->allowedRefererDomains)>0)
		{
			foreach ($this->allowedRefererDomains as $allowedRefererDomain)
			{
				if (!(stristr($_SERVER["HTTP_REFERER"], $allowedRefererDomain)===false))
				{
					// referer in list --> referer ok
					return true;
				}
			}
			// domain not in list --> referer not ok
			return false;
		}
		// no referer or empty allowed referer domain list --> ok
		return true;
	}
	
	/**
	 * Check if the current user agent is the same who created the current session 
	 */
	public function CheckUserAgent()
	{
		// no user agent in session --> ok
		if (!isset($_SESSION["userAgent"])) return true;
		// different ip --> not ok
		if (!($this->options & self::ACCEPT_DIFFERENT_IP) && $_SESSION["userAgent"]->GetClientIp()!=$this->currentUserAgent->GetClientIp()) return false;
		// different user agent string --> not ok
		if ($_SESSION["userAgent"]->GetHttpUserAgentString()!=$this->currentUserAgent->GetHttpUserAgentString()) return false;
		// all checks are fine --> ok
		return true;
	}
	
	/**
	 * Return the current session id
	 * @return string
	 */
	public function GetSessionID()
	{
		return $this->sessionID;
	}
	
	/**
	 * Return the current session directory
	 * @return string 
	 */
	public function GetSessionDirectory()
	{
		return $this->sessionDirectoriesRoot.$this->sessionID."/";
	}
	
	/**
	 * Creates the direcory for the current session
	 * @return boolean 
	 */
	public function CreateSessionDirectory()
	{
		// Sessionverzeichnis erzeugen falls nicht vorhanden
		if (!@file_exists(self::GetSessionDirectory()))
		{
			if (!@mkdir(self::GetSessionDirectory(), 0777))
			{
				// SessionVerzeichniss kontne nicht erzeugt werden
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Delete the directory for the current session
	 * @return boolean 
	 */
	public function DeleteSessionDirectory()
	{
		if (is_dir(self::GetSessionDirectory()))
		{
			// Inhalt des Verzeichnisses holen
			$dirContent = scandir(self::GetSessionDirectory());
			// Prüfen ob ein Unterverzeichnis existiert
			foreach ($dirContent as $entry)
			{
				if ($entry=="." || $entry==".." ) continue;
				if (is_dir(self::GetSessionDirectory().$entry) )
				{
					// Fehler: Es befindet sich ein Unterverzeichniss im SessionVerzeichniss
					echo "Nö!!!<br>";
					return false;
				}
			}
			// Dateien löschen 
			foreach ($dirContent as $entry)
			{
				if ($entry=="." || $entry=="..") continue;
				unlink(self::GetSessionDirectory().$entry);
			}
			// Verzeichniss löschen
			rmdir(self::GetSessionDirectory());
		}
		return true;
	}
	
	/**
	 * Copy a file into the session directory and rename the file to target file name
	 * @param string $sourceFile
	 * @param string $targetFileName
	 * @param int $errorCode (1=can't create session directory 2=source file not found, 3=can't copy file)
	 * @return boolean 
	 */
	public function CopyFileToSessionDirectory($sourceFile, $targetFileName, &$errorCode)
	{
		// Create session directory if not allready exists
		if (!self::CreateSessionDirectory())
		{
			// can't create session directory 
			$errorCode = 1;
			return false;
		}
		// check if source file exists
		if (!@file_exists($sourceFile))
		{
			// source file dosen't exists
			$errorCode = 2;
			return false;
		}
		// copy file
		if (!@copy( $sourceFile, self::GetSessionDirectory().$targetFileName))
		{
			// can't copy file
			$errorCode = 3;
			return false;
		}
		return true;
	}
	
	/**
	 * Moves a uploaded file to the session directory  and rename the file to target file name
	 * @param type $sourceFile
	 * @param type $targetFileName
	 * @param type $errorCode (1=can't create session directory 2=source file not found, 3=can't move file)
	 * @return boolean 
	 */
	public function MoveUploadedFileToSessionDirectory($sourceFile, $targetFileName, &$errorCode)
	{
		// Create session directory if not allready exists
		if (!self::CreateSessionDirectory())
		{
			// can't create session directory 
			$errorCode = 1;
			return false;
		}
		// check if source file exists
		if (!@file_exists($sourceFile))
		{
			// source file dosen't exists
			$errorCode = 2;
			return false;
		}
		// Move file 
		if (!@move_uploaded_file($sourceFile, self::GetSessionDirectory().$targetFileName))
		{
			// Datei konnte nicht kopiert werden
			$errorCode = 3;
			return false;
		}
		return true;
	}
	
}
?>