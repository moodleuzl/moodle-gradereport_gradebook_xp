<?php

// Include behat_base for Moodle environment setup
require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;

class behat_gradereport_gradebook_xp_admin extends behat_base {
    /**
     * @Given /^I am on the "([^"]*)" page with course ID "([^"]*)"$/
     * @param string $page
     * @param string $courseId
     */
    public function i_am_on_the_page_with_course_id($page, $courseId)
    {
        $url = "/grade/report/gradebook_xp_admin/edit.php?courseid={$courseId}";
        $this->visitPath($url);
    }

    /**
     * @Then /^I should be on the "([^"]*)" page$/
     */
    public function i_should_be_on_the_page($expectedPage)
    {
        # TODO: missing implementation
    }
}
