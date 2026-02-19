<?php

/**
 * Represents a complete OAI-PMH record entity.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @version   0.1.0
 * @since     0.1.0
 */

namespace OaiPmh\Domain\Entity;

use InvalidArgumentException;

/**
 * Represents a Record entity — combines header and optional metadata.
 *
 * According to OAI-PMH 2.0 specification section 2.5 (Record), a Record
 * consists of a header (identifier, datestamp, optional setSpecs and status)
 * and, when not deleted, a metadata container with the record's metadata.
 * Deleted records MUST contain only a header and MUST omit the metadata
 * element.
 *
 * This entity:
 * - encapsulates a `RecordHeader` and optional metadata payload,
 * - enforces the invariant that deleted records cannot have metadata,
 * - is compared by identifier (value equality via the record identifier),
 * - is used in `GetRecord` and `ListRecords` responses.
 *
 * Usage:
 * - Construct with a `RecordHeader` and `null` metadata for deleted records.
 * - For active records provide the metadata array; callers should serialize
 *   or validate metadata according to the selected `metadataPrefix`.
 *
 * Note: Kept as a final entity rather than a value object to reflect its
 * lifecycle and relationships within the domain model.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#Record
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
     * Initializes a Record with a `RecordHeader` and optional metadata. If the
     * provided header indicates the record is deleted, the metadata MUST be
     * null; otherwise the metadata may contain the record's payload.
     *
     * @param RecordHeader $header The record header (identifier, datestamp and status).
     * @param array<string, mixed>|null $metadata The metadata content, or null for deleted records.
     * @throws InvalidArgumentException If the header is marked deleted but metadata is not null.
     */
    public function __construct(
        RecordHeader $header,
        ?array $metadata = null
    ) {
        if ($header->isDeleted() && $metadata !== null) {
            throw new InvalidArgumentException(
                'Deleted records cannot have metadata.'
            );
        }

        $this->header = $header;
        $this->metadata = $metadata;
    }

    /**
     * This function returns the RecordHeader for this Record.
     *
     * Returns the `RecordHeader` containing the record's identifier,
     * datestamp, optional setSpecs and status flag (deleted or not).
     *
     * @return RecordHeader The record header (identifier, datestamp and status).
     */
    public function getHeader(): RecordHeader
    {
        return $this->header;
    }

    /**
     * This function returns the record metadata.
     *
     * Returns the metadata payload for active records, or null when the
     * record is marked deleted. Consumers should serialize or validate the
     * metadata according to the repository's `metadataPrefix`.
     *
     * @return array<string, mixed>|null The metadata content, or null for deleted records.
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * This function returns whether the record is marked as deleted.
     *
     * Returns true when the underlying `RecordHeader` has status="deleted".
     * Callers can use this to decide whether metadata is expected to be present
     * or intentionally omitted for deleted records.
     *
     * @return bool True if the record is deleted, false otherwise.
     */
    public function isDeleted(): bool
    {
        return $this->header->isDeleted();
    }

    /**
     * This function checks whether this Record is equal to another by identifier.
     *
     * Determines equality by delegating to the underlying `RecordIdentifier`.
     * Two records are equal when their identifiers are equal.
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
     * This function returns a concise string representation of the Record.
     *
     * Returns a short, human-readable summary including the record identifier,
     * deletion status and whether metadata is present. Intended for logging
     * and debugging; avoid relying on the exact format for machine parsing.
     *
     * @return string A string in the format: 'Record(identifier: ..., deleted: true|false, hasMetadata: true|false)'.
     */
    public function __toString(): string
    {
        return sprintf(
            'Record(identifier: %s, deleted: %s, hasMetadata: %s)',
            $this->header->getIdentifier()->getRecordIdentifier(),
            $this->isDeleted() ? 'true' : 'false',
            $this->metadata !== null ? 'true' : 'false'
        );
    }
}
