<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use InvalidArgumentException;
use OaiPmh\Domain\ValueObject\BaseURL;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for the BaseURL value object in the OAI-PMH domain.
 *
 * Ensures correct instantiation, validation, immutability, value equality, and string representation.
 */
class BaseURLTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create a BaseURL with a valid HTTP URL
     * So that it can be used in OAI-PMH Identify responses.
     */
    public function testCanInstantiateWithValidHttpUrl(): void
    {
        // Given: A valid HTTP URL
        $url = 'http://example.org/oai';

        // When: I create a BaseURL instance
        $baseUrl = new BaseURL($url);

        // Then: The object should be created without error
        $this->assertInstanceOf(BaseURL::class, $baseUrl);
        $this->assertSame($url, $baseUrl->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want to create a BaseURL with a valid HTTPS URL
     * So that it can be used for secure OAI-PMH repositories.
     */
    public function testCanInstantiateWithValidHttpsUrl(): void
    {
        // Given: A valid HTTPS URL
        $url = 'https://example.org/oai-pmh';

        // When: I create a BaseURL instance
        $baseUrl = new BaseURL($url);

        // Then: The object should be created without error
        $this->assertInstanceOf(BaseURL::class, $baseUrl);
        $this->assertSame($url, $baseUrl->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want BaseURL to reject empty strings
     * So that invalid repository URLs are prevented.
     */
    public function testThrowsExceptionForEmptyString(): void
    {
        // Given: An empty string
        $url = '';

        // Then: It should throw an exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BaseURL cannot be empty.');

        // When: I try to create a BaseURL instance
        new BaseURL($url);
    }

    /**
     * User Story:
     * As a developer,
     * I want BaseURL to reject invalid URL formats
     * So that only valid URLs are accepted.
     */
    public function testThrowsExceptionForInvalidUrlFormat(): void
    {
        // Given: An invalid URL format
        $url = 'not-a-valid-url';

        // Then: It should throw an exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL format:');

        // When: I try to create a BaseURL instance
        new BaseURL($url);
    }

    /**
     * User Story:
     * As a developer,
     * I want BaseURL to reject non-HTTP(S) protocols
     * So that only web-accessible repositories are allowed.
     */
    public function testThrowsExceptionForNonHttpProtocol(): void
    {
        // Given: A URL with FTP protocol
        $url = 'ftp://example.org/oai';

        // Then: It should throw an exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BaseURL must use HTTP or HTTPS protocol.');

        // When: I try to create a BaseURL instance
        new BaseURL($url);
    }

    /**
     * User Story:
     * As a developer,
     * I want BaseURL to reject file:// URLs
     * So that only network-accessible repositories are allowed.
     */
    public function testThrowsExceptionForFileProtocol(): void
    {
        // Given: A URL with file protocol
        $url = 'file:///path/to/repository';

        // Then: It should throw an exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BaseURL must use HTTP or HTTPS protocol.');

        // When: I try to create a BaseURL instance
        new BaseURL($url);
    }

    /**
     * User Story:
     * As a developer,
     * I want BaseURL to accept URLs with query parameters
     * So that repositories with query strings are supported.
     */
    public function testCanInstantiateWithQueryParameters(): void
    {
        // Given: A valid URL with query parameters
        $url = 'https://example.org/oai?verb=Identify';

        // When: I create a BaseURL instance
        $baseUrl = new BaseURL($url);

        // Then: The object should be created without error
        $this->assertInstanceOf(BaseURL::class, $baseUrl);
        $this->assertSame($url, $baseUrl->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want BaseURL to accept URLs with ports
     * So that non-standard port configurations are supported.
     */
    public function testCanInstantiateWithCustomPort(): void
    {
        // Given: A valid URL with a custom port
        $url = 'http://example.org:8080/oai';

        // When: I create a BaseURL instance
        $baseUrl = new BaseURL($url);

        // Then: The object should be created without error
        $this->assertInstanceOf(BaseURL::class, $baseUrl);
        $this->assertSame($url, $baseUrl->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want two BaseURL instances with the same value to be considered equal
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        // Given: Two BaseURL instances with the same value
        $url = 'https://example.org/oai';
        $baseUrl1 = new BaseURL($url);
        $baseUrl2 = new BaseURL($url);

        // When: I check if they are equal
        $isEqual = $baseUrl1->equals($baseUrl2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual, 'BaseURL instances with the same value should be equal.');
    }

    /**
     * User Story:
     * As a developer,
     * I want two BaseURL instances with different values to not be equal
     * So that I can distinguish between different repositories.
     */
    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        // Given: Two BaseURL instances with different values
        $baseUrl1 = new BaseURL('http://example.org/oai');
        $baseUrl2 = new BaseURL('https://other.org/oai-pmh');

        // When: I check if they are equal
        $isEqual = $baseUrl1->equals($baseUrl2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual, 'BaseURL instances with different values should not be equal.');
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of BaseURL
     * So that I can log or display it for debugging.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A BaseURL instance
        $url = 'https://example.org/oai';
        $baseUrl = new BaseURL($url);

        // When: I convert it to a string
        $stringRepresentation = (string)$baseUrl;

        // Then: The string representation should match the expected format
        $expected = "BaseURL(url: $url)";
        $this->assertSame(
            $expected,
            $stringRepresentation,
            'String representation of BaseURL should match expected format.'
        );
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that BaseURL is immutable
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (see issue #8).
     */
    public function testIsImmutable(): void
    {
        // Given: A BaseURL instance
        $baseUrl = new BaseURL('https://example.org/oai');

        // When: I use reflection to inspect its properties
        $reflection = new ReflectionClass($baseUrl);

        // Then: All properties should be private
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }

    /**
     * User Story:
     * As a developer,
     * I want BaseURL to handle URLs with paths
     * So that repositories at specific paths are supported.
     */
    public function testCanInstantiateWithPath(): void
    {
        // Given: A valid URL with a path
        $url = 'https://example.org/repository/oai-pmh/endpoint';

        // When: I create a BaseURL instance
        $baseUrl = new BaseURL($url);

        // Then: The object should be created without error
        $this->assertInstanceOf(BaseURL::class, $baseUrl);
        $this->assertSame($url, $baseUrl->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want BaseURL to handle URLs with trailing slashes
     * So that different URL conventions are supported.
     */
    public function testCanInstantiateWithTrailingSlash(): void
    {
        // Given: A valid URL with a trailing slash
        $url = 'https://example.org/oai/';

        // When: I create a BaseURL instance
        $baseUrl = new BaseURL($url);

        // Then: The object should be created without error
        $this->assertInstanceOf(BaseURL::class, $baseUrl);
        $this->assertSame($url, $baseUrl->getValue());
    }
}
