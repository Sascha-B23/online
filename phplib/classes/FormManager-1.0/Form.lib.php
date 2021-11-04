<?php
/**
 * Basisklasse aller Forms
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class Form 
{	
	/**
	 * Die Daten der Form die dargestellt werden sollen
	 * @var FormData 
	 */
	protected $formData = null;
	
	/**
	 * Die Rücksprung-URL wenn gespeichert oder abgebrochen wird
	 * @var string
	 */
	protected $jumpBackURL = "";
	
	/**
	 * Label
	 * @var string 
	 */
	protected $jumpBackLabel = "Abbrechen";
	
	/**
	 * Der Dateiname des Templates das benutzt werden soll
	 * @var string
	 */
	protected $templateToUse= "";
	
	/**
	 * 
	 * @var boolean
	 */
	protected $forwardToListView = true;
	
	/**
	 * Send form data by default
	 * @var bool
	 */
	protected $defaultSendData = true;
	
	/**
	 * Constructor
	 * @param FormData $formData Form-Inhalt
	 */
	public function Form( $jumpBackURL, &$formData, $templateToUse="template_1row_edit.inc.php5", $noOutput=false )
	{
		if(!$noOutput){?><script type="text/javascript">var newElementID='<?=$formData->GetFormDataObjectID();?>'; var dataStored=false;</script><?}
		$this->jumpBackURL=$jumpBackURL;
		$this->formData = &$formData;
		$this->templateToUse = $templateToUse;
		// Abhängig vom Status eine Aktion durchführen
		if( $this->formData->GetFormDataStatus()==1 ){
			$_POST["editElement"]=$this->formData->GetFormDataObjectID();
			if(!$noOutput){?><script type="text/javascript">newElementID='<?=$this->formData->GetFormDataObjectID();?>'; dataStored=true;</script><?}
			if( $_POST["forwardToListView"]=="true" ){
				// Daten wurden erfolgreich gespeichert -> zurück zur aufrufenden Seite springen
				global $SHARED_FILE_SYSTEM_ROOT;
				$REDIRECT_TARGET_URL=$jumpBackURL;
				include($SHARED_FILE_SYSTEM_ROOT."templates/redirect.php5");
				exit;
			}
		}
	}
	
	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben bevor etwas an den Browser gesendet wurde
	 */
	public function PrePrint()
	{
		if( $this->formData!=null )$this->formData->PrePrint();
	}
	
	/**
	 * Gibt das Formular aus
	 */
	public function PrintData()
	{
	?>	<form id="FM_FORM" name="FM_FORM" method="post" enctype="multipart/form-data">
			<input type="hidden" name="forwardToListView" id="forwardToListView" value="<?=($this->forwardToListView ? "true" : "false");?>">
			<input type="hidden" name="editElement" id="editElement" value="<?=$_POST["editElement"];?>"><?
			ob_start();
			$this->PrintContent();
			$CONTENT = ob_get_contents();
			ob_end_clean();
			$HEADER_PIC = $this->formData->GetIcon();
			$HEADER_TEXT = $this->formData->GetIconText();
			$SHOW_SAVE_ICON = !$this->formData->IsReadOnly();
			$SHOW_BACK_ICON = $this->formData->IsPreviousButtonAvailable();
			$SHOW_NEXT_ICON = $this->formData->IsNextButtonAvailable();
			$SHOW_CANCEL_ICON = $this->formData->IsCancelButtonAvailable();
			$FORMULARNAME = "FM_FORM";
			$CANCEL_URL = $this->jumpBackURL;
			$CANCEL_TEXT = $this->formData->GetJumpBackLabel();
			include($this->templateToUse); // Content includen
			?><!-- Anfang PostPrint() --><?
			$this->formData->PostPrint();
			?><!-- Ende PostPrint() --><?
	?>	</form><?
	}
	
	/**
	 * Set the property send form data by default
	 * @param boolean $send
	 * @return boolean
	 */
	public function SetDefaultForwardToListView($forwardToListView)
	{
		if (!is_bool($forwardToListView)) return false;
		$this->forwardToListView = $forwardToListView;
		return true;
	}
	
	/**
	 * Set the property send form data by default
	 * @param boolean $send
	 * @return boolean
	 */
	public function SetDefaultSendData($send)
	{
		if (!is_bool($send)) return false;
		$this->defaultSendData = $send;
		return true;
	}
	
	/**
	 * Set the label for the JumpBack Link in the top right corner
	 */
	public function SetJumpBackLabel($label)
	{
		$this->jumpBackLabel = $label;
	}
	
	/**
	 * Gibt den Inhalt aus
	 */
	abstract protected function PrintContent();
	
}
?>