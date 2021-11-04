<?php
require_once 'WiderspruchsGenerator-1.0/WiderspruchsGenerator.lib.php5';
// Konfiguratuion des Prozess-Ablaufs 
$processStatusConfig = Array();
// ACHTUNG: Die StatusID muss nicht dem ArrayIndex entsprechen!! Immer die Funktion WorkflowManager::GetProzessStatusForStatusID($statusID) verwenden um an die Infos der jeweiligen Status ID zu gelangen!
// ACHTUNG: Für jede Status ID muss in der Klasse ProcessFormData entsprechend eine Funktion 'InitElementsForStatus_X' und 'StoreForStatus_X' implementiert werden (mit X=Status ID)!
// Array-Aufbau je Status: StatusID, Nachfolge StatusIDs, Verantwortliche Gruppe, Name Status FMS, Name Status Kunde
$processStatusConfig[] = Array( "ID" => 0, "nextStatusIDs" => Array(29), "responsible" => Array(UM_GROUP_BASETYPE_KUNDE, UM_GROUP_BASETYPE_RSMITARBEITER, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT), "forbitProcessSwitching" => true, "name" => "Prüfung beauftragen", "nameCustomer" => "Prüfung beauftragen", "class" => "NkasPruefungBeauftragen");
$processStatusConfig[] = Array( "ID" => 29, "nextStatusIDs" => Array(1, 2), "responsible" => Array(UM_GROUP_BASETYPE_KUNDE, UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT, UM_GROUP_BASETYPE_AUSHILFE), "forbitProcessSwitching" => true, "name" => "Vertrag auf Vollständigkeit prüfen", "nameCustomer" => "Vertrag auf Vollständigkeit prüfen", "class" => "NkasVertragPruefen");
$processStatusConfig[] = Array( "ID" => 1, "nextStatusIDs" => Array(2), "responsible" => Array(UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT, UM_GROUP_BASETYPE_AUSHILFE), "forbitProcessSwitching" => true, "name" => "Vertrag erfassen", "nameCustomer" => "Vertrag erfassen", "class" => "NkasVertragErfassen");
$processStatusConfig[] = Array( "ID" => 2, "nextStatusIDs" => Array(3), "responsible" => Array(UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT, UM_GROUP_BASETYPE_AUSHILFE), "forbitProcessSwitching" => true, "name" => "Teilabrechnung erfassen", "nameCustomer" => "Teilabrechnung erfassen", "class" => "NkasTeilabrechnungErfassen");
$processStatusConfig[] = Array( "ID" => 3, "nextStatusIDs" => Array(36, 0), "responsible" => Array(UM_GROUP_BASETYPE_AUSHILFE_ERWEITERT, UM_GROUP_BASETYPE_AUSHILFE), "forbitProcessSwitching" => true, "name" => "Teilabrechnungspositionen erfassen", "nameCustomer" => "Teilabrechnungspositionen erfassen", "class" => "NkasTeilabrechnungspositionenErfassen");

$processStatusConfig[] = Array( "ID" => 36, "nextStatusIDs" => Array(4, 3), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "forbitProcessSwitching" => true, "name" => "Vertrags- und Nebenkostenerfassung durch SFM freigeben", "nameCustomer" => "Vertrags- und Nebenkostenerfassung durch SFM freigeben", "class" => "NkasDatenerfassungFreigabeFMS");

