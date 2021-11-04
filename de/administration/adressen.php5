<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen

$HM = 2;
$UM = 2;

include("page_top.inc.php5");

ob_start();

$list1 = new NCASList( new AddressGroupListData($db, $lm, $addressManager) );
$list2 = new NCASList( new AddressCompanyListData($db, $lm, $addressManager) );
$list3 = new NCASList( new AddressDataListData($db, $lm, $addressManager) );

?>
<!-- NEUE GRUPPE ANLEGEN -->
<br />
<a href="adressgruppen_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/adressgruppen_new.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neue Gruppe anlegen</span>
	</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="adressCompany_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/addressCompany_new.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neue Firma anlegen</span>
	</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="adressen_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/address_new.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neuer Ansprechpartner anlegen</span>
	</span>
</a>
<? if( $_SESSION["currentUser"]->GetGroupBasetype($db) >= UM_GROUP_BASETYPE_ADMINISTRATOR ){ ?>
	<br /><br />
	<a href="#" style="text-decoration:none;" onfocus="if (this.blur) this.blur();" onclick="javascript:window.open('../sync/syncAddressGroups.php5?<?=SID?>','Gruppensynchronisation','width=950,height=680,location=no,status=yes,resizable=yes');">
		<span style="position:relative; left:30px;">
			<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/adressgruppen_sync.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Gruppen mit CSV-Datei synchronisieren</span>
		</span>
	</a>
	<a href="#" style="text-decoration:none;" onfocus="if (this.blur) this.blur();" onclick="javascript:window.open('../sync/syncAddressCompanies.php5?<?=SID?>','Firmensynchronisation','width=950,height=680,location=no,status=yes,resizable=yes');">
		<span style="position:relative; left:30px;">
			<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/addressCompany_sync.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Firmen mit CSV-Datei synchronisieren</span>
		</span>
	</a>
	<a href="#" style="text-decoration:none;" onfocus="if (this.blur) this.blur();" onclick="javascript:window.open('../sync/syncAddressData.php5?<?=SID?>','Ansprechpartnersynchronisation','width=950,height=680,location=no,status=yes,resizable=yes');">
		<span style="position:relative; left:30px;">
			<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/address_sync.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Ansprechpartner mit CSV-Datei synchronisieren</span>
		</span>
	</a>
<? }?>

<br /><br />
<form method="post" action="#" name="form_lists" id="form_lists">
	<? $list1->PrintData(); ?>
	<br />
	<? $list2->PrintData(); ?>
	<br />
	<? $list3->PrintData(); ?>
</form>
	
<?
$CONTENT = ob_get_contents();
ob_end_clean();

include("template_1row.inc.php5"); // Content includen

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>