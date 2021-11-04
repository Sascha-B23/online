<?php
/**
 * JenkinsJobMonitor
 *
 * @access public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
class JenkinsJobMonitor
{

	/**
	 * jenkins server URL
	 * @var string
	 */
	protected $jenkinsServer = "";
	
	/**
	 * jenkins job name
	 * @var string
	 */
	protected $jobName = "";
	
	/**
	 * special server port number
	 * @var int
	 */
	protected $serverPort = 0;
	
	/**
	 * jenkins user and password
	 * @var string
	 */
	protected $userAndPwd = "";
	
	/**
	 * duration of the job
	 * @var int
	 */
	protected $startTime = 0;
	
	/**
	 * error text
	 * @var string 
	 */
	protected $errorText = "";

	/**
	 * Constructor
	 * @param string $jenkinsServer The Jenkins server address 
	 * @param string $jobName The name of the Jenkins job
	 * @param string $userAndPwd Username and password of the Jenkins user (user:password)
	 * @param int $serverPort The port of the Jenkins server
	 */
	public function JenkinsJobMonitor($jenkinsServer, $jobName, $userAndPwd="", $serverPort=0)
	{
		$this->jenkinsServer = $jenkinsServer;
		$this->jobName = $jobName;
		$this->userAndPwd = $userAndPwd;
		$this->serverPort = $serverPort;
	}

	/**
	 * Return the last error
	 * @return string
	 */
	public function GetErrorText()
	{
		return $this->errorText;
	}
	
	/**
	 * Start the run
	 */
	public function StartRun()
	{
		$this->startTime = time();
	}
	
	/**
	 * Submit a run to the Jenkins-Server
	 * @param string $log The console output to log
	 * @param int $resultCode The error code. 0 is success and everything else is failure.
	 * @param string $description Description of the build 
	 * @param string $displayName The name to be displayed rather than the build number 
	 * @return boolean Success
	 */
	public function SubmitRun($log, $resultCode, $description="", $displayName="")
	{
		$durationInMilliseconds = 0;
		if ($this->startTime!=0)
		{
			$durationInMilliseconds = (time()-$this->startTime)*1000;
		}
		// build xml-string
		$xmlToSend = "<run>";
		$xmlToSend.= "  <log encoding=\"hexBinary\">".bin2hex(utf8_decode($log."\n\n"))."</log>";
		$xmlToSend.= "  <result>".((int)$resultCode)."</result>";
		$xmlToSend.= "  <duration>".((int)$durationInMilliseconds)."</duration>";
		if (trim($displayName)!="") $xmlToSend.= "  <displayName>".trim($displayName)."</displayName>";
		if (trim($description)!="") $xmlToSend.= "  <description>".trim($description)."</description>";
		$xmlToSend.= "</run>";
		
		$ch = curl_init();
		$url = $this->jenkinsServer."/job/".str_replace(' ', '%20', $this->jobName)."/postBuildResult";
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($this->serverPort!=0) curl_setopt($ch, CURLOPT_PORT, $this->serverPort);
		if (trim($this->userAndPwd)!="") curl_setopt($ch, CURLOPT_USERPWD, $this->userAndPwd); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);	// 10 seconds timeout
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlToSend);
		// send xml-string
		$response = curl_exec($ch);
		if ($response===false)
		{
			$this->errorText ="Server-URL: ".$url.", ";
			$this->errorText.="CURL Error: ".curl_error($ch);
			curl_close($ch);
			return false;
		}
		/*else
		{
			echo "<!--";
			print_r($response);
			echo "-->";
		}*/
		curl_close($ch);
		return true;
	}
	
}
?>