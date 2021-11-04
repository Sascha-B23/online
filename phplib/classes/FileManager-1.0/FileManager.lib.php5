<?php
include("DependencyFileDescription.lib.php5");
include("File.lib.php5");
include("FileManagerConfig.inc.php5");
include("FileRelation.lib.php5");

/**
 * Verwaltungsklasse für Dateien
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2009 Stoll von Gáti GmbH www.stollvongati.com
 */
class FileManager 
{

	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
								  
	/**
	 * Constructor
	 * @param DBManager $db
	 */
	public function FileManager($db)
	{
		$this->db = $db;
	}

	/**
	 * Gibt den Filetype für die übergebene Dateiendung zurück
	 * @param string $fileExtension
	 * @return int
	 */
	static public function GetFileTypeFromFileExtension($fileExtension)
	{
		$fileExtension = strtoupper(trim($fileExtension));
		if ($fileExtension=="") return File::FM_FILE_TYPE_UNKNOWN;
		global $FM_FILEEXTENSION_TO_FILETYPE;
		$aKeys = array_keys($FM_FILEEXTENSION_TO_FILETYPE);
		$aValues = array_values($FM_FILEEXTENSION_TO_FILETYPE);
		for ($a=0; $a<count($aKeys); $a++)
		{
			if (in_array($fileExtension, $aValues[$a])) return $aKeys[$a];
		}
		return File::FM_FILE_TYPE_UNKNOWN;
	}

	/**
	 * Prüft, ob der übergebene Filetype für die übergebene Filesemantic erlaubt ist
	 * @param int $fileType
	 * @param int $fileSemantic
	 * @return bool
 	 */
	static public function IsFileTypeAllowedForFileSemantic($fileType, $fileSemantic)
	{
		if( !is_int($fileType) || !is_int($fileSemantic) )return false;
		global $FM_ALLOWED_FILETYPE_FOR_SEMANTIC;
		if( ($FM_ALLOWED_FILETYPE_FOR_SEMANTIC[$fileSemantic] & $fileType )==$fileType )return true;	
		return false;
	}

	/**
	 * Gibt alle erlaubten Dateiendungen der übergeben Semantik zurück
	 * @param int $fileSemantic
	 * @return array
  	 */
	static public function GetFileTypesForFileSemantic($fileSemantic)
	{
		$supportetFileTypes=Array();
		global $FM_ALLOWED_FILETYPE_FOR_SEMANTIC;
		global $FM_FILEEXTENSION_TO_FILETYPE;
		if( !is_int($fileSemantic) )return $supportetFileTypes;
		foreach ($FM_FILEEXTENSION_TO_FILETYPE as $key => $value) {
			if( $key==File::FM_FILE_TYPE_UNKNOWN )continue;
			if( ($FM_ALLOWED_FILETYPE_FOR_SEMANTIC[$fileSemantic] & $key)==$key ){
				$supportetFileTypes=array_merge($supportetFileTypes, $value);
			}
		}
		return $supportetFileTypes;
	}
	
	/**
	 * Gibt die maximal erlaubte Dateigröße der übergeben Semantik zurück
	 * @param int	$fileSemantic
	 * @return int
	 */
	static public function GetMaxFilesizeForFileSemantic($fileSemantic)
	{
		global $FM_MAX_FILESIZE_FOR_SEMANTIC;
		if( !is_int($fileSemantic) )return 0;
		if( !isset($FM_MAX_FILESIZE_FOR_SEMANTIC[$fileSemantic]) )return 0;
		return $FM_MAX_FILESIZE_FOR_SEMANTIC[$fileSemantic];
	}
	
	/**
	 * Erzeugt ein File-Objekt von der übergebenen Datei. Der angegebene Dabeipfad 
	 * muss absolut sein. Die Datei wird dabei kopiert (nicht verschoben/gelöscht) 
	 * und das File-Objekt in der Datenbank angelegt. 
	 * @param string $documentPath
	 * @return File|int Bei Erfolg File-Object andernfalls
	 *								-1	Quelldatei nicht gefunden
	 *								-2	Übergebene FileSemantic ist ungültig
	 *								-3	Unbekannter Filetype / Dateiendung
	 *								-4	Filetype ist für die übergebene FileSemantic nicht zugelassen
	 *								-5	Quelldatei konnte nicht kopiert werden
	 *								-6	Eintrag konnte nicht in Datenbank gespeichert werden
	 *								-7	Dateigröße überschreitet den maximal erlaubten Wert
	 *								-8  Datei ist leer (0 Byte)
	 *								-9  Datei ist nach dem kopieren leer (0 Byte)
	 */
	public function CreateFromFile($db, $documentPath, $fileSemantic, $fileName="", $fileExtension="")
	{
		if( !file_exists($documentPath) || !is_file($documentPath) )return -1;
		if( $fileSemantic==FM_FILE_SEMANTIC_UNKNOWN )return -2;
		if( $this->GetMaxFilesizeForFileSemantic($fileSemantic)>0 && filesize($documentPath)>$this->GetMaxFilesizeForFileSemantic($fileSemantic) )return -7;
		if( filesize($documentPath)==0 )return -8; // Datei leer
		$pathInfo = pathinfo($documentPath);
		if(trim($fileExtension)!="")$pathInfo["extension"]=trim($fileExtension);
		if(trim($fileName)!="")$pathInfo["basename"]=trim($fileName);
		$fileType = FileManager::GetFileTypeFromFileExtension($pathInfo["extension"]);
		if( $fileType==File::FM_FILE_TYPE_UNKNOWN )return -3;
		// Prüfen, ob Dateityp für Semantik erlaubt ist
		if( !FileManager::IsFileTypeAllowedForFileSemantic( $fileType, $fileSemantic) )return -4;
		// Datei kopieren
		$destinationFileName="";
		do{
			$destinationFileName="file_".$fileSemantic."_".time()."_".rand(100000, 999999).".".$pathInfo["extension"];
		}while( file_exists(FM_FILE_ROOT.$destinationFileName) );
		if( !copy($documentPath, FM_FILE_ROOT.$destinationFileName) )return -5;
		if( filesize(FM_FILE_ROOT.$destinationFileName)<=0 )return -9; // Datei nach kopieren leer
		$file = new File($this->db);
		$fileNameTemp = urldecode($pathInfo["basename"]);
		$fileNameTemp = preg_replace("/\[[0-9]*\]/i", "", $fileNameTemp);
		$file->SetFileName($fileNameTemp);
		$file->SetFileType($fileType);
		$file->SetOriginalFileName($fileNameTemp);
		$file->SetDescription("");
		$file->SetDocumentPath($destinationFileName);
		$file->SetFileSemantic($fileSemantic);		
		// Eintrag speichern
		if( $file->Store($this->db)!==true ){
			unlink(FM_FILE_ROOT.$destinationFileName);
			return -6;
		}
		return $file;
	}
	
