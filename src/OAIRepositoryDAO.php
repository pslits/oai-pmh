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

class OAIRepositoryDAO
{
    public function __construct() {}

    // return a list of OAIRecordDTO objects
    public function getRecords(OAIRequestDTO $requestDTO): array
    {
        $records = array();

        $record = new OAIRecordDTO(
            'oai:example.org:1',
            new DateTime("2025-01-01T00:00:00Z"),
            'set1',
            '<metadata><title>Record 1</title></metadata>'
        );
        $records[] = $record;

        $record = new OAIRecordDTO(
            'oai:example.org:2',
            new DateTime("2025-01-02T00:00:00Z"),
            'set1',
            '<metadata><title>Record 2</title></metadata>'
        );
        $records[] = $record;

        $record = new OAIRecordDTO(
            'oai:example.org:3',
            new DateTime("2025-01-03T00:00:00Z"),
            'set1',
            '<metadata><title>Record 3</title></metadata>'
        );

        return $records;
    }
}
