<?php

/*****************************************************************************
 * File:        UtilsTest.php
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
 * Description:  Tests for utility functions in Utils.php.
 *
 *****************************************************************************/

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    // -------------------------------------------------------------------------
    // convertToUnsignedFloat
    // -------------------------------------------------------------------------

    public function testPositiveIntegerReturnedUnchanged(): void
    {
        $this->assertSame(42, convertToUnsignedFloat(42));
    }

    public function testOneReturnedUnchanged(): void
    {
        $this->assertSame(1, convertToUnsignedFloat(1));
    }

    public function testZeroReturnedUnchanged(): void
    {
        $this->assertSame(0, convertToUnsignedFloat(0));
    }

    public function testLargePositiveIntegerReturnedUnchanged(): void
    {
        $this->assertSame(2147483647, convertToUnsignedFloat(2147483647));
    }

    public function testPositiveFloatReturnedUnchanged(): void
    {
        $this->assertSame(3.14, convertToUnsignedFloat(3.14));
    }

    public function testNegativeOneProducesNonNegativeFloat(): void
    {
        $result = convertToUnsignedFloat(-1);
        $this->assertGreaterThan(0, $result);
    }

    public function testNegativeOneMatchesSprintf(): void
    {
        $expected = floatval(sprintf('%u', -1));
        $this->assertEquals($expected, convertToUnsignedFloat(-1));
    }

    public function testNegativeTwoMatchesSprintf(): void
    {
        $expected = floatval(sprintf('%u', -2));
        $this->assertEquals($expected, convertToUnsignedFloat(-2));
    }

    public function testNegativeHundredProducesNonNegativeFloat(): void
    {
        $result = convertToUnsignedFloat(-100);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testNegativeIntMinMatchesSprintf(): void
    {
        $min = PHP_INT_MIN;
        $expected = floatval(sprintf('%u', $min));
        $this->assertEquals($expected, convertToUnsignedFloat($min));
    }

    public function testNegativeInputReturnsFloat(): void
    {
        $result = convertToUnsignedFloat(-1);
        $this->assertIsFloat($result);
    }

    public function testPositiveInputReturnTypePreserved(): void
    {
        // Positive int is returned as-is (not converted to float)
        $result = convertToUnsignedFloat(5);
        $this->assertIsInt($result);
    }

    // -------------------------------------------------------------------------
    // validateFeatureCode
    // -------------------------------------------------------------------------

    public function testMinimumValidCodeThreeReturnsTrue(): void
    {
        $this->assertTrue(validateFeatureCode(3));
    }

    public function testCodeFourReturnsTrue(): void
    {
        $this->assertTrue(validateFeatureCode(4));
    }

    public function testMaximumValidCode99ReturnsTrue(): void
    {
        $this->assertTrue(validateFeatureCode(99));
    }

    public function testCode100ReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode(100));
    }

    public function testCode501ReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode(501));
    }

    public function testCode1000ReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode(1000));
    }

    public function testCodeTwoReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode(2));
    }

    public function testCodeOneReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode(1));
    }

    public function testCodeZeroReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode(0));
    }

    public function testNegativeCodeReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode(-1));
    }

    public function testNegativeLargeCodeReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode(-100));
    }

    public function testNonNumericStringReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode('abc'));
    }

    public function testAlphanumericStringReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode('3x'));
    }

    public function testDecimalStringReturnsFalse(): void
    {
        // A decimal separator is not a digit — the regex rejects it
        $this->assertFalse(validateFeatureCode('3.5'));
    }

    public function testEmptyStringReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode(''));
    }

    public function testStringNumericInRangeReturnsTrue(): void
    {
        $this->assertTrue(validateFeatureCode('10'));
    }

    public function testStringNumericAtMinBoundaryReturnsTrue(): void
    {
        $this->assertTrue(validateFeatureCode('3'));
    }

    public function testStringNumericAtMaxBoundaryReturnsTrue(): void
    {
        $this->assertTrue(validateFeatureCode('99'));
    }

    public function testStringNumericAtUpperExclusiveBoundaryReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode('100'));
    }

    public function testStringNumericBelowMinReturnsFalse(): void
    {
        $this->assertFalse(validateFeatureCode('2'));
    }

    public function testAllDefinedDBConstantsPassValidation(): void
    {
        $defined = [
            NA_GEO_DB, NA_EDGE_DB, NA_ZIP_DB,
            NA_ISP_DB, NA_HOME_BIZ_DB, NA_ASN_DB, NA_LANGUAGE_DB, NA_PROXY_DB,
            NA_ISANISP_DB, NA_COMPANY_DB, NA_DOMAIN_16_DB, NA_DEMOGRAPHICS_DB,
            NA_NAICS_DB, NA_CBSA_DB, NA_PULSE_MAX_DB,
            NA_MOBILE_CARRIER_DB, NA_ORGANIZATION_DB, NA_PULSE_DB,
            NA_PULSE_PLUS_DB, NA_VPN_PROXY_DB, NA_IPC_DB,
            NA_NODIFY_DB, NA_OBSERVED_COUNTRIES_DB, NA_AA_SA1_DB, NA_AAMAX_SA1_DB,
            NA_AA_IRIS_DB, NA_AA_PLZ8_DB, NA_AA_ZIP4_DB, NA_AAMAX_ZIP4_DB,
            NA_AAMAX_IRIS_DB, NA_AAMAX_PLZ8_DB,
        ];

        foreach ($defined as $code) {
            $this->assertTrue(validateFeatureCode($code), "Expected defined DB code $code to pass validateFeatureCode");
        }
    }

    public function testReturnValueIsStrictlyBoolean(): void
    {
        $this->assertIsBool(validateFeatureCode(3));
        $this->assertIsBool(validateFeatureCode(1));
    }

    // -------------------------------------------------------------------------
    // ipsEqual
    // -------------------------------------------------------------------------

    public function testIdenticalIPv4StringsAreEqual(): void
    {
        $this->assertTrue(ipsEqual('192.0.2.1', '192.0.2.1'));
    }

    public function testDifferentIPv4StringsAreNotEqual(): void
    {
        $this->assertFalse(ipsEqual('192.0.2.1', '192.0.2.2'));
    }

    public function testIdenticalIPv6StringsAreEqual(): void
    {
        $this->assertTrue(ipsEqual('2001:db8::1', '2001:db8::1'));
    }

    public function testDifferentlyFormattedButEqualIPv6StringsAreEqual(): void
    {
        $this->assertTrue(ipsEqual('2001:db8::1', '2001:0db8:0000:0000:0000:0000:0000:0001'));
    }

    public function testDifferentIPv6StringsAreNotEqual(): void
    {
        $this->assertFalse(ipsEqual('2001:db8::1', '2001:db8::2'));
    }

    public function testIPv4AndIPv6AreNeverEqual(): void
    {
        $this->assertFalse(ipsEqual('192.0.2.1', '::ffff:192.0.2.1'));
    }

    public function testMalformedFirstArgumentIsNotEqualToAnything(): void
    {
        $this->assertFalse(ipsEqual('not-an-ip', '192.0.2.1'));
    }

    public function testMalformedSecondArgumentIsNotEqualToAnything(): void
    {
        $this->assertFalse(ipsEqual('192.0.2.1', 'not-an-ip'));
    }

    public function testBothArgumentsMalformedIsNotEqual(): void
    {
        $this->assertFalse(ipsEqual('not-an-ip', 'also-not-an-ip'));
    }

    public function testReturnValueIsStrictlyBooleanForIpsEqual(): void
    {
        $this->assertIsBool(ipsEqual('192.0.2.1', '192.0.2.1'));
        $this->assertIsBool(ipsEqual('192.0.2.1', 'not-an-ip'));
    }
}
