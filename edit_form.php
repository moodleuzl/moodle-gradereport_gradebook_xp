<?php

// Check if script is being accessed from Moodle page
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

// Include Moodle form library
require_once("$CFG->libdir/formslib.php");
require_once("db_controller.php");

class edit_form extends moodleform {

    // Add elements to form
    public function definition() {
        global $COURSE, $DB, $CFG;

        // Assign form object to variable
        $mform = $this->_form;

        // Get course ID and record ID from parameters
        $id = optional_param('id', null, PARAM_INT);

        // Get current record from database (if ID is provided)
        $current = $DB->get_record('gradereport_gradebook_xp_com', array('id' => $id));

        // Get parent options for select element
        $parent_options = get_parent_options($current->id);
//-------------------------------------------------------------------------------
        // Add hidden parameters to the form

        // Add hidden id element to form
        $mform->addElement('hidden', 'id', $current ? $current->id : 0);
        $mform->setType('id', PARAM_INT);

        // Add hidden courseid element to form
        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);
//-------------------------------------------------------------------------------
        // Add visible parameters to the form

        // Add parent select element to form
        $mform->addElement('select', 'parentid', get_string('parent', 'gradereport_gradebook_xp'), $parent_options);

        // Add competency name text element to form
        $mform->addElement('text', 'name', get_string('name', 'gradereport_gradebook_xp'));
        $mform->setType('name', PARAM_NOTAGS);

        // Add competency descriptionion text element to form
        $mform->addElement('text', 'description', get_string('name', 'gradereport_gradebook_xp'));
        $mform->setType('description', PARAM_NOTAGS);
//-------------------------------------------------------------------------------
        // Add rules to the form

        // Add validation rule for competency name text element
        $mform->addRule('name', get_string('missingname', 'gradereport_gradebook_xp'), 'required', null, 'server');
//-------------------------------------------------------------------------------
        // Set defaults for editing if the current record exists
        if (!empty($current)) {
            $mform->setDefault('id', $current->id);
            $mform->setDefault('courseid', $current->courseid);
            $mform->setDefault('name', $current->name);
            $mform->setDefault('parentid', $current->parentid);
            $mform->setDefault('description', $current->description);
        }
//-------------------------------------------------------------------------------
        // Add action buttons to the form
        $this->add_action_buttons();
//-------------------------------------------------------------------------------

    }
}
