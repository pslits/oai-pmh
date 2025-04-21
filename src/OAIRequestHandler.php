<?php
/* +--------------------------------------------------------------------------+
 * | Filename: OAIRequestHandler.php
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

use DOMElement;

/**
 * Class OAIRequestHandler
 *
 * This class is responsible for handling OAI-PMH requests.
 */
class OAIRequestHandler
{
    /**
     * @var array An array of OAI-PMH commands.
     */
    private array $commands;

    /**
     * OAIRequestHandler constructor.
     *
     * Initializes a new instance of the OAIRequestHandler class.
     */
    public function __construct()
    {
        $this->commands = [
            "ListRecords" => new OAIListRecords()
        ];
    }

    /**
     * Handle the OAI-PMH request.
     *
     * @param OAIRequestDTO $requestDTO The request data transfer object.
     * @return DOMElement The response to the request.
     */
    public function handleRequest(OAIRequestDTO $requestDTO): DOMElement
    {
        $verb = $requestDTO->getVerb();
        $responseXML = null;

        if (isset($this->commands[$verb])) {
            $responseXML = $this->commands[$verb]->execute($requestDTO);
        } else {
            throw new OAIException("badVerb", "The verb " . $verb . " is not supported by this repository.");
        }

        return $responseXML;
    }
}
