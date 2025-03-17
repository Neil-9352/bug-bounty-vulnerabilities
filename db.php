<?php
$host = "127.0.0.1"; // Use the Docker service name
$user = "root";
$pass = "root";
$db = "bugbounty";

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Automatically create tables if they don't exist
$conn->query("
    CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL
    )
");

$conn->query("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        flag TEXT
    )
");

$conn->query("
    CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content TEXT NOT NULL
    )
");

// $conn->query("TRUNCATE TABLE comments");

// Insert admin user if not exists
$conn->query("
    INSERT INTO users (username, password, flag) 
    SELECT * FROM (SELECT 'admin', 'ftt87o6rdtrfuygkdtrfxjhcvgiy6ftu', 'FLAG{admin_login_bypass}') AS tmp
    WHERE NOT EXISTS (
        SELECT username FROM users WHERE username='admin'
    )
");

$conn->query("TRUNCATE TABLE products");

$conn->query("INSERT IGNORE INTO products (name, category) VALUES
    -- Electronics
    ('Apple iPhone 15', 'Electronics'),
    ('MacBook Air M2', 'Electronics'),
    ('Samsung Galaxy S23', 'Electronics'),
    ('Sony WH-1000XM5', 'Electronics'),
    ('GoPro Hero 11', 'Electronics'),
    ('Nintendo Switch OLED', 'Electronics'),  -- Added item

    -- Books
    ('The Alchemist by Paulo Coelho', 'Books'),
    ('1984 by George Orwell', 'Books'),
    ('Sapiens: A Brief History of Humankind', 'Books'),
    ('Atomic Habits by James Clear', 'Books'),
    ('The Subtle Art of Not Giving a F*ck', 'Books'),
    ('To Kill a Mockingbird by Harper Lee', 'Books'),  -- Added item

    -- Toys
    ('Lego Star Wars Millennium Falcon', 'Toys'),
    ('Hot Wheels Monster Truck', 'Toys'),
    ('Barbie Dreamhouse', 'Toys'),
    ('Rubik\'s Cube', 'Toys'),
    ('NERF Elite 2.0 Blaster', 'Toys'),
    ('PlayStation 5 DualSense Controller', 'Toys'),  -- Added item

    -- Kitchen
    ('Philips Air Fryer', 'Kitchen'),
    ('Prestige Electric Kettle', 'Kitchen'),
    ('Borosil Glass Baking Dish', 'Kitchen'),
    ('Butterfly Rapid Pressure Cooker', 'Kitchen'),
    ('Hawkins Stainless Steel Tawa', 'Kitchen'),
    ('Preethi Zodiac Mixer Grinder', 'Kitchen'),  -- Added item

    -- Tools
    ('Bosch Cordless Drill Machine', 'Tools'),
    ('Stanley Hammer', 'Tools'),
    ('Black+Decker Electric Screwdriver', 'Tools'),
    ('Makita Angle Grinder', 'Tools'),
    ('Ingco Tool Set 100 Pieces', 'Tools'),
    ('Dremel Rotary Tool Kit', 'Tools'),  -- Added item

    -- Hidden Flag disguised as a product in flags category
    ('FLAG{product_category_sqli}', 'flags')
");
?>