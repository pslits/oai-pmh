<?php

/* +--------------------------------------------------------------------------+
 * | Filename: MetadataFormatPlugin.php
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

interface MetadataFormatPlugin
{
    /**
     * Get the metadata prefix used by OAI-PMH for this metadata format.
     *
     * @return string
     */
    public function getMetadataPrefix();

    /**
     * Get the namespace URI for this metadata format.
     *
     * @return string
     */
    public function getMetadataNamespace();

    /**
     * Get the XML schema for this metadata format.
     *
     * @return string
     */
    public function getSchema();

    /**
     * Create metadata for the given record in this metadata format.
     *
     * @param DOMDocument $doc
     * @param array<string, string> $record
     *
     * @return DomElement XML representation of the metadata.
     */
    public function createMetadata(DOMDocument $doc, array $record): DOMElement;
}
