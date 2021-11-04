<?php
/***************************************************************************
 * Listen-Darstellungs Klasse NCASList
 * 
 * @access   	public
 * @author   	Johannes Glaser <j.glaser@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class NCASList{
	/***************************************************************************
	 * Variablen
	 ***************************************************************************/
	public $myListManager;
	
	protected $formName="";

    protected $maxNumEntriesPerPage = 50;

	/***************************************************************************
	 * Konstruktor
	 ***************************************************************************/
	public function NCASList( $listObject, $formName="form_lists" ){
		$this->formName=$formName;
		$this->myListManager = new ListManager( $listObject );
		
		$ID = $this->myListManager->GetListID();
		// LÖSCHEN
		$deleteSelected = "";
		if($_GET["deleteSelected"] != ""){
			$deleteSelected = $_GET["deleteSelected"];
		}else if($_POST["deleteElements"] != ""){
			$deleteSelected = $_POST["deleteElements"];
		}
		if( $deleteSelected != "" ){
			$deleteAufrufArray=Array();
			$deleteArray = explode("|", $deleteSelected);
			for($lauf=0; $lauf<count($deleteArray); $lauf++){
				$deleteArrayExp[$lauf] = explode("_", $deleteArray[$lauf]);
			}
			for($lauf=0; $lauf<count($deleteArrayExp); $lauf++){
				if($deleteArrayExp[$lauf][0] == $ID){
					$deleteAufrufArray[] = $deleteArrayExp[$lauf][1];
				}
			}
			$this->myListManager->DeleteEntries( $deleteAufrufArray );
		}
		// KLONEN
		$cloneSelected = "";
		if($_GET["cloneSelected"] != ""){
			$cloneSelected = $_GET["cloneSelected"];
		}else if($_POST["cloneElements"] != ""){
			$cloneSelected = $_POST["cloneElements"];
		}
		if( $cloneSelected != "" ){
			$cloneAufrufArray=Array();
			$cloneArray = explode("|", $cloneSelected);
			for($lauf=0; $lauf<count($cloneArray); $lauf++){
				$cloneArrayExp[$lauf] = explode("_", $cloneArray[$lauf]);
			}
			for($lauf=0; $lauf<count($cloneArrayExp); $lauf++){
				if($cloneArrayExp[$lauf][0] == $ID){
					$cloneAufrufArray[] = $cloneArrayExp[$lauf][1];
				}
			}
			$this->myListManager->CloneEntries( $cloneAufrufArray );
		}
	}
	
	/***************************************************************************
	 * PrintData()
	 * Ausgabe der Daten in HTML mit Design
	 ***************************************************************************/
	public function PrintData(){
		global $DOMAIN_FILE_SYSTEM_ROOT;
		global $SHARED_HTTP_ROOT;
		
		$pagename = explode("/", $_SERVER["SCRIPT_NAME"]);
		$pagename = $pagename[ count($pagename)-3 ]."/".$pagename[ count($pagename)-2 ]."/".$pagename[ count($pagename)-1 ];

		$dataArray = $this->myListManager->PrintData();
		$ID = $this->myListManager->GetListID();
		
		// Anzahl der anzuzeigenden Einträge je Seite
		$showEntrysAtOnce = 5;
		if( !isset($_POST["elements_per_page_".$ID]) && isset($_SESSION["listmanager"][$pagename]) && isset($_SESSION["listmanager"][$pagename][$ID]["elements_per_page"])){
			$showEntrysAtOnce = $_SESSION["listmanager"][$pagename][$ID]["elements_per_page"];
		}
		if( ((int)$_POST["elements_per_page_".$ID])>0 ){
			$showEntrysAtOnce=(int)$_POST["elements_per_page_".$ID];
		}
        if ($showEntrysAtOnce>$this->maxNumEntriesPerPage) $showEntrysAtOnce = $this->maxNumEntriesPerPage;
		$_SESSION["listmanager"][$pagename][$ID]["elements_per_page"] = $showEntrysAtOnce;
		
		if( !isset($_POST["search_".$ID]) && isset($_SESSION["listmanager"][$pagename]) && isset($_SESSION["listmanager"][$pagename][$ID]["search"])){
			$_POST["search_".$ID] = $_SESSION["listmanager"][$pagename][$ID]["search"];
		}
		
		if( isset($_POST["search_".$ID]) ){
			$_SESSION["listmanager"][$pagename][$ID]["search"] = $_POST["search_".$ID];
		}
		
		// Gesamtanzahl der Einträge ermitteln
		$totalNumEntrys=$this->myListManager->GetNumTotalEntrys($_POST["search_".$ID]);
		// Anzahl der benötigten Seiten berechnen
		$pages = ceil($totalNumEntrys / $showEntrysAtOnce);
		if( $pages<=0 ) $pages=1;
		// Aktuelle Seite ermitteln
		$page = 0;
		if( isset($_POST["page_".$ID]) && is_numeric($_POST["page_".$ID]) ){
			$page = ((int)$_POST["page_".$ID])-1;
			$_SESSION["listmanager"][$pagename][$ID]["page"] = $page;
		}
			
		if($_POST["page_".$ID."_next_x"] != "" && $_POST["page_".$ID."_next_x"] != "0" && $_POST["page_".$ID."_next_y"] != "0"){
			$page++;
			$_SESSION["listmanager"][$pagename][$ID]["page"] = $page;
		}
		if($_POST["page_".$ID."_back_x"] != "" && $_POST["page_".$ID."_back_x"] != "0" && $_POST["page_".$ID."_back_y"] != "0"){
			$page--;
			$_SESSION["listmanager"][$pagename][$ID]["page"] = $page;
		}
		if($page < 0 ){
			$page=0;
			$_SESSION["listmanager"][$pagename][$ID]["page"] = $page;
		}
		if(($page+1) >= $pages){
			$page=$pages-1;
			$_SESSION["listmanager"][$pagename][$ID]["page"] = $page;
		}
		// Page in Session speichern
		$page = $_SESSION["listmanager"][$pagename][$ID]["page"];
		
		if( isset($_SESSION["listmanager"][$pagename][$ID]["show_liste"]) && !isset($_POST["show_liste_".$ID]) ){
			$_POST["show_liste_".$ID] = $_SESSION["listmanager"][$pagename][$ID]["show_liste"];
		}
		if(!isset($_POST["show_liste_".$ID])){ 
			$_POST["show_liste_".$ID] = true;
			$_SESSION["listmanager"][$pagename][$ID]["show_liste"] = $_POST["show_liste_".$ID];
		}
		if($_POST["show_liste_".$ID] == true && isset($_POST["minimize_".$ID."_x"])){
			$show_liste[$ID] = false;
			$_SESSION["listmanager"][$pagename][$ID]["show_liste"] = $show_liste[$ID];
		}else if($_POST["show_liste_".$ID] == false && isset($_POST["maximize_".$ID."_x"])){
			$show_liste[$ID] = true;
			$_SESSION["listmanager"][$pagename][$ID]["show_liste"] = $show_liste[$ID];
		}else{
			if($_POST["show_liste_".$ID] == true){
				$show_liste[$ID] = true;
				$_SESSION["listmanager"][$pagename][$ID]["show_liste"] = $show_liste[$ID];
			}else{
				$show_liste[$ID] = false;
				$_SESSION["listmanager"][$pagename][$ID]["show_liste"] = $show_liste[$ID];
			}
		}
		$show_liste[$ID] = $_SESSION["listmanager"][$pagename][$ID]["show_liste"];
		
		// Sortierung
		if( isset( $_SESSION["listmanager"][$pagename][$ID]["order_by"] ) && !isset($_POST["order_by_".$ID]) ){
			$_POST["order_by_".$ID] = $_SESSION["listmanager"][$pagename][$ID]["order_by"];
		}
		if( !isset($_POST["order_by_".$ID]) ){
			$_POST["order_by_".$ID]=0;
			$_SESSION["listmanager"][$pagename][$ID]["order_by"] = $_POST["order_by_".$ID];
		}else{
			$_SESSION["listmanager"][$pagename][$ID]["order_by"] = $_POST["order_by_".$ID];
		}
		$_POST["order_by_".$ID] = $_SESSION["listmanager"][$pagename][$ID]["order_by"];
		
		
		if( isset( $_SESSION["listmanager"][$pagename][$ID]["order_direction"] ) &&  !isset($_POST["order_direction_".$ID]) ){
			$_POST["order_direction_".$ID] = $_SESSION["listmanager"][$pagename][$ID]["order_direction"];
		}
		if( !isset($_POST["order_direction_".$ID]) ){
			$_POST["order_direction_".$ID]="ASC";
			$_SESSION["listmanager"][$pagename][$ID]["order_direction"] = $_POST["order_direction_".$ID];
		}else{
			$_SESSION["listmanager"][$pagename][$ID]["order_direction"] = $_POST["order_direction_".$ID];
		}
		$_POST["order_direction_".$ID] = $_SESSION["listmanager"][$pagename][$ID]["order_direction"];
		
		////////////////////////////////////
		// SUCHE		
		$search = $this->myListManager->SearchData($_POST["search_".$ID], $_POST["order_by_".$ID], $_POST["order_direction_".$ID], $showEntrysAtOnce, $page);
		if($search != "" && $search != NULL){
			$dataArray["data"] = $search;
		}elseif(trim($_POST["search_".$ID])!="" ){
			$errorText = '<span style="color:#ff2222">Ihre Suchanfrage ergab leider keinen Treffer</span>';
		}else{
			$errorText = '<span style="color:#ff2222">Es sind keine Einträge vorhanden</span>';
		}
		// SUCHE ENDE
		////////////////////////////////////
		
		// Listenmanager-Konfiguration im Userobjekt der Session speichern
		$_SESSION["currentUser"]->SetListmanagerConfig( $_SESSION["listmanager"] );
		
		
		$abstand = "40";
		if($ID == 0){?>
			<input type="hidden" id="deleteElements" name="deleteElements" value="" />
			<input type="hidden" id="cloneElements" name="cloneElements" value="" />
			<input type="hidden" id="planElements" name="planElements" value="" />
			<input type="hidden" id="assignElements" name="assignElements" value="" />
		<?}?>
		<input type="hidden" name="show_liste_<?=$ID?>" id="show_liste_<?=$ID?>" value="<?=$show_liste[$ID]?>" />&#160;
		<input type="hidden" name="order_by_<?=$ID?>" id="order_by_<?=$ID?>" value="<?=$_POST["order_by_".$ID]?>" />
		<input type="hidden" name="order_direction_<?=$ID?>" id="order_direction_<?=$ID?>" value="<?=$_POST["order_direction_".$ID]?>" />
	<? 	if( $dataArray["show"]["show_header"] ){ ?>
		<div style="background:url(<?=$SHARED_HTTP_ROOT?>pics/liste/dif_background.jpg); width:100%; height:29px; white-space:nowrap;">
			<table border="0" cellpadding="0" cellspacing="0" style="width:100%;height:100%;">
				<tr>
					<td width="33" valign="center" align="left">
						<?if($dataArray["icon"] != ""){?>
							<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/<?=$dataArray["icon"]?>" width="25" height="25" /><br/>
						<?}else{?>
							<img src="<?=$SHARED_HTTP_ROOT?>pics/liste/blind.gif" width="25" height="25" /><br/>
						<?}?>
					</td>
					<td valign="center"><strong><?=(trim($dataArray["icontext"])=="" ? "&#160;" : trim($dataArray["icontext"]));?></strong></td>
					<?if($dataArray["show"]["show_elements_all"] == true){?>
						<td width="75" valign="center" align="center"><?=$totalNumEntrys?><?if($totalNumEntrys==1){?>&#160;Eintrag&#160;&#160;<?}else{?>&#160;Einträge<?}?></td>
					<?}?>
					<? //Elements per Page ?>
					<?if($dataArray["show"]["show_elements_page"] == true){?>
						<td width="75" valign="center">Einträge / Seite</td>
						<td width="20" valign="center"><input type="text" name="elements_per_page_<?=$ID?>" id="elements_per_page_<?=$ID?>" value="<?=$showEntrysAtOnce;?>" class="listHeaderInput" style="width:20px;"/></td>
						<td width="25" valign="center"><img src="<?=$SHARED_HTTP_ROOT?>pics/liste/pfeil_right.png" name="send_<?=$ID?>" style="cursor:pointer;cursor:hand;" alt="Anzahl Einträge pro Seite ändern" title="Anzahl Einträge pro Seite ändern" onclick="document.forms.<?=$this->formName;?>.submit();" /></td>					
						<td width="20" valign="center"><img src="<?=$SHARED_HTTP_ROOT?>pics/liste/blind.gif" width="20" height="1" /></td>
					<?}?>
					<? // [<-] Seite x/y [->] ?>
					<?if($dataArray["show"]["show_site_of_sites"] == true){?>
						<td width="25" valign="center"><input type="image" src="<?=$SHARED_HTTP_ROOT?>pics/liste/pfeil_left.png" name="page_<?=$ID?>_back" id="page_<?=$ID?>_back" alt="Seite zurück" title="Seite zurück" /></td>
						<td width="65" valign="center" align="center">Seite <?=$page+1?> / <?=$pages?></td>
						<td width="25" valign="center"><input type="image" src="<?=$SHARED_HTTP_ROOT?>pics/liste/pfeil_right.png" name="page_<?=$ID?>_next" id="page_<?=$ID?>_next" alt="Seite vor" title="Seite vor" /></td>
						<td width="20" valign="center"><img src="<?=$SHARED_HTTP_ROOT?>pics/liste/blind.gif" width="20" height="1" /></td>
						<td width="30" valign="center">Seite</td>
						<td width="20" valign="center"><input type="text" name="page_<?=$ID?>" id="page_<?=$ID?>" value="<?=$page+1?>" class="listHeaderInput" style="width:20px;" /></td>
						<td width="25" valign="center"><img src="<?=$SHARED_HTTP_ROOT?>pics/liste/pfeil_right.png" name="send_<?=$ID?>" style="cursor:pointer;cursor:hand;" alt="Seite anzeigen" title="Seite anzeigen" onclick="document.forms.<?=$this->formName;?>.submit();" /></td>
						<td width="20" valign="center"><img src="<?=$SHARED_HTTP_ROOT?>pics/liste/blind.gif" width="20" height="1" /></td>
					<?}?>
					<? //Search ?>
					<?if($dataArray["show"]["show_search"] == true){?>
						<td width="40" valign="center">Suche</td>
						<td width="150" valign="center"><input type="text" name="search_<?=$ID?>" id="search_<?=$ID?>" value="<?=$_POST["search_".$ID]?>" class="listHeaderInput" style="width:150px;" /></td>
						<td width="25" valign="center"><img src="<?=$SHARED_HTTP_ROOT?>pics/liste/pfeil_right.png" name="send_search_<?=$ID?>" style="cursor:pointer;cursor:hand;"  alt="Suche startet" title="Suche startet" onclick="document.forms.<?=$this->formName;?>.submit();" /></td>
						<td width="25" valign="center"><img src="<?=$SHARED_HTTP_ROOT?>pics/liste/close.png" name="reset_search_<?=$ID?>" style="cursor:pointer;cursor:hand;" alt="Suche zurücksetzen" title="Suche zurücksetzen" onclick="document.getElementById('search_<?=$ID?>').value=''; document.forms.<?=$this->formName;?>.submit();" /></td>
						<td width="20" valign="center"><img src="<?=$SHARED_HTTP_ROOT?>pics/liste/blind.gif" width="20" height="1" /></td>
					<?}?>
					<? //Minimieren/Maximieren ?>
					<?if($show_liste[$ID] == true){?>
						<td width="25" valign="center"><input src="<?=$SHARED_HTTP_ROOT?>pics/liste/liste_maximieren_active.png" type="image" name="maximize_<?=$ID?>" id="maximize_<?=$ID?>" value="true" alt="Liste maximieren" title="Liste maximieren" /></td>
						<td width="25" valign="center"><input src="<?=$SHARED_HTTP_ROOT?>pics/liste/liste_minimieren_inactive.png" type="image" name="minimize_<?=$ID?>" id="minimize_<?=$ID?>" value="true" alt="Liste minimieren" title="Liste minimieren" /></td>
					<?}else{?>
						<td width="25" valign="center"><input src="<?=$SHARED_HTTP_ROOT?>pics/liste/liste_maximieren_inactive.png" type="image" name="maximize_<?=$ID?>" id="maximize_<?=$ID?>" value="true" alt="List maximieren" title="List maximieren" /></td>
						<td width="25" valign="center"><input src="<?=$SHARED_HTTP_ROOT?>pics/liste/liste_minimieren_active.png" type="image" name="minimize_<?=$ID?>" id="minimize_<?=$ID?>" value="true" alt="Liste minimieren" title="Liste minimieren" /></td>
					<?}?>
				</tr>
			</table>
		</div>
	<?	}?>
		<? // AUSGABE DATEN ?>
		<?if($show_liste[$ID] == true){?>
			<table border="0" cellpadding="5" cellspacing="0" style="width:100%;">
				<tr>
					<?for($i=0; $i<count($dataArray["datahead"]); $i++){?>
						<?if($i==0 || $errorText!=""){?><td width="18" <?if($i!=count($dataArray["data"]) || $errorText!=""){?>style="border-bottom: 2px solid #2C2C2C;"<?}?>>&#160;</td><?}?>
						<?if($dataArray["datahead"][$i]["caption"] == "Optionen"){?>
							<td style="border-bottom: 2px solid #2C2C2C; width:90px;">
								<strong>
									<?=$dataArray["datahead"][$i]["caption"]?>
								</strong>
							</td>
						<?}else{?>
							<td style="border-bottom: 2px solid #2C2C2C;<?if(isset($dataArray["datahead"][$i]["width"]))echo "width:".$dataArray["datahead"][$i]["width"].";";?>" valign="top">
								<strong>
									<? if( $dataArray["datahead"][$i]["sortby"] != "" ){ ?>
										<span style="cursor:pointer; cursor:hand;" onclick="if(document.getElementById('order_direction_<?=$ID?>').value=='ASC' && document.getElementById('order_by_<?=$ID?>').value=='<?=$i?>'){ document.getElementById('order_direction_<?=$ID?>').value='DESC'; }else{ document.getElementById('order_direction_<?=$ID?>').value='ASC'; } document.getElementById('order_by_<?=$ID?>').value='<?=$i?>'; document.forms.<?=$this->formName;?>.submit();">
									<?}?>
									<?=$dataArray["datahead"][$i]["caption"]?>
									<? if( $_POST["order_by_".$ID] == $i ){ ?>
										<? if( $_POST["order_direction_".$ID] != "" ){ ?>
											<? if( $_POST["order_direction_".$ID] == "ASC" ){ ?>
												<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/order_asc.png" alt="" />
											<?}else{?>
												<img src="<?=$SHARED_HTTP_ROOT?>pics/gui/order_desc.png" alt="" />
											<?}?>
										<?}?>
									<?}?>
									<? if( $dataArray["datahead"][$i]["sortby"] != "" ){ ?>
										</span>
									<?}?>
								</strong>
							</td>
						<?}?>
					<?}?>
				</tr>
			<?	// Alle Einträge durchlaufen
				for($i=0; $i<count($dataArray["data"]); $i++){?>
					<?
					// Kontextmenüinhalte definieren
					// Bearbeiten und Löschen
					
					$a = count($dataArray["data"][$i])-1;
					$deleteableornot = false;
					$checkid=0;

					$pkey = "";
					if (is_array($dataArray["data"][$i][$a]) && isset($dataArray["data"][$i][$a]["pkey"]) ) $pkey = $dataArray["data"][$i][$a]["pkey"];
					
					$delete=false;
					$del_selected_item_context = "#";
					if( isset($dataArray["data"][$i][$a]["deleteUrl"]) && is_array($dataArray["data"][$i][$a]) ){
						$del_selected_item_context = str_replace("'", '\"', $dataArray["data"][$i][$a]["deleteUrl"]);
						$deleteableornot = true;
						$delete=true;
						$checkid=1;
					}
					
					$bearbeiten = false;
					$strTemp = "#";
					if( isset($dataArray["data"][$i][$a]["editUrl"]) && is_array($dataArray["data"][$i][$a]) ){
						$strTemp=$dataArray["data"][$i][$a]["editUrl"];
						if( strstr( $dataArray["data"][$i][$a]["editUrl"], "javascript:" )===false )$strTemp.="&".SID;
						$bearbeiten = true;
						$checkid=1;
					}
					
					$klonen = false;
					$klonen_url = "#";
					if( isset($dataArray["data"][$i][$a]["cloneUrl"]) && is_array($dataArray["data"][$i][$a]) ){
						$klonen_url=str_replace("'", '\"', $dataArray["data"][$i][$a]["cloneUrl"]);
						$klonen = true;
						$checkid=1;
					}
					
					$planen = false;
					$planen_url = "#";
					if( isset($dataArray["data"][$i][$a]["planTaskUrl"]) && is_array($dataArray["data"][$i][$a]) ){
						$planen_url=str_replace("'", '\"', $dataArray["data"][$i][$a]["planTaskUrl"]);
						$planen = true;
						$checkid=1;
					}
					
					$assign = false;
					$assign_url = "#";
					if( isset($dataArray["data"][$i][$a]["assignTaskUrl"]) && is_array($dataArray["data"][$i][$a]) ){
						$assign_url=str_replace("'", '\"', $dataArray["data"][$i][$a]["assignTaskUrl"]);
						$assign = true;
						$checkid=1;
					}
					
					$phone = false;
					$phone_url = "#";
					if( isset($dataArray["data"][$i][$a]["phoneUrl"]) && is_array($dataArray["data"][$i][$a]) ){
						$phone_url=str_replace("'", '\"', $dataArray["data"][$i][$a]["phoneUrl"]);
						$phone = true;
						$checkid=1;
					}
					
					$editstate = false;
					$editstate_url = "#";
					if( isset($dataArray["data"][$i][$a]["editstateUrl"]) && is_array($dataArray["data"][$i][$a]) ){
						$editstate_url=str_replace("'", '\"', $dataArray["data"][$i][$a]["editstateUrl"]);
						$editstate = true;
						$checkid=1;
					}

					$contextmenu = 'set_context_menu( '.(int)$bearbeiten.', "'.$strTemp.'", '.(int)$delete.', "'.$del_selected_item_context.'", '.(int)$klonen.', "'.$klonen_url.'", '.(int)$planen.', "'.$planen_url.'", '.(int)$phone.', "'.$phone_url.'", '.(int)$assign.', "'.$assign_url.'", '.(int)$editstate.', "'.$editstate_url.'", '.(int)$checkid.' ); showMenu;';
					?>
					<tr id="<?=$ID?>_<?=$pkey?>" <?if($checkid!=0){?>oncontextmenu='tableclicked=true;if( GetIDs().indexOf("<?=$ID?>_<?=$pkey?>") == -1 ){ selectItem("<?=$ID?>_<?=$pkey?>"); } <?=$contextmenu?>'<?}?> style="background-color:#FFFFFF;" <?if($checkid!=0){?>onclick="<?if($deleteableornot == true){?>deletableItems=true;<?}?>tableclicked=true;selectItem('<?=$ID?>_<?=$pkey?>');"<?}?> onmouseover="if( GetIDs().indexOf('<?=$ID?>_<?=$pkey?>') == -1 ) this.style.backgroundColor='#D0E8F2'" onmouseout="if( GetIDs().indexOf('<?=$ID?>_<?=$pkey?>') == -1 ) this.style.backgroundColor='#FFFFFF'" >
					<?	// Alle Spalten durchlaufen
						for($a=0; $a<count($dataArray["datahead"]); $a++){?>
						<?	if($a==0){?>
								<td width="15" <?if( $i<count($dataArray["data"])-1 ){?>style="border-bottom: 2px solid #9E9E9E;"<?}?>>
								<?	if( isset($dataArray["data"][$i][-1]["selectAction"]) ){
										?><input type="radio" name="list_<?=$ID;?>_selection" value="<?=$dataArray["data"][$i][-1]["pkey"]?>" onclick="<?=$dataArray["data"][$i][-1]["selectAction"]?>" <?if($dataArray["data"][$i][-1]["selected"]===true)echo "checked";?> /><?
									}else{?>
										&#160;	
								<?	}?>
								</td>
						<?	}?>
							<td <?if($checkid!=0){?>oncontextmenu='<?=$contextmenu?>'<?}?> onselect="return false;" <?if($i<count($dataArray["data"])-1 ){?>style="border-bottom: 2px solid #9E9E9E;"<?}?> <?=($dataArray["datahead"][$a]["align"]!="" ? "align='".$dataArray["datahead"][$a]["align"]."'" : "");?>>
							<? 	if( !is_array($dataArray["data"][$i][$a]) ){
									echo $dataArray["data"][$i][$a];
								}else{ 
									if( isset($dataArray["data"][$i][$a]["editUrl"]) ){ 
										?><a href="<?=$dataArray["data"][$i][$a]["editUrl"];?>"><img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/edit.png" onmouseover="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/edit_over.png'" onmouseout="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/edit.png'" alt="Eintrag bearbeiten" title="Eintrag bearbeiten" /></a><?
									}
									if( isset($dataArray["data"][$i][$a]["cloneUrl"]) ){ 
										?><a href="javascript:createCloneUrl('<?=$dataArray["data"][$i][$a]["cloneUrl"];?>');"><img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/clone.png" onmouseover="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/clone_over.png'" onmouseout="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/clone.png'"  alt="Eintrag duplizieren" title="Eintrag duplizieren" /></a><?
									}
									if( isset($dataArray["data"][$i][$a]["deleteUrl"]) ){
										/*?><a href="<?=$dataArray["data"][$i][$a]["deleteUrl"];?>"><img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/cancel.png" onmouseover="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/cancel_over.png'" onmouseout="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/cancel.png'" alt="Delete" /></a><?*/
										?><a href="javascript:createDeleteUrl('<?=$dataArray["data"][$i][$a]["deleteUrl"]?>');"><img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/cancel.png" onmouseover="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/cancel_over.png'" onmouseout="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/cancel.png'"  alt="Eintrag löschen" title="Eintrag löschen" /></a><?
									}
									if( isset($dataArray["data"][$i][$a]["planTaskUrl"]) ){
										?><a href="javascript:createPlanUrl('<?=$dataArray["data"][$i][$a]["planTaskUrl"]?>');"><img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/planTask.png" onmouseover="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/planTask_over.png'" onmouseout="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/planTask.png'"  alt="Aufgabe als 'geplant' markieren" title="Aufgabe als 'geplant' markieren" /></a><?
									}
									if( isset($dataArray["data"][$i][$a]["planTaskUrl2"]) ){
										?><a href="javascript:createPlanUrl('<?=$dataArray["data"][$i][$a]["planTaskUrl2"]?>');"><img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/planTask.png" onmouseover="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/planTask_over.png'" onmouseout="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/planTask.png'"  alt="Aufgabe als 'ungeplant' markieren" title="Aufgabe als 'ungeplant' markieren" /></a><?
									}
									if( isset($dataArray["data"][$i][$a]["assignTaskUrl"]) ){
										?><a href="javascript:createAssignUrl('<?=$dataArray["data"][$i][$a]["assignTaskUrl"]?>');"><img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/assigned.png" onmouseover="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/assigned_over.png'" onmouseout="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/assigned.png'"  alt="Benutzer zuweisen" title="Benutzer zuweisen" /></a><?
									}
									if( isset($dataArray["data"][$i][$a]["showReport"]) ){
										?><a href="<?=$SHARED_HTTP_ROOT;?>de/berichte/kundenstandorte.php5?<?=SID;?>&location=<?=$dataArray["data"][$i][$a]["showReport"];?>" target="_blank"><img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/report.png" onmouseover="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/report_over.png'" onmouseout="this.src='<?=$SHARED_HTTP_ROOT;?>pics/gui/report.png'"  alt="Bericht anzeigen" title="Bericht anzeigen" /></a><?
									}
								}?>
								&#160;
							</td>
					<?}?>
					</tr>
			<?	}?>
			<?if($errorText != ""){?>
				<tr>
					<td></td>
					<td colspan="<?=count($dataArray["datahead"])?>"><?=$errorText?><br /><br /></td>
				</tr>
			<?}?>
			</table>
		<?}
	}
} // NCASList



// -----------------------------------------------------------------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------------------------------------------------------------



/***************************************************************************
 * Listen-Verwaltungs Klasse ListManager
 * 
 * @access   	abstract
 * @author   	Johannes Glaser <j.glaser@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class ListManager{
	
	/***************************************************************************
	 * Variablen
	 ***************************************************************************/
	private static $listManagerID=0;
	private $listID;
	private $listArray;
	private $myObject;
	
	
	/***************************************************************************
	 * Konstruktor
	 ***************************************************************************/
	public function ListManager( $ListDataObject ){
		$this->myObject = $ListDataObject;
		$this->listID = $this->getListManagerID();
		self::$listManagerID++;
		
		$this->listArray["icon"] = $this->myObject->GetIcon();
		$this->listArray["icontext"] = $this->myObject->GetIconText();
		$this->listArray["datahead"] = $this->myObject->GetHeaders();
		$this->listArray["data"] = $this->myObject->GetData();
		$this->listArray["show"] = $this->myObject->GetShowArray();
	}
	
	
	/***************************************************************************
	 * SetListArray()
	 * setzt das ListArray des Objekts auf das übergebene Array
	 ***************************************************************************/
	public function SetListArray($newArray){
		$this->$listArray = $newArray;
	}
	
	
	/***************************************************************************
	 * GetListManagerID()
	 * gibt die Anzahl der aktiven Objekte zurück
	 ***************************************************************************/
	public function GetListManagerID(){
		return self::$listManagerID;
	}
	
	
	/***************************************************************************
	 * GetListID()
	 * gibt die Objekt_ID zurück
	 ***************************************************************************/
	public function GetListID(){
		return $this->listID;
	}
	
	
	/***************************************************************************
	 * PrintData()
	 * Editiert die Daten im Array und gibt Sie als Array zurück
	 ***************************************************************************/
	public function PrintData(){
		return $this->listArray;
	}
	
	/***************************************************************************
	 * SearchData()
	 * Funktion zum Abrufen der Daten, die in der Liste angezeigt werden sollen
	 ***************************************************************************/
	public function SearchData($searchString, $orderBy="", $orderDirection="ASC", $numEntrysPerPage=20, $currentPage=0){
		return $this->myObject->Search($searchString, $orderBy, $orderDirection, $numEntrysPerPage, $currentPage);
	}
		
	/***************************************************************************
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 * @access public
	 ***************************************************************************/
	public function GetNumTotalEntrys($searchString){
		return $this->myObject->GetNumTotalEntrys($searchString);
	}
		
	/***************************************************************************
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 ***************************************************************************/
	public function DeleteEntries($deleteArray){
		$this->myObject->DeleteEntries($deleteArray);
	}
		
	/***************************************************************************
	 * Klont die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 ***************************************************************************/
	public function CloneEntries($cloneArray){
		$this->myObject->CloneEntries($cloneArray);
	}
	
} // ListManager


