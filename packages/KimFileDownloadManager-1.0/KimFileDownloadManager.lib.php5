<?
/**
* KimFileDownloadManager
* 
* @access   	public
* @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
*
* @since    	PHP 5.0
* @version		1.0
* @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
*/
class KimFileDownloadManager extends FileDownloadManager 
{
	
	/**
	 * Fügt einen Download hinzu
	 * @param File $file
	 * @param string $fileName
	 * @return string
	 */
	final public function AddDownloadFile(File $file, $fileName = "")
	{
		$filePath = FM_FILE_ROOT.$file->GetDocumentPath();
		if ($fileName=="") $fileName=$file->GetFileName();
		$downloadCode = parent::AddDownload($filePath, $fileName);
		$this->downloadList[$downloadCode]["file"] = $file;
		return $downloadCode;
	}
	
	/**
	* Streamt die Datei mit dem übergebenen DownloadCode
	 * @param DBManager $db
	 * @param string $downloadCode	DownloadCode
	 * @return int					Fehlercode
	 * 								-1: DownloadCode ist ungültig
	 * 								-2: Für den DownloadCode ist keine Datei registriert
	 * 								-3: Datei konnte nicht geöffnet werden
	 */	
	final public function DownloadFile(DBManager $db, $downloadCode, $contentType='octet-stream')
	{
		$downloadCode=trim($downloadCode);
		if( strlen($downloadCode)!=FileDownloadManager::DOWNLOADCODE_LENGTH )return -1;
		if( !isset($this->downloadList[$downloadCode]) )return -2;
		$fileObj = $this->downloadList[$downloadCode]["file"];
		if ($fileObj==null || !is_a($fileObj, "File")) return -4;
		/*@var $fileObj File*/
		// Write action to log
		LoggingManager::GetInstance()->Log(new LoggingFileAccess(LoggingFileAccess::TYPE_DOWNLOAD, $fileObj, $db));
		// additional log if customer download WS files
		if (in_array($fileObj->GetFileSemantic(), Array(FM_FILE_SEMANTIC_WIDERSPRUCH, FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE)) && $_SESSION["currentUser"]->GetGroupBasetype($db)==UM_GROUP_BASETYPE_KUNDE)
		{
			LoggingManager::GetInstance()->Log(new LoggingWsCustomerView($fileObj->GetFileSemanticDescription()["long"], "", serialize($fileObj->GetDependencyStrings($db)), $fileObj->GetFileName()));
		}
		return parent::Download($downloadCode, $contentType);
	}
	
	/**
	 * Fügt einen Download hinzu
	 * @param string $file 			Dateiname (absoluter Pfad)
	 * @param string $fileName		Dateiname unter welchem die Datei gestreamt werden soll
	 * @return string				URL-kompatibler Download-Code für die übergebene Datei
	 */	
	final public function AddDownload($file, $fileName = "")
	{
		// Diese Funktion darf in KIM-Online nicht verwendet werden, da sonst der Zugriff nicht protokolliert werden kann
		die('<strong style="#ff0000">Use function AddDownloadFile</strong>');
	}
	
	/**
	 * Streamt die Datei mit dem übergebenen DownloadCode
	 * @param string $downloadCode	DownloadCode
	 * @return int					Fehlercode
	 * 								-1: DownloadCode ist ungültig
	 * 								-2: Für den DownloadCode ist keine Datei registriert
	 * 								-3: Datei konnte nicht geöffnet werden
	 */	
	final public function Download($downloadCode, $contentType='octet-stream')
	{
		// Diese Funktion darf in KIM-Online nicht verwendet werden, da sonst der Zugriff nicht protokolliert werden kann
		die('<strong style="#ff0000">Use function DownloadFile</strong>');
	}
		
}
?>