<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once('./lib.php');
require_once('./db_controller.php');

// Get required and optional parameters
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

// Verify course ID validity
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    throw new moodle_exception('invalidcourseid');
}

// Ensure user is logged in and has access to the course
require_login($course);
$context = context_course::instance($course->id);

// Define table names for competencies and connections
$table_com = 'gradereport_gradebook_xp_com';
$table_con = 'gradereport_gradebook_xp_con';

// Generate CSV files for competencies and connections
$table1_csv = generate_csv($table_com, sys_get_temp_dir() . '/' . $table_com . '.csv');
$table2_csv = generate_csv($table_con, sys_get_temp_dir() . '/' . $table_con . '.csv');

// Create a zip archive
$zip = new ZipArchive();

// Specify temporary directory and name for the zip file
$temp_zip_file = sys_get_temp_dir() . '/exported_data.zip';
$zip->open($temp_zip_file, ZipArchive::CREATE);

// Add CSV files to the zip archive
$zip->addFile($table1_csv, $table_com . '.csv');
$zip->addFile($table2_csv, $table_con . '.csv');

// Close the zip archive
if ($zip->close() !== true) {
    die("Failed to close zip archive");
}

// Define filename for the zip file
$filename = $courseid . "_gradebook_xp.zip";

// Serve the zip file to the user
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");
readfile($temp_zip_file);

// Delete temporary CSV files and the zip file
unlink($table1_csv);
unlink($table2_csv);
unlink($temp_zip_file);

/**
 * Generate a CSV file for a given database table.
 *
 * @param string $tablename The name of the database table.
 * @param string $filename The filename to save the CSV as.
 * @return string The filename of the generated CSV file.
 * @throws dml_exception
 */
function generate_csv($tablename, $filename) {
    global $DB;

    // Open the output file
    $output = fopen($filename, 'w');

    // Extract column names from the database table
    $table_columns = $DB->get_columns($tablename);
    $headers = array();
    foreach ($table_columns as $column) {
        $headers[] = $column->name;
    }

    // Write column headers to the CSV
    fputcsv($output, $headers);

    // Write each record to the CSV
    $records = $DB->get_records($tablename);
    foreach ($records as $record) {
        fputcsv($output, (array) $record);
    }

    // Close the output file
    fclose($output);
    return $filename;
}

// Terminate script execution
exit;