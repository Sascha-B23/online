<?php
require_once 'ReportManager.lib.php5';
/**
 * Implementation of report 'Standortvergleich Ampelbewertung'
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2012 Stoll von Gáti GmbH www.stollvongati.com
 */
class SignalLightReport extends ReportManager
{
	/**
	 * Constructor
	 * @param DBManager $db
	 * @param User $user
	 */
	public function SignalLightReport(DBManager $db, User $user, CustomerManager $customerManager, AddressManager $addressManager, UserManager $userManager, RSKostenartManager $kostenartManager, ErrorManager $errorManager, ExtendedLanguageManager $languageManager)
	{
		parent::__construct($db, $user, $customerManager, $addressManager, $userManager, $kostenartManager, $errorManager, $languageManager, "signalLightReport", "Berichte > Standortvergleich Ampelbewertung");
	}

	/**
	 * Return the default setting
	 * @return array 
	 */
	protected function GetDefaultSetting()
	{	
		$defalutSettings = Array();
		$defalutSettings["proccess"] = "all";
		$defalutSettings["standorttyp"] = "all";
		$defalutSettings["group"] = "all";
		$defalutSettings["company"] = "all";
		$defalutSettings["owner"] = "all";
		$defalutSettings["trustee"] = "all";
		$defalutSettings["advocate"] = "all";
		$defalutSettings["rsuser"] = "all";
		$defalutSettings["custumeruser"] = "all";
		$defalutSettings["location"] = "all";
		$defalutSettings["shop"] = "all";
		$defalutSettings["showRealisiert"] = "-1";
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$defalutSettings["showArchievedStatus"] = -1;
		}
		else
		{
			$defalutSettings["showArchievedStatus"] = Schedule::ARCHIVE_STATUS_UPTODATE;
		}
		return $defalutSettings;
	}
	
	/**
	 * Prepare the var from POST for the report 
	 * @return boolean 
	 */
	protected function PrepareSettings()
	{
		if (isset($_POST["proccess"])) $this->SetValue("proccess", $_POST["proccess"]);
		if (isset($_POST["standorttyp"])) $this->SetValue("standorttyp", $_POST["standorttyp"]);
		if (isset($_POST["group"])) $this->SetValue("group", $_POST["group"]);
		if (isset($_POST["company"])) $this->SetValue("company", $_POST["company"]);
		if (isset($_POST["owner"])) $this->SetValue("owner", $_POST["owner"]);
		if (isset($_POST["trustee"])) $this->SetValue("trustee", $_POST["trustee"]);
		if (isset($_POST["advocate"])) $this->SetValue("advocate", $_POST["advocate"]);
		if (isset($_POST["rsuser"])) $this->SetValue("rsuser", $_POST["rsuser"]);
		if (isset($_POST["custumeruser"])) $this->SetValue("custumeruser", $_POST["custumeruser"]);
		if (isset($_POST["location"])) $this->SetValue("location", $_POST["location"]);
		if (isset($_POST["shop"])) $this->SetValue("shop", $_POST["shop"]);
		if (isset($_POST["showRealisiert"])) $this->SetValue("showRealisiert", $_POST["showRealisiert"]);
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			if (isset($_POST["showArchievedStatus"])) $this->SetValue("showArchievedStatus", (int)$_POST["showArchievedStatus"]);
		}
		else
		{
			// Customers only can see uptodate prozesses
			$this->SetValue("showArchievedStatus", Schedule::ARCHIVE_STATUS_UPTODATE);
		}

		return true;
	}
	
	/**
	 * Return the filter elements
	 * @return FormElement[]
	 */
	public function GetFilterElements()
	{
		$elements = Array();
		$elements[] = $this->CreateFormProzessstatus($this->GetValue("proccess"), Array(0,1,2,3));
		$elements[] = $this->CreateFormStandorttyp($this->GetValue("standorttyp"));
		$elements[] = $this->CreateFormGroups($this->GetValue("group"));
		$elements[] = $this->CreateFormCompanies($this->GetValue("company"));
		//$elements[] = $this->CreateFormOwner($this->GetValue("owner"));
		//$elements[] = $this->CreateFormTrustee($this->GetValue("trustee"));
		//$elements[] = $this->CreateFormAdvocate($this->GetValue("advocate"));
		$elements[] = $this->CreateFormFmsUsers($this->GetValue("rsuser"));
		$elements[] = $this->CreateFormCustomerUsers($this->GetValue("custumeruser"));
		$elements[] = $this->CreateFormLocation($this->GetValue("location"));
		$elements[] = $this->CreateFormShop($this->GetValue("shop"));
		$elements[] = $this->CreateFormRealisiert($this->GetValue("showRealisiert"));
		if ($this->user->GetGroupBasetype($this->db)>UM_GROUP_BASETYPE_KUNDE)
		{
			$elements[] = $this->CreateFormArchievedStatus($this->GetValue("showArchievedStatus"));
		}
		return $elements;
	}
	
	/**
	 * Return if the specified format is supported
	 * @param int $format
	 * @return boolean
	 */
	public function IsFormatSupported($format)
	{
		//if ($format==ReportManager::REPORT_FORMAT_CSV) return true;
		if ($format==ReportManager::REPORT_FORMAT_PDF) return true;
		return false;
	}
	
	/**
	 * Prepare the report data for output
	 * @return array
	 */
	private function GetReportData()
	{
		$data=Array();
		if (isset($_POST["show"]))
		{
			$query = $this->BuildSqlQuery();
			$data = $this->db->SelectAssoc($query);
			//print_r($data);
			// Ergebnisse nach Shops und Abrechnungsjahr gruppieren
			$sortedData = Array();
			foreach ($data as $value)
			{
				if (isset($sortedData[$value["shopID"]]))
				{
					// Ist bereits einen Widerspruch für dieses Jahr gesetzt?
					if (isset($sortedData[$value["shopID"]]["data"][$value["abrechnungJahrID"]]))
					{
						// Es gibt bereits einen Widerspruch für dieses Jahr -> Folgewidersprüche vorhanden deshalb ...
						if ($sortedData[$value["shopID"]]["data"][$value["abrechnungJahrID"]]["widerspruchsNummer"]<$value["widerspruchsNummer"])
						{
							// ...  aktuellen Folgewiderspruch verwenden - also den mit der höchsten Nummer
							$sortedData[$value["shopID"]]["data"][$value["abrechnungJahrID"]] = $value;
						}
					}
					else
					{
						$sortedData[$value["shopID"]]["data"][$value["abrechnungJahrID"]] = $value;
					}
				}
				else
				{
					$sortedData[$value["shopID"]]=Array("shopID" => $value["shopID"], "data" => Array($value["abrechnungJahrID"] => $value));
				}
			}
			//print_r($sortedData);
			// Widerspruchspunkte der Shops für jedes Jahr laden
			$data=Array();
			foreach ($sortedData as $value1)
			{
				$abrechnungen = Array();
				foreach ($value1["data"] as $value2)
				{
					// Widerspruch laden
					$wsTemp = new Widerspruch($this->db);
					$wsTemp->Load((int)$value2["widerspruchPkey"], $this->db);
					// WS-Punkte holen
					$wsps = $wsTemp->GetWiderspruchspunkte($this->db);
					$positions = Array();
					$allZero = true;
					for ($c=0; $c<count($wsps); $c++)
					{
						$positions[]=array(	"name" => $wsps[$c]->GetTitle(), 
											"betragGruen" => $wsps[$c]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN, 0, (int)$this->GetValue("showRealisiert")), 
											"betragGelb" => $wsps[$c]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GELB, 0, (int)$this->GetValue("showRealisiert")), 
											"betragRot" => $wsps[$c]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT, 0, (int)$this->GetValue("showRealisiert")), 
											"betragGrau" => $wsps[$c]->GetKuerzungbetrag($this->db, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU, 0, (int)$this->GetValue("showRealisiert"))
										);
						if ($positions[count($positions)-1]["betragGruen"]!=0.0 || $positions[count($positions)-1]["betragGelb"]!=0.0 || $positions[count($positions)-1]["betragRot"]!=0.0 || $positions[count($positions)-1]["betragGrau"]!=0.0) $allZero = false;
						// TODO: $positions[count($positions)-1]["betrag"] = ???;
					}
					if (!$allZero || (int)$this->GetValue("showRealisiert")==-1)
					{
						$abrechnungen[]=array(	"abrechnungsjahr" => $value2["abrechnungJahr"],
												"positionen" => $positions
											);
					}
				}
				if (count($abrechnungen)>0)
				{
					$keys = array_keys($value1["data"]);
					$data[] = Array("gruppe" => $value1["data"][$keys[0]]["groupName"],
									"firma" => $value1["data"][$keys[0]]["companyName"],
									"standort" => $value1["data"][$keys[0]]["locationName"],
									"laden" => $value1["data"][$keys[0]]["shopName"],
									"currency" => $value1["data"][$keys[0]]["currency"],
									"anschrift" => $value1["data"][$keys[0]]["locationStreet"].", ".$value1["data"][$keys[0]]["locationZIP"]." ".$value1["data"][$keys[0]]["locationCity"],
									"standorttyp" => GetLocationName($value1["data"][0]["locationType"]),
									"abrechnungen" => $abrechnungen
								);
				}
			}
			//print_r($data);
		}
		return $data;
	}
	
	/**
	 * Build the SQL-Query string
	 * @return string
	 */
	private function BuildSqlQuery()
	{
		// build db query
		$query ="SELECT ".Widerspruch::TABLE_NAME.".pkey AS widerspruchPkey, ".Widerspruch::TABLE_NAME.".widerspruchsNummer, ".CGroup::TABLE_NAME.".name AS groupName, ".CCompany::TABLE_NAME.".name AS companyName, ".CLocation::TABLE_NAME.".pkey AS locationID, ".Contract::TABLE_NAME.".currency AS currency, ".CShop::TABLE_NAME.".pkey AS shopID, CONCAT(".CShop::TABLE_NAME.".name, ' ', ".CShop::TABLE_NAME.".internalShopNo) AS shopName, ".CLocation::TABLE_NAME.".name AS locationName, ".CLocation::TABLE_NAME.".city AS locationCity, ".CLocation::TABLE_NAME.".zip AS locationZIP, ".CLocation::TABLE_NAME.".street AS locationStreet, ".CLocation::TABLE_NAME.".locationType, ".AbrechnungsJahr::TABLE_NAME.".pkey AS abrechnungJahrID, ".AbrechnungsJahr::TABLE_NAME.".jahr AS abrechnungJahr";
		$query.=" FROM ".Widerspruch::TABLE_NAME;
		$query.=" LEFT JOIN ".AbrechnungsJahr::TABLE_NAME." ON ".Widerspruch ::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".Teilabrechnung::TABLE_NAME." ON ".Teilabrechnung::TABLE_NAME.".abrechnungsJahr=".AbrechnungsJahr::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".Contract::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".contract =".Contract::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CShop::TABLE_NAME." ON ".Contract::TABLE_NAME.".cShop=".CShop::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CLocation::TABLE_NAME." ON ".CShop::TABLE_NAME.".cLocation=".CLocation::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CCompany::TABLE_NAME." ON ".CLocation::TABLE_NAME.".cCompany=".CCompany::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".CGroup::TABLE_NAME." ON ".CCompany::TABLE_NAME.".cGroup=".CGroup::TABLE_NAME.".pkey";
		$query.=" LEFT JOIN ".ProcessStatus::TABLE_NAME." ON ".AbrechnungsJahr::TABLE_NAME.".pkey =".ProcessStatus::TABLE_NAME.".abrechnungsjahr";
		// build WHERE clause
		$whereClause = $this->BuildWhereClause();
		if ($whereClause!="") $query.=" WHERE ".$whereClause;
		// sort by year
		$query.=" ORDER BY abrechnungJahr";
		//echo $query;
		return $query;
	}
	
	/**
	 * Helper to add terms to the WHERE-clause
	 * @param string $whereClause
	 * @param string $postVarName
	 * @param string $columnName
	 * @param string $allValue
	 */
	private function AppendToWhereClause(&$whereClause, $postVarName, $columnName, $allValue="all")
	{
		if (isset($_POST[$postVarName]) && $_POST[$postVarName]!=$allValue && $_POST[$postVarName]==(int)$_POST[$postVarName])
		{
			$whereClause.=($whereClause!="" ? " AND " : "").$columnName."=".(int)$_POST[$postVarName];
		}
	}
	
	/**
	 * Build the db WHERE-clause
	 * @return string
	 */
	private function BuildWhereClause()
	{
		$whereClause = "";
		$this->AppendToWhereClause($whereClause, "standorttyp", CLocation::TABLE_NAME.".locationType");
		$this->AppendToWhereClause($whereClause, "group", CGroup::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "company", CCompany::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "location", CLocation::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "shop", CShop::TABLE_NAME.".pkey");
		$this->AppendToWhereClause($whereClause, "rsuser", CShop::TABLE_NAME.".cPersonRS");
		$this->AppendToWhereClause($whereClause, "custumeruser", CShop::TABLE_NAME.".cPersonCustomer");
		//$this->AppendToWhereClause($whereClause, "owner", Teilabrechnung::TABLE_NAME.".eigentuemer");
		//$this->AppendToWhereClause($whereClause, "trustee", Teilabrechnung::TABLE_NAME.".verwalter");
		//$this->AppendToWhereClause($whereClause, "advocate", Teilabrechnung::TABLE_NAME.".anwalt");
		$this->AppendToWhereClause($whereClause, "proccess", ProcessStatus::TABLE_NAME.".currentStatus");
		$this->AppendToWhereClause($whereClause, "showArchievedStatus", ProcessStatus::TABLE_NAME.".archiveStatus", "-1");
		
		// add access rights to query
		if ($this->user->GetGroupBasetype($this->db)<UM_GROUP_BASETYPE_LOWEST_FMS_USER_GROUP)
		{
			$groupIDs = $this->user->GetGroupIDs($this->db);
			for ($a=0; $a<count($groupIDs); $a++)
			{
				if ($groupQuery!="") $groupQuery.=" OR ";
				$groupQuery.=" userGroup=".(int)$groupIDs[$a];
			}
			if (trim($whereClause)!="")
			{
				$whereClause = "(".$groupQuery.")"." AND ".$whereClause;
			}
			else
			{
				$whereClause = "(".$groupQuery.")";
			}
		}
		return $whereClause;
	}
	
	/**
	 * Output the report as HTML
	 * @return bool
	 */
	public function PrintHtml()
	{
		$data = $this->GetReportData();
		include("template_standortvergleichAmpel.inc.php5");
		return true;
	}
	
	
	/**
	 * Output the report in the specified format
	 * @param int $format
	 * @return bool
	 */
	public function StreamAsFormat($format)
	{
		if ($format==ReportManager::REPORT_FORMAT_PDF) return $this->StreamAsPdf();
		if ($format==ReportManager::REPORT_FORMAT_CSV) return $this->StreamAsCsv();
		return false;
	}
	
	/**
	 * Stream report as PDF-File
	 */
	private function StreamAsPdf()
	{
		global $SHARED_HTTP_ROOT;
		ob_start();
		?>	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				</head>
				<body>
					<style type="text/css">
						body{
							text-align: center;
							background-color:#ffffff;
							color:#404040;
							font-size: 11px;
							font-family: Arial, Helvetica, sans-serif;
							line-height: 16px;
							margin-top:0px;
							padding:0px;	
						}

						table{
							color:#404040;
							font-size: 11px;
							font-family: Arial, Helvetica, sans-serif;
							line-height: 16px;
						}

						img{
							border:0;
							margin:0px;
							padding:0px;	
						}
					</style>
					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td>
								<img src="<?=$SHARED_HTTP_ROOT?>pics/Logo.png" alt="" align="left" /><br />
							</td>
						</tr>
						<tr>
							<td style="height:10px;"></td>
						</tr>
						<tr>
							<td>
								<? $this->PrintHtml(); ?>
							</td>
						</tr>
					</table>
				</body>
			</html>
			<?
		$CONTENT = ob_get_contents();
		ob_end_clean();
		// PDF erzeugen
		include("html2pdf.php5");
		$pdfContent = convert_to_pdf($CONTENT, "A4", 964, false, '', true );
		if( $pdfContent!="" )
		{
			// PDF streamen...
			header('HTTP/1.1 200 OK');
			header('Status: 200 OK');
			header('Accept-Ranges: bytes');
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header('Content-Transfer-Encoding: Binary');
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"Ampelbewertung.pdf\"");
			header("Content-Length: ".strlen($pdfContent));
			echo $pdfContent;
			exit;
		}
		else
		{
			// TODO: Fehler: PDF konnte nicht erzeugt werden
			echo "Fehler 1";
		}
		return false;
	}
	
	/**
	 * Stream report as CSV-File
	 */
	private function StreamAsCsv()
	{
		$sep = ";";
		$endline = "\n";
		$myCSVString = "Gruppe".$sep."Firma".$sep."Standort".$sep."Anschrift".$sep."Standorttyp".$sep."Abrechnungsjahr".$sep."Position".$sep."Betrag".$sep.$endline;
		$data = $this->GetReportData();
		for($a=0; $a<count($data); $a++)
		{
			for($b=0; $b<count($data[$a]["abrechnungen"]); $b++)
			{
				for($c=0; $c<count($data[$a]["abrechnungen"][$b]["positionen"]); $c++)
				{
					$myCSVString .= str_replace(";", ",", $data[$a]["gruppe"]).$sep;
					$myCSVString .= str_replace(";", ",", $data[$a]["firma"]).$sep;
					$myCSVString .= str_replace(";", ",", $data[$a]["standort"]).$sep;
					$myCSVString .= str_replace(";", ",", $data[$a]["anschrift"]).$sep;
					$myCSVString .= str_replace(";", ",", $data[$a]["standorttyp"]).$sep;
					$myCSVString .= str_replace(";", ",", $data[$a]["abrechnungen"][$b]["abrechnungsjahr"]).$sep;
					$myCSVString .= str_replace(";", ",", $data[$a]["abrechnungen"][$b]["positionen"][$c]["name"]).$sep;
					$myCSVString .= str_replace(";", ",", HelperLib::ConvertFloatToLocalizedString($data[$a]["abrechnungen"][$b]["positionen"][$c]["betrag"])).$sep.$endline;
				}
			}
		}
		$myCSVString = utf8_decode($myCSVString);
		header('HTTP/1.1 200 OK');
		header('Status: 200 OK');
		header('Accept-Ranges: bytes');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");      
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Transfer-Encoding: Binary');
		header("Content-type: application/octetstream; charset=UTF-8");
		header("Content-Disposition: attachment; filename=\"Ampelbewertung.csv\"");
		header("Content-Length: ".(string)strlen($myCSVString));
		echo $myCSVString;
		exit;
	}
	
}
?>