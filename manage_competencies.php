<?php
// This file is part of Moodle - http://moodle.org/.
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
 * Displays the list of competencies for a course and provides options for managing them.
 *
 * @package    gradereport_gb_xp_admin
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once('lib.php');

global $CFG;

// Get required and optional parameters.
$courseid = required_param('id', PARAM_INT);

// Setup page and validate access.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course->id);
$context = context_course::instance($course->id);


// Set up page URL with parameters.
$url = new moodle_url('/grade/report/gb_xp_admin/manage_competencies.php', ['id' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_pagelayout('default');
$PAGE->set_context($context);

// Check if current edit competencies.
if (!has_capability('moodle/grade:manage', $context)) {
    throw new moodle_exception('nopermissions', 'error', '', 'manage grades');
}

$PAGE->requires->jquery();

print_grade_page_head(
    $courseid, 'report', 'gb_xp_admin',
    false, false, '', false);

echo $OUTPUT->render_from_template('gradereport_gb_xp_admin/manage_competencies', [
    'react_url' => (new moodle_url($CFG->wwwroot . '/grade/report/gb_xp_admin/js/react.production.min.js'))->out(),
    'react_dom_url' => (new moodle_url($CFG->wwwroot . '/grade/report/gb_xp_admin/js/react-dom.production.min.js'))->out(),
]);

$PAGE->requires->js_call_amd('gradereport_gb_xp_admin/competency_manager', 'init', [
    'containerId' => "gradebook-xp-manage-react-app-container",
    [
        'courseid' => $courseid,
    ]
]);

echo $OUTPUT->footer();
