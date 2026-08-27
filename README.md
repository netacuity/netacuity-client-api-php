# NetAcuity Client API — PHP

A PHP client library for querying the [NetAcuity](https://www.digitalelement.com/solutions/netacuity/) Server for IP geolocation and intelligence data. Supports the XML UDP query protocol.

## Requirements

- The `ext-simplexml` PHP extension (enabled by default in most PHP distributions)
- **PHP** 8.1 or higher
- A running **NetAcuity Server** accessible on UDP port 5400
- An **API ID** (customer-provided integer, range 0–127; default 0)

## Installation / Build

```bash
git clone https://github.com/netacuity/netacuity-client-api-php.git
cd netacuity-client-api-php
```

In your project root, create a `composer.json` file with the following contents (replace the path with the absolute path to your local clone):

```json
{
    "require": {
        "netacuity/netacuity-client-api-php": "*"
    },
    "repositories": [
        {
            "type": "path",
            "url": "/absolute/path/to/netacuity-client-api-php"
        }
    ]
}
```

Then install:

```bash
composer install
```

## Quick Start

### XML UDP Query

The XML UDP protocol supports multiple feature codes in a single query.

```php
require 'vendor/autoload.php';

$serverIP       = '203.0.113.10'; // your NetAcuity Server address
$apiID          = 0;              // an API ID you choose, 0-127; default 0
$timeoutSeconds = 3;

$na = new NetAcuityUDP($serverIP, $apiID, $timeoutSeconds);

$queryIP = '192.0.2.1'; // the IP address to look up
$transactionID = random_int(0, 1000000000);
$response = $na->queryXml($queryIP, [NA_GEO_DB, NA_ISP_DB, NA_ASN_DB], $transactionID);

if ($response !== 0) {
    foreach ($response as $field => $value) {
        echo "$field = $value\n";
    }
} else {
    echo 'Error: ' . $na->getErrorMsg();
}
```

## API Reference

### `new NetAcuityUDP($serverIP, $apiID = 0, $timeoutSeconds = 2, $naServerPort = NA_UDP_PORT)`

Constructs a client for querying a NetAcuity Server over UDP.

| Parameter | Type | Description |
|---|---|---|
| `$serverIP` | string | IP address of the NetAcuity Server. |
| `$apiID` | int | API ID, 0–127; defaults to 0. |
| `$timeoutSeconds` | int | Socket timeout, in seconds; defaults to 2. |
| `$naServerPort` | int | UDP port to connect to; defaults to `NA_UDP_PORT` (5400). Override only if your NetAcuity Server listens on a non-default port. |

### `queryXml($ip, $featureCodes, $transactionID)`

Performs an XML UDP query for one or more feature codes in a single call. Returns an associative array of response fields on success, or `0` on error (see `getErrorMsg()`).

| Parameter | Type | Description |
|---|---|---|
| `$ip` | string | IP address to look up. |
| `$featureCodes` | array | Feature codes to query. |
| `$transactionID` | int | Caller-supplied transaction ID, echoed back by the server. |

### `getErrorMsg()`

Returns the error message from the most recent failed query, or an empty string if the last query succeeded.

### `getRawResponse()`

The raw, unparsed response text, available alongside the parsed fields.

### `getResponseSize()`

Returns the size, in bytes, of the most recent raw response.

### `debugOn()` / `debugOff()`

Enables or disables inline debug output.

### `setNaServerIPAddr($naServerIPAddr)` / `setApiID($apiID)` / `setTimeoutSeconds($timeoutSeconds)`

Update the server address, API ID, or socket timeout after construction.

## Feature Codes

For the complete, up-to-date list of feature codes and their response fields, see the [NetAcuity documentation](https://docs.netacuity.com/).

## Examples

Runnable examples are provided in the `examples/` directory:

```bash
php examples/XmlQueryExample.php <server_ip> <query_ip> <comma_separated_feature_codes>

# Query the GEO and ISP databases:
php examples/XmlQueryExample.php 203.0.113.10 192.0.2.1 3,8
```

## Running the Tests

```bash
./vendor/bin/phpunit
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## Support

Technical Support is only available to those under active contract with Digital Element. To contact Support, use the contact information provided at contract initiation.

- Documentation: [docs.netacuity.com](https://docs.netacuity.com/)
- Issues: [GitHub Issues](https://github.com/netacuity/netacuity-client-api-php/issues)

## License

Copyright 2026 Digital Envoy, Inc.

Licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for the full license text.

This repository contains no third-party source code or binaries. Its only runtime requirements are PHP itself and the bundled `ext-simplexml` extension; PHPUnit is a development-only dependency resolved by Composer and never shipped.
