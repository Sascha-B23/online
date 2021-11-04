<?php
/**
 * FormData-Implementierung für die Textvorlage
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class StandardTextFormData extends FormData 
{
	/**
	 * CustomerManager
	 * @var CustomerManager 
	 */
	protected $customerManager = null;

	/**
	 * Constructor
	 * @param array $formElementValues
	 * @param StandardText $object
	 * @param DBManager $db
     * @param CustomerManager $customerManager
	 */
	public function StandardTextFormData($formElementValues, StandardText $object, DBManager $db, CustomerManager $customerManager)
	{
		$this->customerManager = $customerManager;
		parent::__construct($formElementValues, $object, $db);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		$languages = $this->customerManager->GetLanguages();
		// Icon und Überschrift festlegen
		$this->options["icon"] = "textmodule.png";
		$this->options["icontext"] = ($this->obj->GetType()==StandardText::ST_TYPE_TEMPLATE ? "Textvorlage " : $this->obj->GetType()==StandardText::ST_TYPE_TRANSLATION ? "Übersetzung " : "Status-Kommentar ");
		$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["name"]=$this->obj->GetName();
			for ($a=0; $a<count($languages);$a++)
			{
				$this->formElementValues["standardText_".$languages[$a]->GetIso639()]=$this->obj->GetStandardText($languages[$a]->GetIso639());
			}
		}
		if (!isset($this->formElementValues["multiline"]))
		{
			$this->formElementValues["protected"]=($this->obj->IsDeletable($this->db) ? "" : "on");
			$this->formElementValues["multiline"]=($this->obj->IsMultiLineText() ? "on" : "");
		}
		
		// Elemente anlegen
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		
		for ($a=0; $a<count($languages);$a++)
		{
			if ($this->obj->IsMultiLineText())
			{
				$this->elements[] = new TextAreaElement("standardText_".$languages[$a]->GetIso639(), "Textvorlage (".$languages[$a]->GetIso639().")", $this->formElementValues["standardText_".$languages[$a]->GetIso639()], true, $this->error["standardText_".$languages[$a]->GetIso639()], false, false);
			}
			else
			{
				$this->elements[] = new TextElement("standardText_".$languages[$a]->GetIso639(), "Textvorlage (".$languages[$a]->GetIso639().")", $this->formElementValues["standardText_".$languages[$a]->GetIso639()], true, $this->error["standardText_".$languages[$a]->GetIso639()]);
			}
			$this->elements[count($this->elements)-1]->SetWidth(700);
			$this->elements[count($this->elements)-1]->SetHeight(400);
		}
		
		$placeholders = StandardTextManager::GetPlaceholders($this->db, $this->obj->GetPKey());
		if (count($placeholders)>0)
		{
			ob_start();
			?><table>
				<tr>
					<td>Platzhalter</td>
					<td>Beispielwert</td>
				</tr>
			<?
			foreach ($placeholders as $key => $value) 
			{?>
				<tr>
					<td><?=$key;?></td>
					<td><?=$value;?></td>
				</tr><?
			}
			?></table><?
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$this->elements[] = new CustomHTMLElement($CONTENT, "Verfügbare Platzhalter");
		}
		
		// Nur Superuser können festlegen, ob der Eintrag gelöscht werden kann oder nicht
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_SUPERUSER)
		{
			$this->elements[] = new CheckboxElement("protected", "<nobr>Kann nicht gelöscht werden</nobr>", $this->formElementValues["protected"], false, $this->error["protected"]);
            if ($this->obj->GetType()!=StandardText::ST_TYPE_SCHEDULECOMMENT) $this->elements[] = new CheckboxElement("multiline", "<nobr>Mehrzeiliger Text</nobr>", $this->formElementValues["multiline"], false, $this->error["multiline"]);
		}
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$languages = $this->customerManager->GetLanguages();
		$this->error=array();
		if ($this->obj->SetName($this->formElementValues["name"])!==true) $this->error["name"] = "Bitte geben Sie einen Namen an.";
		for ($a=0; $a<count($languages);$a++)
		{
			if ($this->obj->SetStandardText($languages[$a]->GetIso639(), $this->formElementValues["standardText_".$languages[$a]->GetIso639()])!==true) $this->error["standardText_".$languages[$a]->GetIso639()] = "Bitte geben Sie einen Text ein.";
		}
		if ($_SESSION["currentUser"]->GetGroupBasetype($this->db)==UM_GROUP_BASETYPE_SUPERUSER)
		{
			$this->obj->SetDeletable($this->formElementValues["protected"]=="on" ? false : true);
			$this->obj->SetMultiLineText($this->formElementValues["multiline"]=="on" ? true : false);
		}
		if (count($this->error)==0)
		{
			$returnValue=$this->obj->Store($this->db);
			if ($returnValue===true) return true;
			if ($returnValue==-101) $this->error["name"]="Bitte geben Sie einen Namen ein";
			else $this->error["misc"][] = "Textvorlage konnte nicht gespeichert werden (".$returnValue.")";
		}
		return false;
	}
	
}
?>