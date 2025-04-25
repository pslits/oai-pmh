<?php

/* +--------------------------------------------------------------------------+
 * | Filename: OAIview.php
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

use DOMDocument;
use DOMElement;

/**
 * Class OAIView
 *
 * This class is responsible for creating and rendering the XML responses
 * for the OAI-PMH protocol. It handles the response creation for errors,
 * list records and potentially other OAI-PMH verbs.
 */
class OAIView
{
    /**
     * @var DOMDocument A DOMDocument instance used for creating XML elements.
     */
    private $dom;

    /**
     * OAIView constructor.
     *
     * Initializes a new instance of the DOMDocument class.
     */
    public function __construct()
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;
    }

    /**
     * Creates the root element for the OAI-PMH response.
     *
     * @return DOMElement The root element.
     */
    private function createRoot(): DOMElement
    {
        $root = $this->dom->createElement('OAI-PMH');
        $root->setAttribute(
            'xmlns',
            'http://www.openarchives.org/OAI/2.0/'
        );
        $root->setAttribute(
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );
        $root->setAttribute(
            'xsi:schemaLocation',
            'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd'
        );

        $responseDate = $this->dom->createElement('responseDate', gmdate('Y-m-d\TH:i:s\Z'));
        $root->appendChild($responseDate);

        return $root;
    }

    /**
     * Creates the request element for the OAI-PMH response.
     *
     * @param OAIRequestDTO $requestDto The request data transfer object.
     * @return DOMElement The request element.
     */
    public function createRequestElement(OAIRequestDTO $requestDto): DOMElement
    {
        $baseURL = $_ENV['BASE_URL'];
        $request = $this->dom->createElement('request', $baseURL);
        $request->setAttribute('verb', $requestDto->getVerb());
        $request->setAttribute('metadataPrefix', $requestDto->getMetadataPrefix());

        return $request;
    }

    /**
     * Renders an error response.
     *
     * @param array $exceptionList The list of exceptions to be rendered.
     * @param OAIRequestDTO|null $requestDTO The request data transfer object, if available.
     */
    public function renderError(array $exceptionList, ?OAIRequestDTO $requestDTO): void
    {
        $root = $this->createRoot();

        $baseURL = $_ENV['BASE_URL'];
        $requestElement = $this->dom->createElement('request', $baseURL);

        // If requestDTO is not null, set the attributes for the request element
        if ($requestDTO) {
            $requestElement->setAttribute('verb', $requestDTO->getVerb());
            $requestElement->setAttribute('metadataPrefix', $requestDTO->getMetadataPrefix());
        }

        $root->appendChild($requestElement);

        // Loop through the exception list and create error elements for each error
        foreach ($exceptionList as $code => $messages) {
            foreach ($messages as $message) {
                $error = $this->dom->createElement('error', $message);
                $error->setAttribute('code', $code);
                $root->appendChild($error);
            }
        }

        $this->dom->appendChild($root);
        $this->output();
    }

    /**
     * Renders a successful response.
     *
     * @param OAIRequestDTO $requestDto The request data transfer object.
     * @param DOMElement     $response   The response to the request.
     */
    public function renderResponse(OAIRequestDTO $requestDto, DomElement $response): void
    {
        $root = $this->createRoot();

        $request = $this->createRequestElement($requestDto);
        $root->appendChild($request);

        $importedNode = $this->dom->importNode($response, true);
        $root->appendChild($importedNode);

        $this->dom->appendChild($root);
        $this->output();
    }

    /**
     * Outputs the created XML response.
     */
    private function output(): void
    {
        header('Content-Type: text/xml');
        echo $this->dom->saveXML();
    }
}
