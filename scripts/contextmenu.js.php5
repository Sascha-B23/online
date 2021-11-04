<?php ?>
<script type="text/javascript">
// Ermittlung von Browsergestützten Befehlen
ie = (document.getElementById && document.all && document.styleSheets) ? 1:0;
nn = (document.getElementById && !document.all) ? 1:0;

// Contextmenü erzeugen
if(ie || nn) {
	var width = 60;
	var height = 100;
	var status = 0;
	var timeout_contextmenu = new Array();
	var spacer = "&nbsp;&nbsp;";
	var show_contextmenu = false;
	var onfocus = "onfocus='if(this.blur)this.blur()'"; 
	var linkstyle = 'style="text-decoration:none; font-weight:bold;"';
	
	
	// Aussehen und Funktion im Kontextmenü
	document.write(
				"<div id='context_menu' style='position:absolute;top:-250;left:0;z-index:100;visibility:hidden;' onmouseover='status=2' onmouseout='status=1; /*timeout_contextmenu[2] = setTimeout(\"hideMenu()\", 1250)*/'>" +
					"<table onmouseover='status=2' onclick='tableclicked=true;' cellpadding='0' cellspacing='0' border='0' style='width:150px; border:1px solid #EAEAEA; background-color:#E8E8E8;'>" +
					/* Bearbeiten */
					"<tr id='contextmenu_edit_td'>"+
						"<td onmouseover='status=2' style='height:28px;'><img src='../../pics/gui/edit.png' alt=''></td>"+
						"<td onmouseover='status=2' style='height:28px;' align='left'><a id='contextmenu_edit_link' " + linkstyle + " onmouseover='status=2' href='' " + onfocus + ">&nbsp;Bearbeiten</a></td>"+
					"</tr>" +
					"<tr id='contextmenu_space_td' style='height:1px;' onmouseover='status=2'>"+
						"<td colspan='2' style='height:1px; background-color:#ffffff;'></td>"+
					"</tr>" +
					/* Klonen */
					"<tr id='contextmenu_clone_td' onmouseover='status=2'>"+
						"<td onmouseover='status=2' style='height:28px;'><img src='../../pics/gui/clone.png' alt=''></td>"+
						"<td onmouseover='status=2' style='height:28px;' align='left'><a id='contextmenu_clone_link' " + linkstyle + " onmouseover='status=2' href='' " + onfocus + ">&nbsp;Klonen</a></td>"+
					"</tr>" +
					"<tr id='contextmenu_space_td' style='height:1px;' onmouseover='status=2'>"+
						"<td colspan='2' style='height:1px; background-color:#ffffff;'></td>"+
					"</tr>" +
					/* Planen */
					"<tr id='contextmenu_plan_td' onmouseover='status=2'>"+
						"<td onmouseover='status=2' style='height:28px;'><img src='../../pics/gui/planTask.png' alt=''></td>"+
						"<td onmouseover='status=2' style='height:28px;' align='left'><a id='contextmenu_plan_link' " + linkstyle + " onmouseover='status=2' href='' " + onfocus + ">&nbsp;Planen</a></td>"+
					"</tr>" +
					"<tr id='contextmenu_space_td_plan' style='height:1px;' onmouseover='status=2'>"+
						"<td colspan='2' style='height:1px; background-color:#ffffff;'></td>"+
					"</tr>" +
					/* Löschen */
					"<tr id='contextmenu_delete_td' onmouseover='status=2'>"+
						"<td onmouseover='status=2' style='height:28px;'><img src='../../pics/gui/cancel.png' alt=''></td>"+
						"<td onmouseover='status=2' align='left'><a id='contextmenu_delete_link' " + linkstyle + " onmouseover='status=2' href='' " + onfocus + ">&nbsp;L&ouml;schen</a></td>"+
					"</tr>" +
					/* Benutzer zuweisen */
					"<tr id='contextmenu_space_td_assign' style='height:1px;' onmouseover='status=2'>"+
						"<td colspan='2' style='height:1px; background-color:#ffffff;'></td>"+
					"</tr>" +
					"<tr id='contextmenu_assign_td' onmouseover='status=2'>"+
						"<td onmouseover='status=2' style='height:28px;'><img src='../../pics/gui/assigned.png' alt=''></td>"+
						"<td onmouseover='status=2' align='left'><a id='contextmenu_assign_link' " + linkstyle + " onmouseover='status=2' href='' " + onfocus + ">&nbsp;Benutzer zuweisen</a></td>"+
					"</tr>" +
					/* Phone */
					"<tr id='contextmenu_space_td_phone' style='height:1px;' onmouseover='status=2'>"+
						"<td colspan='2' style='height:1px; background-color:#ffffff;'></td>"+
					"</tr>" +
					"<tr id='contextmenu_phone_td' onmouseover='status=2'>"+
						"<td onmouseover='status=2' style='height:28px;'><img src='../../pics/gui/call_b.png' alt=''></td>"+
						"<td onmouseover='status=2' align='left'><a id='contextmenu_phone_link' " + linkstyle + " onmouseover='status=2' href='' " + onfocus + ">&nbsp;Telefontermin</a></td>"+
					"</tr>" +
					/* Prozess-Bearbeitungsstatus */
					"<tr id='contextmenu_space_td_editstate' style='height:1px;' onmouseover='status=2'>"+
						"<td colspan='2' style='height:1px; background-color:#ffffff;'></td>"+
					"</tr>" +
					"<tr id='contextmenu_editstate_td' onmouseover='status=2'>"+
						"<td onmouseover='status=2' style='height:28px;'><img src='../../pics/gui/activeTask.png' alt=''></td>"+
						"<td onmouseover='status=2' align='left'><a id='contextmenu_editstate_link' " + linkstyle + " onmouseover='status=2' href='' " + onfocus + ">&nbsp;Bearbeitungsstatus</a></td>"+
					"</tr>" +
					
					"</table>"+
				"</div>"
				  );
				   
	// Funktionen definieren
	document.oncontextmenu = showMenu;
	// document.onmouseup = hideMenu;
}

