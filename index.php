<?php

/*
 * File: index.php
 * Project: gradebook_xp
 * Created Date: 17.11.2022 19:19:13
 * Author: DominikMa, 3urobeat, thePulpo
 * 
 * Last Modified: 01.06.2023 16:14:24
 * Modified By: 3urobeat
 */


/**
 * The gradebook_xp report
 *
 * @package gradebook_xp
 */

require_once '../../../config.php';
require_once './lib.php';
require_once 'db_controller.php';
require_once $CFG->dirroot . '/grade/lib.php';

$courseid = optional_param('id', SITEID, PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

$PAGE->set_url(new moodle_url('/grade/report/gradebook_xp/index.php', array('id' => $courseid, 'userid' => $userid)));


// Check if user is trying to visit our page with an invalid course id and display error message
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

require_login(null, false);
$PAGE->set_course($course);
$context = context_course::instance($course->id);


// TODO: Check if user has permission to view this page
// Display admin view or user view
if (has_capability('moodle/grade:viewall', $context) && $courseid != SITEID) {

    // Print demo msg for admin
    //echo "Hello Admin";

    /* $gpr = new grade_plugin_return();
    $returnurl = $gpr->get_return_url('preferences.php?id='.$courseid);
    redirect($returnurl, 'You have been redirected to the preferences page.'); */


} else {

    // Print demo msg for user
    //echo "Hello User";

}

// Print Header which contains links to all other reports etc. Highlight our tab by passing our pluginname as param active_plugin
print_grade_page_head($courseid, 'report', 'gradebook_xp', get_string('pluginname', 'gradereport_gradebook_xp')); // Function provided by /grade/lib.php


// Print all activities this course has with the corresponding grades of our user
echo "<br><br>"; // Make some room below the greeting

$course_activities = get_all_activities($courseid); // Get grades for all assignments of this course
$raw_grades = get_grades($course, $context, $userid);
$competencies = get_all_competencies();

$grades = array();
foreach ($raw_grades as $raw_grade) {
    if (array_key_exists('cmid', $raw_grade)){
        $grades[$raw_grade['cmid']] = $raw_grade;
    }
}

#echo debug($grades); // Enable to log raw data
#echo debug($competencies); // Enable to log raw data

$chart_competencies = array();
foreach ($competencies as $competency) {
    # debug($competency);
    $competency_total_weight_sum = 0;
    $competency_weight_sum = 0.0;
    foreach (get_connections($competency->id) as $connection) {
        $competency_total_weight_sum += $connection->weight;
        $grade = $grades[$connection->assignmentid];
        $competency_weight_sum += $connection->weight * ($grade['graderaw'] / $grade['grademax']);
    }
    if ($competency_total_weight_sum > 0) {
        $chart_competency = array(
            'name' => "'" . $competency->name . "'",
            'user' => $competency_weight_sum / $competency_total_weight_sum * 100,
            'success' => 50
        );
        $chart_competencies[$competency->id] = $chart_competency;
    }
}

$char_competencies_name = array_map(fn($competency) => $competency['name'], $chart_competencies);
$char_competencies_user = array_map(fn($competency) => $competency['user'], $chart_competencies);
$char_competencies_success = array_map(fn($competency) => $competency['success'], $chart_competencies);

echo $OUTPUT->render_from_template('gradereport_gradebook_xp/index', (object) [
    'competencies' => "[".implode(",", $char_competencies_name)."]",
    'data_user' => "[".implode(",", $char_competencies_user)."]",
    'data_success' => "[".implode(",", $char_competencies_success)."]"
]);

// Print footer
echo $OUTPUT->footer();