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
 * Generates ctag files for project library and Composer dependencies
 */
class Ctagger
{
    /**
     * @var string tag file directory
     */
    protected static $tagsDir;

    /**
     * @var string Path to Composer vendor directory
     */
    protected static $vendorDir;

    /**
     * @var \PhpCtagger\CtagCommand Used to find installed ctags
     */
    protected static $ctagCommand;

    /**
     * Generate ctag files for project library and Composer dependencies
     *
     * @param \Composer\Script\Event $event
     */
    public static function ctag(Event $event)
    {
        $io = $event->getIO();

        try {
            self::confirmDevMode($event);
            $ctagCommand = self::getCtagCommand();
            $command = $ctagCommand::getCommand();
        } catch (\Exception $e) {
            $io->write('PhpCtagger: ' . $e->getMessage());

            return;
        }

        $io->write('Preparing to build tags file . . .');

        self::$vendorDir = realpath($event->getComposer()->getConfig()->get('vendor-dir'));
        $tagsFile = self::getTagsDir() . '/tags';
        self::deleteTagsFile($tagsFile);

        $paths = include self::$vendorDir . '/composer/autoload_namespaces.php';

        // In at least one instance, I've seen paths in the autoload_namespaces
        // file that don't exist. This removes them from the $paths array.
        $paths = array_filter($paths, function($path) {
            return(file_exists($path));
        });

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

        foreach ($paths as $path) {
            chdir($path);
            exec($command . $options . ' 2>&1', $output);
            $io->write($output, true);
            unset($output);
        }

        $io->write('Tagfile complete!');
    }

    /**
     * Deletes tags file
     *
     * @param string Path to tags file
     */
    public static function deleteTagsFile($tagsFile)
    {
        if (file_exists($tagsFile)) {
            unlink($tagsFile);
        }
    }

    /**
     * Throws exception if Composer is not in dev mode
     *
     * @param \Composer\Script\Event Composer Event
     * @throws Exception Throws exception if Composer is not in dev mode
     */
    public static function confirmDevMode(Event $event)
    {
        if (!$event->isDevMode()) {
            throw new \Exception('Composer is not in dev mode. Will not create/modify ctags file.');
        }
    }

    /**
     * Gets path to the tags file directory.
     *
     * By default, the tags directory is the same directory that holds the Composer
     * vendor directory.  It is possible to change the location of the directory
     * using setTagsDir() for testing purposes.
     *
     * @return Path to tags directory
     */
    public static function getTagsDir()
    {
        if (is_null(self::$tagsDir)) {
            return dirname(self::$vendorDir);
        }

        return self::$tagsDir;
    }

    /**
     * Set directory where tags file will be created
     *
     * @param string Directory where tags file will be created
     */
    public static function setTagsDir($tagsDir)
    {
        self::$tagsDir = $tagsDir;
    }

    /**
     * Gets the CtagCommand class
     *
     * @return mixed Returns the default CtagCommand class name or a mock
     * object for testing.
     */
    public static function getCtagCommand()
    {
        if (is_null(self::$ctagCommand)) {
            return '\PhpCtagger\CtagCommand';
        }

        return self::$ctagCommand;
    }

    /**
     * Used to override the default \PhpCtagger\CtagCommand for testing
     *
     * @param mixed A new command class, a mock object, or null
     */
    public static function setCtagCommand($ctagCommand)
    {
        self::$ctagCommand = $ctagCommand;
    }

    /**
     * Sets path to vendor directory
     *
     * Vendor directory is discoverable via Composer's config object. This is to
     * set/reset vendor directory during testing
     *
     * @param string|null Path to vendor directory
     */
    public static function setVendorDir($vendorDir = null)
    {
        self::$vendorDir = $vendorDir;
    }
}
