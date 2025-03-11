<?php
session_start();
include 'db.php';

// Admin Login
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Intentional SQL Injection Vulnerability
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['loggedin'] = true;
        $_SESSION['flag'] = $user['flag'];
        header("Location: index.php");
        exit;
    } else {
        echo "Invalid credentials.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Bug Bounty Challenge</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1 class="<?php echo isset($_SESSION['loggedin']) ? 'hidden' : ''; ?>">Bug Bounty Challenge</h1>


    <?php if (!isset($_SESSION['loggedin'])): ?>
        <!-- Admin Login Form -->
        <form method="POST">
            <h3>Admin Login</h3>
            <input type="text" name="username" placeholder="Username"><br>
            <input type="password" name="password" placeholder="Password"><br>
            <input type="submit" value="Login">
        </form>
    <?php else: ?>
        <div class="navbar">
            <div class="nav-item">Welcome, Admin!</div>
            <div class="nav-item">
                <a class="logout-button" href="index.php?logout=true">Logout</a>
            </div>
        </div>

        <?php if (isset($_SESSION['loggedin'])): ?>
            <p class="admin-login-flag"><strong><?php echo htmlspecialchars($_SESSION['flag']); ?></strong></p>
        <?php endif; ?>

        <?php if (!isset($_GET['xss'])): ?>
            <h3>Product Search</h3>
            <form method="GET">
                <input type="text" name="search" placeholder="Search products"><br>
                <select name="category">
                    <option value="Electronics" <?php if (isset($_GET['category']) && $_GET['category'] == 'Electronics')
                        echo 'selected'; ?>>Electronics</option>
                    <option value="Books" <?php if (isset($_GET['category']) && $_GET['category'] == 'Books')
                        echo 'selected'; ?>>
                        Books</option>
                    <option value="Toys" <?php if (isset($_GET['category']) && $_GET['category'] == 'Toys')
                        echo 'selected'; ?>>
                        Toys</option>
                    <option value="Kitchen" <?php if (isset($_GET['category']) && $_GET['category'] == 'Kitchen')
                        echo 'selected'; ?>>Kitchen</option>
                    <option value="Tools" <?php if (isset($_GET['category']) && $_GET['category'] == 'Tools')
                        echo 'selected'; ?>>
                        Tools</option>
                </select>
                <input type="submit" value="Search">
            </form>
        <?php endif; ?>

        <?php
        // Product Listing with Vulnerability in Category Filter
        if (isset($_GET['category'])) {
            $category = $_GET['category'];

            // Intentional SQL Injection Vulnerability
            $query = "SELECT * FROM products WHERE category='$category' ORDER BY id DESC LIMIT 6";
            $result = $conn->query($query);

            if ($result) {
                echo "<h3>Products in category: " . htmlspecialchars($category) . "</h3>";
                $count = 0;
                while ($row = $result->fetch_assoc()) {
                    if (++$count <= 5) {
                        echo htmlspecialchars($row['name']) . "<br>";
                    } else {
                        echo "Surprise Entry: " . htmlspecialchars($row['name']) . "<br>";
                    }
                }
            } else {
                echo "No products found.";
            }
        }

        // Check if the user has captured the second flag (SQLi)
        if (isset($_GET['category']) && $_GET['category'] == 'flags') {
            // echo "<p><strong>FLAG{product_category_sqli}</strong></p>";
            echo "<p>Nice work! You found the second flag via SQL Injection.</p>";
            echo "<a class='continue' href='index.php?flag2_found=true&xss=true'>Continue to next challenge →</a>";
        }

        // -----------------------------------
// ✅ Stage 2: Unlock the XSS Vulnerability
// -----------------------------------    
        if (isset($_GET['flag2_found'])) {
            echo "<h3>Comments Section</h3>";
            echo '<form onsubmit="addComment(event)">
            <textarea id="comment-input" placeholder="Leave a comment" rows="4" cols="50"></textarea><br>
            <input type="submit" value="Post Comment">
            </form>';

            echo "<h4>Previous Comments:</h4>";
            echo '<div class="comment-box" id="comment-box"></div>';
        }
        ?>

    <?php endif; ?>

    <script>
        // ✅ Handle adding comments (client-side only)
        function addComment(event) {
            event.preventDefault();
            const input = document.getElementById('comment-input');
            const commentBox = document.getElementById('comment-box');

            if (input.value.trim() !== '') {
                // ✅ Directly inject the comment without sanitizing (intentionally vulnerable)
                commentBox.innerHTML += '<p>' + input.value + '</p>';

                // ✅ Trigger XSS if script is injected
                if (input.value.includes('<script>')) {
                    document.body.innerHTML += '<p><strong>FLAG{xss_vulnerability_found}</strong></p>';
                }

                input.value = '';
            }
        }
    </script>
</body>

</html>