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

// Persist product flag in session
if (isset($_GET['category']) && $_GET['category'] === 'flags') {
    $_SESSION['product_flag'] = "FLAG{product_category_sqli}";
    header("Location: index.php"); // Prevent showing the products page again
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bug Bounty Challenge</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="debugging.png" type="image/x-icon">
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
            <div class="nav-item challenge-info">There are hidden flags here. Can you find all of them?
            </div>
            <div class="nav-item">
                <a class="logout-button" href="index.php?logout=true">Logout</a>
            </div>
        </div>

        <?php if (isset($_SESSION['loggedin'])): ?>
            <p class="admin-login-flag"><strong><?php echo htmlspecialchars($_SESSION['flag']); ?></strong></p>

            <?php if (isset($_SESSION['product_flag'])): ?>
                <p class="product-flag"><strong><?php echo $_SESSION['product_flag']; ?></strong></p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!isset($_SESSION['product_flag'])): ?>
            <h3>Product Search</h3>
            <form method="GET">
                <input type="text" name="search" placeholder="Search products"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"><br>

                <select name="category">
                    <?php
                    $categories = ["Electronics", "Books", "Toys", "Kitchen", "Tools"];
                    $selected_category = isset($_GET['category']) ? $_GET['category'] : '';

                    foreach ($categories as $category) {
                        $selected = ($category == $selected_category) ? 'selected' : '';
                        echo "<option value=\"$category\" $selected>$category</option>";
                    }
                    ?>
                </select>

                <input type="submit" value="Search">
            </form>

        <?php endif; ?>

        <?php
        // Product Listing with Vulnerability in Category Filter
        if (isset($_GET['category']) && !isset($_SESSION['product_flag'])) {
            $category = $_GET['category'];
            $search = isset($_GET['search']) ? $_GET['search'] : '';

            // Intentional SQL Injection Vulnerability
            $query = "SELECT * FROM products WHERE name LIKE '%$search%' AND category='$category' ORDER BY id DESC LIMIT 6";
            $result = $conn->query($query);

            echo '<div id="product-list">';

            // if ($result) {
            //     echo "<h3>Products in category: " . htmlspecialchars($category) . "</h3>";
            //     $count = 0;
            //     while ($row = $result->fetch_assoc()) {
            //         if (++$count <= 5) {
            //             echo htmlspecialchars($row['name']) . "<br>";
            //         } else {
            //             echo "Surprise Entry: " . htmlspecialchars($row['name']) . "<br>";
            //         }
    
            //         // ✅ Check if the hidden product flag is found
            //         if ($row['name'] === 'FLAG{product_category_sqli}') {
            //             $_SESSION['product_flag'] = true;
            //             header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page
            //             exit;
            //         }
            //     }
            // } else {
            //     echo "No products found.";
            // }
    
            if ($result) {
                echo "<h3>Products in category: " . htmlspecialchars($category) . "</h3>";
                echo '<div class="product-container">';

                $products = [];
                while ($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }

                // Select a random product as the surprise item
                if (!empty($products)) {
                    $surpriseIndex = array_rand($products);
                }

                foreach ($products as $index => $product) {
                    if ($index == $surpriseIndex) {
                        echo "<div class='product-item surprise-item'>
                                <strong>SALE 30% OFF<br></strong><br>" . htmlspecialchars($product['name']) . "
                              </div>";
                    } else {
                        echo "<div class='product-item'>" . htmlspecialchars($product['name']) . "</div>";
                    }

                    // ✅ Check if the hidden product flag is found
                    if ($product['name'] === 'FLAG{product_category_sqli}') {
                        $_SESSION['product_flag'] = true;
                        header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page
                        exit;
                    }
                }

                echo '</div>'; // Close product-container
            } else {
                echo "No products found.";
            }



            // ✅ Show "Continue" button only if flag is found
            if (isset($_SESSION['product_flag'])) {
                echo '<br><a href="?continue=true" id="continue-btn">Continue to the next challenge</a>';
            }
            echo '</div>';
        }

        // ✅ Automatically unlock comment section once the second flag is found
        if (isset($_SESSION['product_flag'])) {
            $_SESSION['show_comment_section'] = true;
        }


        // ✅ Unlock the XSS Vulnerability only after progressing
        if (isset($_SESSION['show_comment_section'])) {
            echo '<div id="comment-section">';
            echo "<h3>The Journey is Over... Or is it?</h3>";

            // Comment Form (Always at the top)
            echo '<form onsubmit="addComment(event)">
    <textarea id="comment-input" placeholder="Leave a comment" rows="4" cols="50"></textarea><br>
    <input type="submit" value="Post Comment">
</form>';

            // Comment Box (Below the form)
            echo "<h4>Previous Comments:</h4>";
            echo '<div class="comment-box" id="comment-box">';
            echo '</div>';

            echo '</div>';

        }

        ?>
    <?php endif; ?>

    <script>
        // ✅ Handle adding comments (intentionally vulnerable to XSS)
        function addComment(event) {
            event.preventDefault();
            const input = document.getElementById('comment-input');
            const commentBox = document.getElementById('comment-box');

            if (input.value.trim() !== '') {
                commentBox.insertAdjacentHTML('beforeend', '<p>' + input.value + '</p>');

                // Extract & execute script tags manually
                const scripts = commentBox.getElementsByTagName("script");
                for (let script of scripts) {
                    eval(script.innerText); // Execute JavaScript manually
                }

                // Trigger flag if script detected
                if (input.value.includes('<script>')) {
                    document.body.insertAdjacentHTML('beforeend', '<p><strong>FLAG{xss_vulnerability_found}</strong></p>');
                }

                input.value = ''; // Clear input
            }
        }

    </script>
</body>

</html>