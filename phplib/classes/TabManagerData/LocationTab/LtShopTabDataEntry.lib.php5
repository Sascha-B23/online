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
class LtShopTabDataEntry extends LtLocationBaseTabDataEntry 
{
	/**
	 * current shop
	 * @var CShop
	 */
	protected $shop = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 * @param CShop $shop
	 */
	public function LtShopTabDataEntry(DBManager $db, ExtendedLanguageManager $languageManager, CShop $shop)
	{
		$this->shop = $shop;
		parent::__construct($db, $languageManager, $this->shop->GetPKey(), $this->shop->GetName());
	}
	
	/**
	 * Output the HTML for this tabs
	 * @return bool
	 */
	public function PrintContent()
	{
		$rsUserString="-";
		$rsUser=$this->shop->GetCPersonRS();
		if($rsUser!=null)
		{
			$rsUserString=$rsUser->GetName()." ".$rsUser->GetFirstName();
			if( $rsUser->GetAddressData()!=null )
			{
				if( $this->userIsRSMember )$rsUserString="<a href='javascript:ShowAddress(".$rsUser->GetAddressData()->GetPKey().")'>".$rsUserString."</a>";
			}
		}
		$customerUserString="-";
		$customerUser=$this->shop->GetCPersonCustomer();
		if($customerUser!=null)
		{
			$customerUserString=$customerUser->GetName()." ".$customerUser->GetFirstName();
			if( $customerUser->GetAddressData()!=null )
			{
				if( $this->userIsRSMember )$customerUserString="<a href='javascript:ShowAddress(".$customerUser->GetAddressData()->GetPKey().")'>".$customerUserString."</a>";
			}
		}
		// Header ausgeben
		$headerData=Array();
		$headerData[0]=Array( 	Array( "name" => CShop::GetAttributeName($this->languageManager, 'RSID'), "value" => $this->shop->GetRSID() ),
								Array( "name" => CShop::GetAttributeName($this->languageManager, 'internalShopNo'), "value" => trim($this->shop->GetInternalShopNo())=="" ? "-" : $this->shop->GetInternalShopNo() )
							);

		$headerData[1]=Array( 	Array( "name" => "Erstes Jahr", "value" => $this->shop->GetFirstYear() )
							);
		$headerData[2]=Array( 	Array( "name" => "Ansprechpartner SFM", "value" => $rsUserString ),
								Array( "name" => "Ansprechpartner Kunde", "value" => $customerUserString ),
							);

		if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_SHOW_REPORT_STANDORTVERGLEICH_AMPEL)) $headerData[0][] = Array( "name" => "Ampelbewertung", "value" => "<a href='standortvergleichAmpel.php5?".SID."&shop=".$this->shop->GetPKey()."' target='_svAmpel'>Anzeigen</a>" );
		if (UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_SHOW_REPORT_STANDORTVERGLEICH_PROZESS)) $headerData[1][] = Array( "name" => "Prozessstatus", "value" => "<a href='standortvergleichProzess.php5?".SID."&shop=".$this->shop->GetPKey()."' target='_svProzess'>Anzeigen</a>" );

		$this->PrintTabHeaderContent($headerData);
		// Verträge ausgeben...
		$tabs = new TabManager(new LtContractTabData($this->db, $this->languageManager, $this->shop));
		$tabs->PrintData();
	}
	
}
?>