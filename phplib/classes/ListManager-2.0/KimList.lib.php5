<?php
/**
 * List Manager for Dynamic Table
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class KimList
{	
	/**
	 * ListData
	 * @var DynamicTable 
	 */
	protected $listData = null;
		
	/**
	 * Constructor
	 * @param DynamicTable $listData
	 */
	public function KimList(DynamicTableManager $dynamicTableManager, DynamicTable $listData)
	{
		$this->listData = $listData;
	}
	
	/**
	 * Print the dynamic table
	 * @global type $SHARED_HTTP_ROOT
	 */
	public function PrintData()
	{
		global $SHARED_HTTP_ROOT;
		?>
		<div id="dynamictable_<?=$this->listData->GetId();?>" style="width:100%;"></div>
		<script type="text/javascript">
			var dynamic_table_<?=$this->listData->GetId();?> = null;
			window.addEvent('domready', function(){
				dynamic_table_<?=$this->listData->GetId();?> = new DynamicTable($("dynamictable_<?=$this->listData->GetId();?>"), {viewID: "<?=$this->listData->GetId();?>", jsonRequestFile: "<?=$SHARED_HTTP_ROOT?>packages/DynamicTableManager-1.0/jsonInterface/jsonInterface.php5?<?=SID;?>"});
			});
		</script>
		<?
	}
}
?>