<?php
require 'TabData.lib.php5';
require 'TabDataEntry.lib.php5';

/**
 * Klasse zur Darstellung von Tab-Einträgen
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TabManager 
{
	/**
	 * Total count of tabs on the current page
	 * @var int
	 */
	private static $tabManagerID = 0;
	
	/**
	 * Unique ID for this tabs on current page
	 * @var int
	 */
	private $tabID = 0;
	
	/**
	 * TabData object
	 * @var TabData
	 */
	private $tabData = null;
	
	/**
	 * Constructor
	 * @param TabData $tabData
	 */
	public function TabManager(TabData $tabData)
	{
		$this->tabData = $tabData;
		$this->tabID = self::$tabManagerID;
		self::$tabManagerID++;
	}
	
	/**
	 * Return the unique ID of this tabs on current page
	 * @return int
	 */
	public function GetListID()
	{
		return $this->tabID;
	}
	
	/**
	 * Output the HTML for this tabs
	 * return bool
	 */
	public function PrintData()
	{
		global $DOMAIN_HTTP_ROOT;
		$data = $this->tabData->GetVisibleTabEntries();
		// Aktuelles ausgewähltes Tab ermitteln
		$currentActiveTab = "";
		if (!isset($_POST["showTab_".self::$tabManagerID]) && isset($_GET["showTab_".self::$tabManagerID])) $_POST["showTab_".self::$tabManagerID] = $_GET["showTab_".self::$tabManagerID];
		elseif (isset($_POST["showTab_".self::$tabManagerID]) && is_numeric($_POST["showTab_".self::$tabManagerID])) $currentActiveTab = $_POST["showTab_".self::$tabManagerID];
		if ($currentActiveTab=="" || !$this->tabData->IsTabDataEntryIdAvailable($currentActiveTab)) $currentActiveTab = $this->tabData->GetDefaultActiveTab();
		?>
	 	<input type="hidden" name="showTab_<?=self::$tabManagerID;?>" id="showTab_<?=self::$tabManagerID;?>" value="<?=$currentActiveTab;?>">
		<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr>
				<td style="background-image:url('<?=$DOMAIN_HTTP_ROOT?>pics/reiter/hg_leiste.jpg'); background-repeat: repeat-x; height: 28px;">
					<table cellpadding="0" cellspacing="0">
						<tr><?

							for($a=0; $a<count($data); $a++){
								// Anfangs Element
								if( $a==0 ){
									if( $data[$a]->GetId()==$currentActiveTab ){
										?><td width="29"><img src="<?=$DOMAIN_HTTP_ROOT;?>pics/reiter/abschluss_links_aktiv.jpg"></td><?
									}else{
										?><td width="29"><img src="<?=$DOMAIN_HTTP_ROOT;?>pics/reiter/abschluss_links.jpg"></td><?
									}
								}
								if( $data[$a]->GetId()==$currentActiveTab ){
									?><td style="background-image:url('<?=$DOMAIN_HTTP_ROOT?>pics/reiter/hg_aktiv.jpg'); background-repeat: repeat-x;"><nobr><font class="tabActive"><?=$data[$a]->GetCaption();?></font></nobr></td><?
								}else{
									?><td style="background-image:url('<?=$DOMAIN_HTTP_ROOT?>pics/reiter/hg_normal.jpg'); background-repeat: repeat-x;"><nobr><a href="javascript: document.forms.FM_FORM.showTab_<?=self::$tabManagerID;?>.value='<?=$data[$a]->GetId();?>'; document.forms.FM_FORM.submit();"><font class="tabInactive"><?=$data[$a]->GetCaption();?></font></a></nobr></td><?
								}
								// Abschluss Element
								if( $a==count($data)-1 ){
									if( $data[$a]->GetId()==$currentActiveTab ){
										?><td width="28"><img src="<?=$DOMAIN_HTTP_ROOT;?>pics/reiter/abschluss_rechts_aktiv.jpg"></td><?
									}else{
										?><td width="28"><img src="<?=$DOMAIN_HTTP_ROOT;?>pics/reiter/abschluss_rechts.jpg"></td><?
									}
								}else{
									if( $data[$a]->GetId()==$currentActiveTab ){
										?><td width="30"><img src="<?=$DOMAIN_HTTP_ROOT;?>pics/reiter/zwischen_rechts_aktiv.jpg"></td><?
									}elseif( $data[$a+1]->GetId()==$currentActiveTab ){
										?><td width="30"><img src="<?=$DOMAIN_HTTP_ROOT;?>pics/reiter/zwischen_links_aktiv.jpg"></td><?
									}else{
										?><td width="30"><img src="<?=$DOMAIN_HTTP_ROOT;?>pics/reiter/zwischen_links_normal.jpg"></td><?
									}
								}
							}
							?>
						</tr>
					</table>
				</td>
			</tr>
		</table><?
		return $this->tabData->PrintContent($currentActiveTab);
	}
	
}
?>