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
 * Provides utility functions for managing course activities.
 *
 * Functions include retrieving assignments, quizzes, VPLs and other activities
 * from Moodle courses for the gradebook XP admin system.
 *
 * @package    gradereport_gb_xp_admin
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Retrieves all assignments of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all assignments visible to the user. Returns an empty array if none are found.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_all_assignments($courseid) {
    global $DB;

    // Check if the 'assign' table exists in the database.
    if (!$DB->get_manager()->table_exists('assign')) {
        return []; // Return empty array if table doesn't exist.
    }

    // SQL query to get all assignment records for the specified course.
    return $DB->get_records_sql("
        SELECT cm.id, cm.course, cm.section, s.name AS section_name, a.name, a.intro, 'assign' AS module
        FROM {course_modules} cm
        INNER JOIN {course_sections} s ON cm.section = s.id
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {assign} a ON cm.instance = a.id
        WHERE cm.course = ?
            AND m.name = 'assign'
        ORDER BY cm.section, a.name
    ", [$courseid]);
}

/**
 * Retrieves all quizzes of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all quizzes visible to the user. Returns an empty array if none are found.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_all_quizzes($courseid) {
    global $DB;

    // Check if the 'quiz' table exists in the database.
    if (!$DB->get_manager()->table_exists('quiz')) {
        return []; // Return empty array if table doesn't exist.
    }

    // SQL query to get all quiz records for the specified course.
    return $DB->get_records_sql("
        SELECT cm.id, cm.course, cm.section, s.name AS section_name, q.name, q.intro, 'quiz' AS module
        FROM {course_modules} cm
        INNER JOIN {course_sections} s ON cm.section = s.id
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {quiz} q ON cm.instance = q.id
        WHERE cm.course = ?
            AND m.name = 'quiz'
        ORDER BY cm.section, q.name
    ", [$courseid]);
}

/**
 * Retrieves all virtual programming labs (VPLs) of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all VPLs visible to the user. Returns an empty array if none are found.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_all_vpls($courseid) {
    global $DB;

    // Check if the 'vpl' table exists in the database.
    if (!$DB->get_manager()->table_exists('vpl')) {
        return []; // Return empty array if table doesn't exist.
    }

    // SQL query to get all VPL records for the specified course.
    return $DB->get_records_sql("
        SELECT cm.id, cm.course, cm.section, s.name AS section_name, v.name, v.intro, 'vpl' AS module
        FROM {course_modules} cm
        INNER JOIN {course_sections} s ON cm.section = s.id
        INNER JOIN {modules} m ON cm.module = m.id
        INNER JOIN {vpl} v ON cm.instance = v.id
        WHERE cm.course = ?
            AND m.name = 'vpl'
        ORDER BY cm.section, v.name
    ", [$courseid]);
}

/**
 * Retrieves all activities (assignments, quizzes, VPLs) of a course visible to the user.
 *
 * @param int $courseid The ID of the course.
 * @return array An array of all activities (assignments, quizzes, VPLs) visible to the user.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_all_activities($courseid) {
    // Merge all activities (assignments, quizzes, and VPLs) into a single array.
    return array_merge(
        get_all_assignments($courseid),
        get_all_quizzes($courseid),
        get_all_vpls($courseid)
    );
}
