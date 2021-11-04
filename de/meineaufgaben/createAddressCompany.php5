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

?>
<script type="text/javascript">
	<!--
		var addressName="";
		var addressId="";
		var addressCompanyId="";
		function ReturnToOpener()
		{
			if(dataStored)opener.SetAddressCompany("<?=(int)$_GET["type"];?>", addressName, addressId, addressCompanyId); 
			// Fenster schließen
			window.close();
		}
	-->
</script>
<?
					
// Objekt initialisieren
$addressCompany = new AddressCompany($db);
$addressCompanyFormData = new AddressCompanyFormData($_POST, $addressCompany, $db);
if ($addressCompanyFormData->GetFormDataStatus()==1)
{
	// AddressStringID ausgeben, damit sie an den Opener übergebene werden kann
	?><script type="text/javascript">addressName="<?=$addressCompany->GetName();?>"; addressId="<?=$addressCompany->GetPKey();?>"; addressCompanyId="<?=$addressCompany->GetAddressIDString();?>";</script><?
}
$form = new OneColumnForm("javascript:ReturnToOpener();", $addressCompanyFormData);
$form->PrintData(); 


include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>