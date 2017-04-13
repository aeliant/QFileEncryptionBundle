<?php

namespace Querdos\QFileEncryptionBundle\Command;

use Querdos\QFileEncryptionBundle\Entity\QFile;
use Querdos\QFileEncryptionBundle\Entity\QKey;
use Querdos\QFileEncryptionBundle\Exception\EncryptionException;
use Querdos\QFileEncryptionBundle\Exception\KeyOptionsException;
use Querdos\QFileEncryptionBundle\Manager\QFileManager;
use Querdos\QFileEncryptionBundle\Util\LogUtil;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class EncryptFileCommand
 * @package Querdos\QFileEncryptionBundle\Command
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class EncryptFileCommand extends ContainerAwareCommand
{
    /**
     * @var QFileManager
     */
    private $qfileManager;

    /**
     * @var string
     */
    private $gnupg_home;

    /**
     * @var LogUtil
     */
    private $logUtil;

    /**
     * {@inheritdoc}
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->qfileManager = $this->getContainer()->get('qfe.manager.qfile');
        $this->gnupg_home   = $this->getContainer()->getParameter('q_file_encryption.gnupg_home');
        $this->logUtil      = $this->getContainer()->get('q_fe.util.log');

        // checking gnupg_home
        if (null === $this->gnupg_home) {
            throw new InvalidConfigurationException("Incorrect value for the GNUPG_HOME parameter");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName("qfe:encrypt")

            ->addArgument("file", InputArgument::REQUIRED)

            ->addOption("username", "u", InputOption::VALUE_REQUIRED)
            ->addOption("recipient", "r", InputOption::VALUE_REQUIRED)
            ->addOption('delete-original', 'd', InputOption::VALUE_REQUIRED, null, true)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // params
        $file        = $input->getArgument('file');
        $username    = $input->getOption('username');
        $recipient   = $input->getOption('recipient');
        $delOriginal = $input->getOption('delete-original');

        // checking that the file exists
        if (!file_exists($file)) {
            throw new ResourceNotFoundException("File not found");
        }

        // checking that recipient is correct
        $validator = $this->getContainer()->get('validator');
        $error = $validator->validatePropertyValue(
            QKey::class,
            'recipient',
            $recipient
        );

        // throwing exception if error
        if (count($error) != 0) {
            throw new KeyOptionsException((string) $error);
        }

        preg_match('/.*\/(.*)$/', $file, $matches);
        if (0 == count($matches)) {
            $filename = $file;
        } else {
            $filename = $matches[1];
        }

        // upload directory
        $enc_dir     = $this->getContainer()->getParameter('q_file_encryption.enc_dir');
        $uploads_dir = $this->getContainer()->get('kernel')->getRootDir() . '/../web/' . $enc_dir;
        $newFileName = uniqid((new \DateTime())->format('mdY'));

        // building the command
        $builder = new ProcessBuilder();
        $builder
            ->setPrefix("/usr/bin/gpg")
            ->setEnv("GNUPGHOME", $this->gnupg_home . "/{$username}")
            ->setArguments(array(
                '--trust-model', 'always',

                '--encrypt',
                '--recipient', $recipient,
                '--output', "{$uploads_dir}/{$newFileName}.enc",
                $file
            ))
        ;

        // checking if upload dir exists and create it if not
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir);
        }

        // trying to run the command
        try { $builder->getProcess()->mustRun(); } catch (ProcessFailedException $e) {
            // logging
            $this->logUtil->write_error($e);

            // exception
            throw new EncryptionException("Encryption error, see log file");
        }

        // remove the plain text if the option is true
        if (true === $delOriginal) {
            unlink($file);
        }

        // persist new entity in database
        $this->qfileManager->create(new QFile(
            $filename,
            $newFileName,
            $uploads_dir
        ));
    }
}