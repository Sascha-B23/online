<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnPersonClient
 *
 * @author ngerwien
 */
class ColumnPersonClient extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() {
	return "Name Ansprechpartner Kunde";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) {
	$this->columnValue = $this->GetStringFromUser($shop->GetCPersonCustomer());
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) { 
        $newUser = $this->CheckUser($this->columnValue, UM_GROUP_BASETYPE_KUNDE, $validationParameters);
        $oldUser = null;
        if($validationParameters->shop != null) {
            $oldUser = $validationParameters->shop->GetCPersonCustomer();
        }
        
        $this->ValidateShopPersonColumn($validationParameters, $newUser, $oldUser);
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) {
	$user = $this->CheckUserFromDB($this->columnValue, UM_GROUP_BASETYPE_KUNDE, $storeParameters->db);
        if($user == null) {
            return;
        }
        
	$storeParameters->shop->SetCPersonCustomer($storeParameters->db, $user);
    }
}

?>
