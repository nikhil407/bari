<?php
require_once 'config/db.php';

echo "Database check:\n";
$result = $pdo->query("SELECT id, emoji, name FROM products LIMIT 5");
foreach ($result as $row) {
    echo "ID: {$row['id']} | Emoji: {$row['emoji']} | Name: {$row['name']}\n";
}

echo "\nCharset check:\n";
$result = $pdo->query("SHOW VARIABLES LIKE 'character_set%'");
foreach ($result as $row) {
    echo "{$row['Variable_name']}: {$row['Value']}\n";
}

echo "\nTable charset:\n";
$result = $pdo->query("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'bari_saha_grocery'");
foreach ($result as $row) {
    echo "{$row['TABLE_NAME']}: {$row['TABLE_COLLATION']}\n";
}
