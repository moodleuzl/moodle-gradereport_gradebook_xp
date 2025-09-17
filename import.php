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

// Get return URL.
$gpr = new grade_plugin_return();
$returnurl = $gpr->get_return_url('?id=' . $courseid);

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
 * @param bool $overwritecompetencies Whether to overwrite existing competencies.
 * @param bool $deletecompetencies Whether to delete all existing competencies before importing.
 * @param bool $overwriteconnections Whether to overwrite existing connections.
 * @param bool $deleteconnections Whether to delete all existing connections before importing.
 *
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function process_data($courseid, $comname, $comtable, $conname, $contable,
    $relname, $reltable,
    $overwritecompetencies, $deletecompetencies, $overwriteconnections, $deleteconnections) {
    global $DB;

    // Delete all existing competencies if requested.
    if ($deletecompetencies) {
        // Delete relations first (FK safety), then competencies.
        $competencyids = $DB->get_records_menu($comname, ['courseid' => $courseid], '', 'id, id');
        if (!empty($competencyids)) {
            list($insql, $inparams) = $DB->get_in_or_equal(array_keys($competencyids), SQL_PARAMS_QM);
            $DB->delete_records_select($relname, "parentid $insql OR childid $insql", array_merge($inparams, $inparams));
        }
        $DB->delete_records($comname, ['courseid' => $courseid]);
        echo "Deleted all existing competencies for course $courseid.<br>";
    }

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

                if ($overwritecompetencies) {
                    // Overwrite the existing record.
                    $record['id'] = $existingrecord->id;

                    // Strip any legacy parentid column if present in CSV.
                    unset($record['parentid']);
                    $DB->update_record($comname, (object)$record);
                    echo "Updated existing competency: " . $record['name'] . "<br>";
                } else {
                    // Skip if not overwriting.
                    echo "Skipped existing competency: " . $record['name'] . "<br>";
                }
            } else {
                // Insert new record.
                $record['courseid'] = $courseid;
                // Remove legacy parentid column if present.
                unset($record['parentid']);
                $newid = (int) $DB->insert_record($comname, (object)$record);

                // Store mapping of old and new IDs.
                $mapping[$record['id']] = $newid;
                echo "Inserted new competency: " . $record['name'] . "<br>";
            }
        }
    }

    // No direct parentid updates here; relations CSV will be used instead.

    if ($deleteconnections) {
        // Fetch all competency IDs associated with the current course.
        $competencyids = $DB->get_records_menu($comname, ['courseid' => $courseid], '', 'id, id');
        if (!empty($competencyids)) {
            list($insql, $inparams) = $DB->get_in_or_equal(array_keys($competencyids), SQL_PARAMS_QM);
            $DB->delete_records_select($conname, "competencyid $insql", $inparams);
            echo "Deleted all connections for course $courseid.<br>";
        } else {
            echo "No connections to delete for course $courseid.<br>";
        }
    }

    // Insert relations using mapping (if provided file present).
    insert_relations_from_csv($relname, $reltable, $mapping);

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

            // Check if the connection already exists based on activityid and competencyid.
            $existingconnection = $DB->get_record($conname, [
                'activityid' => $record['activityid'],
                'competencyid' => $record['competencyid']
            ]);

            if ($existingconnection) {
                if ($overwriteconnections) {
                    // Overwrite the existing connection.
                    $record['id'] = $existingconnection->id;
                    $DB->update_record($conname, (object)$record);
                    echo "Updated existing connection: Activity " . $record['activityid']
                        . " to Competency " . $record['competencyid'] . "<br>";
                } else {
                    // Skip if not overwriting.
                    echo "Skipped existing connection: Activity " . $record['activityid']
                        . " to Competency " . $record['competencyid'] . "<br>";
                }
            } else {
                // Insert a new record and store the new ID.
                $newid = (int) $DB->insert_record($conname, (object)$record);
                $connectionids[] = $newid;
                echo "Inserted new connection: Activity " . $record['activityid']
                    . " to Competency " . $record['competencyid'] . "<br>";
            }
        }
    }

    // Update the competency IDs for the new connection records.
    foreach ($connectionids as $newvalue) {
        // Get the record with the new ID from the database.
        $existingrecord = $DB->get_record($conname, ['id' => $newvalue]);

        // Check if the record exists.
        if ($existingrecord) {
            // Map the competencyid to the updated ID if available.
            $newcompetencyid = isset($mapping[$existingrecord->competencyid]) ? $mapping[$existingrecord->competencyid] : 0;

            // Update the competencyid if it has changed.
            if ($existingrecord->competencyid != $newcompetencyid) {
                $existingrecord->competencyid = $newcompetencyid;
                $DB->update_record($conname, $existingrecord);
                echo "Updated competency ID in connection: " . $existingrecord->id . "<br>";
            }
        } else {
            echo "Error: Connection with ID $newvalue not found in the database.";
        }
    }
}

