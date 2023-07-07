<?php

/**
 * @group should-commit
 */
class ShouldCommitTest extends WP_UnitTestCase
{
    public function testShouldNotCommitWhenConstNull(): void
    {
        if ( defined('SOLRPOWER_DISABLE_AUTOCOMMIT') ) {
            echo var_dump(SOLRPOWER_DISABLE_AUTOCOMMIT).PHP_EOL;
            $this->fail("SOLRPOWER_DISABLE_AUTOCOMMIT unexpectedly defined.");
        }
        $result = SolrPower_Sync::get_instance()->should_commit();
        $this->assertFalse($result);
    }

    public function testShouldNotCommitWhenConstTrue(): void
    {
        define( 'SOLRPOWER_DISABLE_AUTOCOMMIT', true);
        $result = SolrPower_Sync::get_instance()->should_commit();
        $this->assertFalse($result);  
    }

    public function testShouldCommitWhenConstFalse(): void
    {
        define( 'SOLRPOWER_DISABLE_AUTOCOMMIT', false);
        $result = SolrPower_Sync::get_instance()->should_commit();
        $this->assertTrue($result);
    }
}