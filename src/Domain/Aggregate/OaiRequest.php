<?php

/**
 * This file defines the OaiRequest aggregate, which represents a validated, parsed OAI-PMH protocol request.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

namespace OaiPmh\Domain\Aggregate;

use OaiPmh\Domain\Exception\BadArgumentException;
use OaiPmh\Domain\Exception\BadVerbException;
use OaiPmh\Domain\ValueObject\OaiVerb;

/**
 * Represents a validated, parsed OAI-PMH protocol request.
 *
 * According to OAI-PMH 2.0 specification section 3.1 (Protocol Requests and Responses),
 * every request to an OAI-PMH repository must supply a "verb" argument. Each of the six
 * verbs defines its own set of required, optional, and exclusive arguments:
 *
 * | Verb                 | Required              | Optional                          | Exclusive          |
 * |----------------------|-----------------------|-----------------------------------|--------------------|
 * | Identify             | —                     | —                                 | —                  |
 * | ListMetadataFormats  | —                     | identifier                        | —                  |
 * | ListSets             | —                     | resumptionToken                   | resumptionToken    |
 * | GetRecord            | identifier,           | —                                 | —                  |
 * |                      | metadataPrefix        |                                   |                    |
 * | ListIdentifiers      | metadataPrefix        | from, until, set,                 | resumptionToken    |
 * |                      |                       | resumptionToken                   |                    |
 * | ListRecords          | metadataPrefix        | from, until, set,                 | resumptionToken    |
 * |                      |                       | resumptionToken                   |                    |
 *
 * The exclusive designation for resumptionToken means: when present, it replaces
 * all required arguments and must not be combined with any optional argument.
 *
 * This aggregate:
 * - is created exclusively via the named constructor OaiRequest::fromQueryParameters(),
 * - is immutable after creation (value-object semantics),
 * - throws BadVerbException for any verb-level problem,
 * - throws BadArgumentException for any argument-level problem,
 * - provides typed access to the validated verb and arguments.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#ProtocolMessages
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#ErrorConditions
 */
final class OaiRequest
{
    /**
     * Argument rules per OAI-PMH verb.
     *
     * Each entry defines:
     * - required  — arguments that MUST be present (unless superseded by exclusive)
     * - optional  — arguments that MAY be present
     * - exclusive — if one of these is present it must be the ONLY argument
     *
     * @var array<string, array{required: string[], optional: string[], exclusive: string[]}>
     */
    private const VERB_RULES = [
        'Identify' => [
            'required'  => [],
            'optional'  => [],
            'exclusive' => [],
        ],
        'ListMetadataFormats' => [
            'required'  => [],
            'optional'  => ['identifier'],
            'exclusive' => [],
        ],
        'ListSets' => [
            'required'  => [],
            'optional'  => ['resumptionToken'],
            'exclusive' => ['resumptionToken'],
        ],
        'GetRecord' => [
            'required'  => ['identifier', 'metadataPrefix'],
            'optional'  => [],
            'exclusive' => [],
        ],
        'ListIdentifiers' => [
            'required'  => ['metadataPrefix'],
            'optional'  => ['from', 'until', 'set', 'resumptionToken'],
            'exclusive' => ['resumptionToken'],
        ],
        'ListRecords' => [
            'required'  => ['metadataPrefix'],
            'optional'  => ['from', 'until', 'set', 'resumptionToken'],
            'exclusive' => ['resumptionToken'],
        ],
    ];

    /**
     * The validated OAI-PMH verb.
     */
    private OaiVerb $verb;

    /**
     * The validated arguments for this request (argument name => value).
     *
     * @var array<string, string>
     */
    private array $arguments;

    /**
     * This constructor is private — use OaiRequest::fromQueryParameters() to create instances.
     *
     * Stores the pre-validated verb and argument map. Callers must invoke the named
     * constructor, which runs the full OAI-PMH validation pipeline before delegating here.
     *
     * @param OaiVerb               $verb      The validated verb.
     * @param array<string, string> $arguments The validated arguments (name => value).
     */
    private function __construct(OaiVerb $verb, array $arguments)
    {
        $this->verb      = $verb;
        $this->arguments = $arguments;
    }

    /**
     * This method creates a validated OaiRequest from raw HTTP query parameters.
     *
     * Applies the full OAI-PMH argument validation pipeline:
     * 1. Verify the verb key is present.
     * 2. Verify the verb is a known OAI-PMH verb.
     * 3. Build the legal argument set (required ∪ optional) for this verb.
     * 4. Reject any argument not in the legal set.
     * 5. Enforce the exclusive argument rule (resumptionToken must be alone).
     * 6. Enforce required arguments (waived when exclusive argument is present).
     *
     * @param array<string, string> $queryParameters Raw HTTP query parameters (name => value).
     *
     * @return self A validated, immutable OaiRequest instance.
     *
     * @throws BadVerbException     If the verb is missing or not a legal OAI-PMH verb.
     * @throws BadArgumentException If any argument is illegal, required but missing,
     *                              or the exclusive rule is violated.
     */
    public static function fromQueryParameters(array $queryParameters): self
    {
        $verb      = self::extractVerb($queryParameters);
        $arguments = self::extractArguments($queryParameters);
        $rules     = self::VERB_RULES[$verb];

        self::rejectIllegalArguments($arguments, $rules, $verb);
        self::enforceExclusiveRule($arguments, $rules, $verb);
        self::enforceRequiredArguments($arguments, $rules, $verb);

        return new self(new OaiVerb($verb), $arguments);
    }

    /**
     * This method returns the validated OAI-PMH verb for this request.
     *
     * Provides typed access to the verb that was validated during construction.
     *
     * @return OaiVerb The validated verb encapsulated in an OaiVerb value object.
     */
    public function getVerb(): OaiVerb
    {
        return $this->verb;
    }

