<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ValidationParameters
 *
 * @author ngerwien
 */
class ValidationParameters {
    /** 
    * @var DBManager
    */
    public $db = null;
    
    /** 
    * @var CShop
    */
    public $shop = null;
    
    /** 
    * @var CLocation
    */
    public $location = null;
    
    /** 
    * @var CCompany
    */
    public $company = null;
    
    /** 
    * @var CGroup
    */
    public $group = null;
    
    /** 
    * @var User[]
    */
    public $users = null;
	
	/**
	 * @var CCountry[] 
	 */
	public $countries = null;
	
}

?>
