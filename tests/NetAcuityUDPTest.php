<?php

/*****************************************************************************
 * File:        NetAcuityUDPTest.php
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
 * Description:  Tests for the NetAcuityUDP class.
 *
 *****************************************************************************/

use PHPUnit\Framework\TestCase;

class NetAcuityUDPTest extends TestCase
{
    private NetAcuityUDP $udp;
    private ?MockUdpServer $mockServer = null;

    protected function setUp(): void
    {
        $this->udp = new NetAcuityUDP('192.0.2.0', 76, 3);
    }

    protected function tearDown(): void
    {
        if ($this->mockServer !== null) {
            $this->mockServer->close();
            $this->mockServer = null;
        }
    }

    // -------------------------------------------------------------------------
    // Helpers — private method invocation with correct by-reference semantics
    // -------------------------------------------------------------------------

    /**
     * Calls the private parseXMLResponse method and returns both the boolean
     * result and the populated responses array.
     *
     * @return array{result: bool, responses: array}
     */
    private function callParseXMLResponse(string $xmlResponse): array
    {
        $responses = [];
        $result    = null;
        Closure::bind(
            function () use (&$xmlResponse, &$responses, &$result) {
                $result = $this->parseXMLResponse($xmlResponse, $responses);
            },
            $this->udp,
            NetAcuityUDP::class
        )();
        return ['result' => $result, 'responses' => $responses];
    }

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    public function testConstructorStoresServerIPAddress(): void
    {
        $this->assertSame('192.0.2.0', $this->udp->naServerIPAddr);
    }

    public function testConstructorStoresApiID(): void
    {
        $this->assertSame(76, $this->udp->apiID);
    }

    public function testConstructorStoresTimeoutSeconds(): void
    {
        $this->assertSame(3, $this->udp->timeoutSeconds);
    }

    public function testConstructorInheritsParentDefaults(): void
    {
        // Parent constructor sets responseSize = "2" and rawResponse = ""
        $this->assertSame('2', $this->udp->getResponseSize());
        $this->assertSame('', $this->udp->getRawResponse());
        $this->assertSame('', $this->udp->getErrorMsg());
    }

    // -------------------------------------------------------------------------
    // Setters
    // -------------------------------------------------------------------------

    public function testSetNaServerIPAddr(): void
    {
        $this->udp->setNaServerIPAddr('198.51.100.1');
        $this->assertSame('198.51.100.1', $this->udp->naServerIPAddr);
    }

    public function testSetApiID(): void
    {
        $this->udp->setApiID(99);
        $this->assertSame(99, $this->udp->apiID);
    }

    public function testSetTimeoutSeconds(): void
    {
        $this->udp->setTimeoutSeconds(10);
        $this->assertSame(10, $this->udp->timeoutSeconds);
    }

    // -------------------------------------------------------------------------
    // queryXml — input validation (validation runs before fsockopen)
    // -------------------------------------------------------------------------

    public function testQueryXmlWithInvalidDbInArrayReturnsZero(): void
    {
        $result = $this->udp->queryXml('192.0.2.1', ['notadb'], 1);
        $this->assertSame(0, $result);
    }

    public function testQueryXmlWithInvalidDbSetsError(): void
    {
        $this->udp->queryXml('192.0.2.1', ['notadb'], 1);
        $this->assertNotEmpty($this->udp->getErrorMsg());
    }

    public function testQueryXmlWithDbBelowMinInArrayReturnsZero(): void
    {
        $result = $this->udp->queryXml('192.0.2.1', [2], 1);
        $this->assertSame(0, $result);
    }

    public function testQueryXmlWithDb500InArrayReturnsZero(): void
    {
        $result = $this->udp->queryXml('192.0.2.1', [500], 1);
        $this->assertSame(0, $result);
    }

    public function testQueryXmlWithMixedValidAndInvalidDbReturnsZero(): void
    {
        // First DB is valid, second is not — validation must reject the whole request.
        $result = $this->udp->queryXml('192.0.2.1', [3, 'bad'], 1);
        $this->assertSame(0, $result);
    }

    public function testQueryXmlValidationClearsRawResponseViaSetDefault(): void
    {
        // queryXml calls setDefault() before validation, so rawResponse is
        // always cleared even on a validation failure.
        $this->udp->rawResponse = 'previous data';
        $this->udp->queryXml('192.0.2.1', ['bad'], 1);
        $this->assertSame('', $this->udp->getRawResponse());
    }

    // -------------------------------------------------------------------------
    // parseXMLResponse (private) — tested via callParseXMLResponse helper
    // -------------------------------------------------------------------------

