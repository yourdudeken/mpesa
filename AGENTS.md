# Agent Guide: Mpesa SDK

High-signal guidance for working in this multi-language SDK repository.

## Project Structure
Polyglot repo — SDKs in `packages/node/` (TypeScript), `packages/python/` (Setuptools), `packages/go/` (Go module). Each is standalone; no cross-package dependencies or workspace orchestration.

## Developer Commands

### Node.js (`packages/node/`)
- **Build**: `npm run build` (runs `tsc`).
- **Test**: `npm test` (runs `jest`).
- **Entrypoint**: `src/index.ts`.
- **Note**: `package.json` devDependencies list only `typescript` and `@types/node`. `jest` / `ts-jest` / `@types/jest` must be installed separately before tests can run.

### Python (`packages/python/`)
- **Install**: `pip install -e .` (uses `setup.py`).
- **Test**: `pytest`.
- **Entrypoint**: `src/mpesa/__init__.py` and `src/mpesa/client.py`.
- **Dependencies**: `requests` and `cryptography` (only `requests` in `setup.py`; `cryptography` is listed in `pyproject.toml` but not `setup.py` — install via `pyproject.toml` or `pip install .` from the Python package root).

### Go (`packages/go/`)
- **Test**: `go test ./mpesa/...`
- **Module**: `github.com/yourdudeken/mpesa-sdk`
- **Constructor**: `New(config *Config)` in `client.go` OR `NewClient(config *Config)` expected by tests (neither `NewClient` nor an `auth` field on `Mpesa` exist yet — the Go SDK has dual/conflicting implementation approaches: a monolithic `client.go` and a modular refactored set in `services/`, `auth.go`, `config.go`, `utils.go` that the tests expect but the main client doesn't use).

## Naming Conventions Across Languages

| API | Node (camelCase) | Python (snake_case) | Go (CamelCase) |
|-----|------------------|----------------------|----------------|
| STK Push | `stkpush` | `stkpush` | `Stkpush` |
| STK Query | `stkquery` | `stkquery` | `Stkquery` |
| B2C | `b2c` | `b2c` | `B2c` |
| Validated B2C | `validated_b2c` | `validated_b2c` | `Validated_b2c` |
| B2B | `b2b` | `b2b` | `B2b` |
| C2B Register | `c2bregisterURLS` | `c2bregisterURLS` | `C2bregisterURLS` |
| C2B Simulate | `c2bsimulate` | `c2bsimulate` | `C2bsimulate` |
| Account Balance | `accountBalance` | `account_balance` | `AccountBalance` |
| Transaction Status | `transactionStatus` | `transaction_status` | `TransactionStatus` |
| Reversal | `reversal` | `reversal` | `Reversal` |
| B2 Pochi | `b2pochi` | `b2pochi` | `B2pochi` |

Python has a naming quirk: `c2bregisterURLS` uses the Node camelCase (not snake_case like the rest).

## Key Patterns
- **Phone validation**: strip `+`, replace leading `0` with `254`, prepend `254` to numbers starting with `7`. Consistent across all SDKs.
- **Static constants**: `PAYBILL = 'CustomerPayBillOnline'`, `TILL = 'CustomerBuyGoodsOnline'`. Same value in all languages.
- **Callback config keys**: All snake_case (`callback_url`, `b2c_result_url`, `status_result_url`, etc.) — consistent across SDKs.
- **Token auth**: OAuth client_credentials grant. B2C/B2B operations use separate `b2cConsumerKey`/`b2cConsumerSecret` if configured.
- **Security credential**: RSA-encrypted initiator password using Mpesa certificate (PKCS1v15 padding). Certificate paths differ per package.

## Certificate Locations
- Node: `packages/node/src/certificates/`
- Python: `packages/python/src/mpesa/certificates/`
- Go: `packages/go/` (root, not in subdirectory)

## Notable Gaps
- No CI workflows exist (`.github/workflows/` is empty).
- `examplea/` directory exists but is empty.
- Node.js tests require jest deps not listed in `devDependencies`.
- Python `setup.py` omits `cryptography` (present in `pyproject.toml` only).
- Go tests reference `NewClient()` and `client.auth` which don't compile against monolithic `client.go`.
