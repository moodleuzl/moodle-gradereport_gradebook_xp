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

        $courseid = required_param('courseid', PARAM_INT);
        $id = optional_param('id', null, PARAM_INT);

        $current = $DB->get_record('gradereport_gradebook_xp', array('id' => $id));

/// visible elements
        // parent
        // Get data from db
        if (!empty($current)) {
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
        } else {
            $sql = "SELECT id, name 
            FROM {gradereport_gradebook_xp} 
            WHERE courseid = ?
            ORDER BY id";
            $params = array($COURSE->id);
        }

        $available_parents = $DB->get_records_sql($sql, $params);



        $parent_options = array_column($available_parents, 'name', 'id');
        $parent_options = [0 => '---'] + $parent_options; // add empty option

        // Add a new select (dropdown) element
        $mform->addElement('select', 'parentid', get_string('parent', 'gradereport_gradebook_xp'), $parent_options);



        // comtpetency name
        // Add a new text element
        $mform->addElement('text', 'name', get_string('competencyname', 'gradereport_gradebook_xp')); // Add elements to your form.
        $mform->setType('name', PARAM_NOTAGS);                   // Set type of element.
        $mform->addRule('name', get_string('missingname', 'gradereport_gradebook_xp'), 'required', null, 'server');



/// hidden params
        $mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        // set defaults for editing
        if (!empty($current)){
            $mform->setDefault('id', $current->id);
            $mform->setDefault('courseid', $current->courseid);
            $mform->setDefault('name', $current->name);
            $mform->setDefault('parentid', $current->parentid);
        }


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
