<?php
require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once('./lib.php');
require_once('./db_controller.php');
require_once 'import_form.php';

// Get required parameter
$courseid = required_param('id', PARAM_INT);

// Set up the page
gradereport_gradebook_xp_admin_setup_page($courseid, 'moodle/grade:manage');
$PAGE->navbar->add(get_string('preferences'));

// Instantiate import_form
$mform = new import_form();

if ($mform->is_cancelled()) {
    // Handle form cancellation.
} else if ($data = $mform->get_data()) {
    $zip_content = $mform->get_file_content('userfile');
    $name = $mform->get_new_filename('userfile');
    echo "<script>alert('$name');</script>";

    // Write the zip content to a temporary file
    $zip_file = sys_get_temp_dir() . '/imported_data.zip';
    file_put_contents($zip_file, $zip_content);

    // Open the zip archive
    $zip = new ZipArchive;
    if ($zip->open($zip_file) === TRUE) {
        // Extract each CSV file from the zip archive
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $csv_data = $zip->getFromIndex($i);

            // Determine the table name from the filename
            $table_name = basename($filename, '.csv');

            // Check if the table name matches the expected ones
            if ($table_name == 'gradereport_gradebook_xp_com' || $table_name == 'gradereport_gradebook_xp_con') {
                // Process the CSV data
                process_csv_data($courseid, $table_name, $csv_data);
            } else {
                echo "Skipping file $filename as it doesn't match the expected format.<br>";
            }
        }
        $zip->close();
        echo 'Import successful.';
    } else {
        echo 'Failed to open the zip file.';
    }
}

// Add heading to navbar
$PAGE->navbar->add('Import');

// Print header
print_grade_page_head($courseid, 'settings', 'gradebook_xp_admin', 'Import', false, false, false);

// displays the form
$mform->display();

// Print footer
echo $OUTPUT->footer();

// Function to process CSV data
function process_csv_data($courseid, $table_name, $csv_data) {
    global $DB;

    // Parse the CSV data
    $rows = explode("\n", $csv_data);
    $headers = str_getcsv(array_shift($rows));

    grade_report_gradebook_xp_admin_debug($rows);
    grade_report_gradebook_xp_admin_debug($headers);
    // Insert new data into the table
    foreach ($rows as $row) {
        // Skip empty rows
        if (!empty($row)) {
            $values = str_getcsv($row);
            grade_report_gradebook_xp_admin_debug($values);
            $record = array_combine($headers, $values);
            $record['courseid'] = $courseid; // Set 'courseid' for each record

            // Check if a record with the same key exists in the database
            $existing_record = $DB->get_record($table_name, array('id' => $record['id']));
            if ($existing_record) {
                // Update the existing record
                $DB->update_record($table_name, (object)$record);
            } else {
                // Insert a new record
                $DB->insert_record($table_name, (object)$record);
            }
        }
    }
}