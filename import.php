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

/**
 * Handles the import of competency and connection data for a course from CSV files within a zip archive.
 *
 * @package    gradereport_gb_xp_admin
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once('./lib.php');
require_once('./db_controller.php');
require_once('import_form.php');

// Get required parameter.
$courseid = required_param('id', PARAM_INT);

// Set up the page.
require_course_login($courseid);
gradereport_gb_xp_admin_setup_page($courseid, 'moodle/grade:manage');
$PAGE->navbar->add(get_string('preferences'));

// Instantiate import_form.
$mform = new import_form();

/**
 * Process and import data into the specified tables in the database.
 *
 * This function parses CSV data for competencies and connections,
 * inserts them into their respective tables, and updates parent IDs
 * based on the mappings generated during insertion.
 *
 * @param int $courseid The ID of the course for which the data is being processed.
 * @param string $comname The name of the competency table in the database.
 * @param string $comtable The CSV data for competencies.
 * @param string $conname The name of the connection table in the database.
 * @param string $contable The CSV data for connections.
 * @param bool $overwrite Whether to overwrite existing competencies.
 *
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function process_data($courseid, $comname, $comtable, $conname, $contable, $overwrite) {
    global $DB;

    // Parse the CSV data for competencies.
    $comrows = explode("\n", $comtable);
    $comheaders = str_getcsv(array_shift($comrows));

    // Initialize array to store old and new IDs mapping.
    $mapping = [];

    // Iterate through each row in the CSV data for competencies.
    foreach ($comrows as $row) {
        // Skip empty rows.
        if (!empty($row)) {
            // Extract values from the row.
            $values = str_getcsv($row);
            $record = array_combine($comheaders, $values);

            // Check if the competency already exists in the course.
            $existingrecord = $DB->get_record($comname, ['name' => $record['name'], 'courseid' => $courseid]);

            if ($existingrecord) {
                // Update mapping with existing record ID.
                $mapping[$record['id']] = $existingrecord->id;

                if ($overwrite) {
                    // Overwrite the existing record.
                    $record['id'] = $existingrecord->id;

                    // Ensure parent ID is updated correctly using mapping.
                    if (isset($record['parentid']) && isset($mapping[$record['parentid']])) {
                        $record['parentid'] = $mapping[$record['parentid']];
                    }

                    $DB->update_record($comname, (object)$record);
                    echo "Updated existing competency: " . $record['name'] . "<br>";
                } else {
                    // Skip if not overwriting.
                    echo "Skipped existing competency: " . $record['name'] . "<br>";
                }
            } else {
                // Insert new record.
                $record['courseid'] = $courseid;
                $newid = (int) $DB->insert_record($comname, (object)$record);

                // Store mapping of old and new IDs.
                $mapping[$record['id']] = $newid;
                echo "Inserted new competency: " . $record['name'] . "<br>";
            }
        }
    }

    // Update the parent IDs for the new competency records.
    foreach ($mapping as $oldkey => $newvalue) {
        // Get the record with the new ID from the database.
        $existingrecord = $DB->get_record($comname, ['id' => $newvalue]);

        // Check if the record exists.
        if ($existingrecord) {
            // Map the parentid if it exists in the mapping.
            $newparentid = isset($mapping[$existingrecord->parentid])
                ? $mapping[$existingrecord->parentid]
                : $existingrecord->parentid;

            // Update the parentid property only if it has changed.
            if ($existingrecord->parentid != $newparentid) {
                $existingrecord->parentid = $newparentid;

                // Update the record in the database.
                $DB->update_record($comname, $existingrecord);
                echo "Updated parent ID for competency: " . $existingrecord->name . "<br>";
            }
        } else {
            // Handle the case where the record with the old ID does not exist.
            echo "Error: Record with ID $oldkey not found in the database.";
        }
    }

    // Parse the CSV data for connections.
    $conrows = explode("\n", $contable);
    $conheaders = str_getcsv(array_shift($conrows));

    $connectionids = [];

    // Iterate through each row in the CSV data for connections.
    foreach ($conrows as $row) {
        // Skip empty rows.
        if (!empty($row)) {
            // Extract values from the row.
            $values = str_getcsv($row);
            $record = array_combine($conheaders, $values);

            // Store the old ID.
            $oldid = $record['id'];

            // Set the course ID for the record.
            $record['courseid'] = $courseid;

            // Insert a new record and store the new ID.
            $newid = (int) $DB->insert_record($conname, (object)$record);

            // Store the new ID.
            $connectionids[] = $newid;
        }
    }

    // Update the competency IDs for the new connection records.
    foreach ($connectionids as $newvalue) {
        // Get the record with the old ID from the database.
        $existingrecord = $DB->get_record($conname, ['id' => $newvalue]);

        // Check if the record exists.
        if ($existingrecord) {
            // Get the new ID corresponding to the old competency ID.
            $newcompetencyid = isset($mapping[$existingrecord->competencyid]) ? $mapping[$existingrecord->competencyid] : 0;

            // Update the competencyid property of the existing record.
            $existingrecord->competencyid = $newcompetencyid;

            // Update the record in the database.
            $DB->update_record($conname, $existingrecord);
        } else {
            // Handle the case where the record with the old ID does not exist.
            echo "Error: Record with ID $oldid not found in the database.";
        }
    }
}

if ($mform->is_cancelled()) {
    // Handle form cancellation.
    echo 'Import cancelled.';
} else if ($data = $mform->get_data()) {
    $zipcontent = $mform->get_file_content('userfile');
    $name = $mform->get_new_filename('userfile');
    echo "<script>alert('$name');</script>";

    // Write the zip content to a temporary file.
    $zipfile = sys_get_temp_dir() . '/imported_data.zip';
    file_put_contents($zipfile, $zipcontent);

    // Open the zip archive.
    $zip = new ZipArchive;
    if ($zip->open($zipfile) === true) {
        $comname = 'gradereport_gb_xp_admin_com';
        $comtable = null;
        $conname = 'gradereport_gb_xp_admin_con';
        $contable = null;
        // Extract each CSV file from the zip archive.
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $csvdata = $zip->getFromIndex($i);

            // Determine the table name from the filename.
            $tablename = basename($filename, '.csv');

            // Check if the table name matches the expected ones.
            if ($tablename == $comname) {
                $comtable = $csvdata;
            } else if ($tablename == $conname) {
                $contable = $csvdata;
            } else {
                echo "Skipping file $filename as it doesn't match the expected format.<br>";
            }
        }
        $overwrite = !empty($data->overwrite);
        process_data($courseid, $comname, $comtable, $conname, $contable, $overwrite);
        $zip->close();
        echo 'Import successful.';
    } else {
        echo 'Failed to open the zip file.';
    }
}

// Add heading to navbar.
$PAGE->navbar->add('Import');

// Print header.
print_grade_page_head($courseid, 'report', 'gb_xp_admin', 'Import', false, false, false);

// Displays the form.
$mform->display();

// Print footer.
echo $OUTPUT->footer();
