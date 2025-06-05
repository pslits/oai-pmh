<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain;

use InvalidArgumentException;

/**
 * Class MetadataPrefix
 *
 * Represents a metadata prefix used in OAI-PMH requests and responses.
 *
 * This value object:
 * - encapsulates a metadata prefix,
 * - provides validation to ensure it adheres to the expected format,
 * - is immutable and compared by value (not identity).
 *
 * Domain concerns such as XML serialization or protocol transport are handled outside this class.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
class MetadataRootTag
{
    private string $rootTag;
    private const ROOT_TAG_PATTERN = '/^[A-Za-z_][A-Za-z0-9_.-]*(:[A-Za-z_][A-Za-z0-9_.-]*)?$/';

    /**
     * MetadataRootTag constructor.
     * Initializes a new instance of the MetadataRootTag class.
     *
     * @param string $rootTag The root tag used in XML elements.
     * @throws \InvalidArgumentException If the root tag does not match the expected pattern.
     */
    public function __construct(string $rootTag)
    {
        $this->validateRootTag($rootTag);
        $this->rootTag = $rootTag;
    }

    /**
     * Returns a string representation of the metadata root tag.
     *
     * @return string The metadata root tag.
     */
    public function __toString(): string
    {
        return sprintf('MetadataRootTag(rootTag: %s)', $this->rootTag);
    }

    /**
     * Returns the root tag used in XML elements.
     *
     * @return string The root tag.
     */
    public function getValue(): string
    {
        return $this->rootTag;
    }

    /**
     * Validates the root tag format.
     *
     * @param string $rootTag The root tag to validate.
     * @throws InvalidArgumentException If the root tag does not match the expected pattern.
     */
    private function validateRootTag(string $rootTag): void
    {
        if (!preg_match(self::ROOT_TAG_PATTERN, $rootTag)) {
            throw new InvalidArgumentException("Invalid metadata root tag: '$rootTag'.");
        }
    }

    /**
     * Checks if this root tag is equal to another.
     *
     * @param MetadataRootTag $other The other root tag to compare against.
     * @return bool True if the two root tags are equal, false otherwise.
     */
    public function equals(MetadataRootTag $other): bool
    {
        return $this->rootTag === $other->getValue();
    }
}
