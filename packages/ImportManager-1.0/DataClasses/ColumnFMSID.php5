<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnFMSID
 *
 * @author ngerwien
 */
class ColumnFMSID extends AbstractColumn {    
    /**
    * returns the column name    * 
    */
    static public function GetColumnName()
    {
	    return "SFM ID";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop)
    {
	    $this->columnValue = $shop->GetRSID();
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) {  
        if(empty($this->columnValue)) {
            $this->validationState = ValidationStates::$STATE_ISREQUIRED;
            return;
        }
        
        if($validationParameters->shop == null) {
            $this->validationState = ValidationStates::$STATE_NEW;
        }
        else {
            $this->validationState = ValidationStates::$STATE_NOCHANGE;
        }
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) {
	$storeParameters->shop->SetRSID($this->columnValue);
    }
}

?>
