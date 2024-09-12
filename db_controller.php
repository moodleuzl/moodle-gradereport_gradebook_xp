<?php

// Prevent direct script access outside Moodle environment.
defined('MOODLE_INTERNAL') || die;

/**
 * Retrieves the available parent options for a select element excluding the current record and its descendants.
 *
 * @param int|null $currentRecordId The ID of the current record. Pass null if no record ID is available.
 * @return array An array of parent options for the select element.
 */
function get_available_parents($currentRecordId)
{
    global $COURSE, $DB;

    // Recursive SQL query to retrieve all available parent options excluding current record and its descendants.
    $sql = "WITH RECURSIVE item_descendants AS (
                SELECT id, parentid, name
                FROM {gradereport_gradebook_xp_com}
                WHERE id = ?
                UNION
                SELECT g.id, g.parentid, g.name
                FROM {gradereport_gradebook_xp_com} g
                JOIN item_descendants d ON g.parentid = d.id
            )
            SELECT id, name
            FROM {gradereport_gradebook_xp_com}
            WHERE courseid = ? AND id NOT IN (
                SELECT id FROM item_descendants UNION
                SELECT ? WHERE parentid IS NULL
            )
            AND id != ?
            ORDER BY id";

    $params = array($currentRecordId, $COURSE->id, $currentRecordId, $currentRecordId);
    $availableParents = $DB->get_records_sql($sql, $params); // Execute the query

    return $availableParents; // Return parent records
}

/**
 * Retrieves a competency's details based on its ID.
 *
 * @param int $id The ID of the competency to retrieve.
 * @return array|false An associative array containing the competency's details if found, false otherwise.
 * @throws dml_exception
 */
function get_competency($id)
{
    global $DB;

    // Retrieve competency record from the database.
    return $DB->get_record('gradereport_gradebook_xp_com', array('id' => $id));
}

/**
 * Retrieves all competencies for a specified course or the current course.
 *
 * @param int|null $courseId Optional. Specify another course ID or pass null for the current course.
 * @return array An associative array containing competencies of the specified course, or an empty array if none are found.
 * @throws dml_exception
 */
function get_all_competencies($courseId = null)
{
    global $COURSE, $DB;

    // Default to the current course if no course ID is provided.
    if (is_null($courseId)) {
        $courseId = $COURSE->id;
    }

    // Retrieve all competencies for the specified course, ordered by ID.
    return $DB->get_records("gradereport_gradebook_xp_com", array("courseid" => $courseId), 'id ASC');
}

function get_direct_children($id)
{
    global $DB;

    // Retrieve direct child records with the parent ID.
    $children_records = $DB->get_records('gradereport_gradebook_xp_com', array('parentid' => $id));

    return $children_records;
}

function get_all_children($id)
{
    global $DB;

    // Recursive SQL query to retrieve all descendant records of the specified parent.
    $sql = "WITH RECURSIVE item_descendants AS (
                SELECT id, parentid, name
                FROM {gradereport_gradebook_xp_com}
                WHERE id = ?
                UNION
                SELECT g.id, g.parentid, g.name
                FROM {gradereport_gradebook_xp_com} g
                JOIN item_descendants d ON g.parentid = d.id
            )
            SELECT id, name
            FROM item_descendants
            WHERE id != ?";

    $params = array($id, $id);
    $children_records = $DB->get_records_sql($sql, $params); // Execute the query

    return $children_records;
}

function update_competency($competency)
{
    global $DB;

    // Update the competency record in the database.
    $DB->update_record('gradereport_gradebook_xp_com', $competency);
}

function insert_competency($competency)
{
    global $DB;

    // Insert a new competency record into the database.
    $DB->insert_record('gradereport_gradebook_xp_com', $competency);
}

function delete_competency($id)
{
    global $DB;

    // Delete the competency record from the database.
    $DB->delete_records('gradereport_gradebook_xp_com', array('id' => $id));
}

/**
 * Retrieves all assignments of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all assignments visible to the user. Returns an empty array if none are found.
 */
