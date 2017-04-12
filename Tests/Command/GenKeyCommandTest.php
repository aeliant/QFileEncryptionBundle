<?php
namespace Querdos\QFileEncryptionBundle\Tests\Command;

use Querdos\QFileEncryptionBundle\Command\GenKeyCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class GenKeyCommandTest
 * @package Querdos\QFileEncryptionBundle\Tests\Command
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class GenKeyCommandTest extends WebTestCase
{
    const USERNAME   = "querdos_test_genkey";
    const RECIPIENT  = self::USERNAME . "@gmail.com";
    const PASSPHRASE = "test1234";


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
            '--username'    => self::USERNAME,
            '--recipient'   => self::RECIPIENT,
            '--passphrase'  => self::PASSPHRASE
        ));

        // asserting that everything's ok
        $this->assertEmpty($commandTester->getDisplay());

        // trying to retrieve the entity
        $entity =self::$kernel
            ->getContainer()
            ->get('qfe.manager.qkey')
            ->findByUsername(self::USERNAME);

        // checking that the entity has been persisted
        $this->assertTrue($entity !== null);

        // checking that a directory has been created
        $gpghome = self::$kernel->getContainer()->getParameter('q_file_encryption.gnupg_home');
        $this->assertTrue(
            is_dir("{$gpghome}/" . self::USERNAME)
        );

        // checking that at least private and public key has been generated
        $this->assertTrue(
            file_exists(sprintf(
                "%s/%s/%s.pub",
                    $gpghome,
                    self::USERNAME,
                    self::USERNAME))
        );

        $this->assertTrue(
            file_exists(sprintf(
                "%s/%s/%s.sec",
                $gpghome,
                self::USERNAME,
                self::USERNAME))
        );

        // checking that no gpg_xxxxx is still there in the tmp directory
        $this->assertEmpty(
            shell_exec("ls /tmp | grep gpg_")
        );

        // checking that a trust database has been created
        $this->assertTrue(
            file_exists(sprintf(
                "%s/%s/trustdb.gpg",
                $gpghome,
                self::USERNAME
            ))
        );

        // initial input with errors
        $input = array(
            'command' => $command->getName(),
            '--username'    => self::USERNAME,
            '--recipient'   => 'querdos@gmail.com',
            '--passphrase'  => 'test1234'
        );

        // Expecting exception -> username exists
        $this->expectException(Exception::class);
        $commandTester->execute($input);

        // Expecting exception -> username null
        $input['--username'] = null;
        $this->expectException(Exception::class);
        $commandTester->execute($input);

        // Expecting exception -> username empty
        $input['--username'] = '';
        $this->expectException(Exception::class);
        $commandTester->execute($input);

        // Expecting exception -> recipient invalid
        $input['--username']  = uniqid();
        $input['--recipient'] = 'querdos@azerazer';
        $this->expectException(Exception::class);
        $commandTester->execute($input);

        // Expecting exception -> recipient null
        $input['--recipient'] = null;
        $this->expectException(Exception::class);
        $commandTester->execute($input);

        // Excepting exception -> recipient empty
        $input['--recipient'] = '';
        $this->expectException(Exception::class);
        $commandTester->execute($input);

        // Expecting exception -> recipient exists
        $input['--recipient'] = self::RECIPIENT;
        $this->expectException(Exception::class);
        $commandTester->execute($input);

        // Expecting exception -> passphrase null
        $input['--recipient']  = 'querdos@gmail.com';
        $input['--passphrase'] = null;
        $this->expectException(Exception::class);
        $commandTester->execute($input);

        // Expecting exception -> passphrase empty
        $input['--passphrase'] = '';
        $this->expectException(Exception::class);
        $commandTester->execute($input);
    }
}