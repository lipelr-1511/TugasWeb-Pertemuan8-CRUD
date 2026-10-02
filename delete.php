<?php
require_once 'config/database.php';
require_once 'config/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'ID produk tidak valid.');
    header('Location: index.php');
    exit;
}

$pdo = Database::getInstance()->getConnection();

try {
    $pdo->beginTransaction();

    $stmtSelect = $pdo->prepare("SELECT name FROM products WHERE id = ?");
    $stmtSelect->execute([$id]);
    $product = $stmtSelect->fetch();

    if ($product) {
        $stmtDelete = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmtDelete->execute([$id]);

        $stmtLog = $pdo->prepare("INSERT INTO stock_logs (product_name, action) VALUES (?, ?)");
        $stmtLog->execute([$product['name'], 'DELETE_PRODUCT']);

        $pdo->commit();
        set_flash('success', 'Data produk berhasil dihapus dan dicatat di log.');
    } else {
        $pdo->rollBack();
        set_flash('danger', 'Produk tidak ditemukan.');
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Delete Transaction Error: " . $e->getMessage());
    set_flash('danger', 'Terjadi kesalahan saat menghapus data.');
}

header('Location: index.php');
exit;