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

echo debug(get_grades($course, $context, $userid)); // Enable to log raw data

//grade_report_gradebook_xp_add_competency($courseid, 'Java Programmierung', "This is a test!");
//grade_report_gradebook_xp_add_competency($courseid, 'Gen-Datenbanken', "This is an updated test!");
//grade_report_gradebook_xp_add_competency($courseid, 'Bioinformatik Algorithmen', "This is another test!");
//grade_report_gradebook_xp_add_competency($courseid, 'Bioinformatik Konzepte', "This is another updated test!");
//grade_report_gradebook_xp_add_competency($courseid, 'Wissenschaftliches Arbeiten', "This is another updated test!");
//
//grade_report_gradebook_xp_set_competency_connection(70, 1, 50);
//grade_report_gradebook_xp_set_competency_connection(4, 3, 20);

$competencies = get_all_competencies();
$chart_competencies = array();
foreach ($competencies as $competency) {
    $chart_competency = array(
        'name'=>"'".$competency->name."'",
        'user'=>random_int(0, 100),
        'average'=>random_int(0, 100),
        'success'=>50
    );
    $chart_competencies[$competency->id] = $chart_competency;
    # array_push($char_competency_names, $chart_competency['name']);

    $connections = get_connections($competency->id);
//    echo $competency->name;
//    echo "<br>";
//    echo json_encode($connections);
//    echo "<br>";
//    echo "<br>";

//    echo $competency->name;
//    echo ": ";
//    echo $competency->description;
//    echo $competency->id;
//    echo "<br>";
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