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
 * Provides utility functions for managing activity-competency connections.
 *
 * Functions include retrieving, inserting, updating, and deleting connections
 * between activities and competencies in the gradebook XP admin system.
 *
 * @package    gradereport_gb_xp_admin
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Retrieves the connection between an activity and a competency based on their IDs.
 *
 * @param int $activityid The ID of the activity.
 * @param int $competencyid The ID of the competency.
 * @return stdClass|false The connection object if found, or false if not found.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_connection($activityid, $competencyid) {
    global $DB;

    // Retrieve the matching record from the database based on activity and competency IDs.
    return $DB->get_record('gradereport_gb_xp_admin_connections', [
        'activityid' => $activityid,
        'competencyid' => $competencyid
    ]);
}

/**
 * Retrieves all activity connections related to a specific competency.
 *
 * @param int $competencyid The ID of the competency.
 * @return array An array of connection records related to the specified competency.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_connections_by_competency($competencyid) {
    global $DB;

    // Retrieve all connections related to the specified competency.
    return $DB->get_records('gradereport_gb_xp_admin_connections', ['competencyid' => $competencyid]);
}

/**
 * Retrieves all connections for activities in a specified course.
 *
 * @param int $courseid course ID.
 * @return array An array of all connections for the specified course.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_connections($courseid) {
    global $COURSE, $DB;

    // Get all connections for competencies in the specified course.
    $sql = "SELECT conn.*
              FROM {gradereport_gb_xp_admin_connections} conn
              JOIN {gradereport_gb_xp_admin_competencies} comp ON comp.id = conn.competencyid
             WHERE comp.courseid = ?
             ORDER BY conn.activityid ASC, conn.competencyid ASC";
    return $DB->get_records_sql($sql, [$courseid]);
}

/**
 * Inserts a new connection between an activity and a competency into the database.
 *
 * Checks if the activity-competency combination already exists. If it exists, returns the existing
 * connection ID. If not, creates a new connection and returns the new ID.
 *
 * @param stdClass $connection The connection object to insert.
 * @return int The ID of the existing or newly created connection.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function insert_connection($connection) {
    global $DB;

    // Check if this activity-competency combination already exists.
    $existing = $DB->get_record('gradereport_gb_xp_admin_connections', [
        'activityid' => $connection->activityid,
        'competencyid' => $connection->competencyid
    ]);

    if ($existing) {
        // TODO: Update the other connection values, use the update_connection function
        // Return existing connection ID.
        return (int)$existing->id;
    }

    // Insert new connection and return new ID.
    return (int)$DB->insert_record('gradereport_gb_xp_admin_connections', $connection, true);
}

/**
 * Updates an existing connection between an activity and a competency in the database.
 *
 * @param stdClass $connection The connection object to update.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function update_connection($connection) {
    global $DB;

    // Update the connection record in the database.
    $DB->update_record('gradereport_gb_xp_admin_connections', $connection);
}

/**
 * Deletes a connection between an activity and a competency from the database by its ID.
 *
 * @param int $id The ID of the connection to delete.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function delete_connection($id) {
    global $DB;

    // Delete the connection record from the database.
    $DB->delete_records('gradereport_gb_xp_admin_connections', ['id' => $id]);
}