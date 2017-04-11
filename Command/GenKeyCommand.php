<?php
/**
 * Created by Hamza ESSAYEGH
 * User: querdos
 * Date: 4/11/17
 * Time: 1:39 PM
 */

namespace Querdos\QFileEncryptionBundle\Command;


use Querdos\QFileEncryptionBundle\Entity\QKey;
use Querdos\QFileEncryptionBundle\Manager\QKeyManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class GenKeyCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    private $gpg_home;

    /**
     * @var QKeyManager
     */
    private $qkeyManager;

    /**
     * {@inheritdoc}
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->gpg_home    = $this->getContainer()->getParameter('q_file_encryption.gnupg_home');
        $this->qkeyManager = $this->getContainer()->get('qfe.manager.qkey');
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName("qfe:gen-key")

            ->addOption("username", "u", InputOption::VALUE_REQUIRED)
            ->addOption("recipient", "r", InputOption::VALUE_REQUIRED)
            ->addOption("passphrase", "p", InputOption::VALUE_REQUIRED)

            ->setHidden(true)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // getting options
        $username   = $input->getOption('username');
        $recipient  = $input->getOption('recipient');
        $passphrase = $input->getOption('passphrase');

        // checking values
        if (null === $username) {
            throw new Exception("Username cannot be null");
        } else if (null === $recipient) {
            throw new Exception("Recipient cannot be null");
        } else if (null === $passphrase) {
            throw new Exception("Passphrase cannot be null");
        }

        // dirname in tmp directory
        $dirname = sprintf("/tmp/gmp_%s", uniqid());
        mkdir($dirname);

        // creating dir for user and setting correct permission
        // TODO: Check if dir exists or not
        mkdir("{$this->gpg_home}/{$username}", 0700);

        // creating batch gpg
        file_put_contents(
            "{$dirname}/batch_gpg",
            $this->generate_batch($username, $recipient, $passphrase)
        );

        // building process
        $builder = new ProcessBuilder();
        $builder
            ->setPrefix("/usr/bin/gpg")
            ->setArguments(array(
                '--batch',
                '--gen-key',
                "{$dirname}/batch_gpg"
            ))
        ;

        // trying to generate
        try {$builder->getProcess()->mustRun(); } catch (ProcessFailedException $e) {
            dump($e->getMessage());die;
        }

        // removing batch file and directory
        unlink("{$dirname}/batch_gpg");
        rmdir($dirname);

        // building the command to import public key
        $userdir = "{$this->gpg_home}/{$username}";
        $builder
            ->setEnv("GNUPGHOME", $userdir)
            ->setArguments(array(
                "--import",
                "{$userdir}/{$username}.pub"
            ))
        ;

        // trying to run the import
        try {$builder->getProcess()->mustRun(); } catch (ProcessFailedException $e) {
            dump($e->getMessage());die;
        }

        // building the command to import private key
        $builder
            ->setArguments(array(
                "--import",
                "{$userdir}/{$username}.sec"
            ))
        ;

        // trying to run the command
        try {$builder->getProcess()->mustRun(); } catch (ProcessFailedException $e) {
            dump($e->getMessage());die;
        }

        // persisting the key to the datgabase
        $this->qkeyManager->create(new QKey(
            $recipient,
            password_hash($passphrase, PASSWORD_BCRYPT),
            $username
        ));
    }

    /**
     * Generate the string to put in the batch file
     *
     * @param string $username
     * @param string $recipient
     *
     * @return string
     */
    private function generate_batch($username, $recipient, $passphrase)
    {
        $text  = "Key-Type: DSA\n";
        $text .= "Key-Length: 2048\n";
        $text .= "Subkey-Type: ELG-E\n";
        $text .= "Subkey-Length: 2048\n";
        $text .= "Name-Real: {$username}\n";
        $text .= "Name-Comment: User {$username} key pair\n";
        $text .= "Name-Email: {$recipient}\n";
        $text .= "Expire-Date: 0\n";
        $text .= "Passphrase: {$passphrase}\n";
        $text .= "%pubring {$this->gpg_home}/{$username}/{$username}.pub\n";
        $text .= "%secring {$this->gpg_home}/{$username}/{$username}.sec\n";
        $text .= "%commit\n";

        return $text;
    }
}