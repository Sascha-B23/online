<?php
/**
 * FormData-Implementierung für die Telefontermine
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class PhoneDateFormData extends FormData 
{
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		global $SHARED_HTTP_ROOT;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "teilabrechnung.png";
		$this->options["icontext"] = "Telefontermin bearbeiten";
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["dateAndTime"]=$this->obj->GetTelefontermin()==0 ? "" : date("d.m.Y", $this->obj->GetTelefontermin());
			$this->formElementValues["dateAndTime_clock"]=$this->obj->GetTelefontermin()==0 ? "" : date("H:i", $this->obj->GetTelefontermin());
			$this->formElementValues["dateAndTimeEnd"]=$this->obj->GetTelefonterminEnde()==0 ? "" : date("d.m.Y", $this->obj->GetTelefonterminEnde());
			$this->formElementValues["dateAndTimeEnd_clock"]=$this->obj->GetTelefonterminEnde()==0 ? "" : date("H:i", $this->obj->GetTelefonterminEnde());			
			$this->formElementValues["ansprechpartner"]=$this->obj->GetTelefonterminAnsprechpartner() == null ? "" : $this->obj->GetTelefonterminAnsprechpartner()->GetAddressIDString();
		}
		// Wenn kein Ansprechpartner gesetzt ist, den Ansprechpartner der Teilabrechnung übernehmen...
		if ($this->formElementValues["ansprechpartner"]=="")
		{
			$teilabrechnung = $this->obj->GetTeilabrechnung($this->db);
			if( $teilabrechnung!=null) $this->formElementValues["ansprechpartner"] = ($teilabrechnung->GetAnsprechpartner() == null ? "" : $teilabrechnung->GetAnsprechpartner()->GetAddressIDString());
		}
		// Widerspruchspunkt
		$this->elements[] = new DateAndTimeElement("dateAndTime", "Terminbeginn", Array("date" => $this->formElementValues["dateAndTime"], "time" => $this->formElementValues["dateAndTime_clock"]), true, $this->error["dateAndTime"]);
		$this->elements[] = new DateAndTimeElement("dateAndTimeEnd", "Terminende", Array("date" => $this->formElementValues["dateAndTimeEnd"], "time" => $this->formElementValues["dateAndTimeEnd_clock"]), true, $this->error["dateAndTimeEnd"]);
		// Ansprechpartner
		$buttons = Array();
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/address_new.png", "help" => "Neuen Ansprechpartner anlegen", "href" => "javascript:CreateNewAddress(".AM_ADDRESSDATA_TYPE_NONE.")");
		$buttons[]= Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany(".AM_ADDRESSDATA_TYPE_NONE.")");
		$this->elements[] = new TextElement("ansprechpartner", "Ansprechpartner", $this->formElementValues["ansprechpartner"], true, $this->error["ansprechpartner"], false, new SearchTextDynamicContent(  $SHARED_HTTP_ROOT."phplib/jsInterface.php5", Array("reqDataType" => 6, "param01" => AM_ADDRESSDATA_TYPE_NONE), "", "ansprechpartner"), $buttons);
		$this->elements[] = new CheckboxElement("telefonDatumLoeschen", "Termin abschließen/löschen", $this->formElementValues["telefonDatumLoeschen"], false, "");
		$this->elements[] = new BlankElement();
		
		// FMS-Schreiben auflisten
		$widerspruche = $this->obj->GetWidersprueche($this->db);
		$rsSchreibenList = Array();
		for ($a=count($widerspruche)-1; $a>=0; $a--)
		{
			$rsSchreibenWS = $widerspruche[$a]->GetFiles($this->db, FM_FILE_SEMANTIC_RSSCHREIBEN, "");
			for ($b=count($rsSchreibenWS)-1; $b>=0; $b--)
			{
				$rsSchreibenList[] = $rsSchreibenWS[$b];
			}
		}
		ob_start();
		?>
		<a href="javascript:AddRSSchreiben();" style="text-decoration:none;" onfocus="if (this.blur) this.blur()">
		<span>
			<img src="<?=$SHARED_HTTP_ROOT;?>pics/gui/newEntry.png" border="0" alt="" /> <span style="position:relative; top:-6px;">SFM-Schreiben hinzufügen</span>
		</span>
		</a><br/>
		<?
		$table_width = 800;
		include("rsSchreiben.inc.php5");
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT, "SFM-Schreiben");

	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		global $addressManager, $appointmentManager;
		$this->error=array();
		// Datum
		if( $this->formElementValues["telefonDatumLoeschen"]=="on" )
		{
			$this->formElementValues["dateAndTime"]="";
			$this->formElementValues["dateAndTime_clock"]="";
			$this->formElementValues["dateAndTimeEnd"]="";
			$this->formElementValues["dateAndTimeEnd_clock"]="";
			$this->formElementValues["ansprechpartner"]="";
		}
		if( trim($this->formElementValues["dateAndTime"])=="" && trim($this->formElementValues["dateAndTime_clock"])=="" ){
			$this->obj->SetTelefontermin(0);
		}else{
			$tempValue=DateAndTimeElement::GetTimeStamp($this->formElementValues["dateAndTime"], $this->formElementValues["dateAndTime_clock"]);
			if( $tempValue===false )$this->error["dateAndTime"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj und eine gültige Uhrzeit im Format hh:mm ein";
			else $this->obj->SetTelefontermin($tempValue);
		}
		if( trim($this->formElementValues["dateAndTimeEnd"])!="" || trim($this->formElementValues["dateAndTimeEnd_clock"])!="" ){
			$tempValue=DateAndTimeElement::GetTimeStamp($this->formElementValues["dateAndTimeEnd"], $this->formElementValues["dateAndTimeEnd_clock"]);
			if( $tempValue===false )
			{
				$this->error["dateAndTimeEnd"]="Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj und eine gültige Uhrzeit im Format hh:mm ein";
			}
			else
			{
				if ($tempValue<$this->obj->GetTelefontermin())
				{
					$this->error["dateAndTimeEnd"]="Das Terminende muss vor dem Terminbeginn liegen";
				}
				else
				{
					$this->obj->SetTelefonterminEnde($tempValue);
				}
			}
		}else{
			$this->obj->SetTelefonterminEnde(0);
		}
		
		// Ansprechpartner
		if (trim($this->formElementValues["ansprechpartner"])!="")
		{
			$tempAddressData = AddressManager::GetAddessById($this->db, $this->formElementValues["ansprechpartner"]);
			if ($tempAddressData===null)
			{
				$this->error["ansprechpartner"]="Der eingegebene Ansprechpartner konnte in der Adressdatenbank nicht gefunden werden";
			}
			else
			{
				$this->obj->SetTelefonterminAnsprechpartner($tempAddressData);
				$this->formElementValues["ansprechpartner"] = $tempAddressData->GetAddressIDString();
			}
		}
		else
		{
			$this->obj->SetTelefonterminAnsprechpartner(null);
		}
		
		// Speichern
		if( count($this->error)==0 ){
			
			if ($this->obj->GetTelefontermin()!=0 && ($this->obj->GetTelefonterminEnde()==0 || $this->obj->GetTelefonterminAnsprechpartner()==null))
			{
				if ($this->obj->GetTelefonterminEnde()==0) $this->error["dateAndTimeEnd"]="Bitte geben Sie ein Terminende ein";
				if ($this->obj->GetTelefonterminAnsprechpartner()==null) $this->error["ansprechpartner"]="Bitte hinterlegen Sie den Ansprechpartner";
			}
			if( count($this->error)==0 )
			{
				$returnValue = $this->obj->Store($this->db);
				if( $returnValue===true )
				{
					$returnValue = $this->obj->UpdateTelefonterminCalendarEntry($this->db, $appointmentManager);
					if ($returnValue===true)
					{
						return true;
					}
					else
					{
						$this->error["misc"][]="Der Termin konnt nicht aktualisiert werden (".$returnValue.")";
					}
				}
				else
				{
					$this->error["misc"][]="Systemfehler (".$returnValue.")";
				}
			}
		}
		return false;
	}
	
	/**
	 * Diese Funktion kann überladen werden, um HTML-Code auszugeben nachdem das Formular ausgegeben wurde
	 */
	public function PostPrint()
	{
		global $DOMAIN_HTTP_ROOT;
		$widerspruch = $this->obj->GetWiderspruch($this->db);
		?>
		<script type="text/javascript">
			<!--
				function AddRSSchreiben()
				{
				<?	if($widerspruch!=null){?>
						var newWin=window.open('addRSSchreiben.php5?<?=SID;?>&widerspruchID=<?=$widerspruch->GetPKey();?>','_addRSSchreiben','resizable=1,status=1,location=0,menubar=0,scrollbars=0,toolbar=0,height=800,width=1024');
				<?	}else{?>
						alert("Es kann kein SFM-Schreiben angelegt werden, da der Prozess keinen Widerspruch enthält.");
				<?	}?>
					//newWin.moveTo(width,height);
					newWin.focus();					
				}
				
				function CreateNewAddress(type)
				{
					var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/createAddress.php5?<?=SID;?>&type='+type, '_createAddress', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function SetAddress(type, name)
				{
					$('ansprechpartner').value=name;
				}
				
				function CreateNewAddressCompany(type)
				{
					var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/createAddressCompany.php5?<?=SID;?>&type='+type, '_createAddressCompany', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function SetAddressCompany(type, name, addressId, addressCompanyId)
				{
					$('ansprechpartner').value = addressCompanyId;
				}
				
				// Datum übernehmen
				$('dateAndTime_clock').onchange = function(){if($('dateAndTimeEnd').value=="")$('dateAndTimeEnd').value = $('dateAndTime').value;};
			-->
		</script>
		<?
		
	}
	
}
?>