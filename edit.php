<?php

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
//require_once $CFG->dirroot.'/grade/report/lib.php';
require_once 'edit_form.php';


$courseid = required_param('id', PARAM_INT);        // Course id.
$userid   = optional_param('userid', $USER->id, PARAM_INT);

$PAGE->set_pagelayout('admin');
$url = new moodle_url('/grade/report/history/manage.php', array('id' => $courseid, 'userid' => $userid));
$PAGE->set_url($url);

/// Make sure they can even access this course
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

require_login($course);
$context = context_course::instance($course->id);

// TODO: Check if user has permission to view this page

// TODO: Change $heading accordingly, like in ../../edit/tree/item.php
$heading = get_string('newitem', 'grades');

// Add heading to navbar
$PAGE->navbar->add($heading);

// Print header.
print_grade_page_head($courseid, 'settings', 'gradebook_xp', $heading, false, false, false);

//Instantiate edit_form
$mform = new edit_form();
$mform->display();

// TODO: Handle Form submit/cancel

echo $OUTPUT->footer();
