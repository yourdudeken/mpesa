from mpesa.webhooks import WebhookManager


def create_flask_blueprint(webhook_manager: WebhookManager, secret: str = ""):
    try:
        from flask import Blueprint, abort, jsonify, request
    except ImportError:
        raise ImportError("flask is required. Install with: pip install yourdudeken-mpesa-sdk[flask]")

    bp = Blueprint("mpesa", __name__, url_prefix="/mpesa")

    @bp.route("/webhook", methods=["POST"])
    def handle_webhook():
        body = request.get_json(silent=True)
        if body is None:
            abort(400, description="Invalid JSON body")

        if secret:
            signature = request.headers.get("x-mpesa-signature", "")
            if not signature:
                abort(401, description="Missing signature")

        if body.get("Body", {}).get("stkCallback"):
            result = webhook_manager.parse_stk_callback(body)
            webhook_manager.emit("stk:callback", result)
        elif body.get("Result", {}).get("ResultParameters", {}).get("ResultParameter"):
            params = body["Result"]["ResultParameters"]["ResultParameter"]
            has_balance = any(p.get("Key") == "AccountBalance" for p in params)
            has_status = any(p.get("Key") == "TransactionStatus" for p in params)

            if has_balance:
                webhook_manager.emit("account:balance", body)
            elif has_status:
                webhook_manager.emit("transaction:status", body)
            else:
                webhook_manager.emit("b2c:result", body)
        elif body.get("TransactionType"):
            webhook_manager.emit("c2b:validation", body)
        else:
            abort(400, description="Unknown webhook event type")

        return jsonify({"received": True})

    return bp
