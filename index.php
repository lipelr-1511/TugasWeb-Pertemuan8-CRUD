<?php
require_once 'config/database.php';
require_once 'config/flash.php';

$pdo = Database::getInstance()->getConnection();

$search = trim($_GET['search'] ?? '');
$params = [];

$sql = "SELECT p.id, p.name, p.price, p.stock, 
               c.name AS category_name, 
               s.name AS supplier_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN suppliers s ON p.supplier_id = s.id";

if ($search !== '') {
    $sql .= " WHERE p.name LIKE ? OR c.name LIKE ? OR s.name LIKE ?";
    $searchTerm = "%{$search}%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Inventaris Barang</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistem Manajemen Inventaris Barang</h1>
        </header>

        <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
        <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="top-bar">
                <a href="create.php" class="btn btn-primary">+ Tambah Produk</a>
                
                <form action="index.php" method="GET" class="search-form">
                    <input type="text" name="search" class="input-text" placeholder="Cari nama, kategori, supplier..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-secondary">Cari</button>
                    <?php if ($search !== ''): ?>
                        <a href="index.php" class="btn btn-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Supplier</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['category_name']) ?></td>
                                <td><?= htmlspecialchars($item['supplier_name']) ?></td>
                                <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                <td><?= (int)$item['stock'] ?></td>
                                <td class="actions">
                                    <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Edit</a>
                                    
                                    <form method="POST" action="delete.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 4px 10px; font-size: 0.8rem;">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b;">Tidak ada data produk yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>