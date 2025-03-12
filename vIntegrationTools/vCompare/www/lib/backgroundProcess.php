<?php

require_once("../../settings2.php");
require_once("../../vCompare.main.php");

// Fehleranzeige für die Entwicklung (in Produktion deaktivieren)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Retrieve the process ID from the command line argument
$processId = $argv[1];

// Example: simulate a long-running process with progress updates
$totalSteps = 10;

// Definiere das Verzeichnis, in dem die Statusdateien gespeichert werden sollen
$logDir = __DIR__ . '/status_logs';

// Erstelle das Verzeichnis, falls es noch nicht existiert
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Definiere den Dateinamen, z. B. mit einer eindeutigen Prozess-ID
$statusFile = $logDir . "/process_{$processId}.txt";

// Schreibe einen initialen Status in die Datei (zum Beispiel progress 0)
file_put_contents($statusFile, json_encode([
    "progress" => 1,
    "completed" => false,
    "initial" => true
]));

// Simuliere einen langen Prozess
for ($i = 1; $i <= $totalSteps; $i++) {
    // Simuliere Arbeit (sleep for 1 second)
    sleep(1);
    
    // Update the process status in the file
    file_put_contents($statusFile, json_encode([
        "progress" => round(($i / $totalSteps) * 100),
        "completed" => ($i == $totalSteps),
        "Loop" => true
    ]));
}

// Optionally, remove the file after completion or archive it.
?>
