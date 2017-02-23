<?php

//This comment won't appear in the .pot file
echo gettext("Me too");

$apples = 2;
echo ngettext( "I have %d apple", "I have %d apples", $apples );


//Example of a gettext function supporting context 
echo pgettext( "Noun", "Post" );
echo pgettext( "Verb", "Post" );


/// TRANSLATORS: This should be translated as a shorthand for YEAR-MONTH-DAY using 4, 2 and 2 digits.
echo gettext("yyyy-mm-dd");
?>
