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


// GET-Varibale holen
if (!isset($_GET["processId"]))
{
	// Fehler beim Laden des Prozessstatus
	$em->ShowError(pathinfo(__FILE__, PATHINFO_BASENAME), "Zugriff verweigert");
	exit;
}

$proccess = WorkflowManager::GetProcessStatusById($db, $_GET["processId"]);
if ($proccess===null)
{
	// Fehler beim Laden des Prozessstatus
	$em->ShowError(pathinfo(__FILE__, PATHINFO_BASENAME), "Prozessstatus konnte nicht geladen werden");
	exit;
}


$comment = new Comment($db);
$comments=$proccess->GetComments($db, $_SESSION["currentUser"]);
$comments_helper=$comments;
$comments=array();
//print_r($_SESSION["currentUser"]->GetGroupBasetype($db));
for($i=0; $i<count($comments_helper); $i++){
	if( $_SESSION["currentUser"]->GetGroupBasetype($db) >= UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP && $comments_helper[$i]->GetIntern() == true ){
		$comments[] = $comments_helper[$i];
	}
	if( $comments_helper[$i]->GetIntern() == false ){
		$comments[] = $comments_helper[$i];
	}
}

$files = array();
$filedeleted=false;

if(isset($_POST["del_newfile"]) && $_POST["del_newfile"]!="false" && $_POST["del_newfile"]!=""){
	if( is_object( $_SESSION["newfiles"][(int)$_POST["del_newfile"]] ) ){
		$_SESSION["newfiles"][(int)$_POST["del_newfile"]]->DeleteMe($db);
	}
	$_SESSION["newfiles"][(int)$_POST["del_newfile"]] = "";
	$_POST["del_newfile"]="";
	$filedeleted=true;
}

if(isset($_POST["del_editfile"]) && $_POST["del_editfile"]!="false" && $_POST["del_editfile"]!=""){
	$edit_comment = $comments[(int)$_POST["edit_id"]];
	$del_file = $edit_comment->GetFiles($db);
	$del_file = $del_file[(int)$_POST["del_editfile"]];
	$edit_comment->RemoveFile($db,$del_file);
	$_POST["del_editfile"]="";
	$filedeleted=true;
}

