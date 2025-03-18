const express = require('express');
const fs = require('fs-extra');
const axios = require('axios');
const cron = require('node-cron');
const path = require('path');
const { getToken } = require('./oauth');
const { writeLog } = require('./log4connect');
const micromatch = require('micromatch'); // 📌 Paket für Pattern-Matching
const bodyParser = require('body-parser');
const authRouter = require('./authServer');  // <-- OAuth Server einbinden
const http = require('http');
const https = require('https');
const app = express();

// 🛠️ Body Parser korrekt setzen
app.use(bodyParser.urlencoded({ extended: false }));  // WICHTIG für Form-Daten
app.use(bodyParser.json());  // WICHTIG für JSON-Daten
app.use(express.raw({ type: '*/*' }));  // 🔥 Stellt sicher, dass Binärdaten unverändert bleiben
app.use('/oauth', authRouter);  // ✅ OAuth Server registrieren

// Auto-Redirect HTTP → HTTPS
app.use((req, res, next) => {
  if (config.server.enableHttps && req.protocol !== 'https') {
      return res.redirect(`https://${req.headers.host}${req.url}`);
  }
  next();
});


function getConfig() {
  delete require.cache[require.resolve('./config.json')]; // Cache löschen
  const config = require('./config.json');
  writeLog('DEBUG', 'Config geladen');
  console.log("✅ Geladene Config:", JSON.stringify(config, null, 2));
  return config;
}

const config = getConfig();

const SECRET_KEY = config.oauthServer.secretKey;  // 🔐 Geheim für Token-Signierung

// 🔹 Logge direkt nach dem Einlesen der Config die aktiven Tasks
config.tasks.forEach(task => {
  writeLog('INFO', `Task "${task.name}" ist ${task.enabled ? 'AKTIV' : 'DEAKTIVIERT'}`);
  if (task.httpToFile.enabled) {
    writeLog('INFO', `📥 HTTP → Datei aktiv: Speichert nach ${task.httpToFile.targetPath}`);
  }
  if (task.fileToHttp.enabled) {
    writeLog('INFO', `📤 Datei → HTTP aktiv: Sendet von ${task.fileToHttp.sourcePath} nach ${task.fileToHttp.targetUrl}`);
  }
});

writeLog('INFO', `Cron-Job gestartet mit Intervall: ${config.intervalCron}`);
writeLog('INFO', 'vConnect gestartet auf Port 8080');

// 🔹 Verzeichnisse vorbereiten
for (const task of config.tasks.filter(t => t.enabled)) {
  if (task.fileToHttp && task.fileToHttp.sourcePath) {
    fs.ensureDirSync(task.fileToHttp.sourcePath);
  }
  if (task.httpToFile && task.httpToFile.targetPath) {
    fs.ensureDirSync(task.httpToFile.targetPath);
  }
}

const jwt = require('jsonwebtoken');

// 🛡️ Authentifizierungs-Check mit Task-Berechtigungen
async function validateAuth(req, task) {
  if (!task.httpToFile.authType || task.httpToFile.authType === "none") {
    return true; // Keine Authentifizierung nötig
  }

  const authHeader = req.headers.authorization;

  if (!authHeader) {
    writeLog('WARN', `🛑 Kein Auth-Header vorhanden für Task ${task.name}`);
    return false;
  }

  // **🔐 Basic Authentication**
  if (task.httpToFile.authType === "basic" && authHeader.startsWith("Basic ")) {
    const credentials = Buffer.from(authHeader.split(" ")[1], "base64").toString().split(":");
    const [username, password] = credentials;

    if (!config.basicAuth[username] || config.basicAuth[username].password !== password) {
      writeLog('WARN', `🛑 Ungültige Basic-Auth für Task ${task.name} (User: ${username})`);
      return false;
    }

    // **📌 Task-Berechtigungen prüfen**
    if (!config.basicAuth[username].allowedTasks.includes(task.name)) {
      writeLog('WARN', `🚫 Zugriff verweigert: User ${username} darf Task ${task.name} nicht ausführen.`);
      return false;
    }

    writeLog('INFO', `🔑 Basic Auth erfolgreich: ${username} hat Zugriff auf ${task.name}`);
    return true;
  }

  // **🔐 OAuth Authentication**
  if (task.httpToFile.authType === "oauth" && authHeader.startsWith("Bearer ")) {
    const token = authHeader.split(" ")[1];

    try {
      const decoded = jwt.verify(token, config.oauthServer.secretKey);

      // **📌 Task-Berechtigungen prüfen**
      const client = config.oauthServer.clients.find(c => c.clientId === decoded.client_id);
      if (!client || !client.allowedTasks.includes(task.name)) {
        writeLog('WARN', `🚫 Zugriff verweigert: Client ${decoded.client_id} darf Task ${task.name} nicht ausführen.`);
        return false;
      }

      writeLog('INFO', `🔑 OAuth-Token erfolgreich validiert: ${decoded.client_id} hat Zugriff auf ${task.name}`);
      return true;
    } catch (err) {
      writeLog('ERROR', `❌ Fehler beim JWT-Token-Check für Task ${task.name}: ${err.message}`);
      return false;
    }
  }

  writeLog('WARN', `🛑 Authentifizierung fehlgeschlagen für Task ${task.name}`);
  return false;
}


