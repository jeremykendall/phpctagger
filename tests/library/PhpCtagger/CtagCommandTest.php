<?php

namespace PhpCtagger;

class CtagCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider installedCtagsDataProvider
     */
    public function testGetCommand($command)
    {
        $this->assertEquals($command, CtagCommand::getCommand());
    }

    public function installedCtagsDataProvider()
    {
        if (PHP_OS == 'Darwin') {
            return array(array('ctags'));
        }

        return array(array('ctags-exuberant'));
    }

    public function testGetCommandThrowsExceptionWhenCtagsNotInstalled()
    {
        $this->setExpectedException('\Exception', 'command not found: does-not-exist');
        CtagCommand::getCommand('does-not-exist');
    }
}
