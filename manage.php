<?php

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once 'lib.php';

$courseid = required_param('id', PARAM_INT);        // Course id.
$userid   = optional_param('userid', $USER->id, PARAM_INT);

setup_page($courseid, 'moodle/grade:manage');


// TODO: Optimize the following to easily get any field of the parent (like parent.id)
// This is an old approach without SQL
//$competencies = $DB->get_records('gradereport_gradebook_xp', ['courseid' => $courseid]);
//foreach ($competencies as $competency) {
//    $parent = $DB->get_record('gradereport_gradebook_xp', ['id' => $competency->parentid]);
//
//    $competency->parentid = $parent ? $parent->id : null;
//    $competency->parentname = $parent ? $parent->name : '';
//}

$competencies = $DB->get_records_sql("
    SELECT c.*, p.name AS parentname
    FROM {gradereport_gradebook_xp} c
    LEFT JOIN {gradereport_gradebook_xp} p ON p.id = c.parentid
    WHERE c.courseid = :courseid
    ORDER BY c.id ASC
", ['courseid' => $courseid]);

foreach ($competencies as $competency) {
    $competency->parentid = $competency->parentid ?: null;
}


// BEGIN: display

// Print header.
print_grade_page_head($COURSE->id, 'settings', 'gradebook_xp', get_string('pluginname', 'gradereport_gradebook_xp'), false, '');


/// Debug message

//$table_name = 'gradereport_gradebook_xp'; // replace with the actual table name
//$records = $DB->get_records('gradereport_gradebook_xp', ['courseid' => $courseid]);
//echo "<pre>";
//print_r($records);
//echo "</pre>";


$templatecontext = (object)[
    'competencies' => array_values($competencies),
    'editurl' => new moodle_url('/grade/report/gradebook_xp/edit.php'),
    'courseid' => $courseid,
];
//$templatecontext = (object)[
//    'competencies' => array_values($competencies),
//];

echo $OUTPUT->render_from_template('gradereport_gradebook_xp/manage', $templatecontext);

// old button but can still be useful syntax
//echo $OUTPUT->single_button(new moodle_url('edit.php', array('courseid' => $course->id)), get_string('additem',
//    'grades'), 'get');

echo $OUTPUT->footer();

// END: display