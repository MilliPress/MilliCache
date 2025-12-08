<?php

return \StubsGenerator\Finder::create()
    ->in('source/vendor/wp-cli/i18n-command/src')
    ->in('source/vendor/gettext/gettext/src')
    // Conflicts with php-stubs/wordpress-stubs
    ->notPath('translator_functions.php')
    ->sortByName()
;
