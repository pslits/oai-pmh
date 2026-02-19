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

/**
 * Thrown when an OAI-PMH request contains an illegal, missing, or exclusive-violated argument.
 *
 * According to OAI-PMH 2.0 specification section 3.6, the "badArgument" error code
 * covers three distinct argument problems:
 *
 * 1. **Illegal argument** — an argument was supplied that is not valid for the given verb.
 * 2. **Missing required argument** — a mandatory argument for the given verb was not supplied.
 * 3. **Exclusive argument violation** — the "resumptionToken" argument was combined with
 *    other arguments; it must be used alone when present.
 *
 * The caller is expected to catch this exception and return an OAI-PMH XML error
 * response with error code "badArgument".
 *
 * @see https://www.openarchives.org/OAI/openarchivesprotocol.html#ErrorConditions
 */
final class BadArgumentException extends OaiProtocolException
{
    /**
     * Constructs a new BadArgumentException.
     *
     * @param string $message A human-readable description of the bad argument error.
     */
    public function __construct(string $message)
    {
        parent::__construct('badArgument', $message);
    }

    /**
     * Creates an exception for an argument that is not legal for the given verb.
     *
     * @param string $argument The name of the illegal argument.
     * @param string $verb     The verb that received the illegal argument.
     * @return self
     */
    public static function illegalArgument(string $argument, string $verb): self
    {
        return new self(
            sprintf(
                'The argument "%s" is not legal for the verb "%s".',
                $argument,
                $verb,
            )
        );
    }

    /**
     * Creates an exception for a required argument that is absent.
     *
     * @param string $argument The name of the missing required argument.
     * @param string $verb     The verb that requires the argument.
     * @return self
     */
    public static function missingRequiredArgument(string $argument, string $verb): self
    {
        return new self(
            sprintf(
                'The required argument "%s" is missing for the verb "%s".',
                $argument,
                $verb,
            )
        );
    }

    /**
     * Creates an exception for exclusive argument violation.
     *
     * "resumptionToken" is an exclusive argument: when present it must be the only
     * argument supplied alongside the verb.
     *
     * @param string $verb The verb for which the exclusive rule was violated.
     * @return self
     */
    public static function exclusiveArgumentViolation(string $verb): self
    {
        return new self(
            sprintf(
                'The argument "resumptionToken" is exclusive and cannot be combined '
                . 'with other arguments for verb "%s".',
                $verb,
            )
        );
    }
}
