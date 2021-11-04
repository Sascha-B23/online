<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnLocationLabel
 *
 * @author ngerwien
 */
class ColumnLocationLabel extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() {
	return "Bezeichnung Standort";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) {
	$this->columnValue = $shop->GetLocation()->GetObjectDescription();
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) { 
        $valueToCheck = null;
        if($validationParameters->location != null) {
            $valueToCheck = $validationParameters->location->GetObjectDescription();
        }
        $this->ValidateLocationColumn($valueToCheck, $validationParameters, false);
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) {
	$storeParameters->location->SetObjectDescription($this->columnValue);
    }
}

?>
