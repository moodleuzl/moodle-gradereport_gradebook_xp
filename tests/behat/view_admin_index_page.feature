@gradereport @gradereport_gb_xp_admin
Feature: View Gradebook XP Admin index page
  In order to verify the Gradebook XP Admin index page is displayed correctly
  As an editing teacher
  I need to be able to navigate to the page and see its contents

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following competencies in my plugin exist:
      | courseid  | name           | description     | maxcomlvl | islevelsummed |
      | C1        | MyCompetency1  | MyDescription1  | 10        | 1             |
      | C1        | MyCompetency2  | MyDescription2  | 5         | 1             |
      | C1        | MyCompetency3  | MyDescription3  | 2         | 0             |

  @javascript
  Scenario: View index page
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    And I should see "Search users"
    And I should see "Edit"

  @javascript
  Scenario: View manage_competencies page
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    Then I click on "Edit" "button"
    And I wait until the page is ready
    Then I should see "Manage Competencies"

    # TODO: Make this cleaner with tags/ids/names of the actual fields
    And I should see "MyCompetency1"
    And I should see "MyDescription1"
    And I should see "Max Level: 10"
    And I should see " • Level Summed"

    And I should see "MyCompetency2"
    And I should see "MyDescription2"
    And I should see "Max Level: 5"
    And I should see " • Level Summed"

    And I should see "MyCompetency1"
    And I should see "MyDescription1"
    And I should see "Max Level: 2"
#    And I should not see " • Level Summed"

  @javascript
  Scenario: Add a competency
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    Then I click on "Edit" "button"
    And I wait until the page is ready
    Then I should see "Manage Competencies"
    When I click on "Add competency" "button"
    Then I should see "New competency"
    And I fill the React competency form with:
      | New Competency 1 | Gentle restraint | 3 | 1 |
    And I press "Create"
    # TODO: Check if creation was successful

  @javascript
  Scenario: Edit a competency
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    Then I click on "Edit" "button"
    And I wait until the page is ready
    Then I should see "Manage Competencies"
    # TODO: Check editing a competency (find edit button and click it and check if changes apply)

  @javascript
  Scenario: Delete a competency
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    Then I click on "Edit" "button"
    And I wait until the page is ready
    Then I should see "Manage Competencies"

    And I should see "MyCompetency1"
    # TODO: Check deleting a competency (find the button and click it and check if removed)
