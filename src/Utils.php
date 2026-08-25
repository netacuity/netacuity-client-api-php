<?php

/*****************************************************************************
 * File:        Utils.php
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
 * Description:  Utils class for NetAcuity API
 *
 *
 *****************************************************************************/

function convertToUnsignedFloat($intVal)
{
    return ($intVal < 0) ? floatval(sprintf("%u", $intVal)) : $intVal;
}

function validateFeatureCode($featureCode)
{
    return (preg_match('/^\d+$/', $featureCode) && $featureCode < 100 && $featureCode >= 3) ? true : false;
}

function validateApiId($apiId)
{
    return (preg_match('/^\d+$/', (string)$apiId) && $apiId >= 0 && $apiId <= 127) ? true : false;
}

function validateIp($ip)
{
    return filter_var($ip, FILTER_VALIDATE_IP) !== false;
}

/**
 * Returns whether two IP address strings denote the same address, comparing
 * parsed addresses rather than raw text so a differently-formatted-but-equal
 * IPv6 literal (e.g. compressed vs. expanded) still matches. A malformed
 * value is never treated as equal to anything.
 */
function ipsEqual($a, $b)
{
    $packedA = @inet_pton((string)$a);
    $packedB = @inet_pton((string)$b);
    return $packedA !== false && $packedB !== false && $packedA === $packedB;
}
