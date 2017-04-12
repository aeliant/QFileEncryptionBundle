<?php
namespace Querdos\QFileEncryptionBundle\Exception;

/**
 * Exception thrown when a given option for GPG is wrong:
 *      - the username
 *      - the recipient
 *
 * Class KeyOptionsException
 *
 * @package Querdos\QFileEncryptionBundle\Exception
 *
 * @author  Hamza ESSAYEGH <hamza.essayegh@protonmail.com>
 */
class KeyOptionsException extends \RuntimeException
{
}