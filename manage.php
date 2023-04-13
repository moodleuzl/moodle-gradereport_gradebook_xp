<?php
require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once('lib.php');

// Get required and optional parameters
$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

// Set up the page
setup_page($courseid, 'moodle/grade:manage');

// Get the competencies for the course and sort by ID
$competencies = $DB->get_records_sql("
    SELECT c.*, p.name AS parentname
    FROM {gradereport_gradebook_xp} c
    LEFT JOIN {gradereport_gradebook_xp} p ON p.id = c.parentid
    WHERE c.courseid = :courseid
    ORDER BY c.id ASC
", ['courseid' => $courseid]);

// Get the child competencies recursively and sort by ID
function get_child_competencies($id) {
    global $DB, $COURSE;

    $competencies = array();

    $children = $DB->get_records('gradereport_gradebook_xp', ['parentid' => $id, 'courseid' => $COURSE->id], 'id ASC');

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

// Convert the competency hierarchy to JSON
function to_json($hierarchy) {
    $json = json_encode($hierarchy, JSON_PRETTY_PRINT);
    return $json;
}

// Render the page
$templatecontext = (object)[
    'competencies' => array_values($competencies),
    'editurl' => new moodle_url('/grade/report/gradebook_xp/edit.php'),
    'courseid' => $courseid,
];

print_grade_page_head($courseid, 'settings', 'gradebook_xp', get_string('pluginname', 'gradereport_gradebook_xp'), false, '');
display_hierarchy(get_child_competencies(0));
echo $OUTPUT->render_from_template('gradereport_gradebook_xp/manage', $templatecontext);
echo $OUTPUT->footer();
