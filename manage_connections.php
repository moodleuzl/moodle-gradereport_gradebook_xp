<?php
require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once('lib.php');

// Get required and optional parameters
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

// Set up the page
setup_page($courseid, 'moodle/grade:manage');

// Render the page
$templatecontext = (object)[
    'gobackurl' => new moodle_url('/grade/report/gradebook_xp/preferences.php'),
    'courseid' => $courseid,
];

print_grade_page_head($courseid, 'settings', 'gradebook_xp', get_string('pluginname', 'gradereport_gradebook_xp'), false, '');
echo $OUTPUT->render_from_template('gradereport_gradebook_xp/manage_connections', $templatecontext);
echo $OUTPUT->footer();
