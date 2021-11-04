<?php
/**
 * Baseclass for implementing spezial sql queries for filter
 * 
 * @author Stephan Walleczek <s.walleczek@stollvongati.com>
 * @created 20-Jul-2012 13:16:06
 */
abstract class DynamicTableQuery
{
	
	/**
	 * Corresponding filter
	 * @var DynamicTableFilter 
	 */
	protected $filter = null;
	
	/**
	 * Constructor
	 * @param DynamicTableFilter $filter
	 */
	function DynamicTableQuery(DynamicTableFilter $filter)
	{ 
		$this->filter = $filter;
	}

	/**
	 * Return the where clause for this filter
	 * @param DBManager $db
	 * @param DynamicTableColumn $column
	 * @return string
	 */
	abstract public function GetWhereClause(DBManager $db, DynamicTableColumn $column);

}
?>