<?php
/* +--------------------------------------------------------------------------+
 * | Filename: OAIListRecords.php
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
 * Class OAIListRecords
 *
 * This class is responsible for listing the records in the repository.
 */
class OAIListRecords extends OAICommand
{
    /**
     * @var DOMDocument An instance of the DOMDocument class.
     */
    private DOMDocument $dom;

    /**
     * @var MetadataFormatPlugin An instance of the MetadataFormatPlugin class.
     */
    private $metadataFormatPlugin;


    /**
     * OAIListRecords constructor.
     *
     * Initializes a new instance of the OAIListRecords class.
     *
     * @param OAIController $oaiController An instance of the OAIController class.
     */
    public function __construct()
    {
        $this->dom = new DOMDocument();
    }

    /**
     * List the records in the repository.
     *
     * @param OAIRequestDTO $requestDTO The request parameters.
     * @return string The OAI-PMH response.
     */
    public function execute(OAIRequestDTO $requestDTO): DOMElement
    {
        // Initialize controller and metadataFormatPlugin based on metadataPrefix or other parameters
        switch ($requestDTO->getMetadataPrefix()) {
            case 'oai_dc':
                $this->metadataFormatPlugin = new DublinCorePlugin();
                break;
            // case 'other_format':
            //     $oaiController = new OtherOAIControllerPlugin($database);
            //     $metadataFormatPlugin = new OtherMetadataFormatPlugin();
            //     break;
            default:
                throw new OAIException('cannotDisseminateFormat', 'The metadata format provided is not supported');
        }

        // Create XML child for listRecordsXml
        /** @var DOMElement $listRecordsXml */
        $listRecordsXml = $this->dom->createElement('ListRecords');

        // Get records
        $records = $this->retrieveRecords(0, 10);

        // Add records to ListRecords from createMetadata
        foreach ($records as $record) {
            // Create record element
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
            $metadataElement = $this->metadataFormatPlugin->createMetadata($this->dom, $record);
            $metadata->appendChild($metadataElement);
            $recordElement->appendChild($metadata);

            $listRecordsXml->appendChild($recordElement);
        }

        // Add resumptionToken element to ListRecords element

        return $listRecordsXml;
    }

    private function retrieveRecords($offset, $limit)
    {
        // Simulating retrieving records where Dublin Core values are part of the same record.
        $mockRecords = [
            [
                'identifier' => 'oai:example.org:record1',
                'datestamp' => '2023-09-29T12:00:00Z',
                'title' => 'Title of Record 1',
                'creator' => 'Author of Record 1',
                'subject' => 'Subject of Record 1',
                'description' => 'Description of Record 1',
                // ... Other Dublin Core fields for Record 1
            ],
            [
                'identifier' => 'oai:example.org:record2',
                'datestamp' => '2023-09-30T12:00:00Z',
                'title' => 'Title of Record 2',
                'creator' => 'Author of Record 2',
                'subject' => 'Subject of Record 2',
                'description' => 'Description of Record 2',
                // ... Other Dublin Core fields for Record 2
            ],
            // ... More mock records as needed.
        ];

        return $mockRecords;
    }
}
