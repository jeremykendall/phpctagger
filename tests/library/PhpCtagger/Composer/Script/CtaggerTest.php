<?php

namespace PhpCtagger\Composer\Script;

class CtaggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Composer\Composer
     */
    protected $composerMock;

    /**
     * @var \Composer\Config
     */
    protected $composerConfig;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $outputMock;

    /**
     * @var \Composer\IO\IOInterface
     */
    protected $consoleIO;

    /**
     * @var \Composer\Script\Event
     */
    protected $event;

    /**
     * @var Test temp directory
     */
    protected $testTempDir;

    /**
     * @var \PhpCtagger\CtagCommand
     */
    protected $ctagCommandMock;

    /**
     * @var string ctag command (ctags or ctags-exuberant)
     */
    protected $command;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        // ctags command to pass to the mock CtagCommand. Violates isolation of
        // this test by depending on an installed command, but I can't see any
        // way around that, considering the application depends on ctags being
        // installed.
        $this->command = (PHP_OS == 'Darwin') ? 'ctags' : 'ctags-exuberant';

        $this->ctagCommandMock = $this->getMock('\PhpCtagger\CtagCommand', array('getCommand'));

        $this->testTempDir = PROJECT_ROOT_DIR . '/tests/_files';

        $this->composerConfig = new \Composer\Config();
        $this->composerMock = $this->getMock('Composer\Composer');
        $inputMock = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $this->outputMock = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $helperMock = $this->getMock('Symfony\Component\Console\Helper\HelperSet');
        $this->consoleIO = new \Composer\IO\ConsoleIO($inputMock, $this->outputMock, $helperMock);
        $this->event = new \Composer\Script\Event('dummy-event-name', $this->composerMock, $this->consoleIO, $devMode = true);

        // Obviously I'm either misunderstanding backupStaticAttributes or there's 
        // a bug somewhere. Probably my misunderstanding. Until I get it figured 
        // out, I'm resetting properties here.
        Ctagger::setTagsDir(null);
        Ctagger::setCtagCommand(null);
        Ctagger::setVendorDir(null);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        if (file_exists($this->testTempDir . '/tags')) {
            unlink($this->testTempDir . '/tags');
        }

        $this->composerConfig = null;
        $this->consoleIO = null;
        $this->event = null;

        parent::tearDown();
    }

    public function testCtagDevModeTrue()
    {
        $this->assertFileNotExists($this->testTempDir . '/tags');

        $this->composerMock->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($this->composerConfig));

        $this->outputMock->expects($this->exactly(9))
            ->method('write');

        $class = $this->ctagCommandMock;
        $class::staticExpects($this->once())
            ->method('getCommand')
            ->will($this->returnValue($this->command));

        Ctagger::setCtagCommand($this->ctagCommandMock);
        Ctagger::setTagsDir($this->testTempDir);
        Ctagger::ctag($this->event);

        $this->assertFileExists($this->testTempDir . '/tags');
    }

    /**
     * If ctags isn't installed, Ctagger should alert user and quit
     */
    public function testCtagsNotInstalledWritesMessageAndQuits()
    {
        $this->composerMock->expects($this->exactly(0))
            ->method('getConfig');

        $this->outputMock->expects($this->once())
            ->method('write')
            ->with('PhpCtagger: command not found: ctags');

        $class = $this->ctagCommandMock;
        $class::staticExpects($this->once())
            ->method('getCommand')
            ->will($this->throwException(new \Exception('command not found: ctags')));

        $event = new \Composer\Script\Event(
            'dummy-event-name',
            $this->composerMock,
            $this->consoleIO,
            $devMode = true
        );

        Ctagger::setCtagCommand($this->ctagCommandMock);
        Ctagger::ctag($event);
    }

    /**
     * Ctagger should not be run when $devMode is false
     */
    public function testCtagDevModeFalse()
    {
        $this->assertFileNotExists($this->testTempDir . '/tags');

        $this->composerMock->expects($this->exactly(0))
            ->method('getConfig');

        $this->outputMock->expects($this->once())
            ->method('write')
            ->with('PhpCtagger: Composer is not in dev mode. Will not create/modify ctags file.');

        $class = $this->ctagCommandMock;
        $class::staticExpects($this->exactly(0))
            ->method('getCommand');

        $event = new \Composer\Script\Event(
            'dummy-event-name',
            $this->composerMock,
            $this->consoleIO,
            $devMode = false
        );

        Ctagger::setCtagCommand($this->ctagCommandMock);
        Ctagger::setTagsDir($this->testTempDir);
        Ctagger::ctag($event);

        $this->assertFileNotExists($this->testTempDir . '/tags');
    }

    public function testWillDeleteExistingTagfile()
    {
        touch($this->testTempDir . '/tags');

        $this->assertFileExists($this->testTempDir . '/tags');

        $this->composerMock->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($this->composerConfig));

        Ctagger::setTagsDir($this->testTempDir);
        Ctagger::ctag($this->event);
    }

    /**
     * Calling getTagsDir() without first setting tags dir should return the
     * project root directory (as opposed to a temp test directory).
     */
    public function testGetTagsDirProduction()
    {
        Ctagger::setVendorDir(PROJECT_ROOT_DIR . '/vendor');
        $actual = Ctagger::getTagsDir();
        $this->assertEquals(PROJECT_ROOT_DIR, $actual);
    }

}
