<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given I am on login page
     * @Given I go to login page
     */
    public function iAmOnLoginPage()
    {
        $this->visit('/login');
    }

    /**
     * @Given I am on register page
     * @Given I go to register page
     */
    public function iAmOnRegisterPage()
    {
        $this->visit('/register');
    }

    /**
     * @Given I have registered user with login :login and password :password
     */
    public function iHaveRegisteredUserWithLoginAndPassword($login, $password)
    {
        $email = $login.'@test.pl';
        $firstname = ucfirst($login);
        $lastname = ucfirst($login);

        $this->iAmOnRegisterPage();
        $this->fillField('Email', $email);
        $this->fillField('Username', $login);
        $this->fillField('Password', $password);
        $this->fillField('Verification', $password);
        $this->fillField('First name', $firstname);
        $this->fillField('Last name', $lastname);
        $this->pressButton('Register');
    }

    /**
     * @Given I am logged in with login :login and password :password
     */
    public function iAmLoggedInWithLoginAndPassword($login, $password)
    {
        $this->iHaveRegisteredUserWithLoginAndPassword($login, $password);
        $this->iAmOnLoginPage();
        $this->fillField('Username', $login);
        $this->fillField('Password', $password);
        $this->pressButton('Login');
    }

    /**
     * @When I have logged out
     */
    public function iHaveLoggedOut()
    {
        $this->visit('/logout');
    }

    /**
     * @Then I should be registered
     */
    public function iShouldBeRegistered()
    {
        $this->assertPageContainsText('Registration successful');
    }

    /**
     * @Then I should be logged in
     */
    public function iShouldBeLoggedIn()
    {
        $this->assertPageNotContainsText('Log in');
        $this->assertPageNotContainsText('Sign up');
    }

    /**
     * @Then I should not be logged in
     */
    public function iShouldNotBeLoggedIn()
    {
        $this->assertPageContainsText('Log in');
        $this->assertPageContainsText('Sign up');
    }


}
