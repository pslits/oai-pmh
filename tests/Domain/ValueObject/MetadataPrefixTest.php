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
use OaiPmh\Domain\ValueObject\MetadataPrefix;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the MetadataPrefix class.
 *
 * This class contains unit tests for the MetadataPrefix value object,
 * ensuring it behaves correctly as a value object in the OAI-PMH domain.
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
        // Given: A valid prefix
        $prefix = 'oai_dc';

        // When: I create a MetadataPrefix instance
        $metadataPrefix = new MetadataPrefix($prefix);

        // Then: The instance should be created successfully
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
        // Given: An empty prefix
        $metadataPrefix = '';

        // When: I try to create a MetadataPrefix instance with the empty prefix
        // Then: It should throw an InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        new MetadataPrefix($metadataPrefix);
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two MetadataPrefix instances by value
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        // Given: Two MetadataPrefix instances with the same value
        $prefix = 'oai_dc';
        $array1 = new MetadataPrefix($prefix);
        $array2 = new MetadataPrefix($prefix);

        // When: I check if they are equal
        $isEqual = $array1->equals($array2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual, 'MetadataPrefix instances with the same value should be equal.');
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        // Given: Two MetadataPrefix instances with different values
        $array1 = new MetadataPrefix('oai_dc');
        $array2 = new MetadataPrefix('marc21');

        // When: I check if they are equal
        $isEqual = $array1->equals($array2);

        // Then: They should not be considered equal
        $this->assertFalse(
            $isEqual,
            'MetadataPrefix instances with different values should not be equal.'
        );
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of MetadataPrefix
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A MetadataPrefix instance
        $prefix = 'oai_dc';
        $metadataPrefix = new MetadataPrefix($prefix);

        // When: I convert it to a string
        $stringRepresentation = (string)$metadataPrefix;

        // Then: The string representation should match the expected format
        $expected = "MetadataPrefix(prefix: $prefix)";
        $this->assertSame(
            $expected,
            $stringRepresentation,
            'String representation of MetadataPrefix should match expected format.'
        );
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
        // Given: A MetadataPrefix instance
        $metadataPrefix = new MetadataPrefix('oai_dc');

        // When: I create a ReflectionClass instance
        $reflection = new \ReflectionClass($metadataPrefix);

        // Then: All properties should be private
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }
}
