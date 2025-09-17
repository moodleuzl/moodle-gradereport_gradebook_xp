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
 * Provides utility functions for managing competencies, activities, and their connections within the grading system.
 *
 * Functions include retrieving, inserting, updating, and deleting competencies, handling activity connections,
 * and recursive operations for competency hierarchies.
 *
 * @package    gradereport_gb_xp_admin
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Retrieves the available parent options for a select element excluding the current record and its descendants.
 *
 * @param int|null $currentrecordid The ID of the current record. Pass null if no record ID is available.
 * @return array An array of parent options for the select element.
 *
 * @package gradereport_gb_xp_admin
 */
function get_available_parents($currentrecordid) {
    global $COURSE, $DB;

    $courseid = $COURSE->id;

    // Fetch all competencies in course.
    $all = $DB->get_records('gradereport_gb_xp_admin_competencies', ['courseid' => $courseid]);

    if (empty($currentrecordid)) {
        return $all;
    }

    // Build a set of descendants (and the node itself) to exclude to avoid cycles.
    $excluded = array_fill_keys(get_descendants_ids($currentrecordid), true);
    $excluded[$currentrecordid] = true;

    // Filter out excluded ids.
    $filtered = [];
    foreach ($all as $id => $rec) {
        if (!isset($excluded[$id])) {
            $filtered[$id] = $rec;
        }
    }

    return $filtered;
}

/**
 * Retrieves a competency's details based on its ID.
 *
 * @param int $id The ID of the competency to retrieve.
 * @return array|false An associative array containing the competency's details if found, false otherwise.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_competency($id) {
    global $DB;
    return $DB->get_record('gradereport_gb_xp_admin_competencies', ['id' => $id]);
}

/**
 * Retrieves all competencies for a specified course or the current course.
 *
 * @param int|null $courseid Optional. Specify another course ID or pass null for the current course.
 * @return array An associative array containing competencies of the specified course, or an empty array if none are found.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_all_competencies($courseid = null) {
    global $COURSE, $DB;
    if (is_null($courseid)) {
        $courseid = $COURSE->id;
    }
    return $DB->get_records('gradereport_gb_xp_admin_competencies', ['courseid' => $courseid], 'id ASC');
}

/**
 * Retrieves direct children of a given competency by its ID.
 *
 * @param int $id The ID of the parent competency.
 * @return array An array of direct children records of the specified competency.
 * @package gradereport_gb_xp_admin
 */
function get_direct_children($id) {
    global $DB;
    // Join relations to fetch child competency records.
    $sql = "SELECT c.*
              FROM {gradereport_gb_xp_admin_relations} r
              JOIN {gradereport_gb_xp_admin_competencies} c ON c.id = r.childid
             WHERE r.parentid = ?
             ORDER BY c.id ASC";
    return $DB->get_records_sql($sql, [$id]);
}

/**
 * Return all direct parents of a competency.
 * @param int $childid
 * @return array of competency records
 */
function get_parents($childid) {
    global $DB;
    $sql = "SELECT c.*
              FROM {gradereport_gb_xp_admin_relations} r
              JOIN {gradereport_gb_xp_admin_competencies} c ON c.id = r.parentid
             WHERE r.childid = ?
             ORDER BY c.id ASC";
    return $DB->get_records_sql($sql, [$childid]);
}

/**
 * Return all parent ids of a competency.
 * @param int $childid
 * @return int[]
 */
function get_parent_ids($childid) {
    global $DB;
    return array_values($DB->get_records_menu('gradereport_gb_xp_admin_relations', ['childid' => $childid], '', 'parentid, parentid'));
}

/**
 * Set parents for a child competency (replace existing relations).
 * @param int $childid
 * @param int[] $parentids
 */
function set_parents($childid, $parentids) {
    global $DB;
    if (!is_array($parentids)) {
        $parentids = [];
    }
    // Remove existing relations for child.
    $DB->delete_records('gradereport_gb_xp_admin_relations', ['childid' => $childid]);
    // Insert new relations.
    foreach ($parentids as $pid) {
        $pid = (int)$pid;
        if ($pid > 0) {
            $DB->insert_record('gradereport_gb_xp_admin_relations', (object)[
                'parentid' => $pid,
                'childid' => $childid,
            ]);
        }
    }
}

/**
 * Get competencies with no parents (roots) in a course.
 * @param int $courseid
 * @return array of competency records
 */
