<?php
require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once('./lib.php');

// Get required and optional parameters
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

// Set up the page
gradereport_gradebook_xp_admin_setup_page($courseid, 'moodle/grade:manage');
$PAGE->navbar->add(get_string('preferences'));

// Get the competencies for the course and sort by ID
$competencies = $DB->get_records_sql("
    SELECT c.*, p.name AS parentname
    FROM {gradereport_gradebook_xp_com} c
    LEFT JOIN {gradereport_gradebook_xp_com} p ON p.id = c.parentid
    WHERE c.courseid = :courseid
    ORDER BY c.id ASC
", ['courseid' => $courseid]);

// Get the child competencies recursively and sort by ID
function get_child_competencies($id) {
    global $DB, $COURSE;

    $competencies = array();

    $children = $DB->get_records('gradereport_gradebook_xp_com', ['parentid' => $id, 'courseid' => $COURSE->id], 'id ASC');

    foreach ($children as $child) {
        $competency = (array) $child;
        $competency['children'] = get_child_competencies($child->id);
        $competencies[$child->id] = $competency;
    }

    return $competencies;
}

// Display the competency hierarchy recursively
function display_hierarchy($hierarchy) {
    echo '<ul>';
    foreach ($hierarchy as $item) {
        echo '<li>' . $item['name'];
        if (!empty($item['children'])) {
            display_hierarchy($item['children']);
        }
        echo '</li>';
    }
    echo '</ul>';
}

// Render the page
$templatecontext = (object)[
    'managecompetenciesurl' => new moodle_url('/grade/report/gradebook_xp_admin/manage_competencies.php'),
    'manageconnectionsurl' => new moodle_url('/grade/report/gradebook_xp_admin/manage_connections.php'),
    'exporturl' => new moodle_url('/grade/report/gradebook_xp_admin/export.php'),
    'importurl' => new moodle_url('/grade/report/gradebook_xp_admin/import.php'),
    'courseid' => $courseid,
];

print_grade_page_head($courseid, 'settings', 'gradebook_xp_admin', get_string('pluginname', 'gradereport_gradebook_xp_admin'), false, '');
display_hierarchy(get_child_competencies(0));
echo $OUTPUT->render_from_template('gradereport_gradebook_xp_admin/preferences', $templatecontext);
echo $OUTPUT->footer();
