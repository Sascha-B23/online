<?php
/**
 * FormData-Implementierung für die Verträge im Prozess
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class ExportLogFormData extends FormData 
{
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		// Icon und Überschrift festlegen
		$this->options["icon"] = "contract.png";
		$this->options["icontext"] = "Zugriffsprotokoll herunterladen";
		$this->options["show_options_save"] = false;
		if (!isset($this->formElementValues["startDate"])) $this->formElementValues["startDate"] = date("d.m.Y", time()-60*60*24*7);
		if (!isset($this->formElementValues["endDate"])) $this->formElementValues["endDate"] = date("d.m.Y", time());
		$this->elements[] = new DateElement("startDate", "Von", $this->formElementValues["startDate"], true, $this->error["startDate"]);
		$this->elements[] = new DateElement("endDate", "Bis", $this->formElementValues["endDate"], true, $this->error["endDate"]);
		$this->elements[] = new CustomHTMLElement("<input type='button' value='Zugriffsprotokoll herunterladen' onClick='document.forms.FM_FORM.submit();' class='formButton2' />");
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		// Date from...
		$startDate = DateElement::GetTimeStamp($this->formElementValues["startDate"]);
		if ($startDate===false)
		{
			$this->error["startDate"] = "Ungültiges Datum";
		}
		// Date until...
		$endDate = DateElement::GetTimeStamp($this->formElementValues["endDate"]);
		if ($endDate===false)
		{
			$this->error["endDate"] = "Ungültiges Datum";
		}
		// flip dates on wrong order
		if(count($this->error)==0 && $startDate>$endDate)
		{
			$temp = $endDate;
			$endDate = $startDate;
			$startDate = $temp;
		}
		
		// If there are no errors export log to xls file...
		if (count($this->error)==0)
		{
			// PHPExcel library is needed for function GetAsExcelStream()
			global $SHARED_FILE_SYSTEM_ROOT, $db_logging;
			require_once($SHARED_FILE_SYSTEM_ROOT."phplib/PHPExcel/PHPExcel.php");
			$fileContent = LoggingManager::GetInstance()->GetAsExcelStream($db_logging, $startDate, $endDate+60*60*24);
			FileDownloadManager::StreamFile($fileContent, "Zuggriffsprotokoll.xls");
			exit;
		}
		return false;
	}
	
}
?>