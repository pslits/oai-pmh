<?php

/**
 * Contract for storage adapter implementations.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2026 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

namespace OaiPmh\Repository\Contract;

use InvalidArgumentException;
use OaiPmh\Domain\ValueObject\MetadataPrefix;
use OaiPmh\Domain\ValueObject\RecordIdentifier;
use OaiPmh\Domain\ValueObject\SetSpec;
use OaiPmh\Domain\ValueObject\UTCdatetime;

/**
 * Defines the contract for storage backend implementations.
 *
 * This interface abstracts the storage layer, allowing repositories to implement
 * various backends (MySQL, PostgreSQL, MongoDB, file-based, etc.) without
 * coupling the domain layer to a specific storage technology.
 *
 * Implementations must provide:
 * - Record retrieval by identifier
 * - Record listing with optional filters (metadata format, date range, set)
 * - Record counting with the same filters
 * - Earliest datestamp determination for repository identification
 *
 * All date filtering follows OAI-PMH selective harvesting rules (section 3.4):
 * - from: records with datestamp >= from
 * - until: records with datestamp <= until
 * - set: records belonging to the specified set
 *
 * PAGINATION:
 * Pagination support will be added when implementing resumption tokens.
 * The specific approach (offset-based, cursor-based, or stateful) will be
 * determined based on the chosen resumption token strategy. For now, this
 * interface remains simple and will be extended as needed.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html
 */
interface StorageAdapterInterface
{
    /**
     * Gets a single record by its identifier.
     *
     * This method is used to implement the GetRecord verb, which retrieves
     * an individual metadata record from a repository.
     *
     * @throws InvalidArgumentException If the record does not exist in the repository.
     */
    public function getRecord(RecordIdentifier $identifier): RecordInterface;

    /**
     * Lists records with optional selective harvesting filters.
     *
     * This method is used to implement the ListRecords and ListIdentifiers verbs.
     * All filter parameters are optional; when null, they are not applied.
     *
     * Filtering rules:
     * - metadataPrefix: only records available in this metadata format (optional in storage layer)
     * - from: only records with datestamp >= from (inclusive)
     * - until: only records with datestamp <= until (inclusive)
     * - set: only records that are members of the specified set
     *
     * Note: Pagination parameters will be added when implementing resumption tokens.
     *
     * @param MetadataPrefix|null $metadataPrefix Optional metadata format filter.
     * @param UTCdatetime|null $from Optional lower bound for datestamp (inclusive).
     * @param UTCdatetime|null $until Optional upper bound for datestamp (inclusive).
     * @param SetSpec|null $set Optional set membership filter.
     * @return RecordInterface[] Array of records matching the filters (may be empty).
     */
    public function listRecords(
        ?MetadataPrefix $metadataPrefix = null,
        ?UTCdatetime $from = null,
        ?UTCdatetime $until = null,
        ?SetSpec $set = null
    ): array;

    /**
     * Counts records matching the specified filters.
     *
     * This method supports pagination and flow control by determining the
     * total number of records that match the filter criteria before fetching them.
     *
     * Filter parameters follow the same semantics as listRecords().
     *
     * @param MetadataPrefix|null $metadataPrefix Optional metadata format filter.
     * @param UTCdatetime|null $from Optional lower bound for datestamp (inclusive).
     * @param UTCdatetime|null $until Optional upper bound for datestamp (inclusive).
     * @param SetSpec|null $set Optional set membership filter.
     * @return int The number of records matching the filters (0 if none match).
     */
    public function countRecords(
        ?MetadataPrefix $metadataPrefix = null,
        ?UTCdatetime $from = null,
        ?UTCdatetime $until = null,
        ?SetSpec $set = null
    ): int;

    /**
     * Determines the earliest datestamp in the repository.
     *
     * This is used in the Identify verb response to indicate the guaranteed
     * lower limit of all datestamps in the repository (section 3.1.2.6).
     *
     * @return UTCdatetime|null The earliest datestamp, or null if the repository is empty.
     */
    public function getEarliestDatestamp(): ?UTCdatetime;
}
