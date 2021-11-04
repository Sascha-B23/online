<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_ADMINISTRATOR;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen

$HM = 2;
$UM = 8;

include("page_top.inc.php5");
ob_start();

$list1 = new NCASList( new CustomerCountryListData($db) );
$list2 = new NCASList( new CustomerCurrencyListData($db) );
$list3 = new NCASList( new CustomerLanguageListData($db) );

?>
<!-- NEUE GRUPPE ANLEGEN -->
<br />
<a href="country_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/world_icon.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neues Land anlegen</span>
	</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="currency_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/kostenart.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neue Währung anlegen</span>
	</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="language_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/language.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neue Sprache anlegen</span>
	</span>
</a>
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