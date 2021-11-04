<?php
/**
 * Basisklasse für Elemente welche eine dynamische Befüllung per JavaScript erfordern
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class DynamicContent 
{
	
	private static $dynamicContentID=0;
	protected $id = 0;
	protected $url = "";
	protected $parameter = "";
	protected $initScriptCode = "";
	
	/**
	 * Konstruktor
	 * @param string	$url			URL, unter welcher die Daten im JSON-Format zur Verfügung gestellt werden
	 * @param string	$parameter		Übergabeparameter welche per POST an die URL weitergegebene werden
	 * @param string	$initScriptCode	Zusätzlicher Scriptcode, um beisielsweise einen Eventhandler anzubinden
	 */
	public function DynamicContent($url, $parameter="", $initScriptCode=""){
		$this->id = self::$dynamicContentID;
		self::$dynamicContentID++;
		$this->url=$url;
		$this->parameter=$parameter;
		$this->initScriptCode=$initScriptCode;
		if($this->parameter!="" && !is_array($this->parameter))$this->parameter="&".$this->parameter;
	}
	
	/**
	 * Gibt den JavaScript-Code aus
	 */
	public function PrintElement()
	{
		?>	<script type="text/javascript">
				// Daten dynamisch laden...
				var dataRequestObj_<?=$this->id;?> = new Request.JSON (
					{	url:'<?=$this->url;?>', 
						method: 'post',
						noCache: true,
						onSuccess: function(responseJSON, responseText) {
							<?$this->PrintOnSuccess();?>
						},
						onFailure: function(xhr) {
							<?$this->PrintOnFailure();?>
						}/*,
						onException: function(headerName, value){
							alert("Exception: "+value);
						}*/
					}
				);
				<? // Initialisierung
				if( $this->initScriptCode=="" ){
					echo $this->GetRequestCallString();
				}else{
					echo str_replace("%REQUESTCALL%", $this->GetRequestCallString(), $this->initScriptCode);
				}?>
			</script> <?
	}

	/**
	 * Gibt den JavaScript-Code für den Eventhandler im Fehlerfall aus 
	 * In Javascript steht die Variablen xhr, welche das Mootools-Request-Objekt beinhaltet zur Weiterverarbeitung bereit
	 */
	public function GetRequestCallString()
	{
		return "dataRequestObj_".$this->id.".send('".SID.$this->parameter."');";
	}
	
	/**
	 * Gibt den JavaScript-Code für den Eventhandler im Fehlerfall aus 
	 * In Javascript steht die Variablen xhr, welche das Mootools-Request-Objekt beinhaltet zur Weiterverarbeitung bereit
	 */
	protected function PrintOnFailure()
	{
		?>alert('Verbindung fehlgeschlagen!');<?
	}
	
	/**
	 * Gibt den JavaScript-Code für den Erfolgshandler aus 
	 * In Javascript steht die Variablen responseJSON, welche die angeforderten Daten enthält, zur Weiterverarbeitung bereit
	 */
	abstract protected function PrintOnSuccess();
	
}
?>