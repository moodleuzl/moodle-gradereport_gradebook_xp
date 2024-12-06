<?php
// This file is part of Moodle - https://moodle.org/.
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Behat steps definitions for the plugin.
 *
 * @package gradereport_gb_xp_admin
 * @category  test
 * @copyright INB University of Luebeck
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include behat_base for Moodle environment setup.
require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;

/**
 * Steps definitions for the plugin.
 *
 * @package gradereport_gb_xp_admin
 */
class behat_gradereport_gb_xp_admin extends behat_base {
    /**
     * Adds competencies to the database.
     *
     * @Given the following competencies in my plugin exist:
     * @param TableNode $table
     * @throws dml_exception
     */
    public function the_following_competencies_in_my_plugin_exist(TableNode $table) {
        global $DB;

        $competencies = $table->getHash();
        foreach ($competencies as $competency) {
            // Get the course ID by shortname.
            $courseid = $DB->get_field('course', 'id', ['shortname' => $competency['courseid']]);

            // Initialize the record.
            $record = new stdClass();
            $record->courseid = $courseid;
            $record->name = $competency['name'];
            $record->description = $competency['description'];
            $record->maxcomlvl = $competency['maxcomlvl'];

            // If the parent name is not '0', find the parent competency's ID by its name.
            if ($competency['parentid'] !== '0') {
                $parentid = $DB->get_field('gradereport_gb_xp_admin_com', 'id',
                    ['name' => $competency['parentid'], 'courseid' => $courseid]);
                if (!$parentid) {
                    throw new Exception(
                        "The parent competency '{$competency['parentid']}' was not found for course '{$competency['courseid']}'.");
                }
                $record->parentid = $parentid;
            } else {
                $record->parentid = 0; // No parent competency.
            }

            // Insert the competency into your custom table.
            $DB->insert_record('gradereport_gb_xp_admin_com', $record);
        }
    }

    /**
     * Verifies that a specific field in the competency section contains the expected value.
     *
     * @Then /^the "(?P<field>[^"]*)" field should contain "(?P<value>[^"]*)" for competency "(?P<competency>[^"]*)"$/
     * @param string $field The field to check.
     * @param string $value The expected value.
     * @param string $competency The name of the competency.
     * @throws Exception if the field or competency is not found or if the value does not match.
     */
    public function the_field_should_contain_for_competency($field, $value, $competency) {
        // Locate the specific competency section first.
        $competencysection = $this->getSession()->getPage()->find(
            'xpath',
            "//h4[contains(text(), "
            . "'{$competency}')]/following-sibling::table//td[strong[contains(text(), "
            . "'{$field}:')]]/following-sibling::td"
        );

        if (null === $competencysection) {
            throw new Exception("Field '{$field}' for competency '{$competency}' not found on the page");
        }

        // Check if the field within the competency section contains the expected value.
        if ($competencysection->getText() !== $value) {
            throw new Exception(
                "The field '{$field}' for competency '{$competency}' "
                        . "contains '{$competencysection->getText()}', expected '{$value}'");
        }
    }

    /**
     * Clicks the "Delete competency" button for the specified competency.
     *
     * @When I click on the "Delete competency" button for :competency
     * @param string $competency The name of the competency to delete.
     */
    public function i_click_on_the_delete_competency_button_for($competency) {
        // Find the delete button associated with the competency.
        $deletebutton = $this->getSession()->getPage()->find(
            'xpath',
            "//h4[contains(text(), '{$competency}')]/following-sibling::input[@value='Delete competency']"
        );

        if (null === $deletebutton) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), "button", "text",
                "Delete competency for {$competency}");
        }

        // Click the delete button.
        $deletebutton->click();
    }

    /**
     * Clicks the "Edit competency" button for the specified competency.
     *
     * @When I click on the "Edit competency" button for :competency
     * @param string $competency The name of the competency to edit.
     */
    public function i_click_on_the_edit_competency_button_for($competency) {
        // Find the delete button associated with the competency.
        $deletebutton = $this->getSession()->getPage()->find(
            'xpath',
            "//h4[contains(text(), '{$competency}')]/following-sibling::input[@value='Edit competency']"
        );

        if (null === $deletebutton) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), "button", "text",
                "Edit competency for {$competency}");
        }

        // Click the delete button.
        $deletebutton->click();
    }
}
