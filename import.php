<?php
require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once('./lib.php');
require_once ('./db_controller.php');
require_once 'import_form.php';

// Get required and optional parameters
$courseid = required_param('id', PARAM_INT);

// Set up the page
gradereport_gradebook_xp_admin_setup_page($courseid, 'moodle/grade:manage');
$PAGE->navbar->add(get_string('preferences'));

// Instantiate import_form
$mform = new import_form();

//if ($data = $mform->get_data()) {
//    $filecontent = $mform->get_file_content('userfile');
//    $lines = explode(PHP_EOL, $filecontent);
//    foreach ($lines as $line) {
//        $record = str_getcsv($line);
//        $DB->insert_record('gradereport_gradebook_xp_com', (object)$record, false);
//    }
//}
if ($mform->is_cancelled()) {
    // Handle form cancellation.
} else if ($data = $mform->get_data()) {
    $itemid = $data->userfile;
    $fs = get_file_storage();
    $context = context_system::instance();

    $files = $fs->get_area_files($context->id, 'user', 'draft', $itemid);
    foreach ($files as $file) {
        if ($file->get_filename() !== '.') {
            $content = $file->get_content();
            $lines = explode(PHP_EOL, $content);
            foreach ($lines as $line) {
                $record = str_getcsv($line);
                $record['courseid'] = $courseid; // Set 'courseid' for each record
                // Insert the record into the database
                $DB->insert_record('gradereport_gradebook_xp', (object)$record, false);
            }
        }
    }
}

// add heading to navbar
$PAGE->navbar->add('Import');

// Print header
print_grade_page_head($courseid, 'settings', 'gradebook_xp_admin', 'Import', false, false, false);

// displays the form
$mform->display();

// Print footer
echo $OUTPUT->footer();