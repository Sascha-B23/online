<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnLocationType
 *
 * @author ngerwien
 */
class ColumnLocationType extends AbstractColumn 
{
	
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() 
	{
		return "Standort Typ";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) 
	{
		$this->columnValue = GetLocationName($shop->GetLocation()->GetLocationType());
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) 
	{
		
        $valueToCheck = null;
        if($validationParameters->location != null) 
		{
            $valueToCheck = $validationParameters->location->GetLocationType();
        }
		
		$tmpColumnValue = $this->columnValue;
		$this->columnValue = GetLocationId($this->columnValue);
		if ($this->columnValue==-1)
		{
			$this->validationState = ValidationStates::$STATE_INVALID_VALUE;
		}
		else
		{
			$this->ValidateLocationColumn($valueToCheck, $validationParameters);
		}
		$this->columnValue = $tmpColumnValue;     
    }
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    public function SetValueToStore($storeParameters) 
	{
		$storeParameters->location->SetLocationType(GetLocationId($this->columnValue));
    }
}

?>
