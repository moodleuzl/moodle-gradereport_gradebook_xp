<?php

// Load necessary files
require_once '../../../config.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once 'edit_form.php';
require_once 'lib.php';
require_once 'db_controller.php';

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
    global $id, $action, $returnurl, $heading;

    if ($action == 'delete') {
        // Get all children competency of the deleted competency
        $children_competencies = get_direct_children($id);

        // Update the parentid of all children competencies to 0
        foreach ($children_competencies as $child_competency) {
            $child_competency->parentid = 0;
            update_competency($child_competency);
        }

        // Delete the competency using ID
        delete_competency($id);

        // Redirect user to manage_competencies.php page
        redirect($returnurl, 'You have successfully deleted the competency.');

    } else if ($action == 'edit') {
        // Handle edit action
        // Get name using ID
        $name = get_competency($id)->name;

        // Change heading in the navbar to current name
        $heading = $name;

    } else {
        // Handle no action (new action for example)
        // Set page heading
        $heading = get_string('newcompetency', 'gradereport_gradebook_xp');
    }
}


function handle_action_buttons() {
    global $mform, $returnurl;

    // Check if form is cancelled
    if ($mform->is_cancelled()) {
        // Handle form cancel operation
        // Redirect to manage_competencies.php page
        redirect($returnurl, 'You cancelled the competency form.');

    } else if ($fromform = $mform->get_data()) { // If form data is submitted and validated
        // Check if competency already exists
        if (!empty($fromform->id)) {
            // Get existing competency
            $existing_competency = get_competency($fromform->id);

            if ($existing_competency) {
                // Update existing competency
                $existing_competency->name = $fromform->name;
                $existing_competency->description = $fromform->description;
                $existing_competency->parentid = $fromform->parentid;

                update_competency($existing_competency);

                // Redirect with success message
                redirect($returnurl, 'You have successfully updated the competency: \''.$fromform->name.'\'');
            }
        }

        // If competency does not exist, insert a new one
        $competencytoinsert = new stdClass();
        $competencytoinsert->courseid = $fromform->courseid;
        $competencytoinsert->name = $fromform->name;
        $competencytoinsert->description = $fromform->description;
        $competencytoinsert->parentid = $fromform->parentid;

        insert_competency($competencytoinsert);

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

