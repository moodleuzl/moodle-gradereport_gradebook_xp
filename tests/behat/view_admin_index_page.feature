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
#    And I should see "MyCompetency1"
#    And I should see "MyCompetency2"
#    And I should see "MyCompetency3"

  @javascript
  Scenario: View manage_competencies page
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    Then I click on "Edit" "button"
#    Then I should see "Manage Competencies"

#    And I should see "MyCompetency1"
#    And the "Parent ID" field should contain "0" for competency "MyCompetency1"
#    And the "Max Level" field should contain "10" for competency "MyCompetency1"
#    And the "Name" field should contain "MyCompetency1" for competency "MyCompetency1"
#    And the "Parent Name" field should contain "" for competency "MyCompetency1"
#    And the "Description" field should contain "MyDescription1" for competency "MyCompetency1"

#    And I should see "MyCompetency2"
#    And the "Parent ID" field should contain "0" for competency "MyCompetency2"
#    And the "Max Level" field should contain "5" for competency "MyCompetency2"
#    And the "Name" field should contain "MyCompetency2" for competency "MyCompetency2"
#    And the "Parent Name" field should contain "" for competency "MyCompetency2"
#    And the "Description" field should contain "MyDescription2" for competency "MyCompetency2"

#    And I should see "MyCompetency3"
#    And the "Parent ID" field should contain "0" for competency "MyCompetency3"
#    And the "Max Level" field should contain "2" for competency "MyCompetency3"
#    And the "Name" field should contain "MyCompetency3" for competency "MyCompetency3"
#    And the "Parent Name" field should contain "" for competency "MyCompetency3"
#    And the "Description" field should contain "MyDescription3" for competency "MyCompetency3"

  @javascript
  Scenario: Add a competency
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    Then I click on "Edit" "button"
#    Then I should see "Manage Competencies"
#    When I click on "Add competency" "button"
#    And I set the following fields to these values:
#      | Competency name         | NewCompetency             |
#      | Competency description  | NewCompetencyDescription  |
#      | Max Competency Level    | 7                         |
#    And I click on "Save changes" "button"
#    Then I should see "You have successfully created the competency: 'NewCompetency'"
#    And I should see "NewCompetency"

  @javascript
  Scenario: Edit a competency
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    Then I click on "Edit" "button"
#    Then I should see "Manage Competencies"
#    When I click on the "Edit competency" button for "MyCompetency1"
#    And I set the following fields to these values:
#      | Competency name         | EditedCompetency             |
#      | Competency description  | EditedCompetencyDescription  |
#      | Max Competency Level    | 8                            |
#    And I click on "Save changes" "button"
#    Then I should see "You have successfully updated the competency: 'EditedCompetency'"
#    And I should see "EditedCompetency"

  @javascript
  Scenario: Delete a competency
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Gradebook XP Admin"
    Then I click on "Edit" "button"
#    Then I should see "Manage Competencies"

#    And I should see "MyCompetency1"
#
#    When I click on the "Delete competency" button for "MyCompetency1"
#    Then I click on "Delete" "button" in the ".modal-dialog" "css_element"
#
#    Then I should see "You have successfully deleted the competency."
#    And I should not see "MyCompetency1"
