@gradereport @gradereport_gb_xp_admin
Feature: View Gradebook XP index page
  In order to verify the Gradebook XP Admin index page is displayed correctly
  As an editing teacher
  I need to be able to navigate to the page and see its contents

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student |
      | teacher1 | C1     | teacher |
    And the following competencies in my plugin exist:
      | courseid  | name           | description     | maxcomlvl | islevelsummed |
      | C1        | MyCompetency1  | MyDescription1  | 10        | 1             |
      | C1        | MyCompetency2  | MyDescription2  | 5         | 1             |
      | C1        | MyCompetency3  | MyDescription3  | 2         | 0             |
      | C1        | MyCompetency11 | MyDescription11 | 7         | 1             |
    And the following competency relations in my plugin exist:
      | parentname    | childname      |
      | MyCompetency1 | MyCompetency11 |

  @javascript
  Scenario: View index page
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    And I should see "MyCompetency1"
    And I should see "MyCompetency2"
    And I should see "MyCompetency3"

  @javascript
  Scenario: Verify competency names and values are processed correctly
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook

    # Need to add Activity + Connection + Rating to make this work properly
#    Then the "You" value for "MyCompetency1" should be "0"
#    And the "You" value for "MyCompetency2" should be "0"
#    And the "You" value for "MyCompetency3" should be "0"

#    And the "Maximum Level" value for "MyCompetency1" should be "10"
#    And the "Maximum Level" value for "MyCompetency2" should be "5"
#    And the "Maximum Level" value for "MyCompetency3" should be "2"

  @javascript
  Scenario: Navigate charts
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook

  @javascript
  Scenario: Verify user selection is not visible for students
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should not see "Search users"

  @javascript
  Scenario: Verify user selection is visible for teachers
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "View > Gradebook XP Admin" in the course gradebook
    Then I should see "Search users"
