<?php

defined('MOODLE_INTERNAL') || die;

require_once $CFG->dirroot . '/grade/report/user/lib.php';

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
 * Retrieves all assignments of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array containing all assignments of the course visible to the user. If nothing is found, an empty array is returned.
 */
function grade_report_gradebook_xp_get_assignments($courseid)
{
    global $DB;

    $assignments = $DB->get_records_sql("
        SELECT cm.id, cm.course, a.name, a.intro, 'assign' AS module
        FROM {course_modules} cm
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {assign} a ON cm.instance = a.id
        WHERE cm.course = ?
            AND m.name = 'assign'
        ORDER BY cm.section
    ", array($courseid));

    return $assignments;
}

/**
 * Retrieves all quizzes of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array containing all quizzes of the course visible to the user. If nothing is found, an empty array is returned.
 */
function grade_report_gradebook_xp_get_quizzes($courseid)
{
    global $DB;

    $quizzes = $DB->get_records_sql("
        SELECT cm.id, cm.course, q.name, q.intro, 'quiz' AS module
        FROM {course_modules} cm
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {quiz} q ON cm.instance = q.id
        WHERE cm.course = ?
            AND m.name = 'quiz'
        ORDER BY cm.section
    ", array($courseid));

    return $quizzes;
}

/**
 * Retrieves all activities (assignments and quizzes) of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array containing all activities (assignments and quizzes) of the course visible to the user. If nothing is found, an empty array is returned.
 */
function grade_report_gradebook_xp_get_activities($courseid)
{
//    // Merge the assignments and quizzes into a single array
//    $activities = array_merge(
//        grade_report_gradebook_xp_get_assignments($courseid),
//        grade_report_gradebook_xp_get_quizzes($courseid)
//    );

    global $DB;

    $activities = $DB->get_records_sql("
        SELECT cm.id, cm.course, a.name, a.intro, 'assign' AS module, cm.section
        FROM {course_modules} cm
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {assign} a ON cm.instance = a.id
        WHERE cm.course = ?
            AND m.name = 'assign'
        UNION ALL
        SELECT cm.id, cm.course, q.name, q.intro, 'quiz' AS module, cm.section
        FROM {course_modules} cm
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {quiz} q ON cm.instance = q.id
        WHERE cm.course = ?
            AND m.name = 'quiz'
        ORDER BY section, id
    ", array($courseid, $courseid));


    return $activities;
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
function grade_report_gradebook_xp_get_competencies($courseid)
{
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
function grade_report_gradebook_xp_set_competency_connection($assignmentid, $competencyid, $weight)
{
    global $DB;

    // Attempt to find matching record in our table
    $table = "gradereport_gradebook_xp_con";
    $connection = $DB->get_record($table, ["assignmentid" => $assignmentid, "competencyid" => $competencyid]);

    // Update or insert record
    if ($connection) {

        // Update value
        $connection->weight = $weight;

        // Update record with new competency
        $DB->update_record($table, $connection);

    } else {

        // Construct new competency array
        $connection = array(
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
function grade_report_gradebook_xp_get_connections($competencyid)
{
    global $DB;

    $connections = $DB->get_records("gradereport_gradebook_xp_con", ["competencyid" => $competencyid]);

    return $connections;
}
