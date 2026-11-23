<?php
// Database configuration for MAMP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // 👈 MAMP uses 'root' as the default password
define('DB_NAME', 'quizhub');

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, 8889); 
    // 👆 Port 8889 is the default MySQL port for MAMP
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Close connection
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>
