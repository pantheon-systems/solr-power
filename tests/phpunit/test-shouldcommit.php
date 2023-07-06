<?php

/**
 * @group should-commit
 */
class ShouldCommitTest extends WP_UnitTestCase
{	
    protected $preserveGlobalState = FALSE;
    /**
     * Tests how shouldCommit handles different values for the SOLRPOWER_DISABLE_AUTOCOMMIT constant
     *
     * @dataProvider shouldCommitTestValues
     * @runInSeparateProcess
     *
     */
    public function testShouldCommit($const_value, $expected): void
    {
        echo "provided: $const_value, exp: $expected".PHP_EOL;

        if ( defined('SOLRPOWER_DISABLE_AUTOCOMMIT') ) {
            echo "SOLRPOWER_DISABLE_AUTOCOMMIT already defined and '".SOLRPOWER_DISABLE_AUTOCOMMIT."'".PHP_EOL;
        }

        if ( ! defined('SOLRPOWER_DISABLE_AUTOCOMMIT') && ! is_null($const_value) ) {
            echo "Defining as part of test run!".PHP_EOL;
            define( 'SOLRPOWER_DISABLE_AUTOCOMMIT', $const_value);
        }

        if ( defined('SOLRPOWER_DISABLE_AUTOCOMMIT') ) {
            echo var_dump(SOLRPOWER_DISABLE_AUTOCOMMIT).PHP_EOL;
        } else {
            echo "NOT DEFINED".PHP_EOL;
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
            "const is nil"=>[null, false],
            "const is true"=>[true, false],
            "const is false"=>[false, true],
        ];
    }
}