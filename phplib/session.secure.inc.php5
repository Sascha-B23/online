<?php
/*****************************************************************************
* Folgende Variablen müssen VOR dem Einbinden dieses Skriptes gesetzt werden:
* $USER_MANAGER_ACTION Handelt es sich um eine bestimmte Aktion, von welcher die Berechtigung entnommen werden kann?
* $MIN_GROUP_BASETYPE_NEED		Welcher Gruppe muss der Benutzer (min.) angehörig sein, damit er diese Seite einsehen darf?
/*****************************************************************************/

if (isset($USER_MANAGER_ACTION))
{
	if (!UserManager::IsCurrentUserAllowedTo($db, $USER_MANAGER_ACTION))
	{
		// Benutzer darf diese Seite nicht einsehen
		$additionalInfos = "Referer: ".(trim($_SERVER["HTTP_REFERER"])!="" ? $_SERVER["HTTP_REFERER"] : "-");
		$em->ShowError("session.secure.inc.php5", "Zugriff für Benutzer '".$_SESSION["currentUser"]->GetUserName()."' verweigert (4)<br/>\n".$additionalInfos);
		exit;
	}
}
else
{
	if(!isset($MIN_GROUP_BASETYPE_NEED))
	{
		$MIN_GROUP_BASETYPE_NEED = UM_GROUP_BASETYPE_NONE;
	}
	if(!isset($EXCLUDE_GROUP_BASETYPES))
	{
		$EXCLUDE_GROUP_BASETYPES = Array();
	}

	if(!isset($USER_HAVE_TO_BE_LOGGED_IN) || $USER_HAVE_TO_BE_LOGGED_IN !== false)
	{
		/* @var $_SESSION ["currentUser"] User */
		// Ist ein Benutzer-Objekt in der Session registriert?
		if(isset($_SESSION["currentUser"]) && $_SESSION["currentUser"] != null && get_class($_SESSION["currentUser"]) == "User")
		{
			// Darf der Benutzer diese Seite einsehen?
			if($_SESSION["currentUser"]->GetGroupBasetype($db, false) < $MIN_GROUP_BASETYPE_NEED)
			{
				// Benutzer darf diese Seite nicht einsehen
				$additionalInfos = "Referer: " . (trim($_SERVER["HTTP_REFERER"]) != "" ? $_SERVER["HTTP_REFERER"] : "-");
				$em->ShowError("session.secure.inc.php5",
					"Zugriff für Benutzer '" . $_SESSION["currentUser"]->GetUserName() . "' verweigert (2)<br/>\n" . $additionalInfos);
				exit;
			}
			// Ist eine Benutzergruppe explizit ausgeschlossen?
			foreach($EXCLUDE_GROUP_BASETYPES as $value)
			{
				if($_SESSION["currentUser"]->GetGroupBasetype($db) == $value)
				{
					// Benutzer darf diese Seite nicht einsehen
					$additionalInfos = "Referer: " . (trim($_SERVER["HTTP_REFERER"]) != "" ? $_SERVER["HTTP_REFERER"] : "-");
					$em->ShowError("session.secure.inc.php5",
						"Zugriff für Benutzer '" . $_SESSION["currentUser"]->GetUserName() . "' verweigert (3)<br/>\n" . $additionalInfos);
					exit;
				}
			}
			// Muss der Benutzer sein Passwort ändern?
			if($_SESSION["currentUser"]->IsPasswordResetRequired() && !$PASSWORD_CHANGE_SITE)
			{
				// Benutzer darf diese Seite nicht einsehen
				$REDIRECT_TARGET_URL = $DOMAIN_HTTP_ROOT . "de/meinedaten/changePassword.php5?" . SID;
				include($SHARED_FILE_SYSTEM_ROOT . "templates/redirect.php5");
				exit;
			}
		}
		else
		{
			// Kein Session-Objekt registriert -> Abbrechen
			$REDIRECT_TARGET_URL = $DOMAIN_HTTP_ROOT;
			include($SHARED_FILE_SYSTEM_ROOT . "templates/redirect.php5");
			exit;
		}
	}
}
?>