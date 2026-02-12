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
 * Represents an OAI-PMH record identifier as a value object.
 *
 * According to OAI-PMH 2.0 specification section 2.4 (Unique Identifier), each record
 * in a repository must have a unique identifier. The identifier is a URI (anyURI schema
 * type) that uniquely identifies an item.
 *
 * Common identifier schemes:
 * - OAI identifier scheme: 'oai:repositoryIdentifier:localIdentifier'
 *   Example: 'oai:arXiv.org:cs/0112017'
 * - HTTP/HTTPS URIs: 'http://hdl.handle.net/10222/12345'
 * - URNs: 'urn:isbn:0451450523'
 * - DOIs: 'doi:10.1000/182'
 *
 * This value object:
 * - encapsulates a validated record identifier,
 * - is immutable and compared by value (not identity),
 * - ensures identifiers are non-empty,
 * - is used in OAI-PMH GetRecord, ListRecords, and ListIdentifiers responses.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#UniqueIdentifier
 * @see OAI-PMH 2.0 Specification Section 2.4
 */
final class RecordIdentifier
{
    private string $identifier;

    /**
     * Constructs a new RecordIdentifier instance.
     *
     * @param string $identifier The unique identifier for the record.
     * @throws InvalidArgumentException If the identifier is empty or whitespace-only.
     */
    public function __construct(string $identifier)
    {
        $this->validate($identifier);
        $this->identifier = $identifier;
    }

    /**
     * Returns the record identifier.
     *
     * @return string The unique identifier.
     */
    public function getRecordIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Checks if this RecordIdentifier is equal to another.
     *
     * @param RecordIdentifier $otherIdentifier The other instance to compare with.
     * @return bool True if both have the same identifier value, false otherwise.
     */
    public function equals(self $otherIdentifier): bool
    {
        return $this->identifier === $otherIdentifier->identifier;
    }

    /**
     * Returns a string representation of the RecordIdentifier object.
     *
     * @return string A string representation.
     */
    public function __toString(): string
    {
        return sprintf('RecordIdentifier(identifier: %s)', $this->identifier);
    }

    /**
     * Validates the record identifier.
     *
     * @param string $identifier The identifier to validate.
     * @throws InvalidArgumentException If validation fails.
     */
    private function validate(string $identifier): void
    {
        $this->validateNotEmpty($identifier);
    }

    /**
     * Validates that the identifier is not empty or whitespace-only.
     *
     * @param string $identifier The identifier to validate.
     * @throws InvalidArgumentException If the identifier is empty or whitespace-only.
     */
    private function validateNotEmpty(string $identifier): void
    {
        if (empty(trim($identifier))) {
            throw new InvalidArgumentException('RecordIdentifier cannot be empty.');
        }
    }
}
