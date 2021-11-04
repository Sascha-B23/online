<?php
require_once("StoreParameters.php5");
require_once("ValidationParameters.php5");
require_once("DataClasses/DataRow.php5");

/**
 * Handles import and export in csv format
 *
 * @author ngerwien
 */
class ImportManager 
{
	/**
	 * database access
	 * @var Database
	 */
	protected $db = null;
	
	/**
	 * array of imported column names
	 * @var array
	 */
	protected $importDatahead = null;
	
	/**
	 * data array for imported data
	 * @var array
	 */
	protected $importDataRows = null;
	
	/**
	 * CustomerManager
	 * @var CustomerManager
	 */
	protected $customerManager = null;
        
        /**
	 * All Shops in DB
	 * @var array
	 */
	protected $countries = null;
	protected $shops = null;
	protected $locations = null;
	protected $companies = null;
	protected $groups = null;
	
        /**
	 * All Users in DB
	 * @var array
	 */
	protected $users = null;
        
	/**
	 * Constructor
	 * @param DBManager $db
	 */
        
	public function ImportManager($db) 
	{
		$this->db = $db;
		$this->customerManager = new CustomerManager($db);
	}
	
	/**
	 * get export data as csv file
	 * @return csv string
	 */
	public function GetExportContent()
	{
		LoggingManager::GetInstance()->Log(new LoggingCustomerSync(LoggingCustomerSync::TYPE_EXPORT));
                
		$this->LoadAllShops();
		$tmpDataRow = new DataRow();
		$body = "";
		foreach($this->shops as $shop) {
		    $tmpDataRow->initWithShop($shop);
		    $body .= $tmpDataRow->ToCsvString();	    
		}
		
		$header = $tmpDataRow->GetExportHeader();
		
		return utf8_decode($header . $body);
	}
	
