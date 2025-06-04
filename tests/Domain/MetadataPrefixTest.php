<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use OaiPmh\Domain\MetadataPrefix;

/**
 * Tests for the MetadataPrefix class.
 *
 * This class contains unit tests for the MetadataPrefix value object,
 * ensuring it behaves correctly as a value object in the OAI-PMH domain.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
class MetadataPrefixTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create a MetadataPrefix with a valid prefix
     * So that it can be used in OAI-PMH requests and responses.
     */
    public function testCanInstantiateWithValidPrefix(): void
    {
        $prefix = 'oai_dc';
        $metadataPrefix = new MetadataPrefix($prefix);
        $this->assertInstanceOf(MetadataPrefix::class, $metadataPrefix);
        $this->assertSame($prefix, $metadataPrefix->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want MetadataPrefix to throw an exception for an empty prefix
     * So that only valid prefixes are accepted.
     */
    public function testThrowsExceptionForEmptyPrefix(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MetadataPrefix('');
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two MetadataPrefix instances by value
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        $prefix = 'oai_dc';
        $a = new MetadataPrefix($prefix);
        $b = new MetadataPrefix($prefix);
        $this->assertTrue($a->equals($b));
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        $a = new MetadataPrefix('oai_dc');
        $b = new MetadataPrefix('marc21');
        $this->assertFalse($a->equals($b));
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of MetadataPrefix
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        $prefix = 'oai_dc';
        $metadataPrefix = new MetadataPrefix($prefix);
        $expected = "MetadataPrefix(prefix: $prefix)";
        $this->assertSame($expected, (string)$metadataPrefix);
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that MetadataPrefix is immutable
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (see issue #8).
     */
    public function testIsImmutable(): void
    {
        $metadataPrefix = new MetadataPrefix('oai_dc');
        $reflection = new \ReflectionClass($metadataPrefix);
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }
}
