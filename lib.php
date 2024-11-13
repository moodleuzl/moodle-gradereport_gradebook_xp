<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library functions for managing competencies, activity connections, and page setup for the gb_xp_admin report.
 *
 * @package    gradereport_gb_xp_admin
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/grade/report/user/lib.php');
require_once($CFG->dirroot . '/grade/report/gb_xp_admin/db_controller.php');

/**
 * Set up a page for the 'gb_xp' report in Moodle.
 *
 * This function sets the page URL, layout, and verifies user access before displaying the report.
 *
 * @param int $courseid The ID of the course for which the report is being generated.
 * @param string $capability The capability required to view the report. Defaults to 'moodle/grade:manage'.
 *
 * @throws moodle_exception If the course ID is invalid.
 */
function gradereport_gb_xp_admin_setup_page($courseid, $capability = 'moodle/grade:manage') {
    global $PAGE, $CFG, $DB, $context;

    // Set page URL and layout.
    $url = new moodle_url('/grade/report/gb_xp_admin/' . gradereport_gb_xp_admin_get_caller_filename(),
        ['id' => $courseid]);
    if ($courseid !== 0) {
        $url->param('id', $courseid);
    }
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('standard');

    $PAGE->requires->jquery();
    $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/grade/report/gb_xp_admin/js/change_active_tab.js'));

    if (!$course = $DB->get_record('course', ['id' => $courseid])) {
        throw new \moodle_exception('invalidcourseid');
    }

    $context = context_course::instance($course->id);
    require_capability($capability, $context);
    $PAGE->set_context($context);
}

/**
 * Get the filename of the calling script in the 'gb_xp' report context.
 *
 * This function retrieves the filename of the script that calls it within the context
 * of the 'gb_xp' report. It uses debug_backtrace to inspect the call stack.
 *
 * @return string The filename of the calling script.
 */
function gradereport_gb_xp_admin_get_caller_filename() {
    $trace = debug_backtrace();
    $caller = $trace[1];
    return basename($caller['file']);
}


/**
 * Add or update a competency entry for the 'gb_xp' report in Moodle.
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
 */
function gradereport_gb_xp_admin_add_competency($courseid, $name, $description) {
    global $DB;

    // Attempt to find matching record in our table.
    $table = "gradereport_gb_xp_admin_com";
    $competency = $DB->get_record($table, ["courseid" => $courseid, "name" => $name]);

    // Update or insert record.
    if ($competency) {

        // Update values.
        $competency->name = $name;
        $competency->description = $description;

        // Update record with new competency.
        update_competency($competency);

    } else {

        // Construct new competency array.
        $competency = [
            "courseid" => $courseid,
            "name" => $name,
            "description" => $description,
        ];

        // Insert new record.
        insert_competency($competency);
    }
}

/**
 * Add a competency to activity connection or overwrite an existing one.
 *
 * @param int $activityid ID of the activity to connect.
 * @param int $competencyid ID of the competency to connect.
 * @param int $level Level of the competency for this activity.
 *
 * @return void
 */
function gradereport_gb_xp_admin_set_competency_connection($activityid, $competencyid, $level) {
    global $DB;

    // Attempt to find matching record in our table.
    $table = "gradereport_gb_xp_admin_con";
    $connection = get_connection($activityid, $competencyid);

    // Update or insert record.
    if ($connection) {

        // Update value.
        $connection->level = $level;

        // Update record with new competency.
        update_connection($connection);

    } else {

        // Construct new competency array.
        $connection = [
            "activityid" => $activityid,
            "competencyid" => $competencyid,
            "level" => $level,
        ];

        // Insert new record.
        insert_connection($connection);
    }
}

/**
 * Recursively constructs a hierarchical structure of competencies.
 *
 * @param array $competencies An array of competency objects.
 * @param int $parentid The parent ID to start building the hierarchy from. Defaults to 0.
 *
 * @return array The hierarchical structure of competencies.
 */
function get_hierarchy($competencies, $parentid = 0): array {
    // Initialize an empty array to store the hierarchy.
    $hierarchy = [];

    // Iterate through each competency.
    foreach ($competencies as $competency) {
        // Check if the competency's parent ID matches the given parent ID.
        if ($competency->parentid == $parentid) {
            // Recursively get subcompetencies
            $subcompetencies = get_hierarchy($competencies, $competency->id);
            // Ensure subcompetencies is an indexed array
            $competency->subCompetencies = array_values($subcompetencies);

            // Retrieve connections for the current competency.
            $competency->connections = get_connections($competency->id);

            // Add the competency to the hierarchy array.
            $hierarchy[] = $competency;
        }
    }

    return $hierarchy;
}

/**
 * Get all competencies hierarchy.
 *
 * This function retrieves the hierarchical structure of all competencies for a specified course.
 * It uses recursion to build the competency tree and returns an array representing the hierarchy.
 *
 * @param int|null $courseid The ID of the course whose competencies are being retrieved. Defaults to null,
 *                           which means the current course will be used.
 * @param int $competencyid The ID of the parent competency to start building the hierarchy from. Defaults to 0.
 *
 * @return array An array representing the hierarchical structure of all competencies for the specified course.
 * @throws dml_exception
 */
function get_competencies_hierarchy(int $courseid = null, int $competencyid = 0): array {
    if (is_null($courseid)) {
        global $COURSE;
        $courseid = $COURSE->id;
    }

    // Get all competencies of the specified course with their parent IDs.
    $competencies = get_all_competencies($courseid);

    // Build hierarchy.
    return get_hierarchy($competencies, $competencyid);
}
