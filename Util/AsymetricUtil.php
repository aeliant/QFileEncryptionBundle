<?php
/**
 * Created by Hamza ESSAYEGH
 * User: querdos
 * Date: 4/9/17
 * Time: 6:31 PM
 */

namespace Querdos\QFileEncryptionBundle\Util;


use Querdos\QFileEncryptionBundle\Entity\QFile;
use Querdos\QFileEncryptionBundle\Entity\QKey;
use Querdos\QFileEncryptionBundle\Manager\QFileManager;
use Querdos\QFileEncryptionBundle\Manager\QKeyManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Tests\Encoder\PasswordEncoder;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\VarDumper\VarDumper;

class AsymetricUtil
{
    /**
     * @var string
     */
    private $gnupg_home;

    /**
     * @var QKeyManager
     */
    private $qkeyManager;

    /**
     * @var QFileManager
     */
    private $qFileManager;

    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;

    /**
     * Generate a keypair with the gien recipient,
     * passphrase and username
     *
     * @param string        $recipient
     * @param string        $passphrase
     * @param UserInterface $user
     */
    public function generate_key($recipient, $passphrase, UserInterface $user)
    {
        // checking recipient <> null
        if (null === $recipient || 0 == strlen($recipient)) {
            throw new Exception("Recipient cannot be null or empty. (value = {$recipient})");
        }

        // checking passphrase <> null
        if (null === $passphrase || 0 == strlen($passphrase)) {
            throw new Exception("Passphrase cannot be null or empty. (value = {$passphrase})");
        }

        // checking username <> null
        if (null === $user->getUsername() || 0 == strlen($user->getUsername())) {
            throw new Exception("Username cannot be null or empty. (value = {$user->getUsername()})");
        }

        // checking that user hasn't an existing key pair
        if (null !== $this->qkeyManager->findByUsername($user->getUsername())) {
            throw new Exception("User already have a saved key pair.");
        }

        // dirname in temp directory
        $dirname = sprintf("/tmp/gpg_%s", uniqid());
        mkdir($dirname);

        // creating dir for user and setting correct permission
        mkdir("{$this->gnupg_home}/{$user->getUsername()}", 0700);

        // generate the batch file for GPG
        $batchFile = fopen("$dirname/batch_gpg", "a");

        fwrite($batchFile, "Key-Type: DSA\n");
        fwrite($batchFile, "Key-Length: 2048\n");
        fwrite($batchFile, "Subkey-Type: ELG-E\n");
        fwrite($batchFile, "Subkey-Length: 2048\n");
        fwrite($batchFile, "Name-Real: {$user->getUsername()}\n");
        fwrite($batchFile, "Name-Comment: User {$user->getUsername()} key pair\n");
        fwrite($batchFile, "Name-Email: {$recipient}\n");
        fwrite($batchFile, "Expire-Date: 0\n");
        fwrite($batchFile, "Passphrase: {$passphrase}\n");
        fwrite($batchFile, "%pubring {$this->gnupg_home}/{$user->getUsername()}/{$user->getUsername()}.pub\n");
        fwrite($batchFile, "%secring {$this->gnupg_home}/{$user->getUsername()}/{$user->getUsername()}.sec\n");
        fwrite($batchFile, "%commit\n");

        // closing file
        fclose($batchFile);

        // generating keys
        shell_exec("gpg --batch --gen-key {$dirname}/batch_gpg");

        // removing temporary batch file and directory
        unlink("{$dirname}/batch_gpg");
        rmdir($dirname);

        // importing keys
        $user_dir = "{$this->gnupg_home}/{$user->getUsername()}";
        shell_exec("gpg --homedir $user_dir --import {$user_dir}/{$user->getUsername()}.pub");
        shell_exec("gpg --homedir $user_dir --import {$user_dir}/{$user->getUsername()}.sec");

        // creating new entity
        $this->qkeyManager->create(new QKey(
            $recipient,
            password_hash($passphrase, PASSWORD_BCRYPT),
            $user->getUsername()
        ));
    }

    /**
     * Encrypt the given file
     *
     * @param string $filePath
     * @param QKey   $qkey
     */
    public function encrypt_file($filePath, QKey $qkey)
    {
        // checking file path
        if (null === $filePath || !file_exists($filePath)) {
            throw new Exception("No valid file specified (value = {$filePath})");
        }

        // checking user has a key pair
        if (null === $this->qkeyManager->findByUsername($qkey->getUsername())) {
            throw new Exception("No key pair associated with {$qkey->getUsername()}");
        }

        // checking recipient
        if (null === $qkey->getRecipient()) {
            throw new Exception("No recipient specified");
        }

        // encrypting
        preg_match('/(.*)\/(.*)$/', $filePath, $matches);

        $path        = $matches[1];
        $oldFileName = $matches[2];
        $newFileName = uniqid((new \DateTime())->format('mdY'));
        $userdir     = "{$this->gnupg_home}/{$qkey->getUsername()}";

        shell_exec("gpg --homedir {$userdir} --trust-model always --encrypt --recipient {$qkey->getRecipient()} --output {$path}/{$newFileName}.enc {$filePath}");
        unlink($filePath);

        // creating QFile
        $this->qFileManager->create(new QFile(
            $oldFileName,
            $newFileName,
            $path
        ));
    }

    /**
     * Decrypt the given file
     *
     * @param QFile  $qfile
     * @param QKey   $qkey
     * @param string $passphrase
     *
     * @return BinaryFileResponse|null
     */
    public function decrypt_file(QFile $qfile, QKey $qkey, $passphrase)
    {
        // checking passphrase
        if (!password_verify($passphrase, $qkey->getPassphrase())) {
            throw new Exception("Invalid passphrase");
        }

        $userdir = "{$this->gnupg_home}/{$qkey->getUsername()}";
        $cmd = "echo \"{$passphrase}\" | gpg --homedir {$userdir} --trust-model always --decrypt --recipient {$qkey->getRecipient()} --passphrase-fd 0 --output /tmp/{$qfile->getOriginalName()} {$qfile->getPath()}/{$qfile->getFilename()}.enc";
        shell_exec($cmd);

        $response = new BinaryFileResponse("/tmp/{$qfile->getOriginalName()}");
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $qfile->getOriginalName());
        return $response;
    }

    /**
     * @param $gnupg_home
     */
    public function setGnupgHome($gnupg_home)
    {
        $this->gnupg_home = $gnupg_home;
    }

    /**
     * @param $manager
     */
    public function setQKeyManager($manager)
    {
        $this->qkeyManager = $manager;
    }

    /**
     * @param $encoder
     */
    public function setPasswordEncoder($encoder)
    {
        $this->passwordEncoder = $encoder;
    }

    public function setQFileManager($manager)
    {
        $this->qFileManager = $manager;
    }
}