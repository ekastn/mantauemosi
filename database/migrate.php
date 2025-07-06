<?php
// This script reads the schema.sql file and executes it to set up the database.

require_once __DIR__ . '/../config/database.php';

try {
    // 1. Connect to the database
    $db = new Database();
    $conn = $db->getConnection();

    // 2. Read the SQL schema file
    $sql_file_path = __DIR__ . '/schema.sql';
    if (!file_exists($sql_file_path)) {
        die("Error: schema.sql file not found at " . $sql_file_path);
    }
    $sql = file_get_contents($sql_file_path);

    // 3. Execute the SQL commands
    $conn->exec($sql);

    echo "Database schema created and sample data inserted successfully." . PHP_EOL;

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage() . PHP_EOL);
}
?>