async function sendFiles(task) {

  if (!task.fileToHttp || !task.fileToHttp.sourcePath) {
    writeLog('ERROR', `❌ Task ${task.name} hat keinen gültigen sourcePath.`);
    return;
  }

  try {
    const files = await fs.readdir(task.fileToHttp.sourcePath);

    console.log("Files: ", files);

    if (files.length === 0) {
      writeLog('DEBUG', `Keine Dateien in ${task.fileToHttp.sourcePath} zu senden.`);
      return;
    }

    for (const file of files) {
      const filePath = path.join(task.fileToHttp.sourcePath, file);
      
      let data;
      try {
        data = await fs.readFile(filePath);
      } catch (err) {
        writeLog('ERROR', `Fehler beim Lesen der Datei ${filePath}: ${err.message}`);
        continue;
      }

      writeLog('DEBUG', `📤 Sende Datei: ${filePath} → ${task.fileToHttp.targetUrl}`);

      try {
        let headers = {
          'X-File-Name': file,
          'Content-Type': 'application/octet-stream'
        };

        writeLog('DEBUG', `🔑 Auth Tyoe is: ${task.fileToHttp.authType}`);
        console.log(`Auth Tyoe is ${task.fileToHttp.authType}`);

        // Authentifizierung basierend auf `authType`
        if (task.fileToHttp.authType === "oauth") {
          console.log(`🔄 OAuth aktiviert für ${task.fileToHttp.targetUrl}`);
          writeLog('DEBUG', `🔑 OAuth aktiviert. Fordere Token für ${task.fileToHttp.targetUrl}`);
          const token = await getToken(task);
          headers['Authorization'] = `Bearer ${token}`;
        } else if (task.fileToHttp.authType === "basic") {
          const basicAuth = Buffer.from(`${task.fileToHttp.basicAuth.username}:${task.fileToHttp.basicAuth.password}`).toString('base64');
          headers['Authorization'] = `Basic ${basicAuth}`;
        }

        const response = await axios.post(task.fileToHttp.targetUrl, data, { headers });

        writeLog('INFO', `✅ Datei gesendet: ${filePath} → ${task.fileToHttp.targetUrl} | Status: ${response.status}`);


        //File process After send
        if (task.fileToHttp.postSendAction === "delete") {
          try {
            await fs.remove(filePath);
            writeLog('INFO', `🗑️ Datei gelöscht: ${filePath}`);
          } catch (err) {
            writeLog('ERROR', `❌ Fehler beim Löschen der Datei ${filePath}: ${err.message}`);
          }
        } else if (task.fileToHttp.postSendAction === "archive") {
          // Falls archivePath fehlt, auf Standardwert setzen
          const archivePath = task.fileToHttp.archivePath || "./archive";
          
          // Timestamp generieren
          const timestamp = new Date().toISOString().replace(/[-T:.Z]/g, '').slice(0, 17); // YYYYMMDDHHMISSmmm
          const targetPath = path.join(archivePath, `${timestamp}_${path.basename(filePath)}`);
        
          //writeLog('DEBUG', `📦 File should be archioved from : ${filePath} to ${targetPath}`);

          try {
            await fs.ensureDir(archivePath);
            await fs.move(filePath, targetPath);
            writeLog('INFO', `📦 Datei archiviert: ${filePath} → ${targetPath}`);
          } catch (err) {
            writeLog('ERROR', `❌ Fehler beim Archivieren der Datei ${filePath}: ${err.message}`);
          }
        }
         else if (task.fileToHttp.postSendAction === "test") {
          writeLog('INFO', `🔄 Test-Modus: Datei bleibt bestehen ${filePath}`);
        }
        
        

      } catch (err) {
        if (err.response) {
          writeLog('ERROR', `❌ Fehler beim Senden: ${filePath} → ${task.fileToHttp.targetUrl} | Status: ${err.response.status} ${err.response.statusText} | ${JSON.stringify(err.response.data)}`);
        } else if (err.request) {
          writeLog('ERROR', `❌ Keine Antwort vom Server: ${filePath} → ${task.fileToHttp.targetUrl} | Fehler: ${err.message}`);
        } else {
          writeLog('ERROR', `❌ Fehler beim Senden: ${filePath} → ${task.fileToHttp.targetUrl} | Fehler: ${err.message}`);
        }
      }
    }
  } catch (err) {
    writeLog('ERROR', `Fehler beim Lesen des Verzeichnisses ${task.fileToHttp.sourcePath}: ${err.message}`);
  }
}


