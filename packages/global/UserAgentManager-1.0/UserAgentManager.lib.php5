<?
include('UserAgent.lib.php5');

/**
 * UserAgentManager
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class UserAgentManager
{

	/**
	 * Return information about the users agent
	 * @return UserAgent
	 */
	static public function GetCurrentUserAgent()
	{
		$userAgent = new UserAgent();
		$userAgent->SetHttpUserAgentString($_SERVER["HTTP_USER_AGENT"]);
		$userAgent->SetClientIp($_SERVER["REMOTE_ADDR"]);
		if(ini_get("browscap"))
		{
			self::SetUserAgentFromBrowscap($userAgent);
			if ($userAgent->GetBrowserName()=='Default Browser')
			{
				// fallback if browscap returns no result
				self::SetUserAgentBrowserFromPhpServerVar($userAgent);
				self::SetUserAgentOsFromPhpServerVar($userAgent);
			}
		}
		else
		{
			// fallback if no browscap is available on server
			self::SetUserAgentBrowserFromPhpServerVar($userAgent);
			self::SetUserAgentOsFromPhpServerVar($userAgent);
		}
		return $userAgent;
	}
	
	/**
	 * Set the browser and operating systemp attributes of the user agent
	 * @param UserAgent $userAgent 
	 */
	static protected function SetUserAgentFromBrowscap(UserAgent $userAgent)
	{
		$browser = @get_browser(null, true);
		//var_dump($browser);
		$userAgent->SetUseBrowscap(true);
		$userAgent->SetBrowserName($browser['browser']);
		$userAgent->SetBrowserFullname($browser['parent']);
		$userAgent->SetBrowserVersion($browser['majorver']);
		$userAgent->SetOperatingSystem($browser['platform']);
	}
	
	/**
	 * Set the browser attributes of the user agent
	 * @param UserAgent $userAgent
	 */
	static protected function SetUserAgentBrowserFromPhpServerVar(UserAgent $userAgent)
	{
		$userAgent->SetUseBrowscap(false);
		// Internet Explorer
		$browser = strstr($_SERVER["HTTP_USER_AGENT"], "MSIE");
		if ($browser!="")
		{
			$userAgent->SetBrowserName('IE');
			$userAgent->SetBrowserFullname('Internet Explorer');
			$userAgent->SetBrowserVersion(substr($browser,5,1));
		}

		// Netscape 3 & 4
		$browser = strstr($_SERVER["HTTP_USER_AGENT"], "Mozilla");
		$browser1=strstr($_SERVER["HTTP_USER_AGENT"], "compatible");
		if ($browser!="" && $browser1=="")
		{
			$userAgent->SetBrowserName('NS');
			$userAgent->SetBrowserFullname('Netscape Navigator');
			$userAgent->SetBrowserVersion(substr($browser,8,1));
		}

		// Netsape 6
		$browser = strstr($_SERVER["HTTP_USER_AGENT"], "Netscape6");
		if ($browser!="" && $browser1=="")
		{
			$userAgent->SetBrowserName('NS');
			$userAgent->SetBrowserFullname('Netscape Navigator');
			$userAgent->SetBrowserVersion(substr($browser,8,1));
		}

		// Gecko
		$browser=strstr($_SERVER["HTTP_USER_AGENT"],"Gecko");
		if (($browser!="") && ($browser1 ==""))
		{
			$userAgent->SetBrowserName('NS');
			$userAgent->SetBrowserFullname('Gecko');
			$userAgent->SetBrowserVersion(6);
		}

		// OPERA
		$browser=strstr($_SERVER["HTTP_USER_AGENT"],"Opera");
		if ($browser!="")
		{
			$userAgent->SetBrowserName('OP');
			$userAgent->SetBrowserFullname('Opera');
			$userAgent->SetBrowserVersion(substr($browser,6,1));
		}

		// Mozilla Firefox
		$browser=strstr($_SERVER["HTTP_USER_AGENT"],"Firefox");
		if ($browser!="")
		{
			$userAgent->SetBrowserName('FF');
			$userAgent->SetBrowserFullname('Mozilla Firefox');
			$versionTemp = trim(substr($_SERVER["HTTP_USER_AGENT"], strpos($_SERVER["HTTP_USER_AGENT"], "Firefox/")+8));
			$userAgent->SetBrowserVersion(substr($versionTemp,0,1));
		}

		// Safari Browser
		$browser=strstr($_SERVER["HTTP_USER_AGENT"],"Safari");
		if ($browser!="")
		{
			$userAgent->SetBrowserName('SA');
			$userAgent->SetBrowserFullname('Safari');
			$versionTemp = trim(substr($_SERVER["HTTP_USER_AGENT"], strpos($_SERVER["HTTP_USER_AGENT"], "Safari/")+7));
			$userAgent->SetBrowserVersion($versionTemp);
		}

		// Chrome
		$browser=strstr($_SERVER["HTTP_USER_AGENT"],"Chrome");
		if ($browser!="")
		{
			$userAgent->SetBrowserName('CR');
			$userAgent->SetBrowserFullname('Chrome');
			$versionTemp = trim(substr($_SERVER["HTTP_USER_AGENT"], strpos($_SERVER["HTTP_USER_AGENT"], "Chrome/")+7));
			$userAgent->SetBrowserVersion($versionTemp);	
		}
		
	}
	
	/**
	 * Set the operating system attributes
	 * @param UserAgent $userAgent 
	 */
	static protected function SetUserAgentOsFromPhpServerVar(UserAgent $userAgent)
	{
		$platform = "";
		if (preg_match('/win/i',  $_SERVER['HTTP_USER_AGENT']))
		{
			// Windows
			$platform = 'Windows'; 
			if (preg_match('/windows nt 4.0/i', $_SERVER['HTTP_USER_AGENT']))
			{
				$platform = 'Windows 2000'; 
			} 
			elseif(preg_match('/windows nt 5.1/i', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/media center/i', $_SERVER['HTTP_USER_AGENT'])) 
			{
				$platform = 'Windows XP'; 
			} 
			elseif(preg_match('/windows nt 5.1/i', $_SERVER['HTTP_USER_AGENT']))
			{ 
				$platform = 'Windows XP Media Center'; 
			} 
			elseif(preg_match('/windows nt 5.2/i', $_SERVER['HTTP_USER_AGENT']))
			{
				$platform = 'Windows Server 2003'; 
			} 
			elseif(preg_match('/windows ce/i', $_SERVER['HTTP_USER_AGENT'])) 
			{ 
				$platform = 'Windows CE'; 
			} 
			elseif(preg_match('/windows 95/i', $_SERVER['HTTP_USER_AGENT'])) 
			{ 
				$platform = 'Windows 95'; 
			} 
			elseif(preg_match('/windows 98/i', $_SERVER['HTTP_USER_AGENT'])) 
			{ 
				$platform = 'Windows 98'; 
			} 
			elseif(preg_match('/windows 4.0/i', $_SERVER['HTTP_USER_AGENT'])) 
			{ 
				$platform = 'Windows NT 4.0'; 
			} 
			elseif(preg_match('/windows nt 6.0/i', $_SERVER['HTTP_USER_AGENT']))
			{
				$platform = 'Windows Vista'; 
			} 
			elseif(preg_match('/windows nt 6.1/i', $_SERVER['HTTP_USER_AGENT']))
			{
				$platform = 'Win7'; 
			} 
			elseif(preg_match('/windows nt/i', $_SERVER['HTTP_USER_AGENT'], $test)) 
			{ 
				$platform = 'Windows NT'; 
			} 
		}
		elseif(preg_match('/mac/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/apple/i', $_SERVER['HTTP_USER_AGENT'])) 
		{ 
			// Macintosh
			$platform = 'Macintosh';
			if(preg_match('/mac os x/i', $_SERVER['HTTP_USER_AGENT']))
			{ 
				$platform = 'Mac OS X'; 
			} 
		}
		elseif(preg_match('/linux/i', $_SERVER['HTTP_USER_AGENT']))
		{ 
			// Linux
			$platform = 'Linux'; 
			if(preg_match('/ubuntu/i', $_SERVER['HTTP_USER_AGENT']))
			{ 
				$platform = 'Ubuntu Linux'; 
			} 
			elseif(preg_match('/debian/i', $_SERVER['HTTP_USER_AGENT'])) 
			{ 
				$platform = 'Debian Linux'; 
			} 
			elseif(preg_match('/fedora/i', $_SERVER['HTTP_USER_AGENT'])) 
			{ 
				$platform = 'Fedora Core'; 
			} 
			elseif(preg_match('/gentoo/i', $_SERVER['HTTP_USER_AGENT'])) 
			{ 
				$platform = 'Gentoo Linux'; 
			} 
			elseif(preg_match('/suse/i', $_SERVER['HTTP_USER_AGENT'])) 
			{ 
				$platform = 'SuSE Linux'; 
			} 
		}
		elseif(preg_match('/OS\/2/i', $_SERVER['HTTP_USER_AGENT'])) 
		{ 
			$platform = 'OS/2'; 
		}
		elseif(preg_match('/freebsd/i', $_SERVER['HTTP_USER_AGENT']))
		{
			$platform = 'FreeBSD'; 
		}
		elseif(preg_match('/BeOS/i', $_SERVER['HTTP_USER_AGENT']))
		{ 
			$platform = 'BeOS'; 
		}
		elseif(preg_match('/palmos/i', $_SERVER['HTTP_USER_AGENT'])) 
		{ 
			$platform = 'PalmOS'; 
		}
		elseif(preg_match('/sunos/i', $_SERVER['HTTP_USER_AGENT'])) 
		{ 
			$platform = 'SunOS'; 
		}
		elseif(preg_match('/nitro/i', $_SERVER['HTTP_USER_AGENT'])) 
		{ 
			$platform = 'Nintendo DS'; 
		}
		elseif(preg_match('/nintendo wii/i', $_SERVER['HTTP_USER_AGENT'])) 
		{ 
			$platform = 'Nintendo Wii';
			if(preg_match('/shop channel/i', $_SERVER['HTTP_USER_AGENT']))
			{
				$browserinfo['platform'] .= ' (shop channel)';
			} 
		}
		elseif(preg_match('/symbian/i', $_SERVER['HTTP_USER_AGENT'])) 
		{ 
			$platform = 'Symbian OS'; 
		}
		elseif(preg_match('/playstation 3/i', $_SERVER['HTTP_USER_AGENT'])) 
		{ 
			$platform = 'Playstation 3'; 
		}
		elseif(preg_match('/playstation portable/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/psp/i', $_SERVER['HTTP_USER_AGENT']))
		{ 
			$platform = 'Playstation Portable'; 
		}
		// Set the operating system
		if ($platform!="") $userAgent->SetOperatingSystem($platform);
	}
	
}
?>