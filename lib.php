<?php

defined('MOODLE_INTERNAL') || die;

require_once $CFG->dirroot . '/grade/report/user/lib.php';
require_once $CFG->dirroot . '/grade/report/gradebook_xp_admin/db_controller.php';

/**
 * Set up a page for the 'gradebook_xp' report in Moodle.
 *
 * This function sets the page URL, layout, and verifies user access before displaying the report.
 *
 * @param int $courseid The ID of the course for which the report is being generated.
 * @param string $capability The capability required to view the report. Defaults to 'moodle/grade:view'.
 *
 * @global moodle_page $PAGE The global page object.
 * @global moodle_database $DB The global database object.
 * @global context_course $context The global context object.
 *
 * @throws moodle_exception If the course ID is invalid.
 */
function gradereport_gradebook_xp_admin_setup_page($courseid, $capabililty = 'moodle/grade:manage')
{
    global $PAGE, $CFG, $DB, $context;
    // Set page URL and layout
    $url = new moodle_url('/grade/report/gradebook_xp_admin/' . gradereport_gradebook_xp_admin_get_caller_filename(), array('id' => $courseid));
    if ($courseid !== 0) {
        $url->param('id', $courseid);
    }
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('standard');

    $PAGE->requires->jquery();
    $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/grade/report/gradebook_xp_admin/js/change_active_tab.js'));

    // Verify user has access to course
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        print_error('invalidcourseid');
    }

    require_login($course);
    $context = context_course::instance($course->id);
    require_capability($capabililty, $context);    // TODO: Check if user has permission to view this page
    $PAGE->set_context($context);
}

/**
 * Get the filename of the calling script in the 'gradebook_xp' report context.
 *
 * This function retrieves the filename of the script that calls it within the context
 * of the 'gradebook_xp' report. It uses debug_backtrace to inspect the call stack.
 *
 * @return string The filename of the calling script.
 */
function gradereport_gradebook_xp_admin_get_caller_filename()
{
    $trace = debug_backtrace();
    $caller = $trace[1];
    return basename($caller['file']);
}

/**
 * Output a formatted and human-readable representation of a variable for debugging purposes.
 *
 * This function prints a preformatted and human-readable representation of the given variable
 * for debugging purposes. It uses print_r to display the variable's contents.
 *
 * @param mixed $value The variable to be debugged.
 *
 * @return void
 */
function grade_report_gradebook_xp_admin_debug($value)
{
    echo "<pre>";
//    var_dump($value);
    print_r($value);
    echo "</pre>";
}


/**
 * Add or update a competency entry for the 'gradebook_xp' report in Moodle.
 *
 * This function checks if a competency with the given name already exists for the specified course.
 * If it exists, the competency is updated with the new name and description. If not, a new competency
 * entry is inserted into the database.
 *
 * @param int $courseid The ID of the course for which the competency is being added or updated.
 * @param string $name The name of the competency.
 * @param string $description The description of the competency.
 *
 * @return void
 *
 * @throws dml_exception
 *
 * @global moodle_database $DB The global database object.
 *
 */
function gradereport_gradebook_xp_admin_add_competency($courseid, $name, $description)
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
 * Add a competency -> activity connection or overwrite an existing one
 * @param int $activityid ID of the activity to connect
 * @param int $competencyid ID of the competency to connect
 * @param int $weight Weight of the competency for this activity
 */
function gradereport_gradebook_xp_admin_set_competency_connection($activityid, $competencyid, $weight)
{
    global $DB;

    // Attempt to find matching record in our table
    $table = "gradereport_gradebook_xp_con";
    $connection = get_connection($activityid, $competencyid);

    // Update or insert record
    if ($connection) {

        // Update value
        $connection->weight = $weight;

        // Update record with new competency
        update_connection($connection);

    } else {

        // Construct new competency array
        $connection = array(
            "activityid" => $activityid,
            "competencyid" => $competencyid,
            "weight" => $weight
        );

        // Insert new record
        insert_connection($connection);
    }
}
