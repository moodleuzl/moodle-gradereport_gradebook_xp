<?php

/*
 * File: lib.php
 * Project: gradebook_xp
 * Created Date: 21.03.2023 12:31:37
 * Author: DominikMa, 3urobeat, thePulpo
 * 
 * Last Modified: 21.03.2023 13:01:47
 * Modified By: 3urobeat
 */


/**
 * Gets all activities of a course visible to the user
 * @param context_course $context
 * @param object $course The course object to look at
 * @param int $userid The id of the user
 * @return array Returns an array containing all activities of this course visible to this user. If nothing was found an empty array is returned.
 */
function grade_report_gradebook_xp_get_course_activities($context, $course, $userid) {
    $return_data = [];
    
    if (!empty($course->showgrades)) { // TODO: Do we need this check? Does it check for user perms, if the course allows viewing grades or something else?

        // Get tracking object
        $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'user', 'courseid'=>$course->id, 'userid'=>$userid));
        // Create a report instance
        $report = new grade_report_user($course->id, $gpr, $context, $userid, false); // viewasuser = false
        
        if ($report->fill_table()) { // Fill table with data of all assignments
    
            // Add everything grade related we've got
            $return_data = $report->gradeitemsdata;

        }

    }

    return $return_data;

}