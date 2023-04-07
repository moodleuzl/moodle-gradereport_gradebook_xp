<?php

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
//require_once $CFG->dirroot.'/grade/report/lib.php';
require_once 'edit_form.php';

// This is the requiered url parameter
$courseid = required_param('courseid', PARAM_INT);
$competencyid   = optional_param('$competencyid', 0, PARAM_INT);

$url = new moodle_url('/grade/report/gradebook_xp/edit.php', array('id'=>$courseid));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

/// Make sure they can even access this course
if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
    print_error('invalidcourseid');
}

require_login($course);
$context = context_course::instance($course->id);
// TODO: Check if user has permission to view this page

$gpr = new grade_plugin_return();
$returnurl = $gpr->get_return_url('manage.php?id='.$courseid);

$heading = get_string('newcompetency', 'gradereport_gradebook_xp');

// TODO: Create form
//Instantiate edit_form
$mform = new edit_form();

// TODO: Handle Form submit/cancel
// BEGIN: sumbit/cancel

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    // Go back to manage.php page
    redirect($returnurl, 'You cancelled the competency form.');
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.

    // Insert the data into the database table.
    $recordtoinsert = new stdClass();
    $recordtoinsert->courseid = $fromform->id;
    $recordtoinsert->name = $fromform->name;
    // Bypass error when creating competency without parent
    $recordtoinsert->parentid = isset($fromform->parent) ? $fromform->parent : null;
    $DB->insert_record('gradereport_gradebook_xp', $recordtoinsert);

    // Go back to manage.php page
    redirect($returnurl, 'You have successfully created the competency: \''.$fromform->name.'\'');

} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
}

// END: sumbit/cancel

// BEGIN: display

// add heading to navbar
$PAGE->navbar->add($heading);
// Print header
print_grade_page_head($courseid, 'settings', 'gradebook_xp', $heading, false, false, false);

// displays the form
$mform->display();

// Print footer
echo $OUTPUT->footer();

// END: display
