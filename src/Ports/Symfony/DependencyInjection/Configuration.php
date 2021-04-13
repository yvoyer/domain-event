<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use Star\Component\DomainEvent\Ports\Symfony\SymfonyPublisher;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree = new TreeBuilder('domain-event');
        $rootNode = $tree->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('dispatcher_id')->defaultValue('event_dispatcher')->end()
            ->end();

        $rootNode->children()
            ->arrayNode('logging')
                ->children()
                    ->scalarNode('logger_id')->end()
                    ->scalarNode('publisher_id')->defaultValue(SymfonyPublisher::class)->end()
                ->end()
            ->end()
        ->end();

        return $tree;
    }
}
