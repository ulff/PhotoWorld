Feature: logging in
  In order to browse content
  As an registered user
  I need to be able to log in

  Scenario: login
    Given I have registered user with login "test-login" and password "test123"
    When I am on login page
    And I fill in "Username" with "test-login"
    And I fill in "Password" with "test123"
    And I press "Login"
    Then I should be logged in

  Scenario: login: 2
    Given I am logged in with login "test-login" and password "test123"
    Then I should be logged in

  Scenario: logout
    Given I am logged in with login "test-login" and password "test123"
    When I have logged out
    Then I should not be logged in

