<?php

/**
 * PhpCtagger
 *
 * @link      http://github.com/jeremykendall/phpctagger for the canonical source repository
 * @copyright Copyright (c) 2012 Jeremy Kendall (http://about.me/jeremykendall)
 * @license   http://github.com/jeremykendall/phpctagger/blob/master/LICENSE MIT License
 */

namespace PhpCtagger\Composer\Script;

use Composer\Script\Event;

/**
 * Ctagger class
 *
 * Generated ctag files for project library and composer dependencies
 */
class Ctagger
{
    /**
     * @var string tag file directory
     */
    protected static $tagsDir;

    /**
     * Generated ctag files for project library and composer dependencies
     *
     * @param \Composer\Script\Event $event
     */
    public static function ctag(Event $event)
    {
        $io = $event->getIO();

        $io->write('Preparing to build tags file . . .');

        $vendorDir = realpath($event->getComposer()->getConfig()->get('vendor-dir'));
        $tagsDir = self::getTagsDir($vendorDir);
        $tagsFile = $tagsDir . '/tags';

        // Ensure the tags file is new each time
        if (file_exists($tagsFile)) {
            unlink($tagsFile);
        }

        $paths = include $vendorDir . '/composer/autoload_namespaces.php';

        $command = self::getInstalledCtags();

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }
            chdir($path);
            $command .= " -f $tagsFile \
                -h '.php' \
                -R \
                --exclude='.git' \
                --exclude='.svn' \
                --totals=yes \
                --tag-relative=yes \
                --fields=+afkst \
                --PHP-kinds=+cf \
                --append=yes";
            exec($command . ' 2>&1', $output);

            $io->write($output, true);
        }
    }

    public static function getInstalledCtags()
    {
        if (exec('which ctags')) {
            return 'ctags';
        }

        if (exec('which ctags-exuberant')) {
            return 'ctags-exuberant';
        }

        throw new Exception('ctags is not installed');
    }

    public static function getCtagsVersion()
    {
        return null;
    }

    public static function getTagsDir($vendorDir)
    {
        if (is_null(self::$tagsDir)) {
            return dirname($vendorDir) . '/tags';
        }

        return self::$tagsDir;
    }

    public static function setTagsDir($tagsDir)
    {
        self::$tagsDir = $tagsDir;
    }

}
