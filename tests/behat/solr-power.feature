Feature: Solr Power plugin

  Background:
    Given I am on "wp-login.php"
    And I fill in "log" with "pantheon"
    And I fill in "pwd" with "pantheon"
    And I press "wp-submit"
    Then print current URL
    And I should be on "/wp-admin/"

  Scenario: Plugin is loaded
    When I go to "/wp-admin/options-general.php?page=solr-power"
    Then I should see "Solr Power" in the "h2" element
