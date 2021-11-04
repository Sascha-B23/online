<? 
/*	Erwartete Übergabe in Variable $protokoll :
	Array[index]['date']
	Array[index]['username']
	Array[index]['title']
	Array[index]['text']
	
*/
global $SHARED_HTTP_ROOT; ?>
<!---------------->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="center"> <br />
			<table width="922" border="0" cellpadding="0" cellspacing="10" bgcolor="#ffffff">
				<tr>
					<td>
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<?	for($a=0; $a<count($protokoll); $a++){ ?>
							<tr>
								<td width="110" align="left" valign="top" class="smallText"><img src="<?=$SHARED_HTTP_ROOT?>pics/blind.gif" alt="" height="2" width="5"/><br />
								[<?=date("d.m.Y", $protokoll[$a]["date"]);?> &ndash; <?=date("H:i", $protokoll[$a]["date"]);?>]<br />
								<?=$protokoll[$a]["username"];?></td>
								<td width="25" align="left" valign="top">&nbsp;</td>
								<td width="767" align="left" valign="top"><?if( $protokoll[$a]["title"]!="" ){?><strong><?=$protokoll[$a]["title"];?></strong><br /><?}?>
								<?=$protokoll[$a]["text"];?></td>
							</tr>
							<?	if( $a<count($protokoll)-1 ){ ?>
								<tr>
									<td colspan="3" height="20" align="left" valign="middle"><img src="<?=$SHARED_HTTP_ROOT?>pics/liste/1px_eeeeee.gif" alt="" width="902" height="1" /></td>
								</tr>
							<?	}
							}?>
						</table>
					</td>
				</tr>
			</table>
			<br />
			<br />
		</td>
	</tr>
</table>
<!---------------->