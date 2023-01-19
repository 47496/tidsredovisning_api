<?php

declare (strict_types=1);

function connectDb():PDO{
    static $db=null;

    if ($db===Null) {
        // Kopplar mot databas 
        $dsn = 'mysql:dbname=timerbyte;host=localhost';
        $dbUser='root';
        $dbPassword='';
        $db = new PDO($dsn, $dbUser, $dbPassword);
    }
    return $db;
}