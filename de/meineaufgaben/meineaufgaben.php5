<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_KUNDE;
require_once("../../phplib/session.inc.php5");

$HM = 1;
$UM = 1;
$MENU_SHOW = true; // Menü und Username zeigen
include("page_top.inc.php5");

// Automatisch die Aufgaben aktuallisieren
WorkflowManager::CheckAutoUpdateStatus($db);

// Sollen Aufgaben geplant werden?
if ($_SESSION["currentUser"]->GetGroupBasetype($db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
{
	if (isset($_GET["planElements"]) && trim($_GET["planElements"])!="")
	{
		$prozesses = Array();
		$list = explode("|", $_GET["planElements"]);
		foreach ($list as $prozessId)
		{
			$processObj = WorkflowManager::GetProcessStatusById($db, $prozessId);
			if ($processObj!=null)
			{
				$processObj->SetPlanned(!$processObj->IsPlanned());
				$processObj->Store($db);
			}
		}
	}
}
ob_start();

if (!isset($_SESSION['currentMyTasksTableType'])) $_SESSION['currentMyTasksTableType'] = 2;
if (isset($_GET['tableType']) && in_array((int)$_GET['tableType'], Array(1,2)) ) $_SESSION['currentMyTasksTableType'] = (int)$_GET['tableType'];

if ($_SESSION['currentMyTasksTableType']==1)
{
	if(isset($_GET["resetTable"]) && trim($_GET["resetTable"]) != "")
	{
		$dynamicTableManager->RemoveDynamicTable("TasksListData");
	}
	$dynamicTable = $dynamicTableManager->GetDynamicTableById("TasksListData");
	if($dynamicTable == null)
	{
		$dynamicTable = new TasksListData($db);
	}
}else{
	if(isset($_GET["resetTable"]) && trim($_GET["resetTable"]) != "")
	{
		$dynamicTableManager->RemoveDynamicTable("TasksListDataLight");
	}
	$dynamicTable = $dynamicTableManager->GetDynamicTableById("TasksListDataLight");
	if($dynamicTable == null)
	{
		$dynamicTable = new TasksListDataLight($db);
	}
}

$dynamicTable->StoreSettings();

$list1 = new KimList($dynamicTableManager, $dynamicTable);
?>
<script type="text/javascript">
	<!--
		function EditDate(dateID){
			var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/editPhoneDate.php5?<?=SID;?>&processId='+dateID, '_editPhoneDate', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
			//newWin.moveTo(width,height);
			newWin.focus();
		}
		
		function EditTaskState(taskId)
		{
			var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/editTaskState.php5?<?=SID;?>&editElement='+taskId, '_editTaskState', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
			//newWin.moveTo(width,height);
			newWin.focus();
		}
		
		function ShowReport(locationId)
		{
			var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/berichte/kundenstandorte.php5?<?=SID;?>&location='+locationId, '_report', 'resizable=1,status=1,location=1,menubar=0,scrollbars=1,toolbar=1,height=800,width=1050');
			//newWin.moveTo(width,height);
			newWin.focus();
		}
		
		function AssignTask(taskId)
		{
			var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/assignTask.php5?<?=SID;?>&processId='+taskId, '_assignTask', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
			//newWin.moveTo(width,height);
			newWin.focus();
		}
		
		function UploadFiles(process_id)
		{
			var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/uploadFiles.php5?<?=SID;?>&processId='+process_id, '_uploadFiles', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
			//newWin.moveTo(width,height);
			newWin.focus();
		}
		
		function EditUploadedFiles(process_id)
		{
			var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/editUploadedFiles.php5?<?=SID;?>&processId='+process_id, '_editUploadedFiles', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
			//newWin.moveTo(width,height);
			newWin.focus();
		}
		
	-->
</script>
<form method="post" action="#" name="form_lists" id="form_lists">
	<!-- NEUE GRUPPE ANLEGEN -->
	<br />
	<table width="100%" >
		<tr>
			<td width="50%">
				<?	if (UserManager::IsCurrentUserAllowedTo($db, UM_ACTION_CREATE_NEW_TASK)){ ?>
					<table border="0">
						<tr>
							<td width="20px">&#160;</td>

							<td valign="middle"><a href="process.php5"><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/newProcess.png" border="0" /></a></td>
							<td valign="middle"><a href="process.php5">Prüfung beauftragen</a></td>
							<td width="20px">&#160;</td>

						</tr>
					</table>
				<?	}
				if ($_SESSION["currentUser"]->GetGroupBasetype($db)>=UM_GROUP_BASETYPE_RSMITARBEITER)
				{
					?>
					<table border="0">
						<tr>
							<td width="20px">&#160;</td>
							<td valign="middle"><a href="<?=$SHARED_HTTP_ROOT?>templates/downloadiCalendar.php5?<?=SID;?>&<?=time();?>"><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/call_b.png" border="0" /></a></td>
							<td valign="middle"><a href="<?=$SHARED_HTTP_ROOT?>templates/downloadiCalendar.php5?<?=SID;?>&<?=time();?>">Telefontermine als iCalendar-Datei herunterladen</a></td>
							<td width="20px">&#160;</td>
						</tr>
					</table>
					<?
				}
				?>
			</td>
			<td width="50%" valign="top">
				<input type="radio" name="tableType" value="2" <?if($_SESSION['currentMyTasksTableType']==2) echo "checked";?> onclick="document.location='meineaufgaben.php5?<?=SID;?>&tableType=2';" /> Reduzierte Listenansicht<br />
				<input type="radio" name="tableType" value="1" <?if($_SESSION['currentMyTasksTableType']==1) echo "checked";?> onclick="document.location='meineaufgaben.php5?<?=SID;?>&tableType=1';" /> Klassische Listenansicht
			</td>
		</tr>
	</table>
	<? $list1->PrintData();?>
	<br />
</form>

<?
$CONTENT = ob_get_contents();
ob_end_clean();

include("template_1row.inc.php5"); // Content includen
include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>