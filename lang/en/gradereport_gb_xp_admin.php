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
 * Strings for component 'gb_xp_admin', language 'en'
 *
 * @package gradereport_gb_xp_admin
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Gradebook XP Admin';
$string['newcompetency'] = 'New competency';
$string['editcompetency'] = 'Edit competency';
$string['deletecompetency'] = 'Delete competency';
$string['id'] = 'ID';
$string['name'] = 'Competency name';
$string['description'] = 'Competency description';
$string['level'] = 'Competency level';
$string['connections'] = 'Connections';
$string['activities'] = 'Activities';
$string['assignments'] = 'Assignments';
$string['quizzes'] = 'Quizzes';
$string['vpls'] = 'VPLs';
$string['saveconnection'] = 'Save connection';
$string['parent'] = 'Parent';
$string['missingname'] = 'Missing name. Please enter a valid name.';
$string['managecompetencies'] = 'Manage Competencies';
$string['goback'] = "Go back";
$string['listofcompetencies'] = "List of competencies";
$string['listofconnections'] = "List of connections";
$string['addcompetency'] = "Add competency";
$string['privacy:metadata'] = 'The Gradebook XP plugin does not store any personal data.';
$string['export'] = 'Export';
$string['import'] = 'Import';
$string['maxcomlvl'] = 'Max Competency Level';
$string['levelcalcmethod'] = 'Competency level calculation method';
$string['usemax'] = 'Use maximum level';
$string['usesum'] = 'Sum all levels';
$string['nonNumericError'] = 'Invalid input. Please enter only numbers.';
$string['strexceedslimit255'] = 'Please only enter up to 255 Characters.';
$string['strexceedslimit100'] = 'Please only enter up to 100 Characters.';
$string['strupto999'] = 'Please enter a number from 1 to 999.';
$string['missinginput'] = 'This field is required. Please do not leave it empty.';
$string['cancelcompetency'] = 'You cancelled the competency form.';
$string['createcompetencysuccess'] = 'You have successfully created the competency: ';
$string['updatecompetencysuccess'] = 'You have successfully updated the competency: ';
$string['importcompetencies'] = 'Import competencies';
$string['importconnections'] = 'Import connections';
$string['deletecompetencies'] = 'Delete all existing competencies before importing';
$string['deleteconnections'] = 'Delete all existing connections before importing';
$string['overwriteexisting'] = 'Overwrite existing competencies';
$string['overwriteexistingconnections'] = 'Overwrite existing connections';
$string['file'] = 'File to import';
$string['missingfile'] = 'Missing file. Please upload a valid .zip file.';
$string['cancelimport'] = 'You cancelled the import form.';
$string['importsuccess'] = 'You have successfully imported the plugin data for this course.';
$string['allusers'] = 'All users';
$string['selectauser'] = 'Select a user';
$string['viewinguser'] = 'Viewing user: {$a}';
$string['nocompetencies'] = 'No competencies have been defined for this course.';

// UI Actions.
$string['cancel'] = 'Cancel';
$string['close'] = 'Close';
$string['create'] = 'Create';
$string['update'] = 'Update';
$string['save'] = 'Save';
$string['delete'] = 'Delete';
$string['edit'] = 'Edit';
$string['search'] = 'Search';
$string['remove'] = 'Remove';

// Competency Management.
$string['children'] = 'Children';
$string['addsubcompetency'] = 'Add Sub Competency';
$string['editselectedcompetency'] = 'Edit selected competency';
$string['deleteselectedcompetency'] = 'Delete selected competency';

// Toast Messages.
$string['dataloaded'] = 'Loaded Gradebook XP data';
$string['dataloadfailed'] = 'Failed to load Gradebook XP data';
$string['competencycreated'] = 'Created competency "{$a}"';
$string['competencyupdated'] = 'Updated competency "{$a}"';
$string['competencydeleted'] = 'Deleted competency "{$a}"';
$string['competencycreatefailed'] = 'Failed to create competency';
$string['competencyupdatefailed'] = 'Failed to update competency';
$string['competencydeletefailed'] = 'Failed to delete competency';
$string['connectioncreated'] = 'Connected "{$a->competency}" to activity "{$a->activity}"';
$string['connectiondeleted'] = 'Disconnected "{$a->competency}" from activity "{$a->activity}"';
$string['connectioncreatefailed'] = 'Failed to create connection';
$string['connectiondeletefailed'] = 'Failed to delete connection';
$string['relationcreated'] = 'Created competency relation';
$string['relationdeleted'] = 'Deleted competency relation';
$string['relationcreatefailed'] = 'Failed to create relation';
$string['relationdeletefailed'] = 'Failed to delete relation';

// Relations.
$string['addparents'] = 'Add Parents';
$string['addchildren'] = 'Add Children';
$string['addactivities'] = 'Add Activities';
$string['noparentcompetencies'] = 'No parent competencies';
$string['nochildcompetencies'] = 'No child competencies';
$string['noconnectedactivities'] = 'No connected activities';
$string['selectcompetencies'] = 'Select competencies to add as {$a}:';
$string['selectactivities'] = 'Select activities to connect:';
$string['searchcompetencies'] = 'Search competencies...';
$string['searchactivities'] = 'Search activities...';
$string['addselected'] = 'Add Selected';
$string['noitemsfound'] = 'No items found';

// Confirm Dialog.
$string['confirmdelete'] = 'Confirm Delete';
$string['confirmdeletemessage'] = 'Are you sure you want to delete the competency "{$a}"? This action cannot be undone.';
$string['confirmaction'] = 'Confirm';

// Breadcrumb.
$string['root'] = 'Root';

// List Items.
$string['maxlevel'] = 'Max Level';
$string['levelsummed'] = 'Level Summed';

// Competency Form.
$string['createandedit'] = 'Create and Edit';
$string['saving'] = 'Saving...';
$string['creating'] = 'Creating...';
$string['updating'] = 'Updating...';
$string['islevelsummed'] = 'Is Level Summed';
$string['nochildcompetenciesfound'] = 'No child competencies found for "{$a}".';

// Search.
$string['searchplaceholder'] = 'Search competencies';
$string['mincharacters'] = 'min 3 characters';

// Export.
$string['exportdata'] = 'Export';
$string['exportalldata'] = 'Export all data as JSON';

// Loading.
$string['loadingcompetencies'] = 'Loading competencies...';

// Empty States.
$string['nocompetenciesfound'] = 'No competencies found';
$string['addfirstcompetency'] = 'Add Your First Competency';

$string['chart_series_label_user'] = 'You';
$string['chart_competencies_max_level'] = 'Maximum Level';
$string['chart_series_label_success'] = 'Success';
$string['chart_series_label_average'] = 'Average';
$string['missing_data'] = 'No data available.';
$string['allusersnum'] = 'All users ({$a})';