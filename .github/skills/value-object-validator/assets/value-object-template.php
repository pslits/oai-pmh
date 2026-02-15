<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

namespace OaiPmh\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Represents the [OAI-PMH concept] as a value object.
 *
 * According to OAI-PMH 2.0 specification section [X.X] ([Section Name]),
 * [brief explanation of what this represents in the OAI-PMH protocol].
 *
 * [If enumeration, list allowed values:]
 * Allowed values:
 * - 'value1': description
 * - 'value2': description
 * - 'value3': description
 *
 * [OR if formatted value:]
 * Format: [describe the required format]
 *
 * This value object:
 * - encapsulates a validated [concept name],
 * - is immutable and compared by value (not identity),
 * - ensures only valid [concept] values are accepted,
 * - is [required/optional] in the OAI-PMH [response type] response.
 *
 * @package OaiPmh\Domain\ValueObject
 */
final class ExampleValueObject
{
    /**
     * The encapsulated value.
     *
     * @var string
     */
    private string $value;

    /**
     * Constructs a new ExampleValueObject instance.
     *
     * This constructor validates the provided value to ensure it meets
     * OAI-PMH 2.0 specification requirements before encapsulating it.
     *
     * @param string $exampleValue The [concept] to encapsulate (use descriptive name, not $value).
     *
     * @throws InvalidArgumentException If the value is empty, invalid, or does not meet specification requirements.
     */
    public function __construct(string $exampleValue)
    {
        $this->validate($exampleValue);
        $this->value = $exampleValue;
    }

    /**
     * Returns the [concept name] (domain-specific getter).
     *
     * This getter method uses a name that clearly indicates what domain concept
     * it returns. Generic names like getValue() are NOT acceptable.
     *
     * Examples: getBaseUrl(), getRepositoryName(), getDeletedRecord()
     *
     * @return string The encapsulated [concept].
     */
    public function getExampleValue(): string
    {
        return $this->value;
    }

    /**
     * Checks if this ExampleValueObject is equal to another.
     *
     * Two ExampleValueObject instances are considered equal if they encapsulate
     * the same [concept] value. This implements value equality semantics.
     *
     * @param ExampleValueObject $otherExampleValue The other instance to compare with (use descriptive name).
     *
     * @return bool True if both instances have the same value, false otherwise.
     */
    public function equals(self $otherExampleValue): bool
    {
        return $this->value === $otherExampleValue->value;
    }

    /**
     * Returns a string representation of the ExampleValueObject.
     *
     * Useful for debugging and logging purposes.
     *
     * @return string A string representation in the format "ExampleValueObject(value: X)".
     */
    public function __toString(): string
    {
        return sprintf('ExampleValueObject(value: %s)', $this->value);
    }

    /**
     * Validates the [concept].
     *
     * This is the main validation coordinator that calls focused validation methods.
     * Each aspect of validation is handled by a separate private method for clarity
     * and single responsibility.
     *
     * @param string $exampleValue The value to validate.
     *
     * @throws InvalidArgumentException If validation fails.
     */
    private function validate(string $exampleValue): void
    {
        $this->validateNotEmpty($exampleValue);
        $this->validateFormat($exampleValue);
        // Add more validation methods as needed
    }

    /**
     * Validates that the value is not empty.
     *
     * Empty values are not allowed per OAI-PMH specification.
     *
     * @param string $exampleValue The value to check.
     *
     * @throws InvalidArgumentException If the value is empty.
     */
    private function validateNotEmpty(string $exampleValue): void
    {
        if (empty($exampleValue)) {
            throw new InvalidArgumentException(
                'ExampleValueObject cannot be empty. Per OAI-PMH 2.0 section [X.X], ' .
                'the [concept] is required and must have a value.'
            );
        }
    }

    /**
     * Validates the format of the value.
     *
     * [Explain what format requirements exist and why]
     *
     * @param string $exampleValue The value to validate.
     *
     * @throws InvalidArgumentException If the format is invalid.
     */
    private function validateFormat(string $exampleValue): void
    {
        // Example: Enumeration validation
        $allowedValues = ['value1', 'value2', 'value3'];
        if (!in_array($exampleValue, $allowedValues, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid value "%s". Must be one of: %s',
                    $exampleValue,
                    implode(', ', $allowedValues)
                )
            );
        }

        // OR Example: Pattern validation
        // if (!preg_match('/^pattern$/', $exampleValue)) {
        //     throw new InvalidArgumentException(
        //         sprintf(
        //             'Invalid format "%s". Must match pattern: [describe pattern]',
        //             $exampleValue
        //         )
        //     );
        // }

        // OR Example: URL validation
        // if (!filter_var($exampleValue, FILTER_VALIDATE_URL)) {
        //     throw new InvalidArgumentException(
        //         sprintf('Invalid URL format: %s', $exampleValue)
        //     );
        // }
        //
        // if (!preg_match('/^https?:\/\//', $exampleValue)) {
        //     throw new InvalidArgumentException(
        //         'URL must use HTTP or HTTPS protocol per OAI-PMH specification.'
        //     );
        // }
    }
}
