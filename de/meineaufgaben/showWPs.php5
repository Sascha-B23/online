<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, UM_GROUP_BASETYPE_AUSHILFE, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT);
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Menü und Username zeigen
include("page_top.inc.php5");

ob_start();
if( isset($_POST["aj"]) )$_GET["aj"]=$_POST["aj"];
if( isset($_POST["ws"]) )$_GET["ws"]=$_POST["ws"];

if( !isset($_GET["aj"]) || $_GET["aj"]!=(int)$_GET["aj"] || !isset($_GET["ws"]) || $_GET["ws"]!=(int)$_GET["ws"]){
?>	<div class="errorText">
		<br /> Falsche Übergabeparamter<br /><br /><br />
	</div><?
	exit;
}

$abrechnungsjahr = new AbrechnungsJahr($db);
if( $abrechnungsjahr->Load((int)$_GET["aj"], $db)!==true ){
?>	<div class="errorText">
		<br /> Abrechnugsjahr konnte nicht geladen werden<br /><br /><br />
	</div><?
	exit;	
}

$widerspruch = null;
$widersprueche=$abrechnungsjahr->GetWidersprueche($db);
for( $a=0; $a<count($widersprueche); $a++){
	if( $widersprueche[$a]->GetPKey()==(int)$_GET["ws"] ){
		$widerspruch = $widersprueche[$a];
		break;
	}
}

if( $widerspruch==null ){
?>	<div class="errorText">
		<br /> Widerspruch konnte nicht gefunden werden<br /><br /><br />
	</div><?
	exit;	
}

$list1 = new NCASList( new WiderspruchspunktListData($db, $widerspruch) );
?>
<form id="FM_FORM" name="FM_FORM" method="post">
	<br />
	<input type="hidden" name="aj" id="aj" value="<?=(int)$_GET["aj"];?>" />
	<table>
		<tr>
			<td>Widerspruch: </td>
			<td>
				<select name="ws" onChange="document.forms.FM_FORM.submit();">
				<?	for( $a=0; $a<count($widersprueche); $a++){ ?>
						<option value="<?=$widersprueche[$a]->GetPKey();?>" <?if($widersprueche[$a]->GetPKey()==$_GET["ws"])echo "selected";?>>Widerspruch <?=$a+1;?></option>
				<?	}?>
				</select>
			</td>
		</tr>
	</table>

	<script type="text/javascript">
		function CreateNewPos(){
			var newWin=window.open('editWiderspruchspunkt.php5?<?=SID;?>&widerspruchID=<?=$widerspruch->GetPKey();?>','_createEditWiderspruchspunkt','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=900,width=1048');
			newWin.focus();
		}
		function EditPos(posID){
			var newWin=window.open('editWiderspruchspunkt.php5?<?=SID;?>&editElement='+posID,'_createEditWiderspruchspunkt','resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=900,width=1048');
			newWin.focus();
		}
	</script>

	<? $list1->PrintData(); ?>
	<br><br>
	<a href="javascript:CreateNewPos();" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
	<span style="position:relative; left:30px;">
		<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/teilabrechnung.png" border="0" alt="" /> <span style="position:relative; top:-6px;">Widerspruchspunkt hinzufügen</span>
	</span>
	</a>
</form>

<?
$CONTENT = ob_get_contents();
ob_end_clean();

include("template_1row.inc.php5"); // Content includen
include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>