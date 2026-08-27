<?php

declare(strict_types=1);

/*****************************************************************************
 * File:        NetAcuityUDP.php
 * Author:      Digital Envoy
 * Program:     NetAcuity PHP API
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
 * Description: This class can be used to interfaces with a NetAcuity server.
 *
 *
 ****************************************************************************/

require_once dirname(__FILE__) . "/../QueryMethod.php";
require_once dirname(__FILE__) . "/../Utils.php";
require_once dirname(__FILE__) . "/../NetAcuityDBDefs.php";

define ("NA_UDP_PORT",              5400);
define ("MAX_PACKET_SIZE",  1500);


//
// NetAcuity - Main NetAcuity API class.
//
class NetAcuityUDP extends QueryMethod
{
  public $timeoutSeconds;
  public $apiID;
  public $naServerIPAddr;
  protected $naServerPort;

    // Constructor
    public function __construct($naServerIPAddr, $apiID = 0, $timeoutSeconds = 2, $naServerPort = NA_UDP_PORT)
    {
        parent::__construct();

        // Initialize Various Objects
        $this->naServerIPAddr          = $naServerIPAddr;
        $this->apiID                   = $apiID;
        $this->timeoutSeconds          = $timeoutSeconds;
        $this->naServerPort            = $naServerPort;
    }

    public function queryXml($ip, $featureCodes, $transactionID)
    {
        $response = array();
        $this->setDefault();
        if (!$this->lookupFeatureCodes($ip, $featureCodes, $response, $transactionID))
        {
            // The error is set by the lookupDBs function
            return 0;
        }
        return $response;
    }

    private function parseXMLResponse($rawResponse, &$responses)
    {
        $rootElement = simplexml_load_string($rawResponse);
        if ($rootElement === false)
        {
            $this->logError("Unable to parse XML response from NetAcuity Server.");
            return false;
        }
        foreach ($rootElement->attributes() as $attributeKey => $attributeValue) {
            $responses[$attributeKey] = $attributeValue;
            if (strcmp($attributeKey, "error") == 0)
            {
                $this->errorMsg = $attributeValue;
            }
        }
        if ($this->errorMsg != "")
        {
            return false;
        }
        return true;
    }

    private function lookupFeatureCodes($incomingIPAddr, $featureCodes, &$responses, $transactionID)
    {
        if (!validateApiId($this->apiID))
        {
            $this->logError("Invalid API ID.");
            return 0;
        }
        if (!validateIp($this->naServerIPAddr))
        {
            $this->logError("Invalid NetAcuity Server IP address.");
            return 0;
        }
        if (!validateIp($incomingIPAddr))
        {
            $this->logError("Invalid query IP address.");
            return 0;
        }
        if (strpbrk((string)$transactionID, "\"<>&") !== false)
        {
            $this->logError("Invalid transaction ID.");
            return 0;
        }

        // Build the query packet.
        $queryString = "<request trans-id=\"$transactionID\" " .
                       "ip=\"$incomingIPAddr\" api-id=\"" . $this->apiID . "\" >";

        foreach($featureCodes as $featureCode)
        {
            if (!validateFeatureCode($featureCode))
            {
                $this->logError("Request for feature $featureCode is invalid");
                return 0;
            }
            $queryString .= "<query db=\"$featureCode\" />";
        }

        $queryString .= "</request>";

        $naFd = fsockopen("udp://[" . $this->naServerIPAddr . "]",
                          $this->naServerPort, $errno, $errstr);
        if (!$naFd )
        {
            $this->logError("Unable to connect to NetAcuity Server at " .
                            $this->naServerIPAddr . ":" . $this->naServerPort);
            return 0;
        }

        fputs($naFd, $queryString);
        stream_set_timeout($naFd, $this->timeoutSeconds);

        // The server might send multiple packets back.
        $isDone           = 0;
        $totalPacket      = 0;
        $lastTotalPacket  = 0;
        $packetNumber     = 0;
        $lastPacketNumber = 0;
        $i                = 0;
        $xmlResponse      = "";
        while (!$isDone)
        {
            $responseBuffer = fread($naFd, MAX_PACKET_SIZE);
            if ($responseBuffer == FALSE)
            {
                $this->logError("Error reading from UDP socket");
                fclose($naFd);
                return 0;
            }

            $tempResponse = $responseBuffer;
            $packetNumber = intval(substr($responseBuffer, 0, 2));
            $totalPacket  = intval(substr($responseBuffer, 2, 4));

            // Make sure to get all the packets in order.
            if (($packetNumber - 1) != $lastPacketNumber)
            {
                $this->logError("Packets received out of order");
                fclose($naFd);
                return 0;
            }

            $lastPacketNumber = $packetNumber;
            $xmlResponse .= substr($responseBuffer, 4, -1);
            if (($packetNumber == $totalPacket) || (++$i == 10))
            {
                $isDone = 1;
            }
        }

        fclose($naFd);

        $this->rawResponse  = $xmlResponse;
        $this->responseSize = strlen($xmlResponse);

        if (!$this->parseXMLResponse($xmlResponse, $responses))
        {
            //a specific logError should already be assigned; do not overwrite it
            return 0;
        }
        if ((string)($responses['trans-id'] ?? '') != (string)$transactionID)
        {
            $this->logError("Transaction ID from response does not match transaction ID of request.");
            return 0;
        }
        if (!ipsEqual($responses['ip'] ?? '', $incomingIPAddr))
        {
            $this->logError("Response address does not match the queried address.");
            return 0;
        }
        return 1;
    }

    public function setNaServerIPAddr($naServerIPAddr) {
        $this->naServerIPAddr = $naServerIPAddr;
    }

    public function setApiID($apiID) {
        if (!validateApiId($apiID)) {
            $this->logError("Invalid API ID.");
            return false;
        }
        $this->apiID = $apiID;
        return true;
    }

    public function setTimeoutSeconds($timeoutSeconds) {
        $this->timeoutSeconds = $timeoutSeconds; // Seconds
    }

}
