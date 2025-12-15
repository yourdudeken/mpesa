# M-Pesa API Gateway Architecture

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                          CLIENT APPLICATIONS                         │
│  (Web Apps, Mobile Apps, Third-party Services, Postman, cURL)      │
└────────────────────────────┬────────────────────────────────────────┘
                             │
                             │ HTTP/HTTPS Requests
                             │ (JSON)
                             ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         API GATEWAY LAYER                            │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │                      index.php (Entry Point)                   │  │
│  └───────────────────────────┬───────────────────────────────────┘  │
│                              │                                       │
│  ┌───────────────────────────▼───────────────────────────────────┐  │
│  │                    MIDDLEWARE STACK                            │  │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐    │  │
│  │  │ CORS         │→ │ Rate Limit   │→ │ Authentication   │    │  │
│  │  │ Middleware   │  │ Middleware   │  │ Middleware       │    │  │
│  │  └──────────────┘  └──────────────┘  └──────────────────┘    │  │
│  └───────────────────────────┬───────────────────────────────────┘  │
│                              │                                       │
│  ┌───────────────────────────▼───────────────────────────────────┐  │
│  │                         ROUTER                                 │  │
│  │  • Route Matching                                              │  │
│  │  • Parameter Extraction                                        │  │
│  │  • Controller Dispatch                                         │  │
│  └───────────────────────────┬───────────────────────────────────┘  │
│                              │                                       │
│  ┌───────────────────────────▼───────────────────────────────────┐  │
│  │                      CONTROLLERS                               │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐   │  │
│  │  │ STKPush     │  │ C2B         │  │ B2C                 │   │  │
│  │  │ Controller  │  │ Controller  │  │ Controller          │   │  │
│  │  └─────────────┘  └─────────────┘  └─────────────────────┘   │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐   │  │
│  │  │ B2B         │  │ Account     │  │ Transaction         │   │  │
│  │  │ Controller  │  │ Controller  │  │ Controller          │   │  │
│  │  └─────────────┘  └─────────────┘  └─────────────────────┘   │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐   │  │
│  │  │ Callback    │  │ Health      │  │ Docs                │   │  │
│  │  │ Controller  │  │ Controller  │  │ Controller          │   │  │
│  │  └─────────────┘  └─────────────┘  └─────────────────────┘   │  │
│  └───────────────────────────┬───────────────────────────────────┘  │
└────────────────────────────┬─┴───────────────────────────────────────┘
                             │
                             │ Uses
                             ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      M-PESA PACKAGE LAYER                            │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │                    Yourdudeken\Mpesa\Init                      │  │
│  └───────────────────────────┬───────────────────────────────────┘  │
│                              │                                       │
│  ┌───────────────────────────▼───────────────────────────────────┐  │
│  │                      ENGINE CORE                               │  │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────┐  │  │
│  │  │ Config   │  │ Cache    │  │ Curl     │  │ Validator    │  │  │
│  │  │ Manager  │  │ Manager  │  │ Request  │  │              │  │  │
│  │  └──────────┘  └──────────┘  └──────────┘  └──────────────┘  │  │
│  └───────────────────────────┬───────────────────────────────────┘  │
│                              │                                       │
│  ┌───────────────────────────▼───────────────────────────────────┐  │
│  │                   AUTHENTICATION                               │  │
│  │  • OAuth Token Generation                                      │  │
│  │  • Token Caching                                               │  │
│  │  • Credential Management                                       │  │
│  └───────────────────────────┬───────────────────────────────────┘  │
│                              │                                       │
│  ┌───────────────────────────▼───────────────────────────────────┐  │
│  │                   M-PESA SERVICES                              │  │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐    │  │
│  │  │ STK Push     │  │ C2B          │  │ B2C              │    │  │
│  │  │ Service      │  │ Service      │  │ Service          │    │  │
│  │  └──────────────┘  └──────────────┘  └──────────────────┘    │  │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐    │  │
│  │  │ B2B          │  │ Balance      │  │ Transaction      │    │  │
│  │  │ Service      │  │ Service      │  │ Status           │    │  │
│  │  └──────────────┘  └──────────────┘  └──────────────────┘    │  │
│  │  ┌──────────────┐                                             │  │
│  │  │ Reversal     │                                             │  │
│  │  │ Service      │                                             │  │
│  │  └──────────────┘                                             │  │
│  └───────────────────────────┬───────────────────────────────────┘  │
└────────────────────────────┬─┴───────────────────────────────────────┘
                             │
                             │ HTTPS Requests
                             ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    SAFARICOM M-PESA API                              │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │  • OAuth API                                                   │  │