$processStatusConfig[] = Array( "ID" => 4, "nextStatusIDs" => Array(8, 5, 30, 9, 31, 20), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Widerspruch/Protokoll erzeugen", "nameCustomer" => "Widerspruch/Protokoll erzeugen", "class" => "NkasWiderspruchErzeugen", "allowCreateProcessGroup" => true);
$processStatusConfig[] = Array( "ID" => 5, "nextStatusIDs" => Array(35), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Abbrechen", "nameCustomer" => "Abbrechen", "class" => "NkasAbbrechen");
$processStatusConfig[] = Array( "ID" => 7, "nextStatusIDs" => Array(7, 0), "responsible" => Array(UM_GROUP_BASETYPE_KUNDE, UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Prüfung abgeschlossen", "nameCustomer" => "Prüfung abgeschlossen", "class" => "NkasAbgebrochen");
$processStatusConfig[] = Array( "ID" => 8, "nextStatusIDs" => Array(20, 4), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Widerspruch durch SFM freigeben", "nameCustomer" => "Widerspruch durch SFM freigeben", "class" => "NkasWiderspruchFreigabeFMS");
$processStatusConfig[] = Array( "ID" => 20, "nextStatusIDs" => Array(21, 4), "responsible" => Array(UM_GROUP_BASETYPE_KUNDE), "name" => "Widerspruch durch Kunde freigeben", "nameCustomer" => "Widerspruch durch Kunde freigeben", "class" => "NkasWiderspruchFreigabeKunde");
$processStatusConfig[] = Array( "ID" => 21, "nextStatusIDs" => Array(17, 9), "responsible" => Array(UM_GROUP_BASETYPE_KUNDE), "name" => "Widerspruch drucken/versenden/hochladen", "nameCustomer" => "Widerspruch drucken/versenden/hochladen", "class" => "NkasWiderspruchDruckenUndVersenden");
$processStatusConfig[] = Array( "ID" => 30, "nextStatusIDs" => Array(31, 4), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Protokoll durch SFM freigeben", "nameCustomer" => "Protokoll durch SFM freigeben", "class" => "NkasProtokollFreigabeFMS");
$processStatusConfig[] = Array( "ID" => 31, "nextStatusIDs" => Array(9), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Protokoll per Email versenden", "nameCustomer" => "Protokoll per Email versenden", "class" => "NkasProtokollVersenden");
$processStatusConfig[] = Array( "ID" => 9, "nextStatusIDs" => Array(16, 14, 4, 5, 24, 3, 11), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Nächste Maßnahme festlegen", "nameCustomer" => "Nächste Maßnahme festlegen", "class" => "NkasNaechsteMassnahme", "allowCreateProcessGroup" => true);
$processStatusConfig[] = Array( "ID" => 11, "nextStatusIDs" => Array(12, 13, 9), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "In Status Sonstiges überführen", "nameCustomer" => "In Status Sonstiges überführen", "class" => "NkasZurueckstellen");
$processStatusConfig[] = Array( "ID" => 12, "nextStatusIDs" => Array(9), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Sonstiges (SFM)", "nameCustomer" => "Sonstiges (SFM)", "class" => "NkasZurueckgestellt");
$processStatusConfig[] = Array( "ID" => 13, "nextStatusIDs" => Array(9), "responsible" => Array(UM_GROUP_BASETYPE_KUNDE), "name" => "Sonstiges (Kunde)", "nameCustomer" => "Sonstiges (Kunde)", "class" => "NkasZurueckgestellt");
$processStatusConfig[] = Array( "ID" => 14, "nextStatusIDs" => Array(34, 15, 4, 9), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Terminemail versenden", "nameCustomer" => "Terminemail versenden", "class" => "NkasGespraechsTerminVereinbaren");
$processStatusConfig[] = Array( "ID" => 34, "nextStatusIDs" => Array(15, 14, 4), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Terminemail durch SFM freigeben", "nameCustomer" => "Terminemail durch SFM freigeben", "class" => "NkasGespraechsTerminVereinbarenFreigabeFMS");
$processStatusConfig[] = Array( "ID" => 15, "nextStatusIDs" => Array(9, 14), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Gesprächstermin durchführen", "nameCustomer" => "Gesprächstermin durchführen", "class" => "NkasTerminDurchfuehren");
$processStatusConfig[] = Array( "ID" => 16, "nextStatusIDs" => Array(17, 17), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Eingangstermin Antwortschreiben vereinbaren", "nameCustomer" => "Eingangstermin Antwortschreiben vereinbaren", "class" => "NkasTerminEingangSchreibenVereinbaren");
$processStatusConfig[] = Array( "ID" => 17, "nextStatusIDs" => Array(18, 14), "responsible" => Array(UM_GROUP_BASETYPE_KUNDE), "name" => "Antwortschreiben Widerspruch/Protokoll hochladen", "nameCustomer" => "Antwortschreiben Widerspruch/Protokoll hochladen", "class" => "NkasSchreibenHochladen");
$processStatusConfig[] = Array( "ID" => 18, "nextStatusIDs" => Array(9), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Antwortschreiben klassifizieren", "nameCustomer" => "Antwortschreiben klassifizieren", "class" => "NkasSchreibenKlassifizieren");
$processStatusConfig[] = Array( "ID" => 24, "nextStatusIDs" => Array(25, 9), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Korrigierte Abrechnung/Gutschrift/Nachtrag prüfen", "nameCustomer" => "Korrigierte Abrechnung/Gutschrift/Nachtrag prüfen", "class" => "NkasAbrKorrPruefen");
$processStatusConfig[] = Array( "ID" => 25, "nextStatusIDs" => Array(27, 9, 35), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Realisierte Einsparung dokumentieren", "nameCustomer" => "Realisierte Einsparung dokumentieren", "class" => "NkasRealisierteEinsparungDokumentieren");
$processStatusConfig[] = Array( "ID" => 27, "nextStatusIDs" => Array(35), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Rechnung stellen", "nameCustomer" => "Rechnung stellen", "class" => "NkasRechnungStellen");
$processStatusConfig[] = Array( "ID" => 35, "nextStatusIDs" => Array(28, 9, 26), "responsible" => Array(UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Kunde informieren", "nameCustomer" => "Kunde informieren", "class" => "NkasKundeInformieren");
$processStatusConfig[] = Array( "ID" => 28, "nextStatusIDs" => Array(9, 33, 7), "responsible" => Array(UM_GROUP_BASETYPE_KUNDE), "name" => "Buchhaltung informieren", "nameCustomer" => "Buchhaltung informieren", "class" => "NkasBuchhaltungInformieren");
$processStatusConfig[] = Array( "ID" => 33, "nextStatusIDs" => Array(26), "responsible" => Array(UM_GROUP_BASETYPE_ACCOUNTSDEPARTMENT), "name" => "Zahlungseingang prüfen", "nameCustomer" => "Zahlungseingang prüfen", "class" => "NkasZahlungseingangPruefen");
$processStatusConfig[] = Array( "ID" => 26, "nextStatusIDs" => Array(26, 0), "responsible" => Array(UM_GROUP_BASETYPE_KUNDE, UM_GROUP_BASETYPE_RSMITARBEITER), "name" => "Abgeschlossen", "nameCustomer" => "Abgeschlossen", "class" => "NkasAbgeschlossen");


require_once 'StatusFormDataEntry.lib.php5';
require_once 'nkas/NkasStatusFormDataEntry.lib.php5';
require_once 'nkas/NkasPruefungBeauftragen.lib.php5';
require_once 'nkas/NkasVertragErfassen.lib.php5';
require_once 'nkas/NkasTeilabrechnungErfassen.lib.php5';
require_once 'nkas/NkasTeilabrechnungspositionenErfassen.lib.php5';
require_once 'nkas/NkasDatenerfassungFreigabeFMS.lib.php5';
require_once 'nkas/NkasWiderspruchErzeugen.lib.php5';
require_once 'nkas/NkasWiderspruchFreigabeFMS.lib.php5';
require_once 'nkas/NkasProtokollFreigabeFMS.lib.php5';
require_once 'nkas/NkasProtokollVersenden.lib.php5';
require_once 'nkas/NkasAbbrechen.lib.php5';
require_once 'nkas/NkasAbgebrochen.lib.php5';
require_once 'nkas/NkasNaechsteMassnahme.lib.php5';
require_once 'nkas/NkasZurueckstellen.lib.php5';
require_once 'nkas/NkasZurueckgestellt.lib.php5';
require_once 'nkas/NkasGespraechsTerminVereinbaren.lib.php5';
require_once 'nkas/NkasGespraechsTerminVereinbarenFreigabeFMS.lib.php5';
require_once 'nkas/NkasTerminDurchfuehren.lib.php5';
require_once 'nkas/NkasTerminEingangSchreibenVereinbaren.lib.php5';
require_once 'nkas/NkasSchreibenHochladen.lib.php5';
require_once 'nkas/NkasSchreibenKlassifizieren.lib.php5';
require_once 'nkas/NkasWiderspruchFreigabeKunde.lib.php5';
require_once 'nkas/NkasWiderspruchDruckenUndVersenden.lib.php5';
require_once 'nkas/NkasAbrKorrPruefen.lib.php5';
require_once 'nkas/NkasRealisierteEinsparungDokumentieren.lib.php5';
require_once 'nkas/NkasAbgeschlossen.lib.php5';
require_once 'nkas/NkasRechnungStellen.lib.php5';
require_once 'nkas/NkasBuchhaltungInformieren.lib.php5';
require_once 'nkas/NkasVertragPruefen.lib.php5';
require_once 'nkas/NkasZahlungseingangPruefen.lib.php5';
require_once 'nkas/NkasKundeInformieren.lib.php5';

?>