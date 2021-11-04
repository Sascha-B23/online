<?php
/**
 * Sprachpaketverwaltung
 *
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 4.0
 * @version		2.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class LanguageManager 
{
	/**
	 * Verzeichnispfad zu den Sprachdateien
	 * @var string
	 */
	private $languagePackageRoot = "";

	/**
	 * Array mit allen geladenen Sprachinformationen
	 * @var array
	 */
	private $languageData = Array();

	/**
	 * Active language for simplified access
	 * @var string
	 */
	private $activeLanguage = "DE";

	/**
	 * Active country for simplified access
	 * @var string
	 */
	private $activeCountry = "DE";

	/**
	 * Default package name for simplified access
	 * @var string
	 */
	private $defaultPackage = null;

	/**
	 * Konstruktor
	 * @param string $languagePackageRoot Verzeichnis in dem alle Sprachpakete liegen
	 * @param string $defaultPackage Name of the default language package
	 */
	public function LanguageManager($languagePackageRoot, $defaultPackage = "SYSTEM")
	{
		$this->languagePackageRoot = $languagePackageRoot;
		$this->defaultPackage = $defaultPackage;
	}

	/**
	 * Gibt den String in der gewünschten Sprache für das gewünschte Land zurück
	 * @param string $packageName Name des Packetes (z.B. 'System')
	 * @param string $countryShort Land (z.B. 'at')
	 * @param string $languageShort Sprache (z.B. 'de')
	 * @param string $stringID ID des Strings der abgerufen werden soll
	 * @return string String in der gewünschten Sprache für das gewünschte Land
	 */
	public function GetString($packageName, $countryShort, $languageShort, $stringID)
	{
		$index=$this->GetPackageIndex($packageName, $countryShort, $languageShort);
		if( $index==-1 )return "ERROR LOADING LANGUAGE PACK";
		if( !isset($this->languageData[$index]["data"][$stringID]) )return "MISSING ENTRY FOR ".$stringID;
		return $this->languageData[$index]["data"][$stringID];
	}

	/**
	 * Get the localized text from default package for the active country and language.
	 * @param string $stringID  The ID of the text element.
	 * @return string
	 */
	public function GetText($stringID)
	{
		return $this->GetString($this->defaultPackage, $this->activeCountry, $this->activeLanguage, $stringID);
	}

	/**
	 * Get the localized text from default package for the active country and language with all occurences of <code>$replacements</code> replaced. The <code>$replacements</code> is an array of key->value mappings defining what to replace how.
	 * @param string $stringID The ID of the text element.
	 * @param array[search => replace] Keys are searched in the text and replaced with their values.
	 * @return string
	 */
	public function GetTextReplaced($stringID, $replacements)
	{
		$theText = $this->GetText($stringID);
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
	 * Setter for active iso country code. Mehtod GetText() uses this as country.
	 * @param string $isoCountryCode - the iso country code like 'AT'
	 */
	public function SetActiveCountry($isoCountryCode)
	{
		if (mb_strlen($isoCountryCode) > 1)
		{
			$this->activeCountry = mb_strtoupper($isoCountryCode, "UTF-8");
		}
	}

	/**
	 * Setter for active iso language code. Mehtod GetText() uses this as language.
	 * @param string $isoLanguageCode - the iso language code like 'DE'
	 */
	public function SetActiveLanguage($isoLanguageCode)
	{
		if (mb_strlen($isoLanguageCode) > 1)
		{
			$this->activeLanguage = mb_strtoupper($isoLanguageCode, "UTF-8");
		}
	}

	/**
	 * Gibt den String in der gewünschten Sprache für das gewünschte Land zurück
	 * @param string $packageName Name des Packetes (z.B. 'System')
	 * @param string $countryShort Land (z.B. 'at')
	 * @param string $languageShort Sprache (z.B. 'de')
	 * @return int
	 */
	private function GetPackageIndex($packageName, $countryShort, $languageShort)
	{
		// Dateiname
		$languageFile=$this->languagePackageRoot.$packageName."_".$countryShort."_".$languageShort.".xml";

		// Datei bereits geladen?
		for( $a=0; $a<count($this->languageData); $a++)
		{
			if ( $this->languageData[$a]["file"]==$languageFile )
			{
				return $a;
			}
		}
		// Datei jetzt laden
		if( !($fp = @fopen($languageFile, "r")) )return -1;
		$data = fread($fp, filesize($languageFile));
		fclose($fp);
		$xml_parser = xml_parser_create();
		//xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
		//xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
		$success=xml_parse_into_struct($xml_parser, $data, $values, $tags);
		xml_parser_free($xml_parser);
		if ( $success==0 )
		{
			return -1;
		}
		// Durch die Struktur laufen...
		$languageData=Array();
		foreach ($tags as $key=>$val)
		{
			// ... und nach 'entry'-Tag suchen
			if ( strcasecmp($key, "entry")!=0 )
			{
				continue;
			}
			// Alle 'entry'-Tags verarbeiten...
			for ($i=0; $i < count($val); $i++ )
			{
				if (!isset($values[$val[$i]]["attributes"]) || !is_array($values[$val[$i]]["attributes"]) || !isset($values[$val[$i]]["attributes"]["ID"]) || trim($values[$val[$i]]["attributes"]["ID"])=="" )
				{
					continue;
				}
				// ... und im Array merken
				$languageData[$values[$val[$i]]["attributes"]["ID"]]=isset($values[$val[$i]]["value"]) ? $values[$val[$i]]["value"] : "";
			}
		}
		$this->languageData[]=Array("file" => $languageFile , "data" => $languageData);
		return count($this->languageData)-1;
	}

}
?>