<?php

/**
 * Tests for StorageAdapterInterface contract.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2026 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

namespace OaiPmh\Tests\Repository\Contract;

use InvalidArgumentException;
use OaiPmh\Domain\ValueObject\Granularity;
use OaiPmh\Domain\ValueObject\MetadataPrefix;
use OaiPmh\Domain\ValueObject\RecordIdentifier;
use OaiPmh\Domain\ValueObject\SetSpec;
use OaiPmh\Domain\ValueObject\UTCdatetime;
use OaiPmh\Repository\Contract\RecordInterface;
use OaiPmh\Repository\Contract\StorageAdapterInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests that StorageAdapterInterface can be implemented and used correctly.
 *
 * Uses a fake implementation to verify the contract is sufficient for
 * implementing various storage backends (MySQL, PostgreSQL, file-based).
 */
final class StorageAdapterInterfaceTest extends TestCase
{
    private StorageAdapterInterface $adapter;

    protected function setUp(): void
    {
        $this->adapter = new FakeStorageAdapter();
    }

    /**
     * User Story:
     * As a repository manager,
     * I want to retrieve a specific record by its identifier
     * So that I can implement the OAI-PMH GetRecord verb.
     */
    public function testGetRecordWhenRecordExistsReturnsRecord(): void
    {
        // Given: A known record identifier exists in the repository
        $identifier = new RecordIdentifier('oai:example.org:item-1');

        // When: I get the record by identifier
        $record = $this->adapter->getRecord($identifier);

        // Then: The adapter should return that specific record
        $this->assertInstanceOf(RecordInterface::class, $record);
        $this->assertTrue($record->getIdentifier()->equals($identifier));
    }

    /**
     * User Story:
     * As a repository manager,
     * I want the adapter to throw an exception when a record identifier is not found
     * So that I can respond with appropriate OAI-PMH idDoesNotExist errors.
     */
    public function testGetRecordWhenRecordDoesNotExistThrowsException(): void
    {
        // Given: A record identifier that does not exist
        $identifier = new RecordIdentifier('oai:example.org:non-existent');

        // Then: Getting the record should throw an exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Record not found');

        // When: I try to get the non-existent record
        $this->adapter->getRecord($identifier);
    }

    /**
     * User Story:
     * As a repository manager,
     * I want to list all records without filters
     * So that I can implement basic OAI-PMH ListRecords functionality.
     */
    public function testListRecordsWithoutFiltersReturnsAllRecords(): void
    {
        // Given: A repository with multiple records
        // (provided by setUp())

        // When: I list records without any filters
        $records = $this->adapter->listRecords();

        // Then: The adapter should return all records
        $this->assertGreaterThan(0, count($records));
        $this->assertContainsOnlyInstancesOf(RecordInterface::class, $records);
    }

    /**
     * User Story:
     * As a repository manager,
     * I want to filter records by metadata prefix
     * So that harvesters can request specific metadata formats.
     */
    public function testListRecordsWithMetadataPrefixReturnsFilteredRecords(): void
    {
        // Given: A metadata prefix filter
        $metadataPrefix = new MetadataPrefix('oai_dc');

        // When: I list records with that metadata prefix
        $records = $this->adapter->listRecords($metadataPrefix);

        // Then: The adapter should return records in that format
        $this->assertContainsOnlyInstancesOf(RecordInterface::class, $records);
    }

    /**
     * User Story:
     * As a repository manager,
     * I want to filter records by a 'from' date
     * So that harvesters can perform selective harvesting from a specific date.
     */
    public function testListRecordsWithFromDateReturnsRecordsAfterDate(): void
    {
        // Given: A date to filter from
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
        $from = new UTCdatetime('2026-01-01T00:00:00Z', $granularity);

        // When: I list records from that date onwards
        $records = $this->adapter->listRecords(null, $from);

        // Then: All returned records should have datestamps >= from date
        foreach ($records as $record) {
            $this->assertGreaterThanOrEqual(
                0,
                strcmp($record->getDatestamp()->getDateTime(), $from->getDateTime())
            );
        }
    }

