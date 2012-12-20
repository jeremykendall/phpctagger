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
     * Generate ctag files for project library and composer dependencies
     *
     * @param \Composer\Script\Event $event
     */
    public static function ctag(Event $event)
    {
        $io = $event->getIO();

        if (!$event->isDevMode()) {
            $io->write('PhpCtagger: Composer is not in dev mode. Will not create/modify ctags file.');
            return;
        } 

        $io->write('Preparing to build tags file . . .');

        $vendorDir = realpath($event->getComposer()->getConfig()->get('vendor-dir'));
        $tagsFile = self::getTagsDir($vendorDir) . '/tags';

        // Ensure the tags file is new each time
        if (file_exists($tagsFile)) {
            $io->write('Deleting existing tagfile . . .');
            unlink($tagsFile);
        }

        $paths = include $vendorDir . '/composer/autoload_namespaces.php';

        $command = self::getInstalledCtags();

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }
            chdir($path);
            $options = " -f $tagsFile \
                -h '.php' \
                -R \
                --exclude='.git' \
                --exclude='.svn' \
                --totals=yes \
                --tag-relative=yes \
                --fields=+afkst \
                --PHP-kinds=+cf \
                --append=yes";
            exec($command . $options . ' 2>&1', $output);

            $io->write($output, true);

            // Empty the output array
            unset($output);
        }
        $io->write('Tagfile complete!');
    }

    public static function getInstalledCtags()
    {
        if (exec('which ctags')) {
            if (version_compare(self::getCtagsVersion(), '5.8', '<')) {
                throw new \Exception('Incorrect version of ctags installed. Please install ctags version 5.8 or greater');
            }

            return 'ctags';
        }

        if (exec('which ctags-exuberant')) {
            return 'ctags-exuberant';
        }

        throw new \Exception('ctags is not installed');
    }

    public static function getCtagsVersion()
    {
        exec('ctags --version', $output);
        preg_match('/(?:(\d+)\.)?(?:(\d+)\.)?(\*|\d+)/', $output[0], $version);

        return $version[0];
    }

    public static function getTagsDir($vendorDir)
    {
        if (is_null(self::$tagsDir)) {
            return dirname($vendorDir);
        }

        return self::$tagsDir;
    }

    public static function setTagsDir($tagsDir)
    {
        self::$tagsDir = $tagsDir;
    }

}
