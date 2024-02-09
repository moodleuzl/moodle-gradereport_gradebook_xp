<?php
require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once('./lib.php');
require_once('./db_controller.php');

// Get required and optional parameters
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($course);
$context = context_course::instance($course->id);

// Generate CSV file, get the filename returned by generate_csv function
$table1_csv = generate_csv('gradereport_gradebook_xp_com', sys_get_temp_dir() . '/test1.csv');
$table2_csv = generate_csv('gradereport_gradebook_xp_con', sys_get_temp_dir() . '/test2.csv');

// Create a zip archive
$zip = new ZipArchive();

// Specify a custom directory and name for the temporary zip file
$temp_zip_file = sys_get_temp_dir() . '/exported_data.zip';
$zip->open($temp_zip_file, ZipArchive::CREATE);

// Add CSV file to the zip archive
$zip->addFile($table1_csv, 'table1.csv');;
$zip->addFile($table2_csv, 'table2.csv');


// Close the zip archive
if ($zip->close() !== TRUE) {
    die("Failed to close zip archive");
}

// Serve the zip file to the user
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=$temp_zip_file");
header("Pragma: no-cache");
header("Expires: 0");
readfile($temp_zip_file);

// Delete the temporary CSV file and the zip file
unlink($table1_csv);
unlink($table2_csv);
unlink($temp_zip_file);


// Function to generate CSV file
/**
 * @throws dml_exception
 */
function generate_csv($tablename, $filename) {
    global $DB;

    $output = fopen($filename, 'w');

    // Extract column names
    $table_columns = $DB->get_columns($tablename);
    $headers = array();
    foreach ($table_columns as $column) {
        $headers[] = $column->name;
    }

    // Write the headers to the CSV
    fputcsv($output, $headers);

    // Write each record to the CSV
    $records = $DB->get_records($tablename);
    foreach ($records as $record) {
        fputcsv($output, (array)$record);
    }

    fclose($output);
    return $filename;
}

exit;
