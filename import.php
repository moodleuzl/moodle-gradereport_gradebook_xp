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


/**
 * Process and import data into the specified tables in the database.
 *
 * This function parses CSV data for competencies and connections,
 * inserts them into their respective tables, and updates parent IDs
 * based on the mappings generated during insertion.
 *
 * @param int $courseid The ID of the course for which the data is being processed.
 * @param string $com_name The name of the competency table in the database.
 * @param string $com_table The CSV data for competencies.
 * @param string $con_name The name of the connection table in the database.
 * @param string $con_table The CSV data for connections.
 *
 * @throws dml_exception
 * @global moodle_database $DB The global database object.
 */
function process_data($courseid, $com_name, $com_table, $con_name, $con_table)
{
    global $DB;

    // Parse the CSV data for competencies
    $com_rows = explode("\n", $com_table);
    $com_headers = str_getcsv(array_shift($com_rows));

    // Initialize array to store old and new IDs mapping
    $mapping = [];

    // Iterate through each row in the CSV data for competencies
    foreach ($com_rows as $row) {
        // Skip empty rows
        if (!empty($row)) {
            // Extract values from the row
            $values = str_getcsv($row);
            $record = array_combine($com_headers, $values);

            // Store the old ID
            $old_id = $record['id'];

            // Set the course ID for the record
            $record['courseid'] = $courseid;

            // Insert a new record and store the new ID
            $new_id = (int)$DB->insert_record($com_name, (object)$record);

            // Store mapping of old and new IDs
            $mapping[$old_id] = $new_id;
        }
    }

    // Update the parent IDs for the new competency records
    foreach ($mapping as $old_key => $new_value) {
        // Get the record with the old ID from the database
        $existing_record = $DB->get_record($com_name, array('id' => $new_value));

        // Check if the record exists
        if ($existing_record) {
            // Get the new ID corresponding to the old parent ID
            $new_parent_id = isset($mapping[$existing_record->parentid]) ? $mapping[$existing_record->parentid] : 0;

            // Update the parentid property of the existing record
            $existing_record->parentid = $new_parent_id;

            // Update the record in the database
            $DB->update_record($com_name, $existing_record);
        } else {
            // Handle the case where the record with the old ID does not exist
            echo "Error: Record with ID $old_key not found in the database.";
        }
    }

    // Parse the CSV data for connections
    $con_rows = explode("\n", $con_table);
    $con_headers = str_getcsv(array_shift($con_rows));

    $connection_ids = [];

    // Iterate through each row in the CSV data for connections
    foreach ($con_rows as $row) {
        // Skip empty rows
        if (!empty($row)) {
            // Extract values from the row
            $values = str_getcsv($row);
            $record = array_combine($con_headers, $values);

            // Store the old ID
            $old_id = $record['id'];

            // Set the course ID for the record
            $record['courseid'] = $courseid;

            // Insert a new record and store the new ID
            $new_id = (int)$DB->insert_record($con_name, (object)$record);

            // Store the new ID
            $connection_ids[] = $new_id;
        }
    }

    // Update the competency IDs for the new connection records
    foreach ($connection_ids as $new_value) {
        // Get the record with the old ID from the database
        $existing_record = $DB->get_record($con_name, array('id' => $new_value));

        // Check if the record exists
        if ($existing_record) {
            // Get the new ID corresponding to the old competency ID
            $new_competencyid = isset($mapping[$existing_record->competencyid]) ? $mapping[$existing_record->competencyid] : 0;

            // Update the competencyid property of the existing record
            $existing_record->competencyid = $new_competencyid;

            // Update the record in the database
            $DB->update_record($con_name, $existing_record);
        } else {
            // Handle the case where the record with the old ID does not exist
            echo "Error: Record with ID $old_id not found in the database.";
        }
    }
}



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
        $com_name = 'gradereport_gradebook_xp_com';
        $com_table = Null;
        $con_name = 'gradereport_gradebook_xp_con';
        $con_table = Null;
        // Extract each CSV file from the zip archive
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $csv_data = $zip->getFromIndex($i);

            // Determine the table name from the filename
            $table_name = basename($filename, '.csv');

            // Check if the table name matches the expected ones
            if ($table_name == $com_name){
                $com_table = $csv_data;
            }
            else if ($table_name == $con_name) {
                $con_table = $csv_data;
            }
            else {
                echo "Skipping file $filename as it doesn't match the expected format.<br>";
            }
        }
        process_data($courseid, $com_name, $com_table, $con_name, $con_table);
        $zip->close();
        echo 'Import successful.';
    } else {
        echo 'Failed to open the zip file.';
    }
}

// Add heading to navbar
$PAGE->navbar->add('Import');

// Print header
print_grade_page_head($courseid, 'report', 'gradebook_xp_admin', 'Import', false, false, false);

// displays the form
$mform->display();

// Print footer
echo $OUTPUT->footer();
