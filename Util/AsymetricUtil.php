<?php
/**
 * Created by Hamza ESSAYEGH
 * User: querdos
 * Date: 4/9/17
 * Time: 6:31 PM
 */

namespace Querdos\QFileEncryptionBundle\Util;


use Querdos\QFileEncryptionBundle\Entity\QKey;
use Querdos\QFileEncryptionBundle\Manager\QKeyManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Tests\Encoder\PasswordEncoder;
use Symfony\Component\Security\Core\User\UserInterface;

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
//        $this->passwordEncoder->encodePassword()

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

        // dirname in temp directory
        $dirname = sprintf("/tmp/gpg_%s", uniqid());
        mkdir($dirname);

        // creating dir for user and setting correct permission
        mkdir($this->gnupg_home . "/{$user->getUsername()}");
        chmod($this->gnupg_home . "/{$user->getUsername()}", 0700);

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

        // preparing command for gpg
        $cmd = sprintf(
            "gpg --batch --gen-key %s/batch_gpg",
                $dirname
        );
        shell_exec($cmd);

        unlink("$dirname/batch_gpg");
        rmdir($dirname);

        // creating new entity
        $this->qkeyManager->create(new QKey(
            $recipient,
            $this->passwordEncoder->encodePassword($user, $passphrase)
        ));
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
}