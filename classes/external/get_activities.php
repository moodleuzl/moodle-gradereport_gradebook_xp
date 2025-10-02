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
 * External API for getting activities.
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
use core_external\external_multiple_structure;
use context_course;
use moodle_exception;

require_once(__DIR__ . '/../../db/activities.php');

/**
 * External API for getting activities.
 */
class get_activities extends external_api {

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
     * Get all activities for a course.
     *
     * @param int $courseid Course ID
     * @return array List of activities
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
        require_capability('gradereport/gb_xp_admin:view', $context);

        // Get activities.
        $activities = get_all_activities($params['courseid']);

        // Convert to array format for JSON response.
        $result = [];
        foreach ($activities as $activity) {
            $result[] = [
                'id' => $activity->id,
                'courseid' => $activity->course,
                'section_name' => $activity->section_name,
                'name' => $activity->name,
                'intro' => $activity->intro,
                'module' => $activity->module
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
                'id' => new external_value(PARAM_INT, 'Activity ID'),
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'section_name' => new external_value(PARAM_TEXT, 'Section name'),
                'name' => new external_value(PARAM_TEXT, 'Activity name'),
                'intro' => new external_value(PARAM_RAW, 'Activity introduction'),
                'module' => new external_value(PARAM_TEXT, 'Module type')
            ])
        );
    }
}
