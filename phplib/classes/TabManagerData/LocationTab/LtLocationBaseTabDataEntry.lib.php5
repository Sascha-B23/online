<?php
/**
 * Basisklasse für Bericht Kundenstandorte incl. Hilfsfunktionen
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
abstract class LtLocationBaseTabDataEntry extends TabDataEntry 
{	
	/**
	 * Datenbankobjekt
	 * @var DBManager
	 */
	protected $db = null;
	
	/**
	 * ExtendedLanguageManager
	 * @var ExtendedLanguageManager
	 */
	protected $languageManager = null;
	
	/**
	 * FMS user?
	 * @var bool
	 */
	protected $userIsRSMember = false;
	
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param ExtendedLanguageManager $languageManager
	 * @param int $entryId
	 * @param string $entryCaption
	 */
	public function LtLocationBaseTabDataEntry(DBManager $db, ExtendedLanguageManager $languageManager, $entryId, $entryCaption)
	{
		parent::__construct($entryId, $entryCaption);
		$this->db = $db;
		$this->languageManager = $languageManager;
		if ($_SESSION["currentUser"]->GetGroupBasetype($db)>UM_GROUP_BASETYPE_KUNDE) $this->userIsRSMember = true;
	}
	
	/**
	 * Gibt den Inhalt des aktiven Tab aus
	 * @param int
	 */
	protected function PrintTabHeaderContent($headerData, $numRows=3, $usePrintTabHeaderRowFn = 1, $insertBreak = true, $insertHeaderBreak = true)
	{
		$width=floor(948/$numRows);
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<?	if( $insertHeaderBreak ){ ?>
			<tr>
			<? 	for($a=0; $a<$numRows; $a++){ ?>
				<td width="29">&#160;</td>
				<td width="<?=($width-29);?>">&#160;</td>
			<?	}?>
				<td width="29">&#160;</td>
			</tr>
		<?	}?>
			<tr>
			<? 	for($a=0; $a<$numRows; $a++){ ?>
				<td width="29">&#160;</td>
				<td width="<?=($width-29);?>" valign="top">
				<?	switch($usePrintTabHeaderRowFn){
						case 1:
							$this->PrintTabHeaderRowV1($headerData[$a]);
							break;
						case 2:
							$this->PrintTabHeaderRowV2($headerData[$a]);
							break;
						default:
							echo "Unbekannte PrintTabHeaderRow-Funktion!";
							break;
					}?>
				</td>
			<?	}?>
				<td width="29">&#160;</td>
			</tr>
		</table>
		<?if($insertBreak){?><br /><?}?>
		<?
	}
	
	/**
	 * Weiche für die einzelnen Status
	 */
	protected function PrintTabHeaderRowV1($headerRowData)
	{		
	?>	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<?	for($a=0; $a<count($headerRowData); $a++){ ?>
				<tr>
					<td valign="top" width="30%" align="right" class="contentText"><strong><?=str_replace(" ", "&#160;", $headerRowData[$a]["name"]);?></strong></td>
					<td valign="top" width="10px" align="right">&#160;</td>
					<td valign="top" align="left" class="contentText"><?=$headerRowData[$a]["value"];?></td>
				</tr>
		<? }?>
		</table><?
	}
		
	/**
	 * Weiche für die einzelnen Status
	 */
	protected function PrintTabHeaderRowV2($headerRowData)
	{
		for($a=0; $a<count($headerRowData); $a++)
		{ 
			?><strong><?=str_replace(" ", "&#160;", $headerRowData[$a]["name"]);?></strong><br />
			<?=$headerRowData[$a]["value"];?><br />
	<? }
	}
	
}
?>