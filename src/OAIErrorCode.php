<?php

/**
 * +--------------------------------------------------------------------------+
 * | This file is part of the OAI-PMH package.                                |
 * | @link https://github.com/pslits/oai-pmh                                  |
 * +--------------------------------------------------------------------------+
 * | (c) 2025 Paul Slits <paul.slits@gmail.com>                               |
 * | This source code is licensed under the MIT license found in the LICENSE  |
 * | file in the root directory of this source tree or at the following link: |
 * | @license MIT <https://opensource.org/licenses/MIT>                       |
 * +--------------------------------------------------------------------------+
 */

namespace Pslits\OaiPmh;

final class OAIErrorCode
{
    public const BAD_ARGUMENT = 'badArgument';
    public const BAD_RESUMPTION_TOKEN = 'badResumptionToken';
    public const BAD_VERB = 'badVerb';
    public const CANNOT_DISSEMINATE_FORMAT = 'cannotDisseminateFormat';
    public const ID_DOES_NOT_EXIST = 'idDoesNotExist';
    public const NO_RECORDS_MATCH = 'noRecordsMatch';
    public const NO_METADATA_FORMATS = 'noMetadataFormats';
    public const NO_SET_HIERARCHY = 'noSetHierarchy';
}
