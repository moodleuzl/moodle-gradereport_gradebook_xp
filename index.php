<?php
global $CFG, $USER, $PAGE;
require_once('../../../config.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once('./lib.php');

// Get required and optional parameters
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

// Set up the page
gradereport_gradebook_xp_admin_setup_page($courseid, 'moodle/grade:manage');
$PAGE->navbar->add(get_string('preferences'));

// Get the competencies for the course and sort by ID
$competencies = get_competencies_hierarchy($courseid);

// Display the competency hierarchy recursively
function display_hierarchy($competencies) {
    $html = '<ul>';
    foreach ($competencies as $competencyid => $competency) {
        $html .= "<li>$competency->name"; // Display the competency name
        if (!empty($competency->subCompetencies)) {
            $html .= display_hierarchy($competency->subCompetencies);
        }
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}

// Render the page
$templatecontext = (object) [
    'managecompetenciesurl' => new moodle_url('/grade/report/gradebook_xp_admin/manage_competencies.php'),
    'manageconnectionsurl' => new moodle_url('/grade/report/gradebook_xp_admin/manage_connections.php'),
    'exporturl' => new moodle_url('/grade/report/gradebook_xp_admin/export.php'),
    'importurl' => new moodle_url('/grade/report/gradebook_xp_admin/import.php'),
    'courseid' => $courseid,
];

print_grade_page_head($courseid, 'report', 'gradebook_xp_admin', get_string('pluginname', 'gradereport_gradebook_xp_admin'), false,
    '');
echo(display_hierarchy($competencies));
echo $OUTPUT->render_from_template('gradereport_gradebook_xp_admin/preferences', $templatecontext);
echo $OUTPUT->footer();
