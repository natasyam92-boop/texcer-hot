<?php include 'config.php'; 

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #0a0a0a; color: white; font-family: 'Segoe UI', sans-serif; padding-bottom: 80px; }
        
        /* Header */
        .page-header {
            background: #111;
            padding: 20px;
            border-bottom: 1px solid #222;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-header h2 { color: #ff6600; font-size: 1.3rem; }
        
        /* Tabs */
        .tabs-container {
            background: #111;
            padding: 0 15px;
            overflow-x: auto;
            white-space: nowrap;
            border-bottom: 1px solid #222;
            scrollbar-width: none;
        }
        .tabs-container::-webkit-scrollbar { display: none; }
        .tab-btn {
            display: inline-block;
            padding: 15px 10px;
            color: #666;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            transition: 0.3s;
        }
        .tab-btn.active {
            color: #ff6600;
            border-bottom-color: #ff6600;
        }
        
        /* Order Card */
        .order-card {
            background: #1a1a1a;
            margin: 15px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #222;
        }
        .order-header {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #222;
        }
        .order-store {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .store-name { font-weight: 600; color: white; }
        .order-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-pending { background: #4a3800; color: #ffc107; }
        .status-diproses { background: #004a4a; color: #00e5ff; }
        .status-dikirim { background: #003366; color: #4dabf7; }
        .status-selesai { background: #004d00; color: #51cf66; }
        .status-dibatalkan { background: #4d0000; color: #ff6b6b; }
        
        /* Order Info */
        .order-info {
            padding: 15px;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .delivery-icon {
            width: 50px;
            height: 50px;
            background: #ff6600;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .delivery-text h6 { color: white; margin-bottom: 5px; }
        .delivery-text p { color: #888; font-size: 0.85rem; }
        
        /* Order Items */
        .order-items {
            padding: 0 15px 15px;
        }
        .item-row {
            display: flex;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #222;
        }
        .item-img {
            width: 80px;
            height: 80px;
            background: #222;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 0.7rem;
        }
        .item-details { flex: 1; }
        .item-name { color: white; font-size: 0.9rem; margin-bottom: 5px; }
        .item-variant { color: #888; font-size: 0.8rem; margin-bottom: 5px; }
        .item-price { color: #ff6600; font-weight: 600; }
        .item-qty { color: #888; font-size: 0.85rem; }
        
        /* Total */
        .order-total {
            padding: 15px;
            text-align: right;
            border-top: 1px solid #222;
        }
        .total-label { color: #888; font-size: 0.9rem; }
        .total-price { color: #ff6600; font-size: 1.2rem; font-weight: 700; }
        
        /* Action Buttons */
        .order-actions {
            padding: 15px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn-action {
            padding: 10px 25px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-secondary { background: #222; color: #ff6600; border: 1px solid #ff6600; }
        .btn-primary { background: #ff6600; color: white; }
        .btn-primary:hover { background: #e65c00; }
        
        /* Stars Rating */
        .stars { display: flex; gap: 5px; margin: 10px 0; }
        .star { color: #666; font-size: 1.5rem; cursor: pointer; transition: 0.2s; }
        .star.active { color: #ffc107; }
        .star:hover { transform: scale(1.2); }
        
        /* Review Form */
        .review-form {
            background: #222;
            padding: 20px;
            margin: 15px;
            border-radius: 12px;
            display: none;
        }
        .review-form.active { display: block; }
        .review-form textarea {
            width: 100%;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 12px;
            color: white;
            resize: none;
            margin-top: 10px;
        }
        .review-form textarea:focus {
            outline: none;
            border-color: #ff6600;
        }
        
        /* Bottom Nav */
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
<div class="page-header">
    <a href="index.php" style="color: white;"><i class="fas fa-arrow-left"></i></a>
    <h2><i class="fas fa-history"></i> Riwayat Pesanan</h2>
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
                    <span style="color: #ff6600;">🔥</span>
                    <span class="store-name">Texcer Hot</span>
                    <i class="fas fa-chevron-right" style="color: #666; font-size: 0.8rem;"></i>
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
                        <i class="fas fa-image"></i>
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
                    <button class="btn-action btn-primary">
                        <i class="fas fa-redo"></i> Beli Lagi
                    </button>
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
                        
                        <button type="submit" name="submit_review" class="btn-action btn-primary mt-2 w-100">
                            Kirim Ulasan
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <!-- Display Existing Review -->
                <div style="padding: 15px; background: #222; margin: 15px; border-radius: 12px;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span style="color: #ffc107;">
                            <?php for($s=1; $s<=$review['rating']; $s++) echo "★"; ?>
                            <?php for($s=$review['rating']+1; $s<=5; $s++) echo "☆"; ?>
                        </span>
                        <span style="color: #888; font-size: 0.85rem;"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                    </div>
                    <p style="color: white; font-size: 0.9rem;"><?= $review['comment'] ?></p>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Cancel Button (Only for pending) -->
            <?php if($o['status'] == 'Pending'): ?>
            <div class="order-actions">
                <a href="checkout.php" class="btn-action btn-secondary">Bayar Sekarang</a>
                <button class="btn-action btn-secondary" style="border-color: #ff6b6b; color: #ff6b6b;" onclick="if(confirm('Batalkan pesanan?')) window.location='?cancel=<?= $o['id'] ?>'">
                    Batalkan Pesanan
                </button>
            </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="text-center mt-5 text-secondary">
        <i class="fas fa-receipt fa-4x mb-3"></i>
        <h5>Belum ada pesanan</h5>
        <p>Silakan pesan makanan favorit Anda</p>
        <a href="index.php" class="btn-action btn-primary mt-3">Mulai Pesan</a>
    </div>
<?php endif; ?>

<!-- Bottom Navigation -->
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<div class="bottom-nav d-flex justify-content-around">
    <!-- Home -->
    <a href="index.php" class="nav-item <?= $currentPage == 'index.php' ? 'active' : '' ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    
    <!-- Order = Riwayat Pesanan (Icon History) -->
    <a href="riwayat.php" class="nav-item <?= $currentPage == 'riwayat.php' ? 'active' : '' ?>">
        <i class="fas fa-history"></i>
        <span>Order</span>
    </a>
    
    <!-- Cart -->
    <a href="checkout.php" class="nav-item <?= $currentPage == 'checkout.php' ? 'active' : '' ?>">
        <i class="fas fa-shopping-cart"></i>
        <span>Cart</span>
    </a>
    
    <!-- Profile = Admin -->
    <a href="admin.php" class="nav-item <?= $currentPage == 'admin.php' ? 'active' : '' ?>">
        <i class="fas fa-user-shield"></i>
        <span>Profile</span>
    </a>
</div>
<script>
    // Toggle Review Form
    function toggleReview(orderId) {
        const form = document.getElementById('review-' + orderId);
        form.classList.toggle('active');
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
</script>
</body>
</html>