if ($mform->is_cancelled()) {
    global $mform, $returnurl;
    // Handle form cancel operation.
    // Redirect to manage_competencies.php page.
    redirect($returnurl, get_string('cancelimport', 'gradereport_gb_xp_admin'));
} else if ($data = $mform->get_data()) {
    $zipcontent = $mform->get_file_content('userfile');
    $name = $mform->get_new_filename('userfile');

    // Write the zip content to a temporary file.
    $zipfile = sys_get_temp_dir() . '/imported_data.zip';
    file_put_contents($zipfile, $zipcontent);

    // Open the zip archive.
    $zip = new ZipArchive;
    if ($zip->open($zipfile) === true) {
        $comname = 'gradereport_gb_xp_admin_competencies';
        $comtable = null;
    $conname = 'gradereport_gb_xp_admin_connections';
        $contable = null;
        $relname = 'gradereport_gb_xp_admin_relations';
        $reltable = null;
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
            } else if ($tablename == $relname) {
                $reltable = $csvdata;
            } else {
                echo "Skipping file $filename as it doesn't match the expected format.<br>";
            }
        }
        $overwritecompetencies = !empty($data->overwritecompetencies);
        $deletecompetencies = !empty($data->deletecompetencies);
        $overwriteconnections = !empty($data->overwriteconnections);
        $deleteconnections = !empty($data->deleteconnections);
        process_data($courseid, $comname, $comtable, $conname, $contable, $relname, $reltable,
            $overwritecompetencies, $deletecompetencies, $overwriteconnections, $deleteconnections);
        $zip->close();
        // Redirect with success message.
        redirect($returnurl,
            get_string('importsuccess', 'gradereport_gb_xp_admin'));
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

/**
 * Insert relations based on relations CSV and id mapping.
 * This function is added at the end to avoid breaking flow above.
 */
function insert_relations_from_csv($relname, $reltable, $mapping) {
    global $DB;
    if (empty($reltable)) { return; }
    $rows = explode("\n", $reltable);
    if (empty($rows)) { return; }
    $headers = str_getcsv(array_shift($rows));
    $hasparent = in_array('parentid', $headers);
    $haschild = in_array('childid', $headers);
    if (!$hasparent || !$haschild) { return; }
    foreach ($rows as $row) {
        if (trim($row) === '') { continue; }
        $values = str_getcsv($row);
        $record = array_combine($headers, $values);
        $oldparent = $record['parentid'] ?? null;
        $oldchild = $record['childid'] ?? null;
        if ($oldparent === null || $oldchild === null) { continue; }
        $newparent = $mapping[$oldparent] ?? null;
        $newchild = $mapping[$oldchild] ?? null;
        if ($newparent && $newchild) {
            // Avoid duplicates based on composite key.
            if (!$DB->record_exists($relname, ['parentid' => $newparent, 'childid' => $newchild])) {
                $DB->insert_record($relname, (object)['parentid' => (int)$newparent, 'childid' => (int)$newchild]);
            }
        }
    }
}
