<?php
/**
 * Logging class for Customer DB import/export
 * @author Stephan Walleczek <s.walleczek@stollvongati.com>
 * @version 1.0
 */
class LoggingFileAccess extends Logging
{
	/**
	 * Table name
	 */
	const TABLE_NAME = "LoggingFileAccess";
	
	/**
	 * Enums of loggin status
	 */
	const TYPE_UNKNOWN = 0;
	const TYPE_CREATE = 1;
	const TYPE_ADD = 2;
	const TYPE_REMOVE = 3;
	const TYPE_DELETE = 4;
	const TYPE_CHANGED = 5;
	const TYPE_DOWNLOAD = 6;
	
	/**
	 * Type of access
	 * @var int 
	 */
	protected $type = self::TYPE_UNKNOWN;
	
	/**
	 * File id
	 * @var int 
	 */
	protected $fileId = -1;
	
	/**
	 * Name of the file
	 * @var string 
	 */
	protected $fileName = "";
	
	/**
	 * Name of the file
	 * @var string 
	 */
	protected $fileSystemFileName = "";
	
	/**
	 * Short description of the file
	 * @var string 
	 */
	protected $fileSemanticShort = "";
	
	/**
	 * Long description of the file
	 * @var string 
	 */
	protected $fileSemanticLong = "";
	
	/**
	 * Additional, report type specific infos
	 * @var string 
	 */
	protected $additionalInfo = "";
	
	/**
	 * Constructor
	 */
	public function LoggingFileAccess($type, File $file, DBManager $db)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("type", "fileId", "fileName", "fileSystemFileName", "fileSemanticShort", "fileSemanticLong", "additionalInfo");
		$dbConfig->rowParam = Array("INT", "BIGINT", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(255)", "VARCHAR(2550)");
		$dbConfig->rowIndex = Array("type", "fileId", "fileSemanticShort");
		parent::__construct($dbConfig);

		$this->type = $type;
		$this->fileId = $file->GetPKey();
		$this->fileName = $file->GetFileName();
		$this->fileSystemFileName = $file->GetDocumentPath();
		$desc = $file->GetFileSemanticDescription();
		$this->fileSemanticShort = $desc["short"];
		$this->fileSemanticLong = $desc["long"];
		$this->additionalInfo = serialize($file->GetDependencyStrings($db));
	}
	
	/**
	 * Write data to array
	 * @param DBManager $db
	 * @param array $rowName
	 * @param array $rowData
	 * @return boolean
	 */
	protected function BuildDBArray(&$db, &$rowName, &$rowData) 
	{
		parent::BuildDBArray($db, $rowName, $rowData);
		$rowName[] = "type";
		$rowData[] = $this->type;
		$rowName[] = "fileId";
		$rowData[] = $this->fileId;
		$rowName[] = "fileName";
		$rowData[] = $this->fileName;
		$rowName[] = "fileSystemFileName";
		$rowData[] = $this->fileSystemFileName;
		$rowName[] = "fileSemanticShort";
		$rowData[] = $this->fileSemanticShort;
		$rowName[] = "fileSemanticLong";
		$rowData[] = $this->fileSemanticLong;
		$rowName[] = "additionalInfo";
		$rowData[] = $this->additionalInfo;
		return true;
	}

	/**
	 * Read data from array
	 * @param DBManager $db
	 * @param array $data
	 * @return boolean
	 */
	protected function BuildFromDBArray(&$db, $data) 
	{
		parent::BuildFromDBArray($db, $data);
		$this->type = (int)$data["type"];
		$this->fileId = (int)$data["fileId"];
		$this->fileName = $data["fileName"];
		$this->fileSystemFileName = $data["fileSystemFileName"];
		$this->fileSemanticShort = $data["fileSemanticShort"];
		$this->fileSemanticLong = $data["fileSemanticLong"];
		$this->additionalInfo = $data["additionalInfo"];
		return true;
	}
	
	/**
	 * Get the type
	 * @return int
	 */
	public function GetType()
	{
		return $this->type;
	}

		
	/**
	 * Get the file id
	 * @return int
	 */
	public function GetFileId()
	{
		return $this->fileId;
	}
	
	/**
	 * Return the filename
	 * @return string
	 */
	public function GetFileName()
	{
		return $this->fileName;
	}

	/**
	 * Return the filename on the server
	 * @return string
	 */
	public function GetFileSystemFileName()
	{
		return $this->fileSystemFileName;
	}
	
	/**
	 * Return the short description of the semantic 
	 * @return string
	 */
	public function GetFileSemanticShort()
	{
		return $this->fileSemanticShort;
	}

	/**
	 * Return the long description of the semantic
	 * @return string
	 */
	public function GetFileSemanticLong()
	{
		return $this->fileSemanticLong;
	}

	/**
	 * Return additional infos
	 * @return string
	 */
	public function GetAdditionalInfo()
	{
		return $this->additionalInfo;
	}

}
?>