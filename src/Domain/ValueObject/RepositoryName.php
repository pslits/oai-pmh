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
 * Represents the human-readable name of an OAI-PMH repository.
 *
 * This value object:
 * - encapsulates the repository name as a non-empty string,
 * - validates that the name is not empty or contains only whitespace,
 * - is immutable and compared by value (not identity),
 * - is used in the Identify response to provide a descriptive name for the repository.
 *
 * According to the OAI-PMH specification, the repositoryName is a human-readable name
 * for the repository that helps identify it to users and harvesters.
 *
 * Domain concerns such as XML serialization or protocol transport are handled outside this class.
 */
final class RepositoryName
{
    private string $name;

    /**
     * RepositoryName constructor.
     * Initializes a new instance of the RepositoryName class.
     *
     * @param string $name The human-readable name of the repository.
     * @throws InvalidArgumentException If the name is empty or contains only whitespace.
     */
    public function __construct(string $name)
    {
        $this->validateName($name);
        $this->name = $name;
    }

    /**
     * Returns a string representation of the RepositoryName object.
     *
     * @return string A string representation in the format: RepositoryName(name: <name>)
     */
    public function __toString(): string
    {
        return sprintf('RepositoryName(name: %s)', $this->name);
    }

    /**
     * Returns the repository name.
     *
     * @return string The human-readable name of the repository.
     */
    public function getValue(): string
    {
        return $this->name;
    }

    /**
     * Checks if this RepositoryName is equal to another.
     *
     * Two RepositoryName instances are considered equal if they have the same name value.
     *
     * @param RepositoryName $other The other RepositoryName to compare against.
     * @return bool True if both RepositoryNames are equal, false otherwise.
     */
    public function equals(RepositoryName $other): bool
    {
        return $this->name === $other->name;
    }

    /**
     * Validates the repository name.
     *
     * @param string $name The name to validate.
     * @throws InvalidArgumentException If the name is empty or contains only whitespace.
     */
    private function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('RepositoryName cannot be empty or contain only whitespace.');
        }
    }
}
