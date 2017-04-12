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
        // creating the application
        $application = new Application(static::$kernel);
        $application->add(new DecryptFileCommand());

        // adding the command
        $command = $application->find('qfe:decrypt');
        $command->setApplication($application);

        // retrieving a file in the enc_dir direfctory
        $enc_dir        = self::$kernel->getContainer()->getParameter('q_file_encryption.enc_dir');
        $upload_dir     = self::$kernel->getRootDir() . '/../web/' . $enc_dir;
        $fileToDecrypt  = scandir($upload_dir, SCANDIR_SORT_DESCENDING)[0];
        preg_match('/(.*)\.enc$/', $fileToDecrypt, $match);

        // retrieving the qfile
        $manager = self::$kernel->getContainer()->get('qfe.manager.qfile');
        $qfile   = $manager->readByUniqueFileName($match[1]);

        // checking that qfile is not null
        $this->assertFalse(null === $qfile);

        // running decryption
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'         => $command->getName(),
            'file'            => "{$upload_dir}/{$fileToDecrypt}",
            '--username'      => EncryptFileCommandTest::USERNAME,
            '--recipient'     => EncryptFileCommandTest::RECIPIENT,
            '--passphrase'    => EncryptFileCommandTest::PASSPHRASE
        ));

        // checking that the original name is correct
        $this->assertTrue(file_exists("/tmp/{$qfile->getOriginalName()}"));

        // checking that the content (plain) is the same as the original one
        $expectedContent = EncryptFileCommandTest::lorem_text();
        $actualContent   = file_get_contents("/tmp/{$qfile->getOriginalName()}");

        // asserting
        $this->assertTrue($expectedContent === $actualContent);
    }
}