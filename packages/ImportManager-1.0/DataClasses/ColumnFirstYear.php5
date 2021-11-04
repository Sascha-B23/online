<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnFirstYear
 *
 * @author ngerwien
 */
class ColumnFirstYear extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() {
	return "Erstes Jahr";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) {
	$this->columnValue = $shop->GetFirstYear();
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) {  
        $valueToCheck = null;
        if($validationParameters->shop != null) {
            $valueToCheck = $validationParameters->shop->GetFirstYear();
        }
        $this->ValidateShopColumn($valueToCheck, $validationParameters);
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) {
	$storeParameters->shop->SetFirstYear($this->columnValue);
    }
}

?>
