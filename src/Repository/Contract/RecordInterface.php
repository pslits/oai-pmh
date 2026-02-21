<?php

/**
 * Contract for OAI-PMH record data access.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2026 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

namespace OaiPmh\Repository\Contract;

use OaiPmh\Domain\ValueObject\RecordIdentifier;
use OaiPmh\Domain\ValueObject\SetSpec;
use OaiPmh\Domain\ValueObject\UTCdatetime;

/**
 * Defines the contract for accessing metadata record data.
 *
 * This interface abstracts record retrieval regardless of storage backend
 * (MySQL, PostgreSQL, file-based, etc.). Implementations should provide
 * access to all OAI-PMH record properties as defined in the protocol specification.
 *
 * According to OAI-PMH 2.0 specification:
 * - Each record has a unique identifier (section 2.4)
 * - Records have datestamps indicating when they were created/modified (section 2.7)
 * - Records may belong to zero or more sets (section 2.6)
 * - Records may be marked as deleted (section 2.5)
 * - Records contain metadata in format-specific XML (section 2.5)
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html
 */
interface RecordInterface
{
    /**
     * Returns the unique identifier of this record.
     *
     * The identifier uniquely identifies this record within the repository
     * and is used in OAI-PMH requests (GetRecord, ListRecords).
     *
     * @return RecordIdentifier The record's unique identifier.
     */
    public function getIdentifier(): RecordIdentifier;

    /**
     * Returns the datestamp when this record was created or last modified.
     *
     * The datestamp is expressed in UTC and follows the repository's
     * declared granularity (date-only or date-time).
     *
     * @return UTCdatetime The record's datestamp.
     */
    public function getDatestamp(): UTCdatetime;

    /**
     * Returns the set memberships of this record.
     *
     * A record may belong to zero or more sets. Sets provide a mechanism
     * for selective harvesting (section 2.6 of OAI-PMH specification).
     *
     * @return SetSpec[] Array of set specifications (may be empty).
     */
    public function getSets(): array;

    /**
     * Determines whether this record is marked as deleted.
     *
     * When a repository supports deleted records (deletedRecord policy is
     * "transient" or "persistent"), deleted records are included in responses
     * but with only header information, no metadata.
     *
     * @return bool True if the record is deleted, false otherwise.
     */
    public function isDeleted(): bool;

    /**
     * Returns the metadata of this record.
     *
     * The metadata is returned as an XML string in the format specific to
     * the requested metadataPrefix. Returns null for deleted records or
     * when metadata is not available.
     *
     * @return string|null The metadata XML, or null if not available.
     */
    public function getMetadata(): ?string;
}
