<?php
/**
 * Logging class for reports
 * @author Stephan Walleczek <s.walleczek@stollvongati.com>
 * @version 1.0
 */
class LoggingReport extends Logging
{
	/**
	 * Table name
	 */
	const TABLE_NAME = "LoggingReport";
	
	/**
	 * Report ID
	 * @var int 
	 */
	protected $reportClass = "";
	
	/**
	 * Display type
	 * @var int 
	 */
	protected $displayType = 0;
	
	/**
	 * Additional, report type specific infos
	 * @var string 
	 */
	protected $additionalInfo = "";
	
	/**
	 * Constructor
	 */
	public function LoggingReport($reportClass, $displayType, $additionalInfo)
	{
		$dbConfig = new DBConfig();
		$dbConfig->tableName = self::TABLE_NAME;
		$dbConfig->rowName = Array("reportClass", "displayType", "additionalInfo");
		$dbConfig->rowParam = Array("VARCHAR(255)", "INT", "VARCHAR(2550)");
		$dbConfig->rowIndex = Array("reportClass", "displayType");
		parent::__construct($dbConfig);
		
		$this->reportClass = $reportClass;
		$this->displayType = $displayType;
		$this->additionalInfo = $additionalInfo;
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
		$rowName[] = "reportClass";
		$rowData[] = $this->reportClass;
		$rowName[] = "displayType";
		$rowData[] = $this->displayType;
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
		$this->reportClass = $data["reportClass"];
		$this->displayType = (int)$data["displayType"];
		$this->additionalInfo = $data["additionalInfo"];
		return true;
	}
	
	/**
	 * Get the report type
	 * @return int
	 */
	public function GetReportClass()
	{
		return $this->reportClass;
	}

	/**
	 * Get the display type
	 * @return int
	 */
	public function GetDisplayType()
	{
		return $this->displayType;
	}

	/**
	 * Get the additional infos
	 * @return string
	 */
	public function GetAdditionalInfo()
	{
		return $this->additionalInfo;
	}

}
?>