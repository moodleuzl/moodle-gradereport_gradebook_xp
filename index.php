<?php

/*
 * File: index.php
 * Project: gradebook_xp_admin
 * Created Date: 17.11.2022 19:19:13
 * Author: DominikMa, 3urobeat, thePulpo
 * 
 * Last Modified: 01.06.2023 16:14:24
 * Modified By: 3urobeat
 */


/**
 * The gradebook_xp_admin report
 *
 * @package gradebook_xp_admin
 */

require_once '../../../config.php';
require_once './lib.php';
require_once './db_controller.php';
require_once $CFG->dirroot . '/grade/lib.php';

$courseid = optional_param('id', SITEID, PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

gradereport_gradebook_xp_admin_setup_page($courseid, 'moodle/grade:view');
$PAGE->navbar->add(get_string('view'));

// Print Header which contains links to all other reports etc. Highlight our tab by passing our pluginname as param active_plugin
print_grade_page_head($courseid, 'report', 'gradebook_xp_admin', get_string('pluginname', 'gradereport_gradebook_xp_admin')); // Function provided by /grade/lib.php


// Print all activities this course has with the corresponding grades of our user
echo "<br><br>"; // Make some room below the greeting

$course_activities = get_all_activities($courseid); // Get grades for all activities of this course
$raw_grades = gradereport_gradebook_xp_admin_get_grades($COURSE, $context, $userid);
$competencies = get_all_competencies();

$grades = array();
foreach ($raw_grades as $raw_grade) {
    if (array_key_exists('cmid', $raw_grade)) {
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
        $grade = $grades[$connection->activityid];
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

echo $OUTPUT->render_from_template('gradereport_gradebook_xp_admin/index', (object)[
    'competencies' => "[" . implode(",", $char_competencies_name) . "]",
    'data_user' => "[" . implode(",", $char_competencies_user) . "]",
    'data_success' => "[" . implode(",", $char_competencies_success) . "]"
]);

// Print footer
echo $OUTPUT->footer();