if( !empty($_POST) ){
	if( $_POST["edit"] != true){
		if( $_POST["store"] == "false" ){
			$textarea = $_POST["comment2"];
			$visiblefor = $_POST["comment"];
			
			if( $_FILES["uploadfile"] != "" ){
				if( $_FILES["uploadfile"]["name"] != "" ){
					// File uploaden und in Datenbank speichern
					$pathInfo = pathinfo($_FILES["uploadfile"]["name"]);
					$pathInfo["extension"] = trim(pathinfo($pathInfo["basename"], PATHINFO_EXTENSION));
					
					$file = $fileManager->CreateFromFile($db, $_FILES["uploadfile"]["tmp_name"], FM_FILE_SEMANTIC_TEMP, $_FILES["uploadfile"]["name"], $pathInfo["extension"]);
					
					if(!is_a($file, "File")){
						// Fehler beim Anlegen der Datei
						switch($file){
							case -1: $error_file = "Quelldatei nicht gefunden"; break;
							case -2: $error_file = "Übergebene FileSemantic ist ungültig"; break;
							case -3: $error_file = "Unbekannter Filetype / Dateiendung"; break;
							case -4: $error_file = "Filetype ist für die übergebene FileSemantic nicht zugelassen"; break;
							case -5: $error_file = "Quelldatei konnte nicht kopiert werden"; break;
							case -6: $error_file = "Eintrag konnte nicht in Datenbank gespeichert werden"; break;
							case -7: $error_file = "Dateigröße überschreitet den maximal erlaubten Wert"; break;
							case -8: $error_file = "Die angegebene Datei ist leer (Dateigröße: 0 Byte)"; break;
							default: $error_file = "Es ist ein allgemeiner Fehler beim Hochladen und Speichern aufgetreten. Fehlercode: ".(int)$file; break;
						}
					}else{
						// File in Session speichern
						if( !isset($_SESSION["newfiles"]) ){
							$_SESSION["newfiles"] = array();
							$_SESSION["newfiles"][] = $file;
						}else{
							$_SESSION["newfiles"][] = $file;
						}
					}
					// Files als Array in Session speichern
				}else{
					// ERROR: Bitte erst Datei auswählen
					if( $filedeleted != true ){
						$error_file = "Bitte wählen Sie eine Datei aus.";
					}
				}
			}else{
				// ERROR: Bitte erst Datei auswählen
				$error_file = "Bitte wählen Sie eine Datei aus.";
			}
			$_POST["edit"]="";
			$_GET["edit"]="";
		}else{
			if( $_POST["comment2"] == "" || $_POST["comment"] == "0" ){
				$textarea = $_POST["comment2"];
				$visiblefor = $_POST["comment"];
				if( $_POST["comment2"] == "" ){
					$error_text = "Bitte geben Sie ein Kommentar in das Textfeld ein.";
				}
				if( $_POST["comment"] == "0" ){
					$error_user = "Bitte w&auml;hlen Sie, f&uuml;r wen Ihr Kommentar sichtbar sein soll.";
				}
			}else{
				// In Datenbank schreiben.
				$comment->SetIntern(($_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE || $_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT || $_POST["comment"] == "1") ? true : false);
				$comment->SetTime( time() );
				$comment->SetComment( $_POST["comment2"] );
				$comment->SetUser( $_SESSION["currentUser"] );
				$comment->Store($db);
				// Mit Prozess verknüpfen
				$proccess->AddComment($db, $comment);
				// Files abfragen und dann von FM_FILE_SEMANTIC_TEMP auf FM_FILE_SEMANTIC_COMMENT umbiegen und hinzufügen
				for($i=0; $i<count($_SESSION["newfiles"]); $i++){
					if( $_SESSION["newfiles"][$i] == "" ){ continue; }
					$_SESSION["newfiles"][$i]->SetFileSemantic(FM_FILE_SEMANTIC_COMMENT);
					$comment->AddFile($db, $_SESSION["newfiles"][$i]);
				}
				// FileArray leeren
				$_SESSION["newfiles"] = array();			
				$comments=$proccess->GetComments($db, $_SESSION["currentUser"]);
				$_POST["edit"]="";
				$_GET["edit"]="";
			}
		}
	}else{
		if( $_POST["edit_id"] != ""){
			$edit_comment = $comments[(int)$_POST["edit_id"]];
			
			if($_POST["store_edit"] == "false"){
				$edit_comment->SetIntern(($_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE || $_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT || $_POST["comment4"] == "1") ? true : false);
				$edit_comment->SetComment( $_POST["comment3"] );
				// Fileupload
				if( $_FILES["addfile"] != "" ){
					if( $_FILES["addfile"]["name"] != "" ){
						// File uploaden und in Datenbank speichern
						$pathInfo = pathinfo($_FILES["addfile"]["name"]);
						$pathInfo["extension"] = trim(pathinfo($pathInfo["basename"], PATHINFO_EXTENSION));
						
						$file = $fileManager->CreateFromFile($db, $_FILES["addfile"]["tmp_name"], FM_FILE_SEMANTIC_TEMP, $_FILES["addfile"]["name"], $pathInfo["extension"]);
						
						if(!is_a($file, "File")){
							// Fehler beim Anlegen der Datei
							switch($file){
								case -1: $error_file = "Quelldatei nicht gefunden"; break;
								case -2: $error_file = "Übergebene FileSemantic ist ungültig"; break;
								case -3: $error_file = "Unbekannter Filetype / Dateiendung"; break;
								case -4: $error_file = "Filetype ist für die übergebene FileSemantic nicht zugelassen"; break;
								case -5: $error_file = "Quelldatei konnte nicht kopiert werden"; break;
								case -6: $error_file = "Eintrag konnte nicht in Datenbank gespeichert werden"; break;
								case -7: $error_file = "Dateigröße überschreitet den maximal erlaubten Wert"; break;
								case -8: $error_file = "Die angegebene Datei ist leer (Dateigröße: 0 Byte)"; break;
								default: $error_file = "Es ist ein allgemeiner Fehler beim Hochladen und Speichern aufgetreten. Fehlercode: ".(int)$file; break;
							}
						}else{
							//Comment Storen
							$edit_comment->AddFile($db, $file);
						}
					}else{
						// ERROR: Bitte erst Datei auswählen
						if($filedeleted!=true){
							$error_file_edit = "Bitte wählen Sie eine Datei aus.";
						}
					}
				}
			}else{
				if($_POST["comment3"] == ""){
					$error_text_edit = "Bitte geben Sie ein Kommentar in das Textfeld ein.";
				}else{
					$edit_comment->SetIntern(($_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE || $_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT || $_POST["comment4"] == "1") ? true : false);
					$edit_comment->SetComment( $_POST["comment3"] );
					$edit_comment->SetUser( $_SESSION["currentUser"] );
					$edit_comment->Store($db);
					$comments=$proccess->GetComments($db, $_SESSION["currentUser"]);
					$_GET["edit"] = "";
				}
			}	
		}
	}
}

