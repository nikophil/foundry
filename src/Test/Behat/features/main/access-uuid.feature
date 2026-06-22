Feature: Test accessing Uuid-based entity ids

  Scenario: Can create and reference Uuid entity
    Given there is an "entity with uid" named "the object"
    Then "entity with uid" named "the object" should exist

  Scenario: Can access last id for Uuid entity via lastId transform
    Given there is an "entity with uid" named "A"
    # The <lastId> transform resolves the Uuid to its RFC 4122 string format.
    # We just need to verify the transform doesn't throw, the 404 is expected.
    When I am on "/<lastId>"
    Then the response status code should be 404

  Scenario: Can access last id for specific Uuid entity type
    Given there is an "entity with uid" named "B"
    When I am on "/<lastId(entity with uid)>"
    Then the response status code should be 404

  Scenario: Can access id from reference for Uuid entity
    Given there is an "entity with uid" named "my-uid-entity"
    When I am on "/<id(entity with uid, my-uid-entity)>"
    Then the response status code should be 404
