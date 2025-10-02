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
 * Provides utility functions for managing competency relations.
 *
 * Functions include retrieving, inserting, updating, and deleting parent-child
 * relationships between competencies in the gradebook XP admin system.
 *
 * @package    gradereport_gb_xp_admin
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Retrieves a relation's details based on its ID.
 *
 * @param int $id The ID of the relation to retrieve.
 * @return array|false An associative array containing the relation's details if found, false otherwise.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_relation($id) {
    global $DB;
    return $DB->get_record('gradereport_gb_xp_admin_relations', ['id' => $id]);
}

/**
 * Retrieves all relations for a specified course or the current course.
 *
 * @param int|null $courseid Optional. Specify another course ID or pass null for the current course.
 * @return array An associative array containing relations of the specified course, or an empty array if none are found.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_relations($courseid = null) {
    global $COURSE, $DB;
    if (is_null($courseid)) {
        $courseid = $COURSE->id;
    }
    // Get all relations for competencies in the specified course.
    $sql = "SELECT r.*
              FROM {gradereport_gb_xp_admin_relations} r
              JOIN {gradereport_gb_xp_admin_competencies} c ON c.id = r.parentid
             WHERE c.courseid = ?
             ORDER BY r.parentid ASC, r.childid ASC";
    return $DB->get_records_sql($sql, [$courseid]);
}


/**
 * Inserts a new relation into the database.
 *
 * Checks if the parent-child combination already exists. If it exists, returns the existing
 * relation ID. If not, validates that the relation does not create circular dependencies,
 * then creates a new relation and returns the new ID.
 *
 * @param stdClass $relation The relation object to insert.
 * @return int The ID of the existing or newly created relation.
 * @throws dml_exception
 * @throws moodle_exception If the relation would create a circular dependency or self-reference.
 * @package gradereport_gb_xp_admin
 */
function insert_relation($relation) {
    global $DB;

    // Check if this parent-child combination already exists.
    $existing = $DB->get_record('gradereport_gb_xp_admin_relations', [
        'parentid' => $relation->parentid,
        'childid' => $relation->childid
    ]);

    if ($existing) {
        // Return existing relation ID.
        return (int)$existing->id;
    }

    // Validate that inserting this relation does not create a circular dependency.
    // Check if the proposed parent is already a descendant of the proposed child.
    if (is_descendant($relation->parentid, $relation->childid)) {
        throw new moodle_exception('circularrelation', 'gradereport_gb_xp_admin', '',
            'Cannot create relation: parent competency ' . $relation->parentid .
            ' is already a descendant of child competency ' . $relation->childid);
    }

    // Check if the proposed child is already an ancestor of the proposed parent.
    if (is_ancestor($relation->childid, $relation->parentid)) {
        throw new moodle_exception('circularrelation', 'gradereport_gb_xp_admin', '',
            'Cannot create relation: child competency ' . $relation->childid .
            ' is already an ancestor of parent competency ' . $relation->parentid);
    }

    // Prevent self-reference (competency being its own parent).
    if ($relation->parentid == $relation->childid) {
        throw new moodle_exception('selfrelation', 'gradereport_gb_xp_admin', '',
            'Cannot create relation: competency ' . $relation->parentid . ' cannot be its own parent');
    }

    // Insert new relation and return new ID.
    return (int)$DB->insert_record('gradereport_gb_xp_admin_relations', $relation, true);
}

/**
 * Deletes a relation from the database by its ID.
 *
 * @param int $id The ID of the relation to delete.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function delete_relation($id) {
    global $DB;
    // Delete the relation record from the database.
    $DB->delete_records('gradereport_gb_xp_admin_relations', ['id' => $id]);
}
