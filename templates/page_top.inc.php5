<?php
include("page_head.inc.php5"); // Head-Teil

$name="";
if (isset($_SESSION["currentUser"]))
{
	$name = $_SESSION["currentUser"]->GetUserName();
	$name.= " (".$UM_GROUP_BASETYPE[$_SESSION["currentUser"]->GetGroupBasetype($db)].")";
}
?>
<script type="text/javascript">
	<!--
		function body_on_load()
		{
			if (window.setContentHeight) setContentHeight(); 
			if (window.onloadFunction) onloadFunction();
		}
		function body_on_resize()
		{
			if (window.setContentHeight) setContentHeight(); 
			if (window.calcMenueLayout) calcMenueLayout()
		}
		function body_on_contextmenu()
		{
			<?if(preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT'])){?>
				if (window.hideMenu) hideMenu();
			<?}else{?>
				if (window.showMenu) showMenu();
			<?}?>
		}
		function body_on_click()
		{
			if (window.hideMenu) hideMenu();
		}
		
	-->
</script>
<body style="margin:0px; background-color:#d0d0d0; position:relative;" onload="body_on_load();" <?if (isset($_SESSION["currentUser"])){?>onresize="body_on_resize();" oncontextmenu="body_on_contextmenu();" onclick="body_on_click();"<?}else{?>onresize="javascript:setContentHeight();"<?}?>>
	<?
	if (isset($_SESSION["currentUser"]))
	{
		include("dialog_window.js.php5");
		echo $dynamicTableManager->GetTemplateHtmlString();
	}
	?>
	<div style="width:100%; height:100%;" align="center">
		<div id="div_content" align="left" style="width:1024px; background-color:#ffffff;">
			<div id="logo" style="width:100%; height:50px;">
				<!-- Logo + Userinfo -->
				<span style="position:relative; top:7px; right:7px; float:right; text-align:right; line-height:16px; font-family: Arial, Helvetica, sans-serif; font-size:11px; color:#404040;">
					<?if($MENU_SHOW != false){?>
						Angemeldet als<br />
						<strong><?=$name;?></strong>
					<?}?>
				</span>
				<img src="<?=$DOMAIN_HTTP_ROOT?>/pics/Logo.png" style="position:relative" alt="" />
			</div>

<?
include("menu_content.inc.php5"); // Menübalken
?>