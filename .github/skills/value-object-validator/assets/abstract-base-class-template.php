<?php

declare(strict_types=1);

namespace OaiPmh\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Base class for [DomainConcept] value objects.
 *
 * [Detailed explanation of what this base class provides and why it exists.
 * Explain the template method pattern and what subclasses should override.]
 *
 * According to OAI-PMH 2.0 specification section X.X ([SectionName]),
 * [explain OAI-PMH context and requirements].
 *
 * Known subclasses:
 * - [SubclassName1]: [Brief description]
 * - [SubclassName2]: [Brief description]
 *
 * Template Methods (protected):
 * - get[DomainName](): Can be overridden for specialized behavior
 * - validate(): Should be overridden to add subclass-specific validation
 *
 * This abstract base class:
 * - provides shared infrastructure for [domain concept] implementations,
 * - defines extension points through template methods,
 * - is compared by value and class type (not identity),
 * - ensures [validation rule] is enforced,
 * - handles null safety explicitly.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
abstract class ExampleBaseClass
{
    /**
     * The [domain concept] value.
     *
     * Protected to allow subclass access. [Explain null handling].
     *
     * @var string|null
     */
    protected string|null $value;

    /**
     * Constructs a new [BaseClassName] instance.
     *
     * Subclasses should call parent constructor and perform their own validation.
     *
     * @param string|null $domainValue The [domain concept] value.
     * @throws InvalidArgumentException If validation fails.
     */
    public function __construct(string|null $domainValue)
    {
        $this->validate($domainValue);
        $this->value = $domainValue;
    }

    /**
     * Returns the [domain concept] value (template method).
     *
     * This method is protected to allow subclasses to override and provide
     * specialized behavior while maintaining the public interface through getValue().
     *
     * Subclasses can override to:
     * - [Explain use case 1]
     * - [Explain use case 2]
     *
     * @return string|null The [domain concept] value.
     */
    protected function getDomainValue(): string|null
    {
        return $this->value;
    }

    /**
     * Returns the value (public interface).
     *
     * Provided for consistency with other value objects.
     *
     * @return string|null The [domain concept] value.
     */
    public function getValue(): string|null
    {
        return $this->getDomainValue();
    }

    /**
     * Checks if this [BaseClassName] equals another.
     *
     * Comparison includes both class type and value to ensure type-safe
     * equality across inheritance hierarchy.
     *
     * @param self $otherValue The other instance to compare.
     * @return bool True if same class type and value, false otherwise.
     */
    public function equals(self $otherValue): bool
    {
        return get_class($this) === get_class($otherValue)
            && $this->value === $otherValue->value;
    }

    /**
     * Returns a string representation of the [BaseClassName].
     *
     * Includes the actual subclass name for clarity in debugging.
     *
     * @return string A string representation.
     */
    public function __toString(): string
    {
        return sprintf(
            '%s(value: %s)',
            static::class,
            $this->value ?? 'null'
        );
    }

    /**
     * Validates the [domain concept] value (template method).
     *
     * Base implementation provides common validation rules.
     * Subclasses should override to add format-specific validation:
     *
     * @example
     * ```php
     * protected function validate(?string $value): void
     * {
     *     parent::validate($value); // Call base validation
     *     $this->validateFormatSpecificRule($value);
     * }
     * ```
     *
     * @param string|null $domainValue The value to validate.
     * @throws InvalidArgumentException If validation fails.
     */
    protected function validate(?string $domainValue): void
    {
        // Base validation - override in subclasses as needed
        // Example: $this->validateNotEmpty($domainValue);
    }

    /**
     * Validates that the value is not empty (if null not allowed).
     *
     * Example validation method that subclasses can use.
     *
     * @param string|null $domainValue The value to validate.
     * @throws InvalidArgumentException If the value is empty.
     */
    protected function validateNotEmpty(?string $domainValue): void
    {
        if ($domainValue === null || $domainValue === '') {
            throw new InvalidArgumentException(
                sprintf('%s cannot be empty.', static::class)
            );
        }
    }
}
