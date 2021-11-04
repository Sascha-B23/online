<?php
/**
 * FormData-Implementierung für die User
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class UserFormData extends FormData 
{
	/**
	 * 
	 * @var bool
	 */	
	protected $myDataDialog = false;
	
	/**
	 * Konstruktor
	 * @param array $formElementValues Values der Elemente (Post-Vars)
	 * @param DBEntry $object Objekt mit den Daten das Dargestellt werden soll
	 * @param DBManager $db
	 * @param bool $myDataDialog Handelt es sich um die Benutzerdaten unter Administration oder sind es die Daten unter "Meine Daten"?
	 */
	public function UserFormData($formElementValues, $object, $db, $myDataDialog=false)
	{
		$this->myDataDialog = $myDataDialog;
		parent::__construct($formElementValues, $object, $db);
	}
	
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		/* @var $userManager UserManager */
		global $AM_ADDRESSDATA_TYPE, $UM_GROUP_BASETYPE_INFOS, $userManager, $addressManager;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "user.png";
		if (!$this->myDataDialog)
		{
			$this->options["icontext"] = "Benutzer ";
			$this->options["icontext"] .= $edit ? "bearbeiten" : "anlegen";
		}
		else
		{
			$this->options["icontext"] = "Meine Daten";
		}
		/*@var $addresdData AddressData*/
		$addresdData=$this->obj->GetAddressData();
		// Daten aus Objekt laden?
		if ($loadFromObject)
		{
			$this->formElementValues["login"] = $this->obj->GetEMail();
			$this->formElementValues["coverUser"] = $this->obj->GetCoverUser();
			// Nicht anzeigen, wenn man unter "Meine Daten" ist
			if (!$this->myDataDialog)
			{
				$this->formElementValues["usergroup"]=$this->obj->GetGroupIDs($this->db);
				$this->formElementValues["pwd"]=$this->obj->GetPWD()=="" ? "" : "***************";
				$this->formElementValues["passwordResetRequired"] = ($this->obj->IsPasswordResetRequired() ? "on" : "");
				$this->formElementValues["userlock"]=$this->obj->IsLocked();
			}
			if ($addresdData!=null)
			{
				$this->formElementValues["anrede"]=$addresdData->GetTitle() + 1;
				$this->formElementValues["anrede2"]=$addresdData->GetTitle2();
				$this->formElementValues["name"]=$addresdData->GetName();
				$this->formElementValues["vorname"]=$addresdData->GetFirstName();
				$this->formElementValues["shortname"]=$addresdData->GetShortName();
				$this->formElementValues["funktion"]=$addresdData->GetRole();
				$this->formElementValues["strasse"]=$addresdData->GetStreet(true);
				$this->formElementValues["city_zip"]=$addresdData->GetZIP(true);
				$this->formElementValues["city_city"]=$addresdData->GetCity(true);
				$this->formElementValues["telefon"]=$addresdData->GetPhone();
				$this->formElementValues["mobil"]=$addresdData->GetMobile();
				$this->formElementValues["fax"]=$addresdData->GetFax();
				$this->formElementValues["addressCompany"]=($addresdData->GetAddressCompany()==null ? -1 : $addresdData->GetAddressCompany()->GetPKey());
				if (!$this->myDataDialog)
				{
					$this->formElementValues["type"]=Array();
					$typeTemp=$addresdData->GetType();
					$keys = array_keys($AM_ADDRESSDATA_TYPE);
					for ($a=0; $a<count($keys); $a++)
					{
						if ($keys[$a]==AM_ADDRESSDATA_TYPE_NONE) continue;
						if (($typeTemp & $keys[$a])==$keys[$a])
						{
							$this->formElementValues["type"][]=$keys[$a];
						}
					}
				}
			}
		}
		elseif (!isset($this->formElementValues["sendData"]))
		{
			// 1. Aufruf 
			$this->formElementValues["passwordResetRequired"] = "on";
		}
		
		
		// Elemente anlegen
		$this->elements[] = new TextElement("login", "E-Mail", $this->formElementValues["login"], true, $this->error["login"]);
		
		if (!$this->myDataDialog)
		{
			$this->elements[] = new PwdElement2("pwd", "Passwort", $this->formElementValues["pwd"], true, $this->error["pwd"], false, $this->formElementValues["pwd_gen"]=="true" ? true : false);
			$this->elements[] = new CheckboxElement("passwordResetRequired", "Benutzer muss bei nächster Anmeldung sein Passwort ändern", $this->formElementValues["passwordResetRequired"], false, "");
		}
		else
		{
			$this->elements[] = new PwdElement("pwdalt", "Passwort alt", "", true, $this->error["pwdalt"]);
			$this->elements[] = new PwdElement("pwd", "Passwort neu", "", false, $this->error["pwd"]);
			$this->elements[] = new PwdElement("pwd2", "Passwortbestätigung", "", false, $this->error["pwd2"]);
		}
		// Nicht anzeigen, wenn man unter "Meine Daten" ist
		if (!$this->myDataDialog)
		{
			$this->elements[] = new CheckboxElement("userlock", "Benutzer gesperrt", $this->formElementValues["userlock"], false, "");
			$userGroups=$userManager->GetGroups("", $_SESSION["currentUser"]->GetGroupBasetype($this->db), 'name', 0, 0, 0);
			$options=Array();
			for($a=0; $a<count($userGroups); $a++)
			{
				$options[]=Array("name" => $userGroups[$a]->GetName(), "value" => $userGroups[$a]->GetPKey());
			}
			$this->elements[] = new DropdownElement("usergroup", "Benutzergruppe", !isset($this->formElementValues["usergroup"]) ? Array() : $this->formElementValues["usergroup"], false, $this->error["usergroup"], $options, true);
			$this->elements[count($this->elements)-1]->SetHeight(200);
		}		
		$optionsAnrede=Array(0 => Array("name" => "Herr", "value" => 1), Array("name" => "Frau", "value" => 2) );
		$this->elements[] = new RadioButtonElement("anrede", "Anrede", $this->formElementValues["anrede"], false, $this->error["anrede"], $optionsAnrede);
		$this->elements[] = new TextElement("anrede2", "Anrede", $this->formElementValues["anrede2"], false, $this->error["anrede2"]);
		$this->elements[] = new TextElement("name", "Name", $this->formElementValues["name"], true, $this->error["name"]);
		$this->elements[] = new TextElement("vorname", "Vorname", $this->formElementValues["vorname"], false, $this->error["vorname"]);
		$this->elements[] = new TextElement("shortname", "Namenskürzel", $this->formElementValues["shortname"], true, $this->error["shortname"]);
		$this->elements[] = new TextElement("funktion", "Funktion", $this->formElementValues["funktion"], false, $this->error["funktion"]);
		
		// Elemente anlegen	
		$options=Array();
		$options[] = Array("name" => "Bitte wählen...", "value" => -1);
		$addressCompanies = $addressManager->GetAddressCompany("", AddressCompany::TABLE_NAME.".name");
		for($a=0; $a<count($addressCompanies); $a++)
		{
            if (trim($addressCompanies[$a]->GetName())=="") continue;
			$options[]=Array("name" => $addressCompanies[$a]->GetName(), "value" => $addressCompanies[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("addressCompany", AddressCompany::GetAttributeName($this->languageManager, 'name'), $this->formElementValues["addressCompany"], true, $this->error["addressCompany"], $options, false, null, $this->readOnly ? Array() : Array(0 => Array("width" => 25, "pic" => "pics/gui/addressCompany_new.png", "help" => "Neue Firma anlegen", "href" => "javascript:CreateNewAddressCompany();")), $this->readOnly);
		
		$this->elements[] = new TextElement("strasse", "Straße", $this->formElementValues["strasse"], false, $this->error["strasse"], false, null, Array(), Array(), $addresdData!=null && $addresdData->UseAddressFromCompany() ? $addresdData->GetStreet() : '');
		$this->elements[] = new ZIPCityElement("city", "PLZ / Ort", Array("zip" => $this->formElementValues["city_zip"], "city" => $this->formElementValues["city_city"]), false, $this->error["city"], false, $addresdData!=null && $addresdData->UseAddressFromCompany() ? $addresdData->GetZIP() : '', $addresdData!=null && $addresdData->UseAddressFromCompany() ? $addresdData->GetCity() : '');
		$this->elements[] = new TextElement("telefon", "Telefon", $this->formElementValues["telefon"], false, $this->error["telefon"]);
		$this->elements[] = new TextElement("mobil", "Mobil", $this->formElementValues["mobil"], false, $this->error["mobil"]);
		$this->elements[] = new TextElement("fax", "Fax", $this->formElementValues["fax"], false, $this->error["fax"]);
		// Urlaubsvertretung
		// Mögliche Urlaubsvertretungen suchen
		$usersTemp=Array();
		foreach ($UM_GROUP_BASETYPE_INFOS as $groupId => $groupInfo) 
		{
			if ($groupInfo['enableCoverUserForGroup']!==true) continue;
			if ($this->obj->BelongToGroupBasetype($this->db, $groupId))
			{
				$usersTemp2=$userManager->GetUsers($_SESSION["currentUser"], "", "email", 0, 0, 0, $groupId);
				for ($a=0; $a<count($usersTemp2); $a++)
				{
					if ($usersTemp2[$a]->GetPKey()!=$this->obj->GetPKey())
					{
						$usersTemp[]=$usersTemp2[$a];
					}
				}
				break;
			}
		}
		$options=Array();
		$options[]=Array("name" => "Keine Urlaubsvertretung", "value" => -1);
		for($a=0; $a<count($usersTemp); $a++)
		{
			$options[]=Array("name" => $usersTemp[$a]->GetUserName(), "value" => $usersTemp[$a]->GetPKey());
		}
		$this->elements[] = new DropdownElement("coverUser", "Urlaubsvertretung", !isset($this->formElementValues["coverUser"]) ? Array() : $this->formElementValues["coverUser"], false, $this->error["coverUser"], $options, false);
		// Nicht anzeigen, wenn man unter "Meine Daten" ist
		if (!$this->myDataDialog)
		{
			$options=Array();
			$head = array_keys($AM_ADDRESSDATA_TYPE);
			$data = array_values($AM_ADDRESSDATA_TYPE);
			for($a=0; $a<count($head); $a++){
				if( $head[$a]==AM_ADDRESSDATA_TYPE_NONE)continue;
				$options[]=Array("name" => $data[$a], "value" => $head[$a]);
			}
			$this->elements[] = new DropdownElement("type", "Typ", !isset($this->formElementValues["type"]) ? Array() : $this->formElementValues["type"], false, $this->error["type"], $options, true);
		}
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		if ($this->obj->GetPKey()!=-1 && $_SESSION["currentUser"]->GetGroupBasetype($this->db) < UM_GROUP_BASETYPE_ADMINISTRATOR)
		{
			$this->error["misc"][] = "Keine Schreibberechtigung";
			return false;
		}
		if (!$this->myDataDialog)
		{
			// Passwort generieren?
			if ($this->formElementValues["pwd_gen"]=="true")
			{
				$this->formElementValues["pwd"]=$this->obj->GenerateRandomPWD();
				return false;
			}
		}
		// Email setzen
		$retValue=$this->obj->SetEMail( $this->formElementValues["login"] );
		if ($retValue!==true)
		{
			if ($retValue==-1) $this->error["login"]="Bitte geben Sie eine E-Mail-Adresse ein";
			if ($retValue==-2) $this->error["login"]="Die eingegebene E-Mail-Adresse ist ungültig";
		}
		// Urlaubsvertretung setzen
		$this->obj->SetCoverUser( $this->formElementValues["coverUser"] );
		// Passwort setzen
		if ($this->myDataDialog)
		{
			// Meine Daten 
			if (!$this->obj->IsPWDCorrect($this->formElementValues["pwdalt"]))
			{
				$this->error["pwdalt"]="Passwort ist nicht korrekt.<br>Bitte geben Sie das aktuelle Passwort ein, um Ihre Änderungen zu bestätigen";
			}
			else if( $this->formElementValues["pwd"]!=$this->formElementValues["pwd2"] )
			{
				$this->error["pwd2"]="Das Passwort und die Bestätigung sind nicht identisch";
			}
			else if( trim($this->formElementValues["pwd"])!="" )
			{
				$retValue=$this->obj->SetPWD( $this->formElementValues["pwd"] );
				if ($retValue!==true)
				{
					if( $retValue==-1 )$this->error["pwd"]="Bitte geben Sie ein Passwort ein";
					if( $retValue==-2 )$this->error["pwd"]="Das Passwort muss aus mindestens fünf Zeichen bestehen";
					if( $retValue==-3 )$this->error["pwd"]="Das Passwort muss mindestens zwei Zahlen enthalten";
					if( $retValue==-4 )$this->error["pwd"]="Das Passwort muss mindestens zwei Buchstaben enthalten";
				}
			}
		}
		else
		{
			// Benutzerverwaltung
			if( $this->formElementValues["pwd"]!="***************" )
			{
				$retValue=$this->obj->SetPWD( $this->formElementValues["pwd"] );
				if( $retValue!==true )
				{
					if( $retValue==-1 )$this->error["pwd"]="Bitte geben Sie ein Passwort ein";
					if( $retValue==-2 )$this->error["pwd"]="Das Passwort muss aus mindestens fünf Zeichen bestehen";
					if( $retValue==-3 )$this->error["pwd"]="Das Passwort muss mindestens zwei Zahlen enthalten";
					if( $retValue==-4 )$this->error["pwd"]="Das Passwort muss mindestens zwei Buchstaben enthalten";
				}
			}
			$this->obj->SetPasswordResetRequired($this->formElementValues["passwordResetRequired"]=="on" ? true : false);
			// Sperrung zurücksetzen
			$this->obj->Lock();
		}
		if (trim($this->formElementValues["shortname"])=="")
		{
			$this->error["shortname"]="Bitte geben Sie ein Namenskürzel ein";
		}
		
		// Adressdaten setzen
		$addresdData=$this->obj->GetAddressData();
		$newAddressData=false;
		if ($addresdData==null)
		{
			$newAddressData=true;
			$addresdData=new AddressData($this->db);
		}
		if ($this->formElementValues["anrede"]=="1" || $this->formElementValues["anrede"]=="2")$addresdData->SetTitle( $this->formElementValues["anrede"]-1);
		$addresdData->SetTitle2( $this->formElementValues["anrede2"] );
		$addresdData->SetEMail( $this->formElementValues["login"] );
		$addresdData->SetName( $this->formElementValues["name"] );
		$addresdData->SetFirstName( $this->formElementValues["vorname"] );
		$addresdData->SetShortName( $this->formElementValues["shortname"] );
		$addresdData->SetRole( $this->formElementValues["funktion"] );
		
		$addressCompany = null;
		if ($this->formElementValues["addressCompany"]!=-1)
		{
			$addressCompany = AddressManager::GetAddressCompanyByPkey($this->db, $this->formElementValues["addressCompany"]);
			if ($addressCompany==null)
			{
				$this->error["addressCompany"] = "Die Firma konnte nicht gefunden werden";
			}
		}
		else
		{
			$this->error["addressCompany"] = "Bitte wählen Sie eine Firma aus";
		}
		$addresdData->SetAddressCompany($addressCompany);
		
		$addresdData->SetStreet( $this->formElementValues["strasse"] );
		$addresdData->SetZIP( $this->formElementValues["city_zip"] );
		$addresdData->SetCity( $this->formElementValues["city_city"] );
		$addresdData->SetPhone( $this->formElementValues["telefon"] );
		$addresdData->SetMobile( $this->formElementValues["mobil"] );
		$addresdData->SetFax( $this->formElementValues["fax"] );
		// Nicht anzeigen, wenn man unter "Meine Daten" ist
		if( !$this->myData ){
			if( !is_array($this->formElementValues["type"]) )$this->formElementValues["type"]=Array();
			$typeTemp=0;
			for($a=0; $a<count($this->formElementValues["type"]); $a++){
				$typeTemp|=$this->formElementValues["type"][$a];
			}
			$addresdData->SetType( $typeTemp );
		}
		if (count($this->error)>0) return false;
		// Adressdaten speichern
		$returnValue=$addresdData->Store($this->db);
		if( $returnValue===true ){
			$this->obj->SetAddressData($addresdData);
			$returnValue=$this->obj->Store($this->db);
			// Gruppenzugehörigkeit seten (nur im Adminbereich)
			if( !$this->myDataDialog ){
				if( !is_array($this->formElementValues["usergroup"]) )$this->formElementValues["usergroup"]=Array();		
				$this->obj->SetGroupIDs( $this->db, $this->formElementValues["usergroup"] );		
			}
			// Sonderfall wenn geänderter Benutzer der aktuell angemeldete Benutzer ist -> Dann Sessionobjekt ersetzen
			if( $_SESSION["currentUser"]->GetPKey()==$this->obj->GetPKey() ){
				$_SESSION["currentUser"]=$this->obj;
			}
			if( $returnValue===true )return true;
			// Fehler beim Speichern des Benutzers
			// Addressdaten wieder löschen wenn diese neu angelegt wurden
			if( $newAddressData )$addresdData->DeleteMe($this->db);
			// Fehlertext erzeugen
			if( $returnValue==-101 )$this->error["login"]="Bitte geben Sie eine E-Mail-Adresse ein";
			if( $returnValue==-102 )$this->error["pwd"]="Bitte geben Sie ein Passwort ein";
			if( $returnValue==-103 )$this->error["login"]="Es exisitiert bereits ein Benutzer mit dieser E-Mail-Adresse";
			else $this->error["name"]="Es ist ein unerwarteter Fehler aufgetreten (".$returnValue.")";
		}else{
			 // Fehler beim Speichern der Adressdaten
			if( $returnValue==-101 )$this->error["name"]="Bitte geben Sie einen Namen ein";
		}
		return false;
	}
	
	/**
	 * This function can be used to output HTML data after the form
	 */
	public function PostPrint()
	{
		global $DOMAIN_HTTP_ROOT;
		?>
		<script type="text/javascript">
			<!--
				function CreateNewAddressCompany(type)
				{
					var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/createAddressCompany.php5?<?=SID;?>&type='+type, '_createAddressCompany', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
					//newWin.moveTo(width,height);
					newWin.focus();
				}
				
				function SetAddressCompany(type, name, addressId, addressCompanyId)
				{
					$('addressCompany').options[$('addressCompany').options.length] = new Option(name, addressId, false, true);
				}
			-->
		</script>
		<?
	}
	
}
?>