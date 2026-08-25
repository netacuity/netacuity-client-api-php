<?php

/*****************************************************************************
 * File:        NetAcuityDBDefsTest.php
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
 * Description:  Tests for NetAcuityDBDefs constants and field definitions.
 *
 *****************************************************************************/

use PHPUnit\Framework\TestCase;

class NetAcuityDBDefsTest extends TestCase
{
    // -------------------------------------------------------------------------
    // DB feature code constant values
    // -------------------------------------------------------------------------

    public function testNA_GEO_DB(): void           { $this->assertSame(3,  NA_GEO_DB); }
    public function testNA_EDGE_DB(): void          { $this->assertSame(4,  NA_EDGE_DB); }
    public function testNA_ZIP_DB(): void           { $this->assertSame(7,  NA_ZIP_DB); }
    public function testNA_ISP_DB(): void           { $this->assertSame(8,  NA_ISP_DB); }
    public function testNA_HOME_BIZ_DB(): void      { $this->assertSame(9,  NA_HOME_BIZ_DB); }
    public function testNA_ASN_DB(): void           { $this->assertSame(10, NA_ASN_DB); }
    public function testNA_LANGUAGE_DB(): void      { $this->assertSame(11, NA_LANGUAGE_DB); }
    public function testNA_PROXY_DB(): void         { $this->assertSame(12, NA_PROXY_DB); }
    public function testNA_ISANISP_DB(): void       { $this->assertSame(14, NA_ISANISP_DB); }
    public function testNA_COMPANY_DB(): void       { $this->assertSame(15, NA_COMPANY_DB); }
    public function testNA_DOMAIN_16_DB(): void     { $this->assertSame(16, NA_DOMAIN_16_DB); }
    public function testNA_DEMOGRAPHICS_DB(): void  { $this->assertSame(17, NA_DEMOGRAPHICS_DB); }
    public function testNA_NAICS_DB(): void         { $this->assertSame(18, NA_NAICS_DB); }
    public function testNA_CBSA_DB(): void          { $this->assertSame(19, NA_CBSA_DB); }
    public function testNA_PULSE_MAX_DB(): void     { $this->assertSame(21, NA_PULSE_MAX_DB); }
    public function testNA_MOBILE_CARRIER_DB(): void{ $this->assertSame(24, NA_MOBILE_CARRIER_DB); }
    public function testNA_ORGANIZATION_DB(): void  { $this->assertSame(25, NA_ORGANIZATION_DB); }
    public function testNA_PULSE_DB(): void         { $this->assertSame(26, NA_PULSE_DB); }
    public function testNA_PULSE_PLUS_DB(): void    { $this->assertSame(30, NA_PULSE_PLUS_DB); }
    public function testNA_VPN_PROXY_DB(): void     { $this->assertSame(33, NA_VPN_PROXY_DB); }
    public function testNA_IPC_DB(): void           { $this->assertSame(35, NA_IPC_DB); }
    public function testNA_NODIFY_DB(): void        { $this->assertSame(36, NA_NODIFY_DB); }
    public function testNA_OBSERVED_COUNTRIES_DB(): void { $this->assertSame(37, NA_OBSERVED_COUNTRIES_DB); }
    public function testNA_AA_SA1_DB(): void        { $this->assertSame(40, NA_AA_SA1_DB); }
    public function testNA_AAMAX_SA1_DB(): void     { $this->assertSame(41, NA_AAMAX_SA1_DB); }
    public function testNA_AA_IRIS_DB(): void       { $this->assertSame(42, NA_AA_IRIS_DB); }
    public function testNA_AA_PLZ8_DB(): void       { $this->assertSame(43, NA_AA_PLZ8_DB); }
    public function testNA_AA_ZIP4_DB(): void       { $this->assertSame(44, NA_AA_ZIP4_DB); }
    public function testNA_AAMAX_ZIP4_DB(): void    { $this->assertSame(45, NA_AAMAX_ZIP4_DB); }
    public function testNA_AAMAX_IRIS_DB(): void    { $this->assertSame(46, NA_AAMAX_IRIS_DB); }
    public function testNA_AAMAX_PLZ8_DB(): void    { $this->assertSame(47, NA_AAMAX_PLZ8_DB); }
}
