<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen

$HM = 2;
$UM = 6;

include("page_top.inc.php5");

ob_start();

// Vertrag löschen
if( is_numeric($_GET["deleteContract"]) && ((int)$_GET["deleteContract"])==$_GET["deleteContract"] ){
	$contract = new Contract($db);
	if( $contract->Load((int)$_GET["deleteContract"], $db)===true){
		$contract->DeleteMe($db);
	}
}
// Teilabrechnung löschen
if( is_numeric($_GET["deleteTeilabrechnung"]) && ((int)$_GET["deleteTeilabrechnung"])==$_GET["deleteTeilabrechnung"] ){
	$teilabrechnung = new Teilabrechnung($db);
	if( $teilabrechnung->Load((int)$_GET["deleteTeilabrechnung"], $db)===true){
		$teilabrechnung->DeleteMe($db);
	}
}


$list1 = new NCASList( new ContractListData($db, $lm) );
$list2 = new NCASList( new TeilabrechnungListData($db, $lm) );
$list3 = new NCASList( new AbrechnungsJahreListData($db, $lm) );

?>

<br />
<a href="contract_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur();">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/contract.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neuen Vertrag anlegen</span>
	</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="teilabrechnung_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur();">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/teilabrechnung.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neue Teilabrechnung anlegen</span>
	</span>
</a>
<? if( $_SESSION["currentUser"]->GetGroupBasetype($db) >= UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT ){ ?>

	<br />
	<a href="#" style="text-decoration:none;" onfocus="if (this.blur) this.blur();" onclick="javascript:window.open('../sync/syncContracts.php5?<?=SID?>','Vertragssynchronisation','width=950,height=680,location=no,status=yes,resizable=yes');">
		<span style="position:relative; left:30px;">
			<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/snycronisation.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Verträge synchronisieren</span>
		</span>
	</a>
	<a href="#" style="text-decoration:none;" onfocus="if (this.blur) this.blur();" onclick="javascript:window.open('../sync/syncTaps.php5?<?=SID?>','Teilabrechnungspositionensynchronisation','width=950,height=680,location=no,status=yes,resizable=yes');">
		<span style="position:relative; left:30px;">
			<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/snycronisation.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Teilabrechnungspositionen synchronisieren</span>
		</span>
	</a>
	<a href="#" style="text-decoration:none;" onfocus="if (this.blur) this.blur();" onclick="javascript:window.open('../sync/syncKuerzungsbetraege.php5?<?=SID?>','Kuerzungsbetraegesynchronisation','width=950,height=680,location=no,status=yes,resizable=yes');">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/snycronisation.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Kürzungsbeträge synchronisieren</span>
	</span>
	</a>
<? }?>
<br /><br />
<form method="post" action="#" name="form_lists" id="form_lists">
	<? $list1->PrintData(); ?>
	<br><br>
	<? $list3->PrintData(); ?>
	<br><br>
	<? $list2->PrintData(); ?>
</form>

<?
$CONTENT = ob_get_contents();
ob_end_clean();

include("template_1row.inc.php5"); // Content includen

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>