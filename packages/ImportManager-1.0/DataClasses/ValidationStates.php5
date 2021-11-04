<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ValidationStates
 *
 * @author ngerwien
 */
class ValidationStates {
    public static $STATE_NOCHANGE = 0;
    public static $STATE_CHANGED = 1;
    public static $STATE_NEW = 2;
    public static $STATE_ISREQUIRED = 3;			//all states below describe an error
    public static $STATE_HAS_TO_EXIST_IN_DB = 4;
    public static $STATE_CANNOT_BE_CHANGED = 5;
	public static $STATE_INVALID_VALUE = 6;
    
    /**
    * Returns true if the validationstate is STATE_NOCHANGE
    * 
    * @param int $validationState
     * @return bool
    */
    static public function HasChanged($validationState) {
	return $validationState > ValidationStates::$STATE_NOCHANGE;
    }
    
    /**
    * Returns true if the validationstate holds no error state
    * 
    * @param int $validationState
     * @return bool
    */
    static public function IsValid($validationState) {
	return $validationState <= ValidationStates::$STATE_NEW;
    }
    
    /**
    * Returns an error message for each error state
    * 
    * @param int $validationState
     * @return string
    */
    static public function GetMessageForState($validatioState) {
        $errorMessage = "";
        switch ($validatioState) {
            case ValidationStates::$STATE_ISREQUIRED:
                $errorMessage = "Feld darf nicht leer sein";
                break;
            
            case ValidationStates::$STATE_HAS_TO_EXIST_IN_DB:
                $errorMessage = "Eintrag ist nicht in der Datenbank vorhanden";
                break;
            
            case ValidationStates::$STATE_CANNOT_BE_CHANGED:
                $errorMessage = "Feld darf nicht geändert werden";
                break;
			
			case ValidationStates::$STATE_INVALID_VALUE:
				$errorMessage = "Ungültiger Wert";
                break;
			
            default:
                break;
        }
        
        return $errorMessage;
    }
}

?>
