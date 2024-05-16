<?php

// Check if script is being accessed from Moodle page
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

// Include Moodle form library
require_once("$CFG->libdir/formslib.php");
require_once("db_controller.php");

class import_form extends moodleform
{

    // Add elements to form
    public function definition()
    {
        global $COURSE;

        // Assign form object to variable
        $mform = $this->_form;

        $courseid = required_param('id', PARAM_INT); // Get 'id' from URL or somewhere else

        // Add 'id' as a hidden element
        $mform->addElement('hidden', 'id', $courseid);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('filepicker', 'userfile', get_string('file'), null, array('accepted_types' => '.zip'));
        $mform->addElement('submit', 'submitbutton', get_string('import'));
    }
}
