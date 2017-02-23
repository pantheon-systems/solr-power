/*
 * grunt-pot
 * https://github.com/stephenharris/grunt-potW
 *
 * Copyright (c) 2013 Stephen Harris
 * Licensed under the MIT license.
 */

'use strict';

var fs = require('fs');

module.exports = function(grunt) {

  grunt.registerMultiTask('pot', 'Scan files and create a .pot file using xgettext', function() {

	var dest = false, inputFiles = "", headerOptions ="";

	var pkg = grunt.file.readJSON('package.json');

    var options = this.options({
		dest: false,
		overwrite: true,
		keywords: false,
		language: false,
		encoding: false,
		text_domain: 'messages',
		add_location: false,
		package_version: pkg.version,
		package_name: pkg.name,
		msgid_bugs_address: false,
		omit_header: false,
		copyright_holder: false,
		comment_tag: '/',
		msgmerge: false,
	});

	grunt.verbose.writeflags(options, 'Pot options');

	var potFile = options.dest;

	// If destination is a directory, build a file based on text domain
	if( options.dest && fs.lstatSync(options.dest).isDirectory() ){
		potFile = options.dest.replace(/\/$/, "") + "/" + options.text_domain + ".pot";
	}

	if( !grunt.file.exists(potFile) ){
		grunt.file.write(potFile);
	}

	grunt.log.writeln('Destination: ' + potFile);

	//Set join mode
	var join = ( !options.overwrite ? " --join-existing" : "" );

	//Implode keywards
	var keywords = ( options.keywords ? " --keyword=" + options.keywords.join( " --keyword=" ) : "" );

	//Set input files language, if specified
	var language = ( options.language ? " --language="+options.language : "" );

	//Set input files encoding, if required
	var encoding = ( options.encoding ? " --from-code="+options.encoding : "" );
	
	// Set location format (default: full)
	var add_location = ( options.add_location ? " --add-location="+options.add_location : "" );

	//Generate header
	if( options.package_version ){
		headerOptions += " --package-version="+options.package_version;
	}

	if( options.package_name ){
		headerOptions += " --package-name="+options.package_name;
	}

	if( options.msgid_bugs_address ){
		headerOptions += " --msgid-bugs-address="+ options.msgid_bugs_address;
	}

	if( options.omit_header ){
		headerOptions += " --omit-header";
	}

	if( options.copyright_holder ){
		headerOptions += " --copyright-holder='"+options.copyright_holder+"'";
	}

	if( options.comment_tag ){
		headerOptions += " --add-comments='"+options.comment_tag+"'";
	}

	//Generate list of files to scan
	this.files.forEach(function(file) {
		if( !grunt.file.isDir( file.src[0] ) ){
			inputFiles +=  " " + file.src[0];
		}
	});

	//Compile and run command
	var exec = require('child_process').exec;
	var command = 'xgettext' + join + ' --default-domain=' + options.text_domain + ' -o '+potFile + language + encoding + keywords + add_location + headerOptions + inputFiles;
	var done = grunt.task.current.async(); 
	
	grunt.verbose.writeln('Executing: ' + command);

	exec( command,
		function(error, stdout, stderr){

			grunt.verbose.writeln('stderr: ' + stderr);

			if( options.msgmerge ){

				//If msmerge-ing then use specified directory or assume .po files are in same location has .pot
				var poFiles = ( options.msgmerge === true ? options.dest.replace(/\/$/, "") + "/*.po" : options.msgmerge.replace(/\/$/, "") + "/*.po" );
				var poFilePaths = grunt.file.expand( poFiles );

				var count = poFilePaths ? poFilePaths.length : 0;
				grunt.verbose.writeln( count + " .po files found for msgmerge" );

			}

			if( poFilePaths && options.msgmerge ){

				poFilePaths.forEach( function( poFile ) {
					exec( 'msgmerge -U ' + poFile +' ' + potFile, function(error, stdout, stderr) {});
				});

			}

			done( error ); //error will be null if command executed without errors.
		}
  	);

  });

};
