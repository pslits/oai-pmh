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
 * Represents an OAI-PMH protocol verb as a value object.
 *
 * According to OAI-PMH 2.0 specification section 3.1 (Protocol Requests), the protocol
 * supports six verbs, each representing a different type of operation that can be
 * performed on a repository.
 *
 * The six OAI-PMH verbs are:
 * 1. **Identify** - Retrieve information about a repository
 * 2. **ListMetadataFormats** - Retrieve supported metadata formats
 * 3. **ListSets** - Retrieve the set structure of a repository
 * 4. **GetRecord** - Retrieve an individual metadata record
 * 5. **ListIdentifiers** - Retrieve record headers (brief record information)
 * 6. **ListRecords** - Retrieve complete metadata records
 *
 * Verb characteristics:
 * - Case-sensitive (must match exact casing)
 * - Fixed set (only the 6 defined verbs are valid)
 * - Required in every OAI-PMH request
 * - Determines which operation the repository should perform
 *
 * This value object:
 * - encapsulates a validated OAI-PMH verb,
 * - is immutable and compared by value (not identity),
 * - ensures only valid OAI-PMH verbs are accepted,
 * - is used throughout the application layer to route requests.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#ProtocolMessages
 * @see OAI-PMH 2.0 Specification Section 3.1
 */
final class OaiVerb
{
    private string $verb;

    /**
     * Valid OAI-PMH protocol verbs (case-sensitive).
     */
    private const VALID_VERBS = [
        'Identify',
        'ListMetadataFormats',
        'ListSets',
        'GetRecord',
        'ListIdentifiers',
        'ListRecords',
    ];

    /**
     * Constructs a new OaiVerb instance.
     *
     * @param string $verb The OAI-PMH verb.
     * @throws InvalidArgumentException If the verb is empty or not a valid OAI-PMH verb.
     */
    public function __construct(string $verb)
    {
        $this->validate($verb);
        $this->verb = $verb;
    }

    /**
     * Returns the OAI-PMH verb.
     *
     * @return string The verb.
     */
    public function getVerb(): string
    {
        return $this->verb;
    }

    /**
     * Checks if this OaiVerb is equal to another.
     *
     * @param OaiVerb $otherVerb The other instance to compare with.
     * @return bool True if both have the same verb value, false otherwise.
     */
    public function equals(self $otherVerb): bool
    {
        return $this->verb === $otherVerb->verb;
    }

    /**
     * Returns a string representation of the OaiVerb object.
     *
     * @return string A string representation.
     */
    public function __toString(): string
    {
        return sprintf('OaiVerb(verb: %s)', $this->verb);
    }

    /**
     * Validates the OAI-PMH verb.
     *
     * @param string $verb The verb to validate.
     * @throws InvalidArgumentException If validation fails.
     */
    private function validate(string $verb): void
    {
        $this->validateNotEmpty($verb);
        $this->validateIsValidVerb($verb);
    }

    /**
     * Validates that the verb is not empty.
     *
     * @param string $verb The verb to validate.
     * @throws InvalidArgumentException If the verb is empty.
     */
    private function validateNotEmpty(string $verb): void
    {
        if (empty(trim($verb))) {
            throw new InvalidArgumentException('OaiVerb cannot be empty.');
        }
    }

    /**
     * Validates that the verb is one of the six valid OAI-PMH verbs.
     *
     * The verb must match exactly (case-sensitive) one of:
     * Identify, ListMetadataFormats, ListSets, GetRecord, ListIdentifiers, ListRecords
     *
     * @param string $verb The verb to validate.
     * @throws InvalidArgumentException If the verb is not a valid OAI-PMH verb.
     */
    private function validateIsValidVerb(string $verb): void
    {
        if (!in_array($verb, self::VALID_VERBS, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid OAI-PMH verb: %s. ' .
                    'Valid verbs are: %s',
                    $verb,
                    implode(', ', self::VALID_VERBS)
                )
            );
        }
    }
}
