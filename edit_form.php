<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// Check if script is being accessed from Moodle page.
defined('MOODLE_INTERNAL') || die();

// Include Moodle form library.
require_once("$CFG->libdir/formslib.php");
require_once("db_controller.php");

class edit_form extends moodleform {

    // Add elements to form.
    public function definition() {
        global $COURSE;

        // Assign form object to variable.
        $mform = $this->_form;

        // Get course ID and record ID from parameters.
        $id = optional_param('id', null, PARAM_INT);

        // Get current record from database (if ID is provided).
        $current = get_competency($id);

        // Get parent options for select element.

        $parentoptions = [0 => '---']; // Initialize with an empty option.
        if (!empty($current)) { // If record ID is provided.
            $availableparents = get_available_parents($current->id);
        } else { // If no record ID is provided.
            $availableparents = get_all_competencies();
        }
        $parentoptions += array_column($availableparents, 'name', 'id'); // Add parent options to array.

        // -------------------------------------------------------------------------------
        // Add hidden parameters to the form.

        // Add hidden id element to form.
        $mform->addElement('hidden', 'id', $current ? $current->id : 0);
        $mform->setType('id', PARAM_INT);

        // Add hidden courseid element to form.
        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        // Hidden element to store activities array as a JSON string.
        $mform->addElement('hidden', 'connections', '');
        $mform->setType('connections',
            PARAM_RAW);  // Using PARAM_RAW since it'll be a JSON string.
        // -------------------------------------------------------------------------------
        // Add visible parameters to the form.

        // Add parent select element to form.
        $mform->addElement('select', 'parentid', get_string('parent', 'gradereport_gradebook_xp_admin'), $parentoptions);

        // Add competency name text element to form.
        $mform->addElement('text', 'name', get_string('name', 'gradereport_gradebook_xp_admin'));
        $mform->setType('name', PARAM_NOTAGS);

        // Add competency descriptionion text element to form.
        $mform->addElement('textarea', 'description', get_string('description', 'gradereport_gradebook_xp_admin'));
        $mform->setType('description', PARAM_TEXT);

        // Add competency max level input element to form.
        $mform->addElement('text', 'maxcomlvl', get_string('maxcomlvl', 'gradereport_gradebook_xp_admin'));
        $mform->setType('maxcomlvl', PARAM_NOTAGS);

        // -------------------------------------------------------------------------------
        // Add rules to the form.

        // Add validation rule for competency name text element.
        $mform->addRule('name', get_string('missingname', 'gradereport_gradebook_xp_admin'), 'required', null, 'server');
        $mform->addRule('name', get_string('strexceedslimit100', 'gradereport_gradebook_xp_admin'), 'maxlength', 100, 'server');

        $mform->addRule('description', get_string('strexceedslimit255', 'gradereport_gradebook_xp_admin'), 'maxlength', 255,
            'server');

        $mform->addRule('maxcomlvl', get_string('missinginput', 'gradereport_gradebook_xp_admin'), 'required', null, 'server');
        $mform->addRule('maxcomlvl', get_string('nonNumericError', 'gradereport_gradebook_xp_admin'), 'numeric', null, 'server');
        $mform->addRule('maxcomlvl', get_string('strupto999', 'gradereport_gradebook_xp_admin'), 'maxlength', 3, 'server');

        // -------------------------------------------------------------------------------
        // Set defaults for editing if the current record exists.
        if (!empty($current)) {
            $mform->setDefault('id', $current->id);
            $mform->setDefault('courseid', $current->courseid);
            $mform->setDefault('name', $current->name);
            $mform->setDefault('parentid', $current->parentid);
            $mform->setDefault('description', $current->description);
            $mform->setDefault('maxcomlvl', $current->maxcomlvl);
        }
        // -------------------------------------------------------------------------------
        // Retrieve activity records.
        $activityrecords = get_all_activities($COURSE->id);

        if (!empty($current)) { // If record ID is provided.
            $connectionsrecords = get_connections($current->id);
        } else {
            $connectionsrecords = [];
        }

        $activities = [];
        $connections = [];

        // Iterate over activity records and store activity ID and name in the array.
        foreach ($activityrecords as $activityrecord) {
            $activityvalue = json_encode(['id' => $activityrecord->id, 'name' => $activityrecord->name]);
            $activities[$activityrecord->id] = ['activityValue' => $activityvalue, 'activityName' => $activityrecord->name];
        }

        foreach ($connectionsrecords as $connectionrecord) {
            $activity = $activities[$connectionrecord->activityid];

            $connectionvalue = json_encode(['id' => $connectionrecord->activityid, 'name' => $activity['activityName'],
                'level' => $connectionrecord->level]);
            $connections[$connectionvalue] = $activity['activityName'] . " | " . $connectionrecord->level;
            unset($activities[$connectionrecord->activityid]);
        }

        $activitiesform = [];
        foreach ($activities as $activity) {
            $activitiesform[$activity['activityValue']] = $activity['activityName'];
        }
        $activities = $activitiesform;

        $action = optional_param('action', null, PARAM_ALPHA); // Get action from the URL.

        // Check if the action is 'new'.
        if ($action !== 'new') {
            // Create a group for the multiselect elements.

            // Add multiselect 1 to the group.
            $multiselectgroup[] =
                $mform->createElement('select', 'multiselect1', get_string('activities', 'gradereport_gradebook_xp_admin'),
                    $activities, ['multiple' => 'multiple']);

            // Add move buttons to the group.
            $multiselectgroup[] = $mform->createElement('button', 'move_to_multiselect2', '>>');
            $multiselectgroup[] = $mform->createElement('button', 'move_to_multiselect1', '<<');

            // Add multiselect 2 to the group.
            $multiselectgroup[] =
                $mform->createElement('select', 'multiselect2', get_string('connections', 'gradereport_gradebook_xp_admin'),
                    $connections, ['multiple' => 'multiple']);

            // Add the group to the form.
            $mform->addGroup($multiselectgroup, 'multiselect_group', get_string('activities', 'gradereport_gradebook_xp_admin'),
                ' ', false);

            // Add Dropdown Menu for level (ranging from 1 to max_lvl).
            $mform->addElement('select', 'level', get_string('level', 'gradereport_gradebook_xp_admin'), range(1, 'maxcomlvl'),
                []);
        }

        // -------------------------------------------------------------------------------
        // Add action buttons to the form.
        $this->add_action_buttons();
    }
}
