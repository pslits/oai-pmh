<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

namespace OaiPmh\Domain\Exception;

use RuntimeException;

/**
 * Thrown when the repository configuration is missing required keys or contains invalid values.
 *
 * This exception is raised during aggregate initialisation from a configuration array
 * (e.g. loaded from YAML). It is not an OAI-PMH protocol error — it signals that the
 * server itself is misconfigured and cannot start correctly.
 *
 * The caller should treat this as a fatal startup error and halt with a clear message
 * rather than returning an OAI-PMH XML error response.
 */
final class InvalidConfigurationException extends RuntimeException
{
    /**
     * Creates an exception for a required key that is absent from the configuration.
     *
     * @param string $key     The missing configuration key (e.g. "repository_name").
     * @param string $section An optional description of the configuration section for context.
     * @return self
     */
    public static function missingKey(string $key, string $section = 'repository'): self
    {
        return new self(
            sprintf(
                'Required configuration key "%s" is missing from the "%s" section.',
                $key,
                $section,
            )
        );
    }

    /**
     * Creates an exception for a key whose value has an invalid type or format.
     *
     * @param string $key     The configuration key with the invalid value.
     * @param string $reason  A short description of what is wrong with the value.
     * @return self
     */
    public static function invalidValue(string $key, string $reason): self
    {
        return new self(
            sprintf(
                'Configuration key "%s" has an invalid value: %s',
                $key,
                $reason,
            )
        );
    }
}
