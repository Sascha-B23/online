<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnShopInternalCode
 *
 * @author ngerwien
 */
class ColumnShopInternalCode extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() {
	return "Interne Ladennummer";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) {
	$this->columnValue = $shop->GetInternalShopNo();
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) {
        $valueToCheck = null;
        if($validationParameters->shop != null) {
            $valueToCheck = $validationParameters->shop->GetInternalShopNo();
        }
        $this->ValidateShopColumn($valueToCheck, $validationParameters, false);
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) {
	$storeParameters->shop->SetInternalShopNo($this->columnValue);
    }
}

?>
