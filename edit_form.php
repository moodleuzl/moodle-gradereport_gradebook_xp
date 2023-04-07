<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class edit_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $COURSE, $DB, $CFG;

        $mform = $this->_form; // Don't forget the underscore!

/// visible elements
        // comtpetency name
        // Add a new text element
        $mform->addElement('text', 'name', get_string('competencyname', 'gradereport_gradebook_xp')); // Add elements to your form.
        $mform->setType('name', PARAM_NOTAGS);                   // Set type of element.
        $mform->addRule('name', get_string('missingname', 'gradereport_gradebook_xp'), 'required', null, 'server');

        // parent
        // Get data from db
        $competencies = $DB->get_records('gradereport_gradebook_xp', ['courseid' => $COURSE->id], '', 'id, name');
        $competency_options = array_column($competencies, 'name', 'id');
        $competency_options = [0 => '---'] + $competency_options; // add empty option

// Add a new select (dropdown) element
        $mform->addElement('select', 'parentid', get_string('parent', 'gradereport_gradebook_xp'), $competency_options);


/// hidden params
        $mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

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
