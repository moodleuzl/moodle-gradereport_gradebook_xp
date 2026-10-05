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
require_once($CFG->libdir . '/gradelib.php');

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
            'gradeitemid' => new external_value(PARAM_INT, 'Grade item ID'),
            'competencyid' => new external_value(PARAM_INT, 'Competency ID'),
            'level' => new external_value(PARAM_INT, 'Connection level')
        ]);
    }

    /**
     * Create a new activity-competency connection.
     *
     * @param int $gradeitemid Grade item ID
     * @param int $competencyid Competency ID
     * @param int $level Connection level
     * @return array Created connection data
     * @throws moodle_exception
     */
    public static function execute($gradeitemid, $competencyid, $level) {
        global $DB;
        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), [
            'gradeitemid' => $gradeitemid,
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

        if ($params['level'] < 1 || $params['level'] > (int)$competency->maxcomlvl) {
            throw new moodle_exception(
                'invalidparameter',
                'error',
                '',
                'Connection level is outside the competency range'
            );
        }

        $gradeitem = $DB->get_record('grade_items', ['id' => $params['gradeitemid']]);
        if (!$gradeitem || (int)$gradeitem->courseid !== (int)$competency->courseid ||
                !in_array($gradeitem->itemtype, ['mod', 'manual'], true) ||
                (int)$gradeitem->gradetype === GRADE_TYPE_NONE) {
            throw new moodle_exception('invalidparameter', 'error', '', 'Grade item is not selectable in this course');
        }

        if ((int)$competency->islevelsummed === 1) {
            $existing = get_connection($params['gradeitemid'], $params['competencyid']);
            $sql = "SELECT COALESCE(SUM(level), 0)
                      FROM {gradereport_gradebook_xp_connections}
                     WHERE competencyid = :competencyid";
            $currenttotal = (int)$DB->get_field_sql($sql, ['competencyid' => $params['competencyid']]);
            if ($existing) {
                $currenttotal -= (int)$existing->level;
            }
            if ($currenttotal + $params['level'] > (int)$competency->maxcomlvl) {
                throw new moodle_exception(
                    'invalidparameter',
                    'error',
                    '',
                    'Summed connection levels exceed the maximum competency level'
                );
            }
        }

        // Create connection object.
        $connection = new stdClass();
        $connection->activityid = null;
        $connection->gradeitemid = $params['gradeitemid'];
        $connection->competencyid = $params['competencyid'];
        $connection->level = $params['level'];

        // Insert connection.
        $connectionid = insert_connection($connection);

        return [
            'id' => $connectionid,
            'gradeitemid' => $connection->gradeitemid,
            'activityid' => 0,
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
            'gradeitemid' => new external_value(PARAM_INT, 'Grade item ID'),
            'activityid' => new external_value(PARAM_INT, 'Legacy activity ID'),
            'competencyid' => new external_value(PARAM_INT, 'Competency ID'),
            'level' => new external_value(PARAM_INT, 'Connection level')
        ]);
    }
}
