<?php
include_once 'KimList.lib.php5';
/**
 * ListManager for DynamicTable
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class ListManager2
{
	
	/**
	 * Next free list id
	 * @var int 
	 */
	private static $listManagerID=0;
	
	/**
	 * Constructor
	 */
	private function ListManager()
	{
		// No Instance of this class needed so far
	}
	
	/**
	 * Return the next free list id
	 * @return int
	 */
	static public function GetNextFreeListManagerID()
	{
		$freeListID = self::$listManagerID;
		self::$listManagerID++;
		return $freeListID;
	}
	
}
?>