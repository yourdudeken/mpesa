import type { FastifyInstance, FastifyRequest, FastifyReply } from "fastify";
import type { WebhookManager, WebhookEvent } from "../webhooks/index.js";

export interface FastifyMpesaWebhookOptions {
  webhookManager: WebhookManager;
  path?: string;
  verifySignature?: boolean;
  secret?: string;
}

export function createFastifyPlugin(
  options: FastifyMpesaWebhookOptions,
): (fastify: FastifyInstance) => Promise<void> {
  return async (fastify: FastifyInstance) => {
    const path = options.path ?? "/mpesa/webhook";

    fastify.post(path, async (request: FastifyRequest, reply: FastifyReply) => {
      const body = request.body as Record<string, unknown>;

      if (options.verifySignature && options.secret) {
        const signature = request.headers["x-mpesa-signature"] as string;
        if (!signature) {
          return reply.status(401).send({ error: "Missing signature" });
        }
      }

      let event: WebhookEvent;

      if ((body as any)?.Body?.stkCallback) {
        event = { type: "stk:callback" as const, payload: body as any };
      } else if ((body as any)?.Result?.ResultParameters?.ResultParameter) {
        const resultParams = (body as any).Result.ResultParameters.ResultParameter;
        const hasAccountBalance = resultParams.some(
          (p: { Key: string }) => p.Key === "AccountBalance",
        );
        const hasTransactionStatus = resultParams.some(
          (p: { Key: string }) => p.Key === "TransactionStatus",
        );

        if (hasAccountBalance) {
          event = { type: "account:balance" as const, payload: body as any };
        } else if (hasTransactionStatus) {
          event = { type: "transaction:status" as const, payload: body as any };
        } else {
          event = { type: "b2c:result" as const, payload: body as any };
        }
      } else if ((body as any)?.TransactionType) {
        event = { type: "c2b:validation" as const, payload: body as any };
      } else {
        return reply.status(400).send({ error: "Unknown webhook event type" });
      }

      await options.webhookManager.handleEvent(event);
      return reply.status(200).send({ received: true });
    });
  };
}
