@qtype @qtype_wq @wq @regression
Feature: Background WIRIS responses preserve the browser session
    A background request may still carry the session ID from before a login or logout.
    Its response must not overwrite the browser's newer Moodle session cookie.

    Scenario Outline: A WIRIS service cannot replace an obsolete session cookie
        Given the following config values are set as admin:
            | access_provider_enabled | 0 | qtype_wq |
        Then the WIRIS "<service>" service does not replace an obsolete session cookie

        Examples:
            | service  |
            | resource |
            | echo     |

    Scenario Outline: Access control still rejects an obsolete session cookie
        Given the following config values are set as admin:
            | access_provider_enabled | 1 | qtype_wq |
        Then the WIRIS "<service>" service rejects an obsolete session cookie

        Examples:
            | service  |
            | resource |
            | echo     |

    @javascript
    Scenario: Authenticated users can still load protected WIRIS services
        Given the following "users" exist:
            | username | firstname | lastname | email               |
            | student1 | Student   | One      | student@example.com |
        And the following config values are set as admin:
            | access_provider_enabled | 1 | qtype_wq |
        When I log in as "student1"
        Then the authenticated WIRIS "resource" service is available
        And the authenticated WIRIS "echo" service is available
