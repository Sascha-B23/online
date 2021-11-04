
<!-- Aufbau des Popups -->
<div id="popup_div" style="width:100%; height:100%; background:url(<?=$SHARED_HTTP_ROOT?>pics/dialog/background.png); background-repeat:repeat; visibility:hidden; z-index:999; position:absolute; top:-5000px;">
	<table border="0" cellpadding="0" cellspacing="0" align="center" style="position:relative; top:300px;">
		<tr>
			<td style="width:95px; height:29px; background:url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_oben_links.gif);">
				<span id="popup_headline" style="position:relative; left:15px; top:4px; color:#ffffff; font-weight:bold;"></span>
			</td>
			<td style="height:29px; background:url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_wdh_oben.gif); background-repeat:repeat-x;"></td>
			<td style="width:13px; height:29px; background:url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_oben_rechts.gif);"></td>
		</tr>
		<tr>
			<td id="popup_text_icon" style="width:95px; background:url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_mitte_links.gif);"></td>
			<td id="popup_text_td" style="background:url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_mitte_mitte.gif);">
				<span id="popup_text" style="color:#ffffff; font-weight:bold; font-size:12px;"></span>
			</td>
			<td style="width:13px; background:url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_wdh_rechts.gif); background-repeat:repeat-y;"></td>
		</tr>
		<tr>
			<td style="width:95px; height:17px; background:url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_unten_links.gif);"></td>
			<td style="height:17px; background:url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_wdh_unten.gif); background-repeat:repeat-x;"></td>
			<td style="width:13px; height:17px; background:url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_unten_rechts.gif);"></td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	/*******************************************************************************
	* Funktion zum Anzeigen des Dialogfensters
	* @param:	int		width			Breite Content
	*			int		height			Höhe Content
	*			string 	icon			url(Pfad zum Bild)
	* 			string 	headline		Headline des Popups
	*			string 	text			Meldungstext
	*			string 	option1text		Auszuführende Optionen Text
	*			string 	option1action	Auszuführende Optionen Link
	*			string 	option2text		Auszuführende Optionen
	*			string 	option2action	Auszuführende Optionen Link
	*			string 	option3text		Auszuführende Optionen
	*			string 	option3action	Auszuführende Optionen Link
	* @aufruf: 	z.B. <a href="javascript:showDialogWindow(263, 163, 'url(<?=$SHARED_HTTP_ROOT?>pics/dialog/dialog_mitte2_links.gif)', 'Warnhinweis', 'Möchten Sie den Eintrag wirklich löschen', 'ja', '?a=a', 'nein', '?a=b', 'vielleicht', '?a=c');" >show</a>
	*******************************************************************************/
	var innerHTMLContentDiv;
	function showDialogWindow( width, height, icon, headline, text, option1text, option1action, option2text, option2action, option3text, option3action ){
		document.getElementById("popup_text_td").style.height = height+"px";
		document.getElementById("popup_text_td").style.width = width+"px";
		
		document.getElementById("popup_text_icon").style.background = icon;
	
		document.getElementById("popup_div").style.visibility = "visible";
		document.getElementById("popup_div").style.top = "0px";
		
		innerHTMLContentDiv = "";
		if(text != ""){
			innerHTMLContentDiv += "<span style='color:#353334; position:relative; left:-11px;'>";
			innerHTMLContentDiv += text;
			innerHTMLContentDiv += "</span>";
			innerHTMLContentDiv += "<br /><br />";
			innerHTMLContentDiv += "<table border='0' cellpadding='0' cellspacing='0'>";
			if(option1text != '' && option1action != ''){
				innerHTMLContentDiv += "<tr style='cursor:pointer;cursor:hand;' onclick='document.location=\"";
				innerHTMLContentDiv += option1action;
				innerHTMLContentDiv += "\"'";
				innerHTMLContentDiv += " onmouseover='document.getElementById(\"popup_text_img_1\").src=\"<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_aktiv.png\"; document.getElementById(\"popup_text_txt_1\").style.color=\"#CA0032\"'";
				innerHTMLContentDiv += " onmouseout='document.getElementById(\"popup_text_img_1\").src=\"<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_normal.png\"; document.getElementById(\"popup_text_txt_1\").style.color=\"#353334\"'";
				innerHTMLContentDiv += ">";
				innerHTMLContentDiv += "<td>";
				innerHTMLContentDiv += "<img id='popup_text_img_1' src='<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_normal.png' alt='' />";
				innerHTMLContentDiv += "</td>";
				innerHTMLContentDiv += "<td id='popup_text_txt_1' style='color:#353334' align='left'>&#160;";
				innerHTMLContentDiv += option1text;
				innerHTMLContentDiv += "</td>";
				innerHTMLContentDiv += "</tr><tr style='height:6px;'><td></td><td></td></tr>";
			}
			if(option2text != '' && option2action != ''){
				innerHTMLContentDiv += "<tr style='cursor:pointer;cursor:hand;' onclick='document.location=\"";
				innerHTMLContentDiv += option2action;
				innerHTMLContentDiv += "\"'";
				innerHTMLContentDiv += " onmouseover='document.getElementById(\"popup_text_img_2\").src=\"<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_aktiv.png\"; document.getElementById(\"popup_text_txt_2\").style.color=\"#CA0032\"'";
				innerHTMLContentDiv += " onmouseout='document.getElementById(\"popup_text_img_2\").src=\"<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_normal.png\"; document.getElementById(\"popup_text_txt_2\").style.color=\"#353334\"'";
				innerHTMLContentDiv += ">";
				innerHTMLContentDiv += "<td>";
				innerHTMLContentDiv += "<img id='popup_text_img_2' src='<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_normal.png' alt='' />";
				innerHTMLContentDiv += "</td>";
				innerHTMLContentDiv += "<td id='popup_text_txt_2' style='color:#353334' align='left'>&#160;";
				innerHTMLContentDiv += option2text;
				innerHTMLContentDiv += "</td>";
				innerHTMLContentDiv += "</tr><tr style='height:6px;'><td></td><td></td></tr>";
			}
			if(option3text != '' && option3action != ''){
				innerHTMLContentDiv += "<tr style='cursor:pointer;cursor:hand;' onclick='document.location=\"";
				innerHTMLContentDiv += option3action;
				innerHTMLContentDiv += "\"'";
				innerHTMLContentDiv += " onmouseover='document.getElementById(\"popup_text_img_3\").src=\"<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_aktiv.png\"; document.getElementById(\"popup_text_txt_3\").style.color=\"#CA0032\"'";
				innerHTMLContentDiv += " onmouseout='document.getElementById(\"popup_text_img_3\").src=\"<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_normal.png\"; document.getElementById(\"popup_text_txt_3\").style.color=\"#353334\"'";
				innerHTMLContentDiv += ">";
				innerHTMLContentDiv += "<td>";
				innerHTMLContentDiv += "<img id='popup_text_img_3' src='<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_normal.png' alt='' />";
				innerHTMLContentDiv += "</td>";
				innerHTMLContentDiv += "<td id='popup_text_txt_3' style='color:#353334' align='left'>&#160;";
				innerHTMLContentDiv += option3text;
				innerHTMLContentDiv += "</td>";
				innerHTMLContentDiv += "</tr><tr style='height:6px;'><td></td><td></td></tr>";
			}
			innerHTMLContentDiv += "<tr style='cursor:pointer;cursor:hand;' onclick='";
			innerHTMLContentDiv += "javascript:hideDialogWindow();";
			innerHTMLContentDiv += "'";
			innerHTMLContentDiv += " onmouseover='document.getElementById(\"popup_text_img_4\").src=\"<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_aktiv.png\"; document.getElementById(\"popup_text_txt_4\").style.color=\"#CA0032\"'";
			innerHTMLContentDiv += " onmouseout='document.getElementById(\"popup_text_img_4\").src=\"<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_normal.png\"; document.getElementById(\"popup_text_txt_4\").style.color=\"#353334\"'";
			innerHTMLContentDiv += ">";
			innerHTMLContentDiv += "<td>";
			innerHTMLContentDiv += "<img id='popup_text_img_4' src='<?=$SHARED_HTTP_ROOT?>pics/dialog/pfeil_normal.png' alt='' />";
			innerHTMLContentDiv += "</td>";
			innerHTMLContentDiv += "<td id='popup_text_txt_4' style='color:#353334'>&#160;";
			innerHTMLContentDiv += "Vorgang abbrechen";
			innerHTMLContentDiv += "</td>";
			innerHTMLContentDiv += "</tr>";
			innerHTMLContentDiv += "</table>";
			
		}

		document.getElementById("popup_headline").innerHTML = headline;
		document.getElementById("popup_text").innerHTML = innerHTMLContentDiv;
		
		adjustLoading();
	}
	
	// Funktion zum Schließen des Dialogfensters
	function hideDialogWindow(){
		// Aus Sichtbereich entfernen
		document.getElementById("popup_div").style.visibility = "hidden";
		document.getElementById("popup_div").style.top = "-5000px";
		
		//Inhalt leeren
		document.getElementById("popup_headline").innerHTML = "";
		document.getElementById("popup_text").innerHTML = "";
	}
	
	// Scrollfunktionen beim Dialogfenster
	var loadDiv;
	function adjustLoading(){
		yscroll=window.pageYOffset;

		if(yscroll == undefined){
			yscroll=document.body.scrollTop;
		}
		loadDiv=document.getElementById("popup_div");

		if (loadDiv.style.visibility=="visible"){
			loadDiv.style.top = yscroll+"px";
		}
	}
	
	
	window.onscroll = function(){
		adjustLoading();
	}
	
	
</script>