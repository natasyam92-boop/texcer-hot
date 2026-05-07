<?php 
// PERBAIKAN: session_start() SUDAH ADA DI config.php, jadi dihapus dari sini
include 'config.php';

// Update Status
if(isset($_POST['update_status'])){
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    // Pastikan koneksi ada
    if ($conn) {
        $sql = "UPDATE orders SET status = '$status' WHERE id = '$order_id'";
        mysqli_query($conn, $sql);
        echo "<script>alert('Status berhasil diupdate!'); window.location='admin.php';</script>";
        exit;
    }
}

$orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Texcer Hot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #0a0a0a; color: white; font-family: 'Segoe UI', sans-serif; }
        
        .admin-header {
            background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
            padding: 20px;
            text-align: center;
        }
        .admin-header h2 { color: white; }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            padding: 20px;
        }
        .stat-card {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #222;
        }
        .stat-number { font-size: 2rem; font-weight: 700; color: #ff6600; }
        .stat-label { color: #888; font-size: 0.85rem; }
        
        .order-card {
            background: #1a1a1a;
            margin: 15px;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #222;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #222;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending { background: #4a3800; color: #ffc107; }
        .status-diproses { background: #004a4a; color: #00e5ff; }
        .status-dikirim { background: #003366; color: #4dabf7; }
        .status-selesai { background: #004d00; color: #51cf66; }
        .status-dibatalkan { background: #4d0000; color: #ff6b6b; }
        
        .status-select {
            background: #222;
            color: white;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 0.9rem;
        }
        .status-select:focus {
            outline: none;
            border-color: #ff6600;
        }
        .btn-update {
            background: #ff6600;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .bottom-nav { 
            position: fixed; bottom: 0; width: 100%; background: #111; 
            border-top: 1px solid #222; z-index: 1000; padding: 10px 0;
        }
        .nav-item { 
            color: #666; text-decoration: none; text-align: center; 
            font-size: 0.75rem; display: flex; flex-direction: column; align-items: center; gap: 5px;
        }
        .nav-item i { font-size: 1.3rem; }
        .nav-item.active { color: #ff6600; }
    </style>
</head>
<body>

<!-- Header -->
<div class="admin-header">
    <h2><i class="fas fa-user-shield"></i> Admin Panel</h2>
    <p style="color: rgba(255,255,255,0.8);">Kelola Pesanan Texcer Hot</p>
</div>

<!-- Stats -->
<div class="stats-row">
    <?php
    $pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='Pending'"));
    $diproses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='Diproses'"));
    $dikirim = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='Dikirim'"));
    $selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='Selesai'"));
    ?>
    <div class="stat-card">
        <div class="stat-number"><?= $pending['count'] ?></div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $diproses['count'] ?></div>
        <div class="stat-label">Diproses</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $dikirim['count'] ?></div>
        <div class="stat-label">Dikirim</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $selesai['count'] ?></div>
        <div class="stat-label">Selesai</div>
    </div>
</div>

<!-- Orders List -->
<h4 style="padding: 0 20px;">Daftar Pesanan</h4>

<?php if(mysqli_num_rows($orders) > 0): ?>
    <?php while($o = mysqli_fetch_assoc($orders)): ?>
        <?php $items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = {$o['id']}"); ?>
        
        <div class="order-card">
            <div class="order-header">
                <div>
                    <h6 style="color: #ff6600;">Order #<?= $o['id'] ?></h6>
                    <p style="color: #888; font-size: 0.85rem;"><?= $o['customer_name'] ?> • <?= date('d M Y, H:i', strtotime($o['order_date'])) ?></p>
                </div>
                <span class="status-badge status-<?= strtolower($o['status']) ?>"><?= $o['status'] ?></span>
            </div>
            
            <div style="margin-bottom: 15px;">
                <?php while($i = mysqli_fetch_assoc($items)): ?>
                <div style="display: flex; justify-content: space-between; padding: 5px 0; color: #aaa; font-size: 0.9rem;">
                    <span>• <?= $i['product_name'] ?> (x<?= $i['qty'] ?>)</span>
                    <!-- Gunakan subtotal jika price error, atau $i['price'] jika kolom sudah ada -->
                    <span>Rp <?= number_format($i['subtotal'], 0, ',', '.') ?></span>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #222;">
                <div>
                    <span style="color: #888;">Total:</span>
                    <span style="color: #ff6600; font-weight: 700; font-size: 1.1rem;"> Rp <?= number_format($o['total_price'], 0, ',', '.') ?></span>
                </div>
                
                <form method="POST" style="display: flex; gap: 10px;">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <select name="status" class="status-select">
                        <option value="Pending" <?= $o['status']=='Pending'?'selected':'' ?>>Pending</option>
                        <option value="Diproses" <?= $o['status']=='Diproses'?'selected':'' ?>>Dimasak</option>
                        <option value="Dikirim" <?= $o['status']=='Dikirim'?'selected':'' ?>>Dikirim</option>
                        <option value="Selesai" <?= $o['status']=='Selesai'?'selected':'' ?>>Selesai</option>
                        <option value="Dibatalkan" <?= $o['status']=='Dibatalkan'?'selected':'' ?>>Dibatalkan</option>
                    </select>
                    <button type="submit" name="update_status" class="btn-update">
                        <i class="fas fa-check"></i> Update
                    </button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="padding: 20px; color: #888; text-align: center;">Belum ada pesanan.</p>
<?php endif; ?>

<!-- Bottom Nav -->
<div class="bottom-nav d-flex justify-content-around">
    <a href="index.php" class="nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="checkout.php" class="nav-item">
        <i class="fas fa-shopping-cart"></i>
        <span>Keranjang</span>
    </a>
    <a href="riwayat.php" class="nav-item">
        <i class="fas fa-history"></i>
        <span>Riwayat</span>
    </a>
    <a href="admin.php" class="nav-item active">
        <i class="fas fa-user-shield"></i>
        <span>Admin</span>
    </a>
</div>

</body>
</html>