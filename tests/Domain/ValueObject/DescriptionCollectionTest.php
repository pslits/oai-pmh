<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use OaiPmh\Domain\ValueObject\Description;
use OaiPmh\Domain\ValueObject\DescriptionCollection;
use OaiPmh\Domain\ValueObject\DescriptionFormat;
use OaiPmh\Domain\ValueObject\MetadataNamespaceCollection;
use OaiPmh\Domain\ValueObject\MetadataNamespace;
use OaiPmh\Domain\ValueObject\NamespacePrefix;
use OaiPmh\Domain\ValueObject\AnyUri;
use OaiPmh\Domain\ValueObject\MetadataRootTag;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the DescriptionCollection class.
 *
 * This class contains unit tests for the DescriptionCollection value object,
 * ensuring it behaves correctly as a collection in the OAI-PMH domain.
 */
class DescriptionCollectionTest extends TestCase
{
    /**
     * User Story:
     * As a developer when I need to represent a repository with no descriptions,
     * I want to create an empty DescriptionCollection
     * because I need to handle repositories that have no descriptions.
     */
    public function testCanInstantiateEmptyCollection(): void
    {
        // Given: No Description objects
        // When: I create an empty DescriptionCollection
        $collection = new DescriptionCollection();

        // Then: The collection should be created successfully
        $this->assertInstanceOf(DescriptionCollection::class, $collection);
        $this->assertCount(0, $collection);
        $this->assertSame([], $collection->toArray());
    }

    /**
     * User Story:
     * As a developer when I need to represent a repository with a single description,
     * I want to create a DescriptionCollection with one Description object
     * because I need to support repositories that have exactly one description.
     */
    public function testCanInstantiateWithSingleDescription(): void
    {
        // Given: A valid Description object
        $description = $this->createOaiIdentifierDescription();

        // When: I create a DescriptionCollection with this description
        $collection = new DescriptionCollection($description);

        // Then: The collection should be created successfully
        $this->assertInstanceOf(DescriptionCollection::class, $collection);
        $this->assertCount(1, $collection);
        $this->assertSame([$description], $collection->toArray());
    }

