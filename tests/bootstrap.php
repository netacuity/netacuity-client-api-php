<?php

/*****************************************************************************
 * File:        bootstrap.php
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
 * Description:  PHPUnit bootstrap — loads all source files before tests run.
 *
 *****************************************************************************/

require_once __DIR__ . '/../src/NetAcuityDBDefs.php';
require_once __DIR__ . '/../src/QueryMethod.php';
require_once __DIR__ . '/../src/Utils.php';
require_once __DIR__ . '/../src/UDP/NetAcuityUDP.php';
require_once __DIR__ . '/MockUdpServer.php';
