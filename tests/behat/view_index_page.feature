@grade @gradereport_gradebook_xp_admin
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

  @javascript
  Scenario: View Gradebook XP Admin index page
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    And I should see "Manage Competencies"
    And I should see "Export"
    And I should see "Import"
