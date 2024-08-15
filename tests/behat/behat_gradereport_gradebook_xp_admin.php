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
     * @When I click on the "Delete competency" button for :competency and confirm the deletion
     */
    public function iClickOnTheDeleteCompetencyButtonForAndConfirmTheDeletion($competency)
    {
        // Find the delete button associated with the competency
        $deleteButton = $this->getSession()->getPage()->find(
            'xpath',
            "//h4[contains(text(), '{$competency}')]/following-sibling::input[@value='Delete competency']"
        );

        if (null === $deleteButton) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), "button", "text", "Delete competency for {$competency}");
        }

        // Click the delete button
        $deleteButton->click();

        // Accept the alert
        $driver = $this->getSession()->getDriver();
        $driver->getWebDriverSession()->accept_alert();
    }
}