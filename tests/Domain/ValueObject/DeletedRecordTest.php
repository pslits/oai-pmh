<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use OaiPmh\Domain\ValueObject\DeletedRecord;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class DeletedRecordTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create a DeletedRecord with an allowed value
     * So that it can be used in OAI-PMH responses.
     */
    public function testCanInstantiateWithAllowedValues(): void
    {
        // Given: Allowed values for DeletedRecord
        $allowedValues = [
            DeletedRecord::NO,
            DeletedRecord::TRANSIENT,
            DeletedRecord::PERSISTENT
        ];

        // When: I create DeletedRecord instances with each allowed value
        // Then: Each instance should be created successfully
        foreach ($allowedValues as $value) {
            $deletedRecord = new DeletedRecord($value);

            // Then: The instance should be created successfully
            $this->assertInstanceOf(DeletedRecord::class, $deletedRecord);
            $this->assertSame($value, $deletedRecord->getValue());
        }
    }

    /**
     * User Story:
     * As a developer,
     * I want DeletedRecord to throw an exception for an invalid value
     * So that only allowed values are accepted.
     */
    public function testThrowsExceptionForInvalidValue(): void
    {
        // Given: An invalid value for DeletedRecord
        $invalidValue = 'invalid';

        // When: I try to create a DeletedRecord instance with the invalid value
        // Then: It should throw an InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        new DeletedRecord($invalidValue);
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two DeletedRecord instances by value
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        // Given: Two DeletedRecord instances with the same value
        $deletedRecord1 = new DeletedRecord(DeletedRecord::NO);
        $deletedRecord2 = new DeletedRecord(DeletedRecord::NO);

        // When: I check if they are equal
        $isEqual = $deletedRecord1->equals($deletedRecord2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual, 'DeletedRecord instances with the same value should be equal.');
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        // Given: Two DeletedRecord instances with different values
        $deletedRecord1 = new DeletedRecord(DeletedRecord::NO);
        $deletedRecord2 = new DeletedRecord(DeletedRecord::TRANSIENT);

        // When: I check if they are equal
        $isEqual = $deletedRecord1->equals($deletedRecord2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual, 'DeletedRecord instances with different values should not be equal.');
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of DeletedRecord
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A DeletedRecord instance with a specific value
        $deletedRecord = new DeletedRecord(DeletedRecord::TRANSIENT);

        // When: I convert it to a string
        $stringRepresentation = (string)$deletedRecord;

        // Then: The string representation should match the expected format
        $expected = "DeletedRecord(value: " . DeletedRecord::TRANSIENT . ")";
        $this->assertSame(
            $expected,
            $stringRepresentation,
            'String representation of DeletedRecord should match expected format.'
        );
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that DeletedRecord is immutable
     * So that its internal state cannot be changed after construction.
     */
    public function testIsImmutable(): void
    {
        // Given: A DeletedRecord instance
        $deletedRecord = new DeletedRecord(DeletedRecord::NO);

        // When: I try to access its properties
        $reflection = new \ReflectionClass($deletedRecord);

        // Then: All properties should be private
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }
}
