<?php
require_once 'ValidationStates.php5';
require_once 'AbstractColumn.php5';
require_once 'ColumnFMSID.php5';
require_once 'ColumnCompanyAdressLocation.php5';
require_once 'ColumnCompanyAdressStreet.php5';
require_once 'ColumnCompanyAdressZipCode.php5';
require_once 'ColumnCompanyName.php5';
require_once 'ColumnFirstYear.php5';
require_once 'ColumnGroupName.php5';
require_once 'ColumnLocationCity.php5';
require_once 'ColumnLocationCountry.php5';
require_once 'ColumnLocationLabel.php5';
require_once 'ColumnLocationName.php5';
require_once 'ColumnLocationStreet.php5';
require_once 'ColumnLocationType.php5';
require_once 'ColumnLocationZipCode.php5';
require_once 'ColumnPersonClient.php5';
require_once 'ColumnPersonClientAccounting.php5';
require_once 'ColumnPersonClientSupervisor.php5';
require_once 'ColumnPersonFMS.php5';
require_once 'ColumnPersonFMSLeader.php5';
require_once 'ColumnPersonFMSAccountsdepartment.php5';
require_once 'ColumnShopFirstOpening.php5';
require_once 'ColumnShopInternalCode.php5';
require_once 'ColumnShopName.php5';


/**
 * Describes one data row exported and imported in csv format
 * and handles reading and writing from and to the database
 *
 * @author ngerwien
 */
class DataRow {
	/**
	 * An array of AbstractColumn
	 * @var array
	 */
	var $columns = Array();
	
	/**
	 * ErrorMessage generated through validation
	 * @var string
	 */
	var $validationErrorMessage = "";
	
	/**
	 * lowest validation state for all columns
	 * @var int
	 */
	var $maxValidationState = 0;
	
	/**
	 * Constructor of DataRow
	 * defines which columns are included
	 * 
	 * @param string $indexString
	 * @param generic value $value
	 */
	public function __construct() {		
	    $this->SetColumn(ColumnFMSID::GetColumnName(), new ColumnFMSID());
	    $this->SetColumn(ColumnFirstYear::GetColumnName(), new ColumnFirstYear());
	    $this->SetColumn(ColumnGroupName::GetColumnName(), new ColumnGroupName());
	    $this->SetColumn(ColumnCompanyName::GetColumnName(), new ColumnCompanyName());
	    $this->SetColumn(ColumnCompanyAdressZipCode::GetColumnName(), new ColumnCompanyAdressZipCode());
	    $this->SetColumn(ColumnCompanyAdressLocation::GetColumnName(), new ColumnCompanyAdressLocation());
	    $this->SetColumn(ColumnCompanyAdressStreet::GetColumnName(), new ColumnCompanyAdressStreet());
	    $this->SetColumn(ColumnLocationName::GetColumnName(), new ColumnLocationName());
	    $this->SetColumn(ColumnLocationLabel::GetColumnName(), new ColumnLocationLabel());
	    $this->SetColumn(ColumnLocationZipCode::GetColumnName(), new ColumnLocationZipCode());
	    $this->SetColumn(ColumnLocationCity::GetColumnName(), new ColumnLocationCity());
	    $this->SetColumn(ColumnLocationStreet::GetColumnName(), new ColumnLocationStreet());
	    $this->SetColumn(ColumnLocationType::GetColumnName(), new ColumnLocationType());
	    $this->SetColumn(ColumnLocationCountry::GetColumnName(), new ColumnLocationCountry());
	    $this->SetColumn(ColumnShopName::GetColumnName(), new ColumnShopName());
	    $this->SetColumn(ColumnShopFirstOpening::GetColumnName(), new ColumnShopFirstOpening());
	    $this->SetColumn(ColumnShopInternalCode::GetColumnName(), new ColumnShopInternalCode());
	    $this->SetColumn(ColumnPersonFMS::GetColumnName(), new ColumnPersonFMS());
	    $this->SetColumn(ColumnPersonFMSLeader::GetColumnName(), new ColumnPersonFMSLeader());
		$this->SetColumn(ColumnPersonFMSAccountsdepartment::GetColumnName(), new ColumnPersonFMSAccountsdepartment());
	    $this->SetColumn(ColumnPersonClient::GetColumnName(), new ColumnPersonClient());
	    $this->SetColumn(ColumnPersonClientSupervisor::GetColumnName(), new ColumnPersonClientSupervisor());
	    $this->SetColumn(ColumnPersonClientAccounting::GetColumnName(), new ColumnPersonClientAccounting());
	}
	
