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
 * Handles the creation, editing, and deletion of competencies within the gb_xp_admin plugin.
 *
 * @package    gradereport_gb_xp_admin
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Load necessary files.
require_once('../../../config.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once('edit_form.php');
require_once('lib.php');
require_once('db_controller.php');

$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/grade/report/gb_xp_admin/js/move_multiselect.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/grade/report/gb_xp_admin/js/change_level.js'));

// Get URL parameters.
$courseid = required_param('courseid', PARAM_INT);
$id = optional_param('id', null, PARAM_INT);
$action = optional_param('action', 'new', PARAM_ALPHA);

// Set up the page.
require_course_login($courseid);
gradereport_gb_xp_admin_setup_page($courseid);

// Get return URL.
$gpr = new grade_plugin_return();
$returnurl = $gpr->get_return_url('manage_competencies.php?id=' . $courseid);

// Instantiate edit_form.
$mform = new edit_form();

handle_action_buttons();
generate_output();

/**
 * Handles the action for editing, deleting, or creating a new competency.
 */
function handle_action() {
    global $id, $action, $returnurl, $heading;

    if ($action == 'delete') {
        // Delete the competency and any relations involving it.
        delete_competency($id);

        // Redirect user to manage_competencies.php page.
        redirect($returnurl, 'You have successfully deleted the competency.');

    } else if ($action == 'edit') {
        // Handle edit action.
        // Get name using ID.
        $name = get_competency($id)->name;

        // Change heading in the navbar to current name.
        $heading = $name;

    } else {
        // Handle no action (new action for example).
        // Set page heading.
        $heading = get_string('newcompetency', 'gradereport_gb_xp_admin');
    }
}

/**
 * Handles the action buttons for the competency form submission.
 */
function handle_action_buttons() {
    global $mform, $returnurl;

    // Check if form is cancelled.
    if ($mform->is_cancelled()) {
        // Handle form cancel operation.
        // Redirect to manage_competencies.php page.
        redirect($returnurl, get_string('cancelcompetency', 'gradereport_gb_xp_admin'));

    } else if ($fromform = $mform->get_data()) {
        // If form data is submitted and validated.

        $activityids = [];
        if (!empty($fromform->connections)) {
            $connectionsarray = json_decode($fromform->connections, true);

            foreach ($connectionsarray as $connection) {
                $activityid = $connection['id'];
                $connectionlevel = $connection['level'];

                gradereport_gb_xp_admin_set_competency_connection($activityid, $fromform->id, $connectionlevel);
                $activityids[] = $activityid;
                // Process these values as needed.
            }
        }

        foreach (get_connections($fromform->id) as $connection) {
            if (!in_array($connection->activityid, $activityids)) {
                delete_connection($connection->id);
            }
        }

        // Check if competency already exists.
        if (!empty($fromform->id)) {
            // Get existing competency.
            $existingcompetency = get_competency($fromform->id);

            if ($existingcompetency) {
                // Update existing competency.
                $existingcompetency->name = $fromform->name;
                $existingcompetency->description = $fromform->description;
                $existingcompetency->maxcomlvl = $fromform->maxcomlvl;
                $existingcompetency->islevelsummed = $fromform->islevelsummed;

                update_competency($existingcompetency);
                // Update parent relations.
                $parentids = isset($fromform->parentids) ? $fromform->parentids : [];
                set_parents($existingcompetency->id, $parentids);

                // Redirect with success message.
                redirect($returnurl,
                    get_string('updatecompetencysuccess', 'gradereport_gb_xp_admin') . '\'' . $fromform->name . '\'');
            }
        }

        // If competency does not exist, insert a new one.
        $competencytoinsert = new stdClass();
        $competencytoinsert->courseid = $fromform->courseid;
        $competencytoinsert->name = $fromform->name;
        $competencytoinsert->description = $fromform->description;
        $competencytoinsert->maxcomlvl = $fromform->maxcomlvl;
        $competencytoinsert->islevelsummed = $fromform->islevelsummed;

        $newid = insert_competency($competencytoinsert);
        // Set parent relations for new record.
        $parentids = isset($fromform->parentids) ? $fromform->parentids : [];
        if ($newid) {
            set_parents($newid, $parentids);
        }

        // Redirect with success message.
        redirect($returnurl,
            get_string('createcompetencysuccess', 'gradereport_gb_xp_admin') . '\'' . $fromform->name . '\'');

    } else {
        handle_action(); // No action needed.
    }
}

/**
 * Generates the output for the competency page, including the header, form, and footer.
 */
function generate_output() {
    global $PAGE, $heading, $courseid, $mform, $OUTPUT;

    // Add heading to navbar.
    $PAGE->navbar->add($heading);

    // Print header.
    print_grade_page_head($courseid, 'report', 'gb_xp_admin', $heading, false, false, false);

    // Displays the form.
    $mform->display();

    // Print footer.
    echo $OUTPUT->footer();
}
