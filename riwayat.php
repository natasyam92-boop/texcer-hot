<?php include 'config.php'; 
include 'functions.php';

// Submit Review
if(isset($_POST['submit_review'])){
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    $sql = "INSERT INTO reviews (order_id, rating, comment) VALUES ('$order_id', '$rating', '$comment')";
    mysqli_query($conn, $sql);
    echo "<script>alert('Terima kasih atas ulasan Anda!'); window.location='riwayat.php';</script>";
    exit;
}

// Filter berdasarkan tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'semua';
$where = "";
if($tab == 'pending') $where = "WHERE status = 'Pending'";
elseif($tab == 'diproses') $where = "WHERE status = 'Diproses'";
elseif($tab == 'dikirim') $where = "WHERE status = 'Dikirim'";
elseif($tab == 'selesai') $where = "WHERE status = 'Selesai'";
elseif($tab == 'dibatalkan') $where = "WHERE status = 'Dibatalkan'";

$orders = mysqli_query($conn, "SELECT * FROM orders $where ORDER BY id DESC");

// Variabel untuk sidebar active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Texcer Hot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* === DESKTOP DARK THEME === */
        :root {
            --bg-dark: #121212;
            --bg-card: #1e1e1e;
            --bg-sidebar: #181818;
            --accent: #ff6b35;
            --accent-hover: #ff8555;
            --text-primary: #ffffff;
            --text-secondary: #a0a0a0;
            --border-color: #2a2a2a;
            --shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            background: var(--bg-dark); 
            color: var(--text-primary); 
            font-family: 'Segoe UI', system-ui, sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* === SIDEBAR === */
        .sidebar {
            width: 250px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            padding: 25px 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--accent);
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo i { font-size: 1.8rem; }
        
        .nav-menu { list-style: none; }
        .nav-item { margin-bottom: 8px; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 12px;
            transition: 0.3s;
            font-weight: 500;
            position: relative;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 107, 53, 0.15);
            color: var(--accent);
        }
        .nav-link i { width: 20px; text-align: center; }
        .cart-badge {
            position: absolute;
            top: 8px;
            right: 15px;
            background: var(--accent);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }

        /* === MAIN CONTENT === */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        /* === PAGE HEADER === */
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent);
        }
        
        /* === TABS === */
        .tabs-container {
            background: var(--bg-card);
            padding: 0 10px;
            border-radius: 12px;
            margin-bottom: 25px;
            overflow-x: auto;
            white-space: nowrap;
            border: 1px solid var(--border-color);
        }
        .tabs-container::-webkit-scrollbar { display: none; }
        .tab-btn {
            display: inline-block;
            padding: 15px 20px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            transition: 0.3s;
        }
        .tab-btn:hover { color: var(--accent); }
        .tab-btn.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }
        
        /* === ORDER CARD === */
        .order-card {
            background: var(--bg-card);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        .order-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }
        .order-store {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .store-name { font-weight: 600; color: var(--text-primary); font-size: 1.1rem; }
        .order-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending { background: #4a3800; color: #ffc107; }
        .status-diproses { background: #004a4a; color: #00e5ff; }
        .status-dikirim { background: #003366; color: #4dabf7; }
        .status-selesai { background: #004d00; color: #51cf66; }
        .status-dibatalkan { background: #4d0000; color: #ff6b6b; }
        
        /* Order Info */
        .order-info {
            padding: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
            background: rgba(255,107,53,0.05);
        }
        .delivery-icon {
            width: 55px;
            height: 55px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
        }
        .delivery-text h6 { color: var(--text-primary); margin-bottom: 5px; font-size: 1.05rem; }
        .delivery-text p { color: var(--text-secondary); font-size: 0.9rem; }
        
        /* Order Items */
        .order-items {
            padding: 0 20px 20px;
        }
        .item-row {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .item-row:last-child { border-bottom: none; }
        .item-img {
            width: 80px;
            height: 80px;
            background: var(--bg-sidebar);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }
        .item-details { flex: 1; }
        .item-name { color: var(--text-primary); font-size: 1rem; margin-bottom: 5px; font-weight: 600; }
        .item-variant { color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 5px; }
        .item-price { color: var(--accent); font-weight: 700; font-size: 1.05rem; }
        .item-qty { color: var(--text-secondary); font-size: 0.85rem; }
        
        /* Total */
        .order-total {
            padding: 15px 20px;
            text-align: right;
            border-top: 1px solid var(--border-color);
            background: var(--bg-sidebar);
        }
        .total-label { color: var(--text-secondary); font-size: 0.9rem; }
        .total-price { color: var(--accent); font-size: 1.3rem; font-weight: 700; }
        
        /* Action Buttons */
        .order-actions {
            padding: 20px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            border-top: 1px solid var(--border-color);
        }
        .btn-action {
            padding: 12px 25px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-secondary { background: var(--bg-sidebar); color: var(--accent); border: 1px solid var(--accent); }
        .btn-secondary:hover { background: var(--accent); color: white; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: var(--accent-hover); }
        
        /* Stars Rating */
        .stars { display: flex; gap: 8px; margin: 10px 0; }
        .star { color: var(--text-secondary); font-size: 1.8rem; cursor: pointer; transition: 0.2s; }
        .star.active { color: #ffc107; }
        .star:hover { transform: scale(1.2); }
        
        /* Review Form */
        .review-form {
            background: var(--bg-sidebar);
            padding: 25px;
            margin: 0 20px 20px;
            border-radius: 12px;
            display: none;
            border: 1px solid var(--border-color);
        }
        .review-form.active { display: block; }
        .review-form textarea {
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            color: var(--text-primary);
            resize: none;
            margin-top: 15px;
            font-size: 0.95rem;
        }
        .review-form textarea:focus {
            outline: none;
            border-color: var(--accent);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-secondary);
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
            color: var(--accent);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 20px 10px;
            }
            .logo span, .nav-link span { display: none; }
            .nav-link { justify-content: center; padding: 15px; }
            .nav-link i { margin: 0; }
            .main-content { margin-left: 80px; }
        }
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
                display: flex;
                justify-content: space-around;
                padding: 15px;
            }
            .logo { display: none; }
            .nav-menu { display: flex; gap: 5px; }
            .nav-item { margin: 0; }
            .nav-link { padding: 10px 15px; flex-direction: column; gap: 5px; font-size: 0.75rem; }
            .nav-link i { font-size: 1.2rem; }
            .main-content { margin-left: 0; padding: 20px; }
            .item-row { flex-direction: column; }
            .item-img { width: 100%; height: 150px; }
            .order-actions { flex-direction: column; }
            .btn-action { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="logo">
        <i class="fas fa-fire"></i>
        <span>Texcer Hot</span>
    </div>
    
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?= $currentPage == 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="riwayat.php" class="nav-link <?= $currentPage == 'riwayat.php' ? 'active' : '' ?>">
                <i class="fas fa-history"></i>
                <span>Order</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="checkout.php" class="nav-link <?= $currentPage == 'checkout.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
                <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="cart-badge"><?= count($_SESSION['cart']) ?></span>
                <?php endif; ?>
            </a>
        </li>
        <!-- ✅ MENU NOTIFIKASI -->
        <li class="nav-item">
            <a href="notifikasi.php" class="nav-link <?= $currentPage == 'notifikasi.php' ? 'active' : '' ?>">
                <i class="fas fa-bell"></i>
                <span>Notifikasi</span>
                <?php
                $user_phone = $_SESSION['user_phone'] ?? '';
                if(!empty($user_phone)){
                    $unread = mysqli_fetch_assoc(mysqli_query($conn, "
                        SELECT COUNT(*) as count FROM notifications 
                        WHERE user_phone = '$user_phone' AND is_read = 0
                    "))['count'];
                    if($unread > 0):
                ?>
                    <span class="cart-badge" style="background: #ff4757;"><?= $unread ?></span>
                <?php endif; } ?>
            </a>
        </li>
        <li class="nav-item">
            <a href="admin.php" class="nav-link <?= $currentPage == 'admin.php' ? 'active' : '' ?>">
                <i class="fas fa-user-shield"></i>
                <span>Profile</span>
            </a>
        </li>
    </ul>
</aside>

<!-- MAIN CONTENT -->
<main class="main-content">
    
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-history me-2"></i>Riwayat Pesanan</h1>
    </div>
    
    <!-- Tabs -->
    <div class="tabs-container">
        <a href="riwayat.php?tab=semua" class="tab-btn <?= $tab=='semua'?'active':'' ?>">Semua</a>
        <a href="riwayat.php?tab=pending" class="tab-btn <?= $tab=='pending'?'active':'' ?>">Perlu Dibayar</a>
        <a href="riwayat.php?tab=diproses" class="tab-btn <?= $tab=='diproses'?'active':'' ?>">Diproses</a>
        <a href="riwayat.php?tab=dikirim" class="tab-btn <?= $tab=='dikirim'?'active':'' ?>">Dikirim</a>
        <a href="riwayat.php?tab=selesai" class="tab-btn <?= $tab=='selesai'?'active':'' ?>">Selesai</a>
        <a href="riwayat.php?tab=dibatalkan" class="tab-btn <?= $tab=='dibatalkan'?'active':'' ?>">Dibatalkan</a>
    </div>

    <!-- Order List -->
    <?php if(mysqli_num_rows($orders) > 0): ?>
        <?php while($o = mysqli_fetch_assoc($orders)): ?>
            <?php 
            $items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = {$o['id']}");
            $status_class = strtolower($o['status']);
            $review = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM reviews WHERE order_id = {$o['id']}"));
            ?>
            
            <div class="order-card">
                <!-- Header -->
                <div class="order-header">
                    <div class="order-store">
                        <span style="color: var(--accent);">🔥</span>
                        <span class="store-name">Texcer Hot</span>
                        <i class="fas fa-chevron-right" style="color: var(--text-secondary); font-size: 0.8rem;"></i>
                    </div>
                    <span class="order-status status-<?= $status_class ?>"><?= $o['status'] ?></span>
                </div>
                
                <!-- Delivery Info -->
                <div class="order-info">
                    <div class="delivery-icon">
                        <?php if($o['status']=='Pending'): ?>
                            <i class="fas fa-clock"></i>
                        <?php elseif($o['status']=='Diproses'): ?>
                            <i class="fas fa-utensils"></i>
                        <?php elseif($o['status']=='Dikirim'): ?>
                            <i class="fas fa-motorcycle"></i>
                        <?php elseif($o['status']=='Selesai'): ?>
                            <i class="fas fa-check"></i>
                        <?php else: ?>
                            <i class="fas fa-times"></i>
                        <?php endif; ?>
                    </div>
                    <div class="delivery-text">
                        <h6>
                            <?php 
                            $time = date('H:i', strtotime($o['order_date']));
                            if($o['status']=='Pending') echo "$time Menunggu Pembayaran";
                            elseif($o['status']=='Diproses') echo "$time Sedang Dimasak";
                            elseif($o['status']=='Dikirim') echo "$time Sedang Dikirim";
                            elseif($o['status']=='Selesai') echo "$time Pesanan Selesai";
                            else echo "$time Pesanan Dibatalkan";
                            ?>
                        </h6>
                        <p>
                            <?php 
                            if($o['status']=='Diproses') echo "Pesanan Anda sedang disiapkan oleh dapur";
                            elseif($o['status']=='Dikirim') echo "Kurir sedang menuju lokasi Anda";
                            elseif($o['status']=='Selesai') echo "Terima kasih sudah memesan di Texcer Hot";
                            else echo "Status pesanan Anda saat ini";
                            ?>
                        </p>
                    </div>
                </div>
                
                <!-- Items -->
                <div class="order-items">
                    <?php while($i = mysqli_fetch_assoc($items)): ?>
                    <div class="item-row">
                        <div class="item-img">
                            <?php if(!empty($i['image'])): ?>
                                <img src="<?= $i['image'] ?>" alt="<?= $i['product_name'] ?>">
                            <?php else: ?>
                                <i class="fas fa-utensils"></i>
                            <?php endif; ?>
                        </div>
                        <div class="item-details">
                            <div class="item-name"><?= $i['product_name'] ?></div>
                            <div class="item-variant">Variant: Default</div>
                            <div class="item-price">Rp <?= number_format($i['subtotal'] / $i['qty'], 0, ',', '.') ?></div>
                            <div class="item-qty">x<?= $i['qty'] ?></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Total -->
                <div class="order-total">
                    <span class="total-label">Total Pesanan</span><br>
                    <span class="total-price">Rp <?= number_format($o['total_price'], 0, ',', '.') ?></span>
                </div>
                
                <!-- Review Section (Only for completed orders) -->
                <?php if($o['status'] == 'Selesai'): ?>
                    <?php if(!$review): ?>
                    <div class="order-actions">
                        <button class="btn-action btn-secondary" onclick="toggleReview(<?= $o['id'] ?>)">
                            <i class="fas fa-star"></i> Beri Ulasan
                        </button>
                        <a href="index.php" class="btn-action btn-primary">
                            <i class="fas fa-redo"></i> Beli Lagi
                        </a>
                    </div>
                    
                    <!-- Review Form -->
                    <div class="review-form" id="review-<?= $o['id'] ?>">
                        <h6 class="text-warning mb-3">Beri Rating & Ulasan</h6>
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            
                            <div class="stars" id="stars-<?= $o['id'] ?>">
                                <span class="star" data-rating="1" onclick="setRating(<?= $o['id'] ?>, 1)">★</span>
                                <span class="star" data-rating="2" onclick="setRating(<?= $o['id'] ?>, 2)">★</span>
                                <span class="star" data-rating="3" onclick="setRating(<?= $o['id'] ?>, 3)">★</span>
                                <span class="star" data-rating="4" onclick="setRating(<?= $o['id'] ?>, 4)">★</span>
                                <span class="star" data-rating="5" onclick="setRating(<?= $o['id'] ?>, 5)">★</span>
                            </div>
                            <input type="hidden" name="rating" id="rating-<?= $o['id'] ?>" value="0" required>
                            
                            <textarea name="comment" rows="3" placeholder="Tulis ulasan Anda..." required></textarea>
                            
                            <button type="submit" name="submit_review" class="btn-action btn-primary mt-3 w-100">
                                Kirim Ulasan
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <!-- Display Existing Review -->
                    <div style="padding: 20px; background: var(--bg-sidebar); margin: 0 20px 20px; border-radius: 12px; border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span style="color: #ffc107;">
                                <?php for($s=1; $s<=$review['rating']; $s++) echo "★"; ?>
                                <?php for($s=$review['rating']+1; $s<=5; $s++) echo "☆"; ?>
                            </span>
                            <span style="color: var(--text-secondary); font-size: 0.85rem;"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                        </div>
                        <p style="color: var(--text-primary); font-size: 0.95rem;"><?= $review['comment'] ?></p>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Cancel/Pay Button (Only for pending) -->
                <?php if($o['status'] == 'Pending'): ?>
                <div class="order-actions">
                    <a href="checkout.php" class="btn-action btn-primary">Bayar Sekarang</a>
                    <button class="btn-action btn-secondary" style="border-color: #ff6b6b; color: #ff6b6b;" onclick="if(confirm('Batalkan pesanan?')) window.location='?cancel=<?= $o['id'] ?>'">
                        <i class="fas fa-times"></i> Batalkan
                    </button>
                </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <h3>Belum ada pesanan</h3>
            <p style="margin: 15px 0 25px;">Silakan pesan makanan favorit Anda</p>
            <a href="index.php" class="btn-action btn-primary">Mulai Pesan</a>
        </div>
    <?php endif; ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle Review Form
    function toggleReview(orderId) {
        const form = document.getElementById('review-' + orderId);
        form.classList.toggle('active');
        if(form.classList.contains('active')) {
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    // Set Rating
    function setRating(orderId, rating) {
        document.getElementById('rating-' + orderId).value = rating;
        const stars = document.querySelectorAll('#stars-' + orderId + ' .star');
        stars.forEach((star, index) => {
            if(index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }
    
    // Active nav link based on current page
    document.addEventListener('DOMContentLoaded', function() {
        var currentPage = window.location.pathname.split('/').pop();
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
            if(link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    });
</script>

</body>
</html>