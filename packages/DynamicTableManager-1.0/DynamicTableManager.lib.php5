<?php
require_once ('filters/DynamicTableQuery.lib.php5');
require_once ('filters/DynamicTableFilter.lib.php5');
require_once ('filters/DynamicTableFilterCheckbox.lib.php5');
require_once ('filters/DynamicTableFilterLink.lib.php5');
require_once ('filters/DynamicTableFilterText.lib.php5');
require_once ('columns/DynamicTableColumn.lib.php5');
require_once ('DynamicTable.lib.php5');
require_once ('DynamicTableInformation.lib.php5');
require_once ('DynamicTableRow.lib.php5');
require_once ('DynamicTableConfig.lib.php5');
require_once ('DynamicTableConfigManager.lib.php5');


/**
 * @author jglaser
 * @version 1.0
 * @created 20-Jul-2012 13:15:52
 */
class DynamicTableManager
{
	/**
	 * Singelton Instance 
	 * @var DynamicTableManager 
	 */
	private static $instance = null;
	
	protected $jsHttpRoot = '';
	protected $cssHttpRoot = '';
	protected $iconHttpRoot = '';
	protected $htmlLayoutFile = '';
	
	/**
	 * The dynamic tables
	 * @var DynamicTable[]  
	 */
	private $m_DynamicTables = Array();
	
	/**
	 * Return the singelton instance this class
	 * On the first call the first three paramters are required 
	 * @param string $jsHttpRoot
	 * @param string $cssHttpRoot
	 * @param string $iconHttpRoot
	 * @param string $htmlLayoutFile
	 * @return DynamicTableManager
	 */
	static public function GetInstance($jsHttpRoot='', $cssHttpRoot='', $iconHttpRoot='', $htmlLayoutFile = '')
	{
		if (self::$instance==null)
		{
			if ($jsHttpRoot!='' && $cssHttpRoot!='' && $iconHttpRoot!='')
			{
				self::$instance = new DynamicTableManager($jsHttpRoot, $cssHttpRoot, $iconHttpRoot, $htmlLayoutFile);
			}
		}
		return self::$instance;
	}

	/**
	 * On wake up from session set the static var vor the singelton to this object
	 */
	public function __wakeup()
	{
		self::$instance = $this;
	}
	
	/**
	 * Constructor
	 * @param string $jsHttpRoot
	 * @param string $cssHttpRoot
	 * @param string $iconHttpRoot
	 * @param string $htmlLayoutFile
	 */
	private function DynamicTableManager($jsHttpRoot, $cssHttpRoot, $iconHttpRoot, $htmlLayoutFile = '')
	{
		$this->jsHttpRoot = $jsHttpRoot;
		$this->cssHttpRoot = $cssHttpRoot;
		$this->iconHttpRoot = $iconHttpRoot;
		if ($htmlLayoutFile!='')
		{
			$this->htmlLayoutFile = $htmlLayoutFile;
		}
		else
		{
			$this->htmlLayoutFile = dirname(__FILE__)."/templates/dynamicTable.inc.php5";
		}
		
	}
	
	/**
	 * Return the required javascript includes for the HMLT-Head
	 * @return string
	 */
	public function GetReqirementHtmlString()
	{
		ob_start();
		?>
		<script type="text/javascript" src="<?=$this->jsHttpRoot;?>Types.config.js?date=<?=date("dmY", time());?>"></script>
		<script type="text/javascript" src="<?=$this->jsHttpRoot;?>DynamicTable.class.js?date=<?=date("dmY", time());?>"></script>
		<script type="text/javascript" src="<?=$this->jsHttpRoot;?>ContextMenu.class.js?date=<?=date("dmY", time());?>"></script>
		<link rel="stylesheet" type="text/css" href="<?=$this->cssHttpRoot;?>dynamicTable.css?date=<?=date("dmY", time());?>" />
		<?
		$returnValue = ob_get_contents();
		ob_end_clean();
		return $returnValue;
	}
	
	/**
	 * Return the required HMTL template code which have to be included in every page which a table is displayed
	 * @return string
	 */
	public function GetTemplateHtmlString()
	{
		ob_start();
		include($this->htmlLayoutFile);
		$returnValue = ob_get_contents();
		ob_end_clean();
		return $returnValue;
	}
	
	/**
	 * Function to add a DynamicTable
	 * @param  DynamicTable $dynamicTable
	 * @return bool
	 */
	public function AddDynamicTable(DynamicTable $dynamicTable)
	{
		if (trim($dynamicTable->GetId())=="") return false;
		$this->m_DynamicTables[$dynamicTable->GetId()] = $dynamicTable;
		return true;
	}
	
	/**
	 * Function to get a DynamicTable by the DynamicTableID
	 * @param string $id
	 * @return DynamicTable
	 */
	public function GetDynamicTableById($id)
	{
		if (trim($id)=="") return null;
		if (isset($this->m_DynamicTables[$id])) return $this->m_DynamicTables[$id];
		return null;
	}
	
	/**
	 * Remove the DynamicTable
	 * @param string $id
	 */
	public function RemoveDynamicTable($id)
	{
		if (trim($id)=="") return;
		unset($this->m_DynamicTables[$id]);
	}
	
	/**
	 * Function to get all DynamicTables
	 * @return DynamicTable[]
	 */
	public function GetDynamicTables()
	{
		return $this->m_DynamicTables;
	}
	
}
?>