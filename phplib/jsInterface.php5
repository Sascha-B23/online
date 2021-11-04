<?php
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("classes/UserManager-1.0/UserManager.inc.php5");
$USER_HAVE_TO_BE_LOGGED_IN=false;
require_once("session.inc.php5");

if( $_GET["debug"]=="true" ){
	$_POST=$_GET;
}

/**@var $customerManager CustomerManager */

$returnData=Array();
if( isset($_SESSION["currentUser"]) ){
	switch( ((int)$_POST["reqDataType"]) ){
		// Es werden die Firmen einer Gruppe abgefragt
		case 1:
		case 101:
			if( is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"] ){
				$object=$customerManager->GetGroupByID($_SESSION["currentUser"], (int)$_POST["param01"], ((int)$_POST["reqDataType"])==101 && ($_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE || $_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT));
				if( $object!=null ){
					$companys=$object->GetCompanys($db);
					for( $a=0; $a<count($companys); $a++){
						$returnData[]=array("name" => $companys[$a]->GetName(), "value" => $companys[$a]->GetPkey());
					}
				}
			}
			break;
		// Es werden die Standorte einer Firma abgefragt
		case 2:
		case 102:
			if( is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"] ){
				$object=$customerManager->GetCompanyByID($_SESSION["currentUser"], (int)$_POST["param01"], ((int)$_POST["reqDataType"])==102 && ($_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE || $_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT));
				if( $object!=null ){
					$userHasAccess = $object->HasUserAccess($_SESSION["currentUser"], $db);
					$locations=$object->GetLocations($db);
					for( $a=0; $a<count($locations); $a++){
						if ($userHasAccess || $locations[$a]->GetName()==$_POST["param02"] ) // if user has not full rights only return entries with the submitted name if exist
						{
							$returnData[] = array("name" => $locations[$a]->GetName(), "value" => $locations[$a]->GetPkey());
						}
					}
				}
			}
			break;
		// Es werden die Läden eines Standortes abgefragt
		case 3:
		case 103:
			if( is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"] ){
				$object=$customerManager->GetLocationByID($_SESSION["currentUser"], (int)$_POST["param01"], ((int)$_POST["reqDataType"])==103 && ($_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE || $_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT));
				if( $object!=null ){
					$shops=$object->GetShops($db);
					for( $a=0; $a<count($shops); $a++){
						$returnData[]=array("name" => $shops[$a]->GetName(), "value" => $shops[$a]->GetPkey());
					}
				}
			}
			break;
		// Es werden die Verträge eines Ladens abgefragt
		case 4:
		case 104:
			if( is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"] ){
				$object=$customerManager->GetShopByID($_SESSION["currentUser"], (int)$_POST["param01"], ((int)$_POST["reqDataType"])==104 && ($_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE || $_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT));
				if( $object!=null )
				{
					$contracts=$object->GetContracts($db);
					for( $a=0; $a<count($contracts); $a++)
					{
						$returnData[]=array("name" => "Vertrag ".($contracts[$a]->GetLifeOfLeaseString()=='' ? ($a+1) : $contracts[$a]->GetLifeOfLeaseString() ), "value" => $contracts[$a]->GetPkey());
					}
				}
			}
			break;
		// Es wird das Beauftragungsdatum eines Abrechnungsjahrs abgefragt
		case 5:
			if( is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"] ){
				$object=$rsKostenartManager->GetTeilabrechnungByID($_SESSION["currentUser"], (int)$_POST["param01"]);
				if( $object!=null ){
					$returnData[]=array("name" => date("d.m.Y", $object->GetAuftragsdatumAbrechnung()), "value" => date("d.m.Y", $object->GetAuftragsdatumAbrechnung()) );
					break;
				}
			}
			$returnData[]=array("name" => date("d.m.Y", time()), "value" => date("d.m.Y", time()) );
			break;
		// Suche-Textfeld (Adress-Data Abfrage)
		case 6:
			$search = $_POST["query"];
			if (is_string($search) && strlen($search) >= 3 && strlen($search) < 64 && is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"])
			{
				$objects = $addressManager->GetAddressData($search, "name", 0, 0, 25, (int)$_POST["param01"]);
				for ($i=0; $i<count($objects); $i++)
				{
					$str =  $objects[$i]->GetAddressIDString(); 
					$returnData[] = $str;
				}
				$objects = $addressManager->GetAddressCompany($search, "name", 0, 0, 25);
				for ($i=0; $i<count($objects); $i++)
				{
					$str =  $objects[$i]->GetAddressIDString(); 
					$returnData[] = $str;
				}
				sort($returnData);
			}
			break;
		// Es werden unzugeordnete Teilabrechnungen abgefragt
		case 7:
			if( is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"] && is_numeric($_POST["param02"]) && ((int)$_POST["param02"])==$_POST["param02"] ){
				$object=$rsKostenartManager->GetContractByID($_SESSION["currentUser"], (int)$_POST["param01"]);
				if( $object!=null ){
					$abrechnungsjahre=$object->GetAbrechnungsJahre($db);
					for( $a=0; $a<count($abrechnungsjahre); $a++){
						if( $abrechnungsjahre[$a]->GetJahr()==(int)$_POST["param02"] ){
							// Alle untergeordneten, keinem Prozess zugewiesenen Teilabrechnungen holen
							$teilabrechnungen=$rsKostenartManager->GetTeilabrechnungenByYear($_SESSION["currentUser"], $abrechnungsjahre[$a]->GetPKey());
							for( $b=0; $b<count($teilabrechnungen); $b++){
								$returnData[]=array("name" => $teilabrechnungen[$b]->GetBezeichnung(), "value" => $teilabrechnungen[$b]->GetPKey() );
							}
						}
					}
				}
			}
			break;
		// Es wird die Vorauszahlung laut Buchhaltung für eine bestimmte Abrechnung abgefragt
		case 8:
			if( is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"] ){
				$object=$rsKostenartManager->GetTeilabrechnungByID($_SESSION["currentUser"], (int)$_POST["param01"]);
				if( $object!=null ){
					$returnData[]=array("name" => HelperLib::ConvertFloatToLocalizedString( $object->GetVorauszahlungLautKunde() ), "value" => HelperLib::ConvertFloatToLocalizedString( $object->GetVorauszahlungLautKunde() ) );
				}
			}
			break;
		// Es soll der Anteil des Kunden berechnet und zurückgegeben werden
		case 9:
			if( is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"] && is_numeric($_POST["param02"]) && is_numeric($_POST["param03"]) && is_numeric($_POST["param04"]) ){
				$object=null;
				// Pkey der TAP übergeben?
				if( (int)$_POST["param01"]==-1 ){
					// Pkey einer TAP wurde nicht übergeben -> wurde Pkey einer TA übergeben?
					if( is_numeric($_POST["param05"]) && ((int)$_POST["param05"])==$_POST["param05"] ){
						// Pkey einer TA wurde übergeben -> TAP anlegen...
						$object=new Teilabrechnungsposition($db);
						// ...TA laden und diese der TAP zuweisen..
						$object->SetTeilabrechnung( $rsKostenartManager->GetTeilabrechnungByID($_SESSION["currentUser"], (int)$_POST["param05"]) );
					}
				}else{
					// Pkey einer TAP wurde übergeben -> TAP laden...
					$object=$rsKostenartManager->GetTeilabrechnungspositionByID($_SESSION["currentUser"], (int)$_POST["param01"]);
				}
				if( $object!=null ){
					$returnData[]=$object->GetBetragKundeSoll($db, (float)$_POST["param02"], (float)$_POST["param03"], (float)$_POST["param04"], true);
				}
			}
			break;
		// Es werden die Abrechnungsjahre eines Vertrages abgefragt
		case 10:
			if( is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"] ){
				$object=$rsKostenartManager->GetContractByID($_SESSION["currentUser"], (int)$_POST["param01"]);
				if( $object!=null ){
					$abrechnungsjahre=$object->GetAbrechnungsJahre($db);
					for( $a=0; $a<count($abrechnungsjahre); $a++){
						$returnData[]=array("name" => $abrechnungsjahre[$a]->GetJahr(), "value" => $abrechnungsjahre[$a]->GetPKey() );
					}
				}
			}
			break;
		// Es werden die Teilabrechnungen für ein Jahr (pkey) abgefragt
		case 11:
			if( is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"] ){
				$objects=$rsKostenartManager->GetTeilabrechnungenByYear($_SESSION["currentUser"], (int)$_POST["param01"], false);
				for( $a=0; $a<count($objects); $a++){
					$returnData[]=array("name" => $objects[$a]->GetBezeichnung(), "value" => $objects[$a]->GetPKey() );
				}
			}
			break;
		// Hide the difference of the settlement (Abrechnungsdifferenz)
		case 12:
			if( is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"] )
			{
				$teilabrechnung=$rsKostenartManager->GetTeilabrechnungByID($_SESSION["currentUser"], (int)$_POST["param01"]);
				if( $teilabrechnung!=null )
				{
					$teilabrechnung->SetSettlementDifferenceHidden(true);
					if ($teilabrechnung->Store($db)===true )
					{
						$returnData[]="OK";
					}
				}
			}
			break;
		// EMail Suche
		case 13:
			$search = $_POST["query"];
			if (is_string($search) && strlen($search) >= 3 && strlen($search) < 64 && is_numeric($_POST["param01"]) && ((int)$_POST["param01"])==$_POST["param01"])
			{
				$objects = $addressManager->GetAddressData( $search, "name", 0, 0, 25, (int)$_POST["param01"], AddressData::TABLE_NAME.".email!=''");
				for ($i=0; $i<count($objects); $i++)
				{
					$str =  $objects[$i]->GetEmailString(); 
					$returnData[] = $str;
				}
				$objects = $addressManager->GetAddressCompany($search, "name", 0, 0, 25, AddressCompany::TABLE_NAME.".email!=''");
				for ($i=0; $i<count($objects); $i++)
				{
					$str =  $objects[$i]->GetEmailString(); 
					$returnData[] = $str;
				}
				sort($returnData);
			}
			break;
        case 14:
            $search = trim($_POST["query"]);
            $standardTextManager = new StandardTextManager($db);
            $objects = $standardTextManager->GetStandardText($search, "name", 0, 0, 25, "", StandardText::ST_TYPE_SCHEDULECOMMENT);
            for ($i=0; $i<count($objects); $i++)
            {
                $str =  $objects[$i]->GetStandardText(strtoupper($LANG));
                $returnData[] = "".$str;
            }
            break;
		// Suche-Textfeld (Adress-Data Abfrage)
		case 15:
			$search = $_POST["query"];
			if (is_string($search) && strlen($search) >= 3 && strlen($search) < 64)
			{
				/** @var RSKostenartManager $rsKostenartManager */
				$hits = $rsKostenartManager->GetBezeichnungTeilflächeAndFmsKostenart($search);
				for ($i=0; $i<count($hits); $i++)
				{
					$str =  $hits[$i]['bezeichnungKostenart']." [".$hits[$i]['kostenartRS']."]";
					$returnData[] = $str;
				}
			}
			break;
		default:
			$returnData[]="Unbekannter Request";
	}
}else{
	$returnData[]="Zugriff verweigert";
}


echo json_encode($returnData);
?>
