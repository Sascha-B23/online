<?php
require_once 'LanguageManager.lib.php5';

/**
 * ExtendedLanguageManager
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class ExtendedLanguageManager 
{
	/**
	 * LanguageManagers instance
	 * @var LanguageManager
	 */
	protected $languageManager = null;
	
	/**
	 * Root directory with languagepacks
	 * @var string
	 */
	protected $languagePackageRoot = "";
	
	/**
	 * Active country
	 * @var string
	 */
	protected $countryToUse = "";
	
	/**
	 * Active language
	 * @var string
	 */
	protected $languageToUse = "";
	
	/**
	 * Construcor
	 * @param string $languagePackageRoot Root directory with languagepacks
	 * @param string $country Country
	 * @param string $language Language
	 */
	public function ExtendedLanguageManager($languagePackageRoot, $country, $language)
	{
		$this->languageManager = new LanguageManager($languagePackageRoot);
		$this->languagePackageRoot = $languagePackageRoot;
		$this->countryToUse = $country;
		$this->languageToUse = $language;
	}
	
	/**
	 * Set active language
	 * @param string $language				Language
	 */
	public function SetLanguage($language)
	{
		$this->languageToUse = $language;
	}

	/**
	 * Return active language
	 * @return string 						Language
	 */
	public function GetLanguage()
	{
		return $this->languageToUse;
	}
	
	/**
	 * Return text in active language
	 * @param string	$packageName			Name of the package (e.g. 'System')
	 * @param string	$stringID				ID of the string to return
	 * @return string						Text in active language
	 */
	public function GetString($packageName, $stringID)
	{
		return $this->languageManager->GetString($packageName, $this->countryToUse, $this->languageToUse, $stringID);
	}
	
	/**
	 * Return text in active language after replacing all occurence of array keys with array value ($replacements) 
	 * @param string $packageName Name of the package (e.g. 'System')
	 * @param string $stringID ID of the string to return
	 * @param array[search => replace] $replacements Keys = Suchstrings die im Text (String) durch das zugeordnete Array-Value ersetzt werden.
	 * @return string Text in active language
	 */
	public function GetStringReplace($packageName, $stringID, $replacements)
	{
		$theText = $this->GetString($packageName, $stringID);
		if (is_array($replacements))
		{
			// replace all values provieded by the $replacements array
			foreach ($replacements as $search => $replace)
			{
				$theText = str_ireplace($search, $replace, $theText);
			}
		}
		return $theText;
	}
	
	/**
	 * Returns the language manager instance
	 * @return LanguageManager
 	 */
	public function GetLanguageManager()
	{
		return $this->languageManager;
	}
	
	/**
	 * Returns all available languages for the active country from languagepack root directory 
	 * @param string $packageToUse Packagename to check the languages for
	 * @return Array
	 */
	public function GetAvailableLanguages($packageToUse="SYSTEM")
	{
		$dirContent = scandir($this->languagePackageRoot);
		$languageFiles = Array();
		foreach ($dirContent as $dirEntry)
		{
			// Verzeichnisse überspringen
			if (is_dir($dirEntry)) continue;
			// Konfigurationen haben die Form "inst_XXXXX.php5" wobei X für eine Großbuchstaben oder eine Zahl steht
			$matches = null;
			if (preg_match("/^".$packageToUse."_".$this->countryToUse."_([0-9A-Z]+).xml\b/", $dirEntry, $matches)==0 || $matches==null) continue;
			$languageFiles[] = Array("short" => $matches[1], "long" => $this->languageManager->GetString("SYSTEM", $this->countryToUse, $matches[1], "ID_LANGUAGE"));
		}
		return $languageFiles;
	}
	
}
?>