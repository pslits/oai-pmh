<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain;

use ReflectionClass;
use ReflectionException;
use OaiPmh\Domain\AnyUri;
use PHPUnit\Framework\TestCase;
use OaiPmh\Domain\NamespacePrefix;
use OaiPmh\Domain\MetadataNamespace;

/**
 * Tests for the MetadataNamespace class.
 *
 * This class contains unit tests for the MetadataNamespace value object,
 * ensuring it behaves correctly as a value object in the OAI-PMH domain.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
class MetadataNamespaceTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to instantiate a MetadataNamespace with valid prefix and URI objects
     * So that I can represent a qualified XML namespace.
     */
    public function testCanInstantiateWithValidPrefixAndUri(): void
    {
        // Given: A NamespacePrefix and an AnyUri
        $prefix = $this->givenNamespacePrefix('oai_dc');
        $anyaUri = $this->givenAnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/');

        // When: I construct a MetadataNamespace
        $namespace = new MetadataNamespace($prefix, $anyaUri);

        // Then: The object should be created without error
        $this->assertInstanceOf(MetadataNamespace::class, $namespace);
    }

    /**
     * User Story:
     * As a developer,
     * I want to retrieve the namespace prefix and URI from a MetadataNamespace instance
     * So that I can use them in XML serialization or display.
     */
    public function testGetPrefixAndUri(): void
    {
        // Given: A MetadataNamespace with a specific prefix and URI
        $expectedPrefix = "oai_dc";
        $expectedURI = "http://www.openarchives.org/OAI/2.0/oai_dc/";
        $namespacePrefix = $this->givenNamespacePrefix($expectedPrefix);
        $anyUri = $this->givenAnyUri($expectedURI);
        $namespace = new MetadataNamespace($namespacePrefix, $anyUri);

        // When: I retrieve the prefix and URI
        $actualPrefix = $namespace->getPrefix()->getValue();
        $actualUri = $namespace->getUri()->getValue();

        // Then: It should return the expected prefix and URI
        $this->assertSame($expectedPrefix, $actualPrefix, 'The prefix should match the expected value.');
        $this->assertSame($expectedURI, $actualUri, 'The URI should match the expected value.');
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that the MetadataNamespace behaves as an immutable value object
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutabbility in 8.0 and 8.2 are different. When using 8.2 this needs to be improved (issue #8).
     */
    public function testMetadataNamespaceIsImmutable(): void
    {
        // Given: A constructed MetadataNamespace
        $namespace = $this->givenMetadataNamespace('oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');

        // When: I attempt to access or change private properties via reflection or dynamic means
        $reflectionClass = new ReflectionClass(MetadataNamespace::class);
        $prefixProperty = $reflectionClass->getProperty('prefix');
        $uriProperty = $reflectionClass->getProperty('uri');

        // Then: It should not allow state modification or expose setters
        $this->assertTrue($prefixProperty->isPrivate(), 'The namespacePrefix property should be private.');
        $this->assertTrue($uriProperty->isPrivate(), 'The uri property should be private.');

        // Attempting to set values should not work
        $this->expectException(ReflectionException::class);
        $prefixProperty->setValue($namespace, new MetadataNamespace(
            $this->givenNamespacePrefix('new_prefix'),
            $this->givenAnyUri('http://example.com/new_uri/')
        ));
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that MetadataNamespace instances are compared by value
     * So that two instances with the same prefix and URI are considered equal.
     */
    public function testMetadataNamespaceEqualityByValue(): void
    {
        // Given: Two MetadataNamespace instances with the same prefix and URI
        $namespace1 = $this->givenMetadataNamespace('oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
        $namespace2 = $this->givenMetadataNamespace('oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');

        // Then: They should be considered equal
        $this->assertTrue(
            $namespace1->equals($namespace2),
            'MetadataNamespace instances with the same prefix and URI should be equal.'
        );
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that MetadataNamespace instances with different prefixes or URIs are not equal
     * So that I can distinguish between different metadata namespaces.
     */
    public function testMetadataNamespaceNotEqualWhenDifferent(): void
    {
        // Given: Two MetadataNamespace instances with different prefixes or URIs
        $namespace1 = $this->givenMetadataNamespace('oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
        $namespace2 = $this->givenMetadataNamespace('oai_marc', 'http://www.openarchives.org/OAI/2.0/oai_marc/');

        // Then: They should not be considered equal
        $this->assertFalse(
            $namespace1->equals($namespace2),
            'MetadataNamespace instances with different prefixes or URIs should not be equal.'
        );
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that the MetadataNamespace can be converted to a string
     * So that it can be easily logged or displayed in a human-readable format.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A MetadataNamespace
        $namespace = $this->givenMetadataNamespace('oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');

        // When: I convert it to a string
        $actual = (string)$namespace;

        // Then: It should return a string in the expected format
        $expected = 'MetadataNamespace(prefix: NamespacePrefix(prefix: oai_dc), uri: AnyUri(uri: http://www.openarchives.org/OAI/2.0/oai_dc/))';
        $this->assertSame($expected, $actual);
    }

    /**
     * Helper method to create a MetadataNamespace instance with given prefix and URI.
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

        // return new MetadataNamespace(new NamespacePrefix($prefix), new AnyUri($uri));
    }

    /**
     * Creates a mock or real NamespacePrefix for testing purposes.
     *
     * @param string $prefix The prefix to use in the mock.
     * @return NamespacePrefix
     */
    private function givenNamespacePrefix(string $prefix): NamespacePrefix
    {
        return new NamespacePrefix($prefix);
    }

    /**
     * Creates a mock or real AnyUri for testing purposes.
     *
     * @param string $uri The URI to use in the mock.
     * @return AnyUri
     */
    private function givenAnyUri(string $uri): AnyUri
    {
        return new AnyUri($uri);
    }
}