function get_root_competencies($courseid) {
    global $DB;
    $sql = "SELECT c.*
              FROM {gradereport_gb_xp_admin_competencies} c
         LEFT JOIN {gradereport_gb_xp_admin_relations} r ON r.childid = c.id
             WHERE c.courseid = ? AND r.childid IS NULL
             ORDER BY c.id ASC";
    return $DB->get_records_sql($sql, [$courseid]);
}

/**
 * Get all descendant ids for a competency (via relations), avoiding cycles.
 * @param int $parentid
 * @return int[] array of ids
 */
function get_descendants_ids($parentid) {
    global $DB;
    $visited = [];
    $stack = [$parentid];
    while (!empty($stack)) {
        $current = array_pop($stack);
        // Get direct children ids.
        $childids = $DB->get_records_menu('gradereport_gb_xp_admin_relations', ['parentid' => $current], '', 'childid, childid');
        foreach ($childids as $cid) {
            if (!isset($visited[$cid])) {
                $visited[$cid] = true;
                $stack[] = $cid;
            }
        }
    }
    return array_map('intval', array_keys($visited));
}

/**
 * Updates an existing competency in the database.
 *
 * @param stdClass $competency The competency object containing updated data.
 * @package gradereport_gb_xp_admin
 */
function update_competency($competency) {
    global $DB;
    $DB->update_record('gradereport_gb_xp_admin_competencies', $competency);
}

/**
 * Inserts a new competency into the database.
 *
 * @param stdClass $competency The competency object to insert.
 * @package gradereport_gb_xp_admin
 */
function insert_competency($competency) {
    global $DB;
    // Insert and return new id.
    return (int)$DB->insert_record('gradereport_gb_xp_admin_competencies', $competency, true);
}

/**
 * Deletes a competency from the database by its ID.
 *
 * @param int $id The ID of the competency to delete.
 * @package gradereport_gb_xp_admin
 */
function delete_competency($id) {
    global $DB;
    // Remove any relations involving this competency.
    $DB->delete_records('gradereport_gb_xp_admin_relations', ['parentid' => $id]);
    $DB->delete_records('gradereport_gb_xp_admin_relations', ['childid' => $id]);
    // Delete the competency record from the database.
    $DB->delete_records('gradereport_gb_xp_admin_competencies', ['id' => $id]);
}

/**
 * Retrieves all assignments of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all assignments visible to the user. Returns an empty array if none are found.
 * @package gradereport_gb_xp_admin
 */
