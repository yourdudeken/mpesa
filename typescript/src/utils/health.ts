import { MpesaApiClient } from "../client/client.js";

export interface HealthResponse {
  status: "healthy" | "degraded" | "unhealthy";
  version: string;
  timestamp: string;
  tokenOk: boolean;
}

export async function createHealthCheck(client: MpesaApiClient): Promise<HealthResponse> {
  let tokenOk = false;
  try {
    await client.getAccessToken();
    tokenOk = true;
  } catch {
    tokenOk = false;
  }

  const status = tokenOk ? "healthy" : "degraded";

  return {
    status,
    version: "0.2.0",
    timestamp: new Date().toISOString(),
    tokenOk,
  };
}
