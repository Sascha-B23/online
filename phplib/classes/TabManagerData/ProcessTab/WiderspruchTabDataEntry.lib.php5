<?php
/**
 * TabDataEntry implementation
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class WiderspruchTabDataEntry extends TabDataEntry 
{
	/**
	 * db object
	 * @var DBManager 
	 */
	protected $db = null;
	
	/**
	 * current proccess
	 * @var ProcessStatus
	 */
	protected $processStatus = null;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ProcessStatus $processStatus
	 */
	public function WiderspruchTabDataEntry(DBManager $db, ProcessStatus $processStatus)
	{
		$this->db = $db;
		$this->processStatus = $processStatus;
		parent::__construct(ProcessTabData::TAB_WS, "Widerspruch");
	}
	
	/**
	 * Return if the tab is visible
	 * @return bool 
	 */
	public function IsVisible()
	{
		if (!UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_SHOW_PROCESS_TAB_WIDERSPRUCH)) return false;
		if ($this->processStatus->GetWiderspruch($this->db)==null) return false;
		return true;
	}
	
	/**
	 * Output the HTML for this tabs
	 * @return bool
	 */
	public function PrintContent()
	{
		$abrechnungsjahr = $this->processStatus->GetAbrechnungsJahr();
		$widerspruch = $this->processStatus->GetWiderspruch($this->db);
		$widersprueche = $this->processStatus->GetWidersprueche($this->db);
		if (isset($_POST["showwiderspruch"]))
		{
			for ($a=0; $a<count($widersprueche); $a++)
			{
				if ($widersprueche[$a]->GetPKey()==(int)$_POST["showwiderspruch"])
				{
					$widerspruch=$widersprueche[$a];
					break;
				}
			}
		}
		$wdate = "-";
		$aspName = "-";
		if ($widerspruch!=null)
		{
			$wdate = $widerspruch->GetDatum()>0 ? date("d.m.Y", $widerspruch-GetDatum()) : "-";
			$asp = $widerspruch->GetAnsprechpartner();
			if ($asp!=null)
			{
				$aspName = $asp->GetOverviewString();
			}
		}
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td width="29">&#160;</td>
				<td width="287">&#160;</td>
				<td width="29">&#160;</td>
				<td width="287">&#160;</td>
				<td width="29">&#160;</td>
				<td width="287">&#160;</td>
				<td width="29">&#160;</td>
			</tr>
			<tr>
				<td width="29">&#160;</td>
				<td width="287" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong>Datum</strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=$wdate;?></td>
						</tr>
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong>Ansprechpartner</strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=$aspName;?></td>
						</tr>
					</table>
				</td>
				<td width="29">&#160;</td>
				<td width="287" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td width="60%" align="right" valign="top" class="contentText"><strong>Auswahl Widerspruch</strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText">
								<table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td>
											<select name="showwiderspruch" style="height: 18px;">
										<? 	for($a=0; $a<count($widersprueche); $a++){?>
												<option value="<?=$widersprueche[$a]->GetPKey();?>" <?if($widersprueche[$a]->GetPKey()==$widerspruch->GetPKey())echo "selected";?>>Widerspruch <?=$a+1;?></option>
										<?	}?>
											</select>
										</td>
										<td>
											<input type="submit" value=">" class="formButton" style="width: 18px; height: 18px;" />
										</td>
									</tr>
								</table>
							</td>
						</tr>
					<?	if ($abrechnungsjahr!=null){?>
							<tr>
								<td valign="top" width="60%" align="right" class="contentText"><strong>Widerspruchspunkte</strong></td>
								<td valign="top" width="10px" align="right">&#160;</td>
								<td valign="top" align="left" class="contentText"><a href="showWPs.php5?<?=SID;?>&aj=<?=$abrechnungsjahr->GetPKey();?>&ws=<?=$widerspruch->GetPKey();?>" target="_showWPs">Anzeigen</a></td>
							</tr>
					<?	}?>
					</table>
				</td>
				<td width="29">&#160;</td>
				<td width="287" valign="top">
					
				</td>
				<td width="29">&#160;</td>
			</tr>
		</table>
		<br />
		<?
	}
	
}
?>