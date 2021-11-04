<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnLocationCity
 *
 * @author ngerwien
 */
class ColumnLocationCity extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() {
	return "Ort Standort";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) {
	$this->columnValue = $shop->GetLocation()->GetCity();
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) { 
        $valueToCheck = null;
        if($validationParameters->location != null) {
            $valueToCheck = $validationParameters->location->GetCity();
        }
        $this->ValidateLocationColumn($valueToCheck, $validationParameters);
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) {
	$storeParameters->location->SetCity($this->columnValue);
    }
}

?>