	/**
	 * initializes the datarow from shop data
	 * 
	 * @param CShop $shop
	 */
	public function InitWithShop($shop) {
		foreach($this->columns as $column) {
		    $column->initWithShop($shop);
		}		
	}
	
	/**
	 * init DataRow with CSV array
	 * 
	 * @param array $datahead
	 * @param array $csvRow
	 */
	public function InitWithCsvRow($datahead, $csvRow) 
	{
	    for ($columnIndex = 0; $columnIndex < count($datahead); $columnIndex++) {
		$column = $this->columns[$datahead[$columnIndex]];
		$column->setColumnValue(trim((string)$csvRow[$columnIndex]));
	    }
	}
	
	/**
	 * set the value of a specified Datarow variable defined in ValueLayoutConfig
	 * 
	 * @param string $indexString
	 * @param string $value
	 */
	private function SetColumn($indexString, $value) {
		$this->columns[$indexString] = $value;
	}
	
	/**
	 * get the name of a specified column in the Datarow
	 * 
	 * @param string $indexString
	 * @return string columnName
	 */
	public function GetColumnName($indexString) {
		return $this->columns[$indexString]->GetColumnName();
	}
        
        /**
	 * get the name of a specified column in the Datarow
	 * 
	 * @param string $indexString
	 * @return string columnName
	 */
	public function GetColumnValue($indexString) {
		return $this->columns[$indexString]->GetColumnValue();
	}
        
        /**
	 * get the name of a specified column in the Datarow
	 * 
	 * @param string $indexString
	 * @return string columnName
	 */
	public function GetColumnNameByIndex($index) {
                if($index < 0 || $index >= $this->GetColumnCount()) {
                    return null;
                }
                $i = 0;
                foreach($this->columns as $column) {
                    if($i == $index) {
                        return $column->GetColumnName();
                    }
                    $i++;
                }
                
		return null;
	}
	
	/**
	 * get the number of columns
	 * 
	 * @return int
	 */
	public function GetColumnCount() {
		return count($this->columns);
	}
	
	/**
	 * Generates header for csv export
	 * @return csv string
	 */
	public function GetExportHeader() {
		$header = "";
		foreach($this->columns as $column) {
		    $header .= $column->getColumnName() . ";";
		}		
		$header .= "\n";
		
		return $header;
	}
	
	/**
	 * convert DataRow to csv string
	 * @return csv string
	 */
	public function ToCsvString() {		
		$csvString = "";
		foreach($this->columns as $column) {
		    $csvString .= str_replace("\n", "", $column->getColumnValue()) . ";";
		}
		$csvString .= "\n";
		
		return $csvString;
	}
	
	/**
	 * validate DataRow
         * 
         * @param ValidationParameters $validationParameters
	 * @return csv string
	 */
	public function Validate($validationParameters) {            		
		foreach($this->columns as $column) {
		    $column->validate($validationParameters);

		    if($this->maxValidationState < $column->getValidationState()) {
				$this->maxValidationState = $column->getValidationState();
		    }
                    
			$errorMessage = ValidationStates::GetMessageForState($column->getValidationState());
			if($errorMessage != "") {
				if($this->validationErrorMessage != "") {
					$this->validationErrorMessage .= "\n";
				}
				$this->validationErrorMessage .= $errorMessage;                        
			} 

			//if(!$this->IsValid($this->maxValidationState)) {
			//    break;
			//}
		}
	}
	
	/**
	 * Returns true if all columns in the dataRow are valid
	 * 
	 * @return bool isValid
	 */
	public function IsValid() {		
	    return ValidationStates::IsValid($this->maxValidationState);
	}
	
	/**
	 * Returns true if all columns in the dataRow are valid
	 * 
	 * @return bool isValid
	 */
	public function HasChanged() {		
	    return ValidationStates::HasChanged($this->maxValidationState);
	}
	
	/**
	 * Get error message from validation
	 * @return string validationErrorMessage
	 */
	public function GetValidationErrorMessage() {		
	    return $this->validationErrorMessage;
	}
	
