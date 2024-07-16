<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Ecommerce";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming the user is logged in, use a dummy UserID for demonstration
// Replace this with the actual logged-in user's ID
$loggedInUserID = 1; 

// Add Product to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    $sql = "SELECT price FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($price);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        $sql = "INSERT INTO shopping_cart (customer_id, product_id, quantity) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("iii", $loggedInUserID, $product_id, $quantity);
        $stmt->execute();
    }
    $stmt->close();
}

// Update Cart
if (isset($_POST['update_cart'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];

    $sql = "UPDATE shopping_cart SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ii", $quantity, $cart_id);
    $stmt->execute();
    $stmt->close();
}

// Remove Product from Cart
if (isset($_POST['remove_from_cart'])) {
    $cart_id = $_POST['cart_id'];

    $sql = "DELETE FROM shopping_cart WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch Cart Items
$sql = "SELECT c.id, p.name AS product_name, c.quantity, p.price, (c.quantity * p.price) AS total
        FROM shopping_cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.customer_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $loggedInUserID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
</head>
<body>
    <h2>Shopping Cart</h2>
    <table border="1">
        <tr>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
            <th>Actions</th>
        </tr>
        <?php
        $totalAmount = 0;
        while ($row = $result->fetch_assoc()) {
            $totalAmount += $row['total'];
            echo "<tr>
                    <td>{$row['product_name']}</td>
                    <td>
                        <form action='cart.php' method='POST'>
                            <input type='hidden' name='cart_id' value='{$row['id']}'>
                            <input type='number' name='quantity' value='{$row['quantity']}' min='1' required>
                            <input type='submit' name='update_cart' value='Update'>
                        </form>
                    </td>
                    <td>{$row['price']}</td>
                    <td>{$row['total']}</td>
                    <td>
                        <form action='cart.php' method='POST'>
                            <input type='hidden' name='cart_id' value='{$row['id']}'>
                            <input type='submit' name='remove_from_cart' value='Remove'>
                        </form>
                    </td>
                  </tr>";
        }
        ?>
    </table>
    <h3>Total Amount: $<?php echo number_format($totalAmount, 2); ?></h3>
    <hr>
    <h3>Add New Product</h3>
    <form action="cart.php" method="POST">
        <label for="product_id">Product:</label><br>
        <select id="product_id" name="product_id" required>
            <?php
            $sql = "SELECT id, name FROM products";
            $result = $conn->query($sql);
            if ($result === false) {
                die("Error fetching products: " . $conn->error);
            }
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select><br><br>

        <label for="quantity">Quantity:</label><br>
        <input type="number" id="quantity" name="quantity" min="1" required><br><br>

        <input type="submit" name="add_to_cart" value="Add to Cart">
    </form>
</body>
</html>

<?php
$conn->close();
?>