│  │  • STK Push API                                                │  │
│  │  • C2B API                                                     │  │
│  │  • B2C API                                                     │  │
│  │  • B2B API                                                     │  │
│  │  • Account Balance API                                         │  │
│  │  • Transaction Status API                                      │  │
│  │  • Reversal API                                                │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                      │
│  Callbacks ◄─────────────────────────────────────────────────────┐  │
└──────────────────────────────────────────────────────────────────┼──┘
                                                                   │
                                                                   │
┌──────────────────────────────────────────────────────────────────▼──┐
│                      CALLBACK HANDLER                                │
│  • Receives M-Pesa callbacks                                         │
│  • Logs callback data                                                │
│  • Processes results                                                 │
│  • Sends acknowledgment                                              │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                         STORAGE LAYER                                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────────┐  │
│  │ Logs         │  │ Cache        │  │ Rate Limits              │  │
│  │ • transactions│  │ • OAuth      │  │ • IP tracking            │  │
│  │ • callbacks  │  │   tokens     │  │ • Request counting       │  │
│  │ • errors     │  │              │  │                          │  │
│  └──────────────┘  └──────────────┘  └──────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

## Request Flow

### 1. STK Push Request Flow

```
Client → API Gateway → Middleware → Router → STKPushController
                                                      │
                                                      ▼
                                            M-Pesa Package (Init)
                                                      │
                                                      ▼
                                            Authenticator (Get Token)
                                                      │
                                                      ▼
                                            STKPush Service
                                                      │
                                                      ▼
                                            Validator (Validate Input)
                                                      │
                                                      ▼
                                            Core Engine (Make Request)
                                                      │
                                                      ▼
                                            Safaricom M-Pesa API
                                                      │
                                                      ▼
                                            Response ← ← ← ← ← Client
```

### 2. Callback Flow

```
Safaricom M-Pesa API → Callback URL (/api/v1/callbacks/*)
                              │
                              ▼
                      CallbackController
                              │
                              ├─→ Log to callbacks.log
                              │
                              ├─→ Process callback data
                              │
                              └─→ Send acknowledgment
```

## Data Flow

### Request Data Flow
```
JSON Request → Request Parser → Validator → Controller → M-Pesa Package
                                                              │
                                                              ▼
                                                    Format Parameters
                                                              │
                                                              ▼
                                                    Authenticate (OAuth)
                                                              │
                                                              ▼
                                                    Make HTTP Request
                                                              │
                                                              ▼
                                                    Parse Response
                                                              │
                                                              ▼
JSON Response ← Response Formatter ← Logger ← Controller ← Package
```

## Security Layers

```
┌─────────────────────────────────────────────────────────────┐
│ Layer 1: CORS Middleware                                    │
│ • Validates origin                                          │
│ • Sets CORS headers                                         │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│ Layer 2: Rate Limiting                                      │
│ • Tracks requests per IP                                    │
│ • Enforces rate limits                                      │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│ Layer 3: API Key Authentication                             │
│ • Validates API key                                         │
│ • Checks authorization                                      │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│ Layer 4: Input Validation                                   │
│ • Validates request data                                    │
│ • Sanitizes inputs                                          │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│ Layer 5: M-Pesa OAuth                                       │
│ • Authenticates with M-Pesa                                 │
│ • Manages access tokens                                     │
└─────────────────────────────────────────────────────────────┘
```

## Component Interaction

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Router     │────▶│  Controller  │────▶│   M-Pesa     │
│              │     │              │     │   Package    │
└──────────────┘     └──────────────┘     └──────────────┘
       │                    │                     │
       │                    │                     │
       ▼                    ▼                     ▼
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Request    │     │   Response   │     │   Logger     │
│   Handler    │     │   Formatter  │     │              │
└──────────────┘     └──────────────┘     └──────────────┘
```

## Technology Stack

```
┌─────────────────────────────────────────────────────────────┐
│ Frontend Layer                                              │
│ • Any HTTP Client (Web, Mobile, Desktop)                    │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│ API Gateway Layer                                           │
│ • PHP 7.4+                                                  │
│ • Custom Router                                             │
│ • Middleware Stack                                          │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│ Business Logic Layer                                        │
│ • M-Pesa Package (Yourdudeken\Mpesa)                       │
│ • Service Classes                                           │
│ • Validation Engine                                         │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│ External API Layer                                          │
│ • Safaricom M-Pesa API                                      │
│ • OAuth 2.0                                                 │
│ • HTTPS/TLS                                                 │
└─────────────────────────────────────────────────────────────┘
```

---

**Legend:**
- `→` : Data flow direction
- `▼` : Sequential flow
- `│` : Connection/Relationship
- `┌─┐` : Component boundary
