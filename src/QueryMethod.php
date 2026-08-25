<?php

declare(strict_types=1);

/*****************************************************************************
 * File:        QueryMethod.php
 * Author:      Digital Envoy
 * Program:     NetAcuity API
 * Version:     7.0.0
 * Date:        2026-06-14
 *
 * Copyright 2026 Digital Envoy, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 *
 * Description:  Base class for access methods to NetAcuity databases.
 *
 *
 ******************************************************************************/

abstract class QueryMethod
{
    public $rawResponse  = "";
    public $responseSize = 0;
    public $debugOn      = false;
    public $errorMsg     = "";

    public function __construct()
    {
        $this->responseSize = "2";
        $this->rawResponse  = "";
        $this->debugOn      = false;
    }

    abstract public function queryXml($ip, $featureCodes, $transactionID);

    public function getRawResponse(): string
    {
        return $this->rawResponse;
    }

    public function getResponseSize()
    {
        return $this->responseSize;
    }

    public function debugOn()
    {
        $this->debugOn = true;
    }

    public function debugOff()
    {
        $this->debugOn = false;
    }

    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    protected function debug($msg)
    {
        if ($this->debugOn)
        {
            echo($msg);
        }
    }

    protected function setDefault()
    {
        $this->responseSize = 0;
        $this->rawResponse  = "";
        $this->errorMsg = "";
    }

    protected function logError($msg)
    {
        $this->errorMsg = $msg;
        $this->debug("NetAcuity Error: $msg<BR>\n");
    }

}
