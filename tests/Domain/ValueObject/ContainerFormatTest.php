<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// This file intentionally contains a test helper class and a test class for PHPUnit.

namespace OaiPmh\Tests\Domain\ValueObject;

use OaiPmh\Domain\ValueObject\ContainerFormat;
use OaiPmh\Domain\ValueObject\MetadataPrefix;
use OaiPmh\Domain\ValueObject\MetadataNamespaceCollection;
use OaiPmh\Domain\ValueObject\MetadataNamespace;
use OaiPmh\Domain\ValueObject\NamespacePrefix;
use OaiPmh\Domain\ValueObject\AnyUri;
use OaiPmh\Domain\ValueObject\MetadataRootTag;
use PHPUnit\Framework\TestCase;

/**
 * Test helper class for ExtensibleContainer.
 *
 * This class is used to test the abstract ExtensibleContainer class.
 */
class TestExtensibleContainer extends ContainerFormat
{
}

/**
 * Tests for the ExtensibleContainer class.
 *
 * This class contains unit tests for the ExtensibleContainer value object,
 * ensuring it behaves correctly as a value object in the OAI-PMH domain.
 */
class ExtensibleContainerTest extends TestCase
{
    /**
     * User story:
     * As a developer,
     * I want to create an ExtensibleContainer with a metadata prefix, namespaces, schema URL, and root tag
     * So that I can represent an extensible OAI-PMH container
     * with the necessary metadata for OAI-PMH responses.
     */
    public function testCanInstantiateWithPrefix(): void
    {
        // Given: Valid dependencies
        $prefix = new MetadataPrefix('oai_dc');
        $namespaces = new MetadataNamespaceCollection(
            new MetadataNamespace(
                new NamespacePrefix('oai_dc'),
                new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
            )
        );
        $schemaUrl = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
        $rootTag = new MetadataRootTag('oai_dc:dc');

        // When: I create an ExtensibleContainer instance
        $container = new TestExtensibleContainer($prefix, $namespaces, $schemaUrl, $rootTag);

        // Then: The object should be created without error
        $this->assertInstanceOf(ContainerFormat::class, $container);

        // And: The container should have the correct properties
        $this->assertEquals($prefix, $container->getPrefix());
        $this->assertEquals($namespaces, $container->getNamespaces());
        $this->assertEquals($schemaUrl, $container->getSchemaUrl());
        $this->assertEquals($rootTag, $container->getRootTag());
    }

    /**
     * User story:
     * As a developer,
     * I want to create an ExtensibleContainer without a metadata prefix
     * So that I can represent containers that do not require a prefix.
     */
    public function testCanInstantiateWithoutPrefix(): void
    {
        // Given: Valid dependencies without a prefix
        $namespaces = new MetadataNamespaceCollection(
            new MetadataNamespace(
                new NamespacePrefix('custom'),
                new AnyUri('http://example.org/custom')
            )
        );
        $schemaUrl = new AnyUri('http://example.org/schema.xsd');
        $rootTag = new MetadataRootTag('custom:root');

        // When: I create an ExtensibleContainer instance without a prefix
        $container = new TestExtensibleContainer(null, $namespaces, $schemaUrl, $rootTag);

        // Then: The object should be created without error
        // And: The container should have the correct properties
        $this->assertNull($container->getPrefix());
        $this->assertEquals($namespaces, $container->getNamespaces());
        $this->assertEquals($schemaUrl, $container->getSchemaUrl());
        $this->assertEquals($rootTag, $container->getRootTag());
    }

    /**
     * User story:
     * As a developer,
     * I want to compare two ExtensibleContainer instances for equality
     * So that I can determine if they represent the same metadata format.
     */
    public function testEqualsReturnsTrueForSameValues(): void
    {
        // Given: Two ExtensibleContainer instances with the same values
        $prefix = new MetadataPrefix('oai_dc');
        $namespaces = new MetadataNamespaceCollection(
            new MetadataNamespace(
                new NamespacePrefix('oai_dc'),
                new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
            )
        );
        $schemaUrl = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
        $rootTag = new MetadataRootTag('oai_dc:dc');

        $containerA = new TestExtensibleContainer($prefix, $namespaces, $schemaUrl, $rootTag);
        $containerB = new TestExtensibleContainer($prefix, $namespaces, $schemaUrl, $rootTag);

        // When: I compare the two containers for equality
        $isEqual = $containerA->equals($containerB);

        // Then: They should be considered equal
        $this->assertTrue($isEqual);
    }

    /**
     * User story:
     * As a developer,
     * I want to compare two ExtensibleContainer instances with different values
     * So that I can ensure they are not considered equal.
     */
    public function testEqualsReturnsFalseForDifferentValues(): void
    {
        // Given: Two ExtensibleContainer instances with different values
        $prefix = new MetadataPrefix('oai_dc');
        $namespaces = new MetadataNamespaceCollection(
            new MetadataNamespace(
                new NamespacePrefix('oai_dc'),
                new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
            )
        );
        $schemaUrl = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
        $rootTag = new MetadataRootTag('oai_dc:dc');

        $containerA = new TestExtensibleContainer($prefix, $namespaces, $schemaUrl, $rootTag);
        $containerB = new TestExtensibleContainer(null, $namespaces, $schemaUrl, $rootTag);

        // When: I compare the two containers for equality
        $isEqual = $containerA->equals($containerB);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    /**
     * User story:
     * As a developer,
     * I want to convert an ExtensibleContainer to a string
     * So that I can log or display it in a human-readable format.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: An ExtensibleContainer instance with predefined values
        $prefix = new MetadataPrefix('oai_dc');
        $namespaces = new MetadataNamespaceCollection(
            new MetadataNamespace(
                new NamespacePrefix('oai_dc'),
                new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
            ),
            new MetadataNamespace(
                new NamespacePrefix('oai_marc'),
                new AnyUri('http://www.openarchives.org/OAI/2.0/oai_marc/')
            )
        );
        $schemaUrl = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
        $rootTag = new MetadataRootTag('oai_dc:dc');

        // When: I create an ExtensibleContainer instance
        $container = new TestExtensibleContainer($prefix, $namespaces, $schemaUrl, $rootTag);

        // Then: The string representation should match the expected format
        $expected = sprintf(
            'TestExtensibleContainer(prefix: %s, namespaces: %s, schemaUrl: %s, rootTag: %s)',
            (string)$prefix,
            (string)$namespaces,
            (string)$schemaUrl,
            (string)$rootTag
        );
        $this->assertSame($expected, (string)$container);
    }
}
