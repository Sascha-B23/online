<?

$path = $SHARED_HTTP_ROOT."de/";
$curMainMenuIndex = 1;

// --------------------------- Meine Aufgaben ----------------------------------
$mainmenu[$curMainMenuIndex]["name"] = "Meine Aufgaben";
$mainmenu[$curMainMenuIndex]["img"] = "hm_menu_1.gif";
$mainmenu[$curMainMenuIndex]["id"] = 1;
	
	$submenu[$curMainMenuIndex][1]["name"] = "Workflowstatus";
	$submenu[$curMainMenuIndex][1]["link"] = $path."meineaufgaben/meineaufgaben.php5";
	$submenu[$curMainMenuIndex][1]["img"] = "dd_aufgaben_01.gif";
	$submenu[$curMainMenuIndex][1]["subID"] = 1;
	
	$curMainMenuIndex++;

// --------------------------- Administration ----------------------------------
if( $_SESSION["currentUser"]->GetGroupBasetype($db)>=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT ){
	
	$mainmenu[$curMainMenuIndex]["name"] = "Administration";
	$mainmenu[$curMainMenuIndex]["img"] = "hm_menu_2.gif";
	$mainmenu[$curMainMenuIndex]["id"] = 2;

	$curSubMainMenuIndex=1;
	
	// Menü nur bei Benutzer anzeigen, die min. zur Administrator-Gruppe gehören
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Benutzerverwaltung";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path."administration/user.php5";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_admin_01.gif";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 1;
	$curSubMainMenuIndex++;

	
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Adressdatenbank";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path."administration/adressen.php5";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_admin_03.gif";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 2;
	$curSubMainMenuIndex++;
	
	// Menü nur bei Benutzer anzeigen, die min. zur Administrator-Gruppe gehören
	if( $_SESSION["currentUser"]->GetGroupBasetype($db)>=UM_GROUP_BASETYPE_ADMINISTRATOR )
	{
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Kostenarten";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path."administration/kostenarten.php5";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_admin_05.gif";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 3;
		$curSubMainMenuIndex++;
		
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Textbausteine Wiederspruch";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path."administration/textbausteine.php5";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_admin_06.gif";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 4;
		$curSubMainMenuIndex++;
	}

	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Kundendatenbank";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "administration/kundendatenbank.php5";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_admin_04.gif";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 5;
	$curSubMainMenuIndex++;


	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Verträge";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path."administration/contracts.php5";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_admin_07.gif";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 6;
	$curSubMainMenuIndex++;

	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Prozesse";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path."administration/prozesse.php5";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_admin_08.gif";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 7;
	$curSubMainMenuIndex++;

	// Menü nur bei Benutzer anzeigen, die min. zur Administrator-Gruppe gehören
	if( $_SESSION["currentUser"]->GetGroupBasetype($db)>=UM_GROUP_BASETYPE_ADMINISTRATOR )
	{
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Länder / Währungen";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "administration/countries_currencies.php5";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_admin_09.gif";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 8;
		$curSubMainMenuIndex++;
	}

	// Menü nur bei Benutzer anzeigen, die min. zur Administrator-Gruppe gehören
	if( $_SESSION["currentUser"]->GetGroupBasetype($db) >= UM_GROUP_BASETYPE_ADMINISTRATOR ){

		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Zugriffsprotokoll";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path."administration/logging.php5";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_admin_10.gif";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 9;
		$curSubMainMenuIndex++;
	}

	if( $_SESSION["currentUser"]->GetGroupBasetype($db)>=UM_GROUP_BASETYPE_ADMINISTRATOR )
	{
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Textvorlagen";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "administration/standardtext.php5";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_admin_11.gif";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 10;
		$curSubMainMenuIndex++;
	}
	
	$curMainMenuIndex++;
}

// Menü nur bei Benutzer anzeigen, die min. zur FMS-Gruppe gehören
if ($_SESSION["currentUser"]->GetGroupBasetype($db)>=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT)
{
	// --------------------------- Berichte ----------------------------------
	$mainmenu[$curMainMenuIndex]["name"] = "Berichte";
	$mainmenu[$curMainMenuIndex]["img"] = "hm_menu_4.gif";
	$mainmenu[$curMainMenuIndex]["id"] = 3;

		$curSubMainMenuIndex=1;
		
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Kundenstandorte";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path."berichte/kundenstandorte.php5";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_01.gif";
		$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 1;
		$curSubMainMenuIndex++;

		if ($_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT)
		{
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Teilabrechnungen";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "berichte/teilabrechnungen.php5";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_07.gif";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 7;
			$curSubMainMenuIndex++;

			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Teilabrechnungspositionen";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "berichte/teilabrechnungspositionen.php5";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_05.gif";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 8;
			$curSubMainMenuIndex++;

			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Prozessstatus";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "berichte/standortvergleichProzess2.php5";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_06.gif";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 3;
			$curSubMainMenuIndex++;

			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Standortvergleich Prozessstatus";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "berichte/standortvergleichProzess.php5";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_02.gif";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 2;
			$curSubMainMenuIndex++;

			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Terminschiene";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "berichte/terminschiene.php5";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_04.gif";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 6;
			$curSubMainMenuIndex++;

			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Standortvergleich Ampelbewertung";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "berichte/standortvergleichAmpel.php5";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_03.gif";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 4;
			$curSubMainMenuIndex++;

			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Ampelbewertung CSV";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "berichte/standortvergleichAmpelCSV.php5";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_08.gif";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 5;
			$curSubMainMenuIndex++;

			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Kürzungsbeträge CSV";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "berichte/kuerzungsbetraege.php5";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_09.gif";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 9;
			$curSubMainMenuIndex++;

			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Konditions- und Fristenliste";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "berichte/konditionsundfristenliste.php5";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_10.gif";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 10;
			$curSubMainMenuIndex++;

			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Kostenarten CSV";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "berichte/kostenarten.php5";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_11.gif";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 11;
			$curSubMainMenuIndex++;

			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Testspalte";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path . "berichte/kostenarten.php5";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_berichte_10.gif";
			$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 12;
			$curSubMainMenuIndex++;
		}
	$curMainMenuIndex++;
}

// --------------------------- Meine Daten ----------------------------------
$mainmenu[$curMainMenuIndex]["name"] = "Meine Daten";
$mainmenu[$curMainMenuIndex]["img"] = "hm_menu_3.gif";
$mainmenu[$curMainMenuIndex]["id"] = 4;
$mainmenu[$curMainMenuIndex]["link"] = $path."meinedaten/meinedaten.php5";


$curMainMenuIndex++;
	
// --------------------------- KIM-PRO Button ----------------------------------
$mainmenu[$curMainMenuIndex]["name"] = "zu Kim-Pro";
$mainmenu[$curMainMenuIndex]["img"] = "hm_menu_5.gif";
$mainmenu[$curMainMenuIndex]["id"] = 5;


	$curSubMainMenuIndex=1;
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["name"] = "Klassifikation";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["link"] = $path."kimpro/klassifikation.php5";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["img"] = "dd_pro_01.png";
	$submenu[$curMainMenuIndex][$curSubMainMenuIndex]["subID"] = 15;
	$curSubMainMenuIndex++;

$curMainMenuIndex++;
?>