// 🔄 Cron Jobs für alle fileToHttp Tasks mit eigenem Intervall
config.tasks
  .filter(t => t.enabled && t.fileToHttp && t.fileToHttp.enabled && t.fileToHttp.intervalCron) // 🛑 Nur Tasks mit eigenem Cron-Intervall
  .forEach(task => {
    if (!task.fileToHttp.sourcePath) { // 🛑 Fehlende sourcePath-Überprüfung
      writeLog('ERROR', `❌ Task ${task.name} hat keinen gültigen sourcePath. Wird übersprungen.`);
      return; // 🚫 Task nicht einplanen!
    }

    const cronSchedule = task.fileToHttp.intervalCron;

    cron.schedule(cronSchedule, () => {
      writeLog('INFO', `🔄 [Custom Cron] Job für ${task.name} gestartet...`);
      sendFiles(task);
      writeLog('INFO', `✅ [Custom Cron] Job für ${task.name} abgeschlossen.`);
    });

    writeLog('INFO', `⏰ [Custom Cron] Task ${task.name} läuft mit eigenem Intervall: ${cronSchedule}`);
  });

// 🔄 Globaler Cron-Job für alle anderen Tasks, die KEIN eigenes `intervalCron` haben
cron.schedule(config.intervalCron, () => {
  writeLog('INFO', '🔄 [Global Cron] Starte alle regulären Tasks...');

  config.tasks
    .filter(t => 
      t.enabled && 
      t.fileToHttp && 
      t.fileToHttp.enabled && // 🛑 Sicherstellen, dass fileToHttp auch wirklich aktiv ist!
      !t.fileToHttp.intervalCron // 🛑 Tasks mit eigenem intervalCron sind hier ausgeschlossen
    )
    .forEach(task => {
      if (!task.fileToHttp.sourcePath) { // 🛑 Wieder: Fehlende sourcePath-Überprüfung
        writeLog('ERROR', `❌ Task ${task.name} hat keinen gültigen sourcePath. Wird übersprungen.`);
        return;
      }

      writeLog('INFO', `✅ [Global Cron] Starte Task: ${task.name}`);
      sendFiles(task);
    });

  writeLog('INFO', '🟢 [Global Cron] Alle regulären Tasks abgeschlossen.');
});




