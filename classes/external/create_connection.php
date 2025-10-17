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
 * External API for creating connections.
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
use context_course;
use moodle_exception;
use stdClass;

require_once(__DIR__ . '/../../db/competencies.php');
require_once(__DIR__ . '/../../db/connections.php');

/**
 * External API for creating connections.
 */
class create_connection extends external_api {

    /**
     * Returns description of method parameters for execute.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'activityid' => new external_value(PARAM_INT, 'Activity ID'),
            'competencyid' => new external_value(PARAM_INT, 'Competency ID'),
            'level' => new external_value(PARAM_INT, 'Connection level')
        ]);
    }

    /**
     * Create a new activity-competency connection.
     *
     * @param int $activityid Activity ID
     * @param int $competencyid Competency ID
     * @param int $level Connection level
     * @return array Created connection data
     * @throws moodle_exception
     */
    public static function execute($activityid, $competencyid, $level) {
        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), [
            'activityid' => $activityid,
            'competencyid' => $competencyid,
            'level' => $level
        ]);

        // Validate competency exists.
        $competency = get_competency($params['competencyid']);
        if (!$competency) {
            throw new moodle_exception('competencynotfound', 'gradereport_gradebook_xp');
        }

        // Context validation.
        $context = context_course::instance($competency->courseid);
        self::validate_context($context);
        require_capability('gradereport/gradebook_xp:manage', $context);

        // Create connection object.
        $connection = new stdClass();
        $connection->activityid = $params['activityid'];
        $connection->competencyid = $params['competencyid'];
        $connection->level = $params['level'];

        // Insert connection.
        $connectionid = insert_connection($connection);

        return [
            'id' => $connectionid,
            'activityid' => $connection->activityid,
            'competencyid' => $connection->competencyid,
            'level' => $connection->level
        ];
    }

    /**
     * Returns description of method return value for execute.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Connection ID'),
            'activityid' => new external_value(PARAM_INT, 'Activity ID'),
            'competencyid' => new external_value(PARAM_INT, 'Competency ID'),
            'level' => new external_value(PARAM_INT, 'Connection level')
        ]);
    }
}
