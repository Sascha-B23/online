<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_ADMINISTRATOR;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen

$HM = 2;
$UM = 3;

include("page_top.inc.php5");

ob_start();

// Kostenart löschen
if( is_numeric($_GET["delete"]) && ((int)$_GET["delete"])==$_GET["delete"] ){
	$kostenart = new RSKostenart($db);
	if( $kostenart->Load((int)$_GET["delete"], $db)===true ){
		$kostenart->DeleteMe($db);
	}
}

$list1 = new NCASList( new RSKostenartenListManager($db) );

?>
<!-- NEUE GRUPPE ANLEGEN -->
<br />
<a href="kostenarten_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/kostenart.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neue Kostenart anlegen</span>
	</span>
</a>
<br /><br />
<form method="post" action="#" name="form_lists" id="form_lists">
	<? $list1->PrintData(); ?>
</form>

<?
$CONTENT = ob_get_contents();
ob_end_clean();

include("template_1row.inc.php5"); // Content includen

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>