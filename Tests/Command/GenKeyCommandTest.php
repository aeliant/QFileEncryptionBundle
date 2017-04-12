<?php
/**
 * Created by Hamza ESSAYEGH
 * User: querdos
 * Date: 4/12/17
 * Time: 9:40 AM
 */

namespace Querdos\QFileEncryptionBundle\Tests\Command;

use Querdos\QFileEncryptionBundle\Command\GenKeyCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Config\Definition\Exception\Exception;

class GenKeyCommandTest extends WebTestCase
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
        $application->add(new GenKeyCommand());

        $command = $application->find('qfe:gen-key');
        $command->setApplication($application);

        $commandTester = new CommandTester($command);

        // creating new key pair, normally good
        $commandTester->execute( array (
            'command' => $command->getName(),
            '--username'    => 'querdos',
            '--recipient'   => 'querdos@gmail.com',
            '--passphrase'  => 'test1234'
        ));

        // asserting that everything's ok
        $this->assertEmpty($commandTester->getDisplay());

        // trying to retrieve the entity
        $entity =self::$kernel
            ->getContainer()
            ->get('qfe.manager.qkey')
            ->findByUsername('querdos');

        // checking that the entity has been persisted
        $this->assertTrue($entity !== null);

        // checking that a directory has been created
        $gpghome = self::$kernel->getContainer()->getParameter('q_file_encryption.gnupg_home');
        $this->assertTrue(
            is_dir("{$gpghome}/querdos")
        );

        // checking that at least private and public key has been generated
        $this->assertTrue(
            file_exists("{$gpghome}/querdos/querdos.pub")
        );

        $this->assertTrue(
           file_exists("{$gpghome}/querdos/querdos.sec")
        );

        // checking that no gpg_xxxxx is still there in the tmp directory
        $this->assertEmpty(
            shell_exec("ls /tmp | grep gpg_")
        );

        // checking that a trust database has been created
        $this->assertTrue(
            file_exists("{$gpghome}/querdos/trustdb.gpg")
        );

        // Expecting exception -> username exists
        $this->expectException(Exception::class);
        $commandTester->execute( array (
            'command' => $command->getName(),
            '--username'    => 'querdos',
            '--recipient'   => 'querdos@gmail.com',
            '--passphrase'  => 'test1234'
        ));

        // Expecting exception -> username null
        $this->expectException(Exception::class);
        $commandTester->execute( array (
            'command' => $command->getName(),
            '--username'    => null,
            '--recipient'   => 'querdos@gmail.com',
            '--passphrase'  => 'test1234'
        ));

        // checking that dir exists
    }
}