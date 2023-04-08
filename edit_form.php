<?php

// Check if script is being accessed from Moodle page
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

// Include Moodle form library
require_once("$CFG->libdir/formslib.php");

class edit_form extends moodleform {

    public function definition() {
        global $COURSE, $DB, $CFG;

        $mform = $this->_form;

        // Get course ID and record ID from parameters
        $courseid = required_param('courseid', PARAM_INT);
        $id = optional_param('id', null, PARAM_INT);

        // Get current record from database (if ID is provided)
        $current = $DB->get_record('gradereport_gradebook_xp', array('id' => $id));
//-------------------------------------------------------------------------------
        // Get parent options for select element
        $parent_options = [0 => '---']; // Add empty option
        if (!empty($current)) { // If record ID is provided
            $parentid = $current->id;
            $sql = "WITH RECURSIVE item_descendants AS (
                SELECT id, parentid, name
                FROM {gradereport_gradebook_xp}
                WHERE id = ?
                UNION
                SELECT g.id, g.parentid, g.name
                FROM {gradereport_gradebook_xp} g
                JOIN item_descendants d ON g.parentid = d.id
            )
            SELECT id, name
            FROM {gradereport_gradebook_xp}
            WHERE courseid = ? AND id NOT IN (
                SELECT id FROM item_descendants UNION
                SELECT ? WHERE parentid IS NULL
            )
            AND id != ?
            ORDER BY id";
            $params = array($parentid, $COURSE->id, $parentid, $parentid);
            $available_parents = $DB->get_records_sql($sql, $params); // Get parent records
            $parent_options += array_column($available_parents, 'name', 'id'); // Add parent options to array
        } else { // If no record ID is provided
            $sql = "SELECT id, name 
            FROM {gradereport_gradebook_xp} 
            WHERE courseid = ?
            ORDER BY id";
            $params = array($COURSE->id);
            $available_parents = $DB->get_records_sql($sql, $params); // Get all parent records
            $parent_options += array_column($available_parents, 'name', 'id'); // Add parent options to array
        }
//-------------------------------------------------------------------------------
        // Add hidden parameters to the form

        // Add hidden id element to form
        $mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);

        // Add hidden courseid element to form
        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);
//-------------------------------------------------------------------------------
        // Add visible parameters to the form

        // Add parent select element to form
        $mform->addElement('select', 'parentid', get_string('parent', 'gradereport_gradebook_xp'), $parent_options);

        // Add competency name text element to form
        $mform->addElement('text', 'name', get_string('competencyname', 'gradereport_gradebook_xp'));
        $mform->setType('name', PARAM_NOTAGS);
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
        }
//-------------------------------------------------------------------------------
        // Add action buttons to the form
        $this->add_action_buttons();
//-------------------------------------------------------------------------------

    }
}
