<?	
/**
 * This template is included in the following packages
 * - TerminschieneReport.lib.php5 
 * - TerminschieneTabDataEntry.lib.php5
 */
global $DOMAIN_HTTP_ROOT;
$customerUser = ($_SESSION["currentUser"]->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP ? true : false);

if (!isset($process) || !is_object($process) || !is_a($process, 'ProcessStatus'))
{
	if( isset($yearToShow) && is_numeric($yearToShow) && (int)$yearToShow==$yearToShow )
	{
		$currentAbrechnungsJahr=new AbrechnungsJahr($this->db);
		if ($currentAbrechnungsJahr->Load((int)$yearToShow, $this->db)!==true) $currentAbrechnungsJahr=null;
	}
	// Abrechnungsjahr geladen und aktueller Benutzer berechtigt?
	$process=null;
	if ($currentAbrechnungsJahr!=null && $currentAbrechnungsJahr->HasUserAccess($_SESSION["currentUser"], $this->db))
	{ 
		$process = $currentAbrechnungsJahr->GetProcessStatus($this->db);
		if (isset($_POST["showArchievedStatus"]) && $_POST["showArchievedStatus"]>=0)
		{
			if ($process->GetArchiveStatus()!=$_POST["showArchievedStatus"])
			{
				$process = null;
			}
		}
	}
}
// Prozess geladen?
if ($process!=null)
{
	$showProcessColumn = is_a($process, 'ProcessStatusGroup') ? true : false;
	
	// delete file from this process
	if (isset($_POST['delete_file_id']) && (int)$_POST['delete_file_id']>0)
	{
		$fileIdToDelete = (int)$_POST['delete_file_id'];
		$process->DeleteProcessFile($this->db, $fileIdToDelete);
	}
	// Get all files from this process
	$files = $process->GetAllProcessFiles($this->db);
	// Sort files
	if (!isset($_REQUEST["sortTimeLineDir"])) $_REQUEST["sortTimeLineDir"] = 1;
	$sortTimeLineRow = (int)$_REQUEST["sortTimeLineRow"];
	$sortTimeLineDir = (int)$_REQUEST["sortTimeLineDir"];
	// Alle übergebenen Files  nach Datum sortieren
	function UsortCallback_SortFilesByCreateDate($a, $b)
	{
		if( (int)$a["date"] == (int)$b["date"] )return 0;
		return ((int)$a["date"] < (int)$b["date"]) ? -1 : 1;
	}
	// Alle übergebenen Files nach Typ sortieren
	function UsortCallback_SortFilesByType($a, $b)
	{
		if( $a["type1"] == $b["type1"] )return 0;
		return ($a["type1"] < $b["type1"]) ? -1 : 1;
	}
	// Alle übergebenen Files nach Sichtbar für sortieren
	function UsortCallback_SortFilesByVisibleFor($a, $b)
	{
		if( $a["sichtbar"] == $b["sichtbar"] )return 0;
		return ($a["sichtbar"] < $b["sichtbar"]) ? -1 : 1;
	}
	// Alle übergebenen Files nach Dateityp sortieren
	function UsortCallback_SortFilesByFileType($a, $b)
	{
		if( $a["fileType"] == $b["fileType"] )return 0;
		return ($a["fileType"] < $b["fileType"]) ? -1 : 1;
	}
	// Alle übergebenen Files nach Status sortieren
	function UsortCallback_SortFilesByStatus($a, $b)
	{
		if( $a["fileStatus"] == $b["fileStatus"] )return 0;
		return ($a["fileStatus"] < $b["fileStatus"]) ? -1 : 1;
	}
	// Alle übergebenen Files nach Prozess 
	function UsortCallback_SortFilesByProzess($a, $b)
	{
		if( $a["prozess"] == $b["prozess"] )return 0;
		return ($a["prozess"] < $b["prozess"]) ? -1 : 1;
	}
	// Alle übergebenen Files nach Dateiname sortieren
	function UsortCallback_SortFilesByFilename($a, $b)
	{
		if( $a["name"] == $b["name"] )return 0;
		return ($a["name"] < $b["name"]) ? -1 : 1;
	}
	// Alle übergebenen Files nach Beschreibung für sortieren
	function UsortCallback_SortFilesByDescription($a, $b)
	{
		if( $a["description"] == $b["description"] )return 0;
		return ($a["description"] < $b["description"]) ? -1 : 1;
	}
	switch($sortTimeLineRow)
	{
		case 0:
			// Datum
			usort($files, "UsortCallback_SortFilesByCreateDate");
			break;
		case 1:
			// Typ
			usort($files, "UsortCallback_SortFilesByType");
			break;
		case 2:
			// Sichtbar für
			usort($files, "UsortCallback_SortFilesByVisibleFor");
			break;
		case 3:
			// Art
			usort($files, "UsortCallback_SortFilesByFileType");
			break;
		case 4:
			// Status
			usort($files, "UsortCallback_SortFilesByStatus");
			break;
		case 5:
			// Prozess
			usort($files, "UsortCallback_SortFilesByProzess");
			break;
		case 6:
			// Dateiname
			usort($files, "UsortCallback_SortFilesByFilename");
			break;
		case 7:
			// Dateiname
			usort($files, "UsortCallback_SortFilesByDescription");
			break;
	}
	// Reihgenfolge umkehren
	if( $sortTimeLineDir==1 )
	{
		$files=array_reverse($files);
	}

	function print_sort_header($rowName, $rowIndex, $sortTimeLineRow, $sortTimeLineDir)
	{
		global $DOMAIN_HTTP_ROOT;
		?><a href="javascript:sort_time_line(<?=$rowIndex;?>, <?=($sortTimeLineRow==$rowIndex ? ($sortTimeLineDir==0 ? "1" : "0" ) : "0" );?>)"><strong><?=$rowName;?> <? if($sortTimeLineRow==$rowIndex){?><img src="<?=$DOMAIN_HTTP_ROOT;?>pics/gui/order_<?=($sortTimeLineDir==1 ? "asc" : "desc");?>.png" /><?}?></strong></a> <?

	}

	// Files ausgeben
	?>
	<input type="hidden" name="sortTimeLineRow" id="sortTimeLineRow" value="<?=$sortTimeLineRow;?>" />
	<input type="hidden" name="sortTimeLineDir" id="sortTimeLineDir" value="<?=$sortTimeLineDir;?>" />
	<input type="hidden" name="delete_file_id" id="delete_file_id" value="" />
	<table border="0" cellpadding="2" cellspacing="0" style="width: 950px" >
		<tr>
			<td class="TAPMatrixHeader2" style="width:120px;" align="left" valign="top"><?print_sort_header("Datum", 0, $sortTimeLineRow, $sortTimeLineDir);?></td>
			<td class="TAPMatrixHeader2" style="width:40px;" align="left" valign="top"><?print_sort_header("Typ", 1, $sortTimeLineRow, $sortTimeLineDir);?></td>
			<? if( !$customerUser){ ?><td class="TAPMatrixHeader2" style="width:80px;" align="left" valign="top"><?print_sort_header("Sichtbar für", 2, $sortTimeLineRow, $sortTimeLineDir);?></td><?}?>
			<td class="TAPMatrixHeader2" style="width:100px;" align="left" valign="top"><?print_sort_header("Art", 3, $sortTimeLineRow, $sortTimeLineDir);?></td>
			<? if( !$customerUser){ ?><td class="TAPMatrixHeader2" style="width:150px;" align="left" valign="top"><?print_sort_header("Status", 4, $sortTimeLineRow, $sortTimeLineDir);?></td><?}?>
			<? if($showProcessColumn){?><td class="TAPMatrixHeader2" style="width:200px;" align="left" valign="top"><?print_sort_header("Prozess", 5, $sortTimeLineRow, $sortTimeLineDir);?></td><?}?>
			<td class="TAPMatrixHeader2" style="<?if (!$customerUser){?>width:150px;<?}?>" align="left" valign="top"><?print_sort_header("Kommentar", 7, $sortTimeLineRow, $sortTimeLineDir);?></td>
			<td class="TAPMatrixHeader2" style="width:<?if (!$customerUser){?>200px<?}else{?>100<?}?>;" align="left" valign="top">Optionen</td>
		</tr>
	<?	$current_location = $process->GetLocation();
		$current_company = $current_location!=null ? $current_location->GetCompany() : null;
		$current_group = $current_company!=null ? $current_company->GetGroup() : null;
		for( $b=0; $b<count($files); $b++)
		{
			$download_file_name = FileManager::GetDownloadFileName($files[$b]["type1"], $current_group, $current_location, $files[$b]["fileObject"], $process);
		?>
			<tr>
				<td class="TAPMatrixRow" align="left" valign="top"><?=date("d.m.Y H:i", $files[$b]["date"]);?> Uhr</td>
				<td class="TAPMatrixRow" align="left" valign="top"><?=$files[$b]["type1"];?></td>
				<? if( !$customerUser){ ?><td class="TAPMatrixRow" align="left" valign="top"><?=$files[$b]["sichtbar"];?></td><?}?>
				<td class="TAPMatrixRow" align="left" valign="top"><?=$files[$b]["fileType"];?></td>
				<? if( !$customerUser){ ?><td class="TAPMatrixRow" align="left" valign="top"><?=$files[$b]["fileStatus"];?></td><?}?>
				<? if($showProcessColumn){?><td class="TAPMatrixRow" align="left" valign="top"><?=$files[$b]["prozess"];?></td><?}?>
				<td class="TAPMatrixRow" align="left" valign="top"><?=$files[$b]["description"];?></td>
				<td class="TAPMatrixRow" align="left" valign="top">
					<a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($files[$b]["fileObject"], $download_file_name)?>&timestamp=<?=time();?>">[Anzeigen]</a>
				<?	if( !$customerUser){ ?>
						<a href="javascript:delete_file_entry(<?=$files[$b]["id"];?>);">[Löschen]</a>
						<a href="javascript:edit_file_entry('<?=$files[$b]["processStatusId"];?>', <?=$files[$b]["id"];?>);">[Bearbeiten]</a>
				<?	}?>
				</td>
			</tr>
	<?	}
		if( count($files)==0 ) {?>
			<tr><td colspan="6"><strong>Für dieses Abrechnungsjahr sind keine Dokumente hinterlegt</strong></td></tr>
	<? 	}?>

	</table>
	<script type="text/javascript">
		function sort_time_line(row, direction)
		{
			$('sortTimeLineRow').value= row;
			$('sortTimeLineDir').value= direction;
			$('FM_FORM').submit();
		}

		function delete_file_entry(file_id)
		{
			if (confirm('Die Datei wird unwiderruflich gelöscht!\n\nMöchten sie trotzdem fortfahren?'))
			{
				$('delete_file_id').value= file_id;
				$('FM_FORM').submit();
			}
		}
		function edit_file_entry(process_id, file_id)
		{
			var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/editFileEntry.php5?<?=SID;?>&processId='+process_id+'&editElement='+file_id, '_editFileEntry','resizable=1,status=1,location=0,menubar=0,scrollbars=0,toolbar=0,height=800,width=1024');
			newWin.focus();
		}
	</script>
	<?		
}
?>