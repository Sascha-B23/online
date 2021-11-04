<?php
/***************************************************************************
 * Klasse mit Hilfsfunktionen
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class HelperLib {

	/***************************************************************************
	* Wandelt den übergebenen String in einen Integer-Wert um, und gibt diesen zurück. Im Fehlerfall wird 
	* false zurückgeben
	* @param string	Zu wandelnder String
	* @return bool/int Gibt im Erfolgsfall den Integer oder im Fehlerfall false zurück
	* @access public
	***************************************************************************/
	static public function ConvertStringToInteger($string){
		$string=trim($string);
		if( is_numeric($string) && ((int)$string)==$string )return (int)$string;
		return false;
	}
	
	/***************************************************************************
	* Wandelt den übergebenen String in einen Float-Wert um, und gibt diesen zurück. Im Fehlerfall wird 
	* false zurückgeben
	* @param string	Zu wandelnder String
	* @return bool/float Gibt im Erfolgsfall einen Float oder im Fehlerfall false zurück
	* @access public
	***************************************************************************/
	static public function ConvertStringToFloat($string){
		$string=trim($string);
		// Prüfen, ob ein Punkt verwendet wird
		if( stripos($string, ".")!==false ){
			// Nach Vor- und Nachkommateil trennen
			$parts=explode(",", $string);
			if( count($parts)>2 )return false;
			// Im Nachkommateil darf kein Punkt vorhanden sein
			if( count($parts)==2 && stripos($parts[1], ".")!==false )return false;
			// Vorkommateil in Blöcke teilen
			$blocks=explode(".", $parts[0]);
			// Jeder Block muss 3 Stellen beinhalten -> es darf immer nur an jeder 4. Stelle ein Punkt stehen
			for($a=1; $a<count($blocks); $a++){
				if( strlen($blocks[$a])!=3 )return false;
			}
			// Erster Block darf auch weniger Stellen haben (aber max. 3)
			if( strlen($blocks[0])>3 )return false;
			// Alles OK -> Punkte entfernen
			$string=trim(str_replace(".", "", $string));
		}
		$string=str_replace(",", ".", $string);
		if( is_numeric($string) ){
			return (float)$string;
		}
		return false;
	}
	
	/***************************************************************************
	* Wandelt den übergebenen Float in einen String um, und gibt diesen zurück. Im Fehlerfall wird 
	* false zurückgeben
	* @param float	Zu wandelnder Float
	* @param string	Länderkürzel, in dessen Format der String gewandelt werden soll
	* @return bool/string Gibt im Erfolgsfall einen String oder im Fehlerfall false zurück
	* @access public
	***************************************************************************/
	static public function ConvertFloatToLocalizedString($number, $language="DE"){
		$language=strtoupper(trim($language));
		if($language=="DE")return number_format($number, 2 ,",",".");
		return number_format($number, 2);
	}
	
	/***************************************************************************
	* Wandelt den übergebenen Float in einen String um, und gibt diesen zurück. Im Fehlerfall wird 
	* false zurückgeben
	* @param float	Zu wandelnder Float
	* @param string	Länderkürzel, in dessen Format der String gewandelt werden soll
	* @return bool/string Gibt im Erfolgsfall einen String oder im Fehlerfall false zurück
	* @access public
	***************************************************************************/
	static public function ConvertFloatToRoundedLocalizedString($number, $language="DE"){
		$language=strtoupper(trim($language));
		if($language=="DE")return number_format($number, 0 ,",",".");
		return number_format($number, 0);
	}
	
	/**
	 * Gibt den Mwst-Satz für das überegbene Jahr zurück
	 * @param int $year Jahr für welches der Mwst-Satz zurückgegeben werden soll
	 */
	static public function GetMwst($year)
	{
		return 1.19;
	}
	
	/**
	 * Wandelt den übergebene String in einen Timestamp
	 * @return bool/int Gibt im Erfolgsfall den Timestamp oder im Fehlerfall false zurück
	 */
	static public function ConvertStringToTimeStamp($dateString)
	{
		// Beauftragungsdatum
		$dateParts = explode(".", trim($dateString));
		if (count($dateParts)==3)
		{
			if (((int)$dateParts[0])>=1 && ((int)$dateParts[0])<=31)
			{
				if (((int)$dateParts[1])>=1 && ((int)$dateParts[1])<=12)
				{
					if (((int)$dateParts[2])>=1900)
					{
						return mktime(0,0,0,(int)$dateParts[1], (int)$dateParts[0], (int)$dateParts[2]);
					}
				}
			}
		}
		return false;
	}
	
}

?>
