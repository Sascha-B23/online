<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_ADMINISTRATOR;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen
$HM = 2;
$UM = 10;

include("page_top.inc.php5");
ob_start();
$list1 = new NCASList(new StandardTextListManager($db, StandardText::ST_TYPE_TEMPLATE, "Textvorlagen"));
$list2 = new NCASList(new StandardTextListManager($db, StandardText::ST_TYPE_TRANSLATION, "Übersetzungen"));
$list3 = new NCASList(new StandardTextListManager($db, StandardText::ST_TYPE_SCHEDULECOMMENT, "Status-Kommentare"));
?>
<!-- NEUE GRUPPE ANLEGEN -->
<br />
<a href="standardtext_edit.php5?type=<?=StandardText::ST_TYPE_TEMPLATE?>" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/textmodule.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neue Textvorlage anlegen</span>
	</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="standardtext_edit.php5?type=<?=StandardText::ST_TYPE_TRANSLATION?>" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/textmodule.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neue Übersetzung anlegen</span>
	</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="standardtext_edit.php5?type=<?=StandardText::ST_TYPE_SCHEDULECOMMENT?>" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
<span style="position:relative; left:30px;">
    <img src="<?=$SHARED_HTTP_ROOT?>pics/gui/textmodule.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neues Status-Kommentar anlegen</span>
</span>
</a>
<br /><br />
<form method="post" action="#" name="form_lists" id="form_lists">
	<? $list1->PrintData();?>
	<br />
	<? $list2->PrintData();?>
    <br />
    <? $list3->PrintData();?>
</form>
<?
$CONTENT = ob_get_contents();
ob_end_clean();
include("template_1row.inc.php5"); // Content includen
include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>