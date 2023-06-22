<?php

// Load necessary files
require_once '../../../config.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once 'edit_form.php';
require_once 'lib.php';

// Get URL parameters
$courseid = required_param('courseid', PARAM_INT);
$id = optional_param('id', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);

setup_page($courseid, 'moodle/grade:manage');

// Get return URL
$gpr = new grade_plugin_return();
$returnurl = $gpr->get_return_url('manage_competencies.php?id=' . $courseid);

// Set page heading
//$heading = get_string('name', 'gradereport_gradebook_xp');

//handle_action($returnurl, $action);

// Instantiate edit_form
$mform = new edit_form();

handle_action_buttons();
generate_output();

function handle_action(){
    global $DB, $id, $action, $returnurl, $heading;

    if ($action == 'delete') {
        // Get all children records of the deleted record
        $children_records = get_direct_children($id);

        // Update the parentid of all children records to 0
        foreach ($children_records as $child_record) {
            $child_record->parentid = 0;
            $DB->update_record('gradereport_gradebook_xp_com', $child_record);
        }

        // Delete the record using ID
        $DB->delete_records('gradereport_gradebook_xp_com', array('id' => $id));

        // Redirect user to manage_competencies.php page
        redirect($returnurl, 'You have successfully deleted the competency.');

    } else if ($action == 'edit') {
        // Handle edit action
        // Get name using ID
        $name = $DB->get_field(
            'gradereport_gradebook_xp_com',
            'name',
            array('id' => $id)
        );

        // Change heading in the navbar to current name
        $heading = $name;

    } else {
        // Handle no action (new action for example)
        // Set page heading
        $heading = get_string('newcompetency', 'gradereport_gradebook_xp');
    }
}


function handle_action_buttons() {
    global $DB, $mform, $returnurl;

    // Check if form is cancelled
    if ($mform->is_cancelled()) {
        // Handle form cancel operation
        // Redirect to manage_competencies.php page
        redirect($returnurl, 'You cancelled the competency form.');

    } else if ($fromform = $mform->get_data()) { // If form data is submitted and validated
        // Check if record already exists
        if (!empty($fromform->id)) {
            // Get existing record
            $existing_record = $DB->get_record('gradereport_gradebook_xp_com', array('id' => $fromform->id));

            if ($existing_record) {
                // Update existing record
                $existing_record->name = $fromform->name;
                $existing_record->description = $fromform->description;
                $existing_record->parentid = $fromform->parentid;

                $DB->update_record('gradereport_gradebook_xp_com', $existing_record);

                // Redirect with success message
                redirect($returnurl, 'You have successfully updated the competency: \''.$fromform->name.'\'');
            }
        }

        // If record does not exist, insert a new one
        $recordtoinsert = new stdClass();
        $recordtoinsert->courseid = $fromform->courseid;
        $recordtoinsert->name = $fromform->name;
        $recordtoinsert->description = $fromform->description;
        $recordtoinsert->parentid = $fromform->parentid;

        $DB->insert_record('gradereport_gradebook_xp_com', $recordtoinsert);

        // Redirect with success message
        redirect($returnurl, 'You have successfully created the competency: \''.$fromform->name.'\'');

    } else { // If form is submitted but the data doesn't validate and the form should be redisplayed or on the first display of the form
        handle_action(); // No action needed
    }
}


function generate_output() {
    global $PAGE, $heading, $courseid, $mform, $OUTPUT;

    // add heading to navbar
    $PAGE->navbar->add($heading);

    // Print header
    print_grade_page_head($courseid, 'settings', 'gradebook_xp', $heading, false, false, false);

    // displays the form
    $mform->display();

    // Print footer
    echo $OUTPUT->footer();
}


function get_direct_children($id) {
    global $DB;

    $children_records = $DB->get_records('gradereport_gradebook_xp_com', array('parentid' => $id));

    return $children_records;
}


function get_children($id) {
    global $DB;

    // Use a recursive SQL query to get all children records of the parent
    $sql = "WITH RECURSIVE item_descendants AS (
                SELECT id, parentid, name
                FROM {gradereport_gradebook_xp_com}
                WHERE id = ?
                UNION
                SELECT g.id, g.parentid, g.name
                FROM {gradereport_gradebook_xp_com} g
                JOIN item_descendants d ON g.parentid = d.id
            )
            SELECT id, name
            FROM item_descendants
            WHERE id != ?";

    $params = array($id, $id);
    $children_records = $DB->get_records_sql($sql, $params);

    return $children_records;
}
