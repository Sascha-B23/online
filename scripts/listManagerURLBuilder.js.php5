<script type="text/javascript">
// Funktion zum Bauen der Delete URL
function createDeleteUrl(Formname){
	
	if( typeof(Formname) == "undefined" )Formname = "";
	
	var a = GetIDs(); // Selektierte ID's abfragen
	
	if(a != ""){
		document.getElementById("deleteElements").value = a;
		
		var URL_all = "&deleteSelected=" + a;
		
		if( Formname == "" || document.getElementById("deleteElements") == null ){
			if( a.indexOf("|") != -1 ){
				var deleteURL = "javascript:showDialogWindow(263, 163, "+
								"'url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_mitte_links.gif)', " +
								"'Warnhinweis', " +
								"'Möchten Sie die Einträge wirklich löschen?', " + 
								"'Ja, Einträge löschen', " +
								"'?<?=SID?>" + URL_all + "', '', '', '', '');";
			}else{
				var deleteURL = "javascript:showDialogWindow(263, 163, "+
								"'url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_mitte_links.gif)', " +
								"'Warnhinweis', " +
								"'Möchten Sie den Eintrag wirklich löschen?', " + 
								"'Ja, Eintrag löschen', " +
								"'?<?=SID?>" + URL_all + "', '', '', '', '');";
			}
			document.location = deleteURL;
		}else{
			if( a.indexOf("|") == -1 ){
				showDialogWindow(263, 163, 'url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_mitte_links.gif)', 'Warnhinweis', 'Möchten Sie den Eintrag wirklich löschen?', 'Ja, Eintrag löschen', "javascript:document.getElementById(\\\"" + Formname + "\\\").submit();", '', '', '', '');
			}else{
				showDialogWindow(263, 163, 'url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_mitte_links.gif)', 'Warnhinweis', 'Möchten Sie die Einträge wirklich löschen?', 'Ja, Einträge löschen', "javascript:document.getElementById(\\\"" + Formname + "\\\").submit();", '', '', '', '');
			}
		}
		
	}else{
		var deleteURL = "#";
	}
	
}

function createCloneUrl(Formname){
	if( typeof(Formname) == "undefined" ||  document.getElementById(Formname) == null )Formname = "";
	
	var a = GetIDs(); // Selektierte ID's abfragen
	
	if(a != ""){
		document.getElementById("cloneElements").value = a;
		var URL_all = "&cloneSelected=" + a;
		var cloneURL = document.location + URL_all;
		
		if( Formname == "" || document.getElementById("cloneElements") == null ){
			document.location = cloneURL;
		}else{
			//document.location = cloneURL;
			document.getElementById(Formname).submit();
		}
		
	}else{
		var deleteURL = "#";
	}
	
}

function createPlanUrl(Formname){
	if( typeof(Formname) == "undefined" ||  document.getElementById(Formname) == null )Formname = "";
	
	var a = GetIDs(); // Selektierte ID's abfragen
	
	if(a != ""){
		document.getElementById("planElements").value = a;
		var URL_all = "&taskToPlan=" + a;
		var cloneURL = document.location + URL_all;
		
		if( Formname == "" || document.getElementById("planElements") == null ){
			document.location = cloneURL;
		}else{
			//document.location = cloneURL;
			document.getElementById(Formname).submit();
		}
		
	}else{
		var deleteURL = "#";
	}
	
}

function createAssignUrl(Formname){
	if( typeof(Formname) == "undefined" ||  document.getElementById(Formname) == null )Formname = "";
	
	var a = GetIDs(); // Selektierte ID's abfragen
	
	if(a != ""){
		document.getElementById("assignElements").value = a;
		var URL_all = "&assignElements=" + a;
		var cloneURL = document.location + URL_all;
		
		if( Formname == "" || document.getElementById("assignElements") == null ){
			document.location = cloneURL;
		}else{
			//document.location = cloneURL;
			document.getElementById(Formname).submit();
		}
		
	}else{
		var deleteURL = "#";
	}
}

</script>
