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
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
 * Add a competency to activity connection or overwrite an existing one.
 *
 * @param int $activityid ID of the activity to connect.
 * @param int $competencyid ID of the competency to connect.
 * @param int $level Level of the competency for this activity.
 *
 * @return void
 */
function gradereport_gb_xp_admin_set_competency_connection($activityid, $competencyid, $level) {
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
    // Build map id -> record.
    $map = [];
    foreach ($competencies as $c) {
        $map[$c->id] = $c;
    }

    // Build children adjacency via relations helper.
    $childrenbyparent = [];
    foreach ($competencies as $c) {
        // For each competency, get its parents to populate reverse map.
        $pids = get_parent_ids($c->id);
        if (empty($pids)) {
            $pids = [0]; // Treat as root when no parents.
        }
        foreach ($pids as $pid) {
            if (!isset($childrenbyparent[$pid])) {
                $childrenbyparent[$pid] = [];
            }
            $childrenbyparent[$pid][] = $c->id;
        }
    }

    // Recursive builder with cycle guard.
    $building = [];
    $build = function($pid) use (&$build, &$childrenbyparent, &$map, &$building) {
        $result = [];
        $children = $childrenbyparent[$pid] ?? [];
        foreach ($children as $cid) {
            if (isset($building[$cid])) {
                continue; // Avoid cycles.
            }
            $building[$cid] = true;

            $node = clone $map[$cid];
            $node->subCompetencies = $build($cid);
            $node->connections = get_connections($cid);
            $result[] = $node;

            unset($building[$cid]);
        }
        return $result;
    };

    return $build($parentid);
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

    $competencies = get_all_competencies($courseid);
    return get_hierarchy($competencies, $competencyid);
}
