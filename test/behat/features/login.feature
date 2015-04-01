Feature: publishing
  In order to create content
  As an editor
  I need to be able to publish new articles

  Scenario: login
    And I am on login page
    And I fill in "Username" with "test"
    And I fill in "Password" with "test"
    And I press "Log In"
    And I should be logged in