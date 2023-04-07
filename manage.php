<?php

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';

$courseid = required_param('id', PARAM_INT);        // Course id.
$userid   = optional_param('userid', $USER->id, PARAM_INT);

$url = new moodle_url('/grade/report/gradebook_xp/manage.php', array('id' => $courseid, 'userid' => $userid));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

/// Make sure they can even access this course
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

require_login($course);
$context = context_course::instance($course->id);

// TODO: Check if user has permission to view this page

// Print header.
print_grade_page_head($COURSE->id, 'settings', 'gradebook_xp', get_string('pluginname', 'gradereport_gradebook_xp'), false, '');

echo $OUTPUT->single_button(new moodle_url('edit.php', array('id' => $course->id)), get_string('additem',
    'grades'), 'get');

echo $OUTPUT->footer();