for($i=0; $i<count($_SESSION["newfiles"]); $i++){
	if(!is_object($_SESSION["newfiles"][$i])){
		$files[] = "";
	}else{
		$files[] = array("name" => $_SESSION["newfiles"][$i]->GetFileName(), "type" => strtolower($FM_FILEEXTENSION_TO_FILETYPE [$_SESSION["newfiles"][$i]->GetFileType()][0]));
	}
}
?>
<pre>
<?
// print_r($comments);
// print_r($_POST);
// print_r($_FILES);
// print_r($_SESSION);
?>
</pre>

<div style="width:100%; overflow:scroll-y; margin-top:15px" id="div_body_main_content">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="right" valign="top"><a href="javascript:window.close();" style="position:relative; top:11px">Fenster schlie&szlig;en</a></td>
            <td width="35" align="right" valign="top">&nbsp;</td>
        </tr>
    </table>
    <br />	
	<form enctype="multipart/form-data" method="post" name="comment_form">
		<input type="hidden" name="del_newfile" id="del_newfile" value="false" />
		<table border="0" bgcolor="#ffffff" cellpadding="0" cellspacing="0" width="980" style="margin-left:22px;">
			<tr>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_left.png); background-repeat: no-repeat; height:37px; width:32px;">&#160;</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_center.png); background-repeat: repeat-x; height:37px;">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td align="left" valign="top">
								<img src="../../pics/gui/comments.png" width="25" height="25" style="position:relative; top:4px"/>
								<span style="position:relative; top:-3px">
									<strong>Kommentar schreiben</strong>
								</span>
							</td>
							<td width="20" align="right" valign="top">&nbsp;</td>
						</tr>
					</table>
				</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_right.png); background-repeat: no-repeat; height:37px; width:17px;">&#160;</td>
			</tr>
			<tr>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_left.png); background-repeat: repeat-y; width:32px;">&#160;</td>
				<td style="vertical-align:top; text-align:left;"> 
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td><br />
								<table border="0" cellspacing="0" cellpadding="0" width="100%">
									<tr>
										<td width="400" rowspan="2" valign="top">
									  <textarea name="comment2" style="height:120px; width:400px;"><?=$textarea?></textarea>
									  <span class="errorText"><?=$error_text?></span></td>
									 
									  <td width="50" rowspan="2">&nbsp;</td>
										<td align="left" valign="top">
											<strong>Dateianhänge zu Ihrem Kommentar:</strong>
											<br />
											<?if(count($files) != 0){?>
												<table width="100%" border="0" cellspacing="0" cellpadding="0">
													<?for($i=0; $i<count($files); $i++){?>
													<?if($files[$i] == ""){ continue; }?>
														<tr>
															<td height="23" style="border-bottom: 1px solid #f0f0f0;">
																<a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($_SESSION["newfiles"][$i])?>&timestamp=<?=time();?>">
																	<img src="../../pics/gui/file-icon_<?=$files[$i]["type"]?>.png" alt="" width="16" height="16" style="position:relative; top:4px" border="0"/> 
																	<?=$_SESSION["newfiles"][$i]->GetFileName();?>
																</a>
															</td>
															<td width="16" style="border-bottom: 1px solid #f0f0f0;">
																<?$link = $SHARED_HTTP_ROOT."de/meineaufgaben/comments.php5?".SID."&processId=".$_GET["processId"]."&page=".$_GET["page"]."&del_newfile=".$i;?>
																<a href="#" onclick="document.forms.comment_form.del_newfile.value='<?=$i?>'; document.forms.comment_form.submit();">
																	<img src="../../pics/gui/deleteAttachment.png" alt="Dateianhang entfernen" title="Dateianhang entfernen" width="16" height="15" border="0"/>
																</a>
															</td>
														</tr>
													<?}?>
												</table>
											<?}?>
										<br />
										<strong>Datei:</strong>&nbsp;
										<input type="file" id="uploadfile" name="uploadfile" class="fileForm"/>
										<input type="button" value="Upload" id="btn_" name="btn_" class="formButton" style="position:relative; top:-1px" onClick="javascript:/*document.forms.comment_form.forwardToListView.value=false;*/ document.forms.comment_form.submit();">
										<br />
										<span class="errorText"><?=$error_file?></span>
										<br /><br />
											<? if($_SESSION["currentUser"]->GetGroupBasetype($db)!=UM_GROUP_BASETYPE_AUSHILFE && $_SESSION["currentUser"]->GetGroupBasetype($db)!=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT){ ?>
												<strong>Kommentar sichtbar f&uuml;r:</strong>&nbsp;
												<select name="comment" id="comment2" style="position:relative; top:2px">
													<option value="0" <?if($visiblefor==0){?>selected<?}?>>Bitte wählen...</option>
													<option value="1" <?if($visiblefor==1){?>selected<?}?>>SFM</option>
													<option value="2" <?if($visiblefor==2){?>selected<?}?>>Alle Benutzer</option>
												</select>
												<br />

												<span class="errorText"><?=$error_user?></span> <br />
												<br />
											<?}?>
									  </td>
										<td align="left" valign="top">&nbsp;</td>
									</tr>
									<tr>
										<td align="right" valign="bottom">
											<input type="hidden" name="store" id="store" value="false" />
											<img src="../../pics/gui/submitComment.png" alt="" width="25" height="25" style="position:relative; top:7px"/>
											<a href="#" onclick="document.forms.comment_form.store.value='true'; document.forms.comment_form.submit();">Kommentar absenden</a>
										</td>
										<td width="20" align="right" valign="bottom">&nbsp;</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_right.png); background-repeat: repeat-y; width:17x;">&#160;</td>
			</tr>
			<tr>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_left.png); background-repeat: no-repeat; height:63px; width:32px;">&#160;</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_center.png); background-repeat: repeat-x; height:63px;">&#160;</td>
				<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_right.png); background-repeat: no-repeat; height:63px; width:17px;">&#160;</td>
			</tr>
		</table>
	</form>
	
	<table border="0" bgcolor="#ffffff" cellpadding="0" cellspacing="0" width="980" style="margin-left:22px;">
        <tr>
            <td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_left.png); background-repeat: no-repeat; height:37px; width:32px;">&#160;</td>
            <td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_center.png); background-repeat: repeat-x; height:37px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:7px">
                    <tr>
                        <td><strong>Kommentare von anderen Usern:</strong></td>
                    </tr>
                </table>
            </td>
            <td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_top_right.png); background-repeat: no-repeat; height:37px; width:17px;">&#160;</td>
        </tr>
        <tr>
            <td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_left.png); background-repeat: repeat-y; width:32px;">&#160;</td>
            <td style="vertical-align:top; text-align:left;">
				<!-- $comments -->
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<?
					$maxEntrys=5;

					$pagejump=0;
					if( $maxEntrys > count($comments) ){
						$maxEntrys = count($comments);
					}else{
						$pages = (int)(ceil(count($comments)/$maxEntrys));
						if( empty($_GET["page"]) ){ $actual_page=0; }else{ $actual_page=(int)$_GET["page"]; }
						$pagejump=(int)($actual_page*$maxEntrys);
					}

					?>
					<?for($i=$pagejump; $i<$pagejump+$maxEntrys; $i++){?>
						<? if(!isset($comments[$i])){ break; }
						    $commentUser = $comments[$i]->GetUser();
						?>
						<?if( $_GET["edit"]=="" ){?>
							<tr>
								<td align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;"><br />
									<?if( $comments[$i]->GetIntern() == true ){?>
										<img src="../../pics/gui/rs-logo_small.gif" alt="Kommentar nur für SFM sichtbar" title="Kommentar nur für SFM sichtbar" width="8" height="8"/>
									<?}?>
									<?=$comments[$i]->GetComment()?>
									<br />
									<img src="../../pics/blind.gif" alt="" width="20" height="5"><br />
									<?
									// FILES
									$files = $comments[$i]->GetFiles($db);
									for($ii=0; $ii<count($files); $ii++){?>
										<a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($files[$ii])?>&timestamp=<?=time();?>">
											<img src="../../pics/gui/file-icon_<?=strtolower($FM_FILEEXTENSION_TO_FILETYPE[$files[$ii]->GetFileType()][0])?>.png" alt="" width="16" height="16" style="position:relative; top:4px" border="0"/> 
											<?=$files[$ii]->GetFileName()?>
										</a>&#160;&#160;&#160;
									<?}?>
									<br />
									<br />
								</td>
								<td width="30" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;">&nbsp;</td>
								<td width="140" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;">
									<br />
									<?=date("[d.m.Y - H:i:s]", $comments[$i]->GetTime());?>
									<br />
									<img src="../../pics/gui/user_small.png" alt="User" title="User" width="9" height="14" style="position:relative; top:3px"/>
										<?=($commentUser==null ? '**GELÖSCHT**' : $commentUser->GetUserName());?>
									<br />
									<br /> 
								</td>
								<td width="20" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;">&nbsp;</td>
								<td width="26" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;">
									<br />
									<?if( $commentUser!=null && $_SESSION["currentUser"]->GetPKey() == $commentUser->GetPKey()){?>
										<?$link = $SHARED_HTTP_ROOT."de/meineaufgaben/comments.php5?".SID."&processId=".$_GET["processId"]."&page=".$_GET["page"]."&edit=".$i;?>
										<a href="<?=$link?>">
											<img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/edit.png" onmouseover="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/edit_over.png'" onmouseout="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/edit.png'" alt="Kommentar bearbeiten" title="Kommentar bearbeiten" border="0"/>
										</a>
									<?}?>
								</td>
								<td width="15" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;">&nbsp;</td>
							</tr>
						<?}else if((int)$_GET["edit"] != $i){?>
							<tr>
								<td align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;"><br />
									<?if( $comments[$i]->GetIntern() == true ){?>
										<img src="../../pics/gui/rs-logo_small.gif" alt="Kommentar nur für SFM sichtbar" title="Kommentar nur für SFM sichtbar" width="8" height="8"/>
									<?}?>
									<?=$comments[$i]->GetComment()?>
									<br />
									<img src="../../pics/blind.gif" alt="" width="20" height="5"><br />
									<?
									// FILES
									$files = $comments[$i]->GetFiles($db);
									for($ii=0; $ii<count($files); $ii++){?>
										<a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($files[$ii])?>&timestamp=<?=time();?>">
											<img src="../../pics/gui/file-icon_<?=strtolower($FM_FILEEXTENSION_TO_FILETYPE[$files[$ii]->GetFileType()][0])?>.png" alt="" width="16" height="16" style="position:relative; top:4px" border="0"/> 
											<?=$files[$ii]->GetFileName()?>
										</a>&#160;&#160;&#160;
									<?}?>
									<br />
									<br />
								</td>
								<td width="30" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;">&nbsp;</td>
								<td width="140" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;">
									<br />
									<?=date("[d.m.Y - H:i:s]", $comments[$i]->GetTime());?>
									<br />
									<img src="../../pics/gui/user_small.png" alt="User" title="User" width="9" height="14" style="position:relative; top:3px"/> 
										<?=($commentUser==null ? '**GELÖSCHT**' : $commentUser->GetUserName());?>
									<br />
									<br /> 
								</td>
								<td width="20" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;">&nbsp;</td>
								<td width="26" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;">
									<br />
									<?if( $commentUser!=null && $_SESSION["currentUser"]->GetPKey() == $commentUser->GetPKey()){?>
										<?$link = $SHARED_HTTP_ROOT."de/meineaufgaben/comments.php5?".SID."&processId=".$_GET["processId"]."&page=".$_GET["page"]."&edit=".$i;?>
										<a href="<?=$link?>">
											<img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/edit.png" onmouseover="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/edit_over.png'" onmouseout="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/edit.png'" alt="Kommentar bearbeiten" title="Kommentar bearbeiten" border="0"/>
										</a>
									<?}?>
								</td>
								<td width="15" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;">&nbsp;</td>
							</tr>
						<?}else{?>
							<tr style="background-color:#f0f0f0">
								<td rowspan="2" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;"><br />
									<form enctype="multipart/form-data" method="post" name="edit_form">
										<input type="hidden" name="edit" id="edit" value="true" />
										<input type="hidden" name="store_edit" id="store_edit" value="false" />
										<input type="hidden" name="edit_id" id="edit_id" value="<?=$i?>" />
										<input type="hidden" name="del_editfile" id="del_editfile" value="false" />
										<span style="position:relative; left:15px"><strong>Kommentar bearbeiten</strong></span>
										<br /><br />
										<table border="0" cellspacing="0" cellpadding="0" width="100%">
											<tr>
												<td width="15" rowspan="2" valign="top">&nbsp;</td>
												<td width="260" rowspan="2" valign="top">
													<textarea name="comment3" style="height:90px; width:260px;"><?=$comments[$i]->GetComment()?></textarea></br>
													<span class="errorText"><?=$error_text_edit?></span></td>
												</td>
												<td width="30" rowspan="2">&nbsp;</td>
												<td align="left" valign="top">
													<strong>Dateianh&auml;nge zu Ihrem Kommentar:</strong>
													<br />
													<table width="100%" border="0" cellspacing="0" cellpadding="0">
														<?
														// FILES
														$files = $comments[$i]->GetFiles($db);
														for($ii=0; $ii<count($files); $ii++)
														{	?>
															<tr>
																<td height="23" style="border-bottom: 1px solid #ffffff;">
																	<a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($files[$ii])?>&timestamp=<?=time();?>">
																		<img src="../../pics/gui/file-icon_<?=trim(strtolower($FM_FILEEXTENSION_TO_FILETYPE[$files[$ii]->GetFileType()][0]))?>.png" alt="" width="16" height="16" style="position:relative; top:4px" border="0"/> 
																		<?=$files[$ii]->GetFileName();?>
																	</a>
																</td>
																<td width="16" style="border-bottom: 1px solid #ffffff;">
																	<a href="#" onclick="document.forms.edit_form.del_editfile.value='<?=$ii?>'; document.forms.edit_form.submit();">
																		<img src="../../pics/gui/deleteAttachment.png" alt="Dateianhang entfernen" title="Dateianhang entfernen" width="16" height="15" border="0"/>
																	</a>
																</td>
															</tr>
													<?	}?>
													</table>
													<br />
													<strong>Datei:</strong>&nbsp;
													<input type="file" id="addfile" name="addfile" class="FileForm"/>
													<input type="button" value="Upload" id="btn_2" name="btn_2" class="FormButton" style="position:relative; top:-1px" onclick="javascript:document.forms.edit_form.submit();" />
													<br />
													<span class="errorText"><?=$error_file_edit?></span> <br />
													<br />
													<? if($_SESSION["currentUser"]->GetGroupBasetype($db)!=UM_GROUP_BASETYPE_AUSHILFE && $_SESSION["currentUser"]->GetGroupBasetype($db)!=UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT){ ?>
														<strong>Kommentar sichtbar f&uuml;r:</strong>&nbsp;
														<select name="comment4" id="comment" style="position:relative; top:2px">
														<option value="1" <?if($comments[$i]->GetIntern() == true){?>selected<?}?>>SFM</option>
														<option value="0" <?if($comments[$i]->GetIntern() == false){?>selected<?}?>>Alle Benutzer</option>
														</select>
														<span class="errorText"><?=$error_user_edit?></span>
														<br />
														<br />
													<?}?>
												</td>
												<td width="20" align="left" valign="top">&nbsp;</td>
											</tr>
										</table>
										<br />
									</form>
								</td>
								<td rowspan="2" align="left" valign="top" style="border-bottom: 2px solid #9E9E9E; border-left: 1px solid #ffffff">&nbsp;</td>
								<td colspan="3" align="left" valign="top"><br />
									<?=date("[d.m.Y - H:i:s]", $comments[$i]->GetTime());?>
									<br />
									<img src="../../pics/gui/user_small.png" alt="User" title="User" width="9" height="14" style="position:relative; top:3px"/> 
									<?=($commentUser==null ? '**GELÖSCHT**' : $commentUser->GetUserName());?>
									<br />
									<br />
									<br />
								</td>
								<td align="left" valign="top">&nbsp;</td>
							</tr>
							<tr style="background-color:#f0f0f0">
								<td colspan="3" align="left" valign="bottom" style="border-bottom: 2px solid #9E9E9E;"><br />
								<img src="../../pics/gui/saveComment.png" alt="" width="25" height="25" style="position:relative; top:7px"/> <a href="#" onclick="document.forms.edit_form.store_edit.value='true'; document.forms.edit_form.submit();">Änderungen übernehmen</a><br />
								<br />
								<br />
								</td>
								<td align="left" valign="top" style="border-bottom: 2px solid #9E9E9E;">&nbsp;</td>
							</tr>
						<?}?>
					<?}?>
				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td align="center">
						
							<?if(isset($pages)){?>
								Seite: 
								<?for($ii=0; $ii<$pages; $ii++){ 
									$link = $SHARED_HTTP_ROOT."de/meineaufgaben/comments.php5?".SID."&processId=".$_GET["processId"]."&page=".$ii;
									if($ii!=0){?>&#160;&#160;I&#160;&#160;<?}?>
									<?if($_GET["page"] == ""){?>
										<?if($ii == 0){?>
											<a href="<?=$link?>" style="color:#993333;"><?=$ii+1?></a>
										<?}else{?>
											<a href="<?=$link?>"><?=$ii+1?></a>
										<?}?>
									<?}else{?>
										<?if($ii == (int)$_GET["page"]){?>
											<a href="<?=$link?>" style="color:#993333;"><?=$ii+1?></a>
										<?}else{?>
											<a href="<?=$link?>"><?=$ii+1?></a>
										<?}?>
									<?}?>
								<?}?>
							<?}?>
						
						</td>
					</tr>
				</table>
			</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_center_right.png); background-repeat: repeat-y; width:17x;">&#160;</td>
		</tr>
		<tr>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_left.png); background-repeat: no-repeat; height:63px; width:32px;">&#160;</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_center.png); background-repeat: repeat-x; height:63px;">&#160;</td>
			<td style="background: url(<?=$SHARED_HTTP_ROOT?>pics/edit/edit_bottom_right.png); background-repeat: no-repeat; height:63px; width:17px;">&#160;</td>
		</tr>
	</table>
</div>
<?
include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>