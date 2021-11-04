<? 
/*	Erwartete Übergabe in Array-Variable $rsSchreibenList  mit File-Objects */
global $SHARED_HTTP_ROOT, $DOMAIN_HTTP_ROOT; 
if (!isset($table_width)) $table_width = 922;
// Alle übergebenen FMS-Files  nach Datum sortieren
function UsortCallback_SortRSFilesByCreateDate($a, $b)
{
    if ((int)$a->GetCreationTime() == (int)$b->GetCreationTime()) return 0;
    return ((int)$a->GetCreationTime() < (int)$b->GetCreationTime()) ? -1 : 1;
}
usort($rsSchreibenList, "UsortCallback_SortRSFilesByCreateDate");
?>
<!---------------->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="center"> <br />
			<table width="<?=$table_width;?>" border="0" cellpadding="0" cellspacing="10" bgcolor="#ffffff">
				<tr>
					<td>
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td width="100px" align="left" valign="top" class="smallText"><strong>Datum</strong></td>
								<td width="210px" align="left" valign="top" class="smallText"><strong>Status</strong></td>
								<td width="60px" align="left" valign="top" class="smallText"><strong>Art</strong></td>
								<td width="80px" align="left" valign="top" class="smallText"><strong>Sichtbar für</strong></td>
								<td align="left" valign="top" class="smallText"><strong>Dateiname</strong></td>
							</tr>
						<?	for ($a=0; $a<count($rsSchreibenList); $a++){ ?>
								<tr>
									<td align="left" valign="top" class="smallText"><?=date("d.m.Y H:i", $rsSchreibenList[$a]->GetCreationTime());?> Uhr</td>
									<td align="left" valign="top" class="smallText"><?=WorkflowManager::GetStatusName($this->db, $_SESSION["currentUser"], FileManager::GetRSFileStatus($rsSchreibenList[$a]->GetFileSemanticSpecificString()));?></td>						
									<td align="left" valign="top" class="smallText"><?=FileManager::GetRSFileType($rsSchreibenList[$a]->GetFileSemanticSpecificString());?></td>
									<td align="left" valign="top" class="smallText"><?=($rsSchreibenList[$a]->GetIntern() ? "SFM Mitarbeiter" : "Alle");?></td>
									<td align="left" valign="top" class="smallText"><a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($rsSchreibenList[$a])?>&timestamp=<?=time();?>"><?=$rsSchreibenList[$a]->GetFileName();?></a></td>
								</tr>
						<?	}
							if (count($rsSchreibenList)==0){?>
								<tr>
									<td colspan="5" height="20" align="left" valign="middle" class="smallText">Keine SFM-Schreiben vorhanden</td>
								</tr>
						<?	}?>
						</table>
					</td>
				</tr>
			</table>
			<br />
			<br />
		</td>
	</tr>
</table>
<!---------------->