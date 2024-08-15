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
    And the following competencies in my plugin exist:
      | courseid  | name         | parentid | description     | maxcomlvl |
      | C1        | MyCompetency1  | 0        | MyDescription1  | 10        |
      | C1        | MyCompetency2  | 0        | MyDescription2  | 5         |
      | C1        | MyCompetency3  | 0        | MyDescription3  | 2         |

  @javascript
  Scenario: View index page
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    And I should see "MyCompetency1"
    And I should see "MyCompetency2"
    And I should see "MyCompetency3"

  @javascript
  Scenario: View manage_competencies page
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    Then I click on "Manage Competencies" "button"
    Then I should see "List of competencies"

    And I should see "MyCompetency1"
    And the "Parent ID" field should contain "0" for competency "MyCompetency1"
    And the "Max Level" field should contain "10" for competency "MyCompetency1"
    And the "Name" field should contain "MyCompetency1" for competency "MyCompetency1"
    And the "Parent Name" field should contain "" for competency "MyCompetency1"
    And the "Description" field should contain "MyDescription1" for competency "MyCompetency1"

    And I should see "MyCompetency2"
    And the "Parent ID" field should contain "0" for competency "MyCompetency2"
    And the "Max Level" field should contain "5" for competency "MyCompetency2"
    And the "Name" field should contain "MyCompetency2" for competency "MyCompetency2"
    And the "Parent Name" field should contain "" for competency "MyCompetency2"
    And the "Description" field should contain "MyDescription2" for competency "MyCompetency2"

    And I should see "MyCompetency2"
    And the "Parent ID" field should contain "0" for competency "MyCompetency3"
    And the "Max Level" field should contain "2" for competency "MyCompetency3"
    And the "Name" field should contain "MyCompetency3" for competency "MyCompetency3"
    And the "Parent Name" field should contain "" for competency "MyCompetency3"
    And the "Description" field should contain "MyDescription3" for competency "MyCompetency3"

  @javascript
  Scenario: Delete a competency
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    Then I click on "Manage Competencies" "button"
    Then I should see "List of competencies"

    And I should see "MyCompetency1"

    When I click on the "Delete competency" button for "MyCompetency1" and confirm the deletion

    Then I should see "You have successfully deleted the competency."
    # I should not see "MyCompetency1"
