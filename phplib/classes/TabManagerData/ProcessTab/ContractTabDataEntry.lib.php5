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
class ContractTabDataEntry extends TabDataEntry 
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
	public function ContractTabDataEntry(DBManager $db, ProcessStatus $processStatus)
	{
		$this->db = $db;
		$this->processStatus = $processStatus;
		parent::__construct(ProcessTabData::TAB_CONTRACT, "Vertrag");
	}
	
	/**
	 * Return if the tab is visible
	 * @return bool 
	 */
	public function IsVisible()
	{
		if (!UserManager::IsCurrentUserAllowedTo($this->db, UM_ACTION_SHOW_PROCESS_TAB_CONTRACT)) return false;
		if ($this->processStatus->GetContract()==null) return false;
		return true;
	}
	
	/**
	 * Output the HTML for this tabs
	 * @return bool
	 */
	public function PrintContent()
	{
		global $DOMAIN_HTTP_ROOT, $lm;
		$contract = $this->processStatus->GetContract();
		if ($contract!=null)
		{
			$mietflaeche_qm = $contract->GetMietflaecheQM();
			if ($mietflaeche_qm==0.0) $mietflaeche_qm="";
			$umlageflaeche_qm = $contract->GetUmlageflaecheQM();
			if ($umlageflaeche_qm==0.0) $umlageflaeche_qm="";
		}
		?>
		<script type="text/javascript">
			<!--
			function EditContract(){
				var newWin=window.open('<?=$DOMAIN_HTTP_ROOT;?>de/meineaufgaben/editContract2.php5?<?=SID;?>&editElement=<?=$contract->GetPKey();?>', '_editContract', 'resizable=1,status=1,location=0,menubar=0,scrollbars=1,toolbar=0,height=800,width=1050');
				//newWin.moveTo(width,height);
				newWin.focus();
			}
			-->
		</script>
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
						<?$this->PrintFileList($contract, FM_FILE_SEMANTIC_MIETVERTRAG, "Mietverträge");?>
					</table>
				</td>
				<td width="29">&#160;</td>
				<td width="287" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td valign="top" width="30%" align="right" class="contentText"><strong>Mietfläche</strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=($mietflaeche_qm!="" ? HelperLib::ConvertFloatToLocalizedString($mietflaeche_qm)." qm" : "-");?></td>
						</tr>
						<?$this->PrintFileList($contract, FM_FILE_SEMANTIC_MIETVERTRAGANLAGE, "Mietvertraganlagen");?>
					</table>
				</td>
				<td width="29">&#160;</td>
				<td width="287" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<? 	if( $_SESSION["currentUser"]->GetGroupBasetype($this->db)>=UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP && !in_array($_SESSION["currentUser"]->GetGroupBasetype($this->db), Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT)) ){?>
							<tr>
								<td valign="top" width="60%" align="right" class="contentText"><strong>Vertrag</strong></td>
								<td valign="top" width="10px" align="right">&#160;</td>
								<td valign="top" align="left" class="contentText"><a href="javascript:EditContract();">Bearbeiten</a></td>
							</tr>
						<?	}?>
						<tr>
							<td width="30%" align="right" valign="top" class="contentText"><strong>Umlagefläche</strong></td>
							<td valign="top" width="10px" align="right">&#160;</td>
							<td valign="top" align="left" class="contentText"><?=($umlageflaeche_qm!="" ? HelperLib::ConvertFloatToLocalizedString($umlageflaeche_qm)." qm" : "-");?></td>
						</tr>
						<?$this->PrintFileList($contract, FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG, "Mietvertragnachträge");?>
					</table>
				</td>
				<td width="29">&#160;</td>
			</tr>
		</table>
		<br />
		<?
	}
	
	/**
	 * Prints a file list with the passed file type
	 * @global type $DOMAIN_HTTP_ROOT
	 * @param Contract $contract
	 * @param type $fileType
	 * @param type $title 
	 */
	protected function PrintFileList(Contract $contract, $fileType, $title)
	{
		global $DOMAIN_HTTP_ROOT;
		$fileList = Array();
		$loaction = null;
		$group = null;
		if ($contract!=null)
		{
			$location = $this->processStatus->GetLocation();
			if ($location!=null)
			{
				$company = $location->GetCompany();
				if ($company!=null)
				{
					$group = $company->GetGroup();
					if ($group!=null)
					{
						if ($fileType!=FM_FILE_SEMANTIC_UNKNOWN)
						{
							$fileList = $contract->GetFiles($this->db, $fileType);
							if ($fileType==FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG)
							{
								$ws = $this->processStatus->GetWiderspruch($this->db);
								if ($ws!=null)
								{
									$aws = $ws->GetAntwortschreiben($this->db);
									for ($a=0; $a<count($aws);$a++)
									{
										$filesTemp = $aws[$a]->GetFiles($this->db, FM_FILE_SEMANTIC_NACHTRAG);
										for ($b=0; $b<count($filesTemp);$b++)
										{
											$fileList[] = $filesTemp[$b];
										}
									}
								}
							}
						}
					}
				}
			}
		}?>
		<tr>
			<td valign="top" width="30%" align="right" class="contentText"><strong><?=$title;?></strong></td>
			<td valign="top" width="10px" align="right">&#160;</td>
			<td valign="top" align="left" class="contentText">
			 <?	for ($a=0; $a<count($fileList); $a++)
				{
					$download_file_name = FileManager::GetDownloadFileName('VG', $group, $location, $fileList[$a]);
					?><a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($fileList[$a], $download_file_name);?>"><?=$fileList[$a]->GetFileName();?></a><br><?
				}
				if (count($fileList)==0) echo "-"; ?>
			</td>
		</tr>
		<?
	}
	
}
?>