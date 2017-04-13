<?php

namespace Querdos\QFileEncryptionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\VarDumper\VarDumper;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class QFileEncryptionExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // formatting gnu_home path
        $gnupg_home = $this->format_path(
            $config['gnupg_home'],
            $container->getParameter('kernel.root_dir')
        );

        // formatting enc_dir
        $enc_dir = $this->format_path(
            "web/" . $config['enc_dir'],
            $container->getParameter('kernel.root_dir')
        );

        // formatting logs dir
        $logs_dir = $this->format_path(
            $config['logs_dir'],
            $container->getParameter('kernel.root_dir')
        );

        // setting configuration in the container
        $container->setParameter("q_file_encryption.gnupg_home", $gnupg_home);
        $container->setParameter('q_file_encryption.enc_dir', $enc_dir);
        $container->setParameter('q_file_encryption.logs_dir', $logs_dir);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yml');
        $loader->load('repositories.yml');
        $loader->load('managers.yml');
    }

    /**
     * Format a given path to absolute one
     *  - For example, will transform ~/Documents/.gnupg as /home/user/Documents/.gnupg
     *
     * @param string $path
     * @param string $rootDir
     *
     * @return string
     */
    private function format_path($path, $rootDir)
    {
        // retrieving current user
        $user  = exec('whoami');

        // building replacement pattern
        $rep_1     = sprintf('/home/%s${1}', $user);
        $rep_slash = '${1}'; // used to remove the / character at the end (eventually)
        $rep_2     = sprintf('%s/../${1}', $rootDir);

        // building regex pattern
        $pat_1     = '/^\~(.*)\/{0,1}/';
        $pat_slash = '/(.*)\/$/';
        $pat_2     = '/(^[A-Za-z].*)/';

        $formatted = preg_replace($pat_slash, $rep_slash, $path);

        $formatted = preg_replace($pat_1, $rep_1, $formatted);
        if ($formatted !== $path) return $formatted;

        $formatted = preg_replace($pat_2, $rep_2, $formatted);
        if ($formatted !== $path) return $formatted;

        return $formatted;
    }
}