function get_all_assignments($courseid)
{
    global $DB;

    // Check if the 'assign' table exists in the database.
    if (!$DB->get_manager()->table_exists("assign")) {
        return array(); // Return empty array if table doesn't exist.
    }

    // SQL query to get all assignment records for the specified course.
    $assignments = $DB->get_records_sql("
        SELECT cm.id, cm.course, a.name, a.intro, 'assign' AS module
        FROM {course_modules} cm
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {assign} a ON cm.instance = a.id
        WHERE cm.course = ?
            AND m.name = 'assign'
        ORDER BY cm.section, a.name
    ", array($courseid));

    return $assignments;
}

/**
 * Retrieves all quizzes of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all quizzes visible to the user. Returns an empty array if none are found.
 */
function get_all_quizzes($courseid)
{
    global $DB;

    // Check if the 'quiz' table exists in the database.
    if (!$DB->get_manager()->table_exists("quiz")) {
        return array(); // Return empty array if table doesn't exist.
    }

    $quizzes = $DB->get_records_sql("
        SELECT cm.id, cm.course, q.name, q.intro, 'quiz' AS module
        FROM {course_modules} cm
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {quiz} q ON cm.instance = q.id
        WHERE cm.course = ?
            AND m.name = 'quiz'
        ORDER BY cm.section, q.name
    ", array($courseid));

    return $quizzes;
}

/**
 * Retrieves all virtual programming labs (VPLs) of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all VPLs visible to the user. Returns an empty array if none are found.
 */
function get_all_vpls($courseid)
{
    global $DB;

    // Check if the 'vpl' table exists in the database.
    if (!$DB->get_manager()->table_exists("vpl")) {
        return array(); // Return empty array if table doesn't exist.
    }

    // SQL query to get all VPL records for the specified course.
    $vpls = $DB->get_records_sql("
        SELECT cm.id, cm.course, v.name, v.intro, 'vpl' AS module
        FROM {course_modules} cm
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {vpl} v ON cm.instance = v.id
        WHERE cm.course = ?
            AND m.name = 'vpl'
        ORDER BY cm.section, v.name
    ", array($courseid));

    return $quizzes;
}

/**
 * Retrieves all activities (assignments, quizzes, VPLs) of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all activities (assignments, quizzes, VPLs) visible to the user. Returns an empty array if none are found.
 */
function get_all_activities($courseid)
{
    // Merge all activities (assignments, quizzes, and VPLs) into a single array.
    $activities = array_merge(
        get_all_assignments($courseid),
        get_all_quizzes($courseid),
        get_all_vpls($courseid)
    );

    return $activities;
}

function get_connection($activityid, $competencyid) {
    global $DB;

    // Attempt to find matching record in our table
    $connection = $DB->get_record("gradereport_gradebook_xp_con", array("activityid" => $activityid, "competencyid" => $competencyid));

    return $connection;
}

function insert_connection($connection) {
    global $DB;

    $DB->insert_record('gradereport_gradebook_xp_con', $connection);
}

function update_connection($connection) {
    global $DB;

    $DB->update_record("gradereport_gradebook_xp_con", $connection);
}

function delete_connection($id) {
    global $DB;

    $DB->delete_records("gradereport_gradebook_xp_con", array('id' => $id));
}

/**
 * Get all competency connections of a competency
 * @param int $courseid ID of the course
 * @param int $competencyid ID of the competency
 * @return array List of activities connected to this competency
 */
function get_connections($competencyid)
{
    global $DB;

    $connections = $DB->get_records("gradereport_gradebook_xp_con", array("competencyid" => $competencyid));

    return $connections;
}
/**
 * Get all activity details (name and level) associated with a competency and its sub-competencies recursively.
 *
 * @param int $competencyid The ID of the competency.
 * @return array An array of objects containing the activity ID, name, and level.
 */
function get_competency_activities($competencyid) {
    global $DB, $courseid;

    // Initialize an empty array to store activity details
    $activityDetailsArray = array();

    // Get all connections of the specified competency and its sub-competencies recursively
    $connections = get_connections_recursive($competencyid);

    // Iterate over connections to get activity details
    foreach ($connections as $connection) {
        $activityId = $connection->activityid;
        $level = $connection->level;

        // Retrieve activity details from the appropriate table (assign, quiz, vpl, etc.)
        $activityDetails = get_activity_details($activityId);
        if ($activityDetails) {
            // Add level to the activity details
            $activityDetails->level = $level;
            // Add activity details to the array
            $activityDetailsArray[] = $activityDetails;
        }
    }

    // Sort the array by level in descending order
    usort($activityDetailsArray, function($a, $b) {
        return $b->level - $a->level;
    });

    return $activityDetailsArray;
}

/**
 * Get all connections of a competency and its sub-competencies recursively.
 *
 * @param int $competencyid The ID of the competency.
 * @return array An array containing all connections of the competency and its sub-competencies.
 */
function get_connections_recursive($competencyid) {
    global $DB;

    // Initialize an empty array to store connections
    $allConnections = array();

    // Get connections of the current competency
    $connections = get_connections($competencyid);

    // Add connections to the array
    $allConnections = array_merge($allConnections, $connections);

    // Recursively get connections of child competencies
    $children = get_direct_children($competencyid);
    foreach ($children as $child) {
        $allConnections = array_merge($allConnections, get_connections_recursive($child->id));
    }

    return $allConnections;
}

/**
 * Get activity details based on the activity ID.
 *
 * @param int $activityId The ID of the activity.
 * @return stdClass|null The details of the activity, or null if not found.
 */
function get_activity_details($activityId) {
    global $courseid;

    $course_activities = get_all_activities($courseid);
    foreach ($course_activities as $activity) {
        if ($activity->id == $activityId) {
            $activityDetails = new stdClass();
            $activityDetails->id = $activity->id;
            $activityDetails->name = $activity->name;
            return $activityDetails;
        }
    }
    return null;
}