<?php
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

// Add to Wish List
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_wishlist'])) {
    $customer_id = $_POST['customer_id'];
    $product_id = $_POST['product_id'];

    // Check if product exists
    $sql = "SELECT id FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $sql = "INSERT INTO wish_list (customer_id, product_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("ii", $customer_id, $product_id);

        if ($stmt->execute()) {
            echo "Product added to wish list successfully";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Error: Product ID does not exist";
    }

    $stmt->close();
}

// Remove from Wish List
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_from_wishlist'])) {
    $wishlist_id = $_POST['wishlist_id'];

    $sql = "DELETE FROM wish_list WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $wishlist_id);

    if ($stmt->execute()) {
        echo "Product removed from wish list successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// List Wish List Items
$customer_id = 1; // Replace with dynamic customer ID as needed
$sql = "SELECT wish_list.id, products.name, products.price 
        FROM wish_list 
        JOIN products ON wish_list.product_id = products.id 
        WHERE wish_list.customer_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Wish List</title>
</head>
<body>
    <h2>Add to Wish List</h2>
    <form method="POST" action="">
        Customer ID: <input type="number" name="customer_id" required><br>
        Product ID: <input type="number" name="product_id" required><br>
        <input type="submit" name="add_to_wishlist" value="Add to Wish List">
    </form>

    <h2>Remove from Wish List</h2>
    <form method="POST" action="">
        Wish List ID: <input type="number" name="wishlist_id" required><br>
        <input type="submit" name="remove_from_wishlist" value="Remove from Wish List">
    </form>

    <h2>Wish List Items</h2>
    <table border="1">
        <tr>
            <th>Wish List ID</th>
            <th>Product Name</th>
            <th>Price</th>
        </tr>
        <?php
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['id'] . "</td><td>" . $row['name'] . "</td><td>" . $row['price'] . "</td></tr>";
        }
        ?>
    </table>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
