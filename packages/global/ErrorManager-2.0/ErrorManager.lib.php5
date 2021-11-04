<?
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002 Digital Art & Animation GmbH www.daa-direkt.de    |
// +----------------------------------------------------------------------+
// | Author(s): Martin Walleczek <walleb@daa-direkt.de>                                   |
// |                                    |
// +----------------------------------------------------------------------+
//
// $Id: ErrorManager.lib.php5,v 1.0 2002/09/10 Exp $
//
// Error Logging & Monitoring Library
//


/**
 * Der ErrorManager ist eine Fehlerbehandlungs- und Überwachungsklasse. 
 * Auftretende Fehler werden auf Wunsch in ein zentrales Logfile geschrieben 
 * oder Fehler direkt an (eine) EMailadresse(n) gesandt. 
 * Keine Abhänigkeiten von anderen Modulen.
 * 
 * @package  ErrorManager
 * @access   public
 * @author   Martin Walleczek <walle@daa-direkt.de>
 * @since    PHP 4.0
 */

class ErrorManager 
{

	/** 
	*	Fehlertext der ausgegeben wird 
	*   @var string
	*/
	var $errorText;
	
	/** 
	*	Mailadresse an die Fehler gesandt werden soll 
	*   @var string
	*/
	var $mailErrors;
	
	/** 
	*	Absoluter Pfad auf das Logfile incl. Dateinamen 
	*   @var string
	*/
	var $logfile;
	
	
	/**
     * Error Manager initialisieren.
     *
     * @param string $logfile Absoluter Pfad auf das zu verwendene Logfile, z.B /WWW/logs/logfile.txt 
     * Wenn Datei nicht vorhanden wird sie angelegt. (optional)
	*
     * @param string $mailErrors EMailadresse an die Fehlermedlungen gesendet werden sollen (optional)
     * 
     * @param boolean $autoCreateDir Soll Verzeichnis automatisch erzeugt werden?
     *
     * @param boolean $autoProtect Soll Logverzeichnis durch .htaccess geschützt werden?
     *
     * @return void
     * 
     * @access public
     */
	public function ErrorManager($logfile="",$mailErrors="",$autoCreateDir=true,$autoProtect=true)
	{
		$this->errorText="";
		$this->logfile=$logfile;
		$this->mailErrors=$mailErrors;
		// Prüfen ob Verzeichnis existiert
		if ($autoCreateDir && $logfile!=""){
			$dirname=dirname($logfile);	
			if (!chdir($dirname))
				mkdir($dirname);
		}
		// .htacces anlegen
		if ($autoProtect && $logfile!=""){
			$dirname=dirname($logfile);	
			if (chdir($dirname) && !file_exists($dirname."/.htaccess")){
				$fp=fopen($dirname."/.htaccess","w");
				fwrite($fp,"AuthType Basic\nAuthName \"NOACCESS\"\nAuthUserFile /none\nrequire user none");
				fclose($fp);
				chmod($dirname."/.htaccess",0777);
			}
		}
	}
	
	/**
     * Auslösen einer Fehlermeldung
     * Zeigt Fehler an, schreibt Fehlerklasse und Fehlermedlung in Logfile und versendet diese wenn gesetzt per EMail.  
     * @param string $className 		Reporting classname and method (e.g.: 'MySqlManager::Insert(string Query)')
     * @param string $errorText 		Description of error
	 * @param bool $silentError 		If true no error will be visible to the user
     * @return void
     * @access public
     */
	public function ShowError($className, $errorText, $silentError=false)
	{
		
		$msg=date("j.m.Y-H:i",time()).";".$_SERVER["REQUEST_URI"].";".$className.";".$errorText.";".gethostbyaddr($_SERVER["REMOTE_ADDR"]);
		
		if ($this->logfile!="")
		{
			@error_log (str_replace("\n", " ", $msg)."\n", 3,$this->logfile);
		}
			
		if ($this->mailErrors!="")
		{
			@mail($this->mailErrors,"Fehlermeldung auf ".$_SERVER["HTTP_HOST"], str_replace(";","\n\n",$msg));
		}
		
		if( !$silentError )
		{
			$message="<span style=\"font-family:verdana,tahoma,arial;font-size:10px;color:#B11A00\"><strong>Error in ".$className."</strong><br>";
			$message.=$errorText."<br><br></span>";
			echo $message;
		}
	}

/**
     * Auslösen einer Debugmeldung.
     * Zeigt Debuginformation an.  
     *
     * @param string $ClassName Klassenname und Methode die die Info meldet, z.B MySqlManager::Insert(string Query)
     * 
     * @param string $DebugText Meldung
     *
     * @return void
     * 
     * @access public
     */
	function ShowDebugInfo($ClassName, $DebugText){
		$message="<span style=\"font-family:verdana,tahoma,arial;font-size:9px;color:#666699\"><strong>$ClassName</strong><br>";
		$message.=$DebugText."<br><br></span>";
		echo $message;
	}

}//class
?>