<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once('../../../config.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once('./lib.php');
require_once('./db_controller.php');

// Get required and optional parameters
// Get required and optional parameters.
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

// Verify course ID validity.
if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new moodle_exception('invalidcourseid');
}

// Ensure user is logged in and has access to the course.
require_login($course);
$context = context_course::instance($course->id);

// Define table names for competencies and connections.
$tablecom = 'gradereport_gradebook_xp_com';
$tablecon = 'gradereport_gradebook_xp_con';

// Generate CSV files for competencies and connections.
$table1csv = generate_csv($tablecom, sys_get_temp_dir() . '/' . $tablecom . '.csv');
$table2csv = generate_csv($tablecon, sys_get_temp_dir() . '/' . $tablecon . '.csv');

// Create a zip archive.
$zip = new ZipArchive();

// Specify temporary directory and name for the zip file.
$tempzipfile = sys_get_temp_dir() . '/exported_data.zip';
$zip->open($tempzipfile, ZipArchive::CREATE);

// Add CSV files to the zip archive.
$zip->addFile($table1csv, $tablecom . '.csv');
$zip->addFile($table2csv, $tablecon . '.csv');

// Close the zip archive.
if ($zip->close() !== true) {
    die("Failed to close zip archive");
}

// Define filename for the zip file.
$filename = $courseid . "_gradebook_xp.zip";

// Serve the zip file to the user.
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");
readfile($tempzipfile);

// Delete temporary CSV files and the zip file.
unlink($table1csv);
unlink($table2csv);
unlink($tempzipfile);

/**
 * Generate a CSV file for a given database table.
 *
 * @param string $tablename The name of the database table.
 * @param string $filename The filename to save the CSV as.
 * @return string The filename of the generated CSV file.
 * @throws dml_exception
 * @package gradereport_gradebook_xp_admin
 */
function generate_csv($tablename, $filename) {
    global $DB;

    // Open the output file.
    $output = fopen($filename, 'w');

    // Extract column names from the database table.
    $tablecolumns = $DB->get_columns($tablename);
    $headers = [];
    foreach ($tablecolumns as $column) {
        $headers[] = $column->name;
    }

    // Write column headers to the CSV.
    fputcsv($output, $headers);

    // Write each record to the CSV.
    $records = $DB->get_records($tablename);
    foreach ($records as $record) {
        fputcsv($output, (array) $record);
    }

    // Close the output file.
    fclose($output);
    return $filename;
}

// Terminate script execution.
exit;
