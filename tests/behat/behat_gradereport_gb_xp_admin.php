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
    /** @var array map "name" => inserted id, useful for later steps if needed */
    protected $createdcompetencies = [];
    /**
     * Create basic competencies (no relations).
     *
     * @Given /^the following competencies in my plugin exist:$/
     * @param TableNode $table
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function the_following_competencies_in_my_plugin_exist(TableNode $table) {
        global $DB;

        foreach ($table->getHash() as $row) {
            // Validate required fields.
            if ((!isset($row['course']) && !isset($row['courseid'])) || empty($row['name'])) {
                throw new moodle_exception('Missing required "course/courseid" or "name" in competencies table.');
            }

            // Resolve courseid.
            if (!empty($row['course'])) {
                // Prefer explicit 'course' column as a shortname.
                $courseid = $DB->get_field('course', 'id', ['shortname' => trim($row['course'])], MUST_EXIST);
            } else {
                // Fallback to 'courseid' which may be a numeric id or a shortname.
                $token = trim($row['courseid']);
                if ($token !== '' && ctype_digit($token)) {
                    $courseid = (int)$token;
                    if (!$DB->record_exists('course', ['id' => $courseid])) {
                        throw new moodle_exception('Invalid course id '.$courseid.' in competencies table.');
                    }
                } else {
                    $courseid = $DB->get_field('course', 'id', ['shortname' => $token], MUST_EXIST);
                }
            }

            // Build record with defaults matching XMLDB.
            $record = (object)[
                'courseid'      => $courseid,
                'name'          => trim($row['name']),
                'description'   => isset($row['description']) && $row['description'] !== '' ? (string)$row['description'] : null,
                'maxcomlvl'     => isset($row['maxcomlvl']) && $row['maxcomlvl'] !== '' ? (int)$row['maxcomlvl'] : 1,
                'islevelsummed' => isset($row['islevelsummed']) && $row['islevelsummed'] !== '' ? (int)$row['islevelsummed'] : 1,
            ];

            // Insert into new table.
            $id = $DB->insert_record('gradereport_gb_xp_admin_competencies', $record);

            // Keep a reference by name (useful for follow-up steps).
            $this->createdcompetencies[$record->name] = $id;
        }
    }

    /**
     * Create competency-to-competency relations.
     *
     * Example:
     *   And the following competency relations in my plugin exist:
     *     | parentname   | childname      |
     *     | Cat Handling | Dog Behaviour  |
     *     | Cat Handling | Surgery Basics |
     *
     * @Given /^the following competency relations in my plugin exist:$/
     * @param TableNode $table
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function the_following_competency_relations_in_my_plugin_exist(TableNode $table) {
        global $DB;

        foreach ($table->getHash() as $row) {
            if (empty($row['parentname']) || empty($row['childname'])) {
                throw new moodle_exception('Missing required "parentname" or "childname" column.');
            }

            $parentname = trim($row['parentname']);
            $childname  = trim($row['childname']);

            // Look up IDs from the ones we already created in this test run.
            if (!isset($this->createdcompetencies[$parentname])) {
                $parentid = $DB->get_field('gradereport_gb_xp_admin_competencies', 'id', ['name' => $parentname], MUST_EXIST);
            } else {
                $parentid = $this->createdcompetencies[$parentname];
            }

            if (!isset($this->createdcompetencies[$childname])) {
                $childid = $DB->get_field('gradereport_gb_xp_admin_competencies', 'id', ['name' => $childname], MUST_EXIST);
            } else {
                $childid = $this->createdcompetencies[$childname];
            }

            // Prevent self-relations or duplicates.
            if ($parentid == $childid) {
                throw new moodle_exception("Competency '{$parentname}' cannot be related to itself.");
            }

            $exists = $DB->record_exists('gradereport_gb_xp_admin_relations', [
                'parentid' => $parentid,
                'childid'  => $childid
            ]);

            if (!$exists) {
                $DB->insert_record('gradereport_gb_xp_admin_relations', (object)[
                    'parentid' => $parentid,
                    'childid'  => $childid
                ]);
            }
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

    /**
     * Step definition to check if a specific competency has the correct value for the given level type.
     *
     * @param string $leveltype The dataset label (e.g., 'Maximum Level', 'Maximum Reachable Level', 'You').
     * @param string $competency The name of the competency (e.g., 'MyCompetency1').
     * @param string $value The expected value for the level type and competency (e.g., '10').
     * @throws Exception If the competency or value is not found or does not match.
     *
     * @Then /^the "([^"]*)" value for "([^"]*)" should be "([^"]*)"$/
     */
    public function thelevelvalueforcompetencyshouldbe($leveltype, $competency, $value) {
        // Get the full page source (HTML including script).
        $pagesource = $this->getSession()->getPage()->getContent();

        // Extract the labels array from the page source.
        $labelsstart = strpos($pagesource, "labels: [");
        if ($labelsstart === false) {
            throw new Exception("The 'labels' array was not found in the page source.");
        }
        $labelsend = strpos($pagesource, "]", $labelsstart);
        $labelsarray = substr($pagesource, $labelsstart + strlen("labels: ["), $labelsend - $labelsstart - strlen("labels: ["));
        $labelsarray = explode(",", str_replace(["'", " "], "", $labelsarray)); // Clean and split into array.

        // Find the index of the competency in the labels array.
        $competencyindex = array_search($competency, $labelsarray);
        if ($competencyindex === false) {
            throw new Exception("The competency '{$competency}' was not found in the 'labels' array.");
        }

        // Find the position of the level type (e.g., 'Maximum Level') in the page source.
        $levelposition = strpos($pagesource, "label: '{$leveltype}'");
        if ($levelposition === false) {
            throw new Exception("The '{$leveltype}' dataset was not found in the page source.");
        }

        // Extract the data array for the specified level type.
        $datastart = strpos($pagesource, 'data: [', $levelposition);
        if ($datastart === false) {
            throw new Exception("No data array found for the '{$leveltype}' dataset.");
        }
        $dataend = strpos($pagesource, ']', $datastart);
        $dataarray = substr($pagesource, $datastart + strlen('data: ['), $dataend - $datastart - strlen('data: ['));
        $dataarray = explode(",", str_replace(" ", "", $dataarray)); // Clean and split into array.

        // Check if the value at the competency's index in the data array matches the expected value.
        if (!isset($dataarray[$competencyindex])) {
            throw new Exception(
                "No data found for the competency '{$competency}' at index {$competencyindex} in the '{$leveltype}' dataset.");
        }

        if ($dataarray[$competencyindex] !== $value) {
            throw new Exception(
                "The value '{$dataarray[$competencyindex]}' at index {$competencyindex} "
                . "for competency '{$competency}' does not match the expected value '{$value}' "
                . "in the '{$leveltype}' dataset.");
        }
    }
}
