<?php

namespace Querdos\QFileEncryptionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\VarDumper\VarDumper;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('q_file_encryption');


        $rootNode
            ->children()

//            ->scalarNode('encryption')
//            ->defaultValue('symetric')
//            ->end()
//
//            ->scalarNode('recipient')
//            ->defaultNull()
//            ->end()

            ->scalarNode('gnupg_home')
            ->defaultNull()
            ->end()
        ;

        return $treeBuilder;
    }
}
