<?php
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
require_once("../phplib/session.inc.php5");

$MENU_SHOW = true;
include("page_top.inc.php5");
//Listmanagerconfig speichern
LoggingManager::GetInstance()->Log(new LoggingLogin($_SESSION["currentUser"]->GetEMail(), $_SESSION["currentUser"]->GetUserName(), LoggingLogin::LOGIN_STATUS_LOGOUT));
$_SESSION["currentUser"]->StoreListmanagerConfig($db);
SessionManager::GetInstance()->DestroySession();
?>
<script type="text/javascript">
	onload = document.location = "<?=$DOMAIN_HTTP_ROOT?>";
</script>