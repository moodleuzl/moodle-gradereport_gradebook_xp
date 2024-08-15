<?php

// Include behat_base for Moodle environment setup
require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../tests/behat/behat_grade.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;
use Behat\Gherkin\Node\TableNode;

class behat_gradereport_gradebook_xp_admin extends behat_base {
    /**
     * Adds competencies to the database.
     *
     * @Given the following competencies in my plugin exist:
     * @param TableNode $table
     */
    public function the_following_competencies_in_my_plugin_exist(TableNode $table) {
        global $DB;

        $competencies = $table->getHash();
        foreach ($competencies as $competency) {
            $courseid = $DB->get_field('course', 'id', ['shortname' => $competency['courseid']]);

            // Create the competency record.
            $record = new stdClass();
            $record->courseid = $courseid;
            $record->name = $competency['name'];
            $record->parentid = $competency['parentid'];
            $record->description = $competency['description'];
            $record->maxcomlvl = $competency['maxcomlvl'];

            // Insert the competency into your custom table.
            $DB->insert_record('gradereport_gradebook_xp_com', $record);
        }
    }
    /**
     * Helper function to get course ID by full name
     */
    private function get_course_id_by_fullname($fullname) {
        global $DB;
        $course = $DB->get_record('course', ['fullname' => $fullname], 'id', MUST_EXIST);
        return $course->id;
    }
}