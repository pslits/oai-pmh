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
use OaiPmh\Domain\MetadataRootTag;

/**
 * Tests for the MetadataRootTag class.
 *
 * This class contains unit tests for the MetadataRootTag value object,
 * ensuring it behaves correctly as a value object in the OAI-PMH domain.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
class MetadataRootTagTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create a MetadataRootTag with a valid XML element name
     * So that it can be used as the root tag in OAI-PMH metadata.
     */
    public function testCanInstantiateWithValidRootTag(): void
    {
        // Given: A valid XML element name
        $tag = 'oai_dc:dc';

        // When: I create a MetadataRootTag instance
        $rootTag = new MetadataRootTag($tag);

        // Then: The instance should be created successfully
        $this->assertInstanceOf(MetadataRootTag::class, $rootTag);
        $this->assertSame($tag, $rootTag->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want MetadataRootTag to throw an exception for an invalid tag
     * So that only valid XML element names are accepted.
     */
    public function testThrowsExceptionForInvalidRootTag(): void
    {
        // Given: An invalid XML element name
        $invalidTag = '123-invalid-tag!';

        // When: I try to create a MetadataRootTag instance with the invalid tag
        // Then: An InvalidArgumentException should be thrown
        $this->expectException(InvalidArgumentException::class);
        new MetadataRootTag('123-invalid-tag!');
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two MetadataRootTag instances by value
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        // Given: Two MetadataRootTag instances with the same value
        $tag = 'oai_dc:dc';
        $array1 = new MetadataRootTag($tag);
        $array2 = new MetadataRootTag($tag);

        // When: I check if they are equal
        $isEqual = $array1->equals($array2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual);
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        // Given: Two MetadataRootTag instances with different values
        $array1 = new MetadataRootTag('oai_dc:dc');
        $array2 = new MetadataRootTag('marc:record');

        // When: I check if they are equal
        $isEqual = $array1->equals($array2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of MetadataRootTag
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A MetadataRootTag instance
        $tag = 'oai_dc:dc';
        $rootTag = new MetadataRootTag($tag);

        // When: I convert it to a string
        $stringRepresentation = (string)$rootTag;

        // Then: The string representation should match the expected format
        $expected = "MetadataRootTag(rootTag: $tag)";
        $this->assertSame($expected, $stringRepresentation);
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that MetadataRootTag is immutable
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (see issue #8).
     */
    public function testIsImmutable(): void
    {
        // Given: A MetadataRootTag instance
        $rootTag = new MetadataRootTag('oai_dc:dc');

        // When: I use reflection to inspect its properties
        $reflection = new \ReflectionClass($rootTag);

        // Then: All properties should be private, indicating immutability
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }
}
