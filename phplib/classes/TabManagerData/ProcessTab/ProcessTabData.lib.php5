<?php
require_once 'TaskTabDataEntry.lib.php5';
require_once 'ContractTabDataEntry.lib.php5';
require_once 'TeilabrechnungTabDataEntry.lib.php5';
require_once 'WiderspruchTabDataEntry.lib.php5';
require_once 'TerminschieneTabDataEntry.lib.php5';

/**
 * Basisklasse, welche die Daten der Tabeinträge bereitstellt
 * 
 * @access   	abstract
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class ProcessTabData extends TabData
{
	/**
	 * process tabs enumerations
	 * @var int 
	 */
	const TAB_TASK = 0;
	const TAB_CONTRACT = 1;
	const TAB_TA = 2;
	const TAB_WS = 3;
	const TAB_TS = 4;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ProcessStatus $processStatus
	 */
	public function ProcessTabData(DBManager $db, ProcessStatus $processStatus)
	{
		$this->tabDataEntries[] = new TaskTabDataEntry($db, $processStatus);
		$this->tabDataEntries[] = new ContractTabDataEntry($db, $processStatus);
		$this->tabDataEntries[] = new TeilabrechnungTabDataEntry($db, $processStatus);
		$this->tabDataEntries[] = new WiderspruchTabDataEntry($db, $processStatus);
		$this->tabDataEntries[] = new TerminschieneTabDataEntry($db, $processStatus);
	}	
	
}
?>