<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Strings for component 'gradebook_xp', language 'de'
 *
 * @package gradereport_gradebook_xp
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Gradebook XP';
$string['newcompetency'] = 'Neue Kompetenz';
$string['editcompetency'] = 'Kompetenz bearbeiten';
$string['deletecompetency'] = 'Kompetenz löschen';
$string['id'] = 'ID';
$string['name'] = 'Kompetenzname';
$string['description'] = 'Kompetenzbeschreibung';
$string['level'] = 'Fortschrittsbeitrag';
$string['connections'] = 'Verbindungen';
$string['activities'] = 'Bewertungselemente';
$string['assignments'] = 'Aufgaben';
$string['quizzes'] = 'Tests';
$string['vpls'] = 'VPLs';
$string['saveconnection'] = 'Verbindung speichern';
$string['parent'] = 'Übergeordnet';
$string['missingname'] = 'Name fehlt. Bitte geben Sie einen gültigen Namen ein.';
$string['managecompetencies'] = 'Kompetenzen verwalten';
$string['goback'] = 'Zurück';
$string['listofcompetencies'] = 'Liste der Kompetenzen';
$string['listofconnections'] = 'Liste der Verbindungen';
$string['addcompetency'] = 'Kompetenz hinzufügen';
$string['privacy:metadata'] = 'Das Gradebook XP Plugin speichert keine persönlichen Daten.';
$string['export'] = 'Exportieren';
$string['import'] = 'Importieren';
$string['maxcomlvl'] = 'Maximaler Fortschrittswert';
$string['targetcomlvl'] = 'Zielwert zum Erreichen des Lernziels';
$string['levelcalcmethod'] = 'Berechnungsart';
$string['usemax'] = 'Höchsten erreichten Fortschrittsbeitrag verwenden';
$string['usesum'] = 'Fortschrittsbeiträge addieren';
$string['maxcontributionerror'] = 'Der maximale Fortschrittswert muss mindestens {$a} betragen.';
$string['targetvalueerror'] = 'Der Lernziel-Zielwert muss zwischen 1 und dem maximalen Fortschrittswert liegen.';
$string['manualgradeitem'] = 'Manuelles Bewertungselement';
$string['nopassgrade'] = 'Keine Bestehensgrenze';
$string['nonNumericError'] = 'Ungültige Eingabe. Bitte geben Sie nur Zahlen ein.';
$string['strexceedslimit255'] = 'Bitte geben Sie maximal 255 Zeichen ein.';
$string['strexceedslimit100'] = 'Bitte geben Sie maximal 100 Zeichen ein.';
$string['strupto999'] = 'Bitte geben Sie eine Zahl von 1 bis 999 ein.';
$string['missinginput'] = 'Dieses Feld ist erforderlich. Bitte lassen Sie es nicht leer.';
$string['cancelcompetency'] = 'Sie haben das Kompetenzformular abgebrochen.';
$string['createcompetencysuccess'] = 'Sie haben die Kompetenz erfolgreich erstellt: ';
$string['updatecompetencysuccess'] = 'Sie haben die Kompetenz erfolgreich aktualisiert: ';
$string['importcompetencies'] = 'Kompetenzen importieren';
$string['importconnections'] = 'Verbindungen importieren';
$string['deletecompetencies'] = 'Alle vorhandenen Kompetenzen vor dem Import löschen';
$string['deleteconnections'] = 'Alle vorhandenen Verbindungen vor dem Import löschen';
$string['overwriteexisting'] = 'Vorhandene Kompetenzen überschreiben';
$string['overwriteexistingconnections'] = 'Vorhandene Verbindungen überschreiben';
$string['file'] = 'Zu importierende Datei';
$string['missingfile'] = 'Fehlende Datei. Bitte laden Sie eine gültige .zip-Datei hoch.';
$string['cancelimport'] = 'Sie haben das Kompetenzformular abgebrochen.';
$string['importsuccess'] = 'Sie haben die Plugin-Daten erfolgreich importiert.';
$string['allusers'] = 'Alle Benutzer';
$string['selectauser'] = 'Benutzer auswählen';
$string['viewinguser'] = 'Benutzer anzeigen: {$a}';
$string['nocompetencies'] = 'Für diesen Kurs wurden keine Kompetenzen definiert.';

