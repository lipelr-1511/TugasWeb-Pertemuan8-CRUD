<?php
require_once 'config/database.php';
require_once 'config/flash.php';

$pdo = Database::getInstance()->getConnection();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$suppliers  = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $supplierId = (int)($_POST['supplier_id'] ?? 0);
    $price      = (float)($_POST['price'] ?? 0);
    $stock      = (int)($_POST['stock'] ?? 0);

    if (empty($name)) {
        $errors[] = "Nama produk tidak boleh kosong.";
    }
    if ($categoryId <= 0 || $supplierId <= 0) {
        $errors[] = "Kategori dan Supplier harus dipilih.";
    }
    if ($price <= 0 || $stock < 0) {
        $errors[] = "Harga dan Stok harus berada dalam rentang bernilai valid.";
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE products
                    SET name = ?, category_id = ?, supplier_id = ?, price = ?, stock = ? 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $categoryId, $supplierId, $price, $stock, $id]);

            set_flash('success', 'Data produk berhasil diperbarui!');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            error_log("Update Product Error: " . $e->getMessage());
            $errors[] = "Gagal memperbarui data produk.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Inventaris</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Edit Produk</h1>
        </header>

        <div class="card">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="edit.php?id=<?= $id ?>" method="POST">
                <div class="form-group">
                    <label for="name">Nama Produk</label>
                    <input type="text" id="name" name="name" class="input-text" value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">Kategori</label>
                        <select id="category_id" name="category_id" required>
                            <?php foreach ($categories as $cat): ?>
                                <?php
                                    $selectedId = $_POST['category_id'] ?? $product['category_id'];
                                ?>
                                <option value="<?= $cat['id'] ?>" <?= $selectedId == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="supplier_id">Supplier</label>
                        <select id="supplier_id" name="supplier_id" required>
                            <?php foreach ($suppliers as $sup): ?>
                                <?php
                                    $selectedId = $_POST['supplier_id'] ?? $product['supplier_id'];
                                ?>
                                <option value="<?= $sup['id'] ?>" <?= $selectedId == $sup['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sup['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Harga (Rp)</label>
                        <input type="number" id="price" name="price" step="100" class="input-text" value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="stock">Jumlah Stok</label>
                        <input type="number" id="stock" name="stock" class="input-text" value="<?= htmlspecialchars($_POST['stock'] ?? $product['stock']) ?>" required>
                    </div>
                </div>

                <div style="margin-top: 10px;">
                    <button type="submit" class="btn btn-primary">Perbarui Produk</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>