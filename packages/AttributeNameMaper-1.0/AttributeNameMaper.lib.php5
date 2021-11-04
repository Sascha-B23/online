<?php
/**
 * FormData-Implementierung für die Gruppen
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
interface AttributeNameMaper
{
	
	/**
	 * Return a human readable name for the requested attribute
	 * @param ExtendedLanguageManager $languageManager
	 * @param string $attributeName
	 * @return string
	 */
	static public function GetAttributeName(ExtendedLanguageManager $languageManager, $attributeName);
	
}
?>