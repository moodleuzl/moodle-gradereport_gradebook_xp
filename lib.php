<?php

defined('MOODLE_INTERNAL') || die;

require_once $CFG->dirroot.'/grade/report/user/lib.php';

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function gradereport_gradebook_xp_extend_navigation_course($navigation, $course, $context) {

    $url = new moodle_url('/grade/report/gradebook_xp/preferences.php', array('id' => $course->id));
    $name = get_string('pluginname', 'gradereport_gradebook_xp');
    $navigation->add($name, $url, navigation_node::TYPE_COURSE, null, null, new pix_icon('i/competencies', ''));
}

function setup_page($courseid, $capabililty='moodle/grade:view') {
    global $PAGE, $DB, $context;
    // Set page URL and layout
    $url = new moodle_url('/grade/report/gradebook_xp/'.get_caller_filename(), array('id' => $courseid));
    if ($courseid !== 0) {
        $url->param('id', $courseid);
    }
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('standard');

    // Verify user has access to course
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        print_error('invalidcourseid');
    }

    require_login($course);
    $context = context_course::instance($course->id);
    require_capability($capabililty, $context);    // TODO: Check if user has permission to view this page
}

function get_caller_filename() {
    $trace = debug_backtrace();
    $caller = $trace[1];
    return basename($caller['file']);
}

function debug($value) {
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
    die;
}

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

/**
 * Add a competency to a course or overwrite an existing one with the same name
 * @param int $courseid ID of the course
 * @param string $name Name if the competency to add
 * @param string $description Description of the competency to add
 */
function grade_report_gradebook_xp_add_competency($courseid, $name, $description) {
    global $DB;

    // Attempt to find matching record in our table
    $table = "gradereport_gradebook_xp_com";
    $competency = $DB->get_record($table, ["courseid" => $courseid, "name" => $name]);

    // Update or insert record
    if ($competency) {

        // Update values
        $competency->name = $name;
        $competency->description = $description;

        // Update record with new competency
        $DB->update_record($table, $competency);

    } else {

        // Construct new competency array
        $competency = array(
            "courseid" => $courseid,
            "name" => $name,
            "description" => $description
        );

        // Insert new record
        $DB->insert_record($table, $competency);
    }
}

/**
 * Get all competencies of a course
 * @param int $courseid ID of the course
 * @return array List of matching competencies
 */
function grade_report_gradebook_xp_get_competencies($courseid) {
    global $DB;

    // Get all competencies of this course with all fields. Ignore if no records are found and just return an empty array
    $competencies = $DB->get_records("gradereport_gradebook_xp_com", ["courseid" => $courseid]);

    return $competencies;
}

/**
 * Add a competency -> assignment connection or overwrite an existing one
 * @param int $courseid ID of the course
 * @param int $assignmentid ID of the assignment to connect
 * @param int $competencyid ID of the competency to connect
 * @param int $weight Weight of the competency for this assignment
 */
function grade_report_gradebook_xp_set_competency_connection($courseid, $assignmentid, $competencyid, $weight) {
    global $DB;

    // Attempt to find matching record in our table
    $table = "gradereport_gradebook_xp_con";
    $connection = $DB->get_record($table, ["courseid" => $courseid, "assignmentid" => $assignmentid, "competencyid" => $competencyid]);

    // Update or insert record
    if ($connection) {

        // Update value
        $connection->weight = $weight;

        // Update record with new competency
        $DB->update_record($table, $connection);

    } else {

        // Construct new competency array
        $connection = array(
            "courseid" => $courseid,
            "assignmentid" => $assignmentid,
            "competencyid" => $competencyid,
            "weight" => $weight
        );

        // Insert new record
        $DB->insert_record($table, $connection);
    }
}

/**
 * Get all competency connections of a competency
 * @param int $courseid ID of the course
 * @param int $competencyid ID of the competency
 * @return array List of assignments connected to this competency
 */
function grade_report_gradebook_xp_get_connections($courseid, $competencyid) {
    global $DB;

    $connections = $DB->get_records("gradereport_gradebook_xp_con", ["courseid" => $courseid, "competencyid" => $competencyid]);

    return $connections;
}
