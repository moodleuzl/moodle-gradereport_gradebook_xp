<?php

// Check if script is being accessed from Moodle page
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

// Include Moodle form library
require_once("$CFG->libdir/formslib.php");
require_once("db_controller.php");

class edit_form extends moodleform
{

    // Add elements to form
    public function definition()
    {
        global $COURSE;

        // Assign form object to variable
        $mform = $this->_form;

        // Get course ID and record ID from parameters
        $id = optional_param('id', null, PARAM_INT);

        // Get current record from database (if ID is provided)
        $current = get_competency($id);

        // Get parent options for select element

        $parent_options = [0 => '---']; // Initialize with an empty option
        if (!empty($current)) { // If record ID is provided
            $availableParents = get_available_parents($current->id);
        } else { // If no record ID is provided
            $availableParents = get_all_competencies();
        }
        $parent_options += array_column($availableParents, 'name', 'id'); // Add parent options to array

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
        $mform->addElement('text', 'description', get_string('description', 'gradereport_gradebook_xp'));
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
        // Retrieve activity records
        $activityRecords = get_all_activities($COURSE->id);
        $activities = array();

        // Iterate over activity records and store activity ID and name in the array
        foreach ($activityRecords as $activity) {
            $activityId = $activity->id;
            $activityName = $activity->name;

            // Store the activity ID and name in the array
            $activities[$activityId] = $activityName;
        }

        // Retrieve connection records
        $connections = array();
        if (!empty($current)) { // If record ID is provided
            $connectionsRecords = get_connections($current->id);

            // Iterate over connection records and store connection ID, activity name, and weight in the array
            foreach ($connectionsRecords as $connection) {
                $connectionId = $connection->id;
                $connectionWeight = $connection->weight;
                $activityId = $connection->assignmentid;
                $activityName = $activities[$activityId];

                // Store the assignment ID, name, and weight in the array
                $connections[$connectionId] = $activityName . " | " . $connectionWeight;
            }
        }

        // Create a group for the multiselect elements
        $multiselect_group = array();

        // Add multiselect 1 to the group
        $multiselect_group[] = $mform->createElement('select', 'multiselect1', get_string('assignments', 'gradereport_gradebook_xp'), $activities, array('multiple' => 'multiple'));


        // Add move buttons to the group
        $multiselect_group[] = $mform->createElement('button', 'move_to_multiselect2', '>>');
        $multiselect_group[] = $mform->createElement('button', 'move_to_multiselect1', '<<');

        // Add multiselect 2 to the group
        $multiselect_group[] = $mform->createElement('select', 'multiselect2', get_string('assignments', 'gradereport_gradebook_xp'), $connections, array('multiple' => 'multiple'));

        // Add the group to the form
        $mform->addGroup($multiselect_group, 'multiselect_group', get_string('assignments', 'gradereport_gradebook_xp'), ' ', false);

        // Add input box for weight
        $mform->addElement('text', 'weight', get_string('weight', 'gradereport_gradebook_xp'));

        // Add a submit button
        $mform->addElement('submit', 'submitbtn', 'Submit');

        $mform->setType('weight', PARAM_INT);

//-------------------------------------------------------------------------------
        // Add action buttons to the form
        $this->add_action_buttons();
    }
}
