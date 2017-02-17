Feature: User controll access
  In order to provide user identity and autorization functionality
  As a developer
  I need to control Sign In / Sign Up processes and ACL

  Points:
  - Login by username / e-mail and password
  - Registration with confirm message and without
  - Confirmation e-mail
  - Restore password
  - Change password
  - Setup user profile
  - ACL
  - Request Sign In when get deny resource
  - Control users by admin

  Scenario: Login user
    Given this user with name "user" and password "pass"
    And this service with URI "/test" and access permissions "logged only"
    When I request the "/login" service
    Then I see login form
    When I send login data with name "ser" and password "pas"
    Then I see login form with message "Incorrect username/password"
    When I send login data with name "user" and password "pas"
    Then I see login form with message "Incorrect username/password"
    When I send login data with name "ser" and password "pass"
    Then I see login form with message "Incorrect username/password"
    When I send login data with name "user" and password "pass"
    Then I see "/test" service output

