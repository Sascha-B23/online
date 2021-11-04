<?php
/**
 * @author jglaser
 * @version 1.0
 * @created 20-Jul-2012 13:16:04
 */
class DynamicTableInformation
{
	
	/**
	 * @property  String  $icon  The icon (defaults to "")
	 */
	private $icon = "";
	
	/**
	 * @property  String  $headline  The headline (defaults to "")
	 */
	private $headline = "";
	
	/**
	 * @property  int  $pageCount  The page count (defaults to 0)
	 */
	private $pageCount = 0;
	
	/**
	 * @property  int  $currentPage  The current page (defaults to 0)
	 */
	private $currentPage = 0;
	
	/**
	 * @property  int  $entriesCount  The entries count (defaults to 0)
	 */
	private $entriesCount = 0;
	
	/**
	 * @property  int  $entriesPerPage  The entries per page (defaults to 0)
	 */
	private $entriesPerPage = 20;
	
	/**
	 * @property  Boolean  $minimized  The minimized state (defaults to false)
	 */
	private $minimized = false;
	
	/**
	 * @property  String  $search  The search string (defaults to "")
	 */
	private $search = "";

	/**
	 * @property  String  $onLoadCallbackFunction  Javascript function to call when table is loaded
	 */
	private $onLoadCallbackFunction = "";
	
	function DynamicTableInformation()
	{ 
		
	}
	
	/**
	 * Function to get the icon
	 * @return  String  The icon
	 */
	public function GetIcon()
	{
		return $this->icon;
	}

	/**
	 * Function to set the Icon
	 * @param  String  $newIcon  The new Icon
	 */
	public function SetIcon($newIcon)
	{
		$this->icon = $newIcon;
	}
	
	/**
	 * Function to get the headline
	 * @return  String  The headline
	 */
	public function GetHeadline()
	{
		return $this->headline;
	}

	/**
	 * Function to set the headline
	 * @param  String  $newHeadline The new headline
	 */
	public function SetHeadline($newHeadline)
	{
		$this->headline = $newHeadline;
	}
	
	/**
	 * Function to get the pageCount
	 * @return  int  The page count
	 */
	public function GetPageCount()
	{
		return $this->pageCount;
	}

	/**
	 * Function to set the PageCount
	 * @param  int  $newPageCount  The new pagecount
	 */
	public function SetPageCount($newPageCount)
	{
		$this->pageCount = $newPageCount;
	}
	
	/**
	 * Function to get the Current Page
	 * @return  int  The current page
	 */
	public function GetCurrentPage()
	{
		return $this->currentPage;
	}

	/**
	 * Function to set the new currentPage
	 * @param  int $newCurrentPage The new current page
	 */
	public function SetCurrentPage($newCurrentPage)
	{
		if (!is_numeric($newCurrentPage) || (int)$newCurrentPage<0) $newCurrentPage = 0;
		$this->currentPage = (int)$newCurrentPage;
		return true;
	}

	/**
	 * Function to get the entries count
	 * @return  int  The entries count
	 */
	public function GetEntriesCount()
	{
		return $this->entriesCount;
	}

	/**
	 * Function to set the new entries count
	 * @param  int  $newEntriesCount
	 */
	public function SetEntriesCount($newEntriesCount)
	{
		$this->entriesCount = $newEntriesCount;
	}

	/**
	 * Function to get the entries per page
	 * @return  int  The entries per page
	 */
	public function GetEntriesPerPage()
	{
		return $this->entriesPerPage;
	}

	/**
	 * Function to set the entries per page
	 * @param  int  $newEntriesPerPage  The new entries per page
	 */
	public function SetEntriesPerPage($newEntriesPerPage)
	{
		if (!is_numeric($newEntriesPerPage) || (int)$newEntriesPerPage<1) return false;
		$this->entriesPerPage = (int)$newEntriesPerPage;
		return true;
	}

	/**
	 * Function to get the minimized property
	 * @return  Boolean  Is it minimized (true or false)
	 */
	public function GetMinimized()
	{
		return $this->minimized;
	}

	/**
	 * Function to set the minimized property
	 * @param  Boolean  $newMinimized  The new property state (true or false)
	 */
	public function SetMinimized($newMinimized)
	{
		$this->minimized = $newMinimized;
	}

	/**
	 * Function to get the search string
	 * @return  String  The search string
	 */
	public function GetSearch()
	{
		return $this->search;
	}

	/**
	 * Function to set the search string
	 * @param  String  $newSearch  The new search string
	 */
	public function SetSearch($newSearch)
	{
		$this->search = $newSearch;
	}
	
	/**
	 * Function to set the onLoad avascript function
	 * @param  String  $onLoadCallbackFunction  The Javascript function name
	 */
	public function SetOnLoadCallbackFunction($onLoadCallbackFunction)
	{
		$this->onLoadCallbackFunction = $onLoadCallbackFunction;
	}
	
	/**
	 * Function to create the JSON Answer as Array
	 * @return  Array  The object information as array
	 */
	public function GetJSONAnswer()
	{
		$returnData = Array(
			"icon" => $this->icon,
			"headline" => $this->headline,
			"pageCount" => $this->pageCount,
			"currentPage" => $this->currentPage+1,
			"entriesCount" => $this->entriesCount,
			"entriesPerPage" => $this->entriesPerPage,
			"minimized" => $this->minimized,
			"search" => $this->search,
			"onLoadCallbackFunction" => $this->onLoadCallbackFunction
		);
		
		return $returnData;
	}
	
	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnStoreSettings()
	{
		return Array("entriesPerPage" => $this->entriesPerPage);
	}
	
	/**
	 * Function is called to retrive all data to be stored in the filter setting templates
	 * return Array()
	 */
	public function OnLoadSettings($settings)
	{
		$this->entriesPerPage = $settings["entriesPerPage"];
		return true;
	}
	
}
?>