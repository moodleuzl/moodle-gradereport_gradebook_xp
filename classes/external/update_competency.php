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
 * External API for updating competencies.
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

/**
 * External API for updating competencies.
 */
class update_competency extends external_api {

    /**
     * Returns description of method parameters for execute.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Competency ID'),
            'name' => new external_value(PARAM_TEXT, 'Competency name'),
            'description' => new external_value(PARAM_TEXT, 'Competency description', VALUE_DEFAULT, ''),
            'maxcomlvl' => new external_value(PARAM_INT, 'Maximum competency level', VALUE_DEFAULT, 1),
            'targetcomlvl' => new external_value(PARAM_INT, 'Target competency level', VALUE_DEFAULT, 1),
            'islevelsummed' => new external_value(PARAM_INT, 'Is level summed flag', VALUE_DEFAULT, 1)
        ]);
    }

    /**
     * Update an existing competency.
     *
     * @param int $id Competency ID
     * @param string $name Competency name
     * @param string $description Competency description
     * @param int $maxcomlvl Maximum competency level
     * @param int $islevelsummed Is level summed flag
     * @return array Updated competency data
     * @throws moodle_exception
     */
    public static function execute($id, $name, $description = '', $maxcomlvl = 1, $targetcomlvl = 1,
            $islevelsummed = 1) {
        global $DB;
        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'maxcomlvl' => $maxcomlvl,
            'targetcomlvl' => $targetcomlvl,
            'islevelsummed' => $islevelsummed
        ]);

        // Get existing competency to validate course access.
        $existing = get_competency($params['id']);
        if (!$existing) {
            throw new moodle_exception('competencynotfound', 'gradereport_gradebook_xp');
        }

        // Context validation.
        $context = context_course::instance($existing->courseid);
        self::validate_context($context);
        require_capability('gradereport/gradebook_xp:manage', $context);

        $params['name'] = trim($params['name']);
        if ($params['name'] === '') {
            throw new moodle_exception('invalidparameter', 'error', '', 'Competency name must not be empty');
        }
        if ($params['maxcomlvl'] < 1) {
            throw new moodle_exception('invalidparameter', 'error', '', 'Maximum competency level must be positive');
        }
        if ($params['targetcomlvl'] < 1 || $params['targetcomlvl'] > $params['maxcomlvl']) {
            throw new moodle_exception('invalidparameter', 'error', '', 'Target level must be between one and the maximum');
        }
        if (!in_array($params['islevelsummed'], [0, 1], true)) {
            throw new moodle_exception('invalidparameter', 'error', '', 'Invalid level-summed flag');
        }

        $sql = $params['islevelsummed'] === 1
            ? "SELECT COALESCE(SUM(level), 0)"
            : "SELECT COALESCE(MAX(level), 0)";
        $sql .= " FROM {gradereport_gradebook_xp_connections} WHERE competencyid = :competencyid";
        $requiredlevel = (int)$DB->get_field_sql($sql, ['competencyid' => $params['id']]);
        if ($requiredlevel > $params['maxcomlvl']) {
            throw new moodle_exception(
                'invalidparameter',
                'error',
                '',
                'Maximum competency level is lower than the connected activity levels'
            );
        }

        // Update competency object.
        $competency = new stdClass();
        $competency->id = $params['id'];
        $competency->courseid = $existing->courseid;
        $competency->name = $params['name'];
        $competency->description = $params['description'];
        $competency->maxcomlvl = $params['maxcomlvl'];
        $competency->targetcomlvl = $params['targetcomlvl'];
        $competency->islevelsummed = $params['islevelsummed'];

        // Update competency.
        update_competency($competency);

        return [
            'id' => $competency->id,
            'courseid' => $competency->courseid,
            'name' => $competency->name,
            'description' => $competency->description,
            'maxcomlvl' => $competency->maxcomlvl,
            'targetcomlvl' => $competency->targetcomlvl,
            'islevelsummed' => $competency->islevelsummed
        ];
    }

    /**
     * Returns description of method return value for execute.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Competency ID'),
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'name' => new external_value(PARAM_TEXT, 'Competency name'),
            'description' => new external_value(PARAM_TEXT, 'Competency description'),
            'maxcomlvl' => new external_value(PARAM_INT, 'Maximum competency level'),
            'targetcomlvl' => new external_value(PARAM_INT, 'Target competency level'),
            'islevelsummed' => new external_value(PARAM_INT, 'Is level summed flag')
        ]);
    }
}
