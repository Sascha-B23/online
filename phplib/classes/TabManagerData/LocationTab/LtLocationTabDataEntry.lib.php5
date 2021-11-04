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
class LtLocationTabDataEntry extends LtLocationBaseTabDataEntry 
{
	/**
	 * current location
	 * @var CLocation
	 */
	protected $location = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 * @param CLocation $location
	 */
	public function LtLocationTabDataEntry(DBManager $db, ExtendedLanguageManager $languageManager, CLocation $location)
	{
		$this->location = $location;
		parent::__construct($db, $languageManager, $this->location->GetPKey(), $this->location->GetName());
	}
	
	/**
	 * Output the HTML for this tabs
	 * @return bool
	 */
	public function PrintContent()
	{
		global $DOMAIN_HTTP_ROOT;
		// Header ausgeben
		$headerData = Array();
		$headerData[0] = Array( 	Array( "name" => "Straße", "value" => $this->location->GetStreet() ) );
		$headerData[1] = Array( 	Array( "name" => "PLZ / ".CLocation::GetAttributeName($this->languageManager, 'city'), "value" => $this->location->GetZIP()." ".$this->location->GetCity() ) );
		$headerData[2] = Array( 	Array( "name" => CLocation::GetAttributeName($this->languageManager, 'locationType'), "value" => GetLocationName($this->location->GetLocationType()) ) );
		
		// Kunden an diesem Standort
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$locationName = $this->location->GetName();
			if (trim($locationName)!="")
			{
				$customerGroups = CustomerManager::GetCustomerGroupsByLocationName($this->db, $_SESSION["currentUser"], $locationName);
				if (count($customerGroups)>0)
				{
					$headerData[0][] = Array( "name" => "Kunden an diesem Standort", "value" =>implode(", ", $customerGroups));
				}
			}
		}
		
		$this->PrintTabHeaderContent($headerData);
		
		?>
		<script type="text/javascript">
			<!--
				function ShowAddress(addressID)
				{
					var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/showAddress.php5?<?=SID;?>&editElement='+addressID,'_showAddress','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function ShowAddressCompany(addressID)
				{
					var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/showAddressCompany.php5?<?=SID;?>&editElement='+addressID,'_showAddressCompany','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
			-->
		</script>
		<?
		// Läden ausgeben...
		$tabs = new TabManager(new LtShopTabData($this->db, $this->languageManager, $this->location));
		$tabs->PrintData();
	}
	
}
?>