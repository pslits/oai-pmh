<?php
/* +--------------------------------------------------------------------------+
 * | Filename: OAIApplication.php
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
 * Class OAIApplication
 *
 * This class is responsible for handling the OAI-PMH application logic.
 */
class OAIApplication
{
    protected OAIView $view;

    /**
     * OAIApplication constructor.
     *
     * Initializes a new instance of the OAIApplication class.
     */
    public function __construct()
    {
        $this->view = new OAIView();
    }

    /**
     * Run the OAI-PMH application.
     *
     * @param string $request The input parameters for the OAI-PMH request.
     */
    public function run(string $requestQuery): void
    {
        $requestDTO = null;

        try {
            $parsedQuery = new OAIParsedQuery($requestQuery);
            $requestDTO = new OAIRequestDTO($parsedQuery);

            $handler = new OAIRequestHandler();
            $response = $handler->handleRequest($requestDTO);

            $this->view->renderResponse($requestDTO, $response);
        } catch (OAIException $e) {
            $this->view->renderError($e->getExceptionList(), $requestDTO);
        }
        // } catch (Exception $e) {
        //     $oaiView->renderError('internalServerError', $e->getMessage());
    }
}
