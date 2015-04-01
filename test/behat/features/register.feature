Feature: registering users
  In order to browse content
  As an anonymous user
  I need to be able to register my account

  Scenario: register new user
    Given I am on register page
#    When I fill in the following:
#    | Email:         | Username:    | Password:   | Verification: | First name: | Last name: |
#    | test@test.pl   | test         | test123     | test123       | Testing     | Tester     |
    When I fill in "Email" with "test@test.pl"
    And I fill in "Username" with "test-register"
    And I fill in "Password" with "test123"
    And I fill in "Verification" with "test123"
    And I fill in "First name" with "Test"
    And I fill in "Last name" with "Tester"
    And I press "Register"
    Then I should be registered