    public function testParseXMLResponseReturnsTrueForValidXml(): void
    {
        $out = $this->callParseXMLResponse('<response trans-id="42" country="usa" region="ca" error="" />');
        $this->assertTrue($out['result']);
    }

    public function testParseXMLResponsePopulatesResponseArray(): void
    {
        $out = $this->callParseXMLResponse('<response trans-id="42" country="deu" region="by" error="" />');

        $this->assertEquals('deu', (string) $out['responses']['country']);
        $this->assertEquals('by', (string) $out['responses']['region']);
        $this->assertEquals('42', (string) $out['responses']['trans-id']);
    }

    public function testParseXMLResponseWithEmptyErrorAttributeReturnsTrueAndDoesNotSetError(): void
    {
        $out = $this->callParseXMLResponse('<response error="" country="jpn" />');

        $this->assertTrue($out['result']);
        // errorMsg is set to the SimpleXMLElement for the empty "error" attribute;
        // cast to string for comparison.
        $this->assertEquals('', (string) $this->udp->getErrorMsg());
    }

    public function testParseXMLResponseReturnsFalseWhenErrorAttributeIsSet(): void
    {
        $out = $this->callParseXMLResponse('<response error="Database not loaded" />');
        $this->assertFalse($out['result']);
    }

    public function testParseXMLResponseSetsErrorMsgFromAttribute(): void
    {
        $this->callParseXMLResponse('<response error="Database not loaded" />');
        $this->assertEquals('Database not loaded', (string) $this->udp->getErrorMsg());
    }

    public function testParseXMLResponseWithNoErrorAttributeDoesNotSetErrorMsg(): void
    {
        // No "error" attribute — errorMsg stays as the constructor default ("").
        $out = $this->callParseXMLResponse('<response country="fra" />');

        $this->assertTrue($out['result']);
        $this->assertSame('', $this->udp->getErrorMsg());
    }

    public function testParseXMLResponseErrorAttributeAlsoAppearsInResponsesArray(): void
    {
        $out = $this->callParseXMLResponse('<response error="bad input" />');

        $this->assertArrayHasKey('error', $out['responses']);
        $this->assertEquals('bad input', (string) $out['responses']['error']);
    }

    // -------------------------------------------------------------------------
    // Error-state management
    // -------------------------------------------------------------------------

    public function testGetErrorMsgAfterValidationFailureIsNonEmpty(): void
    {
        $this->udp->queryXml('192.0.2.1', [0], 1);
        $this->assertNotEmpty($this->udp->getErrorMsg());
    }

    public function testQueryXmlAlwaysCallsSetDefaultBeforeValidation(): void
    {
        // Seed a prior error and responseSize, then trigger a validation error in queryXml.
        $this->udp->errorMsg = 'old error';
        $this->udp->responseSize = 999;

        $this->udp->queryXml('192.0.2.1', ['invalid'], 1);

        // setDefault() runs at the start of queryXml — responseSize resets to 0.
        $this->assertSame(0, $this->udp->responseSize);
        // errorMsg is then overwritten by logError inside lookupDBs.
        $this->assertNotEmpty($this->udp->getErrorMsg());
        $this->assertNotSame('old error', $this->udp->getErrorMsg());
    }

    // -------------------------------------------------------------------------
    // Network-level round trips via MockUdpServer (real UDP socket, ephemeral
    // port) — exercises queryXml() end-to-end, including request construction,
    // transaction-ID/IP echo verification, and multi-packet reassembly and
    // ordering.
    // -------------------------------------------------------------------------

    private function startMockServer(array $packets): MockUdpServer
    {
        $this->mockServer = new MockUdpServer($packets);
        return $this->mockServer;
    }

    private function makeXmlPacket(string $packetNum, string $totalPackets, string $content): string
    {
        // 2-digit packet number + 2-digit total-packet count + content + 1
        // trailing filler byte, matching what NetAcuityUDP::lookupDBs() expects
        // (it strips the last byte of every received packet via substr(...,4,-1)).
        return $packetNum . $totalPackets . $content . "\x00";
    }

    public function testQueryXmlRoundTripSinglePacketReturnsParsedAttributes(): void
    {
        $xml    = '<response trans-id="tx1" ip="192.0.2.1" error="" country="usa" region="ca" />';
        $server = $this->startMockServer([$this->makeXmlPacket('01', '01', $xml)]);

        $udp = new NetAcuityUDP('127.0.0.1', 5, 3, $server->port());

        $result = $udp->queryXml('192.0.2.1', [NA_GEO_DB], 'tx1');

        $this->assertSame('usa', (string) $result['country']);
        $this->assertSame('ca',  (string) $result['region']);
        $this->assertSame('',    (string) $udp->getErrorMsg());
    }

