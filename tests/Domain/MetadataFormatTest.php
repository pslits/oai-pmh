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
use PHPUnit\Framework\TestCase;
use OaiPmh\Domain\MetadataFormat;
use OaiPmh\Domain\MetadataPrefix;
use OaiPmh\Domain\MetadataRootTag;
use OaiPmh\Domain\NamespacePrefix;
use OaiPmh\Domain\MetadataNamespace;
use OaiPmh\Domain\MetadataNamespaceCollection;

/**
 * Unit tests for the MetadataFormat value object in the OAI-PMH domain.
 *
 * Ensures correct instantiation, immutability, value equality, and string representation.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
class MetadataFormatTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to instantiate a MetadataFormat with valid prefix, namespaces, schema URL, and root tag
     * So that I can represent a qualified OAI-PMH metadata format.
     */
    public function testCanInstantiateWithValidArguments(): void
    {
        // Given: Valid dependencies
        $prefix = $this->givenMetadataPrefix('oai_dc');

        $namespaces = $this->givenMetadataNamespaceCollection(
            new MetadataNamespace(
                $this->givenNamespacePrefix('oai_dc'),
                $this->givenAnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
            )
        );
        $schemaUrl = $this->givenAnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
        $rootTag = $this->givenMetadataRootTag('oai_dc:dc');

        // When: I construct a MetadataFormat
        $format = new MetadataFormat($prefix, $namespaces, $schemaUrl, $rootTag);

        // Then: The object should be created without error
        $this->assertInstanceOf(MetadataFormat::class, $format);
    }

    /**
     * User Story:
     * As a developer,
     * I want to retrieve all properties from a MetadataFormat instance
     * So that I can use them in OAI-PMH responses or validation.
     */
    public function testGettersReturnExpectedValues(): void
    {
        // Given: A MetadataFormat instance with predefined values
        $prefix = $this->givenMetadataPrefix('oai_dc');

        // When: I create a MetadataFormat instance
        $namespaces = $this->givenMetadataNamespaceCollection(
            $this->givenMetadataNamespace('oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/')
        );
        $schemaUrl = $this->givenAnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
        $rootTag = $this->givenMetadataRootTag('oai_dc:dc');
        $format = new MetadataFormat($prefix, $namespaces, $schemaUrl, $rootTag);

        // Then: The getters should return the expected values
        $this->assertSame($prefix, $format->getPrefix());
        $this->assertSame($namespaces, $format->getNamespaces());
        $this->assertSame($schemaUrl, $format->getSchemaUrl());
        $this->assertSame($rootTag, $format->getRootTag());
    }

    /**
     * User Story:
     * As a developer,
     * I want MetadataFormat to behave as an immutable value object
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (issue #8).
     */
    public function testMetadataFormatIsImmutable(): void
    {
        // Given: A MetadataFormat instance
        $format = $this->givenMetadataFormat();

        // When: I use reflection to inspect its properties
        $reflection = new \ReflectionClass($format);

        // Then: All properties should be private
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }

    /**
     * User Story:
     * As a developer,
     * I want two MetadataFormat instances with the same values to be considered equal
     * So that value equality is supported.
     */
    public function testMetadataFormatEqualityByValue(): void
    {
        // Given: Two MetadataFormat instances with the same values
        $format1 = $this->givenMetadataFormat();
        $format2 = $this->givenMetadataFormat();

        // When: I check if they are equal
        $isEqual = $format1->equals($format2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want two MetadataFormat instances with different values to not be equal
     * So that I can distinguish between different formats.
     */
    public function testMetadataFormatNotEqualWhenDifferent(): void
    {
        // Given: Two MetadataFormat instances with different values
        $format1 = $this->givenMetadataFormat();
        $format2 = $this->givenMetadataFormatWithDifferentPrefix();

        // When: I check if they are equal
        $isEqual = $format1->equals($format2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to convert MetadataFormat to a string
     * So that I can log or display it in a human-readable format.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A MetadataFormat instance with predefined values
        $format = $this->givenMetadataFormat();

        // When: I convert it to a string
        $stringRepresentation = (string)$format;

        // Then: The string representation should match the expected format
        $expected = 'MetadataFormat(prefix: MetadataPrefix(prefix: oai_dc), namespaces: ' .
            'MetadataNamespaceCollection(namespaces: ' .
            'MetadataNamespace(prefix: NamespacePrefix(prefix: oai_dc), uri: AnyUri(uri: http://www.openarchives.org/OAI/2.0/oai_dc/)), ' .
            'MetadataNamespace(prefix: NamespacePrefix(prefix: oai_marc), uri: AnyUri(uri: http://www.openarchives.org/OAI/2.0/oai_marc/))), ' .
            'schemaUrl: AnyUri(uri: http://www.openarchives.org/OAI/2.0/oai_dc.xsd), ' .
            'rootTag: MetadataRootTag(rootTag: oai_dc:dc))';
        $this->assertSame($expected, $stringRepresentation);
    }

    // --- Helper methods ---

    /**
     * Creates a MetadataFormat with predefined values for testing purposes.
     *
     * @return MetadataFormat
     */
    private function givenMetadataFormat(): MetadataFormat
    {
        return new MetadataFormat(
            $this->givenMetadataPrefix('oai_dc'),
            $this->givenMetadataNamespaceCollection(
                $this->givenMetadataNamespace('oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/'),
                $this->givenMetadataNamespace('oai_marc', 'http://www.openarchives.org/OAI/2.0/oai_marc/')
            ),
            $this->givenAnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd'),
            $this->givenMetadataRootTag('oai_dc:dc')
        );
    }

    /**
     * Creates a MetadataFormat with a different prefix for testing purposes.
     *
     * @return MetadataFormat
     */
    private function givenMetadataFormatWithDifferentPrefix(): MetadataFormat
    {
        return new MetadataFormat(
            $this->givenMetadataPrefix('oai_marc'),
            $this->givenMetadataNamespaceCollection(
                $this->givenMetadataNamespace('oai_marc', 'http://www.openarchives.org/OAI/2.0/oai_marc/')
            ),
            $this->givenAnyUri('http://www.openarchives.org/OAI/2.0/oai_marc.xsd'),
            $this->givenMetadataRootTag('oai_marc:marc')
        );
    }

    /**
     * Creates a MetadataPrefix instance for testing purposes.
     *
     * @param string $prefix The prefix to use in the MetadataPrefix.
     * @return MetadataPrefix
     */
    private function givenMetadataPrefix(string $prefix): MetadataPrefix
    {
        return new MetadataPrefix($prefix);
    }

    /**
     * Creates a NamespacePrefix instance for testing purposes.
     *
     * @param string $prefix The prefix to use in the NamespacePrefix.
     * @return NamespacePrefix
     */
    private function givenNamespacePrefix(string $prefix): NamespacePrefix
    {
        return new NamespacePrefix($prefix);
    }

    /**
     * Creates a MetadataNamespace instance with the given prefix and URI for testing purposes.
     *
     * @param string $prefix The namespace prefix.
     * @param string $uri The namespace URI.
     * @return MetadataNamespace
     */
    private function givenMetadataNamespace(string $prefix, string $uri): MetadataNamespace
    {
        return new MetadataNamespace(
            $this->givenNamespacePrefix($prefix),
            $this->givenAnyUri($uri)
        );
    }

    /**
     * Creates a MetadataNamespaceCollection with the given MetadataNamespace instances.
     *
     * @param MetadataNamespace ...$namespaces The namespaces to include in the collection.
     * @return MetadataNamespaceCollection
     */
    private function givenMetadataNamespaceCollection(MetadataNamespace ...$namespaces): MetadataNamespaceCollection
    {
        return new MetadataNamespaceCollection(...$namespaces);
    }

    /**
     * Creates a AnyUri instance for testing purposes.
     *
     * @param string $uri The URI to use in the AnyUri.
     * @return AnyUri
     */
    private function givenAnyUri(string $uri): AnyUri
    {
        return new AnyUri($uri);
    }

    /**
     * Creates a MetadataRootTag instance for testing purposes.
     *
     * @param string $tag The root tag to use in the MetadataRootTag.
     * @return MetadataRootTag
     */
    private function givenMetadataRootTag(string $tag): MetadataRootTag
    {
        return new MetadataRootTag($tag);
    }
}
