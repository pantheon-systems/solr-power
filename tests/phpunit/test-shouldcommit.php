<?php

class ShouldCommitTest extends WP_UnitTestCase
{	
    /**
     * Tests reading, parsing, and validating a sites.yml file.
     *
     * @dataProvider shouldCommitTestValues
     *
     */
    public function testShouldCommit($const_value, $expected): void
    {
        if ( ! defined('SOLRPOWER_DISABLE_AUTOCOMMIT') && ! is_null($const_value) ) {
            define( 'SOLRPOWER_DISABLE_AUTOCOMMIT', $const_value);
        }
        
        $result = SolrPower_Sync::get_instance()->should_commit();

        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testShouldCommit.
     *
     * Return an array of arrays, each of which contains the parameter
     * values to be used in one invocation of the testShouldCommit test function.
     */
    public function shouldCommitTestValues(): array
    {
        return [
            "const is nil"=>[null,true],
            "const is true"=>[false,true],
            "const is false"=>[true, false],
        ];
    }
}