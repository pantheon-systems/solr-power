/*
 * grunt-pot
 * https://github.com/stephenharris/grunt-pot
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

    copy:{
	test:{
		files: [ {expand: true, cwd: 'test/fixtures/', src: ['msgmerge-po.po'], dest: 'tmp/'} ]
	}
    },

    // Configuration to be run (and then tested).
    pot: {
	options:{
		text_domain: 'my-text-domain',
		package_name: 'my-project',
		package_version: '1.0.0',
		dest: 'tmp/',
		keywords: ['__','gettext', 'ngettext:1,2', 'pgettext:1c,2' ],
		msgmerge: true
	},
	files: {
		src: ['test/fixtures/*.php'],
		expand: true,
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
  grunt.loadNpmTasks('grunt-contrib-copy');

  // Whenever the "test" task is run, first clean the "tmp" dir, then run this
  // plugin's task(s), then test the result.
  grunt.registerTask('test', ['clean', 'copy:test', 'pot', 'nodeunit']);

  // By default, lint and run all tests.
  grunt.registerTask('default', ['jshint', 'test']);

};
