<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use OaiPmh\Domain\ValueObject\Granularity;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * Tests for the Granularity class.
 *
 * This class contains unit tests for the Granularity value object,
 * ensuring it behaves correctly as a value object in the OAI-PMH domain.
 */
class GranularityTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create a Granularity with value DATE
     * So that it can be used in OAI-PMH responses.
     */
    public function testCanInstantiateWithDateGranularity(): void
    {
        // Given: A valid granularity value
        $granularity = new Granularity(Granularity::DATE);

        // When: I create a Granularity instance with DATE
        $this->assertInstanceOf(Granularity::class, $granularity);

        // Then: The instance should have the correct value
        $this->assertSame(Granularity::DATE, $granularity->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want to create a Granularity with value DATE_TIME_SECOND
     * So that it can be used in OAI-PMH responses.
     */
    public function testCanInstantiateWithDateTimeSecondGranularity(): void
    {
        // Given: A valid granularity value
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);

        // When: I create a Granularity instance with DATE_TIME_SECOND
        $this->assertInstanceOf(Granularity::class, $granularity);

        // Then: The instance should have the correct value
        $this->assertSame(Granularity::DATE_TIME_SECOND, $granularity->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want Granularity to throw an exception for an invalid value
     * So that only allowed granularities are accepted.
     */
    public function testThrowsExceptionForInvalidGranularity(): void
    {
        // Given: An invalid granularity value
        $invalidGranularity = 'YYYY';

        // When: I try to create a Granularity instance with the invalid value
        // Then: It should throw an InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        new Granularity($invalidGranularity);
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two Granularity instances by value
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        // Given: Two Granularity instances with the same value
        $granularity1 = new Granularity(Granularity::DATE);
        $granularity2 = new Granularity(Granularity::DATE);

        // When: I check if they are equal
        $isEqual = $granularity1->equals($granularity2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual, 'Granularity instances with the same value should be equal.');
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        // Given: Two Granularity instances with different values
        $granularity1 = new Granularity(Granularity::DATE);
        $granulairty2 = new Granularity(Granularity::DATE_TIME_SECOND);

        // When: I check if they are equal
        $isEqual = $granularity1->equals($granulairty2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual, 'Granularity instances with different values should not be equal.');
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of Granularity
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A Granularity instance with DATE granularity
        $granularity = new Granularity(Granularity::DATE);

        // When: I convert it to a string
        $stringRepresentation = (string)$granularity;

        // Then: The string representation should match the expected format
        $expected = "Granularity(granularity: " . Granularity::DATE . ")";
        $this->assertSame(
            $expected,
            $stringRepresentation,
            'String representation of Granularity should match expected format.'
        );
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that Granularity is immutable
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (see issue #8).
     */
    public function testIsImmutable(): void
    {
        // Given: A Granularity instance
        $granularity = new Granularity(Granularity::DATE);

        // When: I check the properties of the instance
        $reflection = new \ReflectionClass($granularity);

        // Then: All properties should be private
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }
}
