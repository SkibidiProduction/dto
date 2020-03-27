<?php

namespace Cerbero\Dto;

use Cerbero\Dto\Manipulators\ArrayConverter;
use Cerbero\Dto\Manipulators\DateTimeConverter;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

/**
 * Tests for ArrayConverter.
 *
 */
class ArrayConverterTest extends TestCase
{
    /**
     * This method is called before each test.
     * 
     */
    protected function setUp(): void
    {
        // reset ArrayConverter instance
        $instances = new ReflectionProperty(ArrayConverter::class, 'instance');
        $instances->setAccessible(true);
        $instances->setValue(null, null);
        $instances->setAccessible(false);
    }

    /**
     * @test
     */
    public function is_singleton()
    {
        $converter1 = ArrayConverter::instance();
        $converter2 = ArrayConverter::instance();

        $this->assertSame($converter1, $converter2);
    }

    /**
     * @test
     */
    public function sets_and_gets_conversions()
    {
        $converter = ArrayConverter::instance();

        $this->assertEmpty($converter->getConversions());

        $conversions = ['foo' => 'bar'];

        $converter->setConversions($conversions);

        $this->assertSame($conversions, $converter->getConversions());
    }

    /**
     * @test
     */
    public function calls_registered_conversions()
    {
        $data = [
            'key1' => 'value1',
            'key2' => [
                'key3' => new DateTime('2020-01-01'),
            ],
        ];

        $expected = [
            'key1' => 'value1',
            'key2' => [
                'key3' => '2020-01-01',
            ],
        ];

        $converter = ArrayConverter::instance()->setConversions([
            'DateTime' => DateTimeConverter::class,
        ]);

        $this->assertSame($expected, $converter->convert($data));
    }

    /**
     * @test
     */
    public function gets_converter_by_instance()
    {
        $converter = ArrayConverter::instance()->setConversions([
            'DateTime' => DateTimeConverter::class,
        ]);

        $dateTimeConverter = $converter->getConverterByInstance(new DateTime());

        $this->assertInstanceOf(DateTimeConverter::class, $dateTimeConverter);
        $this->assertSame($dateTimeConverter, $converter->getConverterByInstance(new DateTime()));

        $this->assertNull($converter->getConverterByInstance(new stdClass()));
    }

    /**
     * @test
     */
    public function gets_converter_by_class()
    {
        $converter = ArrayConverter::instance()->setConversions([
            'DateTime' => DateTimeConverter::class,
        ]);

        $dateTimeConverter = $converter->getConverterByClass(DateTime::class);

        $this->assertInstanceOf(DateTimeConverter::class, $dateTimeConverter);
        $this->assertSame($dateTimeConverter, $converter->getConverterByClass(DateTime::class));

        $this->assertNull($converter->getConverterByClass(stdClass::class));
    }
}