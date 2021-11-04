<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../../phplib/classes/UserManager-1.0/UserManager.inc.php5");
$MIN_GROUP_BASETYPE_NEED=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT;
require_once("../../phplib/session.inc.php5");
$MENU_SHOW = true; // Menü und Username zeigen

$HM = 2;
$UM = 7;

include("page_top.inc.php5");

// Delete confirm process...
if ($_SESSION["currentUser"]->GetGroupBasetype($db) >= UM_GROUP_BASETYPE_ADMINISTRATOR)
{
	if (isset($_REQUEST['deleteSelected']) && $_REQUEST['deleteSelected']!='')
	{
		// check if delete action is allready confirmed
		$deleteConfirmed = false;
		if ($_REQUEST['doAction']=="deleteNow")
		{
			if ($_SESSION["currentUser"]->IsPWDCorrect($_POST['pwd']))
			{
				// delete action is confirmed
				$deleteConfirmed = true;
			}
			else
			{
				// wrong password
				$errors['pwd'] = 1;
			}
		}
		if (!$deleteConfirmed)
		{
			// action has to be confirmed
			$pkeys = Array();
			$ids = explode('|', $_REQUEST['deleteSelected']);
			for ($a=0; $a<count($ids);$a++)
			{
				$temp = explode('_', $ids[$a]);
				if (count($temp)==2 && $temp[0]==(int)$temp[0] && $temp[1]==(int)$temp[1])
				{
					$pkeys[$temp[0]][] = $temp[1];
				}
			}
			// Load objects...
			$objects = Array();
			for ($a=0; $a<count($pkeys[0]);$a++)
			{
				$object = new ProcessStatus($db);
				if ($object->Load($pkeys[0][$a], $db)===true)
				{
					$objects[] = $object;
				}
			}
			$objects2 = Array();
			for ($a=0; $a<count($pkeys[1]);$a++)
			{
				$object = new ProcessStatusGroup($db);
				if ($object->Load($pkeys[1][$a], $db)===true)
				{
					$objects2[] = $object;
				}
			}

			if (count($objects)>0 || count($objects2)>0)
			{
				ob_start();
				?>
				<table width="100%" border="0">
					<tr>
						<td style="width: 20px;">&#160;</td>
						<td>
							<form method="post" action="prozesse.php5?<?=SID;?>" name="form_lists" id="form_lists">
								<input type="hidden" name="deleteSelected" id="deleteSelected" value="<?=$_REQUEST['deleteSelected']?>" />
								<input type="hidden" name="doAction" id="doAction" value="deleteNow" />
								<br />
							<?	if(count($objects)>0){ ?>
									<strong>Bitte bestätigen Sie das Löschen der folgenden <?=count($objects);?> Prozesse:</strong><br />
								<?	for ($a=0; $a<count($objects);$a++)
									{
										echo "&#160;&#160;&#160;".($a+1).": ";
										echo ($objects[$a]->GetAbrechnungsJahr()==null ? "-" : $objects[$a]->GetAbrechnungsJahr()->GetJahr());
										// Namem von Standort und Laden bei Prozess-Workflows ermitteln
										if( $objects[$a]->GetStatusTyp()==WM_WORKFLOWSTATUS_TYPE_PROCESS ){
											$cShop=$objects[$a]->GetShop();
											if( $cShop!=null && is_a($cShop, "CShop") ){
												$cLocation=$cShop->GetLocation();
												if( $cLocation!=null && is_a($cLocation, "CLocation") ){
													echo ", ".$cLocation->GetName();
												}
												echo ", ".$cShop->GetName();
											}
										}
										echo ", ".$objects[$a]->GetCurrentStatusName($_SESSION["currentUser"], $db);
										echo " (".WorkflowManager::GetProcessStatusId($objects[$a]).")";
										echo "<br />";
									}?>
									<br />
									<strong>ACHTUNG: Es werden alle hinterlegten Daten und Dokumente (Teilabrechnungen, Teilabrechnungspositionen, Widersprüche etc.) der aufgelisteten Prozesse unwiderruflich gelöscht!</strong><br />
							<?	}?>
							<?	if(count($objects2)>0){ ?>
								<br />
								<strong>Bitte bestätigen Sie das Löschen der folgenden <?=count($objects2);?> Prozessgruppen:</strong><br />
								<?	for ($a=0; $a<count($objects2);$a++)
									{
										echo "&#160;&#160;&#160;".($a+1).": ";
										echo $objects2[$a]->GetName()." (".WorkflowManager::GetProcessStatusId($objects2[$a]).")<br />";
									}?>
									<br />
							<?	}?>
								<br />
								Bitte bestätigen Sie das Löschen der Prozesse durch Eingabe Ihres Passwortes:<br /><br />
								<table width="350px;" border="0">
									<tr>
										<td style="width:20px;">&#160;</td>
										<td style="width:80px;" valign="top">Login:</td>
										<td style="width:250px;"><?=$_SESSION["currentUser"]->GetEMail();?></td>
									</tr>
									<tr>
										<td>&#160;</td>
										<td valign="top">Passwort:</td>
										<td>
											<input type="password" name="pwd" />
											<? if ($errors['pwd']==1){?><div class='errorText'>Das eingegebene Passwort ist falsch</div><?}?>
										</td>
									</tr>
									<tr>
										<td>&#160;</td>
										<td colspan="2">
											<input type="submit" class="formButton2" value="Unwiderruflich löschen" />
											<input type="button" value="Abbrechen" onclick="document.location='prozesse.php5?<?=SID?>'" class="formButton2" />
										</td>
									</tr>
								</table>
								<br />
								<br />

								<br />

							</form>
						</td>
					</tr>
				</table>
				<?
				$CONTENT = ob_get_contents();
				ob_end_clean();
				include("template_1row.inc.php5"); // Content includen
				include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
				exit;
			}
		}
		else
		{
			// Delete operation is allready confirmed
			// --> delete operation will be handelt in function ProzessListData::DeleteEntries / ProzessGroupListData::DeleteEntries
			$_GET['deleteSelected'] = $_REQUEST['deleteSelected'];
		}
	}
}

ob_start();

$list1 = new NCASList( new ProzessListData($db, $lm, $workflowManager) );
$list2 = new NCASList( new ProzessGroupListData($db, $lm, $workflowManager) );
?>
<!-- NEUE GRUPPE ANLEGEN -->
<br />
<a href="processgroup_edit.php5" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
<span style="position:relative; left:30px;">
	<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/newEntry.png" border="0" alt="" /> <span style="position:relative; top:-6px; font-weight: bolder;">Neues Paket anlegen</span>
</span>
</a>
&#160;&#160;&#160;&#160;&#160;&#160;
<br /><br />
<form method="post" action="#" name="form_lists" id="form_lists">
	<? $list1->PrintData(); ?>
	<? $list2->PrintData(); ?>
</form>
<br /><br />
<?
$CONTENT = ob_get_contents();
ob_end_clean();

include("template_1row.inc.php5"); // Content includen
include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>