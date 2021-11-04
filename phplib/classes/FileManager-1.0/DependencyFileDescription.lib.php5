<?php
/**
 * Interface to query file description
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
interface DependencyFileDescription
{

	const SEPERATOR = " / ";
	
	/**
	 * Return file description
	 */
	public function GetDependencyFileDescription();
	
}
?>