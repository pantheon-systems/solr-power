Feature: Solr Power plugin

  Background:
    Given I log in as an admin

  Scenario: Plugin is loaded
    When I go to "/wp-admin/options-general.php?page=solr-power"
    Then I should see "Solr Power" in the "h2" element

  Scenario: Solr is available
    When I go to "/wp-admin/options-general.php?page=solr-power"
    When I should see "Successful" in the "#solr_info" element

  Scenario: I can submit default schema
    When I go to "/wp-admin/admin.php?page=solr-power#top#solr_action"
    And I press "s4wp_repost_schema"
    Then I should see "Schema Upload Success: 200" in the "#message" element

  Scenario: I see the default schema path in the action dashboard
    When I go to "/wp-admin/admin.php?page=solr-power#top#solr_action"
    Then I should see "To use a custom schema.xml, upload it to the /code/wp-content/uploads/solr-for-wordpress-on-pantheon directory."
