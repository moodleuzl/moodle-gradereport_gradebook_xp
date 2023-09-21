<?php

defined('MOODLE_INTERNAL') || die;

require_once $CFG->dirroot . '/grade/report/user/lib.php';
require_once $CFG->dirroot . '/grade/report/gradebook_xp/db_controller.php';

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function gradereport_gradebook_xp_extend_navigation_course($navigation, $course, $context)
{

    $url = new moodle_url('/grade/report/gradebook_xp/preferences.php', array('id' => $course->id));
    $name = get_string('pluginname', 'gradereport_gradebook_xp');
    $navigation->add($name, $url, navigation_node::TYPE_COURSE, null, null, new pix_icon('i/competencies', ''));
}

function setup_page($courseid, $capabililty = 'moodle/grade:view')
{
    global $PAGE, $DB, $context;
    // Set page URL and layout
    $url = new moodle_url('/grade/report/gradebook_xp/' . get_caller_filename(), array('id' => $courseid));
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

function get_caller_filename()
{
    $trace = debug_backtrace();
    $caller = $trace[1];
    return basename($caller['file']);
}

function debug($value)
{
    echo "<pre>";
//    var_dump($value);
    print_r($value);
    echo "</pre>";
}



/**
 * Add a competency to a course or overwrite an existing one with the same name
 * @param int $courseid ID of the course
 * @param string $name Name if the competency to add
 * @param string $description Description of the competency to add
 */
function grade_report_gradebook_xp_add_competency($courseid, $name, $description)
{
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
        update_competency($competency);

    } else {

        // Construct new competency array
        $competency = array(
            "courseid" => $courseid,
            "name" => $name,
            "description" => $description
        );

        // Insert new record
        insert_competency($competency);
    }
}

/**
 * Add a competency -> assignment connection or overwrite an existing one
 * @param int $courseid ID of the course
 * @param int $assignmentid ID of the assignment to connect
 * @param int $competencyid ID of the competency to connect
 * @param int $weight Weight of the competency for this assignment
 */
function grade_report_gradebook_xp_set_competency_connection($assignmentid, $competencyid, $weight)
{
    global $DB;

    // Attempt to find matching record in our table
    $table = "gradereport_gradebook_xp_con";
    $connection = get_connection($assignmentid, $competencyid);

    // Update or insert record
    if ($connection) {

        // Update value
        $connection->weight = $weight;

        // Update record with new competency
        update_connection($connection);

    } else {

        // Construct new competency array
        $connection = array(
            "assignmentid" => $assignmentid,
            "competencyid" => $competencyid,
            "weight" => $weight
        );

        // Insert new record
        insert_connection($connection);
    }
}

function get_grades() {
    // Get grades for all assignments of this course
    global $OUTPUT;
    if (!empty($course->showgrades)) {

        /// return tracking object
        $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'user', 'courseid'=>$course->id, 'userid'=>$userid));
        // Create a report instance
        $report = new grade_report_user($course->id, $gpr, $context, $userid, false); // viewasuser = false

        if ($report->fill_table()) { // Fill table with data of all assignments
            return $report->gradeitemsdata;
        }
    }
}