// -----------------------------------------------------------------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------------------------------------------------------------



/***************************************************************************
 * Klasse mit Funktionen für die Datenklassen ListData
 * 
 * @access   	abstract
 * @author   	Johannes Glaser <j.glaser@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
abstract class ListData
{

	/***************************************************************************
	 * Variablen
	 ***************************************************************************/
	protected $options = array(	"icon" => "fehlt.png",
								"icontext" => "Listenname",
								"show_header" => true,
								"show_elements_all" => true,
								"show_elements_page" => true,
								"show_site_of_sites" => true,
								"show_search" => true,
						);
	
	protected $data = array( 	"datahead" => array(),
								"data" => array()
						);
	
	/**
	 * DBManager
	 * @var DBManager 
	 */
	protected $db = null;
	
	/**
	 * ExtendedLanguageManager
	 * @var ExtendedLanguageManager
	 */
	protected $languageManager = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 */
	public function ListData(DBManager $db, ExtendedLanguageManager $languageManager)
	{
		$this->db = $db;
		$this->languageManager = $languageManager;
	}
	
	/***************************************************************************
	* Funktion zum Abrufen der Daten, die in der Liste angezeigt werden sollen
	* @param string	$searchString 		Suchstring (leer bedeutet KEINE Suche)
	* @param string	$orderBy 			Sortiert nach Spalte
	* @param string	$orderDirection 		Sortier Richtung (ASC oder DESC)
	* @param int	$numEntrysPerPage 	Anzahl der Einträge pro Seite
	* @param int	$currentPage 		Angezeigte Seite
	* @return array	Das Rückgabearray muss folgendes Format haben:
	*			Array[index][rowIndex] = content 	mit 	index = laufvaribale von 0 bis  $numEntrysPerPage 
	*										rowIndex = Zugehörige Spalte siehe auch $this->data["datahead"]
	*										content = Ausgabetext oder URL-Array in der Format Array[urlType] = url mit urlType = [editUrl oder deleteUrl]
	* @access public
	 ***************************************************************************/
	abstract public function Search($searchString, $orderBy, $orderDirection="ASC", $numEntrysPerPage=20, $currentPage=0);
	
	/***************************************************************************
	 * Gibt die Gesamtanzahl der Einträge zurück
	 * @return int	Anzahl der Einträge
	 * @access public
	 ***************************************************************************/
	abstract public function GetNumTotalEntrys($searchString);
	
	
	/***************************************************************************
	 * GetHeaders()
	 ***************************************************************************/
	public function GetHeaders(){
		return $this->data["datahead"];
	}

	
	/***************************************************************************
	 * GetData()
	 ***************************************************************************/
	public function GetData(){
		return $this->data["data"];
	}
	
	
	/***************************************************************************
	 * SetData()
	 ***************************************************************************/
	public function SetData($datahead, $data){
		$this->data["datahead"] = $datahead;
		$this->data["data"] = $data;
	}
	
	
	/***************************************************************************
	 * GetIcon()
	 ***************************************************************************/
	 public function GetIcon(){
		return $this->options["icon"];
	 }
	 
	/***************************************************************************
	 * GetIconText()
	 ***************************************************************************/
	public function GetIconText(){
		return $this->options["icontext"];
	}
	
	/***************************************************************************
	 * GetShowArray
	 ***************************************************************************/
	public function GetShowArray(){
		$show["show_elements_all"] = $this->options["show_elements_all"];
		$show["show_elements_page"] = $this->options["show_elements_page"];
		$show["show_site_of_sites"] = $this->options["show_site_of_sites"];
		$show["show_search"] = $this->options["show_search"];
		$show["show_header"] = $this->options["show_header"];		
		return $show;
	}
	
	/***************************************************************************
	 * Setzt die Optionen
	 * @access public
	 ***************************************************************************/
	public function SetOptions($array_options){
		$this->options = $array_options;
	}
	
	/***************************************************************************
	 * Löscht die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 ***************************************************************************/
	abstract public function DeleteEntries($deleteArray);
	
	/***************************************************************************
	 * Klont die Einträge anhand der im Array vorhandenen PKEY's
	 * @access public
	 ***************************************************************************/
	public function CloneEntries($cloneArray){}
	
} // ListData

?>