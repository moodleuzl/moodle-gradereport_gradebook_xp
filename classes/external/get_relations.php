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
 * External API for getting relations.
 *
 * @package    gradereport_gradebook_xp
 * @copyright  INB University of Luebeck
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradereport_gradebook_xp\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use context_course;
use moodle_exception;
use gradereport_gradebook_xp\relations;

/**
 * External API for getting relations.
 */
class get_relations extends external_api {

    /**
     * Returns description of method parameters for execute.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID')
        ]);
    }

    /**
     * Get all relations for a course.
     *
     * @param int $courseid The course ID
     * @return array List of relations
     * @throws moodle_exception
     */
    public static function execute($courseid) {
        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid
        ]);

        // Context validation.
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('gradereport/gradebook_xp:view', $context);

        // Get relations.
        $relations = relations::get_relations($params['courseid']);

        // Convert to array format for JSON response.
        $result = [];
        foreach ($relations as $relation) {
            $result[] = [
                'id' => $relation->id,
                'parentid' => $relation->parentid,
                'childid' => $relation->childid
            ];
        }

        return $result;
    }

    /**
     * Returns description of method return value for execute.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Relation ID'),
                'parentid' => new external_value(PARAM_INT, 'Parent competency ID'),
                'childid' => new external_value(PARAM_INT, 'Child competency ID')
            ])
        );
    }
}
