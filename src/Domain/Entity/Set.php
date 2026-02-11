<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain\Entity;

use OaiPmh\Domain\ValueObject\SetSpec;

/**
 * Represents an OAI-PMH set entity.
 *
 * According to OAI-PMH 2.0 specification section 4.6 (SelectiveHarvesting), sets
 * are optional constructs for grouping items for selective harvesting. Sets allow
 * repositories to organize items into meaningful collections that can be harvested
 * independently.
 *
 * Set components (per OAI-PMH specification):
 * - **setSpec** (required): A colon-separated string identifying the set
 * - **setName** (required): A short human-readable name for the set
 * - **setDescription** (optional): XML container describing the set in detail
 *
 * Set characteristics:
 * - Sets are identified by their setSpec (unique within the repository)
 * - Sets can be hierarchical (e.g., 'math:algebra' is a subset of 'math')
 * - Items can belong to zero or more sets
 * - Set descriptions typically use Dublin Core but can use other schemas
 *
 * Common use cases:
 * - Thematic collections (e.g., 'open-access', 'theses', 'images')
 * - Department/division organization (e.g., 'dept:physics', 'dept:chemistry')
 * - Date-based collections (e.g., 'recent', '2024-publications')
 * - Status-based (e.g., 'peer-reviewed', 'preprints')
 *
 * This entity:
 * - represents a collection/grouping of repository items,
 * - supports hierarchical set structures via colon-separated setSpec,
 * - is compared by setSpec value (two sets are equal if they have same setSpec),
 * - is used in ListSets responses and set-based selective harvesting.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#SelectiveHarvestingandSets
 * @see OAI-PMH 2.0 Specification Section 4.6
 */
final class Set
{
    private SetSpec $setSpec;
    private string $setName;
    private ?string $setDescription;

    /**
     * Constructs a new Set instance.
     *
     * @param SetSpec $setSpec The unique set specification identifier.
     * @param string $setName The human-readable name for the set.
     * @param string|null $setDescription Optional description of the set.
     */
    public function __construct(
        SetSpec $setSpec,
        string $setName,
        ?string $setDescription = null
    ) {
        $this->setSpec = $setSpec;
        $this->setName = $setName;
        // Treat empty strings as null
        $this->setDescription = empty($setDescription) ? null : $setDescription;
    }

    /**
     * Returns the set specification.
     *
     * @return SetSpec The unique set identifier.
     */
    public function getSetSpec(): SetSpec
    {
        return $this->setSpec;
    }

    /**
     * Returns the set name.
     *
     * @return string The human-readable set name.
     */
    public function getSetName(): string
    {
        return $this->setName;
    }

    /**
     * Returns the set description.
     *
     * @return string|null The set description, or null if not provided.
     */
    public function getSetDescription(): ?string
    {
        return $this->setDescription;
    }

    /**
     * Checks if this Set is equal to another.
     *
     * Two sets are considered equal if they have the same setSpec.
     *
     * @param Set $otherSet The other Set instance to compare with.
     * @return bool True if both sets have the same setSpec, false otherwise.
     */
    public function equals(self $otherSet): bool
    {
        return $this->setSpec->equals($otherSet->setSpec);
    }

    /**
     * Returns a string representation of the Set.
     *
     * @return string A string representation.
     */
    public function __toString(): string
    {
        return sprintf(
            'Set(setSpec: %s, setName: %s)',
            $this->setSpec->getSetSpec(),
            $this->setName
        );
    }
}
