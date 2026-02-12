<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\Entity;

use OaiPmh\Domain\Entity\Record;
use OaiPmh\Domain\Entity\RecordHeader;
use OaiPmh\Domain\ValueObject\RecordIdentifier;
use OaiPmh\Domain\ValueObject\UTCdatetime;
use OaiPmh\Domain\ValueObject\Granularity;
use OaiPmh\Domain\ValueObject\SetSpec;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Record entity.
 *
 * Tests the OAI-PMH record implementation following the specification
 * section 2.5 (Record). Records combine header and metadata.
 *
 * @covers \OaiPmh\Domain\Entity\Record
 */
final class RecordTest extends TestCase
{
    private Granularity $granularity;

    protected function setUp(): void
    {
        $this->granularity = new Granularity('YYYY-MM-DDThh:mm:ssZ');
    }

    /**
     * @test
     * Given a non-deleted record with metadata
     * When I create a Record
     * Then it should be created successfully
     */
    public function testConstructWithMetadata(): void
    {
        // Given
        $header = new RecordHeader(
            new RecordIdentifier('oai:example.org:12345'),
            new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity)
        );
        $metadata = ['title' => 'Test Article', 'author' => 'John Doe'];

        // When
        $record = new Record($header, $metadata);

        // Then
        $this->assertInstanceOf(Record::class, $record);
        $this->assertSame($header, $record->getHeader());
        $this->assertSame($metadata, $record->getMetadata());
        $this->assertFalse($record->isDeleted());
    }

    /**
     * @test
     * Given a deleted record
     * When I create a Record
     * Then it should have no metadata
     */
    public function testConstructDeletedRecord(): void
    {
        // Given
        $header = new RecordHeader(
            new RecordIdentifier('oai:example.org:12345'),
            new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity),
            true  // deleted
        );

        // When
        $record = new Record($header);

        // Then
        $this->assertTrue($record->isDeleted());
        $this->assertNull($record->getMetadata());
    }

    /**
     * @test
     * Given a record with complex metadata
     * When I create a Record
     * Then all metadata should be accessible
     */
    public function testConstructWithComplexMetadata(): void
    {
        // Given
        $header = new RecordHeader(
            new RecordIdentifier('oai:example.org:12345'),
            new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity)
        );
        $metadata = [
            'title' => 'Advanced Research in Quantum Computing',
            'creator' => ['Alice Smith', 'Bob Johnson'],
            'subject' => ['Physics', 'Computer Science', 'Quantum Mechanics'],
            'description' => 'A comprehensive study of quantum computing algorithms.',
            'date' => '2024-01-01',
            'type' => 'Article',
            'identifier' => 'doi:10.1234/example.2024.001'
        ];

        // When
        $record = new Record($header, $metadata);

        // Then
        $this->assertSame($metadata, $record->getMetadata());
        $this->assertIsArray($record->getMetadata());
    }

    /**
     * @test
     * Given a record with set specifications
     * When I create a Record
     * Then the header should contain the set specs
     */
    public function testConstructWithSetSpecs(): void
    {
        // Given
        $setSpecs = [
            new SetSpec('mathematics'),
            new SetSpec('math:algebra')
        ];
        $header = new RecordHeader(
            new RecordIdentifier('oai:example.org:12345'),
            new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity),
            false,
            $setSpecs
        );
        $metadata = ['title' => 'Algebraic Structures'];

        // When
        $record = new Record($header, $metadata);

        // Then
        $this->assertCount(2, $record->getHeader()->getSetSpecs());
    }

    /**
     * @test
     * Given a Record
     * When I access the header identifier
     * Then it should return the correct identifier
     */
    public function testGetIdentifierFromHeader(): void
    {
        // Given
        $identifier = new RecordIdentifier('oai:arxiv.org:cs/0112017');
        $header = new RecordHeader(
            $identifier,
            new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity)
        );
        $metadata = ['title' => 'Paper Title'];

        $record = new Record($header, $metadata);

        // When
        $retrievedIdentifier = $record->getHeader()->getIdentifier();

        // Then
        $this->assertSame($identifier, $retrievedIdentifier);
    }

    /**
     * @test
     * Given two Records with the same header identifier
     * When I compare them using equals()
     * Then equals() should return true
     */
    public function testEqualsSameIdentifier(): void
    {
        // Given
        $identifier = new RecordIdentifier('oai:example.org:12345');
        $header1 = new RecordHeader(
            $identifier,
            new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity)
        );
        $header2 = new RecordHeader(
            $identifier,
            new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity)
        );

        $record1 = new Record($header1, ['title' => 'Version 1']);
        $record2 = new Record($header2, ['title' => 'Version 2']);

        // When/Then
        $this->assertTrue($record1->equals($record2));
        $this->assertTrue($record2->equals($record1));
    }

    /**
     * @test
     * Given two Records with different identifiers
     * When I compare them using equals()
     * Then equals() should return false
     */
    public function testEqualsDifferentIdentifier(): void
    {
        // Given
        $header1 = new RecordHeader(
            new RecordIdentifier('oai:example.org:12345'),
            new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity)
        );
        $header2 = new RecordHeader(
            new RecordIdentifier('oai:example.org:67890'),
            new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity)
        );

        $record1 = new Record($header1, ['title' => 'Article 1']);
        $record2 = new Record($header2, ['title' => 'Article 2']);

        // When/Then
        $this->assertFalse($record1->equals($record2));
        $this->assertFalse($record2->equals($record1));
    }

    /**
     * @test
     * Given a Record
     * When I call __toString()
     * Then it should return a string representation
     */
    public function testToString(): void
    {
        // Given
        $header = new RecordHeader(
            new RecordIdentifier('oai:example.org:12345'),
            new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity)
        );
        $metadata = ['title' => 'Test Article'];

        $record = new Record($header, $metadata);

        // When
        $string = (string) $record;

        // Then
        $this->assertStringContainsString('Record', $string);
        $this->assertStringContainsString('oai:example.org:12345', $string);
    }

    /**
     * @test
     * Given a deleted record with null metadata
     * When I create a Record
     * Then isDeleted should return true
     */
    public function testDeletedRecordWithNullMetadata(): void
    {
        // Given
        $header = new RecordHeader(
            new RecordIdentifier('oai:example.org:deleted'),
            new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity),
            true
        );

        // When
        $record = new Record($header, null);

        // Then
        $this->assertTrue($record->isDeleted());
        $this->assertNull($record->getMetadata());
    }

    /**
     * User Story:
     * As an OAI-PMH repository developer,
     * I want the system to enforce that deleted records cannot have metadata
     * So that my repository remains compliant with OAI-PMH 2.0 specification.
     */
    public function testThrowsExceptionWhenDeletedRecordHasMetadata(): void
    {
        // Given: A deleted record header
        $header = new RecordHeader(
            new RecordIdentifier('oai:example.org:123'),
            new UTCdatetime('2024-01-01', new Granularity(Granularity::DATE)),
            isDeleted: true
        );

        // When: Attempting to create a deleted record with metadata
        // Then: An exception should be thrown
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Deleted records cannot have metadata');

        new Record($header, ['title' => 'Should not exist']);
    }

    /**
     * User Story:
     * As an OAI-PMH repository developer,
     * I want to ensure deleted records can still be created with null metadata
     * So that I can properly handle deleted records.
     */
    public function testCanCreateDeletedRecordWithNullMetadata(): void
    {
        // Given: A deleted record header
        $header = new RecordHeader(
            new RecordIdentifier('oai:example.org:deleted-123'),
            new UTCdatetime('2024-01-01', new Granularity(Granularity::DATE)),
            isDeleted: true
        );

        // When: Creating a deleted record with null metadata (explicit)
        $record = new Record($header, null);

        // Then: The record should be created successfully
        $this->assertInstanceOf(Record::class, $record);
        $this->assertTrue($record->isDeleted());
        $this->assertNull($record->getMetadata());
    }

    /**
     * User Story:
     * As an OAI-PMH repository developer,
     * I want to ensure deleted records can be created without specifying metadata
     * So that the API is convenient to use.
     */
    public function testCanCreateDeletedRecordWithoutMetadataParameter(): void
    {
        // Given: A deleted record header
        $header = new RecordHeader(
            new RecordIdentifier('oai:example.org:deleted-456'),
            new UTCdatetime('2024-01-01', new Granularity(Granularity::DATE)),
            isDeleted: true
        );

        // When: Creating a deleted record without metadata parameter
        $record = new Record($header);

        // Then: The record should be created successfully
        $this->assertInstanceOf(Record::class, $record);
        $this->assertTrue($record->isDeleted());
        $this->assertNull($record->getMetadata());
    }
}