    public function testQueryXmlRoundTripReassemblesMultiplePackets(): void
    {
        $xml  = '<response trans-id="tx1" ip="192.0.2.1" error="" country="usa" region="ca" />';
        $half = (int) (strlen($xml) / 2);
        $pkt1 = $this->makeXmlPacket('01', '02', substr($xml, 0, $half));
        $pkt2 = $this->makeXmlPacket('02', '02', substr($xml, $half));

        $server = $this->startMockServer([$pkt1, $pkt2]);

        $udp = new NetAcuityUDP('127.0.0.1', 5, 3, $server->port());

        $result = $udp->queryXml('192.0.2.1', [NA_GEO_DB], 'tx1');

        $this->assertSame('usa', (string) $result['country']);
        $this->assertSame('ca',  (string) $result['region']);
    }

    public function testQueryXmlWithMismatchedTransactionIdIsRejected(): void
    {
        $xml    = '<response trans-id="WRONG" ip="192.0.2.1" error="" country="usa" />';
        $server = $this->startMockServer([$this->makeXmlPacket('01', '01', $xml)]);

        $udp = new NetAcuityUDP('127.0.0.1', 5, 3, $server->port());

        $result = $udp->queryXml('192.0.2.1', [NA_GEO_DB], 'tx1');

        $this->assertSame(0, $result);
        $this->assertStringContainsString('Transaction ID', $udp->getErrorMsg());
    }

    public function testQueryXmlWithMismatchedIpIsRejected(): void
    {
        $xml    = '<response trans-id="tx1" ip="203.0.113.9" error="" country="usa" />';
        $server = $this->startMockServer([$this->makeXmlPacket('01', '01', $xml)]);

        $udp = new NetAcuityUDP('127.0.0.1', 5, 3, $server->port());

        $result = $udp->queryXml('192.0.2.1', [NA_GEO_DB], 'tx1');

        $this->assertSame(0, $result);
        $this->assertStringContainsString('address does not match', $udp->getErrorMsg());
    }

    public function testQueryXmlWithOutOfOrderPacketsIsRejected(): void
    {
        $xml  = '<response trans-id="tx1" ip="192.0.2.1" error="" country="usa" region="ca" />';
        $half = (int) (strlen($xml) / 2);
        $pkt1 = $this->makeXmlPacket('01', '02', substr($xml, 0, $half));
        $pkt2 = $this->makeXmlPacket('02', '02', substr($xml, $half));

        // Send packet 2 before packet 1.
        $server = $this->startMockServer([$pkt2, $pkt1]);

        $udp = new NetAcuityUDP('127.0.0.1', 5, 3, $server->port());

        $result = $udp->queryXml('192.0.2.1', [NA_GEO_DB], 'tx1');

        $this->assertSame(0, $result);
        $this->assertStringContainsString('out of order', $udp->getErrorMsg());
    }

    // -------------------------------------------------------------------------
    // Validation guards — integration through the public entry points; asserts
    // a fast rejection with no network call.
    // -------------------------------------------------------------------------

    public function testQueryXmlWithOutOfRangeApiIdIsRejectedWithoutNetworkCall(): void
    {
        $udp = new NetAcuityUDP('192.0.2.0', 999, 5);

        $start   = microtime(true);
        $result  = $udp->queryXml('192.0.2.1', [NA_GEO_DB], 'txn1');
        $elapsed = microtime(true) - $start;

        $this->assertSame(0, $result);
        $this->assertStringContainsString('API ID', $udp->getErrorMsg());
        $this->assertLessThan(1.0, $elapsed, 'queryXml should reject before attempting any network I/O');
    }

    public function testQueryXmlWithInvalidTransactionIdIsRejectedWithoutNetworkCall(): void
    {
        $udp = new NetAcuityUDP('192.0.2.0', 76, 5);

        $start   = microtime(true);
        $result  = $udp->queryXml('192.0.2.1', [NA_GEO_DB], 'bad"id');
        $elapsed = microtime(true) - $start;

        $this->assertSame(0, $result);
        $this->assertStringContainsString('transaction ID', $udp->getErrorMsg());
        $this->assertLessThan(1.0, $elapsed, 'queryXml should reject before attempting any network I/O');
    }

    // -------------------------------------------------------------------------
    // UDP port and packet-size constants
    // -------------------------------------------------------------------------

    public function testNAUDPPortConstant(): void
    {
        $this->assertSame(5400, NA_UDP_PORT);
    }

    public function testMaxPacketSizeConstant(): void
    {
        $this->assertSame(1500, MAX_PACKET_SIZE);
    }
}
