<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP;
$EXCLUDE_GROUP_BASETYPES = Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT);
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Menü und Username zeigen
include("page_top.inc.php5");

ob_start();
if( isset($_POST["aj"]) )$_GET["aj"]=$_POST["aj"];
if( isset($_POST["ta"]) )$_GET["ta"]=$_POST["ta"];

if( !isset($_GET["aj"]) || $_GET["aj"]!=(int)$_GET["aj"] || !isset($_GET["ta"]) || $_GET["ta"]!=(int)$_GET["ta"]){
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

$teilabrechnung = null;
$tas=$abrechnungsjahr->GetTeilabrechnungen($db);
for( $a=0; $a<count($tas); $a++){
	if( $tas[$a]->GetPKey()==(int)$_GET["ta"] ){
		$teilabrechnung = $tas[$a];
		break;
	}
}

if( $teilabrechnung==null ){
?>	<div class="errorText">
		<br /> Teilabrechnung konnte nicht gefunden werden<br /><br /><br />
	</div><?
	exit;	
}

$list1 = new NCASList( new TeilabrechnungspositionReadOnlyListData($db, $teilabrechnung) );
?>
<form method="post" action="#" name="form_lists" id="form_lists">
	<br />
	<input type="hidden" name="aj" id="aj" value="<?=(int)$_GET["aj"];?>" />
	<table>
		<tr>
			<td>Teilabrechnung: </td>
			<td>
				<select name="ta" onChange="document.forms.form_lists.submit();">
				<?	for( $a=0; $a<count($tas); $a++){ ?>
						<option value="<?=$tas[$a]->GetPKey();?>" <?if($tas[$a]->GetPKey()==$_GET["ta"])echo "selected";?>><?=$tas[$a]->GetBezeichnung();?></option>
				<?	}?>
				</select>
			</td>
		</tr>
	</table>
	<? $list1->PrintData(); ?>
</form>
<?
$CONTENT = ob_get_contents();
ob_end_clean();

include("template_1row.inc.php5"); // Content includen
include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>