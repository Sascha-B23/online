<?php
/***************************************************************************
 * EntryIDManager
 * 
 * @access   	public
 * @author   	Johannes Glaser <j.glaser@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class EntryIDManager {
	
	/***************************************************************************
	* Enthält die Singelton-Instanz der Klasse EntryIDManager
	* @var object
	***************************************************************************/
	static private $singeltonInstance = null;

	/***************************************************************************
	* Die gespeicherten Objekte
	* @var array
	***************************************************************************/
	private $entryList = Array();
		
	/***************************************************************************
	* Zeichen, die zur Generierung der IDs verwendet werden (a-z, A-Z, 0-9)
	* @var string
	***************************************************************************/
	private $idCharsetPool = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

	/***************************************************************************
	* Länge der zu generierenden IDs - also die Anzahl der Zeichen (10)
	* Mögliche Kombinationen bei 62 verschiedenen Zeichen und 10 Zeichen Länge: 62^10 = 839.299.365.868.340.224 
	* @var int
	***************************************************************************/
	private $idLength = 10;
	
	/***************************************************************************
	* Konstruktor
	* @access 	private (da Singelton -> Objekt muss über statische Fn. GetInstance() bezogen werden!!)
	***************************************************************************/
	private function EntryIDManager(){
	}
	
	/***************************************************************************
	* Gibt eine Instanz des EntryIDManagers zurück
	* @return	EntryIDManager 	Instanz des EntryIDManagers
	* @access 	public
	***************************************************************************/
	static public function GetInstance(){ 
		// Singelton-Objekt anlegen, falls nicht bereits geschehen...
		if( self::$singeltonInstance==null ){
			self::$singeltonInstance=new EntryIDManager();
		}
		// Singelton-Objekt zurückgeben
		return self::$singeltonInstance; 
	}
	
	/***************************************************************************
	* Generiert eine neue ID
	* @return	String		Die generierte ID
	* @access 	public
	***************************************************************************/
	public function GenerateNewID(){ 
		$charset_length = strlen($this->idCharsetPool);
		// Solange die ID schon existiert wird eine neue ID generiert.
		while(true)
		{
			$returnValue = "";
			for($a=0; $a<$this->idLength; $a++)
			{
				$returnValue .= substr($this->idCharsetPool, mt_rand(0, $charset_length-1), 1);
			}
			//$returnValue = uniqid();
			if( $this->IsIDFree($returnValue) == true ) break;
		}
		return $returnValue;
	}
	
	/***************************************************************************
	* Prüft ob die ID bereits vorhanden ist
	* @param 	String		Die zu prüfende ID
	* @return	bool		true wenn sie nicht existiert, ansonsten false
	* @access 	public
	***************************************************************************/
	public function IsIDFree( $id ){ 
		return (isset($this->entryList[$id]) ? false : true);
	}
	
	/***************************************************************************
	* Registriert die EntryID
	* @param 	EntryID		Die EntryID
	* @return	bool		Bei Erfolg true, sonst false
	* @access 	public
	***************************************************************************/
	public function RegisterEntryID( $entryID ){
		// Objekt vom richtigen Typ?
		if( $entryID==null || !is_a($entryID, "EntryID") )return false;
		// ID bereits registriert?
		if( !$this->IsIDFree( $entryID->GetEntryID() ) )return false;
		// ID jetzt registrieren
		$this->entryList[$entryID->GetEntryID()] = $entryID;
		return true; 
	}
	
	/***************************************************************************
	* Unregistriert die EntryID
	* @param 	EntryID		Die EntryID
	* @return	bool		Bei Erfolg true, sonst false
	* @access 	public
	***************************************************************************/
	public function UnregisterEntryID( $entryID ){ 
		// Objekt vom richtigen Typ?
		if( $entryID==null || !is_a($entryID, "EntryID") )return false;
		unset( $this->entryList[$entryID->GetEntryID()] );
		return true; 
	}
	
	/***************************************************************************
	* Unregistriert die EntryID
	* @param 	EntryID		Die EntryID
	* @return	bool		Bei Erfolg true, sonst false
	* @access 	public
	***************************************************************************/
	public function UnregisterAllEntryIDs(){
		unset($this->entryList);
		$this->entryList=Array();
	}
	
	/***************************************************************************
	* Gibt die EntryID zurück
	* @param 	String 		Die ID
	* @return	EntryID		Die EntryID
	* @access 	public
	***************************************************************************/
	public function GetEntryID( $id ){
		return (isset($this->entryList[$id]) ? $this->entryList[$id] : null );
	}
	
	/***************************************************************************
	* Gibt alle EntryIDs aus
	* @access 	public
	***************************************************************************/
	public function PrintEntryIDs(){
		$keys = array_keys($this->entryList);
		echo count($keys)." EntryID(s) registriert: <br />";
		for($i=0; $i<count($keys); $i++){
			echo "+ ID: ".$this->entryList[$keys[$i]]->GetEntryID()."(".$keys[$i].") / Class: ". get_class($this->entryList[$keys[$i]])."<br />";
		}
	}
	
} // class EntryIDManager



/***************************************************************************
 * EntryID
 * 
 * @access   	public
 * @author   	Johannes Glaser <j.glaser@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
abstract class EntryID {
	
	/***************************************************************************
	* Die ID
	* @var string
	***************************************************************************/
	protected $entryId="";
	
	/***************************************************************************
	* Konstruktor
	* @access 	public
	***************************************************************************/
	public function EntryID(){
		$this->entryId = EntryIDManager::GetInstance()->GenerateNewID();
		EntryIDManager::GetInstance()->RegisterEntryID($this);
	}
	
	/***************************************************************************
	* Magische-Methode "__wakeup" zum erneuten Registrieren der ID beim Manager
	* wenn die Objekte z.B. in der Session gehalten wurden
	* @access 	public
	***************************************************************************/
	public function __wakeup(){
		EntryIDManager::GetInstance()->RegisterEntryID($this);
	}
	
	/***************************************************************************
	* Erzeugt und setzt eine neue EntryID für dieses Objekt
	* @return	String		Die neue EntryID
	* @access 	public
	***************************************************************************/
	public function GetNewEntryID(){
		$this->entryId = EntryIDManager::GetInstance()->GenerateNewID();
		EntryIDManager::GetInstance()->RegisterEntryID($this);
		return $this->entryId;
	}
	
	/***************************************************************************
	* Gibt die ID zurück
	* @return	String		Die ID
	* @access 	public
	***************************************************************************/
	public function GetEntryID(){
		return $this->entryId;
	}
	
	/***************************************************************************
	* Löscht die EntryID
	* @param DBManager  $db			Datenbank Objekt
	* @return bool					Erfolg
	* @access public
	***************************************************************************/
	public function DeleteMe(&$db){
		EntryIDManager::GetInstance()->UnregisterEntryID($this);
		$this->entryId="";
		return true;
	}
	
} // class EntryID

?>