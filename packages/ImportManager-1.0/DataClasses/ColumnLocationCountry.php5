<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnLocationCountry
 *
 * @author ngerwien
 */
class ColumnLocationCountry extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() {
	return "Land Standort";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) {
		$this->columnValue = $shop->GetLocation()->GetCountry();
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) { 
        $valueToCheck = null;
		
		$valid = false;
		foreach ($validationParameters->countries as $country)
		{
			if ($country->GetIso3166()==$this->columnValue)
			{
				$valid = true;
			}
		}
		if (!$valid)
		{
			$this->validationState = ValidationStates::$STATE_INVALID_VALUE;
			return;
		}
		
        if($validationParameters->location != null) 
		{
            $valueToCheck = $validationParameters->location->GetCountry();
        }
        $this->ValidateLocationColumn($valueToCheck, $validationParameters);
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) {
        $storeParameters->location->SetCountry($this->columnValue);
    }
}

?>