    /**
     * User Story:
     * As a developer when I need to represent a repository with multiple descriptions,
     * I want to create a DescriptionCollection with multiple Description objects
     * because I need to support repositories that have several descriptions.
     */
    public function testCanInstantiateWithMultipleDescriptions(): void
    {
        // Given: Two valid Description objects
        $description1 = $this->createOaiIdentifierDescription();
        $description2 = $this->createCustomDescription();

        // When: I create a DescriptionCollection with these descriptions
        $collection = new DescriptionCollection($description1, $description2);

        // Then: The collection should be created successfully
        $this->assertInstanceOf(DescriptionCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertSame([$description1, $description2], $collection->toArray());
    }

    /**
     * User Story:
     * As a developer when I need to determine how many descriptions are present,
     * I want to count the number of descriptions in the collection
     * because I need to know the collection size.
     */
    public function testCanCountDescriptions(): void
    {
        // Given: A DescriptionCollection with three descriptions
        $collection = new DescriptionCollection(
            $this->createOaiIdentifierDescription(),
            $this->createCustomDescription(),
            $this->createOaiIdentifierDescription()
        );

        // When: I count the descriptions
        $count = count($collection);

        // Then: The count should be 3
        $this->assertSame(3, $count);
        $this->assertSame(3, $collection->count());
    }

    /**
     * User Story:
     * As a developer when I need to access each description in the collection,
     * I want to iterate over the DescriptionCollection
     * because I need to process or inspect each description.
     */
    public function testCanIterateOverCollection(): void
    {
        // Given: A DescriptionCollection with two Description objects
        $description1 = $this->createOaiIdentifierDescription();
        $description2 = $this->createCustomDescription();
        $collection = new DescriptionCollection($description1, $description2);

        // When: I iterate over the collection
        $iterations = 0;
        foreach ($collection as $description) {
            // Then: Each item should be a Description object
            $this->assertInstanceOf(
                Description::class,
                $description,
                'Each item in DescriptionCollection should be a Description object.'
            );
            $iterations++;
        }

        // And: I should have iterated over all descriptions
        $this->assertSame(2, $iterations);
    }

    /**
     * User Story:
     * As a developer when I need to access descriptions by index or use array functions,
     * I want to convert the collection to an array
     * because array access and functions are convenient.
     */
    public function testCanConvertToArray(): void
    {
        // Given: A DescriptionCollection with two descriptions
        $description1 = $this->createOaiIdentifierDescription();
        $description2 = $this->createCustomDescription();
        $collection = new DescriptionCollection($description1, $description2);

        // When: I convert the collection to an array
        $array = $collection->toArray();

        // Then: The array should contain the same descriptions in order
        $this->assertCount(2, $array);
        $this->assertSame($description1, $array[0]);
        $this->assertSame($description2, $array[1]);
    }

    /**
     * User Story:
     * As a developer when I need to check if two collections are the same,
     * I want to compare two DescriptionCollections for equality
     * because I need to know if they contain the same descriptions in the same order.
     */
    public function testEqualsReturnsTrueForSameDescriptionsAndOrder(): void
    {
        // Given: Two DescriptionCollections with the same descriptions in the same order
        $description1 = $this->createOaiIdentifierDescription();
        $description2 = $this->createCustomDescription();
        $collection1 = new DescriptionCollection($description1, $description2);
        $collection2 = new DescriptionCollection($description1, $description2);

        // When: I compare the two collections
        $isEqual = $collection1->equals($collection2);

        // Then: They should be considered equal
        $this->assertTrue(
            $isEqual,
            'DescriptionCollection instances with the same descriptions in the same order should be equal.'
        );
    }

    /**
     * User Story:
     * As a developer when I need to check if two empty collections are the same,
     * I want to compare two empty DescriptionCollections
     * because empty collections should be considered equal.
     */
    public function testEqualsReturnsTrueForEmptyCollections(): void
    {
        // Given: Two empty DescriptionCollections
        $collection1 = new DescriptionCollection();
        $collection2 = new DescriptionCollection();

        // When: I compare the two collections
        $isEqual = $collection1->equals($collection2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual, 'Empty DescriptionCollection instances should be equal.');
    }

    /**
     * User Story:
     * As a developer when I need to check if collections with different descriptions are different,
     * I want to compare two DescriptionCollections with different descriptions
     * because they should not be considered equal.
     */
    public function testEqualsReturnsFalseForDifferentDescriptions(): void
    {
        // Given: Two DescriptionCollections with different descriptions
        $description1 = $this->createOaiIdentifierDescription();
        $description2 = $this->createCustomDescription();
        $collection1 = new DescriptionCollection($description1);
        $collection2 = new DescriptionCollection($description2);

        // When: I compare the two collections
        $isEqual = $collection1->equals($collection2);

        // Then: They should not be considered equal
        $this->assertFalse(
            $isEqual,
            'DescriptionCollection instances with different descriptions should not be equal.'
        );
    }

    /**
     * User Story:
     * As a developer when I need to check if collections with different counts are different,
     * I want to compare two DescriptionCollections with different counts
     * because they should not be considered equal.
     */
    public function testEqualsReturnsFalseForDifferentCounts(): void
    {
        // Given: Two DescriptionCollections with different counts
        $description = $this->createOaiIdentifierDescription();
        $collection1 = new DescriptionCollection($description);
        $collection2 = new DescriptionCollection($description, $this->createCustomDescription());

        // When: I compare the two collections
        $isEqual = $collection1->equals($collection2);

        // Then: They should not be considered equal
        $this->assertFalse(
            $isEqual,
            'DescriptionCollection instances with different counts should not be equal.'
        );
    }

    /**
     * User Story:
     * As a developer when I need to check if order matters,
     * I want to compare two DescriptionCollections with the same descriptions in different order
     * because order should affect equality.
     */
    public function testEqualsReturnsFalseForDifferentOrder(): void
    {
        // Given: Two DescriptionCollections with the same descriptions in different order
        $description1 = $this->createOaiIdentifierDescription();
        $description2 = $this->createCustomDescription();
        $collection1 = new DescriptionCollection($description1, $description2);
        $collection2 = new DescriptionCollection($description2, $description1);

        // When: I compare the two collections
        $isEqual = $collection1->equals($collection2);

        // Then: They should not be considered equal
        $this->assertFalse(
            $isEqual,
            'DescriptionCollection instances with different order should not be equal.'
        );
    }

    /**
     * User Story:
     * As a developer when I need to log or display the collection,
     * I want to get a string representation of the collection
     * because I need a human-readable format.
     */
    public function testToStringReturnsExpectedFormatWithMultipleDescriptions(): void
    {
        // Given: A DescriptionCollection with two descriptions
        $description1 = $this->createOaiIdentifierDescription();
        $description2 = $this->createCustomDescription();
        $collection = new DescriptionCollection($description1, $description2);

        // When: I convert the collection to a string
        $stringRepresentation = (string)$collection;

        // Then: It should return a string in the expected format
        $expected = sprintf(
            'DescriptionCollection(%s, %s)',
            (string)$description1,
            (string)$description2
        );
        $this->assertSame($expected, $stringRepresentation);
    }

    /**
     * User Story:
     * As a developer when I need to log or display empty collections,
     * I want to get a string representation of an empty collection
     * because I need to handle empty cases clearly.
     */
    public function testToStringReturnsExpectedFormatForEmptyCollection(): void
    {
        // Given: An empty DescriptionCollection
        $collection = new DescriptionCollection();

        // When: I convert the collection to a string
        $stringRepresentation = (string)$collection;

        // Then: It should return the expected empty format
        $expected = 'DescriptionCollection()';
        $this->assertSame($expected, $stringRepresentation);
    }

    /**
     * User Story:
     * As a developer when I need to ensure the collection cannot be changed after creation,
     * I want to ensure the collection is immutable
     * because immutability prevents accidental modification.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (see issue #8).
     */
    public function testCollectionIsImmutable(): void
    {
        // Given: A DescriptionCollection instance
        $collection = new DescriptionCollection($this->createOaiIdentifierDescription());

        // When: I use reflection to inspect its properties
        $reflection = new \ReflectionClass($collection);

        // Then: All properties should be private, ensuring immutability
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue(
                $property->isPrivate(),
                "Property {$property->getName()} should be private."
            );
        }
    }

