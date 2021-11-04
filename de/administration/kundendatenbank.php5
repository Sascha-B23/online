<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen
$HM = 2;
$UM = 5;
include("page_top.inc.php5");

ob_start();

// Kundengruppe löschen
if( is_numeric($_GET["deleteCGroup"]) && ((int)$_GET["deleteCGroup"])==$_GET["deleteCGroup"] ){
	$group = new CGroup($db);
	if( $group->Load((int)$_GET["deleteCGroup"], $db)===true){
		$group->DeleteMe($db);
	}
}
// Firma löschen
if( is_numeric($_GET["deleteCCompany"]) && ((int)$_GET["deleteCCompany"])==$_GET["deleteCCompany"] ){
	$company = new CCompany($db);
	if( $company->Load((int)$_GET["deleteCCompany"], $db)===true){
		$company->DeleteMe($db);
	}
}
// Standort löschen
if( is_numeric($_GET["deleteCLocation"]) && ((int)$_GET["deleteCLocation"])==$_GET["deleteCLocation"] ){
	$location = new CLocation($db);
	if( $location->Load((int)$_GET["deleteCLocation"], $db)===true){
		$location->DeleteMe($db);
	}
}
// Laden löschen
if( is_numeric($_GET["deleteCShop"]) && ((int)$_GET["deleteCShop"])==$_GET["deleteCShop"] ){
	$shop = new CShop($db);
	if( $shop->Load((int)$_GET["deleteCShop"], $db)===true){
		$shop->DeleteMe($db);
	}
}
// Vertrag löschen
/*if( is_numeric($_GET["deleteContract"]) && ((int)$_GET["deleteContract"])==$_GET["deleteContract"] ){
	$contract = new Contract($db);
	if( $contract->Load((int)$_GET["deleteContract"], $db)===true){
		$contract->DeleteMe($db);
	}
}*/

$list1 = new NCASList( new CustomerGroupListData($db, $lm, $customerManager) );
$list2 = new NCASList( new CustomerCompanyListData($db, $lm, $customerManager) );
$list3 = new NCASList( new CustomerLocationListData($db, $lm, $customerManager) );
$list4 = new NCASList( new CustomerShopListData($db, $lm, $customerManager) );

?>
<br />
<a href="cGroup_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/cGroup.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neue Gruppe anlegen</span>
	</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="cCompany_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/cCompany.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neue Firma anlegen</span>
	</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="cLocation_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/cLocation.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neuer Standort anlegen</span>
	</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="cShop_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/cShops.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neuer Laden anlegen</span>
	</span>
</a>
<? if( $_SESSION["currentUser"]->GetGroupBasetype($db) >= UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT ){ ?>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="#" style="text-decoration:none;" onfocus="if (this.blur) this.blur()" onclick="javascript:window.open('../import/importer.php5?<?=SID?>','Kundensynchronisation','width=950,height=680,location=no,status=yes,resizable=yes');">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/snycronisation.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Kundendaten mit CSV-Datei synchronisieren</span>
	</span>
</a>
<? }?>
<br /><br />
<form method="post" action="#" name="form_lists" id="form_lists">
	<? $list1->PrintData(); ?>
	<br><br>
	<? $list2->PrintData(); ?>
	<br><br>
	<? $list3->PrintData(); ?>
	<br><br>
	<? $list4->PrintData(); ?>
</form>

<?
$CONTENT = ob_get_contents();
ob_end_clean();

include("template_1row.inc.php5"); // Content includen

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>