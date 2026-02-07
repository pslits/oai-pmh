<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use OaiPmh\Domain\ValueObject\DescriptionFormat;
use OaiPmh\Domain\ValueObject\MetadataNamespaceCollection;
use OaiPmh\Domain\ValueObject\MetadataNamespace;
use OaiPmh\Domain\ValueObject\NamespacePrefix;
use OaiPmh\Domain\ValueObject\AnyUri;
use OaiPmh\Domain\ValueObject\MetadataRootTag;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the DescriptionFormat class.
 *
 * This class contains unit tests for the DescriptionFormat value object,
 * ensuring it behaves correctly as a value object in the OAI-PMH domain.
 */
class DescriptionFormatTest extends TestCase
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
     * User Story:
     * As a developer,
     * I want to create an OAI Identifier DescriptionFormat
     * So that I can represent the OAI Identifier metadata format in OAI-PMH.
     */
    public function testCanInstantiateOaiIdentifierDescriptionFormat(): void
    {
        // Given: Valid dependencies for OAI Identifier DescriptionFormat
        // When: I create a new DescriptionFormat instance
        $format = $this->createOaiIdentifierDescriptionFormat();

        // Then: The instance should be created successfully
        $this->assertInstanceOf(DescriptionFormat::class, $format);

        // And: The properties should be set correctly
        $this->assertNull($format->getPrefix());
        $this->assertInstanceOf(MetadataNamespaceCollection::class, $format->getNamespaces());
        $this->assertInstanceOf(AnyUri::class, $format->getSchemaUrl());
        $this->assertInstanceOf(MetadataRootTag::class, $format->getRootTag());
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two DescriptionFormat instances for equality
     * So that I can ensure they represent the same OAI-PMH description format.
     */
    public function testEqualsReturnsTrueForSameValues(): void
    {
        // Given: Two DescriptionFormat instances with the same properties
        $discriptionFormat1 = $this->createOaiIdentifierDescriptionFormat();
        $discriptionFormat2 = $this->createOaiIdentifierDescriptionFormat();

        // When: I compare them for equality
        $isEqual = $discriptionFormat1->equals($discriptionFormat2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two DescriptionFormat instances with different properties
     * So that I can ensure they are not considered equal.
     */
    public function testEqualsReturnsFalseForDifferentNamespaces(): void
    {
        // Given: Two DescriptionFormat instances with different namespaces
        $namespaces1 = new MetadataNamespaceCollection(
            new MetadataNamespace(
                new NamespacePrefix('oai-identifier'),
                new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier')
            )
        );
        $namespaces2 = new MetadataNamespaceCollection(
            new MetadataNamespace(
                new NamespacePrefix('custom'),
                new AnyUri('http://example.org/custom')
            )
        );
        $schemaUrl = new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier.xsd');
        $rootTag = new MetadataRootTag('oai-identifier:oai-identifier');

        $descriptionFormat1 = new DescriptionFormat(null, $namespaces1, $schemaUrl, $rootTag);
        $descriptionFormat2 = new DescriptionFormat(null, $namespaces2, $schemaUrl, $rootTag);

        // When: I compare them for equality
        $isEqual = $descriptionFormat1->equals($descriptionFormat2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two DescriptionFormat instances with different schema URLs
     * So that I can ensure they are not considered equal.
     */
    public function testEqualsReturnsFalseForDifferentSchemaUrl(): void
    {
        // Given: Two DescriptionFormat instances with different schema URLs
        $namespaces = new MetadataNamespaceCollection(
            new MetadataNamespace(
                new NamespacePrefix('oai-identifier'),
                new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier')
            )
        );
        $schemaUrl1 = new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier.xsd');
        $schemaUrl2 = new AnyUri('http://example.org/other.xsd');
        $rootTag = new MetadataRootTag('oai-identifier:oai-identifier');

        $descriptionFormat1 = new DescriptionFormat(null, $namespaces, $schemaUrl1, $rootTag);
        $descriptionFormat2 = new DescriptionFormat(null, $namespaces, $schemaUrl2, $rootTag);

        // When: I compare them for equality
        $isEqual = $descriptionFormat1->equals($descriptionFormat2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two DescriptionFormat instances with different root tags
     * So that I can ensure they are not considered equal.
     */
    public function testEqualsReturnsFalseForDifferentRootTag(): void
    {
        // Given: Two DescriptionFormat instances with different root tags
        $namespaces = new MetadataNamespaceCollection(
            new MetadataNamespace(
                new NamespacePrefix('oai-identifier'),
                new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier')
            )
        );
        $schemaUrl = new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier.xsd');
        $rootTag1 = new MetadataRootTag('oai-identifier:oai-identifier');
        $rootTag2 = new MetadataRootTag('custom:root');

        $descriptionFormat1 = new DescriptionFormat(null, $namespaces, $schemaUrl, $rootTag1);
        $descriptionFormat2 = new DescriptionFormat(null, $namespaces, $schemaUrl, $rootTag2);

        // When: I compare them for equality
        $isEqual = $descriptionFormat1->equals($descriptionFormat2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to convert DescriptionFormat to a string
     * So that I can log or display it in a human-readable format.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A DescriptionFormat instance with predefined values
        $format = $this->createOaiIdentifierDescriptionFormat();

        $expected = sprintf(
            'DescriptionFormat(prefix: null, namespaces: %s, schemaUrl: %s, rootTag: %s)',
            (string)$format->getNamespaces(),
            (string)$format->getSchemaUrl(),
            (string)$format->getRootTag()
        );

        // When: I convert it to a string
        $formatString = (string)$format;

        // Then: The string representation should match the expected format
        $this->assertSame($expected, $formatString);
    }
}