	/**
	 * shows the results of the imported data
	 */
	public function ShowImportResult() 
	{
		global $SHARED_HTTP_ROOT;
		if( count($this->importDataRows) <= 0 ) {
			return false;
		}
		$_SESSION["importDataRows"] = null;
		ob_start();
		?>
		<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%;">
			<tr>
				<td valign="top" style="height:29px;">
					<table style="width:100%; height:29px; background:url(<?=$SHARED_HTTP_ROOT?>pics/liste/dif_background.jpg);" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td width="200">
								<table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/snycronisation.png" alt="" style="width:28px; height:27px;" /></td>
										<td><strong>Datensätze</strong></td>
									</tr>
								</table>
							</td>
							<td align="right">
								<table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td><div style="width:15px; height:15px; background-color:#ffffff; border:1px solid #CBCBCB; border-radius:5px;"></div></td>
										<td width="5"></td>
										<td>Keine Änderungen</td>
										<td width="21"></td>
										<td><div style="width:15px; height:15px; background-color:#D0E8F2; border:1px solid #CBCBCB;"></div></td>
										<td width="5"></td>
										<td>Neu hinzufügen</td>
										<td width="21"></td>
										<td><div style="width:15px; height:15px; background-color:#75BC18; border:1px solid #CBCBCB;"></div></td>
										<td width="5"></td>
										<td>Änderung</td>
										<td width="21"></td>
										<td><div style="width:15px; height:15px; background-color:#DA8A01; border:1px solid #CBCBCB;"></div></td>
										<td width="5"></td>
										<td>Fehlerhaft</td>
										<td width="21"></td>
									</tr>
								</table>
							</td>
							<td width="27">&#160;</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<div id="floaterDiv" name="floaterDiv" style="width:900px; height:471px; overflow:scroll; border-bottom: 1px solid #C2C2C2;">
						<form name="table_import" action="#" method="post">
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td valign="bottom" style="height:32px; width:30px; border-right:1px solid #CACACA; border-bottom:2px solid #000000;"><strong>CSV Zeile</strong></td>
									<?  $dummyDataRow = new DataRow(); for($i=0; $i<$dummyDataRow->GetColumnCount(); $i++){?>
                                                                        <td valign="bottom" style="height:32px; border-right:1px solid #CACACA; border-bottom:2px solid #000000;"><strong><?=$dummyDataRow->GetColumnNameByIndex($i)?></strong></td>
									<?}?>
									<td style="height:32px; width:51px; border-right:1px solid #CACACA; border-bottom:2px solid #000000;"><strong>IMPORT</strong></td>
								</tr>
								<?for($i=0; $i<count($this->importDataRows); $i++){
								    $dataRow = $this->importDataRows[$i];
								?>
									<tr>
										<td valign="top" height="30" style="border-right:1px solid #CACACA; border-bottom:2px solid #CACACA;"><?=$i?></td>
										<?for($a=0; $a<count($this->importDatahead); $a++){?>
											<? 
											switch($dataRow->getValidationStateForColumn($this->importDatahead, $a)) {
                                                                                                case ValidationStates::$STATE_CANNOT_BE_CHANGED:
												case ValidationStates::$STATE_HAS_TO_EXIST_IN_DB:
												case ValidationStates::$STATE_ISREQUIRED:
												case ValidationStates::$STATE_INVALID_VALUE:
													$statuscolor = "#DA8A01;";
													break;
												
												case ValidationStates::$STATE_CHANGED:
													$statuscolor = "#75BC18;"; 
													break;
												
												case ValidationStates::$STATE_NEW:
													$statuscolor = "#D0E8F2;";
													break;												
												
												case ValidationStates::$STATE_NOCHANGE:
												default:
													$statuscolor = "#FFFFFF;";
											}
											?>
											<td height="30" valign="top" style="background-color:<?=$statuscolor?> border-right:1px solid #CACACA; border-bottom:2px solid #CACACA;"><?=$this->importDataRows[$i]->GetColumnValue($this->importDatahead[$a])?><?if($dataRow->GetColumnValue($this->importDatahead[$a])==""){?>&#160;<?}?></td>
										<?}?>
										<td height="30" align="center" style="border-right:1px solid #CACACA; border-bottom:2px solid #CACACA;">
										<?	if($dataRow->IsValid() && $dataRow->HasChanged()) {//if($this->checkData[$i]["valid"] == true){
												?><input type="checkbox" name="checkbox_<?=$i?>" id="checkbox_<?=$i?>" /><?
												$checkboxen[$i] = "checkbox_".$i;												
												$_SESSION["importDataRows"][$i]["data"] = $dataRow;
												$_SESSION["importDataRows"][$i]["checkboxName"] = $checkboxen[$i];
											}else{
												if(!$dataRow->HasChanged()) {//if($this->checkData[$i]["allreadyExist"]===true){
													?><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/check.gif" width="18" height="18" alt="<?=$dataRow->GetValidationErrorMessage()?>" title="<?=$dataRow->GetValidationErrorMessage()?>" /><?
												}else{
													?><img src="<?=$SHARED_HTTP_ROOT?>pics/gui/warning.png" alt="<?=$dataRow->GetValidationErrorMessage()?>" title="<?=$dataRow->GetValidationErrorMessage()?>" /><?
												}
											}?>
										</td>
									</tr>
								<?}
                                                                if(!empty($_SESSION["importDataRows"])) {
                                                                    array_multisort($_SESSION["importDataRows"]);
                                                                }
								?>
							</table>
							<input type="hidden" name="filename" value="<?=$this->filename?>" />
						</form>
					</div>
					<script type="text/javascript">
							
							function GetWindowWidth() 
							{
								if (window.innerWidth) 
								{
									return window.innerWidth;
								} 
								else if (document.body && document.body.offsetWidth) 
								{
									return document.body.offsetWidth;
								} 
								else 
								{
									return 0;
								}
							}
							
							function GetWindowHeight() 
							{
								if (window.innerHeight) 
								{
									return window.innerHeight;
								} 
								else if (document.body && document.body.offsetHeight) 
								{
									return document.body.offsetHeight;
								} 
								else 
								{
									return 0;
								}
							}

							window.onresize = function()
							{
								$('floaterDiv').style.width = (GetWindowWidth()-50)+"px";
								$('floaterDiv').style.height = (GetWindowHeight()-209)+"px";
							}
							$('floaterDiv').style.width = (GetWindowWidth()-50)+"px";
							$('floaterDiv').style.height = (GetWindowHeight()-209)+"px";
						</script>
				</td>
			</tr>
			<tr>
				<td valign="top" style="height:100px;">
					<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
						<tr>
							<td colspan="4" style="height:8px;"></td>
						</tr>
						<tr>
							<td colspan="3">
								<?
									if( is_array( $checkboxen ) == true ){
										array_multisort($checkboxen);
									}
								?>
								<script type="text/javascript">
									var marked = false;
									function checkMarked(){
										for (var i=0; i< <?=count($checkboxen)?>; i++){
											if( document.getElementById("checkbox_"+i) != undefined && typeof(document.getElementById("<?=$checkboxen[$i]?>")) != undefined ){
												if( document.getElementById("checkbox_"+i).checked == true){
													marked = true;
												}else{
													marked = false;
													break;
												}
											}
										}
									}
									function allesMarkieren(){
										marked = true;
										<?for($i=0; $i<count($checkboxen); $i++){?>
											if( document.getElementById("<?=$checkboxen[$i]?>") != undefined && typeof(document.getElementById("<?=$checkboxen[$i]?>")) != undefined ){
												document.getElementById("<?=$checkboxen[$i]?>").checked = true;
											}
										<?}?>
									}
									function nichtsMarkieren(){
										marked = false;
										<?for($i=0; $i<count($checkboxen); $i++){?>
											if( document.getElementById("<?=$checkboxen[$i]?>") != undefined && typeof(document.getElementById("<?=$checkboxen[$i]?>")) != undefined ){
												document.getElementById("<?=$checkboxen[$i]?>").checked = false;
											}
										<?}?>
									}
									function checkboxenMarkieren(){
										checkMarked()
										if(marked == false){
											allesMarkieren();
										}else if(marked == true){
											nichtsMarkieren();
										}

									}
									var check_for_send = false;
									function checkForSend(){
										<?for($i=0; $i<count($checkboxen); $i++){?>
											if( document.getElementById("<?=$checkboxen[$i]?>") != undefined && typeof(document.getElementById("<?=$checkboxen[$i]?>")) != undefined ){
												if( document.getElementById("<?=$checkboxen[$i]?>").checked == true ){
													check_for_send = true;
												}
											}
										<?}?>

										if(check_for_send == true){
											document.forms.table_import.submit();
										}else{
											alert("Bitte erst einen Datensatz auswählen");
										}
									}

								</script>
								<img src="<?=$SHARED_HTTP_ROOT?>pics/kundensynchronisation/alle_markieren.png" align="right" onclick="checkboxenMarkieren();" alt="" style="cursor:pointer;cursor:hand;" />
							</td>
							<td style="width:17px;"></td>
						</tr>
						<tr>
							<td colspan="4" style="height:12px;"></td>
						</tr>
						<tr>
							<td>
								<img src="<?=$SHARED_HTTP_ROOT?>pics/kundensynchronisation/abbrechen.png" onclick="window.close();" align="right" alt="" style="cursor:pointer;cursor:hand;" />
							</td>
							<td style="width:5px;"></td>
							<td style="width:75px;">
								<img src="<?=$SHARED_HTTP_ROOT?>pics/kundensynchronisation/importieren.png" align="right" onclick="checkForSend()" alt="" style="cursor:pointer;cursor:hand;" />
							</td>
							<td style="width:17px;"></td>
						</tr>				
					</table>
				</td>
			</tr>
		</table>
		<?
		$HTMLTags = ob_get_contents();
		ob_end_clean();
		print_r($HTMLTags);
		return 0;
	}
	
	/**
	 * upload data from csv file
	 * 
	 * @param string 	$path			path to upload file
	 * @param bool		$upload			already uploaded (false), upload file (true)
	 * @return 			int 			0 success
	 *									1 wrong file ending
	 *									2 file not readable
	 *									3 empty path
	 *									4 file does not exist
	 *									5 upload error
	 *									6 empty csv file
	 *									
	 */
	public function UploadCsvFile($path) {
		if($path == "") 
		{
			return 3;
		}
		
		if(stristr($path["userfile"]["name"], ".csv") == "") 
		{
			return 1;
		}

		$this->uploadFilename = $_FILES['userfile']['tmp_name'];

		if(!file_exists($this->uploadFilename)) 
		{
			return 5;
		}

		if(!is_readable($this->uploadFilename)) 
		{
			return 2;
		}
		
		return $this->ReadCsvFile($this->uploadFilename);
	}
	
	/**
	 * fetch checked import data from session and store in db
	 * @return bool/array success(true) or an array with errorcodes
	 */
	public function StoreImportData() 
	{
		LoggingManager::GetInstance()->Log(new LoggingCustomerSync(LoggingCustomerSync::TYPE_IMPORT));
		
        $this->LoadAndIndexAll();
                
		$dataRows = null;
		for($i = 0; $i < count($_SESSION["importDataRows"]); $i++) 
		{
			//get only checked rows
			if($_POST[$_SESSION["importDataRows"][$i]["checkboxName"]] == "on") 
			{
				$dataRows[$i] = $_SESSION["importDataRows"][$i]["data"];
			}
		}
		array_multisort($dataRows);
		
		$errors = array();
		for($i = 0; $i < count($dataRows); $i++) 
		{
			$result = $dataRows[$i]->store($this->db, $this->shops, $this->locations, $this->companies);
			if($result !== true) 
			{
				$errors[] = array("fmsId" => $dataRows[$i]->GetColumnValue(ColumnFMSID::GetColumnName()), "errorCode" => $result);
			}
		}
		
		return (count($errors) == 0) ? true : $errors;
	}
	
	/**
	 * reades csv file
	 * @return int see error codes from "UploadCsvFile"
	 */
	private function ReadCsvFile($fileName) 
	{	
		$this->LoadAndIndexAll();        
            
		$fileContent = utf8_encode(file_get_contents($fileName));
		$lines = explode("\n", $fileContent);
		if(empty($lines[count($lines) - 1])) 
		{
			unset($lines[count($lines) - 1]);
        }                
                
		$dataRows = null;
		$dataHead = null;
		$validationParameters = new ValidationParameters();
		$validationParameters->db = $this->db;
		$validationParameters->users = $this->users;
		$validationParameters->countries = $this->countries;

		foreach ($lines as $line) 
		{
			$csvRow = explode(";", $line);
			if($dataHead===null && $this->IsHead($csvRow))
			{
				$dataHead = Array();
				for ($column=0; $column<count($csvRow); $column++) 
				{
					if (trim($csvRow[$column])=="") 
					{
						continue;
					}
					$dataHead[$column] = trim($csvRow[$column]);
				}
			}
			elseif ($dataHead!==null)
			{
				$dataRow = new DataRow();
				$dataRow->initWithCsvRow($dataHead, $csvRow);
                                
				$validationParameters->shop = $this->shops[$dataRow->GetColumnValue(ColumnFMSID::GetColumnName())];
				if($validationParameters->shop == null) {
					$validationParameters->location = $this->locations[trim($dataRow->GetColumnValue(ColumnLocationName::GetColumnName()))][trim($dataRow->GetColumnValue(ColumnCompanyName::GetColumnName()))][trim($dataRow->GetColumnValue(ColumnGroupName::GetColumnName()))];
					//$validationParameters->company = $this->companies[$dataRow->GetColumnValue(ColumnCompanyName::GetColumnName())];
					$validationParameters->company = $this->companies[self::GetIndexStringForCompanyByDataRow($dataRow)];
					$validationParameters->group = $this->groups[$dataRow->GetColumnValue(ColumnGroupName::GetColumnName())];
				}
				else {
					$validationParameters->location = $validationParameters->shop->GetLocation();
					$validationParameters->company = $validationParameters->shop->GetLocation()->GetCompany();
					$validationParameters->group = $validationParameters->shop->GetLocation()->GetCompany()->GetGroup();
				}

				$dataRow->Validate($validationParameters);
				$dataRows[] = $dataRow;
			}
		}
		
		if ($dataHead==null) return 6;
		if($dataRows == null) return 6;

		$this->importDatahead = $dataHead;
		$this->importDataRows = $dataRows;
		//$this->CheckImportData();
		
		return 0;
	}
			
	private function IsHead($csvRow) 
	{
		for ($column = 0; $column < count($csvRow); $column++)
		{
			if ($csvRow[$column] == ColumnFMSID::GetColumnName()) 
			{
				return true;
			}
		}
		return false;
	}
        
        private function LoadAllShops() {
            if($this->shops != null) {                
                return;                
            }
            
            $this->shops = $this->customerManager->GetShops($_SESSION["currentUser"], "", "name", 0, 0, 0, "");
        }
        
        private function LoadAndIndexAll() {
            if($this->shops != null) {                
                return;                
            }
            $this->countries = $this->customerManager->GetCountries();
            
			//index shops and location
			$shops = $this->customerManager->GetShops($_SESSION["currentUser"], "", "name", 0, 0, 0, "");
			$this->shops = array();
            $this->locations = array();
            foreach($shops as $shop) {
                $this->shops[$shop->GetRSID()] = $shop;
				$location = $shop->GetLocation();
				$company = $location->GetCompany();
				$group = $company->GetGroup();
                $this->locations[trim($location->GetName())][trim($company->GetName())][trim($group->GetName())] = $shop->GetLocation();
            }
            
			//index companies and groups
			$companies = $this->customerManager->GetCompanys($_SESSION["currentUser"], "", CCompany::TABLE_NAME.".name", 0, 0, 0);
			$this->companies = array();
            $this->groups = array();
			foreach($companies as $company) {
                //$this->companies[$company->GetName()] = $company;
				$this->companies[self::GetIndexStringForCompanyByCompany($company)] = $company;
                $this->groups[$company->GetGroup()->GetName()] = $company->GetGroup();
            }
			
            global $userManager;
            $users = $userManager->GetUsers($_SESSION["currentUser"], "", "name", 0, 0, 0, UM_GROUP_BASETYPE_NONE);
            $this->users = array();
            foreach($users as $user) {
                $this->users[utf8_decode($user->GetName())][utf8_decode($user->GetFirstName())] = $user;
            }
        }
        
        /**
	 * returns a unique identifier string for a company
         * 
         * @param CCompany $company
	 * @return string
	 */
        static public function GetIndexStringForCompanyByCompany($company) {
            $indexString = trim($company->GetName());
            $indexString .= trim($company->GetStreet());
            $indexString .= trim($company->GetCity());
            $indexString .= trim($company->GetZIP());
            
            return $indexString;
        }
        
        /**
	 * returns a unique identifier string for a company
         * 
         * @param DataRow $dataRow
	 * @return string
	 */
        static public function GetIndexStringForCompanyByDataRow($dataRow) {
            $indexString = trim($dataRow->GetColumnValue(ColumnCompanyName::GetColumnName()));
            $indexString .= trim($dataRow->GetColumnValue(ColumnCompanyAdressStreet::GetColumnName()));
            $indexString .= trim($dataRow->GetColumnValue(ColumnCompanyAdressLocation::GetColumnName()));
            $indexString .= trim($dataRow->GetColumnValue(ColumnCompanyAdressZipCode::GetColumnName()));
            
            return $indexString;
        }
}

?>