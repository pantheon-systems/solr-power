Feature: Solr Power plugin

  Background:
    Given I log in as an admin

  Scenario: Plugin is loaded
    When I go to "/wp-admin/options-general.php?page=solr-power"
    Then I should see "Solr Power" in the "h2" element

  Scenario: Solr is available
    When I go to "/wp-admin/options-general.php?page=solr-power"
    When I should see "Successful" in the "#solr_info" element