// UI Actions.
$string['cancel'] = 'Abbrechen';
$string['close'] = 'Schließen';
$string['create'] = 'Erstellen';
$string['update'] = 'Aktualisieren';
$string['save'] = 'Speichern';
$string['delete'] = 'Löschen';
$string['edit'] = 'Bearbeiten';
$string['search'] = 'Suchen';
$string['remove'] = 'Entfernen';

// Competency Management.
$string['children'] = 'Untergeordnete';
$string['addsubcompetency'] = 'Unterkompetenz hinzufügen';
$string['editselectedcompetency'] = 'Ausgewählte Kompetenz bearbeiten';
$string['deleteselectedcompetency'] = 'Ausgewählte Kompetenz löschen';

// Toast Messages.
$string['dataloaded'] = 'Gradebook XP Daten geladen';
$string['dataloadfailed'] = 'Fehler beim Laden der Gradebook XP Daten';
$string['competencycreated'] = 'Kompetenz "{$a}" erstellt';
$string['competencyupdated'] = 'Kompetenz "{$a}" aktualisiert';
$string['competencydeleted'] = 'Kompetenz "{$a}" gelöscht';
$string['competencycreatefailed'] = 'Fehler beim Erstellen der Kompetenz';
$string['competencyupdatefailed'] = 'Fehler beim Aktualisieren der Kompetenz';
$string['competencydeletefailed'] = 'Fehler beim Löschen der Kompetenz';
$string['connectioncreated'] = '"{$a->competency}" mit Aktivität "{$a->activity}" verbunden';
$string['connectiondeleted'] = '"{$a->competency}" von Aktivität "{$a->activity}" getrennt';
$string['connectioncreatefailed'] = 'Fehler beim Erstellen der Verbindung';
$string['connectiondeletefailed'] = 'Fehler beim Löschen der Verbindung';
$string['relationcreated'] = 'Kompetenzrelation erstellt';
$string['relationdeleted'] = 'Kompetenzrelation gelöscht';
$string['relationcreatefailed'] = 'Fehler beim Erstellen der Relation';
$string['relationdeletefailed'] = 'Fehler beim Löschen der Relation';

// Relations.
$string['addparents'] = 'Übergeordnete hinzufügen';
$string['addchildren'] = 'Untergeordnete hinzufügen';
$string['addactivities'] = 'Bewertungselemente hinzufügen';
$string['noparentcompetencies'] = 'Keine übergeordneten Kompetenzen';
$string['nochildcompetencies'] = 'Keine untergeordneten Kompetenzen';
$string['noconnectedactivities'] = 'Keine verbundenen Bewertungselemente';
$string['selectcompetencies'] = 'Kompetenzen auswählen, um sie als {$a} hinzuzufügen:';
$string['selectactivities'] = 'Bewertungselemente zum Verbinden auswählen:';
$string['searchcompetencies'] = 'Kompetenzen suchen...';
$string['searchactivities'] = 'Bewertungselemente suchen...';
$string['addselected'] = 'Ausgewählte hinzufügen';
$string['noitemsfound'] = 'Keine Einträge gefunden';

// Confirm Dialog.
$string['confirmdelete'] = 'Löschen bestätigen';
$string['confirmdeletemessage'] = 'Möchten Sie die Kompetenz "{$a}" wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.';
$string['confirmaction'] = 'Bestätigen';

// Breadcrumb.
$string['root'] = 'Wurzel';

// List Items.
$string['maxlevel'] = 'Max. Fortschrittswert';
$string['levelsummed'] = 'Fortschrittsbeiträge werden addiert';

