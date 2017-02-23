# grunt-pot

> Scan files and creates a .pot file using xgettext.

## Getting Started
This plugin requires Grunt `>=0.4.0`

If you haven't used [Grunt](http://gruntjs.com/) before, be sure to check out the [Getting Started](http://gruntjs.com/getting-started) guide, as it explains how to create a [Gruntfile](http://gruntjs.com/sample-gruntfile) as well as install and use Grunt plugins. Once you're familiar with that process, you may install this plugin with this command:

```shell
npm install grunt-pot --save-dev
```

Once the plugin has been installed, it may be enabled inside your Gruntfile with this line of JavaScript:

```js
grunt.loadNpmTasks('grunt-pot');
```

## The "pot" task

### Overview
In your project's Gruntfile, add a section named `pot` to the data object passed into `grunt.initConfig()`.

```js
grunt.initConfig({
  pot: {
    options: {
      // Specify options
    },
    files: {
      // Specify files to scan
    },
  },
})
```

### Options

#### text_domain
Type: `String`
Default value: (Your package name)

This is the text domain of your project. Currently it is only used to generate the destination file name: `[text-domain].pot`.

#### dest
Type: `String`
Default value: False

Either a path to a folder (with trailing slash) for the generated `.pot` to be placed or a file path. When using a folder, the filename is generated using the text domain.

#### overwrite
Type: `Bool`
Default value: True

When false, append to pre-existing `.pot` file, rather than overwriting it.

#### encoding
Type `String`|`Bool`
Default value: False.

Specifies the encoding of the input files. E.g. "ASCII" or "UTF-8". This option is needed only if some untranslated message strings or their corresponding comments contain non-ASCII characters. This option maps to `xgettext`'s `--from-code` command line option. False (default value) does not specify an encoding, by default, `xgettext` will interpret input files as ASCII. Note that Tcl and Glade input files are always assumed to be in UTF-8, regardless of this option.

### language
Type `String`|`Bool`
Default value: False.

Specifies the language of the input files. The supported languages are C, C++, ObjectiveC, PO, Shell, Python, Lisp, EmacsLisp, librep, Scheme, Smalltalk, Java, JavaProperties, C#, awk, YCP, Tcl, Perl, PHP, GCC-source, NXStringTable, RST, Glade.

By default `xgettext` guesses the language based on the input file name extension.


#### keywords
Type: `Array`
Default value: (none)

An array of strings ('keywords'). Each keyword specifies a gettext function to scan for:

```
keywords: ['gettext', '__'], //functions to look for
```

By default `xgettext` looks for strings in the *first argument* of each keyword. However you can specify a different argument with `id:argnum`. `xgettext` then uses the `argnum`-th argument.  If keyword is of the form `id:argnum1,argnum2`, `xgettext` looks for strings in the `argnum1`-th and in the `argnum2`-th argument of the call, and treats them as singular/plural variants for a message with plural handling.

```
keywords: [ 'gettext', '__', 'dgettext:2', 'ngettext:1,2' ]
```

If keyword is of the form `id:contextargnumc,argnum` or `id:argnum,contextargnumc`, `xgettext` treats strings in the `contextargnum`-th argument as a context specifier. 

```
keywords: [ 'gettext', '__', 'pgettext:1c,2']
```

#### package_name
Type: `String`
Default value: (name specified in your `package.json`)

This is the name that appears in the header msgid.

#### package_version
Type: `String`
Default value: (version specified in your `package.json`)

This is the version that appears in the header msgid

#### msgid_bugs_address
Type: `String`
Default value: (none)

The email (to report bugs to) that appears in the header msgid 

#### omit_header
Type: `Bool`
Default value: `false`

Whether to omit the header. It is recommended to keep this `false`.

#### comment_tag
Type: `String`
Default value: `/`

Comments immediately above a listed keyword which begin with this tag will be included as a comment in the generate `.pot` file. This is useful for providing hints or guidance for translators. For example, in your parsed file(s) you might have:

```
/// TRANSLATORS: This should be translated as a shorthand for YEAR-MONTH-DAY using 4, 2 and 2 digits.
echo gettext("yyyy-mm-dd");
```

#### msgmerge
Type: `Bool|String`
Default value: `false`

After the `.pot` file has been generated, you can [msgmerge](https://www.gnu.org/software/gettext/manual/html_node/msgmerge-Invocation.html) it into existing `.po` files. This updates the `.po` files, preserving the translations (as long as they are still required), but updating extracted comments and file references to those given by the newly generated `.pot` file. Where an exact match cannot be found, fuzzy matching is used to produce better results. In effect this keeps extracted comments and references in the `.po` files 'up to date' with the `.pot` files, while ensuring any minor string changes do not loose their existing translation.

You can enable this by setting `msgmerge` to `true`, in which case the `.po` files are assumed to be in the same directory as the generated `.pot` file. If you wish to specify an alternative directory for the `.po` files you may set this option to that directory path (with trailing slash).


#### add_location
Type: `Null|String`
Default value: `null`

Whether (and how) to include the translatable string's location(s). Accepts 'full' (file and line number), 'file' (file name only) or 'never' (no references). When not specified reverts to the default behaviour of 'full'.


### Usage Examples

```js
grunt.initConfig({
  pot: {
      options:{
	  text_domain: 'my-text-domain', //Your text domain. Produces my-text-domain.pot
	  dest: 'languages/', //directory to place the pot file
	  keywords: ['gettext', '__'], //functions to look for
	},
	files:{
	  src:  [ '**/*.php' ], //Parse all php files
	  expand: true,
       }
  },
})
```

```js
grunt.initConfig({
  pot: {
      options:{
	text_domain: 'my-text-domain', //Your text domain. Produces my-text-domain.pot
	dest: 'languages/', //directory to place the pot file
	keywords: [ 'gettext', 'ngettext:1,2' ], //functions to look for
	msgmerge: true
	},
	files:{
	  src:  [ '**/*.php' ], //Parse all php files
	  expand: true,
       }
  },
})
```


## Contributing
In lieu of a formal styleguide, take care to maintain the existing coding style. Add unit tests for any new or changed functionality. Lint and test your code using [Grunt](http://gruntjs.com/).

## Release History
* *0.3.0* - Added support for --add-location [#16](https://github.com/stephenharris/grunt-pot/issues/16); Use fs for directory detection [#13](https://github.com/stephenharris/grunt-pot/issues/13). 
* *0.2.1* - Fixes bug if directories are included in `files`. See [#10](https://github.com/stephenharris/grunt-pot/issues/10)
* *0.2.0* - Add `msmerge` option.
* *0.1.2* - Pass error messages from `exec` to Grunt.
* *0.1.1* - Added `language`, `encoding` and `overwrite` option. Thanks to @robinnorth.
* *0.1.0* - Initial release
