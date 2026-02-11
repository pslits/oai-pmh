<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain\Entity;

use InvalidArgumentException;

/**
 * Represents a complete OAI-PMH record entity.
 *
 * According to OAI-PMH 2.0 specification section 2.5 (Record), a record is the
 * fundamental unit of metadata harvesting in the OAI-PMH protocol. It combines
 * a header (identifying and cataloging information) with metadata (the actual
 * content being harvested).
 *
 * Record structure (per OAI-PMH specification):
 * - **header** (required): RecordHeader with identifier, datestamp, setSpecs, status
 * - **metadata** (conditional): The actual metadata content in a specified format
 * - **about** (optional): Additional information about the record (not yet implemented)
 *
 * Record states:
 * 1. **Active Record**: Has header and metadata
 *    - Normal harvestable record with complete information
 *    - Header status is not "deleted"
 *
 * 2. **Deleted Record**: Has header only, no metadata
 *    - Header status is "deleted"
 *    - Metadata element is absent
 *    - Used to communicate record deletions to harvesters
 *
 * Deleted records behavior:
 * - Repositories supporting deletedRecord="persistent" or "transient" must
 *   indicate deleted records by marking the header with status="deleted"
 * - Deleted records contain only the header (identifier, datestamp, optional setSpecs)
 * - The metadata element is omitted for deleted records
 *
 * This entity:
 * - combines header information with metadata content,
 * - supports both active and deleted record states,
 * - is compared by record identifier (two records are equal if same identifier),
 * - is used in GetRecord and ListRecords responses.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#Record
 * @see OAI-PMH 2.0 Specification Section 2.5
 */
final class Record
{
    private RecordHeader $header;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $metadata;

    /**
     * Constructs a new Record instance.
     *
     * Per OAI-PMH 2.0 specification section 2.5, deleted records must NOT
     * contain metadata. This invariant is enforced here.
     *
     * For deleted records, metadata should be null or omitted.
     * For active records, metadata should contain the record's metadata content.
     *
     * @param RecordHeader $header The record header.
     * @param array<string, mixed>|null $metadata The metadata content (null for deleted records).
     * @throws InvalidArgumentException If deleted record has metadata.
     */
    public function __construct(
        RecordHeader $header,
        ?array $metadata = null
    ) {
        if ($header->isDeleted() && $metadata !== null) {
            throw new InvalidArgumentException(
                'Deleted records cannot have metadata. Per OAI-PMH 2.0 specification ' .
                'section 2.5, records with status="deleted" must omit the metadata element.'
            );
        }

        $this->header = $header;
        $this->metadata = $metadata;
    }

    /**
     * Returns the record header.
     *
     * @return RecordHeader The header containing identifier, datestamp, and status.
     */
    public function getHeader(): RecordHeader
    {
        return $this->header;
    }

    /**
     * Returns the record metadata.
     *
     * @return array<string, mixed>|null The metadata content, or null for deleted records.
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * Checks if the record is marked as deleted.
     *
     * @return bool True if the record is deleted, false otherwise.
     */
    public function isDeleted(): bool
    {
        return $this->header->isDeleted();
    }

    /**
     * Checks if this Record is equal to another.
     *
     * Two records are considered equal if they have the same identifier.
     *
     * @param Record $otherRecord The other Record instance to compare with.
     * @return bool True if both records have the same identifier, false otherwise.
     */
    public function equals(self $otherRecord): bool
    {
        return $this->header->getIdentifier()->equals(
            $otherRecord->header->getIdentifier()
        );
    }

    /**
     * Returns a string representation of the Record.
     *
     * @return string A string representation.
     */
    public function __toString(): string
    {
        return sprintf(
            'Record(identifier: %s, deleted: %s, hasMetadata: %s)',
            $this->header->getIdentifier()->getValue(),
            $this->isDeleted() ? 'true' : 'false',
            $this->metadata !== null ? 'true' : 'false'
        );
    }
}
