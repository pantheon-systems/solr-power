/*
 * grunt-wp-readme-to-markdown
 * https://github.com/stephen/wp-readme-to-markdown
 *
 * Copyright (c) 2013 Stephen Harris
 * Licensed under the MIT license.
 */

'use strict';

module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    jshint: {
      all: [
        'Gruntfile.js',
        'tasks/*.js',
        '<%= nodeunit.tests %>',
      ],
      options: {
        jshintrc: '.jshintrc',
      },
    },

    // Before generating any new files, remove any previously-created files.
    clean: {
      tests: ['tmp'],
    },

    // Configuration to be run (and then tested).
    wp_readme_to_markdown: {
      default_options: {
        files: {
          'tmp/readme.md': 'test/fixtures/readme.txt',
        },
      },
      without_screenshot_section: {
		files: {
			'tmp/readme-without-screenshots.md': 'test/fixtures/readme-without-screenshots.txt',
		}
      },
      with_spaces_after_headers: {
		files: {
			'tmp/readme-with-spaces-after-headers.md': 'test/fixtures/readme-with-spaces-after-headers.txt',
		}
      },
      with_spaces_between_plugin_details: {
		files: {
			'tmp/readme-with-spaces-between-plugin-details.md': 'test/fixtures/readme-with-spaces-between-plugin-details.txt',
		}
      },
	
      with_code_blocks: {
		files: {
			'tmp/readme-with-code-blocks.md': 'test/fixtures/readme-with-code-blocks.txt',
		}
      },

    },

    // Unit tests.
    nodeunit: {
      tests: ['test/*_test.js'],
    },

  });

  // Actually load this plugin's task(s).
  grunt.loadTasks('tasks');

  // These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-nodeunit');

  // Whenever the "test" task is run, first clean the "tmp" dir, then run this
  // plugin's task(s), then test the result.
  grunt.registerTask('test', ['clean', 'wp_readme_to_markdown', 'nodeunit']);

  // By default, lint and run all tests.
  grunt.registerTask('default', ['jshint', 'test']);

};
