<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use InvalidArgumentException;
use OaiPmh\Domain\ValueObject\NamespacePrefix;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the NamespacePrefix class.
 *
 * This class contains unit tests for the NamespacePrefix value object,
 * ensuring it behaves correctly as a value object in the OAI-PMH domain.
 */
class NamespacePrefixTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create a NamespacePrefix with a valid prefix
     * So that it can be used as an XML namespace prefix in OAI-PMH.
     */
    public function testCanInstantiateWithValidPrefix(): void
    {
        // Given: A valid XML namespace prefix
        $prefix = 'oai_dc';

        // When: I create a NamespacePrefix instance
        $namespacePrefix = new NamespacePrefix($prefix);

        // Then: The instance should be created successfully
        $this->assertInstanceOf(NamespacePrefix::class, $namespacePrefix);
        $this->assertSame($prefix, $namespacePrefix->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want NamespacePrefix to throw an exception for an invalid prefix
     * So that only valid XML namespace prefixes are accepted.
     */
    public function testThrowsExceptionForInvalidPrefix(): void
    {
        // Given: An invalid XML namespace prefix
        $invalidPrefix = '123-invalid!';

        // When: I try to create a NamespacePrefix instance with an invalid prefix
        // Then: It should throw an InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        new NamespacePrefix($invalidPrefix);
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two NamespacePrefix instances by value
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        // Given: Two NamespacePrefix instances with the same value
        $prefix = 'oai_dc';
        $array1 = new NamespacePrefix($prefix);
        $array2 = new NamespacePrefix($prefix);

        // When: I check if they are equal
        $isEqual = $array1->equals($array2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual, 'NamespacePrefix instances with the same value should be equal.');
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        // Given: Two NamespacePrefix instances with different values
        $array1 = new NamespacePrefix('oai_dc');
        $array2 = new NamespacePrefix('marc');

        // When: I check if they are equal
        $isEqual = $array1->equals($array2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual, 'NamespacePrefix instances with different values should not be equal.');
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of NamespacePrefix
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A NamespacePrefix instance with a valid prefix
        $prefix = 'oai_dc';
        $namespacePrefix = new NamespacePrefix($prefix);

        // When: I convert it to a string
        $stringRepresentation = (string)$namespacePrefix;

        // Then: The string representation should match the expected format
        $expected = "NamespacePrefix(prefix: $prefix)";
        $this->assertSame(
            $expected,
            $stringRepresentation,
            'String representation of NamespacePrefix should match expected format.'
        );
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that NamespacePrefix is immutable
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (see issue #8).
     */
    public function testIsImmutable(): void
    {
        // Given: A NamespacePrefix instance
        $namespacePrefix = new NamespacePrefix('oai_dc');

        // When: I use reflection to inspect its properties
        $reflection = new \ReflectionClass($namespacePrefix);

        // Then: All properties should be private
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }
}
