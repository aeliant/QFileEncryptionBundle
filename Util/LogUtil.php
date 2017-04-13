<?php
namespace Querdos\QFileEncryptionBundle\Util;

use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class LogUtil
 * @package Querdos\QFileEncryptionBundle\Util
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class LogUtil
{
    /** Error file */
    const LOG_FILE_ERROR = "qfe_error.log";

    /** Info file */
    const LOG_FILE_INFO  = "qfe_info.log";

    /**
     * @var string
     */
    private $log_file_error;

    /**
     * Write an error log into the error file
     *
     * @param \Exception $exception
     */
    public function write_error(\Exception $exception)
    {
        // opening file in write mode
        $stream = fopen($this->log_file_error, 'a');

        // logging
        fwrite($stream, "-----------------------------------\n");
        fwrite($stream, $exception->getMessage() . "\n\n");
        fwrite($stream, $exception->getTraceAsString() . "\n");
        fwrite($stream, "-----------------------------------\n");

        // closing file
        fclose($stream);
    }

    /**
     * @param Kernel $kernel
     * @param string $log_dir
     */
    public function setLogError($kernel, $log_dir)
    {
        // checking log dir value
        if (null === $log_dir) {
            throw new InvalidConfigurationException("Invalid value for log_dir");
        }

        // setting log file
        $this->log_file_error = $kernel->getRootDir() . '/../' . $log_dir . self::LOG_FILE_ERROR;
    }
}