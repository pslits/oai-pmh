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
    private MetadataNamespace $metadataNamespace;

    protected function setUp(): void
    {
        $prefix = new NamespacePrefix('oai_dc');
        $uri = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/');
        $this->metadataNamespace = new MetadataNamespace($prefix, $uri);
    }

    /**
     * User Story:
     * As a developer,
     * I want to instantiate a MetadataNamespace with valid prefix and URI objects
     * So that I can represent a qualified XML namespace.
     */
    public function testCanInstantiateWithValidPrefixAndUri(): void
    {
        // Given: A NamespacePrefix and an AnyUri
        $prefix = new NamespacePrefix('oai_dc');
        $uri = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/');

        // When: I construct a MetadataNamespace
        $metadataNamespace = new MetadataNamespace($prefix, $uri);

        // Then: The object should be created without error and retain the input values
        $this->assertInstanceOf(MetadataNamespace::class, $metadataNamespace);
        $this->assertSame($prefix, $metadataNamespace->getPrefix());
        $this->assertSame($uri, $metadataNamespace->getUri());
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that instantiating MetadataNamespace with invalid prefix or URI throws an error
     * So that I can catch configuration errors early.
     */
    public function testCannotInstantiateWithInvalidPrefixOrUri(): void
    {
        // Given: Invalid types for NamespacePrefix and AnyUri
        $namespacePrefix = new NamespacePrefix('invalid_prefix');
        $anyUri = new AnyUri('invalid-uri');

        // When: I attempt to create a MetadataNamespace with invalid types
        // Then: It should throw an InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        new MetadataNamespace(new NamespacePrefix(123), new AnyUri('invalid-uri'));
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
        $expectedPrefix = new NamespacePrefix('oai_dc');
        $expectedUri = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/');
        $namespace = new MetadataNamespace($expectedPrefix, $expectedUri);

        // When: I call getPrefix() and getUri()
        $actualPrefix = $namespace->getPrefix();
        $actualUri = $namespace->getUri();

        // Then: It should return the expected prefix and URI
        $this->assertSame($expectedPrefix, $actualPrefix);
        $this->assertSame($expectedUri, $actualUri);
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that the MetadataNamespace behaves as an immutable value object
     * So that its internal state cannot be changed after construction.
     */
    public function testMetadataNamespaceIsImmutable(): void
    {
        // Given: A constructed MetadataNamespace
        $namespace = new MetadataNamespace(
            new NamespacePrefix('oai_dc'),
            new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
        );

        // When: I attempt to access or change private properties via reflection or dynamic means
        $reflectionClass = new ReflectionClass(MetadataNamespace::class);
        $prefixProperty = $reflectionClass->getProperty('namespacePrefix');
        $uriProperty = $reflectionClass->getProperty('uri');

        // Then: It should not allow state modification or expose setters
        $this->assertTrue($prefixProperty->isPrivate(), 'The namespacePrefix property should be private.');
        $this->assertTrue($uriProperty->isPrivate(), 'The uri property should be private.');

        // Attempting to set values should not work
        $this->expectException(ReflectionException::class);
        $prefixProperty->setValue($namespace, new NamespacePrefix('new_prefix'));
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
        $prefix = new NamespacePrefix('oai_dc');
        $uri = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/');
        $namespace1 = new MetadataNamespace($prefix, $uri);
        $namespace2 = new MetadataNamespace($prefix, $uri);

        // When: I compare them for equality
        $areEqual =
            ($namespace1->getPrefix() === $namespace2->getPrefix()) &&
            ($namespace1->getUri() === $namespace2->getUri());

        // Then: They should be considered equal
        $this->assertTrue($areEqual, 'MetadataNamespace instances with the same prefix and URI should be equal.');
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
        $prefix1 = new NamespacePrefix('oai_dc');
        $uri1 = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/');
        $namespace1 = new MetadataNamespace($prefix1, $uri1);

        $prefix2 = new NamespacePrefix('oai_marc');
        $uri2 = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_marc/');
        $namespace2 = new MetadataNamespace($prefix2, $uri2);

        // Then: They should not be considered equal
        $this->assertNotEquals(
            json_encode($namespace1),
            json_encode($namespace2),
            'MetadataNamespace instances with different prefixes or URIs should not be equal.'
        );
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that MetadataNamespace does not expose setters or unintended public methods
     * So that the value object remains pure and controlled.
     */
    public function testMetadataNamespaceDoesNotExposeSetters(): void
    {
        // Given: A MetadataNamespace instance

        // When: I inspect its public methods

        // Then: It should not have any set* methods
        $this->assertFalse($this->methodExists('setPrefix'), 'MetadataNamespace should not have setPrefix() method');
        $this->assertFalse($this->methodExists('setUri'), 'MetadataNamespace should not have setUri() method');
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that MetadataNamespace does not expose private properties via public means
     * So that its internal state remains encapsulated.
     */
    public function testMetadataNamespacePropertiesAreNotPubliclyAccessible(): void
    {
        // Given: A MetadataNamespace instance

        // When: I check for public properties

        // Then: It should not have public properties like namespacePrefix or uri
        $this->assertFalse(
            $this->propertyExists('namespacePrefix'),
            'MetadataNamespace should not have public property namespacePrefix'
        );
        $this->assertFalse($this->propertyExists('uri'), 'MetadataNamespace should not have public property uri');
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that MetadataNamespace implements the MetadataNamespaceInterface
     * So that it adheres to the expected contract for metadata namespaces.
     */
    public function testImplementsMetadataNamespaceInterface(): void
    {
        // Given: A MetadataNamespace instance

        // When: I check if it implements MetadataNamespaceInterface
        $this->assertInstanceOf(
            'OaiPmh\Domain\MetadataNamespaceInterface',
            $this->metadataNamespace,
            'MetadataNamespace should implement MetadataNamespaceInterface'
        );
    }

    /**
     * Checks if a method exists in the MetadataNamespace class.
     * @param string $methodName The name of the method to check.
     * @return bool True if the method exists, false otherwise.
     */
    private function methodExists(string $methodName): bool
    {
        return method_exists($this->metadataNamespace, $methodName);
    }

    /**
     * Checks if a property exists in the MetadataNamespace class.
     * @param string $propertyName The name of the property to check.
     * @return bool True if the property exists, false otherwise.
     */
    private function propertyExists(string $propertyName): bool
    {
        $object = new MetadataNamespace(new NamespacePrefix('test'), new AnyUri('http://example.com'));
        $publicProperties = array_keys(get_object_vars($object));
        return in_array($propertyName, $publicProperties, true);
    }
}
