<?php
define("FM_FILE_ROOT", $DOMAIN_FILE_SYSTEM_ROOT."uploadfiles/");

// Zuordung der Dateiendungen zu den entsprechenden Dateitypen
$FM_FILEEXTENSION_TO_FILETYPE = Array( 	File::FM_FILE_TYPE_UNKNOWN 	=> Array(),
										File::FM_FILE_TYPE_PDF 		=> Array("PDF"),
										File::FM_FILE_TYPE_RTF 		=> Array("RTF"),
										File::FM_FILE_TYPE_EXCEL 	=> Array("XLS", "XLSX"),
									);

define("FM_FILE_SEMANTIC_UNKNOWN", 0);
define("FM_FILE_SEMANTIC_MIETVERTRAG", 1);
define("FM_FILE_SEMANTIC_MIETVERTRAGANLAGE", 2);
define("FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG", 4);
define("FM_FILE_SEMANTIC_RSSCHREIBEN", 8);
define("FM_FILE_SEMANTIC_DATEIFUERKUNDE", 16);
define("FM_FILE_SEMANTIC_ANTWORTSCHREIBEN", 32);
define("FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT", 64);
define("FM_FILE_SEMANTIC_NACHTRAG", 128);
define("FM_FILE_SEMANTIC_SONSTIGES", 256);
define("FM_FILE_SEMANTIC_TEILABRECHNUNG", 512);
define("FM_FILE_SEMANTIC_COMMENT", 1024);
define("FM_FILE_SEMANTIC_TEMP", 2048);
define("FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE", 4096);
define("FM_FILE_SEMANTIC_WIDERSPRUCH", 8192);
define("FM_FILE_SEMANTIC_WSANSCHREIBEN", 16384);
define("FM_FILE_SEMANTIC_PROTOCOL", 32768);
define("FM_FILE_SEMANTIC_NEWCUSTOMERFILE", 65536);
define("FM_FILE_SEMANTIC_WIDERSPRUCH_RECHNUNG", 131072);
define("FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE", 262144);
define("FM_FILE_SEMANTIC_STAMMDATENBLATT", 524288);
define("FM_FILE_SEMANTIC_KONDITIONSUNDFRISTENLISTE", 1048576);

// Zuordnung der erlaubten Dateitypen zu den jeweiligen Semantiken
$FM_ALLOWED_FILETYPE_FOR_SEMANTIC = Array( 	FM_FILE_SEMANTIC_UNKNOWN						=> File::FM_FILE_TYPE_UNKNOWN,
											FM_FILE_SEMANTIC_MIETVERTRAG 					=> File::FM_FILE_TYPE_PDF | File::FM_FILE_TYPE_RTF,
											FM_FILE_SEMANTIC_MIETVERTRAGANLAGE 				=> File::FM_FILE_TYPE_PDF | File::FM_FILE_TYPE_RTF,
											FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG 			=> File::FM_FILE_TYPE_PDF | File::FM_FILE_TYPE_RTF,
											FM_FILE_SEMANTIC_RSSCHREIBEN 					=> File::FM_FILE_TYPE_PDF | File::FM_FILE_TYPE_RTF,
											FM_FILE_SEMANTIC_DATEIFUERKUNDE 				=> File::FM_FILE_TYPE_PDF | File::FM_FILE_TYPE_RTF,
											FM_FILE_SEMANTIC_ANTWORTSCHREIBEN 				=> File::FM_FILE_TYPE_PDF,
											FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT => File::FM_FILE_TYPE_PDF,
											FM_FILE_SEMANTIC_NACHTRAG 						=> File::FM_FILE_TYPE_PDF,
											FM_FILE_SEMANTIC_SONSTIGES 						=> File::FM_FILE_TYPE_PDF | File::FM_FILE_TYPE_RTF | File::FM_FILE_TYPE_EXCEL,
											FM_FILE_SEMANTIC_TEILABRECHNUNG 				=> File::FM_FILE_TYPE_PDF,
											FM_FILE_SEMANTIC_COMMENT						=> File::FM_FILE_TYPE_PDF | File::FM_FILE_TYPE_RTF | File::FM_FILE_TYPE_EXCEL,
											FM_FILE_SEMANTIC_TEMP							=> File::FM_FILE_TYPE_PDF | File::FM_FILE_TYPE_RTF | File::FM_FILE_TYPE_EXCEL,
											FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE 			=> File::FM_FILE_TYPE_PDF,
											FM_FILE_SEMANTIC_WIDERSPRUCH					=> File::FM_FILE_TYPE_PDF,
											FM_FILE_SEMANTIC_WSANSCHREIBEN					=> File::FM_FILE_TYPE_RTF,
											FM_FILE_SEMANTIC_PROTOCOL						=> File::FM_FILE_TYPE_PDF | File::FM_FILE_TYPE_RTF | File::FM_FILE_TYPE_EXCEL,
											FM_FILE_SEMANTIC_NEWCUSTOMERFILE				=> File::FM_FILE_TYPE_PDF | File::FM_FILE_TYPE_RTF | File::FM_FILE_TYPE_EXCEL,
											FM_FILE_SEMANTIC_WIDERSPRUCH_RECHNUNG			=> File::FM_FILE_TYPE_PDF,
											FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE				=> File::FM_FILE_TYPE_PDF | File::FM_FILE_TYPE_RTF | File::FM_FILE_TYPE_EXCEL,
											FM_FILE_SEMANTIC_STAMMDATENBLATT				=> File::FM_FILE_TYPE_PDF,
											FM_FILE_SEMANTIC_KONDITIONSUNDFRISTENLISTE		=> File::FM_FILE_TYPE_EXCEL,
										);

