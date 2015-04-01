Feature: logging in
  In order to browse content
  As an registered user
  I need to be able to log in

  Background:
    Given I have registered user with login "test-login" and password "test123"

  Scenario: login
    When I am on login page
    And I fill in "Username" with "test-login"
    And I fill in "Password" with "test123"
    And I press "Login"
    Then I should be logged in

  Scenario: login with register in background
    When I am logged in with login "test-login-background" and password "test"
    Then I should be logged in

  Scenario Outline: login failures
    Given I have registered user with login "test-login-2" and password "not-my-pwd"
    When I am on login page
    And I fill in "Username" with "<username>"
    And I fill in "Password" with "<password>"
    And I press "Login"
    Then I should not be logged in

    Examples:
    | username      | password          |
    | test-login    |                   |
    | test-login    | invalid-password  |
    | invalid-login | test123           |
    |               | test123           |
    | invalid-login | invalid-password  |
    |               |                   |
    | test-login    | not-my-pwd        |
    | test-login-2  | test123           |

  Scenario: logout
    Given I am logged in with login "test-login" and password "test123"
    When I have logged out
    Then I should not be logged in
