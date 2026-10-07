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
use gradereport_gradebook_xp\competencies;
use gradereport_gradebook_xp\relations;

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
            'targetcomlvl' => new external_value(PARAM_INT, 'Target competency level', VALUE_DEFAULT, 1),
            'islevelsummed' => new external_value(PARAM_INT, 'Is level summed flag', VALUE_DEFAULT, 1),
            'parentid' => new external_value(PARAM_INT, 'Optional parent competency ID', VALUE_DEFAULT, 0)
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
    public static function execute($courseid, $name, $description = '', $maxcomlvl = 1, $targetcomlvl = 1,
            $islevelsummed = 1, $parentid = 0) {
        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'name' => $name,
            'description' => $description,
            'maxcomlvl' => $maxcomlvl,
            'targetcomlvl' => $targetcomlvl,
            'islevelsummed' => $islevelsummed,
            'parentid' => $parentid
        ]);

        // Context validation.
        $context = context_course::instance($params['courseid']);
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

        $parent = null;
        if ($params['parentid'] > 0) {
            $parent = competencies::get_competency($params['parentid']);
            if (!$parent || (int)$parent->courseid !== (int)$params['courseid']) {
                throw new moodle_exception('invalidparameter', 'error', '', 'Parent competency is not in this course');
            }
        }

        // Create competency object.
        $competency = new stdClass();
        $competency->courseid = $params['courseid'];
        $competency->name = $params['name'];
        $competency->description = $params['description'];
        $competency->maxcomlvl = $params['maxcomlvl'];
        $competency->targetcomlvl = $params['targetcomlvl'];
        $competency->islevelsummed = $params['islevelsummed'];

        // Insert the competency and its initial parent relation atomically.
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $competencyid = competencies::insert_competency($competency);
        $relationid = 0;
        if ($parent) {
            $relation = new stdClass();
            $relation->parentid = (int)$parent->id;
            $relation->childid = $competencyid;
            $relationid = relations::insert_relation($relation);
        }
        $transaction->allow_commit();

        return [
            'id' => $competencyid,
            'courseid' => $competency->courseid,
            'name' => $competency->name,
            'description' => $competency->description,
            'maxcomlvl' => $competency->maxcomlvl,
            'targetcomlvl' => $competency->targetcomlvl,
            'islevelsummed' => $competency->islevelsummed,
            'relationid' => $relationid
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
            'islevelsummed' => new external_value(PARAM_INT, 'Is level summed flag'),
            'relationid' => new external_value(PARAM_INT, 'Created parent relation ID or zero')
        ]);
    }
}