function get_all_assignments($courseid) {
    global $DB;

    // Check if the 'assign' table exists in the database.
    if (!$DB->get_manager()->table_exists("assign")) {
        return []; // Return empty array if table doesn't exist.
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
    ", [$courseid]);

    return $assignments;
}

/**
 * Retrieves all quizzes of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all quizzes visible to the user. Returns an empty array if none are found.
 * @package gradereport_gb_xp_admin
 */
function get_all_quizzes($courseid) {
    global $DB;

    // Check if the 'quiz' table exists in the database.
    if (!$DB->get_manager()->table_exists("quiz")) {
        return []; // Return empty array if table doesn't exist.
    }

    // SQL query to get all quiz records for the specified course.
    $quizzes = $DB->get_records_sql("
        SELECT cm.id, cm.course, q.name, q.intro, 'quiz' AS module
        FROM {course_modules} cm
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {quiz} q ON cm.instance = q.id
        WHERE cm.course = ?
            AND m.name = 'quiz'
        ORDER BY cm.section, q.name
    ", [$courseid]);

    return $quizzes;
}

/**
 * Retrieves all virtual programming labs (VPLs) of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all VPLs visible to the user. Returns an empty array if none are found.
 * @package gradereport_gb_xp_admin
 */
function get_all_vpls($courseid) {
    global $DB;

    // Check if the 'vpl' table exists in the database.
    if (!$DB->get_manager()->table_exists("vpl")) {
        return []; // Return empty array if table doesn't exist.
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
    ", [$courseid]);

    return $vpls;
}

/**
 * Retrieves all activities (assignments, quizzes, VPLs) of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all activities (assignments, quizzes, VPLs) visible to the user. Returns an empty array if none are
 *     found.
 * @package gradereport_gb_xp_admin
 */
function get_all_activities($courseid) {
    // Merge all activities (assignments, quizzes, and VPLs) into a single array.
    $activities = array_merge(
        get_all_assignments($courseid),
        get_all_quizzes($courseid),
        get_all_vpls($courseid)
    );

    return $activities;
}

/**
 * Retrieves the connection between an activity and a competency based on their IDs.
 *
 * @param int $activityid The ID of the activity.
 * @param int $competencyid The ID of the competency.
 * @return stdClass|null The connection object if found, or null if not found.
 * @package gradereport_gb_xp_admin
 */
function get_connection($activityid, $competencyid) {
    global $DB;

    // Retrieve the matching record from the database based on activity and competency IDs.
    $connection =
        $DB->get_record("gradereport_gb_xp_admin_con", ["activityid" => $activityid, "competencyid" => $competencyid]);

    return $connection;
}

/**
 * Inserts a new connection between an activity and a competency into the database.
 *
 * @param stdClass $connection The connection object to insert.
 * @package gradereport_gb_xp_admin
 */
function insert_connection($connection) {
    global $DB;

    // Insert the new connection record into the database.
    $DB->insert_record('gradereport_gb_xp_admin_con', $connection);
}

/**
 * Updates an existing connection between an activity and a competency in the database.
 *
 * @param stdClass $connection The connection object to update.
 * @package gradereport_gb_xp_admin
 */
function update_connection($connection) {
    global $DB;

    // Update the connection record in the database.
    $DB->update_record("gradereport_gb_xp_admin_con", $connection);
}

/**
 * Deletes a connection between an activity and a competency from the database by its ID.
 *
 * @param int $id The ID of the connection to delete.
 * @package gradereport_gb_xp_admin
 */
function delete_connection($id) {
    global $DB;

    // Delete the connection record from the database.
    $DB->delete_records("gradereport_gb_xp_admin_con", ['id' => $id]);
}

/**
 * Retrieves all activity connections related to a specific competency.
 *
 * @param int $competencyid The ID of the competency.
 * @return array An array of connection records related to the specified competency.
 * @package gradereport_gb_xp_admin
 */
function get_connections($competencyid) {
    global $DB;

    // Retrieve all connections related to the specified competency.
    $connections = $DB->get_records("gradereport_gb_xp_admin_con", ["competencyid" => $competencyid]);

    return $connections;
}

/**
 * Retrieves activity details (name and level) associated with a competency and its sub-competencies recursively.
 *
 * @param int $competencyid The ID of the competency.
 * @return array An array of objects containing the activity ID, name, and level.
 * @package gradereport_gb_xp_admin
 */
function get_competency_activities($competencyid) {
    // Initialize an empty array to store activity details.
    $activitydetailsarray = [];

    // Retrieve all connections of the competency and its sub-competencies.
    $connections = get_connections_recursive($competencyid);

    // Iterate through the connections to retrieve activity details.
    foreach ($connections as $connection) {
        $activityid = $connection->activityid;
        $level = $connection->level;

        // Retrieve the activity details.
        $activitydetails = get_activity_details($activityid);
        if ($activitydetails) {
            // Add the level to the activity details.
            $activitydetails->level = $level;
            // Append the activity details to the array.
            $activitydetailsarray[] = $activitydetails;
        }
    }

    // Sort the activities by level in descending order.
    usort($activitydetailsarray, function($a, $b) {
        return $b->level - $a->level;
    });

    return $activitydetailsarray;
}

/**
 * Retrieves all connections of a competency and its sub-competencies recursively.
 *
 * @param int $competencyid The ID of the competency.
 * @return array An array of all connections related to the competency and its sub-competencies.
 * @package gradereport_gb_xp_admin
 */
function get_connections_recursive($competencyid) {
    // Initialize an empty array to store all connections.
    $allconnections = [];

    // Retrieve the connections for the current competency.
    $connections = get_connections($competencyid);

    // Merge the connections into the result array.
    $allconnections = array_merge($allconnections, $connections);

    // Recursively retrieve connections for child competencies.
    $children = get_direct_children($competencyid);
    foreach ($children as $child) {
        $allconnections = array_merge($allconnections, get_connections_recursive($child->id));
    }

    return $allconnections;
}

/**
 * Retrieves activity details (name and ID) based on the activity ID.
 *
 * @param int $activityid The ID of the activity.
 * @return stdClass|null The activity details object if found, or null if not found.
 * @package gradereport_gb_xp_admin
 */
function get_activity_details($activityid) {
    global $courseid;

    // Retrieve all activities of the course.
    $courseactivities = get_all_activities($courseid);

    // Iterate through the activities to find a matching ID.
    foreach ($courseactivities as $activity) {
        if ($activity->id == $activityid) {
            // Create an object to store the activity details.
            $activitydetails = new stdClass();
            $activitydetails->id = $activity->id;
            $activitydetails->name = $activity->name;
            return $activitydetails;
        }
    }
    return null;
}