    /**
     * This method returns all validated arguments for this request.
     *
     * Provides access to the full argument map, excluding the verb, as validated during construction.
     *
     * @return array<string, string> Argument names mapped to their string values.
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * This method returns true when the named argument is present in this request.
     *
     * Use this before calling getArgument() to avoid an exception on missing arguments.
     *
     * @param string $name The argument name (e.g. metadataPrefix, resumptionToken).
     *
     * @return bool True if the argument is present, false otherwise.
     */
    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->arguments);
    }

    /**
     * This method returns the value of a named argument.
     *
     * Call hasArgument() first if the presence of the argument is not guaranteed.
     *
     * @param string $name The argument name (e.g. metadataPrefix, resumptionToken).
     *
     * @return string The argument value.
     *
     * @throws \InvalidArgumentException If the named argument is not present in this request.
     */
    public function getArgument(string $name): string
    {
        if (!$this->hasArgument($name)) {
            throw new \InvalidArgumentException(
                sprintf('Argument "%s" is not present in this request.', $name)
            );
        }

        return $this->arguments[$name];
    }

    /**
     * This method returns a string representation of the OaiRequest.
     *
     * Produces a debug-friendly representation that includes the verb and all arguments.
     *
     * @return string A debug-friendly string in the format OaiRequest(verb: X, arguments: [k=v, ...]).
     */
    public function __toString(): string
    {
        $args = implode(
            ', ',
            array_map(
                static fn (string $k, string $v): string => sprintf('%s=%s', $k, $v),
                array_keys($this->arguments),
                array_values($this->arguments),
            )
        );

        return sprintf('OaiRequest(verb: %s, arguments: [%s])', $this->verb->getVerb(), $args);
    }

    // -----------------------------------------------------------------------
    // Private validation helpers
    // -----------------------------------------------------------------------

    /**
     * This method extracts and validates the verb from the query parameters.
     *
     * @param array<string, string> $queryParameters The raw HTTP query parameters.
     *
     * @return string The raw verb string, confirmed to be a legal OAI-PMH verb.
     *
     * @throws BadVerbException If the verb key is absent or holds an unknown value.
     */
    private static function extractVerb(array $queryParameters): string
    {
        if (!array_key_exists('verb', $queryParameters)) {
            throw BadVerbException::missingVerb();
        }

        $verb = $queryParameters['verb'];

        if (!array_key_exists($verb, self::VERB_RULES)) {
            throw BadVerbException::illegalVerb($verb);
        }

        return $verb;
    }

    /**
     * This method returns all query parameters except verb as the argument map.
     *
     * Strips the verb key so the returned map contains only request arguments, ready for rule validation.
     *
     * @param array<string, string> $queryParameters The raw HTTP query parameters.
     *
     * @return array<string, string> All parameters with the verb key removed.
     */
    private static function extractArguments(array $queryParameters): array
    {
        $arguments = $queryParameters;
        unset($arguments['verb']);

        return $arguments;
    }

    /**
     * This method rejects any argument that is not in the legal set (required ∪ optional) for the verb.
     *
     * @param array<string, string>                                              $arguments The request arguments.
     * @param array{required: string[], optional: string[], exclusive: string[]} $rules     The verb rules.
     * @param string                                                              $verb      The OAI-PMH verb string.
     *
     * @throws BadArgumentException On the first illegal argument found.
     */
    private static function rejectIllegalArguments(array $arguments, array $rules, string $verb): void
    {
        $legalArguments = array_merge($rules['required'], $rules['optional']);

        foreach (array_keys($arguments) as $name) {
            if (!in_array($name, $legalArguments, true)) {
                throw BadArgumentException::illegalArgument($name, $verb);
            }
        }
    }

    /**
     * This method enforces the exclusive argument rule.
     *
     * When a verb defines exclusive arguments (currently only resumptionToken),
     * that argument must be the sole argument in the request. Any other argument
     * alongside it is a protocol violation.
     *
     * @param array<string, string>                                              $arguments The request arguments.
     * @param array{required: string[], optional: string[], exclusive: string[]} $rules     The verb rules.
     * @param string                                                              $verb      The OAI-PMH verb string.
     *
     * @throws BadArgumentException If an exclusive argument is combined with others.
     */
    private static function enforceExclusiveRule(array $arguments, array $rules, string $verb): void
    {
        foreach ($rules['exclusive'] as $exclusiveArg) {
            if (array_key_exists($exclusiveArg, $arguments) && count($arguments) > 1) {
                throw BadArgumentException::exclusiveArgumentViolation($verb);
            }
        }
    }

    /**
     * This method enforces that all required arguments are present.
     *
     * The required check is waived when an exclusive argument (resumptionToken) is
     * present — in that case the token carries the full query context from the
     * previous response and replaces all regular arguments.
     *
     * @param array<string, string>                                              $arguments The request arguments.
     * @param array{required: string[], optional: string[], exclusive: string[]} $rules     The verb rules.
     * @param string                                                              $verb      The OAI-PMH verb string.
     *
     * @throws BadArgumentException If a required argument is absent.
     */
    private static function enforceRequiredArguments(array $arguments, array $rules, string $verb): void
    {
        // If an exclusive argument is present it supersedes all required arguments.
        foreach ($rules['exclusive'] as $exclusiveArg) {
            if (array_key_exists($exclusiveArg, $arguments)) {
                return;
            }
        }

        foreach ($rules['required'] as $required) {
            if (!array_key_exists($required, $arguments)) {
                throw BadArgumentException::missingRequiredArgument($required, $verb);
            }
        }
    }
}
