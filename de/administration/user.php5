<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT;
require_once("../../phplib/session.inc.php5");

$HM = 2;
$UM = 1;

$MENU_SHOW = true; // Menü und Username zeigen
include("page_top.inc.php5");

ob_start();

// Gruppe löschen
if ($_SESSION["currentUser"]->GetGroupBasetype($db) >= UM_GROUP_BASETYPE_ADMINISTRATOR)
{
	if( is_numeric($_GET["deleteGroup"]) && ((int)$_GET["deleteGroup"])==$_GET["deleteGroup"] ){
		$group = new UserGroup($db);
		if( $group->Load((int)$_GET["deleteGroup"], $db)===true){
			// Es dürfen nur Gruppen gelöscht werden, auf welche der angemeldete Benutzer Zugriff hat
			if( $group->GetBaseType()<=$_SESSION["currentUser"]->GetGroupBasetype($db) ){
				$group->DeleteMe($db);
			}else{
				// Zugriff verweigert
			}
		}
	}
// User löschen
	if( is_numeric($_GET["deleteUser"]) && ((int)$_GET["deleteUser"])==$_GET["deleteUser"] ){
		$user = new User($db);
		if( $user->Load((int)$_GET["deleteUser"], $db)===true){
			// Es dürfen nur Benutzer gelöscht werden, auf welche der angemeldete Benutzer Zugriff hat
			if( $user->GetGroupBasetype($db)<=$_SESSION["currentUser"]->GetGroupBasetype($db) ){
				$user->DeleteMe($db);
			}else{
				// Zugriff verweigert
			}
		}
	}
}


$list1 = new NCASList( new GroupListManager($db) );
$list2 = new NCASList( new UserListManager($db) );

?>
<!-- NEUE GRUPPE ANLEGEN -->
<br />
<a href="group_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
<span style="position:relative; left:30px;">
	<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/userGroup.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neue Gruppe anlegen</span>
</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<a href="user_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/user.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neuen Benutzer anlegen</span>
	</span>
</a>
<br /><br />
<form method="post" action="#" name="form_lists" id="form_lists">
	<? $list1->PrintData();?>
	<br />
	<? $list2->PrintData();?>
</form>
	
<?
$CONTENT = ob_get_contents();
ob_end_clean();

include("template_1row.inc.php5"); // Content includen

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>