// Funktion zum Einstellen des Inhaltes
// ID MUSS 1 SEIN !!!
function set_context_menu( edit, edit_href, del, del_href, clone, clone_href, plan, plan_href, phone, phone_href, assign, assign_href, editstate, editstate_href, id ){
	if(typeof(document.getElementById("context_menu")) != "undefined"){
		if(id != false){
			show_contextmenu = true;
			
			if(deletableItems != true){ deletableItems = del; }
			
			var edit_td = document.getElementById("contextmenu_edit_td");
			var edit_link = document.getElementById("contextmenu_edit_link");
			
			var clone_td = document.getElementById("contextmenu_clone_td");
			var clone_link = document.getElementById("contextmenu_clone_link");
			
			var plan_td = document.getElementById("contextmenu_plan_td");
			var plan_link = document.getElementById("contextmenu_plan_link");
			var plan_spacer = document.getElementById("contextmenu_space_td_plan");
			
			var assign_td = document.getElementById("contextmenu_assign_td");
			var assign_link = document.getElementById("contextmenu_assign_link");
			var assign_spacer = document.getElementById("contextmenu_space_td_assign");
			
			var del_td = document.getElementById("contextmenu_delete_td");
			var del_link = document.getElementById("contextmenu_delete_link");
			
			var phone_td = document.getElementById("contextmenu_phone_td");
			var phone_link = document.getElementById("contextmenu_phone_link");
			var phone_spacer = document.getElementById("contextmenu_space_td_phone");
			
			var editstate_td = document.getElementById("contextmenu_editstate_td");
			var editstate_link = document.getElementById("contextmenu_editstate_link");
			var editstate_spacer = document.getElementById("contextmenu_space_td_editstate");
			
			var spacer = document.getElementById("contextmenu_space_td");
			
			var a = GetIDs();
			if( a.indexOf("|") == -1 ){
				if(edit != false || del != false){
					spacer.style.display="table-row";
				}else{
					spacer.style.display="none";
				}
				
				if(edit == false){ 
					edit_td.style.display="none";
				}else{ 
					edit_td.style.display="table-row";
					edit_link.href = edit_href;
				}
				
				if(clone == false){ 
					clone_td.style.display="table-row";
					clone_link.style.color = "#999999";
					clone_link.href = "javascript:void(0);";
				}else{ 
					clone_td.style.display="table-row";
					clone_link.style.color = "#666666";
					clone_link.href = "javascript:createCloneUrl('"+clone_href+"');";
				}
				
				if(plan == false){
					plan_td.style.display = "none";
					plan_link.style.color = "#999999";
					plan_link.href = "javascript:void(0);";
					plan_spacer.style.display = "none";
				}else{
					plan_td.style.display = "table-row";
					plan_link.style.color = "#666666";
					plan_link.href = "javascript:createPlanUrl('"+plan_href+"');";
					plan_spacer.style.display = "table-row";
				}
				
				if(assign == false){
					assign_td.style.display = "none";
					assign_link.style.color = "#999999";
					assign_link.href = "javascript:void(0);";
					assign_spacer.style.display = "none";
				}else{
					assign_td.style.display = "table-row";
					assign_link.style.color = "#666666";
					assign_link.href = "javascript:createAssignUrl('"+assign_href+"');";
					assign_spacer.style.display = "table-row";
				}
				
				if(phone == false){
					phone_td.style.display = "none";
					phone_link.style.color = "#999999";
					phone_link.href = "javascript:void(0);";
					phone_spacer.style.display = "none";
				}else{
					phone_td.style.display = "table-row";
					phone_link.style.color = "#666666";
					phone_link.href = phone_href;
					phone_spacer.style.display = "table-row";
				}
				
				if(editstate == false){
					editstate_td.style.display = "none";
					editstate_link.style.color = "#999999";
					editstate_link.href = "javascript:void(0);";
					editstate_spacer.style.display = "none";
				}else{
					editstate_td.style.display = "table-row";
					editstate_link.style.color = "#666666";
					editstate_link.href = editstate_href;
					editstate_spacer.style.display = "table-row";
				}
				
				if(del == false){ 
					del_td.style.display="table-row";
					del_link.style.color = "#999999";
					del_link.href = "javascript:void(0);";
				}else{ 
					del_td.style.display="table-row";
					del_link.style.color = "#666666";
					del_link.href = "javascript:createDeleteUrl('"+del_href+"');";
				}
			}else{
			
				spacer.style.display="table-row";
				edit_td.style.display="table-row";
				edit_link.style.color = "#999999";
				edit_link.href = "javascript:void(0);";
				
				if( clone == false ){
					// Link deaktiviert
					clone_td.style.display="table-row";
					clone_link.style.color = "#999999";
					clone_link.href = "javascript:void(0);";
				}else{
					// Link Aktiviert
					clone_td.style.display="table-row";
					clone_link.style.color = "#666666";
					clone_link.href = "javascript:createCloneUrl('"+clone_href+"');";
				}
				
				if(plan == false){
					plan_td.style.display = "none";
					plan_link.style.color = "#999999";
					plan_link.href = "javascript:void(0);";
					plan_spacer.style.display = "none";
				}else{
					plan_td.style.display = "table-row";
					plan_link.style.color = "#666666";
					plan_link.href = "javascript:createPlanUrl('"+plan_href+"');";
					plan_spacer.style.display = "table-row";
				}
				
				if(assign == false){
					assign_td.style.display = "none";
					assign_link.style.color = "#999999";
					assign_link.href = "javascript:void(0);";
					assign_spacer.style.display = "none";
				}else{
					assign_td.style.display = "table-row";
					assign_link.style.color = "#666666";
					assign_link.href = "javascript:createAssignUrl('"+assign_href+"');";
					assign_spacer.style.display = "table-row";
				}
				
				if(phone != false){
					//phone_td.style.display = "none";
					phone_link.style.color = "#999999";
					phone_link.href = "javascript:void(0);";
					//phone_spacer.style.display = "none";
				}
				
				if(editstate != false){
					//editstate_td.style.display = "none";
					editstate_link.style.color = "#999999";
					editstate_link.href = "javascript:void(0);";
					//editstate_spacer.style.display = "none";
				}
					
				
				if( deletableItems == false ){
					// Link deaktiviert
					del_td.style.display="table-row";
					del_link.style.color = "#999999";
					del_link.href = "javascript:void(0);";
				}else{
					// Link Aktiviert
					del_td.style.display="table-row";
					del_link.style.color = "#666666";
					del_link.href = "javascript:createDeleteUrl('"+del_href+"');";
				}
			}

		}else{
			show_contextmenu = false;
			return false;
		}
	}
	
	//var a = GetIDs();
	//if( a.indexOf("|") != -1 ){
		// NEUER AUFRUF
	//	spacer.style.display="none";
	//	edit_td.style.display="none";
	//	del_td.style.display="table-row";
		//del_link.href = "?onlyDelete=" + a;
	//	del_link.href = "?bla_blupp="+a;
		//alert("?multiDelete: " + a);
	//}
}

