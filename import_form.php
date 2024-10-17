<?php

// Check if script is being accessed from Moodle page
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

// Include Moodle form library
require_once("$CFG->libdir/formslib.php");
require_once("db_controller.php"); // Include the database controller file

/**
 * Class import_form
 *
 * Represents a form for importing data in Moodle.
 */
class import_form extends moodleform {

    /**
     * Define the form elements.
     */
    public function definition() {
        global $COURSE;

        // Assign form object to variable
        $mform = $this->_form;

        // Get the course ID from the URL or somewhere else
        $courseid = required_param('id', PARAM_INT);

        // Add 'id' as a hidden element to pass the course ID
        $mform->addElement('hidden', 'id', $courseid);
        $mform->setType('id', PARAM_INT);

        // Add a filepicker element for selecting a file to import
        $mform->addElement('filepicker', 'userfile', get_string('file'), null, array('accepted_types' => '.zip'));

        // Add a submit button to submit the form
        $mform->addElement('submit', 'submitbutton', get_string('import'));
    }
}
