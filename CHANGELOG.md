# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
This changelog starts at the initial public release on GitHub; changes prior to that are not tracked here.

## [7.0.0]

### Added
- Initial public release of the NetAcuity PHP Client API on GitHub.
- XML UDP query protocol, with a `validateIp()` helper (`Utils.php`) validating both the server address and query IP, transaction-ID validation, response-echo verification (transaction ID and IP) to reject spoofed or stale replies, and a 2-second default timeout.
- Apache License 2.0 (see [LICENSE](LICENSE) and [NOTICE](NOTICE)).
