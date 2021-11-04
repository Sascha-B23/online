<?php
/**
 * Interface
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.4
 * @version		1.0
 * @copyright 	Copyright (c) 2013 Stoll von Gáti GmbH www.stollvongati.com
 */
interface AddressBase
{
	/**
	 * Class types
	 */
	const AM_CLASS_UNKNOWN = 0;
	const AM_CLASS_ADDRESSDATA = 1;
	const AM_CLASS_ADDRESSCOMPANY = 2;
	const AM_CLASS_ADDRESSGROUP = 3;
	
	/**
	 * return the class type 
	 * @return int
	 */
	public function GetClassType();
	
	/**
	 * Gibt einen eindeutigen String für den Datensatz zurück
	 * @param bool $noID
	 * @return string
	 */
	public function GetAddressIDString($noID=false);
	
	/**
	 * Gibt einen String für diesen Datensatz zurück
	 * @return string
	 */
	public function GetOverviewString();
	
	/**
	 * return the name of the group
	 * @return string
	 */
	public function GetAddressGroupName();
	
	/**
	 * return default placeholders 
	 * @return Array
	 */
	public function GetPlaceholders(DBManager $db, $language = 'DE', $prefix = 'AP');
	
}
?>