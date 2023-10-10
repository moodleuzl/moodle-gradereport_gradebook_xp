<?php

defined('MOODLE_INTERNAL') || die;

/**
 * Retrieves the parent options for a select element based on the provided record ID.
 *
 * @param int|null $currentRecordId The ID of the current record. Pass null if no record ID is available.
 * @return array An array of parent options for the select element.
 */
function get_available_parents($currentRecordId)
{
    global $COURSE, $DB;

    // SQL query to retrieve available parent options for the select element based on the current record's ID.
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
    $availableParents = $DB->get_records_sql($sql, $params); // Get parent records

    return $availableParents;
}

function get_competency($id)
{
    global $DB;

    $competency = $DB->get_record('gradereport_gradebook_xp_com', array('id' => $id));

    return $competency;
}

function get_all_competencies()
{
    global $COURSE, $DB;

    // Get all competencies of this course with all fields. Ignore if no records are found and just return an empty array
    $competencies = $DB->get_records("gradereport_gradebook_xp_com", array("courseid" => $COURSE->id), 'id ASC');


    return $competencies;
}

function get_direct_children($id)
{
    global $DB;

    $children_records = $DB->get_records('gradereport_gradebook_xp_com', array('parentid' => $id));

    return $children_records;
}

function get_all_children($id)
{
    global $DB;

    // Use a recursive SQL query to get all children records of the parent
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
    $children_records = $DB->get_records_sql($sql, $params);

    return $children_records;
}

function update_competency($competency)
{
    global $DB;

    $DB->update_record('gradereport_gradebook_xp_com', $competency);
}

function insert_competency($competency)
{
    global $DB;

    $DB->insert_record('gradereport_gradebook_xp_com', $competency);
}

function delete_competency($id)
{
    global $DB;

    $DB->delete_records('gradereport_gradebook_xp_com', array('id' => $id));
}

/**
 * Retrieves all assignments of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array containing all assignments of the course visible to the user. If nothing is found, an empty array is returned.
 */
function get_all_assignments($courseid)
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
function get_all_quizzes($courseid)
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
 * Retrieves all vpls of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array containing all quizzes of the course visible to the user. If nothing is found, an empty array is returned.
 */
function get_all_vpls($courseid)
{
    global $DB;
    if (!$DB->get_manager()->table_exists("vpl")) {
        return array();
    }

    $quizzes = $DB->get_records_sql("
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
 * Retrieves all activities (assignments and quizzes) of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array containing all activities (assignments and quizzes) of the course visible to the user. If nothing is found, an empty array is returned.
 */
function get_all_activities($courseid)
{
    // Merge the assignments and quizzes into a single array
    $activities = array_merge(
        get_all_assignments($courseid),
        get_all_quizzes($courseid)
    );

    return $activities;
}

// TODO: connections
function get_connection($assignmentid, $competencyid) {
    global $DB;

    // Attempt to find matching record in our table
    $connection = $DB->get_record("gradereport_gradebook_xp_con", array("assignmentid" => $assignmentid, "competencyid" => $competencyid));

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
 * @return array List of assignments connected to this competency
 */
function get_connections($competencyid)
{
    global $DB;

    $connections = $DB->get_records("gradereport_gradebook_xp_con", array("competencyid" => $competencyid));

    return $connections;
}