<?php

return \StubsGenerator\Finder::create()
    ->in('source/vendor/wp-cli/*-command/src')
    // Exclude wp-cli/i18n-command
    ->notContains('namespace WP_CLI\\I18n')
    ->append(\StubsGenerator\Finder::create()->in(['source/vendor/nb/oxymel'])->path('Oxymel.php')->files())
    ->sortByName()
;