// Competency Form.
$string['createandedit'] = 'Erstellen und Bearbeiten';
$string['saving'] = 'Speichern...';
$string['creating'] = 'Erstellen...';
$string['updating'] = 'Aktualisieren...';
$string['islevelsummed'] = 'Fortschrittsbeiträge werden addiert';
$string['nochildcompetenciesfound'] = 'Keine untergeordneten Kompetenzen für "{$a}" gefunden.';

// Search.
$string['searchplaceholder'] = 'Kompetenzen suchen';
$string['mincharacters'] = 'mind. 3 Zeichen';

// Export.
$string['exportdata'] = 'Exportieren';
$string['exportalldata'] = 'Alle Daten als JSON exportieren';
$string['importpreview'] = 'Importvorschau';
$string['importpreviewhelp'] = 'Bitte prüfen Sie die Zuordnung, bevor Daten in den Kurs geschrieben werden. Bereits vorhandene Lernziele gleichen Namens werden wiederverwendet.';
$string['importinvalidfile'] = 'Die Datei ist kein gültiger Gradebook-XP-JSON-Export.';
$string['importfailed'] = 'Der Import konnte nicht vollständig ausgeführt werden.';
$string['importing'] = 'Wird importiert …';
$string['importnewcompetencies'] = 'Neue Lernziele';
$string['importexistingcompetencies'] = 'Bereits vorhandene Lernziele';
$string['importrelations'] = 'Hierarchiebeziehungen';
$string['importmatchedconnections'] = 'Eindeutig zugeordnete Bewertungselemente';
$string['importambiguousconnections'] = 'Mehrdeutige Bewertungselemente';
$string['importmissingconnections'] = 'Nicht gefundene Bewertungselemente';
$string['importunmatchedwarning'] = 'Mehrdeutige und nicht gefundene Bewertungselemente werden ausgelassen. Alte Aktivitäts-IDs werden niemals direkt übernommen.';
$string['importcreatedcompetencies'] = 'Erstellte Lernziele';
$string['importcreatedrelations'] = 'Erstellte Hierarchiebeziehungen';
$string['importcreatedconnections'] = 'Erstellte Verbindungen';
$string['importskippedconnections'] = 'Ausgelassene Verbindungen';
$string['importconnectionfailures'] = 'Nicht erstellbare Verbindungen';

// Loading.
$string['loadingcompetencies'] = 'Kompetenzen werden geladen...';

// Empty States.
$string['nocompetenciesfound'] = 'Keine Kompetenzen gefunden';
$string['addfirstcompetency'] = 'Fügen Sie Ihre erste Kompetenz hinzu';

$string['chart_series_label_user'] = 'Sie';
$string['chart_series_label_target'] = 'Lernziel';
$string['chart_competencies_max_level'] = 'Maximaler Fortschrittswert';
$string['chart_series_label_success'] = 'Erfolg';
$string['chart_series_label_average'] = 'Durchschnitt';
$string['missing_data'] = 'Keine Daten verfügbar.';
$string['allusersnum'] = 'Alle Teilnehmer ({$a})';

// Index page strings.
$string['notenoughcompetencies'] = 'Nicht genügend Kompetenzen ({$a} von 3) um das Diagramm anzuzeigen.';
$string['backtotoplevel'] = 'Zurück zur obersten Ebene';
$string['onelevelup'] = 'Eine Ebene nach oben';
$string['sumallactivitylevels'] = '(Fortschrittsbeiträge addiert)';
$string['highestsingleactivitylevel'] = '(Höchster erreichter Fortschrittsbeitrag)';
$string['yourlevel'] = 'Ihr Fortschrittswert:';
$string['maximumlevel_display'] = 'Maximaler Fortschrittswert:';
$string['targetlevel_display'] = 'Lernziel:';
$string['levelcolon'] = 'Fortschrittsbeitrag:';
