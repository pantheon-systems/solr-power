/*
 * grunt-phpcs
 * https://github.com/SaschaGalley/grunt-phpcs
 *
 * Copyright (c) 2013 Sascha Galley
 * http://xash.at
 * Licensed under the MIT license.
 */
'use strict';

module.exports = function(grunt) {

    var path = require('path'),
        exec = require('child_process').exec;
    
    var command = {
            flags: {
                verbose: 'v',
                showSniffCodes: 's'
            },
            options: {
                errorSeverity: 'error-severity',
                report: 'report',
                reportFile: 'report-file',
                severity: 'severity',
                standard: 'standard',
                warningSeverity: 'warning-severity',
                tabWidth: 'tab-width'
            }
        },
        defaults = {
            bin: 'phpcs',
            report: 'full',
            maxBuffer: 200*1024
        },
        done = null;
    
    grunt.registerMultiTask('phpcs', 'Run PHP Code Sniffer', function() {
        var done = null,
            parameters = null,
            target = this.target,
            options = this.options(defaults),
            execute = path.normalize(options.bin),
            files = [].concat.apply([], this.files.map(function(mapping) { return mapping.src; })).sort();
        
        // removes duplicate files
        files = files.filter(function(file, position) { 
            return !position || file !== files[position - 1];
        });
        
        // generates parameters
        parameters = Object.keys(options).map(function(option) {
            return option in command.flags && options[option] === true ? 
                '-' + command.flags[option] : option in command.options && options[option] !== undefined ? 
                    '--' + command.options[option] + '=' + options[option] : null;
        }).filter(Boolean);
        
        execute += ' ' + parameters.join(' ') + ' "' + files.join('" "') + '"';
        
        grunt.verbose.writeln('Executing: ' + execute);
        
        done = this.async();
        
        exec(execute, {maxBuffer: options.maxBuffer}, function(error, stdout, stderr) {
            /* jshint -W030 */
            typeof options.callback === 'function' && options.callback.call(this, error, stdout, stderr, done);
            stdout && grunt.log.write(stdout);
            error && grunt.fail.warn(stderr ? stderr : 'Task phpcs:' + target + ' failed.');
            !error && grunt.log.ok(files.length + ' file' + (files.length === 1 ? '' : 's') + ' lint free.');
            done(error);
        });
    });
};