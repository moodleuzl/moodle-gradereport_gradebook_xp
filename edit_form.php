<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class edit_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $COURSE, $CFG;

        $mform = $this->_form; // Don't forget the underscore!

/// visible elements
        $mform->addElement('text', 'competencyname', get_string('competencyname', 'gradereport_gradebook_xp')); // Add elements to your form.
        $mform->setType('competencyname', PARAM_NOTAGS);                   // Set type of element.

/// hidden params
        $mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
//-------------------------------------------------------------------------------
    }

// TODO: Handle Form submit/cancel
//    function definition_after_data() {
//
//    }
//
//    //Custom validation should be added here
//    function validation($data, $files) {
//        return array();
//    }
}
