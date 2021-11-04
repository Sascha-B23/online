<?php
/**
 * FormData-Implementierung für DynamicTableConfig
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class DynamicTableConfigFormData extends FormData 
{
	
	/**
	 * DynamicTable
	 * @var DynamicTable 
	 */
	protected $dynamicTable = null;
	
	/**
	 * Constructor
	 * @param array $formElementValues
	 * @param DynamicTable $dynamicTable
	 * @param DBManager $db
	 */
	
	public function DynamicTableConfigFormData($formElementValues, DynamicTable $dynamicTable, DBManager $db)
	{
		$this->dynamicTable = $dynamicTable;
		parent::__construct($formElementValues, null, $db);
}
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $DOMAIN_HTTP_ROOT;
		// Icon und Überschrift festlegen
		$this->options["show_options_save"] = false;
		$this->options["icon"] = "filter.png";
		$this->options["icontext"] = "Filterkonfigurationen verwalten";
		$this->options["jumpBackLabel"] = "Schließen";
				
		// Elemente anlegen
		ob_start();
		?>
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td align="left" valign="middle">
					<input type="text" id="name" name="name" value="<?=$this->formElementValues["name"];?>" class="TextForm" style="width: 300px;" >
				</td>
				<td align="left" valign="middle">
					<a href="javascript: StoreCurrentConfiguration();"><img src="<?=$DOMAIN_HTTP_ROOT;?>pics/gui/next.png" width="25px" alt="Konfiguration anlegen" title="Konfiguration anlegen" border="0"></a>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="left" valign="middle">
					<input type="checkbox" id="defaultConfig" name="defaultConfig" class="CeckboxForm" <? if($this->formElementValues["defaultConfig"]=="on")echo "checked";?> /> Als Standardfilter festlegen
				</td>
			</tr>
		<?	if (isset($this->error["name"])){?>
				<tr>
					<td colspan="2" class="errorText" align="left" valign="middle"><?=$this->error["name"];?>&#160;</td>
				</tr>	
		<?	}?>
		</table>
		<?
		$html = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($html, "Aktuelle Filtereinstellungen unter folgendem Namen speichern:");
		$this->elements[count($this->elements)-1]->SetFullWidthNeeded(true);
		
		
		// Konfigurationen
		$this->elements[] = new ListElement("configurations", "Eigene Filtereinstellungen", $this->formElementValues["configurations"], false, $this->error["configurations"], false, new DynamicTableConfigListData($this->db, $this->dynamicTable));
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		if (trim($this->formElementValues["name"])=="") $this->error["name"]="Bitte geben Sie einen Namen für die Konfiguraion an.";
		if (count($this->error)==0)
		{
			if (DynamicTableConfigManager::StoreDynamicTableConfig($this->db, $_SESSION["currentUser"], $this->dynamicTable, $this->formElementValues["name"], $this->formElementValues["defaultConfig"]=="on" ? true : false)==null) $this->error["misc"][] = "Konfiguration konnte nicht gespeichert werden";
			if (count($this->error)==0) return true;
		}
		return false;
	}
	
	/**
	 * This function can be used to output HTML data bevor the form
	 */
	public function PrePrint()
	{
		?>
			<script type="text/javascript">
				<!--
					function StoreCurrentConfiguration()
					{
						$('sendData').value = "true";
						document.forms.FM_FORM.submit();
					}
				-->
			</script>
		<?
	}
	
	/**
	 * This function can be used to output HTML data after the form
	 */
	public function PostPrint()
	{
		?>
			<script type="text/javascript">
				<!--
					$('sendData').value = "false";
				-->
			</script>
		<?
	}
	
}
?>