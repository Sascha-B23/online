<?php
/**
 * Status "Vertrags- und Nebenkostenerfassung durch FMS freigeben"
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2019 Stoll von Gáti GmbH www.stollvongati.com
 */
class NkasDatenerfassungFreigabeFMS extends NkasTeilabrechnungspositionenErfassen
{

	/**
	 * This function is called one time when switching to this status
	 */
	public function Prepare()
	{
		// Bearbeitungsfrist: 1 Tage nach Erhalt

		$this->obj->SetDeadline(time()+60*60*36);
		$this->obj->Store($this->db);
		// RueckweisungsBegruendung erzeugen
		$rueckweisungsBegruendung=new RueckweisungsBegruendungProzess($this->db);
		$rueckweisungsBegruendung->SetType(RueckweisungsBegruendungProzess::RWB_TYPE_ERFASSUNG);
		$rueckweisungsBegruendung->SetDatum( time() );
		$rueckweisungsBegruendung->SetProcess( $this->masterObject );
		$rueckweisungsBegruendung->SetUser( $_SESSION["currentUser"] );
		$rueckweisungsBegruendung->Store($this->db);
	}

	/**
	 * this function is called once-only when sitching to the previous status
	 */
	public function OnReturnToPreviousStatus()
	{
		// delete RueckweisungsBegruendung
		$rueckweisungsBegruendung = $this->masterObject->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendungProzess::RWB_TYPE_ERFASSUNG);
		if ($rueckweisungsBegruendung!=null)
		{
			$rueckweisungsBegruendung->DeleteMe($this->db);
		}
	}

	/**
	 * Initialize all UI elements
	 * @param boolean $loadFromObject
	 */
	public function InitElements($loadFromObject)
	{
		$this->ignoreTAs = true;
		parent::InitElements($loadFromObject);

		if ($loadFromObject)
		{
			$rueckweisungsBegruendung=$this->masterObject->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendungProzess::RWB_TYPE_ERFASSUNG);
			if( $rueckweisungsBegruendung!=null )$this->formElementValues["ablehnungsbegruendung"]=$rueckweisungsBegruendung->GetBegruendung();
		}

		// get on a new row
		$numBlanksToCreate = count($this->elements) % 3;
		$numBlanksToCreate = $numBlanksToCreate==0 ? 0 : 3 - $numBlanksToCreate;
		for ($a=0; $a<$numBlanksToCreate; $a++)
		{
			$this->elements[] = new BlankElement();
		}

		// Entscheidung
		$options=Array();
		$options[]=Array("name" => "Bitte wählen...", "value" => "");
		$options[]=Array("name" => "Freigabe bestätigen", "value" => 1);
		$options[]=Array("name" => "Freigabe zurückweisen", "value" => 2);
		$this->elements[] = new DropdownElement("abbruchAktion", "<br/>Aktion", !isset($this->formElementValues["abbruchAktion"]) ? Array() : $this->formElementValues["abbruchAktion"], false, $this->error["abbruchAktion"], $options, false);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[] = new BlankElement();
		$this->elements[] = new BlankElement();
		// Begründung
		$this->elements[] = new TextAreaElement("ablehnungsbegruendung", "Begründung für die Zurückweisung", $this->formElementValues["ablehnungsbegruendung"], false, $this->error["ablehnungsbegruendung"], $this->formElementValues["abbruchAktion"]=="2" ? false : true);
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		$this->elements[count($this->elements)-1]->SetWidth(800);
	}

	/**
	 * Store all UI data
	 * @param boolean $gotoNextStatus
	 */
	public function Store($gotoNextStatus)
	{
		$rueckweisungsBegruendung=$this->masterObject->GetLastRueckweisungsBegruendung($this->db, RueckweisungsBegruendungProzess::RWB_TYPE_ERFASSUNG);
		$rueckweisungsBegruendung->SetBegruendung($this->formElementValues["ablehnungsbegruendung"]);
		$rueckweisungsBegruendung->SetDatum( trim($this->formElementValues["ablehnungsbegruendung"])=="" ? 0 : time() );
		$rueckweisungsBegruendung->SetUserRelease( $_SESSION["currentUser"] );
		$returnValue=$rueckweisungsBegruendung->Store($this->db);
		if( $returnValue===true ){
			// Prüfen, ob in nächsten Status gesprungen werden kann
			if( $gotoNextStatus ){
				if( $this->formElementValues["abbruchAktion"]!=1 && $this->formElementValues["abbruchAktion"]!=2 )$this->error["abbruchAktion"]="Um die Aufgabe abschließen zu können, müssen Sie eine Aktion auswählen.";
			}
		}else{
			$this->error["misc"][]="Systemfehler (2/".$returnValue.")";
		}

		if( count($this->error)==0 )
		{
			if( $gotoNextStatus &&  $this->formElementValues["abbruchAktion"]==2)
			{
				// Freigabe zurückweisen -> Benutzer welcher die Freigabe angefordert hat als Bearbeiter festlegen
				$tempData = unserialize($this->obj->GetAdditionalInfo());

				if (is_array($tempData) && isset($tempData['previewsStatusUser']))
				{
					$user = new User($this->db);
					if ($user->Load((int)$tempData['previewsStatusUser'], $this->db)===true)
					{
						$this->obj->SetZuweisungUser($user);
						$this->obj->Store($this->db);
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Inject HTML/JavaScript after the UI is send to the browser
	 */
	public function PostPrint()
	{
		global $DOMAIN_HTTP_ROOT;
		?>
		<script type="text/javascript">
			<!--
			$('abbruchAktion').onchange= function(){ $('ablehnungsbegruendung').disabled = ($('abbruchAktion').value=="2" ? false : true); };

			function DownloadFile(format){
				document.forms.FM_FORM.createDownloadFile.value=format;
				document.forms.FM_FORM.submit();
				document.forms.FM_FORM.createDownloadFile.value="";
			}
			-->
		</script>
		<?
	}

	/**
	 * Return the following status
	 */
	public function GetNextStatus()
	{
		if ($this->formElementValues["abbruchAktion"]==2)  return 1;
		// Freigabe wurde bestätigt!
		return 0;
	}

	
}
?>