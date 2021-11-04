<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnLocationName
 *
 * @author ngerwien
 */
class ColumnLocationName extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() {
	return "Name Standort";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) {
	$this->columnValue = $shop->GetLocation()->GetName();
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) {        
        $valueToCheck = null;
        if($validationParameters->location != null) {
            $valueToCheck = $validationParameters->location->GetName();
        }
        $this->ValidateLocationColumn($valueToCheck, $validationParameters);
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) {
	$storeParameters->location->SetName($this->columnValue);
    }
}

?>
