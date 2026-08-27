<?php

/*****************************************************************************
 * File:        MockUdpServer.php
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
 * Description: Test-only helper that launches mock_udp_server_worker.php as
 *   a child process (via proc_open) bound to an OS-assigned ephemeral UDP
 *   port on 127.0.0.1. Pass port() as NetAcuityUDP's constructor $naServerPort
 *   argument to exercise queryXml() against it end-to-end, without needing a
 *   live NetAcuity server.
 *
 * Usage:
 *   $server = new MockUdpServer(["\x01\x02...packet bytes..."]);
 *   // ... point the class under test at 127.0.0.1:$server->port() ...
 *   $server->close();
 *
 ****************************************************************************/

class MockUdpServer
{
    /** @var resource */
    private $process;

    /** @var array<int, resource> */
    private array $pipes = [];

    private int $port = 0;

    /**
     * @param string[] $responsePackets      Raw byte strings to send back, in the
     *                                       order given, after the worker receives
     *                                       the one request datagram it is waiting
     *                                       for. Ignored (in the multi-packet sense)
     *                                       when $oneResponsePerRequest is true.
     * @param bool     $oneResponsePerRequest When true, each entry in $responsePackets
     *                                        answers a SEPARATE incoming request
     *                                        instead of all being sent back-to-back
     *                                        for a single request — for tests that
     *                                        reuse one client across multiple calls.
     */
    public function __construct(array $responsePackets, bool $oneResponsePerRequest = false)
    {
        $encoded = implode('|', array_map('base64_encode', $responsePackets));
        $script  = __DIR__ . '/mock_udp_server_worker.php';
        $mode    = $oneResponsePerRequest ? 'sequential' : 'batch';

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $this->process = proc_open([PHP_BINARY, $script, $encoded, $mode], $descriptors, $this->pipes);
        if (!is_resource($this->process)) {
            throw new RuntimeException('Failed to start mock UDP server worker process.');
        }

        // Block until the worker reports the port it bound. It prints this
        // immediately after binding, before it blocks on recvfrom, so this
        // returns as soon as the worker process has actually started.
        $line = fgets($this->pipes[1]);
        if ($line === false || trim($line) === '') {
            $this->close();
            throw new RuntimeException('Mock UDP server worker did not report a port.');
        }
        $this->port = (int) trim($line);
    }

    public function port(): int
    {
        return $this->port;
    }

    public function close(): void
    {
        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
        if (is_resource($this->process)) {
            proc_terminate($this->process);
            proc_close($this->process);
        }
    }
}
