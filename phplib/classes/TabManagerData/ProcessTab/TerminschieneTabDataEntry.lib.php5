<?php
/**
 * TabDataEntry implementation
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class TerminschieneTabDataEntry extends TabDataEntry 
{
	/**
	 * db object
	 * @var DBManager 
	 */
	protected $db = null;
	
	/**
	 * current proccess
	 * @var ProcessStatus
	 */
	protected $processStatus = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ProcessStatus $processStatus
	 */
	public function TerminschieneTabDataEntry(DBManager $db, ProcessStatus $processStatus)
	{
		$this->db = $db;
		$this->processStatus = $processStatus;
		parent::__construct(ProcessTabData::TAB_TS, "Terminschiene");
	}
	
	/**
	 * Return if the tab is visible
	 * @return bool 
	 */
	public function IsVisible()
	{
		if ($this->processStatus->GetPKey()==-1) return false;
		if (!UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_SHOW_PROCESS_TAB_TERMINSCHIENE)) return false;
		return true;
	}
	
	/**
	 * Output the HTML for this tabs
	 * @return bool
	 */
	public function PrintContent()
	{
		$process = $this->processStatus;
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td width="29">&#160;</td>
				<td>&#160;</td>
				<td width="29">&#160;</td>
			</tr>
			<tr>
				<td>&#160;</td>
				<td valign="top">
					<?include("template_terminschiene.inc.php5");?>
				</td>
				<td>&#160;</td>
			</tr>
		</table>
		<br>
		<?
	}
	
}
?>