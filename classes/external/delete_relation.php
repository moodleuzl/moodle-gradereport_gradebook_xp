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
 * External API for deleting relations.
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

require_once(__DIR__ . '/../../db/competencies.php');
require_once(__DIR__ . '/../../db/relations.php');

/**
 * External API for deleting relations.
 */
class delete_relation extends external_api {

    /**
     * Returns description of method parameters for execute.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Relation ID')
        ]);
    }

    /**
     * Delete a relation.
     *
     * @param int $id Relation ID
     * @return array Success status
     * @throws moodle_exception
     */
    public static function execute($id) {
        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), [
            'id' => $id
        ]);

        // Get existing relation to validate access.
        $existing = get_relation($params['id']);
        if (!$existing) {
            throw new moodle_exception('relationnotfound', 'gradereport_gradebook_xp');
        }

        // Get parent competency to validate course access.
        $parent = get_competency($existing->parentid);
        if (!$parent) {
            throw new moodle_exception('competencynotfound', 'gradereport_gradebook_xp');
        }

        // Context validation.
        $context = context_course::instance($parent->courseid);
        self::validate_context($context);
        require_capability('gradereport/gradebook_xp:manage', $context);

        // Delete relation.
        delete_relation($params['id']);

        return ['success' => true];
    }

    /**
     * Returns description of method return value for execute.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status')
        ]);
    }
}
