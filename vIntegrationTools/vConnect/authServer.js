const express = require('express');
const jwt = require('jsonwebtoken');
const { writeLog } = require('./log4connect');
const config = require('./config.json');
const bodyParser = require('body-parser');

const authRouter = express.Router();

// ✅ Middleware für URL-encoded Form-Daten
authRouter.use(bodyParser.urlencoded({ extended: true }));
authRouter.use(bodyParser.json());

const SECRET_KEY = config.oauthServer.secretKey;


// 🔑 **OAuth-Token-Endpoint** (Clients holen sich Token von hier)
authRouter.post('/token', (req, res) => {
    console.log("📥 Eingehender Content-Type:", req.headers['content-type']);
    console.log("📥 Eingehender Request Body (String):", JSON.stringify(req.body, null, 2));
    console.log("📥 Eingehender Request Body (JSON):", req.body);
    const { client_id, client_secret, grant_type } = req.body;

    if (grant_type !== "client_credentials") {
        writeLog('ERROR', `❌ Unsupported grant_type: ${grant_type}`);
        return res.status(400).json({ error: "unsupported_grant_type" });
    }

    // 🔹 Sicherstellen, dass `config.oauthServer` existiert
    if (!config.oauthServer || !config.oauthServer.clients) {
        writeLog('ERROR', `❌ Konfigurationsfehler: oauthServer fehlt in config.json!`);
        return res.status(500).json({ error: "server_error" });
    }

    // 🔹 Client validieren
    const client = config.oauthServer.clients.find(c => c.clientId === client_id && c.clientSecret === client_secret);
    
    if (!client) {
        writeLog('ERROR', `❌ Ungültige OAuth-Anfrage für Client: ${client_id}`);
        return res.status(401).json({ error: "invalid_client" });
    }

    // 🔹 Token erstellen
    const tokenPayload = {
        client_id: client.clientId,
        scope: client.scope,
        iat: Math.floor(Date.now() / 1000)  // Zeitstempel hinzufügen
    };

    const accessToken = jwt.sign(tokenPayload, SECRET_KEY, { expiresIn: client.expiresIn });

    writeLog('INFO', `✅ Token ausgestellt für ${client.clientId}`);
    res.json({
        access_token: accessToken,
        token_type: "bearer",
        expires_in: client.expiresIn
    });
});

module.exports = authRouter;
