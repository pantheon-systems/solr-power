module.exports = function( grunt ) {

	'use strict';
	// Project configuration
	grunt.initConfig( {

		pkg:    grunt.file.readJSON( 'package.json' ),

		wp_readme_to_markdown: {
			options: {
				screenshot_url: 'https://s.w.org/plugins/{plugin}/{screenshot}.png',
			},
			your_target: {
				files: {
					'README.md': 'readme.txt'
				}
			},
		},

		phpcs: {
			plugin: {
				src: './'
			},
			options: {
				bin: "vendor/bin/phpcs --extensions=php --ignore=\"*/vendor/*,*/node_modules/*\"",
				standard: "phpcs.ruleset.xml"
			}
		},

      /**
       * Clean up the JavaScript
       */
      jshint : {
          options : {
              jshintrc : true
          },
          all     : ['assets/js/admin.js']
      },

      /**
       * Processes and compresses JavaScript.
       */
      uglify : {

          production : {

              options : {
                  beautify         : false,
                  preserveComments : false,
                  mangle           : {
	                  except : ['jQuery']
                  }
              },

              files : {
                  'assets/js/admin.min.js' : [
	                  'assets/js/admin.js'
                  ]
              }
          }
      },

      /**
       * Auto-prefix CSS Elements after SASS is processed.
       */
      autoprefixer : {

          options : {
              browsers : ['last 5 versions'],
              map      : true
          },

          files : {
              expand  : true,
              flatten : true,
              src     : ['assets/css/admin.css'],
              dest    : 'assets/css'
          }
      },

      /**
       * Minify CSS after prefixes are added
       */
      cssmin : {

          target : {

              files : [{
                  expand : true,
                  cwd    : 'assets/css',
                  src    : ['admin.css'],
                  dest   : 'assets/css',
                  ext    : '.min.css'
              }]

          }
      },

      /**
       * Process SASS
       */
      sass : {

          dist : {

              options : {
                  style     : 'expanded',
                  sourceMap : true,
                  noCache   : true
              },

              files : {
                  'assets/css/admin.css' : 'assets/css/scss/admin.scss'
              }
          }
      },

      /**
       * Watch scripts and styles for changes
       */
      watch : {

          options : {
              livereload : false
          },

          scripts : {

              files : [
                  'assets/js/admin.js'
              ],

              tasks : ['uglify:production']

          },

          styles : {

              files : [
                  'assets/css/scss/*'
              ],

              tasks : ['sass', 'autoprefixer', 'cssmin']

          }
      },

		pot: {
			// generate .pot file
			options:{
				text_domain: 'solr-for-wordpress-on-pantheon',
				msgid_bugs_address: 'https://github.com/pantheon-systems/solr-power/issues',
				dest: './',
				language: 'PHP',
				msgmerge: true,
				expand: true,
				// WordPress localization functions
				keywords: [
					'__:1',
					'_e:1',
					'_x:1,2c',
					'esc_html__:1',
					'esc_html_e:1',
					'esc_html_x:1,2c',
					'esc_attr__:1',
					'esc_attr_e:1',
					'esc_attr_x:1,2c',
					'_ex:1,2c',
					'_n:1,2',
					'_nx:1,2,4c',
					'_n_noop:1,2',
					'_nx_noop:1,2,3c'
				]
			},
			files:{
				//Parse all php files, ignore node_modules and vendor directories
				src:  [ '**/*.php', '!node_modules/**/*', '!vendor/**/*' ],
				expand: true
			}
		}


	} );

	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	grunt.loadNpmTasks( 'grunt-phpcs' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-autoprefixer' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks('grunt-pot');
	grunt.registerTask( 'readme', ['wp_readme_to_markdown']);
	grunt.registerTask( 'default', ['jshint', 'uglify:production', 'sass', 'autoprefixer', 'cssmin'] );

	grunt.util.linefeed = '\n';

};
