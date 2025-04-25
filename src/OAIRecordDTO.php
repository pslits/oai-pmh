<?php

/* +--------------------------------------------------------------------------+
 * | Filename: OAIRepositoryDAO.php
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

use DateTime;

class OAIRecordDTO
{
    /**
     * @var string The identifier of the record.
     */
    private string $identifier;

    /**
     * @var DateTime The datestamp of the record.
     */
    private DateTime $datestamp;

    /**
     * @var string The setSpec of the record.
     */
    private string $setSpec;

    /**
     * @var string The metadata of the record.
     */
    private string $metadata;

    /**
     * OAIRecordDTO constructor.
     *
     * Initializes a new instance of the OAIRecordDTO class.
     *
     * @param string $identifier The identifier of the record.
     * @param DateTime $datestamp The datestamp of the record.
     * @param string $setSpec The setSpec of the record.
     * @param string $metadata The metadata of the record.
     */
    public function __construct(string $identifier, DateTime $datestamp, string $setSpec, string $metadata)
    {
        $this->identifier = $identifier;
        $this->datestamp = $datestamp;
        $this->setSpec = $setSpec;
        $this->metadata = $metadata;
    }

    /**
     * Get the identifier of the record.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Get the datestamp of the record.
     *
     * @return DateTime
     */
    public function getDatestamp(): DateTime
    {
        return $this->datestamp;
    }

    /**
     * Get the setSpec of the record.
     *
     * @return string
     */
    public function getSetSpec(): string
    {
        return $this->setSpec;
    }

    /**
     * Get the metadata of the record.
     *
     * @return string
     */
    public function getMetadata(): string
    {
        return $this->metadata;
    }
}
