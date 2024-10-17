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
 * Retrieves the available parent options for a select element excluding the current record and its descendants.
 *
 * @param int|null $currentRecordId The ID of the current record. Pass null if no record ID is available.
 * @return array An array of parent options for the select element.
 * @package gradereport_gradebook_xp_admin
 */
function get_available_parents($currentrecordid) {
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
            WHERE courseid = ?
            AND id NOT IN (
                SELECT id FROM item_descendants UNION
                SELECT ? WHERE parentid IS NULL
            )
            AND id != ?
            ORDER BY id";

    $params = [$currentrecordid, $COURSE->id, $currentrecordid, $currentrecordid];
    $availableparents = $DB->get_records_sql($sql, $params); // Execute the query.

    return $availableparents; // Return parent records.
}

/**
 * Retrieves a competency's details based on its ID.
 *
 * @param int $id The ID of the competency to retrieve.
 * @return array|false An associative array containing the competency's details if found, false otherwise.
 * @throws dml_exception
 * @package gradereport_gradebook_xp_admin
 */
function get_competency($id) {
    global $DB;

    // Retrieve competency record from the database.
    return $DB->get_record('gradereport_gradebook_xp_com', ['id' => $id]);
}

/**
 * Retrieves all competencies for a specified course or the current course.
 *
 * @param int|null $courseId Optional. Specify another course ID or pass null for the current course.
 * @return array An associative array containing competencies of the specified course, or an empty array if none are found.
 * @throws dml_exception
 * @package gradereport_gradebook_xp_admin
 */
function get_all_competencies($courseid = null) {
    global $COURSE, $DB;

    // Default to the current course if no course ID is provided.
    if (is_null($courseid)) {
        $courseid = $COURSE->id;
    }

    // Retrieve all competencies for the specified course, ordered by ID.
    return $DB->get_records("gradereport_gradebook_xp_com", ["courseid" => $courseid], 'id ASC');
}

/**
 * Retrieves direct children of a given competency by its ID.
 *
 * @param int $id The ID of the parent competency.
 * @return array An array of direct children records of the specified competency.
 * @package gradereport_gradebook_xp_admin
 */
function get_direct_children($id) {
    global $DB;

    // Retrieve direct child records with the parent ID.
    $childrenrecords = $DB->get_records('gradereport_gradebook_xp_com', ['parentid' => $id]);

    return $childrenrecords;
}

/**
 * Retrieves all descendants (children, grandchildren, etc.) of a given competency by its ID.
 *
 * @param int $id The ID of the parent competency.
 * @return array An array of all descendant records of the specified competency.
 * @package gradereport_gradebook_xp_admin
 */
function get_all_children($id) {
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

    $params = [$id, $id];
    $childrenrecords = $DB->get_records_sql($sql, $params); // Execute the query.

    return $childrenrecords;
}

/**
 * Updates an existing competency in the database.
 *
 * @param stdClass $competency The competency object containing updated data.
 * @package gradereport_gradebook_xp_admin
 */
function update_competency($competency) {
    global $DB;

    // Update the competency record in the database.
    $DB->update_record('gradereport_gradebook_xp_com', $competency);
}

/**
 * Inserts a new competency into the database.
 *
 * @param stdClass $competency The competency object to insert.
 * @package gradereport_gradebook_xp_admin
 */
function insert_competency($competency) {
    global $DB;

    // Insert a new competency record into the database.
    $DB->insert_record('gradereport_gradebook_xp_com', $competency);
}

/**
 * Deletes a competency from the database by its ID.
 *
 * @param int $id The ID of the competency to delete.
 * @package gradereport_gradebook_xp_admin
 */
function delete_competency($id) {
    global $DB;

    // Delete the competency record from the database.
    $DB->delete_records('gradereport_gradebook_xp_com', ['id' => $id]);
}

/**
 * Retrieves all assignments of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all assignments visible to the user. Returns an empty array if none are found.
 * @package gradereport_gradebook_xp_admin
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
 * @package gradereport_gradebook_xp_admin
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
 * @package gradereport_gradebook_xp_admin
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
 * @package gradereport_gradebook_xp_admin
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
 * @package gradereport_gradebook_xp_admin
 */
function get_connection($activityid, $competencyid) {
    global $DB;

    // Retrieve the matching record from the database based on activity and competency IDs.
    $connection =
        $DB->get_record("gradereport_gradebook_xp_con", ["activityid" => $activityid, "competencyid" => $competencyid]);

    return $connection;
}

/**
 * Inserts a new connection between an activity and a competency into the database.
 *
 * @param stdClass $connection The connection object to insert.
 * @package gradereport_gradebook_xp_admin
 */
function insert_connection($connection) {
    global $DB;

    // Insert the new connection record into the database.
    $DB->insert_record('gradereport_gradebook_xp_con', $connection);
}

/**
 * Updates an existing connection between an activity and a competency in the database.
 *
 * @param stdClass $connection The connection object to update.
 * @package gradereport_gradebook_xp_admin
 */
function update_connection($connection) {
    global $DB;

    // Update the connection record in the database.
    $DB->update_record("gradereport_gradebook_xp_con", $connection);
}

/**
 * Deletes a connection between an activity and a competency from the database by its ID.
 *
 * @param int $id The ID of the connection to delete.
 * @package gradereport_gradebook_xp_admin
 */
function delete_connection($id) {
    global $DB;

    // Delete the connection record from the database.
    $DB->delete_records("gradereport_gradebook_xp_con", ['id' => $id]);
}

/**
 * Retrieves all activity connections related to a specific competency.
 *
 * @param int $competencyid The ID of the competency.
 * @return array An array of connection records related to the specified competency.
 * @package gradereport_gradebook_xp_admin
 */
function get_connections($competencyid) {
    global $DB;

    // Retrieve all connections related to the specified competency.
    $connections = $DB->get_records("gradereport_gradebook_xp_con", ["competencyid" => $competencyid]);

    return $connections;
}

/**
 * Retrieves activity details (name and level) associated with a competency and its sub-competencies recursively.
 *
 * @param int $competencyid The ID of the competency.
 * @return array An array of objects containing the activity ID, name, and level.
 * @package gradereport_gradebook_xp_admin
 */
function get_competency_activities($competencyid) {
    global $DB, $courseid;

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
 * @package gradereport_gradebook_xp_admin
 */
function get_connections_recursive($competencyid) {
    global $DB;

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
 * @param int $activityId The ID of the activity.
 * @return stdClass|null The activity details object if found, or null if not found.
 * @package gradereport_gradebook_xp_admin
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
