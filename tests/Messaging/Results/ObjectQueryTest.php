<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Messaging\Results;

use PHPUnit\Framework\TestCase;

final class ObjectQueryTest extends TestCase
{
    public function test_it_should_throw_exception_when_invoked_with_invalid_result(): void
    {
        $query = new class extends ObjectQuery {
            protected function getObjectType(): string
            {
                return \stdClass::class;
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(' expected an instance of "stdClass". Got: "invalid".');
        $query('invalid');
    }

    public function test_it_should_throw_exception_when_never_invoked(): void
    {
        $query = new class extends ObjectQuery {
            protected function getObjectType(): string
            {
                return \stdClass::class;
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(' was never invoked.');
        $query->getResult();
    }

    public function test_it_should_return_value_when_invoked(): void
    {
        $query = new class extends ObjectQuery {
            protected function getObjectType(): string
            {
                return \stdClass::class;
            }
        };
        $query((object) []);
        $this->assertInstanceOf(\stdClass::class, $query->getResult());
    }

    public function test_it_should_allow_subclass_of_object(): void
    {
        $query = new class extends ObjectQuery {
            protected function getObjectType(): string
            {
                return \DateTimeInterface::class;
            }
        };
        $query(new \DateTimeImmutable());
        $this->assertInstanceOf(\DateTimeInterface::class, $query->getResult());
    }
}
