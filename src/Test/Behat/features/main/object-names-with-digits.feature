Feature: Test object names starting with digits

  Scenario: Quoted object names can start with digits
    Given there is a "generic entity" "123" with
      | prop1 |
      | foo   |
    Then "generic entity" "123" should exist
    And "generic entity" "123" should have properties
      | prop1 |
      | foo   |

  Scenario: Quoted object names can be pure numbers
    Given there is a contact "007"
    Then contact "007" should exist

  Scenario: Count assertions still work correctly alongside digit names
    Given there is a contact "1"
    And there is a contact "2"
    Then 2 contacts should exist
    And contact "1" should exist
    And contact "2" should exist
