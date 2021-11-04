<?
/**
 * UserAgent
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class UserAgent
{

	/**
	 * Data from php browsecap?
	 * @var string
	 */
	protected $useBrowscap = false;
	
	/**
	 * Name of the Browser 
	 * @var string
	 */
	protected $browserName = "";
	
	/**
	 * Version of the browser
	 * @var float
	 */
	protected $browserVersion = "";
	
	/**
	 * Complete user agent string (HTTP_USER_AGENT) 
	 * @var string
	 */
	protected $browserFullname = "";
	
	/**
	 * Client OS
	 * @var string 
	 */
	protected $operatingSystem = "";
	
	/**
	 * The clients ip address
	 * @var string 
	 */
	protected $clientIp = "";
	
	/**
	 * The http user agent string
	 * @var string 
	 */
	protected $httpUserAgentString = "";
	
	/**
	 * Constructor 
	 */
	public function UserAgent()
	{
		
	}
	
	/**
	 * Set if data is from php browsecap
	 * @param bool $useBrowscap
	 */
	public function SetUseBrowscap($useBrowscap)
	{
		$this->useBrowscap = $useBrowscap;
	}
	
	/**
	 * Return if data is from php browsecap
	 * @return bool
	 */
	public function IsBrowscapUsed()
	{
		return $this->useBrowscap;
	}
	
	/**
	 * Set the browsers name
	 * @param string $browserName
	 */
	public function SetBrowserName($browserName)
	{
		$this->browserName = $browserName;
	}
	
	/**
	 * Return the browsers name
	 * @return string 
	 */
	public function GetBrowserName()
	{
		return $this->browserName;
	}
	
	/**
	 * Return the browsers short name
	 * @return string 
	 */
	public function GetBrowserShortName()
	{
		return $this->GetBrowserName().$this->GetBrowserVersion();
	}
	
	/**
	 * Set the browsers version
	 * @param float $browserVersion
	 */
	public function SetBrowserVersion($browserVersion)
	{
		if (!is_numeric($browserVersion)) return false;
		$this->browserVersion = (float)$browserVersion;
		return true;
	}
	
	/**
	 * Return the browsers version
	 * @return float 
	 */
	public function GetBrowserVersion()
	{
		return $this->browserVersion;
	}

	/**
	 * Set the complete user agent string
	 * @param string $browserFullname
	 */
	public function SetBrowserFullname($browserFullname)
	{
		$this->browserFullname = $browserFullname;
	}
	
	/**
	 * Return the complete user agent string
	 * @return string 
	 */
	public function GetBrowserFullname()
	{
		return $this->browserFullname;
	}
	
	/**
	 * Set the OS of the client
	 * @param string $operatingSystem
	 */
	public function SetOperatingSystem($operatingSystem)
	{
		$this->operatingSystem = $operatingSystem;
	}
	
	/**
	 * Return the OS of the client
	 * @return string 
	 */
	public function GetOperatingSystem()
	{
		return $this->operatingSystem;
	}
	
	/**
	 * Set the ip address of the client
	 * @param string $clientIp
	 */
	public function SetClientIp($clientIp)
	{
		$this->clientIp = $clientIp;
	}
	
	/**
	 * Return the ip address of the client
	 * @return string 
	 */
	public function GetClientIp()
	{
		return $this->clientIp;
	}
	
	/**
	 * Set the http user agent string
	 * @param string $httpUserAgentString
	 */
	public function SetHttpUserAgentString($httpUserAgentString)
	{
		$this->httpUserAgentString = $httpUserAgentString;
	}
	
	/**
	 * Return http user agent string
	 * @return string 
	 */
	public function GetHttpUserAgentString()
	{
		return $this->httpUserAgentString;
	}
	
}
?>