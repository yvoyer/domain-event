<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Messaging;

use function array_key_exists;
use function call_user_func_array;
use function get_class;

final class MessageMapBus implements CommandBus, QueryBus
{
    /**
     * @var callable[]
     */
    private array $handlers = [];

    public function registerHandler(
        string $message,
        callable $handler,
    ): void {
        $this->handlers[$message] = $handler;
    }

    public function dispatchCommand(Command $command): void
    {
        $this->dispatch($command);
    }

    public function dispatchQuery(Query $query): void
    {
        $this->dispatch($query);
    }

    private function dispatch(Message $message): void
    {
        $class = get_class($message);
        if (! array_key_exists($class, $this->handlers)) {
            throw new NoHandlerFound($class);
        }

        $handler = $this->handlers[$class];
        call_user_func_array($handler, [$message]);
    }
}
