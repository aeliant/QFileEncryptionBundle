<?php
namespace Querdos\QFileEncryptionBundle\Entity;

/**
 * Class QFile
 *
 * Entity used to store informations about files to encrypt
 *
 * @package Querdos\QFileEncryptionBundle\Entity
 * @author Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
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
     * @var QKey
     */
    private $qkey;

    /**
     * QFile constructor.
     *
     * @param string $original_name
     * @param string $filename
     * @param string $path
     * @param QKey   $qkey
     */
    public function __construct($original_name = null, $filename = null, $path = null, QKey $qkey = null)
    {
        $this->original_name = $original_name;
        $this->filename      = $filename;
        $this->path          = $path;
        $this->qkey          = $qkey;
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

    /**
     * @return QKey
     */
    public function getQkey()
    {
        return $this->qkey;
    }

    /**
     * @param QKey $qkey
     *
     * @return QFile
     */
    public function setQkey($qkey)
    {
        $this->qkey = $qkey;
        return $this;
    }
}