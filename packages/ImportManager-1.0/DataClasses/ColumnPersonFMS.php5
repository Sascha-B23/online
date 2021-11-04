<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnPersonFMS
 *
 * @author ngerwien
 */
class ColumnPersonFMS extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName()
    {
	    return "SFM Forderungsmanager";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop)
    {
	    $this->columnValue = $this->GetStringFromUser($shop->GetCPersonRS());
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) {      
        $newUser = $this->CheckUser($this->columnValue, UM_GROUP_BASETYPE_RSMITARBEITER, $validationParameters);
        $oldUser = null;
        if($validationParameters->shop != null) {
            $oldUser = $validationParameters->shop->GetCPersonRS();
        }
        
        $this->ValidateShopPersonColumn($validationParameters, $newUser, $oldUser);
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) {
	$user = $this->CheckUserFromDB($this->columnValue, UM_GROUP_BASETYPE_RSMITARBEITER, $storeParameters->db);
        if($user == null) {
            return;
        }
        
	$storeParameters->shop->SetCPersonRS($storeParameters->db, $user);
    }
}

?>
