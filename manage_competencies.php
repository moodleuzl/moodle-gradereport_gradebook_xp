<?php
require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once('lib.php');

// Get required and optional parameters
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

// Set up the page
setup_page($courseid, 'moodle/grade:manage');

// Get the competencies for the course and sort by ID
$competencies = $DB->get_records_sql("
    SELECT c.*, p.name AS parentname
    FROM {gradereport_gradebook_xp_com} c
    LEFT JOIN {gradereport_gradebook_xp_com} p ON p.id = c.parentid
    WHERE c.courseid = :courseid
    ORDER BY c.id ASC
", ['courseid' => $courseid]);

// Render the page
$templatecontext = (object)[
    'competencies' => array_values($competencies),
    'gobackurl' => new moodle_url('/grade/report/gradebook_xp/preferences.php'),
    'editurl' => new moodle_url('/grade/report/gradebook_xp/edit.php'),
    'courseid' => $courseid,
];

print_grade_page_head($courseid, 'settings', 'gradebook_xp', get_string('pluginname', 'gradereport_gradebook_xp'), false, '');
echo $OUTPUT->render_from_template('gradereport_gradebook_xp/manage_competencies', $templatecontext);
echo $OUTPUT->footer();