// 🔥 HTTP → FILE (Upload-Handler)
app.post('/upload/:taskName', async (req, res) => {

  const { taskName } = req.params;

  // 🔍 **Fehlende TaskName-Prüfung**
  if (!taskName) {
    writeLog('WARN', `🛑 Fehlender TaskName in der Upload-URL.`);
    return res.status(400).json({ message: "Fehlender TaskName in der URL. Beispiel: /upload/Task1" });
  }

  // 🛠️ **Debugging: req.body & Headers ausgeben**
  console.log("📥 Request Body (Typ):", typeof req.body);
  console.log("📥 Request Body (Inhalt):", req.body);
  console.log("📥 Request Headers:", req.headers);

  // 🔍 **Gültigen Task suchen**
  const task = config.tasks.find(t => t.name === taskName && t.httpToFile.enabled);

  if (!task) {
    writeLog('WARN', `Fehlgeschlagener Upload für unbekannten oder deaktivierten Task: ${taskName}`, req.ip);
    return res.status(404).json({ message: "Task nicht gefunden oder deaktiviert." });
  }

  // 🔐 **Authentifizierung prüfen**
  if (!(await validateAuth(req, task))) {
    writeLog('WARN', `🛑 Authentifizierung fehlgeschlagen für Task ${task.name}`, req.ip);
    return res.status(401).send('Unauthorized');
  }

  // ✅ **Task ist gültig – Datei speichern**
  const fileName = req.headers['x-file-name'] || `file_${Date.now()}`;
  const subPath = req.headers['x-file-path'] || '';

  const sanitizedFileName = filePathSanitize(fileName);
  const sanitizedSubPath = sanitizeSubPath(subPath);

  const targetDir = path.join(task.httpToFile.targetPath, sanitizedSubPath);
  await fs.ensureDir(targetDir);
  let filePath = path.join(targetDir, sanitizedFileName);

  // 🔄 **Dateikonflikt-Handling**
  let conflictMode = task.httpToFile.onConflict || "error";
  if (req.headers['x-conflict-mode']) {
    conflictMode = req.headers['x-conflict-mode'].toLowerCase();
  }

  try {
    const fileExists = await fs.pathExists(filePath);

    if (fileExists) {
      if (conflictMode === "ignore_error") {
        writeLog('WARN', `❌ Datei existiert bereits, aber Fehler wird ignoriert: ${filePath}`);
        return res.status(200).json({ message: "Datei existiert bereits, wird aber ignoriert." });
      }

      if (conflictMode === "overwrite") {
        writeLog('INFO', `🔄 Datei existiert, wird aber überschrieben: ${filePath}`);
      }

      if (conflictMode === "timestamp") {
        const timestamp = new Date().toISOString().replace(/[-T:.Z]/g, '').slice(0, 17);
        filePath = path.join(targetDir, `${timestamp}_${sanitizedFileName}`);
        writeLog('INFO', `📌 Datei umbenannt mit Timestamp: ${filePath}`);
      }

      if (conflictMode === "numbering") {
        let counter = 1;
        let newFilePath = filePath;
        const ext = path.extname(sanitizedFileName);
        const baseName = path.basename(sanitizedFileName, ext);

        while (await fs.pathExists(newFilePath)) {
          newFilePath = path.join(targetDir, `${baseName}_${counter}${ext}`);
          counter++;
        }

        filePath = newFilePath;
        writeLog('INFO', `🔢 Datei umbenannt mit Nummerierung: ${filePath}`);
      }

      if (conflictMode === "error") {
        writeLog('ERROR', `❌ Datei existiert bereits, Fehler geworfen: ${filePath}`);
        return res.status(500).json({ message: "Datei existiert bereits!", file: filePath });
      }
    }

    // 💾 **Datei speichern**
    await fs.writeFile(filePath, req.body);
    writeLog('INFO', `✅ Datei gespeichert: ${filePath}`);

    return res.status(200).json({ message: "Datei erfolgreich gespeichert.", file: filePath });

  } catch (err) {
    writeLog('ERROR', `❌ Fehler beim Speichern von ${filePath}: ${err.message}`);
    return res.status(500).json({ message: "Fehler beim Speichern", error: err.message });
  }
});


// 🔹 Hilfsfunktionen
function filePathSanitize(filename) {
  return filename.replace(/[^a-zA-Z0-9.\-_]/g, '_');
}

function sanitizeSubPath(subpath) {
  return subpath
    .split('/')
    .map(segment => segment.replace(/[^a-zA-Z0-9.\-_]/g, '_'))
    .join('/');
}

// 🔥 Server starten
if (config.server.httpPort) {
  const HTTP_PORT = config.server.httpPort;
  http.createServer(app).listen(HTTP_PORT, () => {
      console.log(`✅ vConnect (HTTP) gestartet auf Port ${HTTP_PORT}`);
      writeLog('INFO', `✅ vConnect (HTTP) gestartet auf Port ${HTTP_PORT}`);
  });
} else {
  console.log(`Kein HTTP-Port in der Konfiguration gefunden!`);
  writeLog('INFO', `Kein HTTP-Port in der Konfiguration gefunden!`);
}

// 📌 Falls HTTPS aktiviert ist, starte HTTPS-Server
if (config.server.enableHttps) {
  try {
      const sslOptions = {
          key: fs.readFileSync(config.server.ssl.keyPath),
          cert: fs.readFileSync(config.server.ssl.certPath)
      };

      const HTTPS_PORT = config.server.httpsPort || 8443;
      https.createServer(sslOptions, app).listen(HTTPS_PORT, () => {
          console.log(`✅ vConnect (HTTPS) gestartet auf Port ${HTTPS_PORT}`);
          writeLog('INFO', `✅ vConnect (HTTPS) gestartet auf Port ${HTTPS_PORT}`);
      });
  } catch (err) {
      console.error(`❌ Fehler beim Starten des HTTPS-Servers: ${err.message}`);
      writeLog('ERROR', `❌ Fehler beim Starten des HTTPS-Servers: ${err.message}`);
  }
}