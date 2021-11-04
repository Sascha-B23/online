<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
	<HEAD>
		<META name="robots" content="noindex, follow">
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
		<!--<META http-equiv="refresh" content="3; URL=<?=$REDIRECT_TARGET_URL;?>">-->
		<LINK REL="STYLESHEET" TYPE="text/css" HREF="<?=$DOMAIN_HTTP_ROOT?>css/content.css">
	</HEAD>
	<BODY style="margin:0px; padding:0px;">
		<script language="javascript">
			//<!--
				document.location="<?=$REDIRECT_TARGET_URL;?>";
			//-->
		</script>
		<table border="0" cellspacing="4" cellpadding="4" width="808px">
			<tr>
				<td width="800px">
					<? if(!isset($LANG) || $LANG=="de"){?>
					<a href="<?=$REDIRECT_TARGET_URL;?>">Wenn Sie nicht automatisch weitergeleitet werden, klicken Sie bitte hier!</a><br>
					<? }?>
					<? if(!isset($LANG) || $LANG!="de"){?>
					<a href="<?=$REDIRECT_TARGET_URL;?>">If you are not forwarded automatically, please click here!</a><br>
					<? }?>
				</td>
			</tr>
		</table>
	</BODY>
</HTML>