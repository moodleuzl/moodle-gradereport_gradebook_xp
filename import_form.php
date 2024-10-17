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
 * @package gradereport_gradebook_xp_admin
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
        $mform->addElement('filepicker', 'userfile', get_string('file'), null, ['accepted_types' => '.zip']);

        // Add a submit button to submit the form
        $mform->addElement('submit', 'submitbutton', get_string('import'));
    }
}
