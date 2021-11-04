<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbstractColumn
 *
 * @author ngerwien
 */
abstract class AbstractColumn {    
    protected $columnValue;
    protected $validationState;
    
    public function __construct() {
	$this->columnValue = "";
	$this->validationState = 0;
    }
    
    /**
    * get the column value
    */
    public function GetColumnValue() {
	return $this->columnValue;
    }
    
    /**
    * set the column value
    */
    public function SetColumnValue($value) {
	$this->columnValue = $value;
    }
    
    /**
    * get the validation state of the column
    */
    public function GetValidationState() {
	return $this->validationState;
    }
    
    /**
    * returns the column name
    * 
    * @return string ColumnName
    */
    static public function GetColumnName() {
		return "";
	}
    
    /**
    * initialize column with data from shop
    * 
    * @param CShop $shop
    */
    abstract public function InitWithShop($shop);
    
    /**
    * validates the column
    * 
     * @param ValidationParameters $validationParameters
    */
    abstract public function Validate($validationParameters);
    
    /**
    * sets the column value in the given class to be stored    * 
    * 
    * @param StoreParameters $storeParameters
    */
    abstract public function SetValueToStore($storeParameters);
    
    /**
    * Prüft, ob der angegebene Name im System vorhanden ist
    * @param string $username Nachname, Vorname in einem String
    * @param int $basetype
    * @param ValidationParameters $validationParameters
    * @return User
    */
    protected function CheckUser($username, $basetype, $validationParameters)
    {
	//global $userManager;
	// Auf Fehler checken
	if (!is_string($username)) return null;
	if (strstr($username, ", ")===false) return null;

	// Aufteilen
	$name = explode(", ", $username);
	$myName["nachname"] = utf8_decode($name[0]);
	$myName["vorname"] = utf8_decode($name[1]);

	// user suchen
	//$user = $userManager->GetUserByName($myName["vorname"], $myName["nachname"]);
        $user = $validationParameters->users[$myName["nachname"]][$myName["vorname"]];
	if ($user!=null && $user->GetGroupBasetype($validationParameters->db)>=$basetype)
	{
		return $user;
	}
	return null;
    }
    
    /**
    * Prüft, ob der angegebene Name im System vorhanden ist
    * @param string $username Nachname, Vorname in einem String
    * @param int $basetype
    * @param ValidationParameters $validationParameters
    * @return User
    */
    protected function CheckUserFromDB($username, $basetype, $db)
    {
	global $userManager;
	// Auf Fehler checken
	if (!is_string($username)) return null;
	if (strstr($username, ", ")===false) return null;

	// Aufteilen
	$name = explode(", ", $username);
	$myName["nachname"] = $name[0];
	$myName["vorname"] = $name[1];

	// user suchen
	$user = $userManager->GetUserByName($myName["vorname"], $myName["nachname"]);
	if ($user!=null && $user->GetGroupBasetype($db)>=$basetype)
	{
		return $user;
	}
	return null;
    }
    
    /**
    * Generates a string in form of "Name, Firstname" from User
    * @param User $user
    * @return string
    */
    protected function GetStringFromUser($user) {
	if(empty($user)) {
	    return "";
	}
	
	$nameString = $user->GetName();
	$nameString .= ", ";
	$nameString .= $user->GetFirstName();
	return $nameString;
    }
    
    /**
    * validates a column from group
    * 
     * @param string $valueToValidate
     * @param ValidationParameters $validationParameters
    */
    protected function ValidateGroupColumn($valueToValidate, $validationParameters) {
        $this->PrepareValueToValidate($valueToValidate);
        if(empty($this->columnValue)) {
            $this->validationState = ValidationStates::$STATE_ISREQUIRED;
            return;
        }
        
        if($validationParameters->shop == null) {
            if($validationParameters->group == null) {
                $this->validationState = ValidationStates::$STATE_HAS_TO_EXIST_IN_DB;
            }
            else {
                if($valueToValidate == $this->GetColumnValue()) {
                    $this->validationState = ValidationStates::$STATE_NEW;
                }
                else {
                    $this->validationState = ValidationStates::$STATE_HAS_TO_EXIST_IN_DB;
                }
            }
        }
        else {
            if($this->columnValue == $valueToValidate) {
                $this->validationState = ValidationStates::$STATE_NOCHANGE;
            }
            else {
                $this->validationState = ValidationStates::$STATE_CANNOT_BE_CHANGED;
            }
        }
    }
    
