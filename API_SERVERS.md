# M-Pesa API Server Scripts

This directory contains scripts to manage the M-Pesa API development servers.

## Scripts

### `start-api.sh` - Start Both API Servers

Starts both production and sandbox API servers simultaneously on different ports.

**Usage:**
```bash
./start-api.sh
```

**What it does:**
1. ✅ Checks PHP installation and version
2. ✅ Sets up both production and sandbox environments
3. ✅ Installs dependencies if needed (composer install)
4. ✅ Creates necessary directories (cache, logs)
5. ✅ Copies .env.example to .env if needed
6. ✅ Starts Production API on port **8000**
7. ✅ Starts Sandbox API on port **8001**
8. ✅ Displays server information and URLs

**Server Information:**

| Environment | Port | Base URL | Health Check |
|-------------|------|----------|--------------|
| **Production** | 8000 | http://localhost:8000/api | http://localhost:8000/api/health |
| **Sandbox** | 8001 | http://localhost:8001/api | http://localhost:8001/api/health |

**Logs:**
- Production: `production/logs/api-production.log`
- Sandbox: `sandbox/logs/api-sandbox.log`

**To Stop:**
- Press `Ctrl+C` in the terminal running the script
- Or use `./stop-api.sh` from another terminal

### `stop-api.sh` - Stop Both API Servers

Stops both production and sandbox API servers.

**Usage:**
```bash
./stop-api.sh
```

**What it does:**
1. Finds processes running on port 8000 (Production)
2. Finds processes running on port 8001 (Sandbox)
3. Stops both servers gracefully

## Quick Start

1. **Start both servers:**
   ```bash
   ./start-api.sh
   ```

2. **Test Production API:**
   ```bash
   curl http://localhost:8000/api/health
   ```

3. **Test Sandbox API:**
   ```bash
   curl http://localhost:8001/api/health
   ```

4. **Stop servers:**
   ```bash
   # Press Ctrl+C in the terminal running start-api.sh
   # OR
   ./stop-api.sh
   ```

## Environment Configuration

Each environment has its own configuration:

- **Production:** `production/api/.env`
- **Sandbox:** `sandbox/api/.env`

Make sure to update these files with your M-Pesa API credentials before starting the servers.

## Default API Key

Both servers use the default API key: `demo-api-key-12345`

Update this in the respective `.env` files for production use.

## Troubleshooting

### Port Already in Use

If you see an error about ports being in use:

```bash
# Check what's using the ports
lsof -i:8000
lsof -i:8001

# Stop the servers
./stop-api.sh
```

### Dependencies Not Installed

If dependencies are missing:

```bash
cd production && composer install
cd ../sandbox && composer install
```

### Permission Denied

If you get permission errors:

```bash
chmod +x start-api.sh stop-api.sh
```

## Features

- ✅ **Dual Environment Support** - Run production and sandbox simultaneously
- ✅ **Automatic Setup** - Installs dependencies and creates directories
- ✅ **Separate Logs** - Each environment has its own log file
- ✅ **Graceful Shutdown** - Properly stops both servers on Ctrl+C
- ✅ **Process Management** - Tracks PIDs for clean shutdown
- ✅ **Health Checks** - Built-in health check endpoints

## See Also

- [SETUP.md](SETUP.md) - Complete setup guide
- [TESTING.md](TESTING.md) - Testing guide
- [production/README.md](production/README.md) - Production environment
- [sandbox/README.md](sandbox/README.md) - Sandbox environment