// Zuordnung der maximalen Dateigröße in Byte zu den jeweiligen Semantiken
$FM_MAX_FILESIZE_FOR_SEMANTIC = Array( 	FM_FILE_SEMANTIC_UNKNOWN						=> 0,
										FM_FILE_SEMANTIC_MIETVERTRAG 					=> 35*1024*1024,
										FM_FILE_SEMANTIC_MIETVERTRAGANLAGE 				=> 35*1024*1024,
										FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG 			=> 35*1024*1024,
										FM_FILE_SEMANTIC_RSSCHREIBEN 					=> 35*1024*1024,
										FM_FILE_SEMANTIC_DATEIFUERKUNDE 				=> 35*1024*1024,
										FM_FILE_SEMANTIC_ANTWORTSCHREIBEN 				=> 35*1024*1024,
										FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT => 35*1024*1024,
										FM_FILE_SEMANTIC_NACHTRAG 						=> 35*1024*1024,
										FM_FILE_SEMANTIC_SONSTIGES 						=> 35*1024*1024,
										FM_FILE_SEMANTIC_TEILABRECHNUNG 				=> 35*1024*1024,
										FM_FILE_SEMANTIC_COMMENT						=> 35*1024*1024,
										FM_FILE_SEMANTIC_TEMP							=> 35*1024*1024,
										FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE			=> 35*1024*1024,
										FM_FILE_SEMANTIC_WIDERSPRUCH					=> 15*1024*1024,
										FM_FILE_SEMANTIC_WSANSCHREIBEN					=> 35*1024*1024,
										FM_FILE_SEMANTIC_PROTOCOL						=> 35*1024*1024,
										FM_FILE_SEMANTIC_NEWCUSTOMERFILE				=> 15*1024*1024,
										FM_FILE_SEMANTIC_WIDERSPRUCH_RECHNUNG			=> 15*1024*1024,
										FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE				=> 15*1024*1024,
										FM_FILE_SEMANTIC_STAMMDATENBLATT				=> 10*1024*1024,
										FM_FILE_SEMANTIC_KONDITIONSUNDFRISTENLISTE		=> 10*1024*1024,
									);

$FM_DESCRIPTIONS_FOR_SEMANTIC = Array(	FM_FILE_SEMANTIC_UNKNOWN						=> Array("short" => "??", "long" => "Unbekannt"),
										FM_FILE_SEMANTIC_MIETVERTRAG 					=> Array("short" => "VG", "long" => "Vertrag"),
										FM_FILE_SEMANTIC_MIETVERTRAGANLAGE 				=> Array("short" => "VG", "long" => "Anlage"),
										FM_FILE_SEMANTIC_MIETVERTRAGNACHTRAG 			=> Array("short" => "VG", "long" => "Nachtrag"),
										FM_FILE_SEMANTIC_RSSCHREIBEN 					=> Array("short" => "FMS", "long" => "%SUB%"),
										FM_FILE_SEMANTIC_DATEIFUERKUNDE 				=> Array("short" => "XXX", "long" => "Datei für Kunde"),
										FM_FILE_SEMANTIC_ANTWORTSCHREIBEN 				=> Array("short" => "AW", "long" => "Antwortschreiben"),
										FM_FILE_SEMANTIC_ABRECHNUNGSKORREKTURGUTSCHRIFT => Array("short" => "AW", "long" => "Abrechnungskorrektur / Gutschrift"),
										FM_FILE_SEMANTIC_NACHTRAG 						=> Array("short" => "AW", "long" => "Nachtrag"),
										FM_FILE_SEMANTIC_SONSTIGES 						=> Array("short" => "AW", "long" => "Sonstiges"),
										FM_FILE_SEMANTIC_TEILABRECHNUNG 				=> Array("short" => "NK", "long" => "Nebenkostenabrechnung"),
										FM_FILE_SEMANTIC_COMMENT						=> Array("short" => "KOM", "long" => "Kommentar"),
										FM_FILE_SEMANTIC_TEMP							=> Array("short" => "TMP", "long" => "Temporäre Datei"),
										FM_FILE_SEMANTIC_WIDERSPRUCH_SONSTIGE			=> Array("short" => "WS", "long" => "Anhang"),
										FM_FILE_SEMANTIC_WIDERSPRUCH					=> Array("short" => "WS", "long" => "Widerspruch"),
										FM_FILE_SEMANTIC_WSANSCHREIBEN					=> Array("short" => "WSV", "long" => "Vorlage für Widerspruchanschrieben"),
										FM_FILE_SEMANTIC_PROTOCOL						=> Array("short" => "PR", "long" => "Protokoll"),
										FM_FILE_SEMANTIC_NEWCUSTOMERFILE				=> Array("short" => "UD", "long" => "Unklassifizierte Datei"),
										FM_FILE_SEMANTIC_WIDERSPRUCH_RECHNUNG			=> Array("short" => "WS", "long" => "Rechnung"),
										FM_FILE_SEMANTIC_PROTOCOL_SONSTIGE				=> Array("short" => "PR", "long" => "Anhang"),
										FM_FILE_SEMANTIC_STAMMDATENBLATT				=> Array("short" => "SD", "long" => "Stammdatenblatt"),
										FM_FILE_SEMANTIC_KONDITIONSUNDFRISTENLISTE		=> Array("short" => "KF", "long" => "Konditions- und Fristenliste"),
									);

define("FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_SONSTIGES", 0);
define("FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_AKTENNOTIZ", 1);
define("FM_FILE_SEMANTIC_RSSCHREIBEN_SUBTYPE_PROTOKOLL", 2);

?>