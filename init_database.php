<?php
require_once 'config/database.php';

// Read the SQL file
$sql = file_get_contents('database.sql');

// Split the SQL file into individual queries
$queries = explode(';', $sql);

// Execute each query
foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        if (mysqli_query($conn, $query)) {
            echo "Query executed successfully: " . substr($query, 0, 50) . "...<br>";
        } else {
            echo "Error executing query: " . mysqli_error($conn) . "<br>";
            echo "Query: " . $query . "<br><br>";
        }
    }
}

echo "<br>Database initialization completed!";
?> 