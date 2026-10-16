<?php
// index.php
include 'auth.php';
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'guest';

// Initialize session cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart
if (isset($_GET['order_id']) && $role != 'admin') {
    $product_id = intval($_GET['order_id']);
    if ($product_id > 0) {
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
    }
    header("Location: index.php");
    exit();
}

// Remove item from cart
if (isset($_GET['remove']) && $role != 'admin') {
    $remove_id = intval($_GET['remove']);
    unset($_SESSION['cart'][$remove_id]);
    header("Location: index.php");
    exit();
}

// Fetch all products
$query = "SELECT id, name, price, url_image FROM product";
$result = mysqli_query($conn, $query);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[$row['id']] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container my-4">
        <!-- Coffee Menu Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Coffee Menu</h4>
                <?php if ($role == 'admin'): ?>
                    <a href="create.php" class="btn btn-success btn-sm">+ Add Product</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($products as $p): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <img src="<?= htmlspecialchars($p['url_image']) ?>" width="80" class="img-fluid rounded" alt="coffee image">
                                </td>
                                <td><?= htmlspecialchars($p['name']) ?></td>
                                <td>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <strike><?= htmlspecialchars($p['price']) ?></strike><br>
                                        <?= htmlspecialchars($p['price'] * 0.9) ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($p['price']) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($role == 'admin'): ?>
                                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    <?php else: ?>
                                        <a href="index.php?order_id=<?= $p['id'] ?>" class="btn btn-success btn-sm">Order</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Simple Shopping Cart Section (Only for non-admins) -->
        <?php if ($role != 'admin'): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Shopping Cart</h4>
            </div>
            <div class="card-body">
                <?php if (empty($_SESSION['cart'])): ?>
                    <p class="text-muted mb-0">Your cart is empty. Click "Order" above to add items.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $c_no = 1;
                                $grand_total = 0;
                                foreach ($_SESSION['cart'] as $c_id => $qty): 
                                    if (!isset($products[$c_id])) continue;
                                    $item = $products[$c_id];
                                    $price = (isset($_SESSION['user_id'])) ? ($item['price'] * 0.9) : $item['price'];
                                    $subtotal = $price * $qty;
                                    $grand_total += $subtotal;
                                ?>
                                <tr>
                                    <td><?= $c_no++ ?></td>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= htmlspecialchars($price) ?></td>
                                    <td><?= $qty ?></td>
                                    <td><?= htmlspecialchars($subtotal) ?></td>
                                    <td class="text-center">
                                        <a href="index.php?remove=<?= $c_id ?>" class="btn btn-danger btn-sm">Remove</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td colspan="2" class="fw-bold"><?= htmlspecialchars($grand_total) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>