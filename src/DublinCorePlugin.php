<?php
/* +--------------------------------------------------------------------------+
 * | Filename: DublinCorePlugin.php
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

class DublinCorePlugin implements MetadataFormatPlugin
{

    // Define Dublin Core elements
    private $elements = [
        'title',
        'creator',
        'subject',
        'description',
        'publisher',
        'contributor',
        'date',
        'type',
        'format',
        'identifier',
        'source',
        'language',
        'relation',
        'coverage',
        'rights',
    ];

    public function getMetadataPrefix()
    {
        return 'oai_dc';
    }

    public function getMetadataNamespace()
    {
        return 'http://www.openarchives.org/OAI/2.0/oai_dc/';
    }

    public function getSchema()
    {
        return 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
    }

    /**
     * Create metadata for the given record in Dublin Core format.
     *
     * @param array $record
     * @return DomElement XML representation of the metadata.
     */
    public function createMetadata(DOMDocument $doc, array $record): DOMElement
    {
        $dcElement = $doc->createElement('oai_dc:dc');
        $dcElement->setAttribute('xmlns:oai_dc', $this->getMetadataNamespace());
        $dcElement->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $dcElement->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $dcElement->setAttribute('xsi:schemaLocation', $this->getMetadataNamespace() . ' ' . $this->getSchema());

        foreach ($this->elements as $element) {
            if (isset($record[$element]) && !empty($record[$element])) {
                $el = $doc->createElement("dc:$element", htmlspecialchars($record[$element]));
                $dcElement->appendChild($el);
            }
        }

        // Return the newly created dcElement
        return $dcElement;
    }
}
