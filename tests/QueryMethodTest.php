<?php

/*****************************************************************************
 * File:        QueryMethodTest.php
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
 * Description:  Tests for the abstract QueryMethod base class.
 *
 *****************************************************************************/

use PHPUnit\Framework\TestCase;

/**
 * Concrete stand-in for the abstract QueryMethod, exposing protected helpers.
 */
class ConcreteQueryMethod extends QueryMethod
{
    public function queryXml($ip, $featureCodes, $transactionID): array { return []; }

    public function callSetDefault(): void   { $this->setDefault(); }
    public function callLogError(string $msg): void { $this->logError($msg); }
    public function callDebug(string $msg): void    { $this->debug($msg); }
}

class QueryMethodTest extends TestCase
{
    private ConcreteQueryMethod $qm;

    protected function setUp(): void
    {
        $this->qm = new ConcreteQueryMethod();
    }

    // -------------------------------------------------------------------------
    // Constructor defaults
    // -------------------------------------------------------------------------

    public function testConstructorSetsResponseSizeToStringTwo(): void
    {
        // The constructor explicitly stores "2" (string) as the initial responseSize
        $this->assertSame('2', $this->qm->getResponseSize());
    }

    public function testConstructorSetsRawResponseToEmptyString(): void
    {
        $this->assertSame('', $this->qm->getRawResponse());
    }

    public function testConstructorSetsDebugOffByDefault(): void
    {
        $this->assertFalse($this->qm->debugOn);
    }

    public function testConstructorSetsErrorMsgToEmptyString(): void
    {
        $this->assertSame('', $this->qm->getErrorMsg());
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    public function testGetRawResponseReturnsCurrentValue(): void
    {
        $this->qm->rawResponse = 'hello';
        $this->assertSame('hello', $this->qm->getRawResponse());
    }

    public function testGetResponseSizeReturnsCurrentValue(): void
    {
        $this->qm->responseSize = 99;
        $this->assertSame(99, $this->qm->getResponseSize());
    }

    public function testGetErrorMsgReturnsCurrentValue(): void
    {
        $this->qm->errorMsg = 'something went wrong';
        $this->assertSame('something went wrong', $this->qm->getErrorMsg());
    }

    // -------------------------------------------------------------------------
    // debugOn / debugOff
    // -------------------------------------------------------------------------

    public function testDebugOnSetsFlag(): void
    {
        $this->qm->debugOn();
        $this->assertTrue($this->qm->debugOn);
    }

    public function testDebugOffClearsFlag(): void
    {
        $this->qm->debugOn();
        $this->qm->debugOff();
        $this->assertFalse($this->qm->debugOn);
    }

    public function testDebugOnThenOffIsOff(): void
    {
        $this->qm->debugOn();
        $this->qm->debugOff();
        $this->assertFalse($this->qm->debugOn);
    }

    // -------------------------------------------------------------------------
    // setDefault
    // -------------------------------------------------------------------------

    public function testSetDefaultResetsResponseSizeToZero(): void
    {
        $this->qm->responseSize = 999;
        $this->qm->callSetDefault();
        $this->assertSame(0, $this->qm->responseSize);
    }

    public function testSetDefaultResetsRawResponseToEmpty(): void
    {
        $this->qm->rawResponse = 'some data';
        $this->qm->callSetDefault();
        $this->assertSame('', $this->qm->rawResponse);
    }

    public function testSetDefaultResetsErrorMsgToEmpty(): void
    {
        $this->qm->errorMsg = 'prior error';
        $this->qm->callSetDefault();
        $this->assertSame('', $this->qm->errorMsg);
    }

    public function testSetDefaultDoesNotAffectDebugFlag(): void
    {
        $this->qm->debugOn();
        $this->qm->callSetDefault();
        $this->assertTrue($this->qm->debugOn);
    }

    // -------------------------------------------------------------------------
    // logError
    // -------------------------------------------------------------------------

    public function testLogErrorSetsErrorMsg(): void
    {
        $this->qm->callLogError('test error message');
        $this->assertSame('test error message', $this->qm->getErrorMsg());
    }

    public function testLogErrorOverwritesPreviousError(): void
    {
        $this->qm->callLogError('first error');
        $this->qm->callLogError('second error');
        $this->assertSame('second error', $this->qm->getErrorMsg());
    }

    public function testLogErrorWithEmptyStringClearsMsg(): void
    {
        $this->qm->callLogError('some error');
        $this->qm->callLogError('');
        $this->assertSame('', $this->qm->getErrorMsg());
    }

    public function testLogErrorEchoesWhenDebugIsOn(): void
    {
        $this->qm->debugOn();
        ob_start();
        $this->qm->callLogError('visible error');
        $output = ob_get_clean();
        $this->assertStringContainsString('visible error', $output);
    }

    public function testLogErrorDoesNotEchoWhenDebugIsOff(): void
    {
        $this->qm->debugOff();
        ob_start();
        $this->qm->callLogError('silent error');
        $output = ob_get_clean();
        $this->assertSame('', $output);
    }

    // -------------------------------------------------------------------------
    // debug (protected echo helper)
    // -------------------------------------------------------------------------

    public function testDebugHelperEchoesWhenFlagIsOn(): void
    {
        $this->qm->debugOn();
        ob_start();
        $this->qm->callDebug('debug message');
        $output = ob_get_clean();
        $this->assertSame('debug message', $output);
    }

    public function testDebugHelperSilentWhenFlagIsOff(): void
    {
        $this->qm->debugOff();
        ob_start();
        $this->qm->callDebug('should not appear');
        $output = ob_get_clean();
        $this->assertSame('', $output);
    }
}
