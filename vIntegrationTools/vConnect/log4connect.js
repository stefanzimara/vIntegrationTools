const fs = require('fs');
const path = require('path');

// Log-Verzeichnis sicherstellen
const logDir = path.join(__dirname, 'log');
fs.mkdirSync(logDir, { recursive: true });

// Log-Level Reihenfolge (von niedrig nach hoch)
const LOG_LEVELS = ["DEBUG", "INFO", "WARN", "ERROR"];

// Lade die Config einmal beim Start
function getConfig() {
  delete require.cache[require.resolve('./config.json')];
  return require('./config.json');
}

// Aktueller Log-Level aus `config.json`
function getLogLevel() {
  const config = getConfig();
  return config.logLevel ? config.logLevel.toUpperCase() : "INFO"; // Standard INFO
}

/**
 * Funktion zum Schreiben in die Log-Datei, abhängig vom Log-Level.
 * @param {string} severity - Log-Level (DEBUG, INFO, WARN, ERROR).
 * @param {string} message - Die Log-Nachricht.
 * @param {string} [ip] - Optionale IP-Adresse für Tracking.
 */
function writeLog(severity, message, ip = '') {
  const currentLevel = getLogLevel();
  if (LOG_LEVELS.indexOf(severity) < LOG_LEVELS.indexOf(currentLevel)) {
    return; // Log wird ignoriert, weil Level niedriger ist
  }

  const now = new Date();
  const dateStr = now.toISOString().split('T')[0].replace(/-/g, '');
  const timeStr = now.toISOString().split('T')[1].replace('Z', '');
  const logFilePath = path.join(logDir, `${dateStr}.log`);
  const callerInfo = getCallerInfo(); // Automatisch Datei & Zeile holen

  const logEntry = `[${now.toISOString().replace('T', ' ').replace('Z', '')}] [${severity}] [${callerInfo}] ${message} ${ip ? `[IP: ${ip}]` : ''}\n`;

  fs.appendFile(logFilePath, logEntry, (err) => {
    if (err) {
      console.error(`Fehler beim Schreiben der Log-Datei: ${err.message}`);
    }
  });
}

function getCallerInfo() {
  const stack = new Error().stack.split("\n");
  
  // Stacktrace sieht so aus:
  // 0: Error
  // 1: at getCallerInfo
  // 2: at writeLog
  // 3: at AUFRUFENDE FUNKTION (z. B. in app.js oder authServer.js)
  
  const callerLine = stack[3]; // Die Zeile, die die Funktion `writeLog` aufgerufen hat
  
  const match = callerLine.match(/\(([^)]+)\)/); // Sucht die Datei und Zeilennummer
  return match ? match[1] : "unknown";
}


/**
 * Löscht alte Log-Dateien, die älter als 30 Tage sind.
 */
function cleanOldLogs() {
  try {
    const now = new Date();
    if (!fs.existsSync(logDir)) return; // Falls das Verzeichnis nicht existiert, nichts tun

    const logFiles = fs.readdirSync(logDir);
    for (const file of logFiles) {
      const filePath = path.join(logDir, file);
      try {
        const stats = fs.statSync(filePath);
        const fileAge = (now - stats.mtime) / (1000 * 60 * 60 * 24); // Alter in Tagen

        if (fileAge > 30) {
          fs.unlinkSync(filePath);
          console.log(`[LOG CLEANUP] Alte Log-Datei gelöscht: ${file}`);
        }
      } catch (err) {
        console.error(`[LOG ERROR] Fehler beim Löschen der Log-Datei ${file}: ${err.message}`);
      }
    }
  } catch (err) {
    console.error(`[LOG ERROR] Fehler beim Aufräumen der Logs: ${err.message}`);
  }
}

// Cleanup beim Start
cleanOldLogs();

// Logging-Methoden exportieren
module.exports = { writeLog };
