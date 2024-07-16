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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Management</title>
</head>
<body>
    <h2>Add New Product</h2>
    <form action="products.php" method="POST">
        <label for="name">Product Name:</label><br>
        <input type="text" id="name" name="name" required><br><br>

        <label for="category">Category:</label><br>
        <select id="category" name="category" required>
            <?php
            $sql = "SELECT * FROM categories";
            $result = $conn->query($sql);
            if ($result === false) {
                echo "Error: " . $conn->error;
            } else {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    }
                } else {
                    echo "<option>No categories found</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="price">Price:</label><br>
        <input type="number" id="price" name="price" step="0.01" required><br><br>

        <input type="submit" name="submit" value="Add Product">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $category_id = $_POST['category'];
        $price = $_POST['price'];

        $sql = "INSERT INTO products (name, category_id, price) VALUES ('$name', '$category_id', '$price')";
        
        if ($conn->query($sql) === TRUE) {
            echo "New product added successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    ?>

    <h2>Product List</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Created At</th>
        </tr>
        <?php
        $sql = "SELECT p.id, p.name, c.name AS category_name, p.price, p.created_at 
                FROM products p
                JOIN categories c ON p.category_id = c.id";
        $result = $conn->query($sql);
        if ($result === false) {
            echo "Error: " . $conn->error;
        } else {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['category_name']}</td>
                            <td>{$row['price']}</td>
                            <td>{$row['created_at']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No products found</td></tr>";
            }
        }
        ?>
    </table>

    <?php $conn->close(); ?>
</body>
</html>
