<?php

/**
 * Tests for RecordInterface contract.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2026 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

namespace OaiPmh\Tests\Repository\Contract;

use OaiPmh\Domain\ValueObject\Granularity;
use OaiPmh\Domain\ValueObject\RecordIdentifier;
use OaiPmh\Domain\ValueObject\SetSpec;
use OaiPmh\Domain\ValueObject\UTCdatetime;
use PHPUnit\Framework\TestCase;

/**
 * Tests that RecordInterface can be implemented and used correctly.
 *
 * Uses a fake implementation to verify the contract is sufficient for
 * representing metadata records regardless of storage backend.
 */
final class RecordInterfaceTest extends TestCase
{
    /**
     * User Story:
     * As a storage adapter developer,
     * I want to implement RecordInterface with a method to retrieve the record identifier
     * So that I can uniquely identify records in OAI-PMH responses.
     */
    public function testGetIdentifierReturnsRecordIdentifier(): void
    {
        // Given: A record with a specific identifier
        $identifier = new RecordIdentifier('oai:example.org:item-123');

        // When: I create a record with that identifier
        $record = new FakeRecord($identifier);

        // Then: The record should return the same identifier
        $this->assertTrue($record->getIdentifier()->equals($identifier));
    }

    /**
     * User Story:
     * As a storage adapter developer,
     * I want to implement RecordInterface with a method to retrieve the record datestamp
     * So that I can include creation/modification dates in OAI-PMH responses.
     */
    public function testGetDatestampReturnsUTCdatetime(): void
    {
        // Given: A specific UTC datestamp
        $identifier = new RecordIdentifier('oai:example.org:item-123');
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
        $datestamp = new UTCdatetime('2026-02-21T10:30:00Z', $granularity);

        // When: I create a record with that datestamp
        $record = new FakeRecord($identifier, $datestamp);

        // Then: The record should return the same datestamp
        $this->assertTrue($record->getDatestamp()->equals($datestamp));
    }

    /**
     * User Story:
     * As a storage adapter developer,
     * I want to implement RecordInterface with a method to retrieve set memberships
     * So that I can support selective harvesting by set in OAI-PMH.
     */
    public function testGetSetsReturnsArrayOfSetSpecs(): void
    {
        // Given: A record belonging to multiple sets
        $identifier = new RecordIdentifier('oai:example.org:item-123');
        $sets = [
            new SetSpec('collection:articles'),
            new SetSpec('collection:open-access'),
        ];

        // When: I create a record with those sets
        $record = new FakeRecord($identifier, null, $sets);

        // Then: The record should return all set memberships
        $recordSets = $record->getSets();
        $this->assertCount(2, $recordSets);
        $this->assertContainsOnlyInstancesOf(SetSpec::class, $recordSets);
    }

    /**
     * User Story:
     * As a storage adapter developer,
     * I want RecordInterface to handle records with no set memberships
     * So that I can represent records not belonging to any set.
     */
    public function testGetSetsWhenNoSetsReturnsEmptyArray(): void
    {
        // Given: A record not belonging to any set
        $identifier = new RecordIdentifier('oai:example.org:item-123');

        // When: I create a record with no sets
        $record = new FakeRecord($identifier, null, []);

        // Then: The record should return an empty array
        $this->assertSame([], $record->getSets());
    }

    /**
     * User Story:
     * As a storage adapter developer,
     * I want RecordInterface to indicate when a record is not deleted
     * So that I can distinguish active records from deleted ones.
     */
    public function testIsDeletedWhenNotDeletedReturnsFalse(): void
    {
        // Given: An active (non-deleted) record
        $identifier = new RecordIdentifier('oai:example.org:item-123');

        // When: I create a record that is not deleted
        $record = new FakeRecord($identifier, null, [], false);

        // Then: The record should indicate it is not deleted
        $this->assertFalse($record->isDeleted());
    }

    /**
     * User Story:
     * As a storage adapter developer,
     * I want RecordInterface to indicate when a record is deleted
     * So that I can support OAI-PMH deletedRecord policies.
     */
    public function testIsDeletedWhenDeletedReturnsTrue(): void
    {
        // Given: A deleted record
        $identifier = new RecordIdentifier('oai:example.org:item-123');

        // When: I create a record that is marked as deleted
        $record = new FakeRecord($identifier, null, [], true);

        // Then: The record should indicate it is deleted
        $this->assertTrue($record->isDeleted());
    }

    /**
     * User Story:
     * As a storage adapter developer,
     * I want RecordInterface to provide access to metadata
     * So that I can include metadata in OAI-PMH GetRecord and ListRecords responses.
     */
    public function testGetMetadataReturnsString(): void
    {
        // Given: Metadata as XML
        $identifier = new RecordIdentifier('oai:example.org:item-123');
        $metadata = '<dc:title>Test Article</dc:title>';

        // When: I create a record with that metadata
        $record = new FakeRecord($identifier, null, [], false, $metadata);

        // Then: The record should return the metadata
        $this->assertSame($metadata, $record->getMetadata());
    }

    /**
     * User Story:
     * As a storage adapter developer,
     * I want RecordInterface to handle records without metadata
     * So that I can represent deleted records or records pending metadata.
     */
    public function testGetMetadataWhenNullReturnsNull(): void
    {
        // Given: A record with no metadata
        $identifier = new RecordIdentifier('oai:example.org:item-123');

        // When: I create a record with null metadata
        $record = new FakeRecord($identifier, null, [], false, null);

        // Then: The record should return null for metadata
        $this->assertNull($record->getMetadata());
    }
}