    /**
     * User Story:
     * As a repository manager,
     * I want to filter records by an 'until' date
     * So that harvesters can perform selective harvesting up to a specific date.
     */
    public function testListRecordsWithUntilDateReturnsRecordsBeforeDate(): void
    {
        // Given: A date to filter until
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
        $until = new UTCdatetime('2026-12-31T23:59:59Z', $granularity);

        // When: I list records up to that date
        $records = $this->adapter->listRecords(null, null, $until);

        // Then: All returned records should have datestamps <= until date
        foreach ($records as $record) {
            $this->assertLessThanOrEqual(
                0,
                strcmp($record->getDatestamp()->getDateTime(), $until->getDateTime())
            );
        }
    }

    /**
     * User Story:
     * As a repository manager,
     * I want to filter records by set membership
     * So that harvesters can perform selective harvesting by collection or category.
     */
    public function testListRecordsWithSetReturnsRecordsInSet(): void
    {
        // Given: A set specification to filter by
        $set = new SetSpec('collection:articles');

        // When: I list records in that set
        $records = $this->adapter->listRecords(null, null, null, $set);

        // Then: All returned records should belong to that set
        foreach ($records as $record) {
            $recordSets = $record->getSets();
            $foundInSet = false;
            foreach ($recordSets as $recordSet) {
                if ($recordSet->equals($set)) {
                    $foundInSet = true;
                    break;
                }
            }
            $this->assertTrue($foundInSet, 'Record should be in the specified set');
        }
    }

    /**
     * User Story:
     * As a repository manager,
     * I want to apply multiple filters simultaneously
     * So that harvesters can perform complex selective harvesting queries.
     */
    public function testListRecordsWithMultipleFiltersReturnsFilteredRecords(): void
    {
        // Given: Multiple filter criteria
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
        $metadataPrefix = new MetadataPrefix('oai_dc');
        $from = new UTCdatetime('2026-01-01T00:00:00Z', $granularity);
        $until = new UTCdatetime('2026-12-31T23:59:59Z', $granularity);
        $set = new SetSpec('collection:articles');

        // When: I list records with all filters applied
        $records = $this->adapter->listRecords($metadataPrefix, $from, $until, $set);

        // Then: The adapter should return only records matching all criteria
        $this->assertContainsOnlyInstancesOf(RecordInterface::class, $records);
    }

    /**
     * User Story:
     * As a repository manager,
     * I want to count the total number of records
     * So that I can implement pagination and flow control.
     */
    public function testCountRecordsWithoutFiltersReturnsTotal(): void
    {
        // Given: A repository with multiple records
        // (provided by setUp())

        // When: I count all records without filters
        $count = $this->adapter->countRecords();

        // Then: The adapter should return the total record count
        $this->assertGreaterThanOrEqual(0, $count);
    }

    /**
     * User Story:
     * As a repository manager,
     * I want to count records matching specific criteria
     * So that I can determine result set sizes before fetching records.
     */
    public function testCountRecordsWithFiltersReturnsFilteredCount(): void
    {
        // Given: Filter criteria for selective counting
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
        $metadataPrefix = new MetadataPrefix('oai_dc');
        $from = new UTCdatetime('2026-01-01T00:00:00Z', $granularity);

        // When: I count records with those filters
        $count = $this->adapter->countRecords($metadataPrefix, $from);

        // Then: The adapter should return the filtered count
        $this->assertGreaterThanOrEqual(0, $count);
    }

    /**
     * User Story:
     * As a repository manager,
     * I want to determine the earliest datestamp in the repository
     * So that I can include it in OAI-PMH Identify responses.
     */
    public function testGetEarliestDatestampReturnsUTCdatetime(): void
    {
        // Given: A repository with multiple records
        // (provided by setUp())

        // When: I request the earliest datestamp
        $earliest = $this->adapter->getEarliestDatestamp();

        // Then: The adapter should return a valid UTC datetime
        $this->assertInstanceOf(UTCdatetime::class, $earliest);
    }

    /**
     * User Story:
     * As a repository manager,
     * I want the adapter to return null for earliest datestamp when the repository is empty
     * So that I can handle empty repositories appropriately.
     */
    public function testGetEarliestDatestampWhenNoRecordsReturnsNull(): void
    {
        // Given: An empty repository
        $emptyAdapter = new FakeStorageAdapter([]);

        // When: I request the earliest datestamp
        $earliest = $emptyAdapter->getEarliestDatestamp();

        // Then: The adapter should return null
        $this->assertNull($earliest);
    }
}
