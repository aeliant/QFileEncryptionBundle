<?php
namespace Querdos\QFileEncryptionBundle\Util;

/**
 * Class LogUtil
 * @package Querdos\QFileEncryptionBundle\Util
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class LogUtil
{
    public static function write_error($log_file, \Exception $exception)
    {
        // opening file in write mode
        $stream = fopen($log_file, 'a');

        // logging
        fwrite($stream, "-----------------------------------\n");
        fwrite($stream, $exception->getMessage() . "\n\n");
        fwrite($stream, $exception->getTraceAsString() . "\n");
        fwrite($stream, "-----------------------------------\n");

        // closing file
        fclose($stream);
    }
}