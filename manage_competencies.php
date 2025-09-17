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
require_once('db_controller.php');

// Get required and optional parameters.
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

// Set up the page.
require_course_login($courseid);
gradereport_gb_xp_admin_setup_page($courseid);

// Get the competencies for the course and sort by ID.
$competencies = get_all_competencies();

// Enrich competencies with parents info for display.
foreach ($competencies as $c) {
    $parents = get_parents($c->id);

    $c->parentids = implode(', ', array_map(function($p) {
        return $p->id;
    }, $parents));

    $c->parentnames = implode(', ', array_map(function($p) {
        return $p->name;
    }, $parents));
}

// Render the page.
$templatecontext = (object) [
    'competencies' => array_values($competencies),
    'gobackurl' => new moodle_url('/grade/report/gb_xp_admin/index.php'),
    'editurl' => new moodle_url('/grade/report/gb_xp_admin/edit.php'),
    'courseid' => $courseid,
];

print_grade_page_head(
    $courseid, 'report', 'gb_xp_admin',
    get_string('pluginname', 'gradereport_gb_xp_admin'), false, '');

echo $OUTPUT->render_from_template('gradereport_gb_xp_admin/manage_competencies', $templatecontext);
echo $OUTPUT->footer();
