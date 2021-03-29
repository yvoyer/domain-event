<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use InvalidArgumentException;
use Star\Component\DomainEvent\Messaging\Command;
use Star\Component\DomainEvent\Messaging\CommandBus;
use Star\Component\DomainEvent\Messaging\MessageMapBus;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class CommandBusPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = new Definition(MessageMapBus::class);
        foreach ($container->findTaggedServiceIds('star.command_handler') as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $handlerDefinition = $container->getDefinition($serviceId);
                $handlerClass = (string) $handlerDefinition->getClass();
                $command = (string) \substr($handlerClass, 0, (int) \strrpos($handlerClass, 'Handler'));
                if (\strlen($command) === 0) {
                    throw new InvalidArgumentException(
                        \sprintf('The handler "%s" must have a "Handler" suffix.', $handlerClass)
                    );
                }

                if (isset($tag['message'])) {
                    $command = (string) $tag['message'];
                }

                if (! \is_subclass_of($command, Command::class)) {
                    throw new \InvalidArgumentException(
                        \sprintf(
                            'The command "%s" must be a class implementing interface "%s".',
                            $command,
                            Command::class
                        )
                    );
                }

                $definition->addMethodCall(
                    'registerHandler',
                    [
                        $command,
                        new Reference($serviceId),
                    ]
                );
            }
        };

        $container->setDefinition('star.command_bus_default', $definition);
        $container->setAlias('star.command_bus', 'star.command_bus_default');
        $container->setAlias(CommandBus::class, 'star.command_bus');
    }
}
