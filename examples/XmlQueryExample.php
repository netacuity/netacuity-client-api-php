<?php

/*****************************************************************************
 * File:        XmlQueryExample.php
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
 * Description:  Example usage of the NetAcuity XML UDP API.
 *
 *
 ******************************************************************************/

require_once dirname(__FILE__) . "/../src/NetAcuityDBDefs.php";
require_once dirname(__FILE__) . "/../src/UDP/NetAcuityUDP.php";

if (empty($argv[1]) || empty($argv[2]) || empty($argv[3]))
{
    echo "Usage: php XmlQueryExample.php <server_ip> <query_ip> <comma_separated_feature_codes> \n";
    exit(2);
}

$serverAddr = $argv[1];
$queryIP    = $argv[2];
$featureCodes = $argv[3];
$exampleApiID = 76;
$naTimeoutSeconds = 3;

$na = new NetAcuityUDP($serverAddr, $exampleApiID, $naTimeoutSeconds);
$na->debugOn();

// The xml-query supports multiple feature codes per query,
//  so split the input feature codes into an array.
$multiFeatureCodes = explode(",", $featureCodes);

$transactionID = random_int(0, 1000000000);
$response = $na->queryXml($queryIP, $multiFeatureCodes, $transactionID);
if ($response)
{
    echo "ip = " . $response['ip'] . "\n";
    echo "trans-id = " . $response['trans-id'] . "\n";
    foreach ($response as $field => $value) {
        if ($field === 'ip' || $field === 'trans-id') {
            continue;
        }
        echo "$field = $value\n";
    }
    echo "raw-response = " . $na->getRawResponse() . "\n";
}
else {
    echo "Error: " . $na->getErrorMsg() . "\n";
    exit(2);
}
exit;
