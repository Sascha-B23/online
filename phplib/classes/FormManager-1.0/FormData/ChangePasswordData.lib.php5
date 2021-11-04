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
class ChangePasswordData extends FormData 
{
	/**
	 * Zu implementierende Funktion, welche die Elemente der Form anlegt
	 */
	public function InitElements($edit, $loadFromObject)
	{
		/* @var $userManager UserManager */
		global $AM_ADDRESSDATA_TYPE, $UM_GROUP_BASETYPE_INFOS, $userManager, $addressManager;
		// Icon und Überschrift festlegen
		$this->options["icon"] = "user.png";
		$this->options["icontext"] = "Password ändern";
		$this->options["show_options_cancel"] = false;
		
		// Elemente anlegen
		ob_start();
		?>
			Bitte hinterlegen Sie ein neues Passwort.<br />
			Das neue Passwort muss aus <strong>mindestens fünf Zeichen</strong> bestehen und mindestens <strong>zwei Zahlen</strong> und <strong>zwei Buchstaben</strong> beinhalten.
		<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		$this->elements[] = new CustomHTMLElement($CONTENT);
		
		$this->elements[] = new PwdElement("pwdalt", "Altes Passwort", "", true, $this->error["pwdalt"]);
		$this->elements[] = new PwdElement("pwd", "Neues Passwort", "", true, $this->error["pwd"]);
		$this->elements[] = new PwdElement("pwd2", "Passwort bestätigen", "", true, $this->error["pwd2"]);
		
		
	}
	
	/**
	 * Zu implementierende Funktion, welche die Daten der Elemente speichert
	 */
	public function Store()
	{
		// Passwort setzen
		if (!$this->obj->IsPWDCorrect($this->formElementValues["pwdalt"]))
		{
			$this->error["pwdalt"]="Das eingegebene Passwort ist falsch";
		}
		elseif ($this->formElementValues["pwd"]!=$this->formElementValues["pwd2"])
		{
			$this->error["pwd2"]="Das Passwort und die Bestätigung sind nicht identisch";
		}
		elseif ($this->formElementValues["pwd"]==$this->formElementValues["pwdalt"])
		{
			$this->error["pwd"]="Das neue Passwort darf nicht identisch mit dem alten Passwort sein";
		}
		else
		{
			$retValue=$this->obj->SetPWD($this->formElementValues["pwd"]);
			if ($retValue!==true)
			{
				if ($retValue==-1) $this->error["pwd"]="Bitte geben Sie ein neues Passwort ein";
				if ($retValue==-2) $this->error["pwd"]="Das Passwort muss aus mindestens fünf Zeichen bestehen";
				if ($retValue==-3) $this->error["pwd"]="Das Passwort muss mindestens zwei Zahlen enthalten";
				if ($retValue==-4) $this->error["pwd"]="Das Passwort muss mindestens zwei Buchstaben enthalten";
			}
		}
		if (count($this->error)>0) return false;
		$this->obj->SetPasswordResetRequired(false);
		$returnValue=$this->obj->Store($this->db);
		if ($returnValue!==true)
		{
			$this->error["name"]="Es ist ein unerwarteter Fehler aufgetreten (".$returnValue.")";
			return false;
		}
		// Sonderfall wenn geänderter Benutzer der aktuell angemeldete Benutzer ist -> Dann Sessionobjekt ersetzen
		if ($_SESSION["currentUser"]->GetPKey()==$this->obj->GetPKey())
		{
			$_SESSION["currentUser"]=$this->obj;
		}
		return true;
	}
	
}
?>