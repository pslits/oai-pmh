<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain;

use OaiPmh\Domain\AnyUri;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use OaiPmh\Domain\NamespacePrefix;
use OaiPmh\Domain\MetadataNamespace;
use OaiPmh\Domain\MetadataNamespaceCollection;

/**
 * Tests for the MetadataNamespaceCollection class.
 *
 * This class contains unit tests for the MetadataNamespaceCollection value object,
 * ensuring it behaves correctly as a collection of MetadataNamespace objects in the OAI-PMH domain.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
class MetadataNamespaceCollectionTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create a MetadataNamespaceCollection with one or more MetadataNamespace objects
     * So that I can represent a set of XML namespaces.
     */
    public function testCanInstantiateWithNamespaces(): void
    {
        // Given: Two MetadataNamespace objects
        $namespace1 = $this->givenNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $namespace2 = $this->givenNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');

        // When: I create a MetadataNamespaceCollection with these namespaces
        $collection = new MetadataNamespaceCollection($namespace1, $namespace2);

        // Then: The collection should be created successfully
        $this->assertInstanceOf(MetadataNamespaceCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertSame($namespace1, iterator_to_array($collection)[0]);
        $this->assertSame($namespace2, iterator_to_array($collection)[1]);
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that the collection cannot be empty
     * So that there is always at least one namespace.
     */
    public function testThrowsExceptionForEmptyCollection(): void
    {
        // Given: No namespaces provided

        // When: I try to create a MetadataNamespaceCollection without any namespaces
        $this->expectException(InvalidArgumentException::class);
        new MetadataNamespaceCollection();

        // Then: An exception should be thrown
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that the collection does not allow duplicate prefixes
     * So that each namespace is unique.
     */
    public function testThrowsExceptionForDuplicatePrefixes(): void
    {
        // Given: Two MetadataNamespace objects with the same prefix
        $namespace1 = $this->givenNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $namespace2 = $this->givenNamespace('dc', 'http://www.openarchives.org/OAI/2.0/');

        // When: I try to create a MetadataNamespaceCollection with duplicate prefixes
        $this->expectException(InvalidArgumentException::class);
        new MetadataNamespaceCollection($namespace1, $namespace2);

        // Then: An exception should be thrown
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that the collection does not allow duplicate URIs
     * So that each namespace is unique.
     */
    public function testThrowsExceptionForDuplicateUris(): void
    {
        // Given: Two MetadataNamespace objects with the same URI
        $namespace1 = $this->givenNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $namespace2 = $this->givenNamespace('oai', 'http://purl.org/dc/elements/1.1/');

        // When: I try to create a MetadataNamespaceCollection with duplicate URIs
        $this->expectException(InvalidArgumentException::class);
        new MetadataNamespaceCollection($namespace1, $namespace2);

        // Then: An exception should be thrown
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two collections for value equality
     * So that collections with the same namespaces in the same order are equal.
     */
    public function testEqualsReturnsTrueForSameNamespacesAndOrder(): void
    {
        // Given: Two MetadataNamespaceCollection objects with the same namespaces
        $namespace1 = $this->givenNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $namespace2 = $this->givenNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
        $col1 = new MetadataNamespaceCollection($namespace1, $namespace2);
        $col2 = new MetadataNamespaceCollection($namespace1, $namespace2);

        // When: I compare the two collections for equality
        $isEqual = $col1->equals($col2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual);
    }

    public function testEqualsReturnsFalseForDifferentOrder(): void
    {
        // Given: Two MetadataNamespaceCollection objects with the same namespaces but in different order
        $namespace1 = $this->givenNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $namespace2 = $this->givenNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
        $col1 = new MetadataNamespaceCollection($namespace1, $namespace2);
        $col2 = new MetadataNamespaceCollection($namespace2, $namespace1);

        // When: I compare the two collections for equality
        $isEqual = $col1->equals($col2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    public function testEqualsReturnsFalseForDifferentNamespaces(): void
    {
        // Given: Two MetadataNamespaceCollection objects with the same namespaces but in different order
        $namespace1 = $this->givenNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $namespace2 = $this->givenNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
        $ns3 = $this->givenNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $col1 = new MetadataNamespaceCollection($namespace1, $namespace2);
        $col2 = new MetadataNamespaceCollection($namespace1, $ns3);

        // When: I compare the two collections for equality
        $isEqual = $col1->equals($col2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    public function testEqualsReturnsFalseForDifferentCount(): void
    {
        // Given: Two MetadataNamespaceCollection objects with different number of namespaces
        $namespace1 = $this->givenNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $namespace2 = $this->givenNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
        $col1 = new MetadataNamespaceCollection($namespace1);
        $col2 = new MetadataNamespaceCollection($namespace1, $namespace2);

        // When: I compare the two collections for equality
        $isEqual = $col1->equals($col2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of the collection
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A MetadataNamespaceCollection with two namespaces
        $namespace1 = $this->givenNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $namespace2 = $this->givenNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
        $collection = new MetadataNamespaceCollection($namespace1, $namespace2);

        // When: I convert the collection to a string
        $stringRepresentation = (string)$collection;

        // Then: It should return a string in the expected format
        $expected = sprintf(
            'MetadataNamespaceCollection(namespaces: %s, %s)',
            (string)$namespace1,
            (string)$namespace2
        );
        $this->assertSame($expected, $stringRepresentation);
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure the collection is immutable
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (see issue #8).
     */
    public function testCollectionIsImmutable(): void
    {
        // Given: A MetadataNamespaceCollection with one namespace
        $collection = new MetadataNamespaceCollection(
            $this->givenNamespace('dc', 'http://purl.org/dc/elements/1.1/')
        );

        // When: I reflect on the collection's properties
        $reflection = new \ReflectionClass($collection);

        // Then: All properties should be private, ensuring immutability
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }

    // --- Helpers ---

    private function givenNamespace(string $prefix, string $uri): MetadataNamespace
    {
        return new MetadataNamespace(
            new NamespacePrefix($prefix),
            new AnyUri($uri)
        );
    }
}
