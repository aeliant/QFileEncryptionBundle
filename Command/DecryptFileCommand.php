<?php
namespace Querdos\QFileEncryptionBundle\Command;

use Querdos\QFileEncryptionBundle\Entity\QFile;
use Querdos\QFileEncryptionBundle\Entity\QKey;
use Querdos\QFileEncryptionBundle\Exception\DecryptException;
use Querdos\QFileEncryptionBundle\Exception\KeyOptionsException;
use Querdos\QFileEncryptionBundle\Manager\QFileManager;
use Querdos\QFileEncryptionBundle\Manager\QKeyManager;
use Querdos\QFileEncryptionBundle\Util\LogUtil;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class DecryptFileCommand
 * @package Querdos\QFileEncryptionBundle\Command
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class DecryptFileCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    private $gpgHome;

    /**
     * @var string
     */
    private $log_file;

    /**
     * @var QKeyManager
     */
    private $qkeyManager;

    /**
     * @var QFileManager
     */
    private $qfileManager;

    /**
     * {@inheritdoc}
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->qfileManager = $this->getContainer()->get('qfe.manager.qfile');
        $this->qkeyManager  = $this->getContainer()->get('qfe.manager.qkey');
        $this->gpgHome      = $this->getContainer()->getParameter('q_file_encryption.gnupg_home');

        // retrieving log dir in main configuration file
        $log_dir = $this->getContainer()->getParameter('q_file_encryption.logs_dir');
        if (null === $log_dir) {
            throw new InvalidConfigurationException("Incorrect value for the log file path");
        }

        // setting the log file
        $this->log_file = sprintf(
            "%s/../%s/qfe.log",
            $this->getContainer()->get('kernel')->getRootDir(),
            $log_dir
        );

        // checking gnupg home
        if (null === $this->gpgHome) {
            throw new InvalidConfigurationException("Incorrect value for GnuPG home directory");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName("qfe:decrypt")

            ->addArgument("file", InputArgument::REQUIRED)

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
        // retrieving data from input
        $file       = $input->getArgument('file');
        $username   = $input->getOption('username');
        $recipient  = $input->getOption('recipient');
        $passphrase = $input->getOption('passphrase');

        // checking that file exists
        if (!file_exists($file)) {
            throw new ResourceNotFoundException("File don't exists ({$file})");
        }

        // validation
        $validator = $this->getContainer()->get('validator');
        $error = $validator->validatePropertyValue(
            QKey::class,
            'recipient',
            $recipient
        );

        // exception if error
        if (0 != count($error)) {
            throw new KeyOptionsException((string) $error);
        }

        // spliting the given file
        preg_match('/(.*)\/(.*)\.enc$/', $file, $matches);
        $filename = $matches[2];

        // retrieving qfile and qkey
        /** @var QFile $qfile */
        $qfile = $this->qfileManager->readByUniqueFileName($filename);

        // checking if null
        if (null === $qfile) {
            throw new ResourceNotFoundException("No associated QFile entity with `{$filename}`");
        }

        // building the command
        $builder = new ProcessBuilder();
        $builder
            ->setPrefix("/usr/bin/gpg")
            ->addEnvironmentVariables(array(
                "GNUPGHOME" => "{$this->gpgHome}/{$username}"
            ))
            ->setArguments(array(
                '--no-tty',
                '--trust-model', 'always',

                '--decrypt',
                '--recipient', $recipient,
                '--passphrase', $passphrase,
                '--output', '/tmp/' . $qfile->getOriginalName(),
                $file
            ))
        ;

        // trying to decrypt
        try { $builder->getProcess()->mustRun(); } catch (ProcessFailedException $e) {
            // logging
            LogUtil::write_error($this->log_file, $e);

            // exception
            throw new DecryptException("Decryption error, see log file");
        }
    }
}