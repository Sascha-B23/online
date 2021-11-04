<script type="text/javascript">
	var keyPressed=false;
	var keyBuffer=new Array();
	var BrowserName=false;
	var BrowserVersion=false;
	var cursorProperty="pointer";
	var iconPath="../pics/icons/";
	var tmp;
	
	function TasteGedrueckt(Ereignis){
		if(navigator.appName != "Netscape" && top.main!=undefined){
			top.main.document.fireEvent("onkeydown",event);
		}

		if (navigator.appName == "Netscape"){
			key=Ereignis.which;
		}else{
			key=event.keyCode;
		}
		// alert(key);
		// Auswerten
		switch(key){
			case 46: break; // ENTF
			case 27: break; // ESC
			case 38: break; // UP
			case 40: break; // DOWN
		}
		
		if (key == 78 && ifHoldKey(17)){ 	//STRG+N
		}
		
		if (key == 82 && ifHoldKey(17)){ 	//STRG+R
		}
		
		if (key == 83 && ifHoldKey(17)){	//STRG+S
		}
		
		if (key == 67 && ifHoldKey(17)){	//STRG+C
		}	
		
		if (key == 88 && ifHoldKey(17)){	//STRG+X
		}	
		
		if (key == 86 && ifHoldKey(17)){	//STRG+V
		}
		
		if (key == 65 && ifHoldKey(17) && ifHoldKey(16)){	//STRG+SHIFT+V
		}
		
		if (key == 65 && ifHoldKey(17) && !ifHoldKey(16)){	//STRG+A
		}
		
		if(!ifHoldKey(key))keyBuffer[keyBuffer.length]=key;
		keyPressed=true;

		return true;
	}  

	
	
	function TasteLosgelassen(Ereignis){
		if(navigator.appName != "Netscape"  && top.main!=undefined){
			top.main.document.fireEvent("onkeyup",event);
		}

		if (navigator.appName == "Netscape")
			key=Ereignis.which;
		else
			key=event.keyCode;
		for(var a=0;a<keyBuffer.length;a++)
		{
			if(keyBuffer[a]==key){
				keyBuffer[a]=-1;
			}
		}
		cleanKeyBuffer();
		if(!anyKeyPressed())
			keyPressed=false;
			
		return true;
	}
	
	function cleanKeyBuffer(){
		temp=new Array();
		count=0;
		for(var a=0;a<keyBuffer.length;a++){
			if(keyBuffer[a]!=-1){
				temp[count]=keyBuffer[a];
				count++;
			}
		}
		keyBuffer=temp;
	}

	function ifHoldKey(keyID){
		for(var a=0;a<keyBuffer.length;a++){
			if(keyBuffer[a]==keyID){
				return true;
			}
		}
		return false;
	}

	function anyKeyPressed(){
		for(var a=0;a<keyBuffer.length;a++){
			if(keyBuffer[a]!=-1){
				return true;
			}
		}
		return false;
	}
	
	function deselectSelection(){
		if(BrowserName=="IE" && BrowserVersion>=4)
			document.selection.empty();
	}
	
	
	/*********************************************
	* Funktion zum Selected Items Array zu setzen
	* bzw. zu ergänzen
	* @ param 	String ID der <tr>
	*********************************************/
	var selectedItems = new Array();
	var selectedIDs = new Array();
	var tableclicked = false;
	var context = false;
	var deletableItems = false;
	var deletableItemsArray1 = new Array(); // ID's
	var deletableItemsArray2 = new Array(); // True / False
		
	function setTableClickedFasle(){ tableclicked = false; }
	
	function selectItem(tableRowID){
		
		if( document.getElementById(tableRowID) == undefined && tableRowID != "CLEAR_ARRAYS"){ return false; }
		if( tableRowID != false ){
			if(tableRowID == "CLEAR_ARRAYS"){
				markTables(false);
				for( var i=0; i<selectedItems.length; i++){
					selectedItems.splice(i,1);
					selectedIDs.splice(i,1);
				}
				i=0;
				while(selectedItems.length > 0){
					selectedItems.splice(i,1);
				}
				i=0;
				while(selectedIDs.length > 0){
					selectedIDs.splice(i,1);
				}
			}else{				
				if( ifHoldKey(17) ){ // STRG gedrückt oder Contextmenü Aufruf => hinzufügen
					var elem = document.getElementById(tableRowID);
					var check = true;
					markTables(false);
					for(var i=0; i<selectedItems.length; i++){
						if(selectedItems[i] == elem){			
							deletableItems = false;
							check = false;
							selectedItems.splice(i,1);
							selectedIDs.splice(i,1);
						}				
					}
					if(check != false){
						selectedItems[selectedItems.length] = elem;
						selectedIDs[selectedIDs.length] = tableRowID;
					}
					markTables(true);
				}else{ // STRG nicht gedrückt => neu
					deletableItems = false;
					markTables(false);
					for( var i = 0; i<selectedItems.length; i++){
						selectedItems.splice(i,1);
						selectedIDs.splice(i,1);
					}
					i=0;
					while(selectedItems.length > 0){
						selectedItems.splice(i,1);
					}
					i=0;
					while(selectedIDs.length > 0){
						selectedIDs.splice(i,1);
					}
					selectedItems[0] = document.getElementById(tableRowID);
					selectedIDs[0] = tableRowID;
					markTables(true);
				}
			}
			//document.getElementById("console").innerHTML ="";
			//for(var i=0; i<selectedItems.length; i++){
			//	document.getElementById("console").innerHTML += selectedIDs[i];
			//}
		}
	}
	
	function markTables(paint){
		if(paint ==  true){
			for( var i=0; i<selectedIDs.length; i++){
				if( selectedIDs[i] != "" &&typeof document.getElementById( selectedIDs[i] ) != undefined ){
					document.getElementById( selectedIDs[i] ).style.backgroundColor = "#FFFF9B";
				}
			}
		}else{
			for( var i=0; i<selectedIDs.length; i++){
				if( selectedIDs[i] != "" && typeof document.getElementById( selectedIDs[i] ) != undefined ){
					document.getElementById( selectedIDs[i] ).style.backgroundColor = "#FFFFFF";
				}
			}
		}
	}
					
	var cooldown;
	function onloadFunction(){
		cooldown = window.setInterval("setTableClickedFasle()", 10); 
		// Browser ermitteln
		// IE
		if (navigator.appName.match(/Microsoft/ig)){
			BrowserName="IE";
			BrowserVersion=navigator.appVersion.match(/MSIE ([0-9])/);
			BrowserVersion=BrowserVersion[1];
		}
		// NS
		if (navigator.appName.match(/Netscape/ig)){
			BrowserName="NS";
			BrowserVersion=navigator.appVersion.substr(0,1);
		}
	}
	
	function GetIDs(){
		var ids = "";
		for(var i=0; i<selectedIDs.length; i++){
			if( i == 0 ){
				ids += selectedIDs[i];
			}else{
				ids += "|" + selectedIDs[i];
			}
		}
		return ids;
	}
			
	
	/////////////////////////
	// DOKUMENT EVENTS
	// Folgende werden bereits definiert:
	// document.oncontextmenu = Edit_This;
	// document.onload = onloadFunction();
	
	document.onkeydown = TasteGedrueckt;
	document.onkeyup = TasteLosgelassen;
	document.onclick = function(Ereignis){
		if (!Ereignis)
			Ereignis = window.event;
		// Wenn nicht Rechtsklick
		if(Ereignis.button != 2){
			if( tableclicked != true ){
				selectItem("CLEAR_ARRAYS");
			}
			if( ifHoldKey(17) ){
				return false;
			}
		}
	}
	
	// DOKUMENT EVENTS
	/////////////////////////
</script>