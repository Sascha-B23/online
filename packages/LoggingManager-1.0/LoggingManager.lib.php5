<?php
require_once ('Logging.lib.php5');
require_once ('logging/LoggingLogin.lib.php5');
require_once ('logging/LoggingReport.lib.php5');
require_once ('logging/LoggingCustomerSync.lib.php5');
require_once ('logging/LoggingFileAccess.lib.php5');
require_once ('logging/LoggingProcessStatus.lib.php5');
require_once ('logging/LoggingSyncManager.lib.php5');
require_once ('logging/LoggingWsApproval.lib.php5');
require_once ('logging/LoggingWsCustomerView.lib.php5');
require_once ('logging/LoggingWsCustomerUpload.lib.php5');

/**
 * Logging Manager
 * @author Stephan Walleczek <s.walleczek@stollvongati.com>
 * @version 1.0
 */
class LoggingManager
{		
	/**
	 * Singelton Instance 
	 * @var DynamicTableManager 
	 */
	private static $instance = null;
	
	/**
	 * DB-Object to store loggings to
	 * @var DBManager 
	 */
	protected $db = null;
	
	/**
	 * Return the singelton 
	 * Parameter $db is required only on the first call to GetInstance
	 * @param DBManager $db
	 * @return LoggingManager
	 */
	public static function GetInstance(DBManager $db = null)
	{
		if (self::$instance==null)
		{
			self::$instance = new LoggingManager($db);
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function LoggingManager(DBManager $db)
	{
		$this->db = $db;
		(new LoggingWsApproval(""))->UpdateDB($db);
		(new LoggingWsCustomerView("", "", "", ""))->UpdateDB($db);
		(new LoggingWsCustomerUpload("", ""))->UpdateDB($db);	
	}
	
	/**
	 * Add the passed Logging object to queue
	 * @param Logging $logging
	 * @return boolean|int
	 */
	public function Log(Logging $logging)
	{
		$logging->UpdateDB($this->db);
		return $logging->Store($this->db);
	}

	/**
	 * 
	 * @param DBManager $db
	 * @return string
	 */
	public function GetAsExcelStream(DBManager $db, $startDate, $endDate)
	{
	
		$timeWhereClause = " creationTime>=".(int)$startDate." AND creationTime<=".(int)$endDate." ";
		
		$LOGIN_STATUS = Array(	LoggingLogin::LOGIN_STATUS_UNKNOWN => "???",
								LoggingLogin::LOGIN_STATUS_SUCCESS => "LOGIN",
								LoggingLogin::LOGIN_STATUS_FAILED => "FEHLGESCHLAGEN",
								LoggingLogin::LOGIN_STATUS_FAILED_LOCKED => "FEHLGESCHLAGEN (ZUGANG GESPERRT)",
								LoggingLogin::LOGIN_STATUS_LOGOUT => "LOGOUT"
							);

		$REPORT_CLASS = Array(	"LocationReport" => "Kundenstandorte",
								"ProzessStatusReport" => "Standortvergleich Prozessstatus",
								"SignalLightReport" => "Standortvergleich Ampelbewertung",
								"TerminschieneReport" => "Terminschiene",
								"TAPReport" => "Teilabrechnungspositionen"
							);

		$REPORT_DISPLAY = Array(0 => "WEB",
								ReportManager::REPORT_FORMAT_CSV => "CSV",
								ReportManager::REPORT_FORMAT_PDF => "PDF"
							);

		$FILE_ACCESS_TYPE = Array(	LoggingFileAccess::TYPE_UNKNOWN => "???",
									LoggingFileAccess::TYPE_CREATE => "ERSTELLEN",
									LoggingFileAccess::TYPE_ADD => "VERKNÜPFEN",
									LoggingFileAccess::TYPE_REMOVE => "VERKNÜPFUNG LÖSCHEN",
									LoggingFileAccess::TYPE_DELETE => "LÖSCHEN",
									LoggingFileAccess::TYPE_CHANGED => "ÄNDERN",
									LoggingFileAccess::TYPE_DOWNLOAD => "DOWNLOAD"
								);

		$PROCESS_TYPE = Array(	LoggingProcessStatus::TYPE_UNKNOWN => "???",
								LoggingProcessStatus::TYPE_DELETE => "LÖSCHEN",
								LoggingProcessStatus::TYPE_MANUAL_STATUS_CHANGE => "MANUELLE STATUSÄNDERUNG"
							);
		
		$CUSTOMER_SYNC_TYPE = Array(LoggingCustomerSync::TYPE_UNKNOWN => "???",
								LoggingCustomerSync::TYPE_IMPORT => "IMPORT",
								LoggingCustomerSync::TYPE_EXPORT => "EXPORT"
							);

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set document properties
		$objPHPExcel->getProperties()->setCreator("KIM Online")
									 ->setLastModifiedBy("")
									 ->setTitle("KIM Online Zuggriffsprotokoll ".date("d.m.Y"))
									 ->setSubject("KIM Online Zuggriffsprotokoll ".date("d.m.Y"))
									 ->setDescription("KIM Online Zuggriffsprotokoll ".date("d.m.Y"))
									 ->setKeywords("")
									 ->setCategory("");

		// Login & Logout
		$data = $db->SelectAssoc("SELECT * FROM ".LoggingLogin::TABLE_NAME." WHERE ".$timeWhereClause);
		$objPHPExcel->setActiveSheetIndex(0)->setTitle("Login & Logout");
		for ($a=0; $a<count($data); $a++)
		{
			$objPHPExcel->getActiveSheet()
						->setCellValue('A'.($a+1), date("d.m.Y", $data[$a]['creationTime']))
						->setCellValue('B'.($a+1), date("H:i", $data[$a]['creationTime']))
						->setCellValue('C'.($a+1), $data[$a]['sessionId'])
						->setCellValue('D'.($a+1), $data[$a]['loginEMailAddress'])
						->setCellValue('E'.($a+1), $LOGIN_STATUS[$data[$a]['loginStatus']]);
		}

		// Berichte
		$data = $db->SelectAssoc("SELECT * FROM ".LoggingReport::TABLE_NAME." WHERE ".$timeWhereClause);
		$objPHPExcel->createSheet(1);
		$objPHPExcel->setActiveSheetIndex(1)->setTitle("Berichte");
		for ($a=0; $a<count($data); $a++)
		{
			$objPHPExcel->getActiveSheet()
						->setCellValue('A'.($a+1), date("d.m.Y", $data[$a]['creationTime']))
						->setCellValue('B'.($a+1), date("H:i", $data[$a]['creationTime']))
						->setCellValue('C'.($a+1), $data[$a]['sessionId'])
						->setCellValue('D'.($a+1), $REPORT_CLASS[$data[$a]['reportClass']])
						->setCellValue('E'.($a+1), $REPORT_DISPLAY[$data[$a]['displayType']])
						->setCellValue('F'.($a+1), $data[$a]['additionalInfo']);
		}

		// Dateizugriff
		$data = $db->SelectAssoc("SELECT * FROM ".LoggingFileAccess::TABLE_NAME." WHERE ".$timeWhereClause);
		$objPHPExcel->createSheet(2);
		$objPHPExcel->setActiveSheetIndex(2)->setTitle("Dateizugriff");
		for ($a=0; $a<count($data); $a++)
		{
			$objPHPExcel->getActiveSheet()
						->setCellValue('A'.($a+1), date("d.m.Y", $data[$a]['creationTime']))
						->setCellValue('B'.($a+1), date("H:i", $data[$a]['creationTime']))
						->setCellValue('C'.($a+1), $data[$a]['sessionId'])
						->setCellValue('D'.($a+1), $FILE_ACCESS_TYPE[$data[$a]['type']])
						->setCellValue('E'.($a+1), $data[$a]['fileName'])
						->setCellValue('F'.($a+1), $data[$a]['fileSemanticShort'])
						->setCellValue('G'.($a+1), $data[$a]['fileSemanticLong'])
						->setCellValue('H'.($a+1), $data[$a]['fileSystemFileName'])
						->setCellValue('I'.($a+1), $data[$a]['fileId'])
						->setCellValue('J'.($a+1), $data[$a]['additionalInfo']);
		}

		// Löschen und Überführen von Prozessstatus
		$data = $db->SelectAssoc("SELECT * FROM ".LoggingProcessStatus::TABLE_NAME." WHERE ".$timeWhereClause);
		$objPHPExcel->createSheet(3);
		$objPHPExcel->setActiveSheetIndex(3)->setTitle("Prozesse");
		for ($a=0; $a<count($data); $a++)
		{
			$objPHPExcel->getActiveSheet()
						->setCellValue('A'.($a+1), date("d.m.Y", $data[$a]['creationTime']))
						->setCellValue('B'.($a+1), date("H:i", $data[$a]['creationTime']))
						->setCellValue('C'.($a+1), $data[$a]['sessionId'])
						->setCellValue('D'.($a+1), $PROCESS_TYPE[$data[$a]['type']])
						->setCellValue('E'.($a+1), $data[$a]['newStatus'])
						->setCellValue('F'.($a+1), $data[$a]['oldStatus'])
						->setCellValue('G'.($a+1), $data[$a]['additionalInfo']);
		}

		// Kundendaten Import & Export
		$data = $db->SelectAssoc("SELECT * FROM ".LoggingCustomerSync::TABLE_NAME." WHERE ".$timeWhereClause);
		$objPHPExcel->createSheet(4);
		$objPHPExcel->setActiveSheetIndex(4)->setTitle("Kundendaten Import & Export");
		for ($a=0; $a<count($data); $a++)
		{
			$objPHPExcel->getActiveSheet()
						->setCellValue('A'.($a+1), date("d.m.Y", $data[$a]['creationTime']))
						->setCellValue('B'.($a+1), date("H:i", $data[$a]['creationTime']))
						->setCellValue('C'.($a+1), $data[$a]['sessionId'])
						->setCellValue('D'.($a+1), $CUSTOMER_SYNC_TYPE[$data[$a]['type']]);
		}
		
		// Kundendaten Import & Export
		$data = $db->SelectAssoc("SELECT * FROM ".LoggingSyncManager::TABLE_NAME." WHERE ".$timeWhereClause);
		$objPHPExcel->createSheet(5);
		$objPHPExcel->setActiveSheetIndex(5)->setTitle("CSV-Datensynchronisierung");
		for ($a=0; $a<count($data); $a++)
		{
			$objPHPExcel->getActiveSheet()
						->setCellValue('A'.($a+1), date("d.m.Y", $data[$a]['creationTime']))
						->setCellValue('B'.($a+1), date("H:i", $data[$a]['creationTime']))
						->setCellValue('C'.($a+1), $data[$a]['sessionId'])
						->setCellValue('D'.($a+1), $data[$a]['syncManagerName'])
						->setCellValue('E'.($a+1), $CUSTOMER_SYNC_TYPE[$data[$a]['type']]);
		}
		
		// WS zur Freigabe an Kunde
		$data = $db->SelectAssoc("SELECT * FROM ".LoggingWsApproval::TABLE_NAME." WHERE ".$timeWhereClause);
		$objPHPExcel->createSheet(6);
		$objPHPExcel->setActiveSheetIndex(6)->setTitle("WS zur Freigabe an Kunde");
		for ($a=0; $a<count($data); $a++)
		{
			$objPHPExcel->getActiveSheet()
						->setCellValue('A'.($a+1), date("d.m.Y", $data[$a]['creationTime']))
						->setCellValue('B'.($a+1), date("H:i", $data[$a]['creationTime']))
						->setCellValue('C'.($a+1), $data[$a]['sessionId'])
						->setCellValue('D'.($a+1), $data[$a]['processPath']);
		}
		
		// WS vom Kunde geöffnet
		$data = $db->SelectAssoc("SELECT * FROM ".LoggingWsCustomerView::TABLE_NAME." WHERE ".$timeWhereClause);
		$objPHPExcel->createSheet(7);
		$objPHPExcel->setActiveSheetIndex(7)->setTitle("WS durch Kunde geöffnet");
		for ($a=0; $a<count($data); $a++)
		{
			$objPHPExcel->getActiveSheet()
						->setCellValue('A'.($a+1), date("d.m.Y", $data[$a]['creationTime']))
						->setCellValue('B'.($a+1), date("H:i", $data[$a]['creationTime']))
						->setCellValue('C'.($a+1), $data[$a]['sessionId'])
						->setCellValue('D'.($a+1), $data[$a]['fileName'])
						->setCellValue('E'.($a+1), $data[$a]['documentType'])
						->setCellValue('F'.($a+1), $data[$a]['processPath']);
		}
		
		// WS vom Kunde hochgeladen
		$data = $db->SelectAssoc("SELECT * FROM ".LoggingWsCustomerUpload::TABLE_NAME." WHERE ".$timeWhereClause);
		$objPHPExcel->createSheet(8);
		$objPHPExcel->setActiveSheetIndex(8)->setTitle("WS durch Kunde hochgeladen");
		for ($a=0; $a<count($data); $a++)
		{
			$objPHPExcel->getActiveSheet()
						->setCellValue('A'.($a+1), date("d.m.Y", $data[$a]['creationTime']))
						->setCellValue('B'.($a+1), date("H:i", $data[$a]['creationTime']))
						->setCellValue('C'.($a+1), $data[$a]['sessionId'])
						->setCellValue('D'.($a+1), $data[$a]['fileName'])
						->setCellValue('E'.($a+1), $data[$a]['processPath']);
		}
		
		
		$objPHPExcel->setActiveSheetIndex(0);

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_start();
		$stream = $objWriter->save('php://output');
		$CONTENT = ob_get_contents();
		ob_end_clean();
		return $CONTENT;
	}
	
}
?>