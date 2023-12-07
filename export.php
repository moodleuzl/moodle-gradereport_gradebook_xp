<?php
require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once('./lib.php');
require_once('./db_controller.php');

// Get required and optional parameters
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

$records = $DB->get_records_sql('SELECT * FROM {gradereport_gradebook_xp_com}');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="export.csv"');

$output = fopen('php://output', 'w');

// Determine the headers
$headers = array('column1', 'column2', 'column3'); // Replace with actual column names

// Write the headers to the CSV
fputcsv($output, $headers);

// Write each record to the CSV
foreach ($records as $record) {
    fputcsv($output, (array)$record);
}

fclose($output);
exit;
