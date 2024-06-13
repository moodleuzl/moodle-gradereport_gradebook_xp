Feature: Navigation to Edit Page with Course ID
  As a user
  I want to navigate to the edit page for a specific course ID
  So that I can manage competencies effectively

  Scenario: Navigate to Edit Page with Course ID
    Given I am on the "edit.php" page with course ID "123"
    Then I should be on the "edit.php?courseid=123" page

  Scenario: Navigate to Edit Page with Another Course ID
    Given I am on the "edit.php" page with course ID "456"
    Then I should be on the "edit.php?courseid=456" page
