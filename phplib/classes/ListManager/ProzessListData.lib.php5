<?php

/**
 * ListData-Implementierung für Prozesse
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2011 Stoll von Gáti GmbH www.stollvongati.com
 */
class ProzessListData extends ListData 
{

	/**
	 * @var WorkflowManager
	 */
	protected $manager = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 * @param WorkflowManager $workflowManager 
	 */
	public function ProzessListData(DBManager $db, ExtendedLanguageManager $languageManager, WorkflowManager $workflowManager)
	{
		parent::__construct($db, $languageManager);
		$this->manager = $workflowManager;		
		// Options Array setzen	
		$this->options["icon"]="passiveTask.png";
		$this->options["icontext"]="Prozesse";			
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => WorkflowStatus::GetAttributeName($this->languageManager, 'currentStatus'), "sortby" => ProcessStatus::TABLE_NAME.".currentStatus" ),
											1 => array( "caption" => CLocation::GetAttributeName($this->languageManager, 'name'), "sortby" => CLocation::TABLE_NAME.".name" ),
											2 => array( "caption" => CShop::GetAttributeName($this->languageManager, 'name'), "sortby" => CShop::TABLE_NAME.".name" ),
											3 => array( "caption" => AbrechnungsJahr::GetAttributeName($this->languageManager, 'jahr'), "sortby" => AbrechnungsJahr::TABLE_NAME.".jahr" ),
											4 => array( "caption" => WorkflowStatus::GetAttributeName($this->languageManager, 'deadline'), "sortby" => ProcessStatus::TABLE_NAME.".deadline", "width" => "70px" ),
											5 => array( "caption" => "Bearbeitungszeit", "sortby" => "duration", "width" => "60px" ),
											6 => array( "caption" => "WS-Summe", "sortby" => "", "width" => "60px"/*, "align" =>"right"*/ ),
											7 => array( "caption" => "Info", "sortby" => ProcessStatus::TABLE_NAME.".telefontermin", "width" => "60px" ),
											8 => array( "caption" => "Optionen", "sortby" => "" ),
										);
	}
	
	/**
	 * Funktion zum Abrufen der Daten, die in der Liste angezeigt werden sollen
	 * @param string	$searchString 		Suchstring (leer bedeutet KEINE Suche)
	 * @param string	$orderBy 			Sortiert nach Spalte
	 * @param string	$orderDirection 	Sortier Richtung (ASC oder DESC)
	 * @param int	$numEntrysPerPage 	Anzahl der Einträge pro Seite
	 * @param int	$currentPage 		Angezeigte Seite
	 * @return array	Das Rückgabearray muss folgendes Format haben:
	 *			Array[index][rowIndex] = content 	mit 	index = laufvaribale von 0 bis  $numEntrysPerPage 
	 *										rowIndex = Zugehörige Spalte siehe auch $this->data["datahead"]
	 *										content = Ausgabetext oder URL-Array in der Format Array[urlType] = url mit urlType = [editUrl oder deleteUrl]
	 * @access public
	 */
	public function Search($searchString, $orderBy, $orderDirection="ASC", $numEntrysPerPage=20, $currentPage=0){
		global $UM_GROUP_BASETYPE, $SHARED_HTTP_ROOT;
		$return=null;
		$objects=$this->manager->GetWorkflows($searchString, $this->data["datahead"][(int)$orderBy]["sortby"], $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for( $a=0; $a<count($objects); $a++){
			$return[$a][0]=$objects[$a]->GetCurrentStatusName($_SESSION["currentUser"], $this->db);
			// Namem von Standort und Laden bei Prozess-Workflows ermitteln
			$return[$a][1]="-";
			$return[$a][2]="-";
			if( $objects[$a]->GetStatusTyp()==WM_WORKFLOWSTATUS_TYPE_PROCESS ){
				$cShop=$objects[$a]->GetShop();
				if( $cShop!=null && is_a($cShop, "CShop") ){
					$return[$a][2]=$cShop->GetName();
					$cLocation=$cShop->GetLocation();
					if( $cLocation!=null && is_a($cLocation, "CLocation") ){
						$return[$a][1]=$cLocation->GetName();
					}
				}
			}
			$return[$a][3]=$objects[$a]->GetAbrechnungsJahr()==null ? "-" : $objects[$a]->GetAbrechnungsJahr()->GetJahr();
			$return[$a][4]=$objects[$a]->GetDeadline()==0 ? "-" : date("d.m.Y", $objects[$a]->GetDeadline());
			// Anzahl Tage berechnen, seit der Prozess gestartet wurde
			$numTage=floor( $objects[$a]->GetDuration($this->db)/60/60/24 );
			// Farbe bestimmen (< 50 Tage: grün, >=50 Tage und  <65 Tage: gelb, >=65 Tage rot)
			$color="#008800";
			if( $numTage>=50 && $numTage<65 )$color="#dd8800";
			if( $numTage>=65 )$color="#dd0000";
			$return[$a][5]="<font color='".$color."'>".$numTage."</font>";
			// Widerspruchssumme
			$return[$a][6]="-";
			if( $objects[$a]->GetStatusTyp()==WM_WORKFLOWSTATUS_TYPE_PROCESS )
			{
				$wsSumme = $objects[$a]->GetWiderspruchssumme($this->db);
				if( $wsSumme===false )
				{
					$return[$a][6] = "-";
				}
				else
				{
					$return[$a][6] = $objects[$a]->GetCurrency()." ".HelperLib::ConvertFloatToLocalizedString($wsSumme);
				}
			}
			// Info-Spalte
			$return[$a][7]="";
			// Ist die Aufgabe von diesem Benutzer oder von einem anderen (z.B. Urlaubsvertretung)?
			$cShop=$objects[$a]->GetShop();
			if( $cShop!=null ){
				if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE ){
					$origUser=$cShop->GetCPersonRS();
				}else{
					$origUser=$cShop->GetCPersonCustomer();
				}
				if( $objects[$a]->GetZuweisungUser()!=null && $objects[$a]->GetZuweisungUser()->GetPKey()==$_SESSION["currentUser"]->GetPKey() ){
					// Aufgabe wurde zugewiesen
					$return[$a][7].="<img src='".$SHARED_HTTP_ROOT."pics/gui/assigned.png' alt='Zugewiesene Aufgabe von ".$origUser->GetUserName()."' title='Zugewiesene Aufgabe von ".$origUser->GetUserName()."' />";
				}else{
					if( $origUser!=null && $origUser->GetPKey()!=$_SESSION["currentUser"]->GetPKey() && $_SESSION["currentUser"]->GetPKey()==$origUser->GetCoverUser() ){
						// Urlaubsvertretung
						$return[$a][7].="<img src='".$SHARED_HTTP_ROOT."pics/gui/assigned_to_replacement.png' alt='Urlaubsvertretung von ".$origUser->GetUserName()."' title='Urlaubsvertretung von ".$origUser->GetUserName()."' />";
					}else{
						$return[$a][7].="<img src='".$SHARED_HTTP_ROOT."pics/blind.gif' width='25' height='25' />";
					}
				}
			}else{
				$return[$a][7].="<img src='".$SHARED_HTTP_ROOT."pics/blind.gif' width='25' height='25' />";
			}
			if( $objects[$a]->GetTelefontermin()==0 || $_SESSION["currentUser"]->GetGroupBasetype($this->db)<=UM_GROUP_BASETYPE_KUNDE ){
				$return[$a][7].="<img src='".$SHARED_HTTP_ROOT."pics/blind.gif' width='25' height='25' />";
			}else{
				// Telefontermie können nur FMS-Mitarbeiter sehen
				$img="call_a.png";
				if( $objects[$a]->GetTelefontermin()-time()<60*60*24*3 )$img="call_b.png";
				if( $objects[$a]->GetTelefontermin()-time()<60*60*12 )$img="call_c.png";
				$return[$a][7].="<a href='javascript:EditDate(".$objects[$a]->GetPKey().");' title='Telefontermin am ".date("d.m.Y", $objects[$a]->GetTelefontermin())." um ".date("H:i",$objects[$a]->GetTelefontermin())." Uhr' alt='Telefontermin am ".date("d.m.Y", $objects[$a]->GetTelefontermin())." um ".date("H:i",$objects[$a]->GetTelefontermin())." Uhr'><img src='".$SHARED_HTTP_ROOT."pics/gui/".$img."' /></a>";
			}
			$return[$a][8]=Array();
			$return[$a][8]["editUrl"] = "prozesse_edit.php5?editElement=".$objects[$a]->GetPKey();
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db) >= UM_GROUP_BASETYPE_ADMINISTRATOR)
			{
				$return[$a][8]["deleteUrl"] = "";
				$return[$a][8]["pkey"] = $objects[$a]->GetPKey();
			}
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 * @access public
	 */
	public function GetNumTotalEntrys($searchString)
	{
		return $this->manager->GetWorkflowCount($searchString);
	}
	
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 */
	public function DeleteEntries($deleteArray)
	{
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db) < UM_GROUP_BASETYPE_ADMINISTRATOR) return;
		for ($a=0; $a<count($deleteArray); $a++)
		{
			$object=new ProcessStatus($this->db);
			if ($object->Load($deleteArray[$a], $this->db)===true)
			{
				$object->DeleteRecursive($this->db, $_SESSION["currentUser"]);
			}
		}
	}

}

?>