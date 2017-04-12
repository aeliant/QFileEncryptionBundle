<?php
namespace Command;

use Querdos\QFileEncryptionBundle\Command\DecryptFileCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class DecryptFileCommandTest
 * @package Command
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class DecryptFileCommandTest extends WebTestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    public function testExecute()
    {
        $application = new Application(static::$kernel);
        $application->add(new DecryptFileCommand());

        $command = $application->find('qfe:gen-key');
        $command->setApplication($application);

        $commandTester = new CommandTester($command);
    }
}