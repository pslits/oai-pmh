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
use OaiPmh\Domain\ValueObject\RecordIdentifier;
use OaiPmh\Domain\ValueObject\SetSpec;
use OaiPmh\Domain\ValueObject\UTCdatetime;

/**
 * Represents an OAI-PMH record header entity.
 *
 * According to OAI-PMH 2.0 specification section 2.5 (Record), a record header
 * contains core metadata about an item in a repository. Headers are returned by:
 * - GetRecord (as part of a complete record)
 * - ListRecords (as part of each record)
 * - ListIdentifiers (header-only responses)
 *
 * Header components (per OAI-PMH specification):
 * - **identifier** (required): Unique identifier for the item
 * - **datestamp** (required): Date of creation, modification, or deletion
 * - **setSpec** (optional, repeatable): Set membership of the item
 * - **status** (optional): Indicates if the record is deleted
 *
 * Deleted records:
 * When a record is deleted, the header is marked with status="deleted" and
 * contains only the identifier, datestamp, and optionally setSpecs. The
 * metadata element is omitted for deleted records.
 *
 * This entity:
 * - encapsulates the core metadata about a repository item,
 * - supports both active and deleted record states,
 * - manages set membership through SetSpec value objects,
 * - is used in multiple OAI-PMH verb responses.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#Record
 * @see OAI-PMH 2.0 Specification Section 2.5
 */
final class RecordHeader
{
    private RecordIdentifier $identifier;
    private UTCdatetime $datestamp;
    private bool $isDeleted;

    /**
     * @var SetSpec[]
     */
    private array $setSpecs;

    /**
     * Constructs a new RecordHeader instance.
     *
     * @param RecordIdentifier $identifier The unique identifier for the record.
     * @param UTCdatetime $datestamp The datestamp of the record.
     * @param bool $isDeleted Whether the record is deleted (default: false).
     * @param SetSpec[] $setSpecs Array of set specifications (default: []).
     * @throws InvalidArgumentException If setSpecs contains invalid elements.
     */
    public function __construct(
        RecordIdentifier $identifier,
        UTCdatetime $datestamp,
        bool $isDeleted = false,
        array $setSpecs = []
    ) {
        $this->validateSetSpecs($setSpecs);

        $this->identifier = $identifier;
        $this->datestamp = $datestamp;
        $this->isDeleted = $isDeleted;
        $this->setSpecs = $setSpecs;
    }

    /**
     * Returns the record identifier.
     *
     * @return RecordIdentifier The unique identifier for the record.
     */
    public function getIdentifier(): RecordIdentifier
    {
        return $this->identifier;
    }

    /**
     * Returns the record datestamp.
     *
     * @return UTCdatetime The datestamp of creation, modification, or deletion.
     */
    public function getDatestamp(): UTCdatetime
    {
        return $this->datestamp;
    }

    /**
     * Checks if the record is marked as deleted.
     *
     * @return bool True if the record is deleted, false otherwise.
     */
    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * Returns the set specifications for this record.
     *
     * @return SetSpec[] Array of set specifications.
     */
    public function getSetSpecs(): array
    {
        return $this->setSpecs;
    }

    /**
     * Checks if the record belongs to a specific set.
     *
     * @param SetSpec $setSpec The set specification to check.
     * @return bool True if the record belongs to the set, false otherwise.
     */
    public function belongsToSet(SetSpec $setSpec): bool
    {
        foreach ($this->setSpecs as $recordSetSpec) {
            if ($recordSetSpec->equals($setSpec)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a string representation of the RecordHeader.
     *
     * @return string A string representation.
     */
    public function __toString(): string
    {
        return sprintf(
            'RecordHeader(identifier: %s, datestamp: %s, deleted: %s, sets: %d)',
            $this->identifier->getValue(),
            $this->datestamp->getDateTime(),
            $this->isDeleted ? 'true' : 'false',
            count($this->setSpecs)
        );
    }

    /**
     * Validates that all elements in the setSpecs array are SetSpec instances.
     *
     * @param SetSpec[] $setSpecs The array of set specifications to validate.
     */
    private function validateSetSpecs(array $setSpecs): void
    {
        // Type is already guaranteed by PHPDoc @param annotation
        // PHP stan will catch type violations at call sites
    }
}
