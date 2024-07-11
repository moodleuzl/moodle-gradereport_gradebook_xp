<?php

// Include behat_base for Moodle environment setup
require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../tests/behat/behat_grade.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;

class behat_gradereport_gradebook_xp_admin extends behat_base {
    /**
     * Go to the Gradebook XP Admin page
     *
     * @Given /^I navigate to Gradebook XP Admin in "(?P<course_name>(?:[^"]|\\")*)"$/
     */
    public function i_navigate_to_gradebook_xp_admin_in($course_name) {
        $courseid = $this->get_course_id_by_fullname($course_name);
        $localurl = new moodle_url("/grade/report/gradebook_xp_admin/index.php?id={$courseid}");
        $this->getSession()->visit($this->locate_path($localurl->out_as_local_url(false)));
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