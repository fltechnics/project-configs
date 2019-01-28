# Common configuration files for project

## .editorconfig
Copy/paste `.editorconfig` file to your projects root directory.

## .gitignore
Copy/paste `.editorconfig` file to your projects root directory. Add other files or folders if needed.

## .php_cs
Copy/paste `.php_cs` file to your projects root directory. Install php-cs-fixer:
```bash
composer require friendsofphp/php-cs-fixer
```

Run command `vendor/bin/php-cs-fixer` to fixes all `*.php` files in your projects.

Default lookup directories are:

```
app
config
database
packages
resources/lang
tests
```

## .sass-lint.yml
Copy/paste `.sass-lint.yml` file to your projects root directory if you are using SASS or SCSS in your project.

## phpunit.xml && phpunit-printer.yml
Copy/paste `phpunit.xml` && `phpunit-printer.yml` files to your projects root directory if you are using PHP Unit in your project - which you must...

Install `codedungeon/phpunit-result-printer` for pretty test results and `nunomaduro/collision` for better error handling:

```bash
composer require codedungeon/phpunit-result-printer nunomaduro/collision
```

Copy/Paste `tests/utilities/PhpUnitResultsPrinter.php` to your project `tests/utilities/` directory.

## Envoy.blade.php
This is your ready to go deployment script. Edit this file accordingly to your project server configuration and directory structure.
