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
        mkdir("{$this->gpg_home}/{$username}", 0700);

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

        try {$builder->getProcess()->mustRun(); } catch (ProcessFailedException $e) {
            dump($e->getMessage());die;
        }

        unlink("{$dirname}/batch_gpg");
        rmdir($dirname);

        $userdir = "{$this->gpg_home}/{$username}";
        $builder
            ->setEnv("GNUPGHOME", $userdir)
            ->setArguments(array(
                "--import",
                "{$userdir}/{$username}.pub"
            ))
        ;

        try {$builder->getProcess()->mustRun(); } catch (ProcessFailedException $e) {
            dump($e->getMessage());die;
        }

        $builder
            ->setArguments(array(
                "--import",
                "{$userdir}/{$username}.sec"
            ))
        ;

        try {$builder->getProcess()->mustRun(); } catch (ProcessFailedException $e) {
            dump($e->getMessage());die;
        }

        $this->qkeyManager->create(new QKey(
            $recipient,
            password_hash($passphrase, PASSWORD_BCRYPT),
            $username
        ));
    }

    /**
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