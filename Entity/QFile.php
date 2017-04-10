<?php
/**
 * Created by Hamza ESSAYEGH
 * User: querdos
 * Date: 4/9/17
 * Time: 4:43 PM
 */

namespace Querdos\QFileEncryptionBundle\Entity;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class QFile
 *
 * Entity used to store informations about files to encrypt
 */
class QFile
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var File
     */
    private $file;

    /**
     * QFile constructor.
     *
     * @param File $file
     */
    public function __construct(File $file)
    {
        if (null === $file) {
            throw new Exception("No file specified");
        }

        $this->file = $file;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return QFile
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param File $file
     *
     * @return QFile
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }
}