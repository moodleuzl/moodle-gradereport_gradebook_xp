<?php

// Include behat_base for Moodle environment setup
require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../tests/behat/behat_grade.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;

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
            // Get the course ID by shortname
            $courseid = $DB->get_field('course', 'id', ['shortname' => $competency['courseid']]);

            // Initialize the record
            $record = new stdClass();
            $record->courseid = $courseid;
            $record->name = $competency['name'];
            $record->description = $competency['description'];
            $record->maxcomlvl = $competency['maxcomlvl'];

            // If the parent name is not '0', find the parent competency's ID by its name
            if ($competency['parentid'] !== '0') {
                $parentid = $DB->get_field('gradereport_gradebook_xp_com', 'id',
                    ['name' => $competency['parentid'], 'courseid' => $courseid]);
                if (!$parentid) {
                    throw new Exception("The parent competency '{$competency['parentid']}' was not found for course '{$competency['courseid']}'.");
                }
                $record->parentid = $parentid;
            } else {
                $record->parentid = 0; // No parent competency
            }

            // Insert the competency into your custom table.
            $DB->insert_record('gradereport_gradebook_xp_com', $record);
        }
    }

    /**
     * @Then /^the "(?P<field>[^"]*)" field should contain "(?P<value>[^"]*)" for competency "(?P<competency>[^"]*)"$/
     */
    public function theFieldShouldContainForCompetency($field, $value, $competency) {
        // Locate the specific competency section first
        $competencySection = $this->getSession()->getPage()->find(
            'xpath',
            "//h4[contains(text(), '{$competency}')]/following-sibling::table//td[strong[contains(text(), '{$field}:')]]/following-sibling::td"
        );

        if (null === $competencySection) {
            throw new Exception("Field '{$field}' for competency '{$competency}' not found on the page");
        }

        // Check if the field within the competency section contains the expected value.
        if ($competencySection->getText() !== $value) {
            throw new Exception("The field '{$field}' for competency '{$competency}' contains '{$competencySection->getText()}', expected '{$value}'");
        }
    }

    /**
     * @When I click on the "Delete competency" button for :competency
     */
    public function iClickOnTheDeleteCompetencyButtonFor($competency) {
        // Find the delete button associated with the competency
        $deleteButton = $this->getSession()->getPage()->find(
            'xpath',
            "//h4[contains(text(), '{$competency}')]/following-sibling::input[@value='Delete competency']"
        );

        if (null === $deleteButton) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), "button", "text",
                "Delete competency for {$competency}");
        }

        // Click the delete button
        $deleteButton->click();
    }

    /**
     * @When I click on the "Edit competency" button for :competency
     */
    public function iClickOnTheEditCompetencyButtonFor($competency) {
        // Find the delete button associated with the competency
        $deleteButton = $this->getSession()->getPage()->find(
            'xpath',
            "//h4[contains(text(), '{$competency}')]/following-sibling::input[@value='Edit competency']"
        );

        if (null === $deleteButton) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), "button", "text",
                "Edit competency for {$competency}");
        }

        // Click the delete button
        $deleteButton->click();
    }
}