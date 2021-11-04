<?php

/**
 * Auswertungsklasse für den ListManager
 * 
 * @access   	public
 * @author   	Johannes Glaser <j.glaser@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class UserListManager extends ListData 
{
	
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * UserManager-Objekt
	 * @var UserManager
	 */
	protected $manager = null;
	
	/**
	 * Konstruktor
	 * @param DBManager $db
	 */
	public function UserListManager(DBManager $db)
	{
		$this->db = $db;
		global $userManager;
		$this->manager = $userManager;		
		// Options Array setzen	
		$this->options["icon"] = "user.png";
		$this->options["icontext"] = "Benutzer";		
		// Header definieren
		$this->data["datahead"] = array(	0 => array( "caption" => "Login", "sortby" => User::TABLE_NAME.".email" ),
											1 => array( "caption" => "Name", "sortby" => AddressData::TABLE_NAME.".name" ),
											2 => array( "caption" => "Mitglied der Gruppe", "sortby" => UserGroup::TABLE_NAME.".name" ),
											3 => array( "caption" => "Letzter Zugriff", "sortby" => User::TABLE_NAME.".lastLoginTime" ),
											4 => array( "caption" => "Namens-kürzel", "sortby" => AddressData::TABLE_NAME.".shortName" ),
											5 => array( "caption" => "Optionen", "sortby" => "" ),
										);
	}
	
	/**
	 * Funktion zum Abrufen der Daten, die in der Liste angezeigt werden sollen
	 * @param string	$searchString 		Suchstring (leer bedeutet KEINE Suche)
	 * @param string	$orderBy 			Sortiert nach Spalte
	 * @param string	$orderDirection 		Sortier Richtung (ASC oder DESC)
	 * @param int	$numEntrysPerPage 	Anzahl der Einträge pro Seite
	 * @param int	$currentPage 		Angezeigte Seite
	 * @return array	Das Rückgabearray muss folgendes Format haben:
	 *			Array[index][rowIndex] = content 	mit 	index = laufvaribale von 0 bis  $numEntrysPerPage 
	 *										rowIndex = Zugehörige Spalte siehe auch $this->data["datahead"]
	 *										content = Ausgabetext oder URL-Array in der Format Array[urlType] = url mit urlType = [editUrl oder deleteUrl]
	 */
	public function Search($searchString, $orderBy, $orderDirection="ASC", $numEntrysPerPage=20, $currentPage=0)
	{
		global $UM_GROUP_BASETYPE;
		global $SHARED_HTTP_ROOT;
		
		if( $this->data["datahead"][(int)$orderBy]["sortby"] != "")
		{
			$orderBy=$this->data["datahead"][(int)$orderBy]["sortby"];
		}
		else
		{
			$orderBy = "email";
		}
		
		$return = null;
		$objects = $this->manager->GetUsers($_SESSION["currentUser"], $searchString, $orderBy, $orderDirection=="ASC" ? 0 : 1, $currentPage, $numEntrysPerPage);
		for( $a=0; $a<count($objects); $a++)
		{
			$addressData = $objects[$a]->GetAddressData();
			$return[$a][0] = $objects[$a]->GetEMail();
			$return[$a][1] = $addressData==null ? "" : $addressData->GetName().($addressData->GetFirstName()!="" ? ", ".$addressData->GetFirstName() : "");
			$groups = $objects[$a]->GetGroups($this->db);
			$return[$a][2] = "";
			for ($b=0; $b<count($groups); $b++)
			{
				if ($return[$a][2]!="") $return[$a][2].=", ";
				$return[$a][2].=$groups[$b]->GetName();
			}
			$return[$a][3] = $objects[$a]->GetLastLogin()>0 ? date("H:i:s - d.m.Y", $objects[$a]->GetLastLogin() ) : "-";
			$return[$a][4]=$objects[$a]->GetShortName();
			$return[$a][5]=Array();
			if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)>=$objects[$a]->GetGroupBasetype($this->db))
			{
				if ($_SESSION["currentUser"]->GetGroupBasetype($this->db) >= UM_GROUP_BASETYPE_ADMINISTRATOR)
				{
					$return[$a][5]["editUrl"] = "user_edit.php5?editElement=" . $objects[$a]->GetPKey();
					if ($objects[$a]->IsDeletable($this->db))
					{
						$return[$a][5]["deleteUrl"] = "";
					}
				}
			}
			$return[$a][5]["pkey"] = $objects[$a]->GetPKey();
		}
		return $return;
	}
	
	/**
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int
	 */
	public function GetNumTotalEntrys($searchString)
	{
		return $this->manager->GetUserCount($_SESSION["currentUser"], $searchString);
	}
		
	/**
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 */
	public function DeleteEntries($deleteArray)
	{
		for($a=0; $a<count($deleteArray); $a++)
		{
			$object=new User($this->db);
			if ($object->Load($deleteArray[$a], $this->db)===true)
			{
				$object->DeleteMe($this->db);
			}
		}
	}
	
} // UserListManager

?>