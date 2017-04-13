<?php
namespace Querdos\QFileEncryptionBundle\Util;

use Psr\Log\InvalidArgumentException;
use Querdos\QFileEncryptionBundle\Entity\QFile;
use Querdos\QFileEncryptionBundle\Entity\QKey;
use Querdos\QFileEncryptionBundle\Exception\KeyOptionsException;
use Querdos\QFileEncryptionBundle\Manager\QKeyManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AsymetricUtil
 * @package Querdos\QFileEncryptionBundle\Util
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class AsymetricUtil
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var QKeyManager
     */
    private $qkeyManager;

    /**
     * Generate a keypair with the gien recipient,
     * passphrase and username
     *
     * @param string        $recipient
     * @param string        $passphrase
     * @param UserInterface $user
     */
    public function generate_key(UserInterface $user, $passphrase, $recipient = null)
    {
        // creating application with current kernel
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        // creating input
        $input = array(
            'command'      => 'qfe:gen-key',
            '--username'   => $user->getUsername(),
            '--passphrase' => $passphrase
        );

        // checking recipient. if recipient not null, it has a priority over the user interface email field (if exists)
        if (null !== $recipient) {
            $input['--recipient'] = $recipient;
        } else { // here, recipient is null
            // checking if the user interface has an email field
            if (method_exists($user, 'getEmail')) {
                $input['--recipient'] = $user->getEmail();
            } else {
                throw new KeyOptionsException("No recipient specified for the key generation");
            }
        }

        // creating the array input
        $input = new ArrayInput($input);

        // Creating output
        $output = new NullOutput();

        // running the key generation
        $application->run($input, $output);
    }

    /**
     * Encrypt the given file
     *
     * @param string $filePath
     * @param QKey   $qkey
     */
    public function encrypt_file($filePath, QKey $qkey)
    {
        // creating application with kernel
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        // creating input for encryption
        $input = new ArrayInput(array(
            'command' => 'qfe:encrypt',
            '--username' => $qkey->getUsername(),
            'file' => $filePath
        ));

        // creating null output
        $output = new NullOutput();

        // running application
        $application->run($input, $output);
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
        // creating application with current kernel
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        // creating input array
        $input = new ArrayInput(array(
            'command'       => 'qfe:decrypt',
            '--username'    => $qkey->getUsername(),
            '--recipient'   => $qkey->getRecipient(),
            '--passphrase'  => $passphrase,
            'file'          => "{$qfile->getPath()}/{$qfile->getFilename()}.enc",
        ));

        $output = new NullOutput();

        $application->run($input, $output);

        // creating the response
        $response = new BinaryFileResponse("/tmp/{$qfile->getOriginalName()}");
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $qfile->getOriginalName());

        return $response;
    }

    /**
     * @param Kernel $kernel
     *
     * @return AsymetricUtil
     */
    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
        return $this;
    }

    /**
     * @param QKeyManager $qkeyManager
     *
     * @return AsymetricUtil
     */
    public function setQkeyManager($qkeyManager)
    {
        $this->qkeyManager = $qkeyManager;
        return $this;
    }
}