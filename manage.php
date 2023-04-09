<?php

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once 'lib.php';

$courseid = required_param('id', PARAM_INT);        // Course id.
$userid   = optional_param('userid', $USER->id, PARAM_INT);

setup_page($courseid, 'moodle/grade:manage');


// TODO: Optimize the following to easily get any field of the parent (like parent.id)
// This is an old approach without SQL
//$competencies = $DB->get_records('gradereport_gradebook_xp', ['courseid' => $courseid]);
//foreach ($competencies as $competency) {
//    $parent = $DB->get_record('gradereport_gradebook_xp', ['id' => $competency->parentid]);
//
//    $competency->parentid = $parent ? $parent->id : null;
//    $competency->parentname = $parent ? $parent->name : '';
//}

$competencies = $DB->get_records_sql("
    SELECT c.*, p.name AS parentname
    FROM {gradereport_gradebook_xp} c
    LEFT JOIN {gradereport_gradebook_xp} p ON p.id = c.parentid
    WHERE c.courseid = :courseid
    ORDER BY c.id ASC
", ['courseid' => $courseid]);

foreach ($competencies as $competency) {
    $competency->parentid = $competency->parentid ?: null;
}


// BEGIN: display

// Print header.
print_grade_page_head($courseid, 'settings', 'gradebook_xp', get_string('pluginname', 'gradereport_gradebook_xp'), false, '');


/// BEGIN debug

$records = $DB->get_records('gradereport_gradebook_xp', ['courseid' => $courseid]);
$hierarchy = get_child_competencies(0);
echo "<pre>";
//print_r($hierarchy);
echo "</pre>";
display_hierarchy($hierarchy);


// END debug

$templatecontext = (object)[
    'competencies' => array_values($competencies),
    'editurl' => new moodle_url('/grade/report/gradebook_xp/edit.php'),
    'courseid' => $courseid,
];
//$templatecontext = (object)[
//    'competencies' => array_values($competencies),
//];

echo $OUTPUT->render_from_template('gradereport_gradebook_xp/manage', $templatecontext);

// old button but can still be useful syntax
//echo $OUTPUT->single_button(new moodle_url('edit.php', array('courseid' => $course->id)), get_string('additem',
//    'grades'), 'get');

echo $OUTPUT->footer();

// END: display

function get_child_competencies($id) {
    global $DB, $COURSE;

    $competencies = array();

    // Get child competencies and sort by ID
    $children = $DB->get_records('gradereport_gradebook_xp', array('parentid' => $id, 'courseid' => $COURSE->id), 'id ASC');

    // Convert child competencies to dictionary
    foreach ($children as $child) {
        $attributes = get_object_vars($child);
        $competency = array();
        foreach ($attributes as $attribute_name => $attribute_value) {
            $competency[$attribute_name] = $attribute_value;
        }

        $competency['children'] = get_child_competencies($child->id);

        $competencies[$child->id] = $competency;
    }


    return $competencies;
}

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

function to_json($hierarchy) {
    $json = json_encode($hierarchy, JSON_PRETTY_PRINT);
    return $json;
}