<?
// Includes
$DOMAIN_NAME="NKAS";	// Name der Domain für Include Files
$libDir="/WWW/nebenkostenabrechnungssystem/phplib/";
$USER_HAVE_TO_BE_LOGGED_IN=false;
require_once("phplib/session.inc.php5");

$MENU_SHOW = false; // Kein Menü und Username zeigen 
include("page_top.inc.php5");

// Login vornehmen
if ($_POST["loging"]==="true")
{
	if (trim($_POST["userLogin"])!="" && trim($_POST["userPwd"])!="")
	{
		$returnValue=$userManager->Login($_POST["userLogin"], $_POST["userPwd"]);
		if ($returnValue!=null && is_a($returnValue, 'User'))
		{
			$_SESSION["currentUser"] = $returnValue;
			//Listmanagerconfig laden
			$_SESSION["listmanager"] = $_SESSION["currentUser"]->GetListmanagerConfig();

			$REDIRECT_TARGET_URL=$DOMAIN_HTTP_ROOT."de/meineaufgaben/meineaufgaben.php5?UID=".$UID;
			include($SHARED_FILE_SYSTEM_ROOT."templates/redirect.php5");
			exit;
		}
	}
	else
	{
		$returnValue = -1;
	}
}
else
{
	if (isset($_SESSION["currentUser"]) && $_SESSION["currentUser"]!=null &&  get_class($_SESSION["currentUser"])=="User" )
	{
		// Session ist bereits aktiv -> neue Session erzeugen
		$REDIRECT_TARGET_URL = $DOMAIN_HTTP_ROOT;
		include($SHARED_FILE_SYSTEM_ROOT."templates/redirect.php5");
		exit;
	}
}
ob_start();
?>

<script language="JavaScript">
<!--
	function onloadFunction(){}

	function on_key_pressed(key_event)
	{
		if (!key_event)
		{
			key_event = window.event;
		}
		// If Enter-Key is pressed submit form
		if (key_event.keyCode == 13) 
		{
			document.forms.loginform.submit();
		}
	}
-->
</script>

<div style="width:60px; height:25px; position:relative; left:343px; top:193px;">
	<span style="font-family: Arial, Helvetica, sans-serif; font-size:15px; color:#CC0033; font-weight:bold;">
		LogIn
	</span>
</div>

<div style="width:346px; height:212px; position:relative; left:313px; top:190px; background: url(./pics/login_background.png); background-repeat:no-repeat;">
	<form action="<?=$DOMAIN_HTTP_ROOT?>index.php5" method=POST id="loginform">
		<input type=hidden name="loging" value="true">
		<table cellpadding="0" cellspacing="0" border="0" style="width:320px; position:relative; top:50px; left:10px; font-family: Arial, Helvetica, sans-serif; font-size:11px; color:#404040;">
			<tr style="vertical-align:bottom;">
				<td width="80px"><span style="float:right;">Login</span></td>
				<td width="10px">&#160;</td>
				<td width="210px"><input type="text" style="width:210px; height:21px; border:1px solid #EBEBEB;" name="userLogin" id="userLogin" value="<?=$_POST["userLogin"];?>" /></td>
			</tr>
			<tr style="height:31px;">
				<td>&#160;</td>
				<td>&#160;</td>
				<td>&#160;</td>
			</tr>
			<tr style="vertical-align:bottom;">
				<td><span style="float:right;">Passwort</span></td>
				<td>&#160;</td>
				<td><input type="password" onkeydown="on_key_pressed()" style="width:210px; height:21px; border:1px solid #EBEBEB;" name="userPwd" id="userPwd" /></td>
			</tr>
			<tr style="height:55px;">
				<td>&#160;</td>
				<td>&#160;</td>
				<td style="vertical-align:top;">
					<div id="error_text" style="position:relative; top:5px; width:207px; height:47px; overflow:auto; font-size:9px; color:#FD0F40">
						<?
						// Fehler bei der Anmeldung
						if( isset( $returnValue ) ){
							$error="Unbekannter Fehler - bitte versuchen Sie es zu einem späteren Zeitpunkt erneut";
							if( $returnValue==-1 )$error="Die eingegebenen Login-Daten sind nicht korrekt.<br>Hinweis: Bei drei falschen Kennwort-Eingaben wird Ihr Zugang aus Sicherheitsgründen gesperrt.";
							if( $returnValue==-2 )$error="Ihr Zugang wurde aufgrund zu vieler falscher Kennworteingaben gesperrt. Bitte nehmen Sie Kontakt mit uns auf.";
							?><span class='errorText'><?=$error;?></span><?	
						}		
						?>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="3"><span style="float:right; font-weight:bold; position:relative; left:13px;"><a style="text-decoration: none; color:#404040;" href="javascript:document.forms.loginform.submit();" id="submit">Anmelden <img src="<?=$DOMAIN_HTTP_ROOT?>pics/login_arrow.gif" alt="" style="border:0px;" />&#160;&#160;&#160;</a></span></td>
			</tr>
			<tr style="height:30px; vertical-align:bottom;">
				<td colspan="3" style="font-size:9px; line-height:10px; color:#8B8B8B">
					<span style="position:relative; left: 20px;">
						&copy; <?=date('Y');?> <?=$lm->GetString('SYSTEM', 'ID_COMPANY_NAME');?> - <?=$lm->GetString('SYSTEM', 'ID_APPLICATION_NAME');?> (Rev. <?=APPLICATION_REVISION;?>)
					</span>
				</td>
			</tr>
		</table>
	</form>
	<br />
</div>
<?
$CONTENT = ob_get_contents();
ob_end_clean();
include("template_1row.inc.php5"); // Content includen
include("page_bottom.inc.php5"); // Ende Content, Javascript Teil um Höhe zu ermitteln, </body></html>
?>