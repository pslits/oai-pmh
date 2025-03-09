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
        // Create a new instance of DOMDocument with version '1.0' and encoding 'UTF-8'
        $this->dom = new DOMDocument('1.0', 'UTF-8');

        // Set the formatOutput property to true, which means the output XML will be nicely formatted with indentation and extra spacing
        $this->dom->formatOutput = true;

        // Set the preserveWhiteSpace property to false, ensuring that unnecessary whitespace (e.g., indentation, new lines) won't affect the DOM's structure
    }

    /**
     * Creates the root XML element for the OAI-PMH response.
     *
     * @return DOMElement The root XML element.
     */
    private function createRoot($verb = null, $parameters = null): DOMElement
    {
        $root = $this->dom->createElement('OAI-PMH');
        $root->setAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');

        $responseDate = $this->dom->createElement('responseDate', gmdate('Y-m-d\TH:i:s\Z'));
        $root->appendChild($responseDate);

        $request = $this->dom->createElement('request', 'http://localhost/oai-mph');

        if ($verb) {
            $request->setAttribute('verb', $verb);

            // If parameters are provided, set them as attributes on the request element
            if ($parameters !== "") {
                foreach ($parameters as $key => $value) {
                    if (!empty($value)) {
                        $request->setAttribute($key, $value);
                    }
                }
            }
        }

        $root->appendChild($request);

        return $root;
    }
    /**
     * Renders an error response.
     *
     * @param string $code    The error code.
     * @param string $message The error message.
     */
    public function renderError(string $code, string $message): void
    {
        $root = $this->createRoot();

        $error = $this->dom->createElement('error', $message);
        $error->setAttribute('code', $code);
        $root->appendChild($error);

        $this->dom->appendChild($root);
        $this->output();
    }

    /**
     * Renders a ListRecords response.
     *
     * @param array                 $records              An array of records data.
     * @param MetadataFormatPlugin  $metadataFormatPlugin An instance of MetadataFormatPlugin to create metadata elements.
     */
    public function renderListRecords(array $records, array $parameters, MetadataFormatPlugin $metadataFormatPlugin): void
    {
        $root = $this->createRoot('ListRecords', $parameters);

        $listRecords = $this->dom->createElement('ListRecords');
        foreach ($records as $record) {
            $recordElement = $this->dom->createElement('record');

            // Handle the header part
            $header = $this->dom->createElement('header');
            $identifier = $this->dom->createElement('identifier', $record['identifier']);
            $datestamp = $this->dom->createElement('datestamp', $record['datestamp']);
            $header->appendChild($identifier);
            $header->appendChild($datestamp);
            $recordElement->appendChild($header);

            // Create metadata element using MetadataFormatPlugin
            $metadata = $this->dom->createElement('metadata');
            $metadataElement = $metadataFormatPlugin->createMetadata($this->dom, $record);
            $metadata->appendChild($metadataElement);
            $recordElement->appendChild($metadata);

            $listRecords->appendChild($recordElement);
        }
        $root->appendChild($listRecords);
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
