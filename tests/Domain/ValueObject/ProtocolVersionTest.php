<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use OaiPmh\Domain\ValueObject\ProtocolVersion;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * Tests for the ProtocolVersion class.
 *
 * This class contains unit tests for the ProtocolVersion value object,
 * ensuring it behaves correctly as a value object in the OAI-PMH domain.
 */
class ProtocolVersionTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create a ProtocolVersion with the allowed version
     * So that it can be used in OAI-PMH responses.
     */
    public function testCanInstantiateWithAllowedVersion(): void
    {
        // Given: A valid OAI-PMH protocol version
        $version = '2.0';

        // When: I create a ProtocolVersion instance
        $protocolVersion = new ProtocolVersion($version);

        // Then: The instance should be created successfully
        $this->assertInstanceOf(ProtocolVersion::class, $protocolVersion);
        $this->assertSame($version, $protocolVersion->getProtocolVersion());
    }

    /**
     * User Story:
     * As a developer,
     * I want ProtocolVersion to throw an exception for an invalid version
     * So that only the allowed version is accepted.
     */
    public function testThrowsExceptionForInvalidVersion(): void
    {
        // Given: An invalid OAI-PMH protocol version
        $invalidVersion = '1.1';

        // When: I try to create a ProtocolVersion instance with the invalid version
        $this->expectException(InvalidArgumentException::class);

        // Then: It should throw an InvalidArgumentException
        new ProtocolVersion($invalidVersion);
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two ProtocolVersion instances by value
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        // Given: Two ProtocolVersion instances with the same version
        $protocolVersion1 = new ProtocolVersion('2.0');
        $protocolVersion2 = new ProtocolVersion('2.0');

        // When: I check if they are equal
        $isEqual = $protocolVersion1->equals($protocolVersion2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual);
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of ProtocolVersion
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A ProtocolVersion instance with a specific version
        $version = '2.0';
        $protocolVersion = new ProtocolVersion($version);

        // When: I convert it to a string
        $stringRepresentation = (string)$protocolVersion;

        // Then: The string representation should match the expected format
        $expected = "ProtocolVersion(version: $version)";
        $this->assertSame($expected, $stringRepresentation);
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that ProtocolVersion is immutable
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (see issue #8).
     */
    public function testIsImmutable(): void
    {
        // Given: A ProtocolVersion instance
        $protocolVersion = new ProtocolVersion('2.0');

        // When: I create a ReflectionClass instance
        $reflection = new \ReflectionClass($protocolVersion);

        // Then: All properties should be private
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }
}