	/**
	 * Get error message from validation
	 * @return string validationErrorMessage
	 */
	public function GetValidationStateForColumn($datahead, $columnIndex) {	    
	    return $this->columns[$datahead[$columnIndex]]->getValidationState();
	}
	
	/**
	 * Store DataRow in database
	 * 
	 * @return bool/int success(true)
	 *					fail: Errorcode
	 *					(-1) Company does not exist
	 *					(-2) Company does not match the Group		
	 *					(< -1000) Error while storing the location
	 *					(< -2000) Error while storing the shop
	 */
	public function store(&$db, &$shops, &$locations, &$companies) {
        $shop = $shops[$this->GetColumnValue(ColumnFMSID::GetColumnName())];
		
	    if($shop == null) 
	    {
			//$company = $companies[$this->GetColumnValue(ColumnCompanyName::GetColumnName())];
			$company = $companies[ImportManager::GetIndexStringForCompanyByDataRow($this)];
			if(empty($company)) 
			{
				return -1;	    
			}
			if($company->GetGroup()->GetName() != $this->GetColumnValue(ColumnGroupName::GetColumnName()))
			{
				return -2;
			}

			$location = $locations[trim($this->GetColumnValue(ColumnLocationName::GetColumnName()))][trim($this->GetColumnValue(ColumnCompanyName::GetColumnName()))][trim($this->GetColumnValue(ColumnGroupName::GetColumnName()))];
			if($location == null) {
				$location = new CLocation($db);
			}
			
			$location->SetCompany($db, $company);		
			$shop = new CShop($db);

			$this->SetColumnValuesToStore($shop, $location, $db);
			$result = $location->Store($db);	
			if ($result!==true)
			{
				return -1000+$result;
			}
			//add new location to cache or overwrite if already exists
			$locations[trim($this->GetColumnValue(ColumnLocationName::GetColumnName()))][trim($this->GetColumnValue(ColumnCompanyName::GetColumnName()))][trim($this->GetColumnValue(ColumnGroupName::GetColumnName()))] = $location;
			
			$shop->SetLocation($db, $location);
			$result = $shop->Store($db);
			if ($result!==true) 
			{
				return -2000+$result;
			}
			//add new shop to cache
			$shops[$shop->GetRSID()] = $shop;
	    } else {
			$location = $shop->GetLocation();

			$this->SetColumnValuesToStore($shop, $location, $db);

			$result = $location->Store($db);	
			if ($result!==true)
			{
				return -1000+$result;
			}
			//add new location to cache or overwrite if already exists
			$locations[trim($this->GetColumnValue(ColumnLocationName::GetColumnName()))][trim($this->GetColumnValue(ColumnCompanyName::GetColumnName()))][trim($this->GetColumnValue(ColumnGroupName::GetColumnName()))] = $location;

			$result = $shop->Store($db);
			if ($result!==true) 
			{
				return -2000+$result;
			}
	    }
	    
	    return true;
	}
	
	/**
	 * let all columns set their values to be stored
         * 
         * @param CShop $shop
         * @param CLocation $location         * 
	 * @return string validationErrorMessage
	 */
	private function SetColumnValuesToStore($shop, $location, $db) {
		$storeParameters = new StoreParameters();
		$storeParameters->db = $db;
		$storeParameters->shop = $shop;
		$storeParameters->location = $location;
            
	    foreach($this->columns as $column) {
			$column->SetValueToStore($storeParameters);
	    }
	}
        
        /**
	 * Search for Location with name from ColumnLocationName
         * 
         * @param CustomerManager $customerManager         * 
         * @return CLocation
         */
        private function SearchForLocation($customerManager) {
            $locationName = $this->GetColumnValue(ColumnLocationName::GetColumnName());
            if($locationName == "") {
                return null;
            }
            
            $location = null;
            $locations = $customerManager->GetLocations($_SESSION["currentUser"], $this->GetColumnValue(ColumnLocationName::GetColumnName()), "name", 0, 0, 10000);
            if(!empty($locations))
            {
                for($i = 0; $i < count($locations); $i++) 
                {
                    if(str_replace("\n", "", $locations[$i]->GetName()) == $locationName) 
                    {
                        $location = $locations[$i];
                        break;
                    }
                }
            }
            
            return $location;
        }
}

?>
