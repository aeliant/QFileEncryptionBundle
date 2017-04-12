<?php
namespace Querdos\QFileEncryptionBundle\Command;

use Querdos\QFileEncryptionBundle\Entity\QFile;
use Querdos\QFileEncryptionBundle\Manager\QFileManager;
use Querdos\QFileEncryptionBundle\Manager\QKeyManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

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

        // spliting the given file
        preg_match('/(.*)\/(.*).enc$/', $file, $matches);
        $filename = $matches[2];

        // retrieving qfile and qkey
        /** @var QFile $qfile */
        $qfile = $this->qfileManager->readByUniqueFileName($filename);

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
        try { $builder->getProcess()->mustRun(); } catch (Exception $e) {
            echo $e->getMessage(); die;
        }
    }
}