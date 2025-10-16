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
 * Competency hierarchy helper functions for the Gradebook XP Admin report.
 *
 * This file contains helper functions for retrieving, traversing, and validating
 * hierarchical relationships between competencies within a Moodle course.
 *
 * @package    gradereport_gb_xp_admin
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/db/relations.php');
require_once(__DIR__ . '/db/competencies.php');

/**
 * Retrieves all descendants (children, grandchildren, etc.) of a given competency.
 *
 * Uses a single query to load all relations for the course via get_relations(), then traverses
 * the hierarchy in PHP for optimal performance with many-to-many relationships.
 *
 * @param int $competencyid The ID of the parent competency.
 * @return array An array of all descendant competency IDs.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_all_descendants($competencyid) {
    // Get the competency to determine the course ID.
    $competency = get_competency($competencyid);
    if (!$competency) {
        return []; // Return empty if competency doesn't exist.
    }

    // Get all relations for the course in one query.
    $relations = get_relations($competency->courseid);

    // Build adjacency list for children.
    $children = [];
    foreach ($relations as $relation) {
        if (!isset($children[$relation->parentid])) {
            $children[$relation->parentid] = [];
        }
        $children[$relation->parentid][] = $relation->childid;
    }

    // Traverse hierarchy using depth-first search with cycle detection.
    $visited = [];
    $stack = [$competencyid];

    while (!empty($stack)) {
        $current = array_pop($stack);
        if (isset($children[$current])) {
            foreach ($children[$current] as $child) {
                if (!isset($visited[$child])) {
                    $visited[$child] = true;
                    $stack[] = $child;
                }
            }
        }
    }

    return array_map('intval', array_keys($visited));
}

/**
 * Retrieves all ancestors (parents, grandparents, etc.) of a given competency.
 *
 * Uses a single query to load all relations for the course via get_relations(), then traverses
 * the hierarchy in PHP for optimal performance with many-to-many relationships.
 *
 * @param int $competencyid The ID of the child competency.
 * @return array An array of all ancestor competency IDs.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_all_ancestors($competencyid) {
    // Get the competency to determine the course ID.
    $competency = get_competency($competencyid);
    if (!$competency) {
        return []; // Return empty if competency doesn't exist.
    }

    // Get all relations for the course in one query.
    $relations = get_relations($competency->courseid);

    // Build adjacency list for parents.
    $parents = [];
    foreach ($relations as $relation) {
        if (!isset($parents[$relation->childid])) {
            $parents[$relation->childid] = [];
        }
        $parents[$relation->childid][] = $relation->parentid;
    }

    // Traverse hierarchy using depth-first search with cycle detection.
    $visited = [];
    $stack = [$competencyid];

    while (!empty($stack)) {
        $current = array_pop($stack);
        if (isset($parents[$current])) {
            foreach ($parents[$current] as $parent) {
                if (!isset($visited[$parent])) {
                    $visited[$parent] = true;
                    $stack[] = $parent;
                }
            }
        }
    }

    return array_map('intval', array_keys($visited));
}

/**
 * Retrieves all descendant competency objects (not just IDs) of a given competency.
 *
 * @param int $competencyid The ID of the parent competency.
 * @return array An array of descendant competency objects.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_descendant_competencies($competencyid) {
    global $DB;

    $descendantids = get_all_descendants($competencyid);

    if (empty($descendantids)) {
        return [];
    }

    // Single query to get all descendant competency objects.
    list($insql, $params) = $DB->get_in_or_equal($descendantids);
    return $DB->get_records_select('gradereport_gb_xp_admin_competencies',
        "id $insql", $params, 'id ASC');
}

/**
 * Retrieves all ancestor competency objects (not just IDs) of a given competency.
 *
 * @param int $competencyid The ID of the child competency.
 * @return array An array of ancestor competency objects.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function get_ancestor_competencies($competencyid) {
    global $DB;

    $ancestorids = get_all_ancestors($competencyid);

    if (empty($ancestorids)) {
        return [];
    }

    // Single query to get all ancestor competency objects.
    list($insql, $params) = $DB->get_in_or_equal($ancestorids);
    return $DB->get_records_select('gradereport_gb_xp_admin_competencies',
        "id $insql", $params, 'id ASC');
}


/**
 * Checks if a competency is a descendant of another competency.
 *
 * @param int $childid The ID of the potential child competency.
 * @param int $parentid The ID of the potential parent competency.
 * @return bool True if child_id is a descendant of parent_id, false otherwise.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function is_descendant($childid, $parentid) {
    $descendants = get_all_descendants($parentid);
    return in_array($childid, $descendants);
}

/**
 * Checks if a competency is an ancestor of another competency.
 *
 * @param int $parentid The ID of the potential parent competency.
 * @param int $childid The ID of the potential child competency.
 * @return bool True if parent_id is an ancestor of child_id, false otherwise.
 * @throws dml_exception
 * @package gradereport_gb_xp_admin
 */
function is_ancestor($parentid, $childid) {
    $ancestors = get_all_ancestors($childid);
    return in_array($parentid, $ancestors);
}
