<?php

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */

function gradereport_gradebook_xp_extend_navigation_course($navigation, $course, $context) {

    $url = new moodle_url('/grade/report/gradebook_xp/manage.php', array('id' => $course->id));
    $name = get_string('pluginname', 'gradereport_gradebook_xp');
    $navigation->add($name, $url, navigation_node::TYPE_COURSE, null, null, new pix_icon('i/competencies', ''));
}

function setup_page($courseid, $capabililty='moodle/grade:view') {
    global $PAGE, $DB, $context;
    // Set page URL and layout
    $url = new moodle_url('/grade/report/gradebook_xp/'.get_caller_filename(), array('id' => $courseid));
    if ($courseid !== 0) {
        $url->param('id', $courseid);
    }
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('standard');

    // Verify user has access to course
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        print_error('invalidcourseid');
    }

    require_login($course);
    $context = context_course::instance($course->id);
    require_capability($capabililty, $context);    // TODO: Check if user has permission to view this page
}

function get_caller_filename() {
    $trace = debug_backtrace();
    $caller = $trace[1];
    return basename($caller['file']);
}
