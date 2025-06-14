<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use OaiPmh\Domain\ValueObject\AnyUri;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the AnyUri class.
 *
 * This class contains unit tests for the AnyUri value object,
 * ensuring it behaves correctly as a value object in the OAI-PMH domain.
 */
class AnyUriTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create an AnyUri with a valid URI
     * So that it can be used in XML serialization for OAI-PMH.
     */
    public function testCanInstantiateWithValidUri(): void
    {
        // Given: A valid URI
        $uri = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';

        // When: I create an AnyUri instance
        $anyUri = new AnyUri($uri);

        // Then: It should be created successfully
        $this->assertSame($uri, $anyUri->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want to create an AnyUri with a Unicode URI
     * So that it can handle internationalized URIs.
     */
    public function testCanInstantiateWithUnicodeUri(): void
    {
        // Given: A valid Unicode URI
        $uri = 'http://example.com/路径/文件.xml';

        // When: I create an AnyUri instance
        $anyUri = new AnyUri($uri);

        // Then: It should be created successfully
        $this->assertSame($uri, $anyUri->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want AnyUri to throw an exception for an invalid URI
     * So that only valid URIs are accepted.
     */
    public function testThrowsExceptionForInvalidUri(): void
    {
        /**
         * TODO: Not possible to test invalid URI in this context (issue #7).
         */
        $this->markTestSkipped('Not possible to test invalid URI in this context');

        // $this->expectException(InvalidArgumentException::class);
        // $uri = new AnyUri('http://example.com/\x01');
        // print_r($uri->getValue()); // This line is just to trigger the exception
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two AnyUri instances by value
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        // Given: Two AnyUri instances with the same URI
        $uri = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
        $anyUri1 = new AnyUri($uri);
        $anyUri2 = new AnyUri($uri);

        // When: I check if they are equal
        $isEqual = $anyUri1->equals($anyUri2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual);
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        // Given: Two AnyUri instances with different URIs
        $anyUri1 = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
        $anyUri2 = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_marc.xsd');

        // When: I check if they are equal
        $isEqual = $anyUri1->equals($anyUri2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of AnyUri
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A valid URI
        $uri = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
        $anyUri = new AnyUri($uri);

        // When: I convert it to a string
        $stringRepresentation = (string)$anyUri;

        // Then: The string representation should match the expected format
        $expected = "AnyUri(uri: $uri)";
        $this->assertSame($expected, $stringRepresentation);
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that the AnyUri behaves as an immutable value object
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutabbility in 8.0 and 8.2 are different. When using 8.2 this needs to be improved (issue #8).
     */
    public function testAnyUriIsImmutable(): void
    {
        // Given: A constructed AnyUri
        $anyUri = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');

        // When: I attempt to access or change private properties via reflection or dynamic means
        $reflectionClass = new \ReflectionClass(AnyUri::class);
        $uriProperty = $reflectionClass->getProperty('uri');

        // Then: It should not allow state modification or expose setters
        $this->assertTrue($uriProperty->isPrivate(), 'The uri property should be private.');

        // Attempting to set values should not work
        $this->expectException(\ReflectionException::class);
        $uriProperty->setValue($anyUri, 'http://example.com/new_uri/');
    }
}
