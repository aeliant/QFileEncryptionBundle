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
            ->addOption('delete-original', 'd', InputOption::VALUE_OPTIONAL, null, true)

            ->setHidden(true)
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

        // checking if upload dir exists and create it if not
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir);
        }

        // trying to run the command
        try { $builder->getProcess()->mustRun(); } catch (ProcessFailedException $exception) {
            dump($exception->getMessage());
        }

        // remove the plain text if the option is true
        if ($delOriginal) {
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