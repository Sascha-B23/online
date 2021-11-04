<?php
/**
 * FormData-Implementierung für "Aufgabe planen"
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class PlanTaskFormData extends FormData 
{
	/**
	 * Konstruktor
	 * @param array $formElementValues Values der Elemente (Post-Vars)
	 * @param DBEntry $object Objekt mit den Daten das Dargestellt werden soll
	 * @param DBManager $db
	 * @param array $prozessIDs
	 */
	public function PlanTaskFormData($formElementValues, $object, $db, $prozessIDs)
	{
		$this->prozessIDs=$prozessIDs;
		// Wenn mehrere Aufgeben geplant werden sollen, prüfen ob sich die Inhalte unterscheiden
		$timeTemp=-2;
		$planedDuration=-2;
		for ($a=0; $a<count($this->prozessIDs); $a++)
		{
			$objectTemp = new ProcessStatus($db);
			if ($objectTemp->Load((int)$this->prozessIDs[$a], $db)===true)
			{
				if ($timeTemp==-2) $timeTemp=$objectTemp->GetDateAndTime();
				if ($planedDuration==-2) $planedDuration=$objectTemp->GetPlannedDuration();
				if ($timeTemp!=$objectTemp->GetDateAndTime())
				{
					$timeTemp=-1;
					break;
				}
				if ($planedDuration!=$objectTemp->GetPlannedDuration())
				{
					$planedDuration=-1;
					break;
				}
			}
		}
		$this->notTheSame=false;
		if ($timeTemp==-1 || $planedDuration==-1) $this->notTheSame=true;
		parent::__construct($formElementValues, $object, $db);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "plannedTask.png";
		$this->options["icontext"] = "Aufgabe planen";
		// Daten aus Objekt laden?
		if( $loadFromObject && !$this->notTheSame ){
			$this->formElementValues["scheduleDatum"]=$this->obj->GetDateAndTime()==0 ? "" : date("d.m.Y", $this->obj->GetDateAndTime());
			$this->formElementValues["scheduleDatum_clock"]=$this->obj->GetDateAndTime()==0 ? "" : date("H:i", $this->obj->GetDateAndTime());
			$this->formElementValues["duration"]=$this->obj->GetPlannedDuration()==0 ? "" : TimeElement::GetString($this->obj->GetPlannedDuration());
		}
		// Widerspruchspunkt
		$this->elements[] = new DateAndTimeElement("scheduleDatum", "Daum / Uhrzeit", Array("date" => $this->formElementValues["scheduleDatum"], "time" => $this->formElementValues["scheduleDatum_clock"]), true, $this->error["scheduleDatum"]);
		$this->elements[] = new TimeElement("duration", "Geplante Dauer", $this->formElementValues["duration"], false, $this->error["duration"]);
		
		$this->elements[] = new CheckboxElement("scheduleLoeschen", "Planung verwerfen", $this->formElementValues["scheduleLoeschen"], false, "");
		$this->elements[] = new BlankElement();
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		$this->error=array();
		// Datum
		if( $this->formElementValues["scheduleLoeschen"]=="on" ){
			$this->formElementValues["scheduleDatum"]="";
			$this->formElementValues["scheduleDatum_clock"]="";
			$this->formElementValues["duration"]="";
		}
		if( trim($this->formElementValues["scheduleDatum"])=="" && trim($this->formElementValues["scheduleDatum_clock"])=="" ){
			$this->obj->SetDateAndTime(0);
		}else{
			$tempValue=DateAndTimeElement::GetTimeStamp($this->formElementValues["scheduleDatum"], $this->formElementValues["scheduleDatum_clock"]);
			if( $tempValue===false )$this->error["scheduleDatum"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj und eine gültige Uhrzeit im Format hh:mm ein";
			else $this->obj->SetDateAndTime($tempValue);
		}
		if( trim($this->formElementValues["duration"])=="" ){
			$this->obj->SetPlannedDuration(0);
		}else{
			$tempValue=TimeElement::GetTimeStamp($this->formElementValues["duration"]);
			if( $tempValue===false )$this->error["duration"]="Bitte geben Sie die Dauer im Format hh:mm ein";
			else $this->obj->SetPlannedDuration($tempValue);
		}
		// Speichern
		if( count($this->error)==0 ){
			$returnValue=$this->obj->Store($this->db);
			if( $returnValue===true ){
				$errorsOccured=false;
				// Wenn mehrere Aufgeben geplant werden sollen, Daten übernehmen und speichern
				for( $a=0; $a<count($this->prozessIDs); $a++){
					if( (int)$this->prozessIDs[$a]==$this->obj->GetPKey() )continue;
					$objectTemp = new ProcessStatus($this->db);
					if( $objectTemp->Load((int)$this->prozessIDs[$a], $this->db)===true ){
						$objectTemp->SetDateAndTime( $this->obj->GetDateAndTime() );
						$objectTemp->SetPlannedDuration( $this->obj->GetPlannedDuration() );
						$returnValue=$objectTemp->Store($this->db);
						if( $returnValue!==true ){
							$this->error["misc"][]="Eingabe konnte nicht für alle Aufgaben übernommen werden (".$returnValue.")";
							$errorsOccured=true;
						}
					}
				}
				if( !$errorsOccured ){
					return true;
				}
			}else{
				$this->error["misc"][]="Systemfehler (".$returnValue.")";
			}
		}
		return false;
	}
	
}
?>