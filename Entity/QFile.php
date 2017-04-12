<?php
namespace Querdos\QFileEncryptionBundle\Entity;

/**
 * Class QFile
 *
 * Entity used to store informations about files to encrypt
 *
 * @author Hamza ESSAYEGH <hamza.Essayegh@protonmail.com>
 */
class QFile
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $original_name;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $path;

    /**
     * QFile constructor.
     *
     * @param string $original_name
     * @param string $filename
     * @param string $path
     */
    public function __construct($original_name = null, $filename = null, $path = null)
    {
        $this->original_name = $original_name;
        $this->filename      = $filename;
        $this->path          = $path;
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
     * @return string
     */
    public function getOriginalName()
    {
        return $this->original_name;
    }

    /**
     * @param string $original_name
     *
     * @return QFile
     */
    public function setOriginalName($original_name)
    {
        $this->original_name = $original_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     *
     * @return QFile
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return QFile
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
}