    /**
    * validates a column from company
    * 
    * @param string $valueToValidate
    * @param ValidationParameters $validationParameters
    */
    protected function ValidateCompanyColumn($valueToValidate, $validationParameters) {
        $this->PrepareValueToValidate($valueToValidate);
        if(empty($this->columnValue)) {
            $this->validationState = ValidationStates::$STATE_ISREQUIRED;
            return;
        }
        
        if($validationParameters->shop == null) {
            if($validationParameters->company == null) {
                $this->validationState = ValidationStates::$STATE_HAS_TO_EXIST_IN_DB;
            }
            else {
                if($valueToValidate == $this->GetColumnValue()) {
                    $this->validationState = ValidationStates::$STATE_NEW;
                }
                else {
                    $this->validationState = ValidationStates::$STATE_HAS_TO_EXIST_IN_DB;
                }
            }
        }
        else {
            if($this->columnValue == $valueToValidate) {
                $this->validationState = ValidationStates::$STATE_NOCHANGE;
            }
            else {
                $this->validationState = ValidationStates::$STATE_CANNOT_BE_CHANGED;
            }
        }
    }
    
    /**
    * validates a column from shop
    * 
     * @param string $valueToValidate
     * @param ValidationParameters $validationParameters
     */
    protected function ValidateShopColumn($valueToValidate, $validationParameters, $isRequired = true) {
        $this->PrepareValueToValidate($valueToValidate);
        if($isRequired && empty($this->columnValue)) {
            $this->validationState = ValidationStates::$STATE_ISREQUIRED;
            return;
        }
        
        if($validationParameters->shop == null) {
            $this->validationState = ValidationStates::$STATE_NEW;
        }
        else {
            if($valueToValidate == $this->GetColumnValue()) {
            $this->validationState = ValidationStates::$STATE_NOCHANGE;
            }
            else {
                $this->validationState = ValidationStates::$STATE_CHANGED;
            }
        }        
    }
    
    /**
    * validates a column from shop of type User
    * 
     * @param ValidationParameters $validationParameters
     * @param User $newUser
     * @param User $oldUser
     * @param bool $isRequired
     */
    protected function ValidateShopPersonColumn($validationParameters, $newUser, $oldUser, $isRequired = true) {
        if($isRequired && empty($this->columnValue)) {
            $this->validationState = ValidationStates::$STATE_ISREQUIRED;
            return;
        }
        
        if(($newUser == null && $oldUser != null) || ($newUser == null && $this->columnValue != null)) {
            $this->validationState = ValidationStates::$STATE_HAS_TO_EXIST_IN_DB;
        }
        else {        
            if($validationParameters->shop == null) {
                $this->validationState = ValidationStates::$STATE_NEW;
            }
            else {
                if($this->GetStringFromUser($oldUser) == $this->GetStringFromUser($newUser)) {
                    $this->validationState = ValidationStates::$STATE_NOCHANGE;
                }
                else {
                    $this->validationState = ValidationStates::$STATE_CHANGED;
                }                
            }            
        }
    }
    
    /**
    * validates a column from location
    * 
     * @param string $valueToValidate
     * @param ValidationParameters $validationParameters
     * @param bool $isRequired
     */
    protected function ValidateLocationColumn($valueToValidate, $validationParameters, $isRequired = true) {
        $this->PrepareValueToValidate($valueToValidate);
        if($isRequired && empty($this->columnValue)) {
            $this->validationState = ValidationStates::$STATE_ISREQUIRED;
            return;
        }
        
        if($validationParameters->shop == null) {
            $this->validationState = ValidationStates::$STATE_NEW;
        }
        else {
            if($valueToValidate == $this->GetColumnValue()) {
            $this->validationState = ValidationStates::$STATE_NOCHANGE;
            }
            else {
                $this->validationState = ValidationStates::$STATE_CHANGED;
            }
        }          
    }
    
    private function PrepareValueToValidate(&$valueToValidate) {
        $valueToValidate = str_replace("\n", "", $valueToValidate);
        $valueToValidate = trim($valueToValidate);
        
        return $valueToValidate;
    }
}

?>