    // --- Helper methods ---

    /**
     * Creates a Description instance with OAI Identifier format.
     * This helper method is used to create consistent test data.
     * @return Description A Description instance for OAI Identifier.
     */
    private function createOaiIdentifierDescription(): Description
    {
        $format = new DescriptionFormat(
            null,
            new MetadataNamespaceCollection(
                new MetadataNamespace(
                    new NamespacePrefix('oai-identifier'),
                    new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier')
                )
            ),
            new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier.xsd'),
            new MetadataRootTag('oai-identifier:oai-identifier')
        );

        $data = [
            'scheme' => 'oai',
            'repositoryIdentifier' => 'www.openarchives.org',
            'delimiter' => ':',
            'sampleIdentifier' => 'oai:www.openarchives.org:oaicat:1'
        ];

        return new Description($format, $data);
    }

    /**
     * Creates a Description instance with custom format.
     * This helper method is used to create different test data for comparison tests.
     * @return Description A Description instance for custom format.
     */
    private function createCustomDescription(): Description
    {
        $format = new DescriptionFormat(
            null,
            new MetadataNamespaceCollection(
                new MetadataNamespace(
                    new NamespacePrefix('custom'),
                    new AnyUri('http://example.org/custom')
                )
            ),
            new AnyUri('http://example.org/custom.xsd'),
            new MetadataRootTag('custom:root')
        );

        $data = [
            'customField' => 'customValue'
        ];

        return new Description($format, $data);
    }
}
