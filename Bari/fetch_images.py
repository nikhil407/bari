import urllib.request, urllib.parse, json, ssl, os, re
import mysql.connector

# Ensure directory exists
os.makedirs('assets/images/products', exist_ok=True)

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

def clean_name(name):
    name = re.sub(r'^(Fresh|Organic|Juicy|Baby|Raw|Sliced|Whole)\s+', '', name, flags=re.IGNORECASE)
    # Mapping some tricky names to their main wikipedia article
    mapping = {
        'Green Capsicum': 'Bell pepper',
        'Milk 1L': 'Milk',
        'Eggs 12 pcs': 'Egg (food)',
        'Butter 500g': 'Butter',
        'Cheese Slices': 'Cheese',
        'Greek Yogurt': 'Strained yogurt',
        'Whole Wheat Bread': 'Whole wheat bread',
        'Croissants': 'Croissant',
        'Chocolate Chip Cookies': 'Chocolate chip cookie',
        'Muffins': 'Muffin',
        'Bagels': 'Bagel',
        'Chicken Breast': 'Chicken meat',
        'Minced Beef': 'Ground beef',
        'Salmon Fillet': 'Salmon as food',
        'Pork Chops': 'Pork chop',
        'Chicken Drumsticks': 'Chicken meat',
        'Orange Juice': 'Orange juice',
        'Coca Cola 1.5L': 'Coca-Cola',
        'Mineral Water': 'Mineral water',
        'Green Tea': 'Green tea',
        'Coffee Beans': 'Coffee bean',
        'Potato Chips': 'Potato chip',
        'Chocolate Bar': 'Chocolate bar',
        'Mixed Nuts': 'Mixed nuts',
        'Popcorn': 'Popcorn',
        'Gummy Bears': 'Gummy bear',
        'Laundry Detergent': 'Laundry detergent',
        'Dishwashing Liquid': 'Dishwashing liquid',
        'Toilet Paper': 'Toilet paper',
        'Paper Towels': 'Paper towel',
        'All-Purpose Cleaner': 'Hard-surface cleaner'
    }
    return mapping.get(name, name)

def get_wiki_image(title):
    search_url = 'https://en.wikipedia.org/w/api.php?action=query&prop=pageimages&format=json&piprop=original&titles=' + urllib.parse.quote(title)
    req = urllib.request.Request(search_url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        response = urllib.request.urlopen(req, context=ctx)
        data = json.loads(response.read())
        pages = data['query']['pages']
        for p in pages.values():
            if 'original' in p:
                return p['original']['source']
    except Exception as e:
        print(f'Error fetching image URL for {title}: {e}')
    return None

try:
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="bari_saha_grocery"
    )
    cursor = conn.cursor(dictionary=True)
    
    # Add image_url column if not exists
    try:
        cursor.execute("ALTER TABLE products ADD COLUMN image_url VARCHAR(255) DEFAULT NULL")
    except Exception as e:
        pass # Already exists
        
    cursor.execute("SELECT id, name FROM products")
    products = cursor.fetchall()
    
    for p in products:
        p_id = p['id']
        name = p['name']
        search_term = clean_name(name)
        
        img_url = get_wiki_image(search_term)
        if not img_url:
            # Fallback to general search if the specific one fails
            print(f"[{name}] No direct match for '{search_term}', trying search...")
            search_api = 'https://en.wikipedia.org/w/api.php?action=query&list=search&srsearch=' + urllib.parse.quote(search_term) + '&format=json'
            try:
                s_req = urllib.request.Request(search_api, headers={'User-Agent': 'Mozilla/5.0'})
                s_resp = urllib.request.urlopen(s_req, context=ctx)
                s_data = json.loads(s_resp.read())
                if s_data['query']['search']:
                    first_title = s_data['query']['search'][0]['title']
                    img_url = get_wiki_image(first_title)
            except Exception as e:
                pass

        if img_url:
            print(f"[{name}] Found image: {img_url}")
            filepath = f"assets/images/products/product_{p_id}.jpg"
            try:
                req = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0'})
                img_data = urllib.request.urlopen(req, context=ctx).read()
                with open(filepath, 'wb') as f:
                    f.write(img_data)
                
                cursor.execute("UPDATE products SET image_url = %s WHERE id = %s", (filepath, p_id))
                conn.commit()
                print(f"[{name}] Saved to {filepath}")
            except Exception as e:
                print(f"[{name}] Error downloading image: {e}")
        else:
            print(f"[{name}] Could not find image.")

except Exception as e:
    print(f"Database error: {e}")
finally:
    if 'conn' in locals() and conn.is_connected():
        cursor.close()
        conn.close()
