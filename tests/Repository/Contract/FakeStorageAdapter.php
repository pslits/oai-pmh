<?php

/**
 * Fake implementation of StorageAdapterInterface for testing purposes.
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

/**
 * Fake implementation of StorageAdapterInterface for testing purposes.
 *
 * This demonstrates that the interface contract is sufficient for implementing
 * various storage backends (MySQL, PostgreSQL, MongoDB, file-based, etc.)
 * without coupling to a specific database implementation.
 *
 * This fake can be used in any test that needs a StorageAdapterInterface
 * implementation without requiring a real database connection.
 */
final class FakeStorageAdapter implements StorageAdapterInterface
{
    /** @var RecordInterface[] */
    private array $records;

    /**
     * Constructs a fake storage adapter.
     *
     * @param RecordInterface[]|null $records Initial records to store (creates default test records if null).
     */
    public function __construct(?array $records = null)
    {
        if ($records === null) {
            // Create some default test records
            $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
            $this->records = [
                new FakeRecord(
                    new RecordIdentifier('oai:example.org:item-1'),
                    new UTCdatetime('2026-01-15T10:00:00Z', $granularity),
                    [new SetSpec('collection:articles')],
                    false,
                    '<dc:title>Article 1</dc:title>'
                ),
                new FakeRecord(
                    new RecordIdentifier('oai:example.org:item-2'),
                    new UTCdatetime('2026-02-20T14:30:00Z', $granularity),
                    [new SetSpec('collection:articles'), new SetSpec('collection:open-access')],
                    false,
                    '<dc:title>Article 2</dc:title>'
                ),
                new FakeRecord(
                    new RecordIdentifier('oai:example.org:item-3'),
                    new UTCdatetime('2025-12-10T08:00:00Z', $granularity),
                    [new SetSpec('collection:books')],
                    true,
                    null
                ),
            ];
        } else {
            $this->records = $records;
        }
    }

    public function getRecord(RecordIdentifier $identifier): RecordInterface
    {
        foreach ($this->records as $record) {
            if ($record->getIdentifier()->equals($identifier)) {
                return $record;
            }
        }

        throw new InvalidArgumentException(
            sprintf('Record not found: %s', $identifier->getRecordIdentifier())
        );
    }

    /**
     * @return RecordInterface[]
     */
    public function listRecords(
        ?MetadataPrefix $metadataPrefix = null,
        ?UTCdatetime $from = null,
        ?UTCdatetime $until = null,
        ?SetSpec $set = null
    ): array {
        $filtered = $this->records;

        // Apply from filter
        if ($from !== null) {
            $filtered = array_filter($filtered, function (RecordInterface $record) use ($from) {
                return strcmp($record->getDatestamp()->getDateTime(), $from->getDateTime()) >= 0;
            });
        }

        // Apply until filter
        if ($until !== null) {
            $filtered = array_filter($filtered, function (RecordInterface $record) use ($until) {
                return strcmp($record->getDatestamp()->getDateTime(), $until->getDateTime()) <= 0;
            });
        }

        // Apply set filter
        if ($set !== null) {
            $filtered = array_filter($filtered, function (RecordInterface $record) use ($set) {
                foreach ($record->getSets() as $recordSet) {
                    if ($recordSet->equals($set)) {
                        return true;
                    }
                }
                return false;
            });
        }

        // Note: metadataPrefix filtering would depend on additional record metadata
        // For this fake implementation, we ignore it

        return array_values($filtered);
    }

    public function countRecords(
        ?MetadataPrefix $metadataPrefix = null,
        ?UTCdatetime $from = null,
        ?UTCdatetime $until = null,
        ?SetSpec $set = null
    ): int {
        return count($this->listRecords($metadataPrefix, $from, $until, $set));
    }

    public function getEarliestDatestamp(): ?UTCdatetime
    {
        if (empty($this->records)) {
            return null;
        }

        $earliest = $this->records[0]->getDatestamp();
        foreach ($this->records as $record) {
            if (strcmp($record->getDatestamp()->getDateTime(), $earliest->getDateTime()) < 0) {
                $earliest = $record->getDatestamp();
            }
        }

        return $earliest;
    }
}
