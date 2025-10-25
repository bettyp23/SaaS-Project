<?php

/**
 * Database Connection Test Script
 * Tests MySQL database connection using credentials from .env file
 */

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    // Check if phpdotenv is available
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    } else {
        // Fallback: Parse .env file manually
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Get database credentials from environment
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '8889';
$database = getenv('DB_DATABASE') ?: 'vibe_templates';
$username = getenv('DB_USERNAME') ?: 'vibe_templates';
$password = getenv('DB_PASSWORD') ?: 'vibe_templates_password';

// Build DSN (Data Source Name)
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);

// Display configuration
echo "Testing database connection...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Host:     $host\n";
echo "Port:     $port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    // Attempt to connect to the database
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Connection successful
    echo "✅ Successfully connected to the database!\n\n";
    
    // Get server version
    $version = $pdo->query('SELECT VERSION() as version')->fetch();
    echo "MySQL Version: " . $version['version'] . "\n";
    
    // Check if database exists and get table count
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = :db');
    $stmt->execute(['db' => $database]);
    $tableCount = $stmt->fetch();
    echo "Tables in database: " . $tableCount['count'] . "\n\n";
    
    // List all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($tables)) {
        echo "Tables found:\n";
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
    }
    
} catch (PDOException $e) {
    // Connection failed
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "\n";
    
    // Provide helpful error messages
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();
    
    if (strpos($errorMessage, 'Access denied') !== false) {
        echo "💡 Tip: Check your username and password in the .env file.\n";
    } elseif (strpos($errorMessage, 'Unknown database') !== false) {
        echo "💡 Tip: The database '{$database}' doesn't exist. Please create it first.\n";
        echo "   Run: CREATE DATABASE IF NOT EXISTS `{$database}`;\n";
    } elseif (strpos($errorMessage, 'Connection refused') !== false) {
        echo "💡 Tip: MySQL server is not running or the host/port is incorrect.\n";
        echo "   Verify MySQL is running on {$host}:{$port}\n";
    } elseif (strpos($errorMessage, 'Connection timed out') !== false) {
        echo "💡 Tip: Unable to reach the MySQL server.\n";
        echo "   Check if MySQL is running and the host/port is correct.\n";
    }
    
    exit(1);
}

echo "\n✅ Connection test completed successfully!\n";
