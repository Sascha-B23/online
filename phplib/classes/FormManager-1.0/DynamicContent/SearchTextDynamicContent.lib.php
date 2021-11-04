<?php
/**
 * Implementierung der Basisklasse DynamicContent für das Such-Text-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
 class SearchTextDynamicContent extends DynamicContent
{
	/**
	 *
	 * @var string
	 */
	protected $textElemntID = "";

	/**
	* Konstruktor
	* @param string	$url			URL, unter welcher die Daten im JSON-Format zur Verfügung gestellt werden
	* @param string	$parameter		Übergabeparameter welche per POST an die URL weitergegebene werden
	* @param string	$textElemntID	ID (oder Name) des Textelements, das befüllt werden soll
	*/
	public function SearchTextDynamicContent($url, $parameter="", $initScriptCode="", $textElemntID="")
	{
		parent::__construct($url, $parameter, $initScriptCode);
		$this->textElemntID=$textElemntID;
	}
	
	/**
	* Gibt den JavaScript-Code aus
	*/
	public function PrintElement()
	{
		global $UID;
		?>
		<script type="text/javascript">
			new Autocompleter.Ajax.Json($('<?=$this->textElemntID?>'), '<?=$this->url;?>', {
				minLength: 3,
				maxChoices: 25,
				postVar : 'query',
				filterSubset: true,
				postData : {
				<?	$keys=array_keys($this->parameter);
					for($a=0; $a<count($keys); $a++){ ?>
					'<?=$keys[$a];?>' : '<?=$this->parameter[$keys[$a]]?>',
				<?	}?>
					'UID' : '<?=$UID?>'
				}
			});
		</script>
	<?
	}
	
	/**
	* Gibt den JavaScript-Code für den Erfolgshandler aus 
	* In Javascript steht die Variablen responseJSON, welche die angeforderten Daten enthält, zur Weiterverarbeitung bereit
	*/
	protected function PrintOnSuccess()
	{
		// Text-Element mit Daten füllen ... 
		
		?>
		//alert(responseText);
		<?/*
		$('<?=$this->textEleemntID;?>').value = ""; 
		for( var i=0; i<responseJSON.length; i++){
			$('<?=$this->textEleemntID;?>').value=responseJSON[i].value;
			break;
		}
		if( $('<?=$this->textEleemntID;?>').onchange!=undefined) $('<?=$this->textEleemntID;?>').onchange();
		*/?>
		<?
	}
	
	/**
	* Gibt den JavaScript-Code für den Eventhandler im Fehlerfall aus 
	* In Javascript steht die Variablen xhr, welche das Mootools-Request-Objekt beinhaltet zur Weiterverarbeitung bereit
	*/
	public function GetRequestCallString()
	{
		return "$('".$this->textElemntID."').value=''; ".parent::GetRequestCallString();
	}
	
}
?>