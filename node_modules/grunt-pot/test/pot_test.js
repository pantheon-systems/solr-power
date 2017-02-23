'use strict';

var grunt = require('grunt');

exports.pot = {
  setUp: function(done) {
    done();
  },
  files: function(test) {
    test.expect(1);

    var actual = grunt.file.read('tmp/my-text-domain.pot');
    var expected = grunt.file.read('test/expected/my-text-domain.pot');

    //Deal with the fact that actual will contain the timestamp of when the test is run
    expected = expected.replace( "YYYY-MM-DD HH:MM+ZZZZ", getTimestamp() );

    test.equal(actual, expected);

    test.done();
  },

    msgmerge: function(test) {
	test.expect(1);

	var actual        = grunt.file.read('tmp/msgmerge-po.po');
	var expected = grunt.file.read('test/expected/msgmerge-po-updated.po');
	expected = expected.replace( "YYYY-MM-DD HH:MM+ZZZZ", getTimestamp() );

	test.equal(actual, expected);

	test.done();
  },
};

/**
  * This handy function has been cannibalised phpjs's date function
  * @see http://phpjs.org/functions/date/ 
  * @license MIT
  * Copyright (c) 2013 Kevin van Zonneveld (http://kvz.io) and Contributors (http://phpjs.org/authors)
 */
var getTimestamp = function() {
   var date = new Date(); 
   
   var _pad = function(n, c) {
     n = String(n);
     while (n.length < c) {
       n = '0' + n;
     }
     return n;
   }

   var yyyy = date.getFullYear().toString();
   var mm  = (date.getMonth()+1).toString(); // getMonth() is zero-based
   var dd     = date.getDate().toString();

  var hh = date.getHours().toString();
  var ii    = date.getMinutes().toString();

   var tzOffset          = date.getTimezoneOffset();
   var absTzOffset = Math.abs( tzOffset );
   var tzString           = ( tzOffset > 0 ? '-' : '+') + _pad(Math.floor( absTzOffset/60) * 100 +  absTzOffset%60, 4);   

   return yyyy + "-"+ _pad( mm, 2 ) + "-" + _pad( dd, 2 ) + " " +_pad( hh, 2 ) + ":" + _pad( ii, 2 ) + tzString; // padding
  };

