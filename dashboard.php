<?php
session_start();
include 'config.php';

// Cek apakah admin
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: auth/login.php");
    exit;
}

// Stats
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'];
$pendingOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status IN ('Menunggu Konfirmasi', 'Diproses')"))['total'];
$todayRevenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'Selesai'"))['total'] ?? 0;
$customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];

// Ambil pesanan terbaru
$recentOrders = mysqli_query($conn, "
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    LEFT JOIN users u ON o.customer_phone = u.phone 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Texcer Hot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #8B6F4E;
            --primary-dark: #6B5637;
            --secondary: #D4A574;
            --bg-cream: #FDF8F3;
            --bg-white: #FFFFFF;
            --text-dark: #3D2914;
            --text-gray: #8B7355;
            --border: #E8DDD4;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f5f5f5;
            color: var(--text-dark);
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--bg-white);
            min-height: 100vh;
            padding: 24px 16px;
            position: fixed;
            left: 0;
            top: 0;
            border-right: 1px solid var(--border);
        }
        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            padding: 12px 16px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-menu { list-style: none; }
        .sidebar-menu li { margin-bottom: 4px; }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-gray);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s;
            font-weight: 500;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: var(--bg-cream);
            color: var(--primary);
        }
        .sidebar-menu i { width: 20px; text-align: center; }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 24px;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }
        .header h1 { font-size: 1.8rem; font-weight: 700; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        .btn-logout {
            color: var(--text-gray);
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-logout:hover { color: var(--danger); }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: var(--bg-white);
            padding: 20px;
            border-radius: 16px;
            border: 1px solid var(--border);
        }
        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 1.3rem;
        }
        .stat-card.blue .icon { background: #DBEAFE; color: #2563EB; }
        .stat-card.orange .icon { background: #FFEDD5; color: #EA580C; }
        .stat-card.green .icon { background: #D1FAE5; color: #059669; }
        .stat-card.purple .icon { background: #EDE9FE; color: #7C3AED; }
        
        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-dark);
        }
        .stat-card .label {
            color: var(--text-gray);
            font-size: 0.9rem;
        }
        
        /* Orders Table */
        .section {
            background: var(--bg-white);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .section-title { font-size: 1.3rem; font-weight: 700; }
        
        .orders-table { width: 100%; border-collapse: collapse; }
        .orders-table th {
            text-align: left;
            padding: 12px 16px;
            color: var(--text-gray);
            font-weight: 600;
            font-size: 0.85rem;
            border-bottom: 1px solid var(--border);
        }
        .orders-table td {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.95rem;
        }
        .orders-table tr:hover { background: #fafafa; }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending { background: #FFF7ED; color: #C2410C; }
        .status-processing { background: #FEF3C7; color: #92400E; }
        .status-shipped { background: #DBEAFE; color: #1E40AF; }
        .status-completed { background: #D1FAE5; color: #065F46; }
        .status-cancelled { background: #FEE2E2; color: #B91C1C; }
        
        .action-btn {
            padding: 6px 14px;
            border-radius: 8px;
            border: none;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-view { background: #F3F4F6; color: var(--text-dark); }
        .btn-view:hover { background: #E5E7EB; }
        .btn-update { background: var(--primary); color: white; }
        .btn-update:hover { background: var(--primary-dark); }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 70px; padding: 24px 8px; }
            .sidebar-logo span, .sidebar-menu a span { display: none; }
            .sidebar-menu a { justify-content: center; padding: 12px; }
            .sidebar-menu i { margin: 0; }
            .main-content { margin-left: 70px; }
        }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-pepper-hot"></i>
        <span>Texcer Hot</span>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
        <li><a href="#"><i class="fas fa-box"></i> <span>Pesanan</span></a></li>
        <li><a href="#"><i class="fas fa-utensils"></i> <span>Produk</span></a></li>
        <li><a href="#"><i class="fas fa-users"></i> <span>Pelanggan</span></a></li>
        <li><a href="#"><i class="fas fa-chart-bar"></i> <span>Laporan</span></a></li>
        <li><a href="#"><i class="fas fa-cog"></i> <span>Pengaturan</span></a></li>
    </ul>
</aside>

<!-- Main Content -->
<main class="main-content">
    <!-- Alert Notifications -->
<?php if(isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; margin-bottom: 24px; border: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);">
    <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; margin-bottom: 24px; border: none; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);">
    <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['error']); endif; ?>
    <!-- Header -->
    <div class="header">
        <h1>👋 Halo, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
            <a href="auth/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="value"><?= $totalOrders ?></div>
            <div class="label">Total Pesanan</div>
        </div>
        <div class="stat-card orange">
            <div class="icon"><i class="fas fa-clock"></i></div>
            <div class="value"><?= $pendingOrders ?></div>
            <div class="label">Menunggu Proses</div>
        </div>
        <div class="stat-card green">
            <div class="icon"><i class="fas fa-wallet"></i></div>
            <div class="value">Rp<?= number_format($todayRevenue, 0, ',', '.') ?></div>
            <div class="label">Pendapatan Hari Ini</div>
        </div>
        <div class="stat-card purple">
            <div class="icon"><i class="fas fa-user-friends"></i></div>
            <div class="value"><?= $customers ?></div>
            <div class="label">Total Pelanggan</div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="section">
        <div class="section-header">
            <h3 class="section-title">Pesanan Terbaru</h3>
            <a href="#" class="btn btn-sm" style="background: var(--primary); color: white; border-radius: 8px; padding: 8px 16px; text-decoration: none;">Lihat Semua</a>
        </div>
        
        <table class="orders-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($order = mysqli_fetch_assoc($recentOrders)): 
                    $statusClass = match($order['status']){
                        'Menunggu Konfirmasi', 'Pending' => 'status-pending',
                        'Diproses', 'Dikemas' => 'status-processing',
                        'Dikirim' => 'status-shipped',
                        'Selesai' => 'status-completed',
                        'Dibatalkan' => 'status-cancelled',
                        default => ''
                    };
                ?>
                <tr>
                    <td>#<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($order['customer_name'] ?? $order['customer_phone'] ?? 'Guest / Tidak Diketahui') ?></td>
                    <td><strong>Rp<?= number_format($order['total_price'], 0, ',', '.') ?></strong></td>
                    <td><span class="status-badge <?= $statusClass ?>"><?= $order['status'] ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'] ?? $order['tanggal'] ?? $order['order_date'] ?? $order['date'] ?? 'now')) ?></td>
                    <td>
                        <button class="action-btn btn-view" onclick="viewOrder(<?= $order['id'] ?>)">Lihat</button>
                        <?php if(!in_array($order['status'], ['Selesai', 'Dibatalkan'])): ?>
                        <button class="action-btn btn-update" onclick="updateStatus(<?= $order['id'] ?>)">Update</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function viewOrder(id) {
    alert('Lihat detail pesanan #' + id);
    // window.location.href = 'order_detail.php?id=' + id;
}

function updateStatus(id) {
    // Ambil data dari tabel (cari row berdasarkan ID)
    const rows = document.querySelectorAll('.orders-table tbody tr');
    let orderData = null;
    
    rows.forEach(row => {
        const cellId = row.cells[0].textContent.replace('#', '').replace(/^0+/, '');
        if(parseInt(cellId) === id) {
            orderData = {
                id: id,
                customer: row.cells[1].textContent.trim(),
                total: row.cells[2].textContent.trim(),
                status: row.cells[3].textContent.trim()
            };
        }
    });
    
    if(!orderData) return;
    
    // Isi modal dengan data
    document.getElementById('update_order_id').value = orderData.id;
    document.getElementById('update_order_id_display').value = '#' + String(orderData.id).padStart(4, '0');
    document.getElementById('update_customer').value = orderData.customer;
    document.getElementById('update_total').value = orderData.total;
    document.getElementById('update_current_status').value = orderData.status;
    
    // Reset form & hide resi field
    document.getElementById('resi_field').style.display = 'none';
    document.querySelector('select[name="new_status"]').value = '';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('modalUpdateStatus'));
    modal.show();
}

// Auto show/hide resi field
document.addEventListener('change', function(e) {
    if(e.target.name === 'new_status') {
        const resiField = document.getElementById('resi_field');
        resiField.style.display = (e.target.value === 'Dikirim') ? 'block' : 'none';
    }
});
</script>
<!-- Modal Update Status -->
<div class="modal fade" id="modalUpdateStatus" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <form method="POST" action="update_status.php">
                <div class="modal-header" style="border-bottom: 1px solid var(--border); background: var(--bg-cream); border-radius: 16px 16px 0 0; padding: 20px 24px;">
                    <h5 class="modal-title" style="font-weight: 700; color: var(--text-dark);">
                        <i class="fas fa-edit me-2"></i>Update Status Pesanan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body" style="padding: 24px;">
                    <input type="hidden" name="order_id" id="update_order_id">
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; color: var(--text-dark);">ID Pesanan</label>
                        <input type="text" class="form-control" id="update_order_id_display" readonly style="background: #f9f9f9; border-radius: 10px;">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; color: var(--text-dark);">Pelanggan</label>
                        <input type="text" class="form-control" id="update_customer" readonly style="background: #f9f9f9; border-radius: 10px;">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; color: var(--text-dark);">Total</label>
                        <input type="text" class="form-control" id="update_total" readonly style="background: #f9f9f9; border-radius: 10px;">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; color: var(--text-dark);">Status Saat Ini</label>
                        <input type="text" class="form-control" id="update_current_status" readonly style="background: #f9f9f9; border-radius: 10px;">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; color: var(--text-dark);">Ubah Status Ke</label>
                        <select name="new_status" class="form-select" style="border-radius: 10px; padding: 10px 14px;" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="Menunggu Konfirmasi">⏳ Menunggu Konfirmasi</option>
                            <option value="Diproses">🔄 Diproses</option>
                            <option value="Dikemas">📦 Dikemas</option>
                            <option value="Dikirim">🚚 Dikirim</option>
                            <option value="Selesai">✅ Selesai</option>
                            <option value="Dibatalkan">❌ Dibatalkan</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="resi_field" style="display: none;">
                        <label class="form-label" style="font-weight: 600; color: var(--text-dark);">📮 Nomor Resi</label>
                        <input type="text" name="resi_number" class="form-control" placeholder="Contoh: JNE123456789" style="border-radius: 10px;">
                        <small class="text-muted">Diisi jika status = Dikirim</small>
                    </div>
                </div>
                
                <div class="modal-footer" style="border-top: 1px solid var(--border); padding: 16px 24px; background: var(--bg-cream); border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
                    <button type="submit" class="btn" style="background: var(--primary); color: white; border-radius: 8px; padding: 10px 24px; border: none;">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>