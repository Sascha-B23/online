<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_KUNDE;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = false; // Kein Menü und Username zeigen
include("page_top.inc.php5");

// GET-Varibale holen
if (!isset($_REQUEST["tableId"])) $_REQUEST["tableId"] = $_SESSION['current_configurate_table_id'];
$dynamicTable = $dynamicTableManager->GetDynamicTableById($_REQUEST["tableId"]);
if ($dynamicTable==null)
{
	echo "Zugriff verweigert";
	exit;
}
$_SESSION['current_configurate_table_id'] = $_REQUEST["tableId"];
?>
<script type="text/javascript">
	<!--
		function ReturnToOpener()
		{
			if (parent!=window && typeof(parent.MOOTOOLS_LIGHTBOX)!="undefined")
			{
				parent.dynamic_table_<?=$dynamicTable->GetId();?>.RequestData({type:DynamicTableRequestTypes.TYPE_INITIAL});
				parent.MOOTOOLS_LIGHTBOX.close();
				
			}
		}
	-->
</script>
<?

// Objekt initialisieren
$form=new OneColumnForm("javascript:ReturnToOpener();", new DynamicTableConfigFormData($_POST, $dynamicTable, $db) );
$form->SetTableWidth(930);
$form->PrintData();

include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>