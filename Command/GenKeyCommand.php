<?php
namespace Querdos\QFileEncryptionBundle\Command;

use Querdos\QFileEncryptionBundle\Entity\QKey;
use Querdos\QFileEncryptionBundle\Manager\QKeyManager;
use Querdos\QFileEncryptionBundle\Util\LogUtil;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class GenKeyCommand
 * @package Querdos\QFileEncryptionBundle\Command
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class GenKeyCommand extends ContainerAwareCommand
{
    const KEY_TYPE      = "DSA";
    const KEY_LENGTH    = "2048";
    const SUBKEY_TYPE   = "ELG-E";
    const SUBKEY_LENGTH = "2048";
    const EXPIRE_DATE   = "0";

    /**
     * @var string
     */
    private $gpg_home;

    /**
     * @var string
     */
    private $log_file;

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

        $this->log_file = sprintf(
            "%s/../%s",
            $this->getContainer()->get('kernel')->getRootDir(),
            $this->getContainer()->getParameter('q_file_encryption.logs_dir')
        );
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

        // creating the object
        $qkey = new QKey(
            $recipient,
            null === $passphrase ?
                null :
                password_hash($passphrase, PASSWORD_BCRYPT),
            $username
        );

        // validate the entity before anything else
        $errors = $this
            ->getContainer()
            ->get('validator')
            ->validate($qkey)
        ;

        // if errors, exception
        if (0 != count($errors)) {
            $str = (string) $errors;
            throw new Exception("Errors occured: \n{$str}");
        }

        // checking if username hasn't already registered a key pair
        if (null !== $this->qkeyManager->findByUsername($username)) {
            throw new Exception("This username is already registered");
        }

        // checking if recipient hasn't already registered a key pair
        if (null !== $this->qkeyManager->findByRecipient($recipient)) {
            throw new Exception("This email is already registered");
        }

        // dirname in tmp directory
        $dirname = sprintf("/tmp/gpg_%s", uniqid());
        mkdir($dirname);

        // creating dir for user and setting correct permission
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
            // generation failed, removing dir and logging
            exec("rm -rf {$this->gpg_home}/{$username}");
            LogUtil::write_error($this->log_file, $e);
            return;
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
            // import failed, removing dir and logging
            exec("rm -rf {$userdir}/{$qkey->getUsername()}");
            LogUtil::write_error($this->log_file, $e);
            return;
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
            // import failed, removing dir and logging
            exec("rm -rf {$userdir}/{$qkey->getUsername()}");
            LogUtil::write_error($this->log_file, $e);
            return;
        }

        // persisting the key to the datgabase
        $this->qkeyManager->create($qkey);
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
        $text  = sprintf("Key-Type: %s\n",      self::KEY_TYPE);
        $text .= sprintf("Key-Length: %s\n",    self::KEY_LENGTH);
        $text .= sprintf("Subkey-Type: %s\n",   self::SUBKEY_TYPE);
        $text .= sprintf("Subkey-Length: %s\n", self::SUBKEY_LENGTH);
        $text .= "Name-Real: {$username}\n";
        $text .= "Name-Comment: User {$username} key pair\n";
        $text .= "Name-Email: {$recipient}\n";
        $text .= sprintf("Expire-Date: %s\n", self::EXPIRE_DATE);
        $text .= "Passphrase: {$passphrase}\n";
        $text .= "%pubring {$this->gpg_home}/{$username}/{$username}.pub\n";
        $text .= "%secring {$this->gpg_home}/{$username}/{$username}.sec\n";
        $text .= "%commit\n";

        return $text;
    }
}