<?php
namespace Command;

use Querdos\QFileEncryptionBundle\Command\EncryptFileCommand;
use Querdos\QFileEncryptionBundle\Command\GenKeyCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class EncryptFileCommandTest
 * @package Command
 *
 * @author Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class EncryptFileCommandTest extends WebTestCase
{
    const USERNAME   = "querdos_test_encrypt";
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
        // creating the application
        $application = new Application(static::$kernel);

        // adding commands
        $application->add(new GenKeyCommand());
        $application->add(new EncryptFileCommand());

        // finding the key pair generation command
        $command = $application->find('qfe:gen-key');
        $command->setApplication($application);

        // generating key pair
        $commandTester = new CommandTester($command);
        $commandTester->execute( array (
            'command'       => $command->getName(),
            '--username'    => self::USERNAME,
            '--recipient'   => self::RECIPIENT,
            '--passphrase'  => self::PASSPHRASE
        ));

        // creating a file for encryption in tmp directory
        $tmp_filename = uniqid("QFEtest", true);
        file_put_contents("/tmp/{$tmp_filename}", self::lorem_text());

        // retrieving encryption command
        $command = $application->find('qfe:encrypt');
        $command->setApplication($application);

        // retrieving upload dirs and qfileManager
        $upload_dir   = self::$kernel->getRootDir() . "/../web/" . self::$kernel->getContainer()->getParameter('q_file_encryption.enc_dir');
        $qfileManager = self::$kernel->getContainer()->get('qfe.manager.qfile');

        // scanning upload dir before encryption + entities before encryption
        $before_dir = scandir($upload_dir);
        $before_ent = $qfileManager->all();

        // creating the input commands
        $input_delete = array(
            'command'           => $command->getName(),
            'file'              => "/tmp/{$tmp_filename}",
            '--username'        => self::USERNAME,
            '--recipient'       => self::RECIPIENT,
        );

        // generating command tester
        $commandTester = new CommandTester($command);
        $commandTester->execute($input_delete);

        // checking that the original file has been deleted
        $this->assertFalse(file_exists("/tmp/{$tmp_filename}"));

        // scanning upload dir after encryption (so we can check if a file has been created)
        $diff_dir = array_diff(scandir($upload_dir), $before_dir);
        $diff_ent = array_diff($qfileManager->all(), $before_ent);

        // arrays mustn't be empty
        $this->assertTrue(0 != count($diff_dir));
        $this->assertTrue(0 != count($diff_ent));

        // creating a new file to encrypt
        $tmp_filename = uniqid("QFEtest", true);
        file_put_contents("/tmp/{$tmp_filename}", self::lorem_text());

        $input_delete['--delete-original'] = false;
        $input_delete['file']              = "/tmp/{$tmp_filename}";

        // executing the same command and checking that the original file has been deleted
        $commandTester = new CommandTester($command);
        $commandTester->execute($input_delete);

        // checking that the file hasn't been deleted
        $this->assertTrue(file_exists("/tmp/{$tmp_filename}"));
        unlink("/tmp/{$tmp_filename}"); // need to unlink it for decryption test
    }

    public static function lorem_text()
    {
        return <<<EOF
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam dui erat, euismod vitae quam quis, molestie auctor nunc. Etiam vel sem mollis, commodo leo quis, scelerisque ligula. Integer a nunc quis enim sollicitudin viverra at eget dolor. Donec efficitur urna magna, in mattis dolor auctor at. Fusce fermentum nisl tellus. Nulla fermentum pellentesque felis feugiat maximus. In nulla ligula, posuere non eleifend eget, feugiat ut neque. Sed efficitur lorem lacus, ut aliquet enim ullamcorper ac.

In blandit consequat semper. Cras vitae auctor neque. Morbi risus mi, ultricies a velit eu, ullamcorper posuere ipsum. Nulla mollis ligula nisi, vitae aliquet velit egestas in. Vivamus hendrerit lobortis sapien sed feugiat. Vivamus suscipit neque eros, quis bibendum nulla placerat eu. Fusce accumsan sit amet justo eget ornare. Aliquam enim nibh, varius et libero at, ullamcorper condimentum nisi. Vestibulum odio eros, laoreet a arcu eget, aliquam tempus enim. Praesent consequat ex eu est commodo, sed semper eros rutrum. Vestibulum imperdiet, elit sit amet hendrerit sodales, mauris tortor mollis est, vel iaculis leo odio varius augue. Aliquam scelerisque felis et velit pellentesque suscipit.

Duis congue sapien eu nibh consectetur lacinia. Sed in ligula ac ex scelerisque suscipit in a nulla. Proin eu lacus pretium, accumsan urna at, ullamcorper arcu. Nunc consequat, felis nec bibendum lobortis, nisi risus semper lacus, a hendrerit erat nunc at nisl. Curabitur et hendrerit urna. Maecenas fermentum faucibus nisi non eleifend. Nulla lectus lorem, vehicula imperdiet felis ut, tempus dapibus lorem. Donec malesuada nec lorem vel maximus. Etiam dapibus sed neque at egestas. Nullam vitae tempor risus. In hac habitasse platea dictumst.

Cras id placerat quam, sed dignissim purus. In bibendum eu libero sit amet lacinia. Fusce in dui ligula. Vivamus condimentum convallis ligula eu maximus. Integer efficitur eleifend felis, eget volutpat lacus iaculis eu. Nullam iaculis purus quis convallis tincidunt. Quisque tincidunt risus est, sit amet fringilla ante finibus quis. Quisque iaculis, velit id hendrerit suscipit, sapien massa condimentum ipsum, eget egestas turpis mauris vitae lorem. Praesent fringilla massa et tincidunt laoreet. Cras sit amet augue sit amet libero blandit fermentum. Phasellus arcu sapien, sodales sit amet euismod eget, pellentesque et libero.

Fusce sit amet tellus arcu. Nullam sollicitudin venenatis justo nec interdum. Vivamus volutpat lorem id vehicula interdum. Morbi non rutrum eros. Nulla in est nec augue dapibus posuere vitae at ante. Pellentesque vulputate vitae odio a sagittis. Pellentesque feugiat quam sed lectus vestibulum viverra. Etiam euismod odio eget turpis tempus, et aliquet mi euismod.
EOF;

    }
}