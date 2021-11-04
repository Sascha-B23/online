<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnCompanyAdressZipCode
 *
 * @author ngerwien
 */
class ColumnCompanyAdressZipCode extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() {
	return "Anschrift Firma PLZ";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) {
	$this->columnValue = $shop->GetLocation()->GetCompany()->GetZIP();
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) { 
        $valueToCheck = null;
        if($validationParameters->company != null) {
            $valueToCheck = $validationParameters->company->GetZIP();
        }
  
        $this->ValidateCompanyColumn($valueToCheck, $validationParameters);
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) {
	return;
    }
}

?>
