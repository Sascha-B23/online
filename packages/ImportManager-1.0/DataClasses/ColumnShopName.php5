<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnShopName
 *
 * @author ngerwien
 */
class ColumnShopName extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() {
		return "Name Laden";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) {
		$this->columnValue = $shop->GetName();
    }
    
    /**
    * validates the column
    * 
    * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) {
        $valueToCheck = null;
        if($validationParameters->shop != null) {
            $valueToCheck = $validationParameters->shop->GetName();
        }
        $this->ValidateShopColumn($valueToCheck, $validationParameters);
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) {
		$storeParameters->shop->SetName($this->columnValue);
    }
}

?>
