<?php
/* +--------------------------------------------------------------------------+
 * | Filename: OAIRequestDTO.php
 * | Author:   Paul Slits
 * | Project:  OAI-PMH
 * +--------------------------------------------------------------------------+
 * | Copyright (C) 2025 Paul Slits
 * |
 * | Permission is hereby granted, free of charge, to any person obtaining a
 * | copy of this software and associated documentation files (the "Software"),
 * | to deal in the Software without restriction, including without limitation
 * | the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * | and/or sell copies of the Software, and to permit persons to whom the
 * | Software is furnished to do so, subject to the following conditions:
 * |
 * | The above copyright notice and this permission notice shall be included in
 * | all copies or substantial portions of the Software.
 * |
 * | THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * | EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * | MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * | IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * | CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * | TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * | SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * +--------------------------------------------------------------------------+
 */

namespace Pslits\OaiPmh;

/**
 * Class OAIRequestDTO
 *
 * This class is a data transfer object (DTO) that holds the request parameters for an OAI-PMH request.
 */
class OAIRequestDTO
{
    /**
     * @const array Allowed OAI-PMH verbs.
     * These verbs are supported by the OAI-PMH protocol and can be used in requests.
     */
    private const ALLOWED_VERBS = [
        'Identify',
        'GetRecord',
        'ListIdentifiers',
        'ListMetadataFormats',
        'ListRecords',
        'ListSets'
    ];

    /**
     * @const array Allowed arguments in the OAI-PMH request.
     * These arguments are valid parameters that can be included in the request.
     */
    private const ALLOWED_ARGUMENTS = [
        'verb',
        'identifier',
        'metadataPrefix',
        'from',
        'until',
        'set',
        'resumptionToken'
    ];

    /**
     * @var OAIParsedQuery An instance of the OAIParsedQuery class that holds the parsed query parameters.
     */
    private OAIParsedQuery $parsedQuery;

    /**
     * @var string The OAI-PMH verb.
     */
    private $verb;

    /**
     * @var string The metadata prefix.
     */
    private $metadataPrefix;

    /**
     * @var OAIException An instance of the OAIException class for handling exceptions.
     */
    private OAIException $exception;

    /**
     * OAIRequestDTO constructor.
     *
     * Initializes a new instance of the OAIRequestDTO class and validates the request parameters.
     *
     * @param OAIParsedQuery $parsedQuery The parsed query parameters from the request.
     * @param OAIException|null $exception An optional OAIException instance for handling exceptions.
     * 
     * @throws OAIException If the request is invalid or contains errors.
     */
    public function __construct(OAIParsedQuery $parsedQuery, ?OAIException $exception = null)
    {
        if ($exception === null) {
            $this->exception = new OAIException();
        } else {
            $this->exception = $exception;
        }

        $this->parsedQuery = $parsedQuery;
        $this->validateQuery();

        if ($this->exception->hasExceptions()) {
            throw $this->exception;
        }

        $this->verb = $this->parsedQuery->getFirstValue('verb');
        $this->metadataPrefix = $this->parsedQuery->getFirstValue('metadataPrefix');
    }

    /**
     * Gets the OAI-PMH verb.
     *
     * @return string The OAI-PMH verb.
     */
    public function getVerb(): string
    {
        return $this->verb;
    }

    /**
     * Gets the metadata prefix.
     *
     * @return string The metadata prefix.
     */
    public function getMetadataPrefix(): string
    {
        return $this->metadataPrefix;
    }

    /**
     * Gets the request URI.
     *
     * @return string The request URI.
     */
    public function getRequestURL(): string
    {
        $baseURL = $_ENV['BASE_URL'];
        // TODO: Validate the base URL
        $requestURL =  $baseURL . '?verb=' . $this->verb . '&metadataPrefix=' . $this->metadataPrefix;
        return $requestURL;
    }

    /**
     * Checks if the request contains the verb argument.
     * 
     * @return bool True if the verb argument is present, false otherwise.
     */
    private function isVerbInRequestArguments(): bool
    {
        return in_array('verb', $this->parsedQuery->getKeys());
    }

    /**
     * Validates if the verb argument is present in the request.
     * 
     * @throws OAIException If the verb argument is missing.
     */
    private function validateVerbIsPresent(): void
    {
        if (!$this->isVerbInRequestArguments()) {
            $this->exception->add('badVerb', 'The verb argument is missing in the request');
        }
    }

    /**
     * Validates if the verb argument is not repeated in the request.
     * 
     * @throws OAIException If the verb argument is repeated.
     */
    private function validateVerbIsNotRepeated(): void
    {
        if ($this->isVerbInRequestArguments()) {
            if ($this->parsedQuery->countKeyOccurrences('verb') > 1) {
                $this->exception->add('badVerb', 'The verb argument is repeated in the request');
            }
        }
    }

    /**
     * Validates if the verb argument is supported by the OAI-PMH protocol.
     * 
     * @throws OAIException If the verb argument is not supported.
     */
    private function validateVerbIsSupported(): void
    {
        if ($this->isVerbInRequestArguments()) {
            $verb = $this->parsedQuery->getFirstValue('verb');
            $isVerbAllowed = ($verb && in_array($verb, self::ALLOWED_VERBS));
            if (!$isVerbAllowed) {
                $this->exception->add('badVerb', 'The value "' . $verb . '" of the verb argument is not supported by the OAI-PMH protocol');
            }
        }
    }

    /**
     * Validates if the arguments in the request are legal.
     * 
     * @throws OAIException If any illegal arguments are found.
     */
    private function validateArgumentsAreLegal(): void
    {
        foreach ($this->parsedQuery->getKeys() as $key) {
            if (!in_array($key, self::ALLOWED_ARGUMENTS)) {
                $this->exception->add('badArgument', 'Illegal argument "' . $key . '" in the request');
            }
        }
    }

    /**
     * Validates if the arguments in the request are not repeated.
     * 
     * @throws OAIException If any arguments are repeated.
     */
    private function validateArgumentsAreNotRepeated(): void
    {
        foreach (self::ALLOWED_ARGUMENTS as $argument) {
            // exclude 'verb' from this check, as it is already validated separately
            if ($argument === 'verb') continue;
            if ($this->parsedQuery->countKeyOccurrences($argument) > 1) {
                $this->exception->add('badArgument', 'Argument "' . $argument . '" is repeated in the request');
            }
        }
    }

    /**
     * Validates the entire request query.
     * 
     * @throws OAIException If any validation errors are found.
     */
    private function validateQuery(): void
    {
        $this->validateVerbIsPresent();
        $this->validateVerbIsNotRepeated();
        $this->validateVerbIsSupported();

        $this->validateArgumentsAreLegal();
        $this->validateArgumentsAreNotRepeated();
    }
}
