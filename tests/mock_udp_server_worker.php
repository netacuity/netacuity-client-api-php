<?php

/*****************************************************************************
 * File:        mock_udp_server_worker.php
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
 * Description: Standalone worker process spawned by tests/MockUdpServer.php
 *   (via proc_open) so PHP's synchronous, single-threaded fsockopen()/fread()
 *   client calls have something to talk to concurrently.
 *
 *   Binds a UDP socket to 127.0.0.1 on an OS-assigned ephemeral port, prints
 *   the bound port number to stdout (so the parent process can discover it),
 *   then serves the configured packets in one of two modes:
 *     - "batch" (default): waits for exactly one incoming datagram (to learn
 *       the client's address) and replies by sending each configured packet,
 *       in order, back-to-back to that address — used for single-request,
 *       multi-packet XML reassembly.
 *     - "sequential": treats each configured packet as the reply to a
 *       SEPARATE incoming request, waiting for a new datagram before sending
 *       each one — used for tests that reuse one client across multiple,
 *       independent calls.
 *   Exits once all packets are sent.
 *
 * Usage: php mock_udp_server_worker.php <base64-packets-joined-by-'|'> [batch|sequential]
 *
 ****************************************************************************/

$argPackets = $argv[1] ?? '';
$mode       = $argv[2] ?? 'batch';
$packets    = $argPackets === '' ? [] : array_map('base64_decode', explode('|', $argPackets));

$server = @stream_socket_server('udp://127.0.0.1:0', $errno, $errstr, STREAM_SERVER_BIND);
if (!$server) {
    fwrite(STDERR, "mock_udp_server_worker: bind failed: $errstr\n");
    exit(1);
}

$name = stream_socket_get_name($server, false);
$port = (int) substr(strrchr($name, ':'), 1);
echo $port . "\n";
fflush(STDOUT);

stream_set_timeout($server, 5);

if ($mode === 'sequential') {
    foreach ($packets as $packet) {
        $request = stream_socket_recvfrom($server, 65535, 0, $peer);
        if ($request === false || $peer === '') {
            exit(0);
        }
        stream_socket_sendto($server, $packet, 0, $peer);
    }
    exit(0);
}

$request = stream_socket_recvfrom($server, 65535, 0, $peer);
if ($request === false || $peer === '') {
    exit(0);
}

foreach ($packets as $packet) {
    stream_socket_sendto($server, $packet, 0, $peer);
}
