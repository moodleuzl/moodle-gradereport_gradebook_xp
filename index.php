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
 */

require_once('../../../config.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once('./lib.php');

// Get required and optional parameters.
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

// Set up the page.
require_course_login($courseid);
gradereport_gb_xp_admin_setup_page($courseid);
$PAGE->navbar->add(get_string('preferences'));

// Get the competencies for the course and sort by ID.
$competencies = get_competencies_hierarchy($courseid);

/**
 * Display the competency hierarchy recursively.
 *
 * @param $competencies
 * @return string
 */
function display_hierarchy($competencies) {
    $html = '<ul>';
    foreach ($competencies as $competencyid => $competency) {
        $html .= "<li>$competency->name"; // Display the competency name.
        if (!empty($competency->subCompetencies)) {
            $html .= display_hierarchy($competency->subCompetencies);
        }
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}

// Render the page.
$templatecontext = (object) [
    'managecompetenciesurl' => new moodle_url('/grade/report/gb_xp_admin/manage_competencies.php'),
    'manageconnectionsurl' => new moodle_url('/grade/report/gb_xp_admin/manage_connections.php'),
    'exporturl' => new moodle_url('/grade/report/gb_xp_admin/export.php'),
    'importurl' => new moodle_url('/grade/report/gb_xp_admin/import.php'),
    'courseid' => $courseid,
    'competencies' => $competencies,
];

print_grade_page_head($courseid, 'report', 'gb_xp_admin',
    get_string('pluginname', 'gradereport_gb_xp_admin'), false, '');
echo(display_hierarchy($competencies));
echo $OUTPUT->render_from_template('gradereport_gb_xp_admin/preferences', $templatecontext);
echo $OUTPUT->footer();
