<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Messaging\Results;

use PHPUnit\Framework\TestCase;

final class CollectionQueryTest extends TestCase
{
    public function test_it_should_throw_exception_when_invoked_with_invalid_result(): void
    {
        $query = new class extends CollectionQuery {};

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(' expected an array, got: "invalid".');
        $query('invalid');
    }

    public function test_it_should_return_default_value_when_never_invoked(): void
    {
        $query = new class extends CollectionQuery {};
        $this->assertSame([], $query->getResult());
    }

    public function test_it_should_return_value_when_invoked(): void
    {
        $query = new class extends CollectionQuery {};
        $query(['result']);
        $this->assertSame(['result'], $query->getResult());
    }
}
