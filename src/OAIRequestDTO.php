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
     * @var string The OAI-PMH verb.
     */
    private $verb;

    /**
     * @var string The metadata prefix.
     */
    private $metadataPrefix;

    /**
     * OAIRequestDTO constructor.
     *
     * Initializes a new instance of the OAIRequestDTO class.
     *
     * @param string $metadataPrefix The metadata prefix.
     */
    public function __construct(array $request)
    {
        $this->verb = $request['verb'] ?? null;
        $this->metadataPrefix = $request['metadataPrefix'] ?? null;

        $this->validateStructure();
    }

    /**
     * Validates the structure of the request parameters.
     *
     * @throws \InvalidArgumentException If the request parameters are invalid.
     */
    private function validateStructure(): void
    {
        if (!$this->verb) {
            throw new OAIException('badVerb', 'Verb is required');
        }

        if (!$this->metadataPrefix) {
            throw new OAIException('badArgument', 'metadataPrefix is required');
        }
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
}
