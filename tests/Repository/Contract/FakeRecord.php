<?php

/**
 * Fake implementation of RecordInterface for testing purposes.
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
use OaiPmh\Repository\Contract\RecordInterface;

/**
 * Fake implementation of RecordInterface for testing purposes.
 *
 * This demonstrates that the interface contract is sufficient to represent
 * any metadata record regardless of storage backend (MySQL, PostgreSQL,
 * file-based, etc.).
 *
 * This fake can be used in any test that needs a RecordInterface implementation
 * without requiring a real database or storage backend.
 */
final class FakeRecord implements RecordInterface
{
    private RecordIdentifier $identifier;
    private UTCdatetime $datestamp;
    /** @var SetSpec[] */
    private array $sets;
    private bool $deleted;
    private ?string $metadataContent;

    /**
     * Constructs a fake record.
     *
     * @param RecordIdentifier $identifier The record identifier.
     * @param UTCdatetime|null $datestamp The record datestamp (defaults to 2026-01-01 if null).
     * @param SetSpec[] $sets The set memberships (defaults to empty array).
     * @param bool $deleted Whether the record is deleted (defaults to false).
     * @param string|null $metadataContent The metadata XML content (defaults to null).
     */
    public function __construct(
        RecordIdentifier $identifier,
        ?UTCdatetime $datestamp = null,
        array $sets = [],
        bool $deleted = false,
        ?string $metadataContent = null
    ) {
        $this->identifier = $identifier;
        $this->datestamp = $datestamp ?? new UTCdatetime(
            '2026-01-01T00:00:00Z',
            new Granularity(Granularity::DATE_TIME_SECOND)
        );
        $this->sets = $sets;
        $this->deleted = $deleted;
        $this->metadataContent = $metadataContent;
    }

    public function getIdentifier(): RecordIdentifier
    {
        return $this->identifier;
    }

    public function getDatestamp(): UTCdatetime
    {
        return $this->datestamp;
    }

    /**
     * @return SetSpec[]
     */
    public function getSets(): array
    {
        return $this->sets;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function getMetadata(): ?string
    {
        return $this->metadataContent;
    }
}
