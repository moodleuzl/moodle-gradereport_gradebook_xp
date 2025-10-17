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
 * Provides utility functions for managing competencies and their hierarchical relationships.
 *
 * Functions include retrieving, inserting, updating, and deleting competencies,
 * as well as managing parent-child relationships and hierarchy traversal.
 *
 * @package    gradereport_gradebook_xp
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/relations.php');

/**
 * Retrieves a competency's details based on its ID.
 *
 * @param int $id The ID of the competency to retrieve.
 * @return array|false An associative array containing the competency's details if found, false otherwise.
 * @throws dml_exception
 * @package gradereport_gradebook_xp
 */
function get_competency($id) {
    global $DB;
    return $DB->get_record('gradereport_gradebook_xp_competencies', ['id' => $id]);
}


/**
 * Retrieves all competencies for a specified course or the current course.
 *
 * @param int|null $courseid Optional. Specify another course ID or pass null for the current course.
 * @return array An associative array containing competencies of the specified course, or an empty array if none are found.
 * @throws dml_exception
 * @package gradereport_gradebook_xp
 */
function get_competencies($courseid = null) {
    global $COURSE, $DB;
    if (is_null($courseid)) {
        $courseid = $COURSE->id;
    }
    return $DB->get_records('gradereport_gradebook_xp_competencies', ['courseid' => $courseid], 'id ASC');
}

/**
 * Updates an existing competency in the database.
 *
 * @param stdClass $competency The competency object containing updated data.
 * @throws dml_exception
 * @package gradereport_gradebook_xp
 */
function update_competency($competency) {
    global $DB;
    $DB->update_record('gradereport_gradebook_xp_competencies', $competency);
}

/**
 * Inserts a new competency into the database.
 *
 * @param stdClass $competency The competency object to insert.
 * @return int The ID of the newly created competency.
 * @throws dml_exception
 * @package gradereport_gradebook_xp
 */
function insert_competency($competency) {
    global $DB;
    // Insert and return new id.
    return (int)$DB->insert_record('gradereport_gradebook_xp_competencies', $competency, true);
}

/**
 * Deletes a competency from the database by its ID.
 *
 * @param int $id The ID of the competency to delete.
 * @throws dml_exception
 * @package gradereport_gradebook_xp
 */
function delete_competency($id) {
    global $DB;
    // Remove any relations involving this competency.
    $DB->delete_records('gradereport_gradebook_xp_relations', ['parentid' => $id]);
    $DB->delete_records('gradereport_gradebook_xp_relations', ['childid' => $id]);
    // TODO: delete connections with this competency
    // Delete the competency record from the database.
    $DB->delete_records('gradereport_gradebook_xp_competencies', ['id' => $id]);
}

/**
 * Retrieves all direct children of a given competency by its ID.
 *
 * @param int $id The ID of the parent competency.
 * @return array An array of direct children records of the specified competency.
 * @throws dml_exception
 * @package gradereport_gradebook_xp
 */
function get_competency_children($id) {
    global $DB;
    // Join relations to fetch child competency records.
    $sql = "SELECT c.*
              FROM {gradereport_gradebook_xp_relations} r
              JOIN {gradereport_gradebook_xp_competencies} c ON c.id = r.childid
             WHERE r.parentid = ?
             ORDER BY c.id ASC";
    return $DB->get_records_sql($sql, [$id]);
}

/**
 * Return all direct parents of a given competency by its ID.
 *
 * @param int $id The ID of the child competency.
 * @return array An array of direct parent competency records.
 * @throws dml_exception
 * @package gradereport_gradebook_xp
 */
function get_competency_parents($id) {
    global $DB;
    $sql = "SELECT c.*
              FROM {gradereport_gradebook_xp_relations} r
              JOIN {gradereport_gradebook_xp_competencies} c ON c.id = r.parentid
             WHERE r.childid = ?
             ORDER BY c.id ASC";
    return $DB->get_records_sql($sql, [$id]);
}
