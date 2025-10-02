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
 * Displays the competency hierarchy for a course and provides links for managing competencies and connections.
 *
 * @package    gradereport_gb_xp_admin
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once('./lib.php');

// Get required and optional parameters.
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', null, PARAM_INT);
$competencyid = optional_param('competencyid', null, PARAM_INT);
$competencyparentid = optional_param('competencyparentid', null, PARAM_INT);

// Setup page and validate access.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course->id);
$context = context_course::instance($course->id);

// Set up page URL with parameters.
$url = new moodle_url('/grade/report/gb_xp_admin/index.php', ['id' => $courseid]);
if ($userid !== null) {
    $url->param('userid', $userid);
}
if ($competencyid !== null) {
    $url->param('competencyid', $competencyid);
}

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_context($context);

// Check that the current user has permission to view grades.
if (!has_any_capability(['moodle/grade:view', 'moodle/grade:viewall'], $context)) {
    throw new moodle_exception('nopermissions', 'error', '', 'view grades');
}

// If userid is not set and current user doesn't have viewall capability, set it to current user.
if ($userid === null && !has_capability('moodle/grade:viewall', $context)) {
    $userid = $USER->id;
}

// Check if current user can view this user's grades.
if ($userid != $USER->id && !has_capability('moodle/grade:viewall', $context)) {
    throw new moodle_exception('nopermissions', 'error', '', 'view user grades');
}

// If userid is specified, validate the user exists and user has access to view their grades.
$targetuser = null;
if ($userid !== null) {
    $targetuser = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
}

// Create action bar if user has viewall capability.
$actionbar = null;
if (has_capability('moodle/grade:viewall', $context)) {
    $actionbar = new \gradereport_gb_xp_admin\output\gb_xp_action_bar($context, $courseid, $userid);
}

// Display page header with action bar.
print_grade_page_head($courseid, 'report', 'gb_xp_admin',
    false, false, false, true, null, null, null, $actionbar);


if ($userid !== null) {
    $course_data_manager = new \gradereport_gb_xp_admin\course_data_manager($courseid, $userid);
    // Generate chart data for visualization.
    $templatedata = $course_data_manager->build_template_data_for_selected_competency($competencyid);
    $templatedata->chartjs_url = (new moodle_url($CFG->wwwroot . '/grade/report/gb_xp_admin/js/chart.umd.min.js'))->out();

    $templatedata->competencyparentid = $competencyparentid;
    if ($userid !== $USER->id) {
        $templatedata->urluserid = $userid;
    }
    // Render the main content using the old gb_xp template.
    echo $OUTPUT->render_from_template('gradereport_gb_xp_admin/index', $templatedata);
}

echo $OUTPUT->footer();
