const axios = require('axios');
const { writeLog } = require('./log4connect');

const tokenCache = {};

async function getToken(task) {
  if (!task.fileToHttp || !task.fileToHttp.oauth || !task.fileToHttp.oauth.tokenUrl) {
    console.log(`❌ OAuth-Konfiguration fehlt oder ist ungültig! OAuth: ${JSON.stringify(task.fileToHttp, null, 2)}`);
    writeLog('ERROR', `❌ OAuth-Konfiguration fehlt oder ist ungültig! OAuth: ${JSON.stringify(task.fileToHttp, null, 2)}`);
    throw new Error("OAuth-Token-URL fehlt in der Konfiguration.");
  }

  const oauth = task.fileToHttp.oauth; // Hier die korrekten OAuth-Daten extrahieren

  try {
    writeLog('DEBUG', `🔑 Token anfordern von: ${oauth.tokenUrl}`);
    console.log(`🔑 Token anfordern von: ${oauth.tokenUrl}`);

    const response = await axios.post(oauth.tokenUrl, new URLSearchParams({
      grant_type: 'client_credentials',
      client_id: oauth.clientId,
      client_secret: oauth.clientSecret,
    }), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    });

    console.log("✅ Erfolgreicher Token-Request:", response.data);
    writeLog('INFO', `✅ Token erfolgreich erhalten für ${oauth.clientId}`);

    return response.data.access_token;
  } catch (err) {
    writeLog('ERROR', `❌ Fehler beim Abrufen des Tokens für ${oauth.clientId}: ${err.message}`);
    throw err;
  }
}


module.exports = { getToken };
