<?php

/*
 * File: index.php
 * Project: gradebook_xp
 * Created Date: 17.11.2022 19:19:13
 * Author: DominikMa, 3urobeat, thePulpo
 * 
 * Last Modified: 09.02.2023 16:33:15
 * Modified By: 3urobeat
 */


/**
 * The gradebook_xp report
 *
 * @package gradebook_xp
 */

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';

$courseid = optional_param('id', SITEID, PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);

$PAGE->set_url(new moodle_url('/grade/report/gradebook_xp/index.php', array('id' => $courseid, 'userid' => $userid)));


// Check if user is trying to visit our page with an invalid course id and display error message
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

require_login(null, false);
$PAGE->set_course($course);
$context = context_course::instance($course->id);


// TODO: Check if user has permission to view this page


// Print Header which contains links to all other reports etc. Highlight our tab by passing our pluginname as param active_plugin
print_grade_page_head($courseid, 'report', 'gradebook_xp', get_string('pluginname', 'gradereport_gradebook_xp')); // Function provided by /grade/lib.php


// Display admin view or user view
if (has_capability('moodle/grade:viewall', $context) && $courseid != SITEID) {

    // Print demo msg for admin
    echo "Hello Admin";

} else {

    // Print demo msg for user
    echo "Hello User";

}


// Get grades for all assignments of this course
global $OUTPUT;
if (!empty($course->showgrades)) {

    /// return tracking object
    $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'user', 'courseid'=>$course->id, 'userid'=>$userid));
    // Create a report instance
    $report = new grade_report_user($course->id, $gpr, $context, $userid, false); // viewasuser = false

    // Make some room below the greeting
    echo "<br><br>";
    
    if ($report->fill_table()) { // Fill table with data of all assignments
        
        // Log everything we've got:
        /* foreach ($report as $key => $child) {
            echo $key;
            echo " = ";
            echo var_dump($child);
            echo "<br>";
        } */

        // Log everything about grading we've got:
        //echo var_dump($report->gradeitemsdata);

        // Log only interesting stuff (assignment id (name) & grade):
        foreach ($report->gradeitemsdata as $key => $child) {
            if ($child["itemtype"] == "course") echo "<br>Course total: "; // Course total is included as last element, precede with line break and string

            echo $child["id"]; // ID können wir dann zu unserer Kompetenz mappen um die verschiedenen Kompetenzpunkte für dieses Element zu berechnen
            echo " (";
            echo $child["itemname"];
            echo ") = ";
            echo $child["graderaw"]; // Gewichtung dieser Aufgabe auf eine Kompetenz müssen wir speichern und mappen

            echo "<br>";
        }
    }
}


// Print footer
echo $OUTPUT->footer();