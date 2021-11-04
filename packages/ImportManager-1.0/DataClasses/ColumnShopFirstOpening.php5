<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnShopFirstOpening
 *
 * @author ngerwien
 */
class ColumnShopFirstOpening extends AbstractColumn {
    /**
    * returns the column name    * 
    */
    static public function GetColumnName() 
	{
		return "Eröffnungsdatum";
    }
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    public function InitWithShop($shop) 
	{
        $timeStamp = $shop->GetOpening();                        
		$this->columnValue = $this->GetDateFromTimeStamp($timeStamp);
    }
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    public function Validate($validationParameters) {     
        $valueToCheck = null;
        if($validationParameters->shop != null) 
		{
            $valueToCheck = strval($validationParameters->shop->GetOpening());
        }
        $tmpColumnValue = $this->columnValue;
        $this->columnValue = $this->GetTimeStampFromDate($this->columnValue);
		if ($this->columnValue===false)
		{
			$this->validationState = ValidationStates::$STATE_INVALID_VALUE;
		}
		else
		{
			$this->ValidateShopColumn($valueToCheck, $validationParameters, false);
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
		$storeParameters->shop->SetOpening($this->GetTimeStampFromDate($this->columnValue));
    }
    
    /**
    * generates a timestamp from given date
    * @param string $date
    */
    protected function GetTimeStampFromDate($date) 
	{
        if($date == "") 
		{
            return "0";
        }
        $timeArray = explode(".", $date);
		if (count($timeArray)!=3) return false;
		return strval(mktime(0, 0, 0, intval($timeArray[1]), intval($timeArray[0]), intval($timeArray[2])));
    }
    
    /**
    * generates a timestamp from given date
    * @param string $date
    */
    protected function GetDateFromTimeStamp($timeStamp) 
	{
        if($timeStamp == 0) 
		{
            return "";
        }
		
		return date('d.m.y', $timeStamp);
    }
}

?>
