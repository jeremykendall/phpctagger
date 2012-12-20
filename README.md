# PHP Ctagger

PHP Ctagger is a [Composer](http://getcomposer.org)
[script](http://getcomposer.org/doc/articles/scripts.md) that will create a
ctags tag file for a project's autoloadable library and that project's dependencies.  The tag file
will be placed in your project's root directory at `/tags`.

## Installation

The only supported method of installation is via Composer. As PHP Ctagger is
intended to be used in a development environment (and will only create a tag file when Composer is in dev mode), PHP Ctagger
must be installed as a dev dependency.  Add the following to your `composer.json`.

```json
{
    "require-dev": {
        "jeremykendall/phpctagger": "dev-master"
    }
}
```

PHP Ctagger utilizes Composer's [Scripts](http://getcomposer.org/doc/articles/scripts.md) 
functionality.  In order for PHP Ctagger to build your tag file, the script must
also be added to your composer.json.

```json
{
    "scripts": {
        "post-install-cmd": [
            "PhpCtagger\\Composer\\Script\\Ctagger::ctag"
        ],
        "post-update-cmd": [
            "PhpCtagger\\Composer\\Script\\Ctagger::ctag"
         ]
    }
}
```

In this example, the script will run both post install and post update.

With that done, run `composer update --dev` to install PHP Ctagger and build
your tag file.

## Limitations

This initial implementation is extremely naive, and will only create tags for
libraries and dependencies that have path entries in
`/vendor/composer/autoload_namespaces.php`.

## Vim

In order to use your new tag file, vim needs to know about it.  Make sure to
load your tag file via whatever means you feel most comfortable.  I've placed
the following in my `.vimrc`

`set tags=tags`

## Work in progress

This project is in its alpha stage, and I'm still not sure if it's even a good
idea.  It's been fun to play with so far, so that's good.  Pull requests, new
issues, comments, and constructive criticisms are welcome.
