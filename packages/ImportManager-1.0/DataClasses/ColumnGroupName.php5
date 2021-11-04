<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnGroupName
 *
 * @author ngerwien
 */
class ColumnGroupName extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() {
	return "Name Gruppe";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) {
	$this->columnValue = $shop->GetLocation()->GetCompany()->GetGroup()->GetName();
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) {  
        $valueToCheck = null;
        if($validationParameters->group != null) {
            $valueToCheck = $validationParameters->group->GetName();
        }
        $this->ValidateGroupColumn($valueToCheck, $validationParameters);
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
