<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Messaging\Results;

use PHPUnit\Framework\TestCase;

final class ScalarQueryTest extends TestCase
{
    public function test_it_should_throw_exception_when_invoked_with_array(): void
    {
        $query = new class extends ScalarQuery {};

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(' expected a scalar, got: "<ARRAY>".');
        $query([]);
    }

    public function test_it_should_throw_exception_when_invoked_with_object(): void
    {
        $query = new class extends ScalarQuery {};

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(' expected a scalar, got: "stdClass".');
        $query(new \stdClass());
    }

    public function test_it_should_throw_exception_when_never_invoked(): void
    {
        $query = new class extends ScalarQuery {};

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('was never invoked.');
        $query->getResult();
    }

    /**
     * @param $result
     * @dataProvider provideSupportedScalars
     */
    public function test_it_should_return_value_when_invoked_with_string($result): void
    {
        $query = new class extends ScalarQuery {};
        $query($result);
        $this->assertSame($result, $query->getResult());
    }

    public static function provideSupportedScalars(): array
    {
        return [
            ['string'],
            [123],
            [true],
            [12.34],
        ];
    }
}
