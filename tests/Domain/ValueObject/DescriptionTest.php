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
use OaiPmh\Domain\ValueObject\DescriptionFormat;
use OaiPmh\Domain\ValueObject\MetadataNamespaceCollection;
use OaiPmh\Domain\ValueObject\MetadataNamespace;
use OaiPmh\Domain\ValueObject\NamespacePrefix;
use OaiPmh\Domain\ValueObject\AnyUri;
use OaiPmh\Domain\ValueObject\MetadataRootTag;
use PHPUnit\Framework\TestCase;

class DescriptionTest extends TestCase
{
    /**
     * Creates a DescriptionFormat instance for OAI Identifier.
     * This method is used to create a DescriptionFormat instance
     * that represents the OAI Identifier metadata format.
     * @return DescriptionFormat The created DescriptionFormat instance.
     */
    private function createOaiIdentifierDescriptionFormat(): DescriptionFormat
    {
        $namespaces = new MetadataNamespaceCollection(
            new MetadataNamespace(
                new NamespacePrefix('oai-identifier'),
                new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier')
            )
        );
        $schemaUrl = new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier.xsd');
        $rootTag = new MetadataRootTag('oai-identifier:oai-identifier');
        return new DescriptionFormat(null, $namespaces, $schemaUrl, $rootTag);
    }

    /**
     * Returns a sample OAI Identifier data array.
     * This method provides a sample data structure that can be used
     * to test the Description object with OAI Identifier metadata.
     * @return array<string, mixed> The sample OAI Identifier data.
     */
    private function getOaiIdentifierData(): array
    {
        return [
            'scheme' => 'oai',
            'repositoryIdentifier' => 'www.openarchives.org',
            'delimiter' => ':',
            'sampleIdentifier' => 'oai:www.openarchives.org:oaicat:1'
        ];
    }

    /**
     * User Story:
     * As a developer,
     * I want to instantiate a Description object with valid description format and data
     * So that I can represent a repository-level description in OAI-PMH.
     */
    public function testCanInstantiateDescription(): void
    {
        // Given: Valid dependencies for Description
        $descriptionFormat = $this->createOaiIdentifierDescriptionFormat();
        $data = $this->getOaiIdentifierData();

        // When: I create a Description instance
        $description = new Description($descriptionFormat, $data);

        // Then: The instance should be created successfully
        $this->assertInstanceOf(Description::class, $description);
        // And: The properties should be set correctly
        $this->assertSame($descriptionFormat, $description->getDescriptionFormat());
        $this->assertSame($data, $description->getData());
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that Description is immutable
     * So that its properties cannot be changed after instantiation.
     */
    public function testDescriptionIsImmutable(): void
    {
        // Given: A valid Description instance
        $descriptionFormat = $this->createOaiIdentifierDescriptionFormat();
        $data = $this->getOaiIdentifierData();

        // When: I use reflection to inspect its properties
        $reflection = new \ReflectionClass(Description::class);

        // Then: All properties should be protected or private
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue(
                $property->isPrivate() || $property->isProtected(),
                "Property {$property->getName()} should be private or protected."
            );
        }
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two Description objects for equality
     * So that I can ensure they represent the same description format and data.
     */
    public function testEqualsReturnsTrueForSameValues(): void
    {
        // Given: Two Description instances with the same description format and data
        $descriptionFormat = $this->createOaiIdentifierDescriptionFormat();
        $data = $this->getOaiIdentifierData();

        $description1 = new Description($descriptionFormat, $data);
        $description2 = new Description($descriptionFormat, $data);

        // When: I compare them for equality
        $isEqual = $description1->equals($description2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two Description objects with different description formats
     * So that I can ensure they are not considered equal.
     */
    public function testEqualsReturnsFalseForDifferentDescriptionFormat(): void
    {
        $descriptionFormat1 = $this->createOaiIdentifierDescriptionFormat();
        $descriptionFormat2 = new DescriptionFormat(
            null,
            new MetadataNamespaceCollection(
                new MetadataNamespace(
                    new NamespacePrefix('custom'),
                    new AnyUri('http://example.org/custom/')
                )
            ),
            new AnyUri('http://example.org/custom.xsd'),
            new MetadataRootTag('custom:root')
        );
        $data = $this->getOaiIdentifierData();

        $description1 = new Description($descriptionFormat1, $data);
        $description2 = new Description($descriptionFormat2, $data);

        // When: I compare them for equality
        $isEqual = $description1->equals($description2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two Description objects with different data
     * So that I can ensure they are not considered equal.
     */
    public function testEqualsReturnsFalseForDifferentData(): void
    {
        // Given: Two Description instances with the same description format but different data
        $descriptionFormat = $this->createOaiIdentifierDescriptionFormat();
        $description1 = new Description($descriptionFormat, $this->getOaiIdentifierData());
        $description2 = new Description($descriptionFormat, ['scheme' => 'custom']);

        // When: I compare them for equality
        $isEqual = $description1->equals($description2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to convert Description to a string
     * So that I can log or display it in a human-readable format.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A Description instance with a specific description format and data
        $descriptionFormat = $this->createOaiIdentifierDescriptionFormat();
        $data = $this->getOaiIdentifierData();
        $description = new Description($descriptionFormat, $data);

        $expected = sprintf(
            'Description(descriptionFormat: %s, data: %s)',
            (string)$descriptionFormat,
            json_encode($data)
        );

        // Then: The string representation should match the expected format
        $descriptionString = (string)$description;
        $this->assertSame($expected, $descriptionString);
    }
}
