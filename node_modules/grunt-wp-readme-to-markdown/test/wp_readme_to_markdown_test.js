'use strict';

var grunt = require('grunt');

/*
  ======== A Handy Little Nodeunit Reference ========
  https://github.com/caolan/nodeunit

  Test methods:
    test.expect(numAssertions)
    test.done()
  Test assertions:
    test.ok(value, [message])
    test.equal(actual, expected, [message])
    test.notEqual(actual, expected, [message])
    test.deepEqual(actual, expected, [message])
    test.notDeepEqual(actual, expected, [message])
    test.strictEqual(actual, expected, [message])
    test.notStrictEqual(actual, expected, [message])
    test.throws(block, [error], [message])
    test.doesNotThrow(block, [error], [message])
    test.ifError(value)
*/

exports.wp_readme_to_markdown = {
  setUp: function(done) {
    // setup here if necessary
    done();
  },
  default_options: function(test) {
    test.expect(1);

    var actual = grunt.file.read('tmp/readme.md');
    var expected = grunt.file.read('test/expected/readme.md');
    test.equal(actual, expected, 'should describe what the default behavior is.');

    test.done();
  },

    without_screenshot_section: function(test) {
    test.expect(1);

    var actual = grunt.file.read('tmp/readme-without-screenshots.md');
    var expected = grunt.file.read('test/expected/readme-without-screenshots.md');
    test.equal(actual, expected, 'should describe what the default behavior is.');

    test.done();
  },

   with_spaces_after_headers: function( test ){
    test.expect(1);

    var actual = grunt.file.read('tmp/readme-with-spaces-after-headers.md');
    var expected = grunt.file.read('test/expected/readme-with-spaces-after-headers.md');
    test.equal(actual, expected );

    test.done();
  },

   with_spaces_between_plugin_details: function( test ){
    test.expect(1);

    var actual = grunt.file.read('tmp/readme-with-spaces-between-plugin-details.md');
    var expected = grunt.file.read('test/expected/readme-with-spaces-between-plugin-details.md');
    test.equal(actual, expected );

    test.done();
  },
  
  with_code_blocks: function( test ){
	    test.expect(1);

	    var actual = grunt.file.read('tmp/readme-with-code-blocks.md');
	    var expected = grunt.file.read('test/expected/readme-with-code-blocks.md');
	    test.equal(actual, expected );

	    test.done();
  },

};
