<?php

/**
 * PHP Ctagger
 *
 * @link      http://github.com/jeremykendall/phpctagger for the canonical source repository
 * @copyright Copyright (c) 2012 Jeremy Kendall (http://about.me/jeremykendall)
 * @license   http://github.com/jeremykendall/phpctagger/blob/master/LICENSE MIT License
 */
namespace PhpCtagger;

/**
 * CtagCommand
 *
 * CtagCommand attempts find and return the installed ctags command
 */
class CtagCommand
{
    /**
     * Attempts to find and return the installed ctags command.
     *
     * @param  string|null $command ctags command, used only for testing
     * @return string      Appropriate ctags command, either ctags or ctags-exuberant
     * @throws \Exception  Throws exception if command not installed
     */
    public static function getCommand($command = null)
    {
        if (is_null($command)) {
            $command = (PHP_OS == 'Darwin') ? 'ctags' : 'ctags-exuberant';
        }

        if (exec("which $command")) {
            return $command;
        }

        throw new \Exception("command not found: $command");
    }

}
