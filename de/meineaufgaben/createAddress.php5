<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE);
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Kein Menü und Username zeigen
include("page_top.inc.php5");

// GET-Varibale holen
if( !isset($_POST["type"]) && isset($_GET["type"]) && is_numeric($_GET["type"]) )$_POST["type"]=Array((int)$_GET["type"]);

?>
<script type="text/javascript">
	<!--
		var addressIDString="";
		function ReturnToOpener(){
			if(dataStored)opener.SetAddress(<?=(int)$_GET["type"];?>, addressIDString); 
			// Fenster schließen
			window.close();
		}
	-->
</script>
<?
					
// Objekt initialisieren
$addressData = new AddressData($db);
$addressDataFormData = new AddressDataFormData($_POST, $addressData, $db);
if( $addressDataFormData->GetFormDataStatus()==1 ){
	// AddressStringID ausgeben, damit sie an den Opener übergebene werden kann
	?><script type="text/javascript">addressIDString="<?=$addressData->GetAddressIDString();?>";</script><?
}
$form = new OneColumnForm( "javascript:ReturnToOpener();", $addressDataFormData );
$form->PrintData(); 


include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>