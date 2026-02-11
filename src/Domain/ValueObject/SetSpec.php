<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Represents an OAI-PMH set specification (setSpec) as a value object.
 *
 * According to OAI-PMH 2.0 specification section 4.6 (SelectiveHarvesting), a setSpec
 * is a colon [:] separated list indicating the set membership of an item. Sets provide
 * a way to selectively harvest specific subsets of metadata from a repository.
 *
 * SetSpec format and rules:
 * - Must contain only alphanumeric characters (a-z, A-Z, 0-9)
 * - May contain hyphens (-), underscores (_), periods (.), and colons (:)
 * - Colons (:) are used as hierarchy delimiters for hierarchical sets
 * - Examples: 'mathematics', 'math:algebra', 'science:physics:quantum'
 * - Case-sensitive
 * - Must not be empty
 * - No spaces or other special characters allowed
 *
 * Common usage patterns:
 * - Simple sets: 'humanities', 'sciences', 'arts'
 * - Hierarchical sets: 'sciences:physics', 'humanities:history:modern'
 * - Themed collections: 'open-access', 'peer-reviewed'
 * - Date-based: '2024-publications', 'recent-additions'
 *
 * This value object:
 * - encapsulates a validated setSpec,
 * - is immutable and compared by value (not identity),
 * - ensures only valid characters are used,
 * - is used in OAI-PMH ListSets, ListRecords, and ListIdentifiers operations.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#SelectiveHarvestingandSets
 * @see OAI-PMH 2.0 Specification Section 4.6
 */
final class SetSpec
{
    private string $setSpec;

    /**
     * Regular expression pattern for valid setSpec format.
     *
     * Pattern breakdown:
     * - ^[A-Za-z0-9\\-_.]+     : First segment (alphanumeric, hyphen, underscore, period)
     * - (?::[A-Za-z0-9\\-_.]+)* : Zero or more additional segments (colon-separated)
     * - $                      : End of string
     *
     * This prevents: leading colons, trailing colons, consecutive colons (empty segments)
     */
    private const PATTERN = '/^[A-Za-z0-9\\-_.]+(?::[A-Za-z0-9\\-_.]+)*$/';

    /**
     * Constructs a new SetSpec instance.
     *
     * @param string $setSpec The set specification identifier.
     * @throws InvalidArgumentException If the setSpec is empty or contains invalid characters.
     */
    public function __construct(string $setSpec)
    {
        $this->validate($setSpec);
        $this->setSpec = $setSpec;
    }

    /**
     * Returns the set specification.
     *
     * @return string The set specification.
     */
    public function getSetSpec(): string
    {
        return $this->setSpec;
    }

    /**
     * Checks if this SetSpec is equal to another.
     *
     * @param SetSpec $otherSetSpec The other instance to compare with.
     * @return bool True if both have the same setSpec value, false otherwise.
     */
    public function equals(self $otherSetSpec): bool
    {
        return $this->setSpec === $otherSetSpec->setSpec;
    }

    /**
     * Returns a string representation of the SetSpec object.
     *
     * @return string A string representation.
     */
    public function __toString(): string
    {
        return sprintf('SetSpec(setSpec: %s)', $this->setSpec);
    }

    /**
     * Validates the set specification.
     *
     * @param string $setSpec The setSpec to validate.
     * @throws InvalidArgumentException If validation fails.
     */
    private function validate(string $setSpec): void
    {
        $this->validateNotEmpty($setSpec);
        $this->validateFormat($setSpec);
    }

    /**
     * Validates that the setSpec is not empty or whitespace-only.
     *
     * @param string $setSpec The setSpec to validate.
     * @throws InvalidArgumentException If the setSpec is empty or whitespace-only.
     */
    private function validateNotEmpty(string $setSpec): void
    {
        if (empty(trim($setSpec))) {
            throw new InvalidArgumentException('SetSpec cannot be empty.');
        }
    }

    /**
     * Validates that the setSpec contains only allowed characters.
     *
     * According to OAI-PMH spec, setSpec may contain:
     * - Alphanumeric characters (a-z, A-Z, 0-9)
     * - Hyphens (-), underscores (_), periods (.), colons (:)
     *
     * @param string $setSpec The setSpec to validate.
     * @throws InvalidArgumentException If the setSpec contains invalid characters.
     */
    private function validateFormat(string $setSpec): void
    {
        if (preg_match(self::PATTERN, $setSpec) !== 1) {
            throw new InvalidArgumentException(
                sprintf(
                    'SetSpec contains invalid characters. ' .
                    'Only alphanumeric, hyphen, underscore, period, and colon are allowed: %s',
                    $setSpec
                )
            );
        }
    }
}
