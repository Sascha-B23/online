<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<HTML>
	<HEAD>
		<TITLE>KIM-Online</TITLE>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
		<meta http-equiv='expires' content='0'>
		<META name='robots' content='noindex, nofollow'>
	</HEAD>

	<body bgcolor='#ffffff' alink='#cc0000' link='#333333' vlink='333333' topmargin='0' leftmargin='0' MARGINHEIGHT='0' MARGINWIDTH='0'>
		<center>
			<table cellspacing=0 cellpadding=0 border=0 width=100% height=100%>
				<tr>
					<td align='center' valign='middle'>
						<table border=0 cellpadding=0 cellspacing=0 width='650'>
							<tr>
								<td style="border-left:1px solid #eaeaea; border-top:1px solid #eaeaea; border-right:1px solid #eaeaea"><img src='<?=$SHARED_HTTP_ROOT;?>pics/logo.png'></td>
							</tr>
							<tr>
								<td align='center' valign='middle' style="border-left:1px solid #eaeaea; border-bottom:1px solid #eaeaea; border-right:1px solid #eaeaea">
									<table border=0 cellpadding=0 cellspacing=0 width='542'>
										<tr>
											<td colspan="3"><img src='<?=$SHARED_HTTP_ROOT;?>pics/blind.gif' height='20' width=6></td>
										</tr>
										<tr>
											<td width="100%" align="left" valign="top" style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 15px; color: #cc0000; line-height: 14pt;">
												<strong>
													Wartungsarbeiten vom <?=date("d.m.Y", $SHOW_CONSTRUCTION_SITE['datefrom']);?> <?=date("G", $SHOW_CONSTRUCTION_SITE['datefrom']);?> Uhr bis <?=date("d.m.Y", $SHOW_CONSTRUCTION_SITE['dateuntil']);?> <?=date("G", $SHOW_CONSTRUCTION_SITE['dateuntil']);?> Uhr
												</strong>
											</td>
										</tr>
										<tr>
											<td align="left" valign="top" style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #535757; line-height: 12pt;">
												<br>
												<br>
												<strong>Sehr geehrte Kunden,</strong><br>
                                                <br>
												wir entwickeln ständig unsere Software für Sie weiter. 
												Nun ist es wieder so weit, ein neues Softwareupdate mit 
												vielen Verbesserungen wird gerade auf unserem Server 
												installiert. Deshalb steht unsere Software 
												KIM-Online vom <?=date("d.m.Y", $SHOW_CONSTRUCTION_SITE['datefrom']);?> <?=date("G", $SHOW_CONSTRUCTION_SITE['datefrom']);?> Uhr
												bis zum <?=date("d.m.Y", $SHOW_CONSTRUCTION_SITE['dateuntil']);?> <?=date("G", $SHOW_CONSTRUCTION_SITE['dateuntil']);?> Uhr nicht zur Verfügung.<br>
                                                <br>
												Vielen Dank für Ihr Verständnis.
											  <br>
											  <br>
											</td>
										</tr>
										<tr>
											<td colspan="3"><img src='<?=$SHARED_HTTP_ROOT;?>pics/blind.gif' height='30' width=6></td>
										</tr>
								  </table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</center>
	</body>
</html>