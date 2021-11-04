<?
/**
* FileDownloadManager
* 
* @access   	public
* @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
*
* @since    	PHP 5.0
* @version		1.0
* @copyright 	Copyright (c) 2011 Stoll von Gáti GmbH www.stollvongati.com
*/
class FileDownloadManager {
	
	/**
	* Zeichen, die zur Generierung der Download-Codes verwendet werden (a-z, A-Z, 0-9)
	* @var string
	*/
	const DOWNLOADCODE_CHARSET_POOL = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	
	/**
	* Länge der zu generierenden Download-Codes - also die Anzahl der Zeichen (10)
	* Mögliche Kombinationen bei 62 verschiedenen Zeichen und 10 Zeichen Länge: 62^10 = 839.299.365.868.340.224 
	* @var int
	*/
	const DOWNLOADCODE_LENGTH = 10;
	
	/**
	* Liste mit den registrierten Downloads
	* @var array
	* @access protected
	*/
	protected $downloadList = Array();
	
	/**
	* Konstruktor
	* @access public
	*/
	public function FileDownloadManager(){		
		
	}
	
	/**
	* Löscht die List mit den registrierten Downloads
	* @access public
	*/
	public function EmptyDownloadList(){
		$this->downloadList = Array();		
	}
	
	/**
	* Fügt einen Download hinzu
	* @param string $file 			Dateiname (absoluter Pfad)
	* @param string $fileName		Dateiname unter welchem die Datei gestreamt werden soll
	* @return string				URL-kompatibler Download-Code für die übergebene Datei
	* @access public
	*/	
	public function AddDownload($file, $fileName = ""){
		if( trim($fileName)=="" ){
			$fileparts=pathinfo($file);
			$fileName = $fileparts['basename'];
		}
		$fileInfo = Array("sourceFile" => $file, "fileName" => $fileName);
		// Prüfen, ob die Datei bereits registriert ist
		$downloadCode = $this->GetDownloadCodeByFile($fileInfo);
		if( $downloadCode!==false )return $downloadCode;
		// Wenn Datei noch nicht registriert dies jetzt nachholen...
		$downloadCode = $this->GenerateDownloadCode();
		$this->downloadList[$downloadCode] = $fileInfo;
		return $downloadCode;
	}
	
	/**
	* Gibt einen Download anhand des DownloadCodes zurück
	* @param string $downloadCode	DownloadCode
	* @return string				Dateiname (absoluter Pfad)
	* @access public
	*/	
	public function GetDownload($downloadCode){
		return $this->downloadList[$downloadCode];
	}
	
	/**
	* Streamt die Datei mit dem übergebenen DownloadCode
	* @param string $downloadCode	DownloadCode
	* @return int					Fehlercode
	* 								-1: DownloadCode ist ungültig
	* 								-2: Für den DownloadCode ist keine Datei registriert
	* 								-3: Datei konnte nicht geöffnet werden
	* @access public
	*/	
	public function Download($downloadCode, $contentType='octet-stream'){
		$downloadCode=trim($downloadCode);
		if( strlen($downloadCode)!=FileDownloadManager::DOWNLOADCODE_LENGTH )return -1;
		if( !isset($this->downloadList[$downloadCode]) )return -2;
		$downloadFile = $this->downloadList[$downloadCode]["sourceFile"];	
		if( $downloadFile!="" && file_exists($downloadFile) ){
			$fileData=file_get_contents($downloadFile);
			if( $fileData===false )$fileData="";
		}
		if( $fileData!="" ){
			// ... und streamen
			header('HTTP/1.1 200 OK');
			header('Status: 200 OK');
			header('Accept-Ranges: bytes');
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header('Content-Transfer-Encoding: Binary');
			header("Content-type: application/".$contentType);
			header("Content-Disposition: attachment; filename=\"".rawurlencode($this->downloadList[$downloadCode]["fileName"])."\"");
			header("Content-Length: ".strlen($fileData));
			echo $fileData;
			exit;			
		}else{
			// Fehler: Datei nicht gefunden
			return -3;
		}
	}
	
	/**
	 * Stream the data as file to the browser
	 * @param string $fileData
	 * @param string $fileName
	 * @param string $contentType 
	 */
	static public function StreamFile($fileData, $fileName, $contentType='octet-stream')
	{
		header('HTTP/1.1 200 OK');
		header('Status: 200 OK');
		header('Accept-Ranges: bytes');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Transfer-Encoding: Binary');
		header("Content-type: application/".$contentType);
		header("Content-Disposition: attachment; filename=\"".rawurlencode($fileName)."\"");
		header("Content-Length: ".strlen($fileData));
		echo $fileData;
		exit;			
	}
	
	/**
	* Gibt den DownloadCodes anhand der Datei zurück
	* @param array $fileInfo		Dateiinfos
	* @return string | bool			DownloadCode oder bool false wenn Datei nicht gefunden
	* @access protected
	*/	
	protected function GetDownloadCodeByFile($fileInfo){
		$keys = array_keys( $this->downloadList );
		for( $a=0; $a<count($keys); $a++ ){
			if( $this->downloadList[$keys[$a]]["sourceFile"]==$fileInfo["sourceFile"] && $this->downloadList[$keys[$a]]["fileName"]==$fileInfo["fileName"] ){
				return $keys[$a];
			}
		}
		return false;
	}
	
	/**
	* Generiert einen DownloadCode
	* @return string				DownloadCode
	* @access protected
	*/
	protected function GenerateDownloadCode(){ 
		$charset_length = strlen(FileDownloadManager::DOWNLOADCODE_CHARSET_POOL)-1;
		// Solange die ID schon existiert wird eine neue ID generiert.
		while(true){
			$returnValue = "";
			for($a=0; $a<FileDownloadManager::DOWNLOADCODE_LENGTH; $a++){
				$returnValue .= substr(FileDownloadManager::DOWNLOADCODE_CHARSET_POOL, rand(0, $charset_length), 1);
			}
			if( !isset($this->entryList[$returnValue]) )break;
		}
		return $returnValue;
	}
		
} // class FileDownloadManager
?>