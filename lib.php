<?php

/*
 * File: lib.php
 * Project: gradebook_xp
 * Created Date: 21.03.2023 12:31:37
 * Author: DominikMa, 3urobeat, thePulpo
 * 
 * Last Modified: 21.03.2023 12:55:34
 * Modified By: 3urobeat
 */


/**
 * Gets all activities of a course visible to the user
 * @param context_course $context
 * @param object $course The course 
 * @param int $userid The id of the user
 * @return object Returns an object containing all activities of the course visible to the user
 */
function grade_report_gradebook_xp_get_course_activities($context, $course, $userid) {
    
    if (!empty($course->showgrades)) { // TODO: Do we need this check? Does it check for user perms, if the course allows viewing grades or something else?

        // Get tracking object
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

}