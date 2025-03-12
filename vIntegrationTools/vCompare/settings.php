<?php

/**
 * File: settings.php
 * Author: Stefan Zimara <stefan.zimara@example.com>
 * Created: 2025-01-01
 * Updated: 2025-01-01
 *
 * Description:
 * This PHP file is part of vCompare / readArtifacts.php
 *
 */

const LOG_LEVEL = "WARN";
const OUTPUT_FORMAT = "db";    //json or db

//----- Relevant for JSON Output
const saveDetails = true;


//----- to be maintained for MySQL / Maria output
const DB_SETTINGS = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'dbname' => 'v-001-compare',
    'username' => 'v-001-compare',
    'password' => 'qijGud-tyrsob-kazru2'
];

//----- no Changes below that line

const version = "1.0.0";
const header = "\nvCompare\n\n";

?>


