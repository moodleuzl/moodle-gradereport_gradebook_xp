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

//$records = $DB->get_records_sql('SELECT * FROM {gradereport_gradebook_xp_com}');
$records = $DB->get_records('gradereport_gradebook_xp_com');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="export.csv"');

$output = fopen('php://output', 'w');

// Extract column names
$table_columns = $DB->get_columns('gradereport_gradebook_xp_com');
$headers = array();
foreach ($table_columns as $column) {
    $headers[] = $column->name;
}

// Write the headers to the CSV
fputcsv($output, $headers);

// Write each record to the CSV
foreach ($records as $record) {
    fputcsv($output, (array)$record);
}

fclose($output);
exit;
