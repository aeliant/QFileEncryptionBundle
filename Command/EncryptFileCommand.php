<?php
/**
 * Created by Hamza ESSAYEGH
 * User: querdos
 * Date: 4/11/17
 * Time: 1:15 PM
 */

namespace Querdos\QFileEncryptionBundle\Command;


use Querdos\QFileEncryptionBundle\Entity\QFile;
use Querdos\QFileEncryptionBundle\Manager\QFileManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class EncryptFileCommand extends ContainerAwareCommand
{
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
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // params
        $file      = $input->getArgument('file');
        $username  = $input->getOption('username');
        $recipient = $input->getOption('recipient');

        preg_match('/(.*)\/(.*)$/', $file, $matches);
        if (0 == count($matches)) {
            $filename = $file;
            echo "File: {$file}\n";
        } else {
            $path     = $matches[1];
            $filename = $matches[2];
        }

        // upload directory
        $uploads_dir = $this->getContainer()->get('kernel')->getRootDir() . "/../web/uploads";
        $newFileName = uniqid((new \DateTime())->format('mdY'));

        // gnupg home
        $gnupg_home = $this->getContainer()->getParameter('q_file_encryption.gnupg_home');

        // building the command
        $builder = new ProcessBuilder();
        $builder
            ->setPrefix("/usr/bin/gpg")
            ->setEnv("GNUPGHOME", $gnupg_home . "/{$username}")
            ->setArguments(array(
                '--trust-model', 'always',

                '--encrypt',
                '--recipient', $recipient,
                '--output', "{$uploads_dir}/{$newFileName}.enc",
                $file
            ))
        ;

        try {
            $builder->getProcess()->mustRun();
        } catch (ProcessFailedException $exception) {
            dump($exception->getMessage());
        }

        // remove the plain text
        unlink($file);

        $this->qfileManager->create(new QFile(
            $filename,
            $newFileName,
            $uploads_dir
        ));
    }
}