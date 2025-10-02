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
 * External API for creating competencies.
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

/**
 * External API for creating competencies.
 */
class create_competency extends external_api {

    /**
     * Returns description of method parameters for execute.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'name' => new external_value(PARAM_TEXT, 'Competency name'),
            'description' => new external_value(PARAM_TEXT, 'Competency description', VALUE_DEFAULT, ''),
            'maxcomlvl' => new external_value(PARAM_INT, 'Maximum competency level', VALUE_DEFAULT, 1),
            'islevelsummed' => new external_value(PARAM_INT, 'Is level summed flag', VALUE_DEFAULT, 1)
        ]);
    }

    /**
     * Create a new competency.
     *
     * @param int $courseid Course ID
     * @param string $name Competency name
     * @param string $description Competency description
     * @param int $maxcomlvl Maximum competency level
     * @param int $islevelsummed Is level summed flag
     * @return array Created competency data
     * @throws moodle_exception
     */
    public static function execute($courseid, $name, $description = '', $maxcomlvl = 1, $islevelsummed = 1) {
        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'name' => $name,
            'description' => $description,
            'maxcomlvl' => $maxcomlvl,
            'islevelsummed' => $islevelsummed
        ]);

        // Context validation.
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('gradereport/gb_xp_admin:manage', $context);

        // Create competency object.
        $competency = new stdClass();
        $competency->courseid = $params['courseid'];
        $competency->name = $params['name'];
        $competency->description = $params['description'];
        $competency->maxcomlvl = $params['maxcomlvl'];
        $competency->islevelsummed = $params['islevelsummed'];

        // Insert competency.
        $competencyid = insert_competency($competency);

        return [
            'id' => $competencyid,
            'courseid' => $competency->courseid,
            'name' => $competency->name,
            'description' => $competency->description,
            'maxcomlvl' => $competency->maxcomlvl,
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
            'islevelsummed' => new external_value(PARAM_INT, 'Is level summed flag')
        ]);
    }
}
