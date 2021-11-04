<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ColumnPersonFMSAccountsdepartment
 *
 * @author stephan
 */
class ColumnPersonFMSAccountsdepartment extends AbstractColumn 
{
	/**
	 * returns the column name    * 
	 */
	static public function GetColumnName()
	{
		return "SFM Buchhaltung";
	}
    
	/**
	 * initialize column with data from shop
	 * 
	 * @param CShop $shop
	 */
	public function InitWithShop($shop)
	{
		$this->columnValue = $this->GetStringFromUser($shop->GetCPersonFmsAccountsdepartment());
	}
    
	/**
	 * validates the column
	 * 
	 * @param ValidationParameters $validationParameters
	 */
	public function Validate($validationParameters)
	{
		$newUser = $this->CheckUser($this->columnValue, UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, $validationParameters);
		$oldUser = null;
		if ($validationParameters->shop != null) 
		{
			$oldUser = $validationParameters->shop->GetCPersonFmsAccountsdepartment();
		}

		$this->ValidateShopPersonColumn($validationParameters, $newUser, $oldUser);
	}
    
	/**
	 * sets the column value in the given class to be stored    * 
	 * 
	 * @param StoreParameters $storeParameters
	 */
	public function SetValueToStore($storeParameters) 
	{
		$user = $this->CheckUserFromDB($this->columnValue, UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT, $storeParameters->db);
		if($user == null)
		{
			return;
		}
		$storeParameters->shop->SetCPersonFmsAccountsdepartment($storeParameters->db, $user);
	}
	
}
?>