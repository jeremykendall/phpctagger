<?php

namespace PhpCtagger\Composer\Script;

use org\bovigo\vfs\vfsStream;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-11-20 at 06:56:07.
 */
class CtaggerTest extends \ComposerScriptTestCase
{

    /**
     * @var  vfsStreamDirectory
     */
    protected $root;

    /**
     * @var mock tags directory
     */
    protected $tagsDir;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->tagsDir = PROJECT_ROOT_DIR . '/tests/_files/tags';
        $filesystem = new \Composer\Util\Filesystem();
        $filesystem->ensureDirectoryExists($this->tagsDir);

        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        if (file_exists($this->tagsDir)) {
            $filesystem = new \Composer\Util\Filesystem();
            $filesystem->removeDirectory($this->tagsDir);
        }
        parent::tearDown();
    }

    public function testCtag()
    {
        $this->assertFileNotExists($this->tagsDir . '/tags');

        $this->composerMock->expects($this->once())
                ->method('getConfig')
                ->will($this->returnValue($this->composerConfig));

        $this->outputMock->expects($this->any())
                    ->method('write');
        
        Ctagger::setTagsDir($this->tagsDir);
        Ctagger::ctag($this->event);

        $this->assertFileExists($this->tagsDir . '/tags');
    }

    public function testGetInstalledCtags()
    {
        $this->assertEquals('ctags', Ctagger::getInstalledCtags());
    }

    public function testGetCtagsVersion()
    {
        $this->assertEquals('5.8', Ctagger::getCtagsVersion());
    } 

}
