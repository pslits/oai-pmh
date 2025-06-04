<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain;

use OaiPmh\Domain\MetadataNamespace;
use IteratorAggregate;
use Countable;
use Traversable;
use ArrayIterator;

/**
 * Represents an immutable collection of MetadataNamespace value objects in the OAI-PMH domain.
 *
 * This value object:
 * - encapsulates a set of MetadataNamespace objects,
 * - provides iteration, counting, and value-based comparison,
 * - is immutable and compared by value (not identity).
 *
 * Typically used to represent the set of `xmlns` declarations relevant for metadata element qualification
 * in OAI-PMH responses. Concerns such as XML serialization or protocol transport are handled outside
 * the domain layer.
 *
 * @implements IteratorAggregate<int, MetadataNamespace>
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
final class MetadataNamespaceCollection implements IteratorAggregate, Countable
{
    /** @var MetadataNamespace[] */
    private array $namespaces;

    /**
     * Constructs a new MetadataNamespaceCollection instance.
     *
     * @param MetadataNamespace ...$namespaces Variable number of MetadataNamespace objects.
     */
    public function __construct(MetadataNamespace ...$namespaces)
    {
        $this->validateNamespaces($namespaces);

        $this->namespaces = $namespaces;
    }

    /**
     * Returns a string representation of the MetadataNamespaceCollection.
     * The string format is presented as:
     * `MetadataNamespaceCollection(namespaces: ns1, ns2, ...)`
     *
     * @return string A string representation of the collection.
     */
    public function __toString(): string
    {
        return sprintf(
            'MetadataNamespaceCollection(namespaces: %s)',
            implode(', ', array_map(fn (MetadataNamespace $ns) => (string)$ns, $this->namespaces))
        );
    }

    /**
     * Returns the iterator for the collection of namespaces.
     *
     * @return ArrayIterator<int, MetadataNamespace>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->namespaces);
    }

    /**
     * Returns the number of namespaces in the collection.
     *
     * @return int The number of namespaces in the collection.
     */
    public function count(): int
    {
        return count($this->namespaces);
    }

    /**
     * Converts the collection of MetadataNamespace objects to an associative array.
     *
     * The keys are the namespace prefixes, and the values are the corresponding URIs.
     *
     * @param MetadataNamespace[] $namespaces The namespaces to convert.
     * @return array<string, string> An associative array mapping prefixes to URIs.
     */
    private function toAssoc(array $namespaces): array
    {
        $assoc = [];
        foreach ($namespaces as $namespace) {
            $assoc[$namespace->getPrefix()->getValue()] = $namespace->getUri()->getValue();
        }
        return $assoc;
    }

    /**
     * Checks if this collection is equal to another MetadataNamespaceCollection.
     *
     * Two collections are considered equal if they:
     * - contain the same number of namespaces
     * - contain the same namespaces, regardless of their order
     *
     * @param self $other The other MetadataNamespaceCollection to compare with.
     * @return bool True if both collections are equal, false otherwise.
     */
    public function equals(self $other): bool
    {
        if (count($this->namespaces) !== count($other->namespaces)) {
            return false;
        }

        $array1 = $this->toAssoc($this->namespaces);
        $array2 = $other->toAssoc($other->namespaces);

        return $array1 === $array2;
    }

    /**
     * Validates the provided namespaces.
     *
     * @param MetadataNamespace[] $namespaces The namespaces to validate.
     * @throws \InvalidArgumentException If any namespace is not an instance of MetadataNamespace.
     */
    private function validateNamespaces(array $namespaces): void
    {
        // there must be at least one namespace
        if (empty($namespaces)) {
            throw new \InvalidArgumentException('At least one MetadataNamespace must be provided.');
        }

        // no duplicate prefixes allowed
        $prefixes = [];
        foreach ($namespaces as $namespace) {
            $prefix = $namespace->getPrefix()->getValue();
            if (in_array($prefix, $prefixes, true)) {
                throw new \InvalidArgumentException("Duplicate namespace prefix found: $prefix");
            }
            $prefixes[] = $prefix;
        }

        // no duplicate URIs allowed
        $uris = [];
        foreach ($namespaces as $namespace) {
            $uri = $namespace->getUri()->getValue();
            if (in_array($uri, $uris, true)) {
                throw new \InvalidArgumentException("Duplicate namespace URI found: $uri");
            }
            $uris[] = $uri;
        }
    }
}
