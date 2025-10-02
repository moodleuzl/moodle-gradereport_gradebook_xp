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
 * External API for creating relations.
 *
 * @package    gradereport_gb_xp_admin
 * @copyright  INB University of Luebeck
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradereport_gb_xp_admin\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use context_course;
use moodle_exception;
use stdClass;

require_once(__DIR__ . '/../../db/competencies.php');
require_once(__DIR__ . '/../../db/relations.php');

/**
 * External API for creating relations.
 */
class create_relation extends external_api {

    /**
     * Returns description of method parameters for execute.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'parentid' => new external_value(PARAM_INT, 'Parent competency ID'),
            'childid' => new external_value(PARAM_INT, 'Child competency ID')
        ]);
    }

    /**
     * Create a new relation between competencies.
     *
     * @param int $parentid Parent competency ID
     * @param int $childid Child competency ID
     * @return array Created relation data
     * @throws moodle_exception
     */
    public static function execute($parentid, $childid) {
        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), [
            'parentid' => $parentid,
            'childid' => $childid
        ]);

        // Validate both competencies exist and get course context.
        $parent = get_competency($params['parentid']);
        $child = get_competency($params['childid']);

        if (!$parent || !$child) {
            throw new moodle_exception('competencynotfound', 'gradereport_gb_xp_admin');
        }

        if ($parent->courseid !== $child->courseid) {
            throw new moodle_exception('competenciesnotsamecourse', 'gradereport_gb_xp_admin');
        }

        // Context validation.
        $context = context_course::instance($parent->courseid);
        self::validate_context($context);
        require_capability('gradereport/gb_xp_admin:manage', $context);

        // Create relation object.
        $relation = new stdClass();
        $relation->parentid = $params['parentid'];
        $relation->childid = $params['childid'];

        // Insert relation.
        $relationid = insert_relation($relation);

        return [
            'id' => $relationid,
            'parentid' => $relation->parentid,
            'childid' => $relation->childid
        ];
    }

    /**
     * Returns description of method return value for execute.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Relation ID'),
            'parentid' => new external_value(PARAM_INT, 'Parent competency ID'),
            'childid' => new external_value(PARAM_INT, 'Child competency ID')
        ]);
    }
}