// Anzeigen des Menüs
function showMenu(e){
	if(typeof(document.getElementById("context_menu")) == "undefined") return false;
	if(ie){
		if( event.clientX > width ){
			xPos = event.clientX - width + document.body.scrollLeft;
		}else{
			xPos = event.clientX + document.body.scrollLeft;
		}
		
		if( event.clientY > height ){
			yPos = event.clientY - height + document.body.scrollTop;
		}else{
			yPos = event.clientY + document.body.scrollTop;
		}
	}else{
		if( e.pageX > width + window.pageXOffset ){
			xPos = e.pageX - width;
		}else{
			xPos = e.pageX;
		}

		if( e.pageY > height + window.pageYOffset ){
			yPos = e.pageY - height;
		}else{
			yPos = e.pageY;
		}
	}
	document.getElementById("context_menu").style.left = 45+xPos+"px";
	document.getElementById("context_menu").style.top = 85+yPos+"px";
	if(show_contextmenu){
		clearTimeout(timeout_contextmenu[0]);
		clearTimeout(timeout_contextmenu[1]);
		clearTimeout(timeout_contextmenu[2]);
		document.getElementById("context_menu").style.visibility="visible";
		status = 1;
		
		// WICHTIG!!! NICHT LÖSCHEN !!! Notwendig zur korrekten Darstellung in allen Browsern
		show_contextmenu = false; 
		// ENDE WICHTIG!!! NICHT LÖSCHEN !!!
		
		return false;
	}
}

// Menü verstecken
function hideMenu(){
	//if( status == 1 ){
        /* timeout_contextmenu[0] = setTimeout("document.getElementById('context_menu').style.top=-250", 15);
		timeout_contextmenu[1] = setTimeout("document.getElementById('context_menu').style.visibility='hidden'", 15);
        status = 0; */
	//}
	document.getElementById('context_menu').style.top=-250;
	document.getElementById('context_menu').style.visibility='hidden';
	status = 0;
	return false;
}

// BODY: oncontextmenu="set_context_menu( false, '#', false, '#', 0 ); showMenu;"
// ELEM: oncontextmenu="set_context_menu( true, '?edit=1', true, '?del=1', 1 ); showMenu;"
</script>
