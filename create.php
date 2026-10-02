<?php
require_once 'config/database.php';
require_once 'config/flash.php';

$pdo = Database::getInstance()->getConnection();

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
        $errors[] = "Nama produk wajib diisi.";
    }
    if ($categoryId <= 0) {
        $errors[] = "Silakan pilih kategori yang valid.";
    }
    if ($supplierId <= 0) {
        $errors[] = "Silakan pilih supplier yang valid.";
    }
    if ($price <= 0) {
        $errors[] = "Harga harus lebih besar dari 0.";
    }
    if ($stock < 0) {
        $errors[] = "Stok tidak boleh bernilai negatif.";
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO products (name, category_id, supplier_id, price, stock) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $categoryId, $supplierId, $price, $stock]);

            set_flash('success', 'Data produk berhasil ditambahkan!');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            error_log("Create Product Error: " . $e->getMessage());
            $errors[] = "Gagal menyimpan data ke database.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Inventaris</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Tambah Produk Baru</h1>
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

            <form action="create.php" method="POST">
                <div class="form-group">
                    <label for="name">Nama Produk</label>
                    <input type="text" id="name" name="name" class="input-text" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">Kategori</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="supplier_id">Supplier</label>
                        <select id="supplier_id" name="supplier_id" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($suppliers as $sup): ?>
                                <option value="<?= $sup['id'] ?>" <?= (isset($_POST['supplier_id']) && $_POST['supplier_id'] == $sup['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sup['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Harga (Rp)</label>
                        <input type="number" id="price" name="price" step="100" class="input-text" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="stock">Jumlah Stok</label>
                        <input type="number" id="stock" name="stock" class="input-text" value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>" required>
                    </div>
                </div>

                <div style="margin-top: 10px;">
                    <button type="submit" class="btn btn-primary">Simpan Produk</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>