	/**
	 * Create a new File-Object from a stream
	 * @param DBManager $db
	 * @param string $fileStream
	 * @param int $fileSemantic
	 * @param string $fileName
	 * @param string $fileExtension
	 * @return File 
	 */
	static public function CreateFromStream($db, $fileStream, $fileSemantic, $fileName, $fileExtension)
	{
		if( $fileSemantic==FM_FILE_SEMANTIC_UNKNOWN )return -2;
		if( self::GetMaxFilesizeForFileSemantic($fileSemantic)>0 && sizeof($fileStream)>self::GetMaxFilesizeForFileSemantic($fileSemantic) )return -7;
		if( sizeof($fileStream)==0 )return -8; // leer
		$fileType = FileManager::GetFileTypeFromFileExtension($fileExtension);
		if( $fileType==File::FM_FILE_TYPE_UNKNOWN )return -3;
		// Prüfen, ob Dateityp für Semantik erlaubt ist
		if( !FileManager::IsFileTypeAllowedForFileSemantic( $fileType, $fileSemantic) )return -4;
		// Datei kopieren
		$destinationFileName="";
		do
		{
			$destinationFileName="file_".$fileSemantic."_".time()."_".rand(100000, 999999).".".$fileExtension;
		}
		while (file_exists(FM_FILE_ROOT.$destinationFileName));
		if (!file_put_contents(FM_FILE_ROOT.$destinationFileName, $fileStream)) return -5;
		if (filesize(FM_FILE_ROOT.$destinationFileName)<=0) return -9; // Datei nach kopieren leer
		$file = new File($db);
		$fileNameTemp = urldecode(trim($fileName));
		$fileNameTemp = preg_replace("/\[[0-9]*\]/i", "", $fileNameTemp);
		$file->SetFileName($fileNameTemp);
		$file->SetFileType($fileType);
		$file->SetOriginalFileName($fileNameTemp);
		$file->SetDescription("");
		$file->SetDocumentPath($destinationFileName);
		$file->SetFileSemantic($fileSemantic);		
		// Eintrag speichern
		if ($file->Store($db)!==true)
		{
			unlink(FM_FILE_ROOT.$destinationFileName);
			return -6;
		}
		return $file;
	}
	
	/**
	 * Gibt anhand des fileSemanticSpecific-Strings die Art des FMS-Schreibens zurück
	 * @param string $fileSemanticSpecificString
	 * @return string 
	 */
	static public function GetRSFileType($fileSemanticSpecificString)
	{
		$parts = explode("_", $fileSemanticSpecificString);
		switch ((int)$parts[0])
		{
			case FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_SONSTIGES:
				return "Sonstiges";
			case FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_AKTENNOTIZ:
				return "Aktennotiz";
			case FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_PROTOKOLL:
				return "Protokoll";
			default:
				return "?";
		}
	}
	
	/**
	 * Gibt den Status zurück, in welchem das FMS-Dokument hochgeladen wurde
	 * @param string $fileSemanticSpecificString
	 * @return int
	 */
	static public function GetRSFileStatus($fileSemanticSpecificString)
	{
		$parts = explode("_", $fileSemanticSpecificString);
		if (!isset($parts[1])) return -1;
		return (int)$parts[1];
	}
	
	
	static public function GetDownloadFileNameFromProzess(ProcessStatus $process)
	{
		if ($process==null) return '';
		$location = $process->GetLocation();
		if ($location==null) return '';
		$company = $location->GetCompany();
		if ($company==null) return '';
		$group = $company->GetGroup();
		if ($group==null) return '';
		
		return self::GetDownloadFileName($type, $group, $location, $date, $fileType);
			
	}
	
	/**
	 * return a standardized file name 
	 * @param string $type
	 * @param CGroup $group
	 * @param CLocation $location
	 * @param File $file
	 * @return string
	 */
	static public function GetDownloadFileName($type, CGroup $group=null, CLocation $location=null, File $file, ProcessStatus $processStatus=null)
	{
		$fileName ='';
		$fileName.=$type.'-';
		if (is_a($processStatus, 'ProcessStatusGroup')) $fileName.=$processStatus->GetName().'-';
		$fileName.=($group!=null ? $group->GetName().'-' : '');
		$fileName.=($location!=null ? $location->GetName().'-' : '');
		$fileName.=date("d.m.y", $file->GetCreationTime()).'-';
		$fileName.=date("Hi", $file->GetCreationTime());
		$fileName.='.'.$file->GetFileTypeExtension();
		return $fileName;
	}
	
}
?>