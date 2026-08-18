<?php
require_once 'config/db.php';

$dir = 'assets/images/products';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// Add image_url column if not exists
try {
    $pdo->exec("ALTER TABLE products ADD COLUMN image_url VARCHAR(255) DEFAULT NULL");
} catch(PDOException $e) {
    // Column might already exist
}

function clean_name($name) {
    $name = preg_replace('/^(Fresh|Organic|Juicy|Baby|Raw|Sliced|Whole)\s+/i', '', $name);
    
    $mapping = [
        'Green Capsicum' => 'Bell pepper',
        'Milk 1L' => 'Milk',
        'Eggs 12 pcs' => 'Egg (food)',
        'Butter 500g' => 'Butter',
        'Cheese Slices' => 'Cheese',
        'Greek Yogurt' => 'Strained yogurt',
        'Whole Wheat Bread' => 'Whole wheat bread',
        'Croissants' => 'Croissant',
        'Chocolate Chip Cookies' => 'Chocolate chip cookie',
        'Muffins' => 'Muffin',
        'Bagels' => 'Bagel',
        'Chicken Breast' => 'Chicken meat',
        'Minced Beef' => 'Ground beef',
        'Salmon Fillet' => 'Salmon as food',
        'Pork Chops' => 'Pork chop',
        'Chicken Drumsticks' => 'Chicken meat',
        'Orange Juice' => 'Orange juice',
        'Coca Cola 1.5L' => 'Coca-Cola',
        'Mineral Water' => 'Mineral water',
        'Green Tea' => 'Green tea',
        'Coffee Beans' => 'Coffee bean',
        'Potato Chips' => 'Potato chip',
        'Chocolate Bar' => 'Chocolate bar',
        'Mixed Nuts' => 'Mixed nuts',
        'Popcorn' => 'Popcorn',
        'Gummy Bears' => 'Gummy bear',
        'Laundry Detergent' => 'Laundry detergent',
        'Dishwashing Liquid' => 'Dishwashing liquid',
        'Toilet Paper' => 'Toilet paper',
        'Paper Towels' => 'Paper towel',
        'All-Purpose Cleaner' => 'Hard-surface cleaner'
    ];
    
    return isset($mapping[$name]) ? $mapping[$name] : $name;
}

function get_wiki_image($title) {
    $title = urlencode($title);
    $url = "https://en.wikipedia.org/w/api.php?action=query&prop=pageimages&format=json&piprop=original&titles={$title}";
    
    $opts = [
        "http" => [
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
        ],
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ]
    ];
    $context = stream_context_create($opts);
    $json = @file_get_contents($url, false, $context);
    if ($json) {
        $data = json_decode($json, true);
        if (isset($data['query']['pages'])) {
            foreach ($data['query']['pages'] as $page) {
                if (isset($page['original']['source'])) {
                    return $page['original']['source'];
                }
            }
        }
    }
    return null;
}

function get_wiki_image_search($term) {
    $term = urlencode($term);
    $url = "https://en.wikipedia.org/w/api.php?action=query&list=search&srsearch={$term}&format=json";
    
    $opts = [
        "http" => ["header" => "User-Agent: Mozilla/5.0\r\n"],
        "ssl" => ["verify_peer" => false, "verify_peer_name" => false]
    ];
    $context = stream_context_create($opts);
    $json = @file_get_contents($url, false, $context);
    if ($json) {
        $data = json_decode($json, true);
        if (isset($data['query']['search'][0]['title'])) {
            return get_wiki_image($data['query']['search'][0]['title']);
        }
    }
    return null;
}

$stmt = $pdo->query("SELECT id, name FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    $id = $p['id'];
    $name = $p['name'];
    $searchTerm = clean_name($name);
    
    $imgUrl = get_wiki_image($searchTerm);
    if (!$imgUrl) {
        echo "[$name] Direct match failed for '$searchTerm', searching...\n";
        $imgUrl = get_wiki_image_search($searchTerm);
    }
    
    if ($imgUrl) {
        echo "[$name] Found image: $imgUrl\n";
        $opts = [
            "http" => ["header" => "User-Agent: Mozilla/5.0\r\n"],
            "ssl" => ["verify_peer" => false, "verify_peer_name" => false]
        ];
        $context = stream_context_create($opts);
        $imgData = @file_get_contents($imgUrl, false, $context);
        
        if ($imgData) {
            $ext = pathinfo(parse_url($imgUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (!$ext) $ext = 'jpg';
            $filepath = "assets/images/products/product_{$id}.{$ext}";
            
            file_put_contents($filepath, $imgData);
            
            $update = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
            $update->execute([$filepath, $id]);
            echo "[$name] Saved to $filepath\n";
        } else {
            echo "[$name] Failed to download image.\n";
        }
    } else {
        echo "[$name] No image found.\n";
    }
}

echo "Done.\n";
