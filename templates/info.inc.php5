<? 
/*	Erwartete Übergabe in Variable $info:
	Array['type'] (0 = Warning)
	Array['text']
*/
global $SHARED_HTTP_ROOT; 
$picture = '';
$borderColor = 'ffffff';
switch($info['type'])
{
	case 0:
		$picture = 'warning.png';
		$borderColor = '960000';
		break;
}
?>
<!---------------->
<table width="100%" border="0" cellpadding="0" cellspacing="10" bgcolor="#ffffff" style="border: #<?=$borderColor;?> solid 1px">
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td style="width: 40px; vertical-align: top;"><?if($picture!=''){?><img src="<?=$SHARED_HTTP_ROOT.'pics/gui/'.$picture;?>" border="0" /><?}?></td>
					<td style="vertical-align: middle;"><?=$info['text']?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<!---------------->