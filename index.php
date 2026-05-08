<?php include 'config.php'; 
include 'functions.php';

// Contoh di admin.php saat update status
if(isset($_POST['update_status'])){
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    $resi_number = $_POST['resi_number'] ?? '';
    
    // Ambil data pesanan
    $order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id"));
    
    // Update status
    mysqli_query($conn, "UPDATE orders SET status = '$new_status', resi_number = '$resi_number' WHERE id = $order_id");
    
    // Buat notifikasi berdasarkan status
    switch($new_status){
        case 'Dikirim':
            notifyOrderShipped($conn, $order['customer_phone'], $order_id, $resi_number);
            if($order['payment_method'] == 'COD'){
                notifyCODPayment($conn, $order['customer_phone'], $order_id, $order['total_price'], $resi_number);
            }
            break;
        case 'Selesai':
            notifyOrderDelivered($conn, $order['customer_phone'], $order_id, $resi_number);
            break;
    }
    
    header("Location: admin.php?success=1");
    exit;
}

// Logika Tambah ke Cart (TETAP SAMA)
if(isset($_POST['add_to_cart'])){
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    
    $cart_item = ['id' => $id, 'name' => $name, 'price' => $price, 'qty' => 1];
    $found = false;
    if(isset($_SESSION['cart'])){
        foreach($_SESSION['cart'] as $key => $item){
            if($item['id'] == $id){
                $_SESSION['cart'][$key]['qty']++;
                $found = true;
                break;
            }
        }
    }
    if(!$found){
        $_SESSION['cart'][] = $cart_item;
    }
}

$products = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Texcer Hot - Menu</title>
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
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 107, 53, 0.15);
            color: var(--accent);
        }
        .nav-link i { width: 20px; text-align: center; }

        /* === MAIN CONTENT === */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        /* === TOP HEADER === */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        .search-box {
            display: flex;
            align-items: center;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 12px 20px;
            width: 400px;
            gap: 10px;
        }
        .search-box input {
            background: transparent;
            border: none;
            color: white;
            width: 100%;
            outline: none;
            font-size: 1rem;
        }
        .search-box input::placeholder { color: var(--text-secondary); }
        .header-icons { display: flex; gap: 15px; }
        .header-icons i {
            font-size: 1.3rem;
            color: var(--text-secondary);
            cursor: pointer;
            transition: 0.3s;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
        .header-icons i:hover { background: var(--bg-card); color: var(--accent); }
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent);
            color: white;
            font-size: 0.7rem;
            padding: 3px 7px;
            border-radius: 10px;
            font-weight: 600;
        }

        /* === HERO BANNER === */
        .hero-banner {
            background: linear-gradient(135deg, rgba(255,107,53,0.15), rgba(255,133,85,0.05));
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 40px;
        }
        .hero-text h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        .hero-text p { color: var(--text-secondary); margin-bottom: 25px; font-size: 1.1rem; }
        .hero-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .hero-btn:hover { background: var(--accent-hover); transform: translateY(-2px); }
        .hero-image {
            width: 250px;
            height: 250px;
            background: var(--bg-card);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: var(--accent);
            opacity: 0.8;
        }

        /* === SECTION TITLE === */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
        }
        .view-all {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* === PRODUCT GRID === */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 25px;
        }
        .product-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            transition: 0.3s;
            cursor: pointer;
        }
        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent);
            box-shadow: var(--shadow);
        }
        .product-img {
            height: 180px;
            background: #2a2a2a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--accent);
            opacity: 0.7;
        }
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-info { padding: 20px; }
        .product-name {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1.05rem;
        }
        .product-desc {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .product-price {
            color: var(--accent);
            font-weight: 700;
            font-size: 1.2rem;
        }
        .btn-add-cart {
            background: var(--accent);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-add-cart:hover { background: var(--accent-hover); transform: scale(1.1); }

        /* === MODAL DETAIL === */
        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
        }
        .modal-header {
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-sidebar);
            border-radius: 20px 20px 0 0;
        }
        .modal-title { color: white; font-weight: 700; }
        .btn-close-white { filter: invert(1); }
        .modal-body { padding: 30px; }

        /* === RESPONSIVE === */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 20px 10px;
            }
            .logo span, .nav-link span { display: none; }
            .nav-link { justify-content: center; padding: 15px; }
            .nav-link i { margin: 0; }
            .main-content { margin-left: 80px; }
            .hero-banner { flex-direction: column; text-align: center; }
            .search-box { width: 100%; }
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
            .product-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .product-grid { grid-template-columns: 1fr; }
            .hero-banner { padding: 25px; }
            .hero-text h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR NAVIGATION -->
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
            <a href="checkout.php" class="nav-link position-relative <?= $currentPage == 'checkout.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
                <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="cart-badge"><?= count($_SESSION['cart']) ?></span>
                <?php endif; ?>
            </a>
        </li>
        <!-- ✅ TAMBAHKAN MENU NOTIFIKASI DI SINI -->
        <li class="nav-item">
            <a href="notifikasi.php" class="nav-link <?= $currentPage == 'notifikasi.php' ? 'active' : '' ?>">
                <i class="fas fa-bell"></i>
                <span>Notifikasi</span>
                <?php
                // Hitung notifikasi belum dibaca
                $user_phone = $_SESSION['user_phone'] ?? '';
                if(!empty($user_phone)){
                    $unread = mysqli_fetch_assoc(mysqli_query($conn, "
                        SELECT COUNT(*) as count FROM notifications 
                        WHERE user_phone = '$user_phone' AND is_read = 0
                    "))['count'];
                    if($unread > 0):
                ?>
                    <span class="cart-badge" style="background: #ff4757; right: 15px;"><?= $unread ?></span>
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
    
    <!-- TOP HEADER -->
    <header class="top-header">
        <div class="search-box">
            <i class="fas fa-search text-secondary"></i>
            <input type="text" placeholder="Cari makanan favoritmu..." id="searchInput">
        </div>
        <div class="header-icons">
            
            <i class="fas fa-user-circle" title="Profile" onclick="window.location='admin.php'"></i>
        </div>
    </header>

    <!-- HERO BANNER -->
    <section class="hero-banner">
        <div class="hero-text">
            <h1>Find Your<br>Favorite Food 🔥</h1>
            <p>Pesan makanan pedas favoritmu sekarang! Gratis ongkir untuk pesanan pertama.</p>
            <button class="hero-btn" onclick="document.getElementById('searchInput').focus()">
                <i class="fas fa-search me-2"></i>Mulai Pesan
            </button>
        </div>
        <div class="hero-image">
            <i class="fas fa-utensils"></i>
        </div>
    </section>

    <!-- PRODUCT SECTION -->
    <section>
        <div class="section-header">
            <h2 class="section-title">Menu Kami</h2>
            <a href="#" class="view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="product-grid">
            <?php while($p = mysqli_fetch_assoc($products)): ?>
            <div class="product-card" data-bs-toggle="modal" data-bs-target="#modalDetail" 
                 data-id="<?= $p['id'] ?>"
                 data-name="<?= $p['name'] ?>" 
                 data-price="<?= $p['price'] ?>" 
                 data-desc="<?= $p['description'] ?>" 
                 data-img="<?= $p['image'] ?>">
                
                <div class="product-img">
                    <?php if(!empty($p['image'])): ?>
                        <img src="<?= $p['image'] ?>" alt="<?= $p['name'] ?>">
                    <?php else: ?>
                        <i class="fas fa-utensils"></i>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <h3 class="product-name"><?= $p['name'] ?></h3>
                    <p class="product-desc"><?= $p['description'] ?></p>
                    <div class="product-footer">
                        <span class="product-price"><?= formatRupiah($p['price']) ?></span>
                        <form method="POST" onclick="event.stopPropagation()">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="name" value="<?= $p['name'] ?>">
                            <input type="hidden" name="price" value="<?= $p['price'] ?>">
                            <button type="submit" name="add_to_cart" class="btn-add-cart" title="Tambah ke Keranjang">
                                <i class="fas fa-plus"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

</main>

<!-- MODAL DETAIL PRODUK -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="md-name"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <img src="" id="md-img" class="img-fluid rounded" style="width:100%; max-height:400px; object-fit:cover;">
                    </div>
                    <div class="col-md-6">
                        <h3 class="text-warning mb-3" id="md-price"></h3>
                        <p class="text-secondary mb-4" id="md-desc"></p>
                        
                        <div class="d-flex gap-3 mb-4">
                            <div class="text-center">
                                <i class="fas fa-motorcycle text-warning fa-2x mb-2"></i>
                                <div class="small text-secondary">Pengiriman Cepat</div>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-shield-alt text-warning fa-2x mb-2"></i>
                                <div class="small text-secondary">Garansi Uang Kembali</div>
                            </div>
                        </div>
                        
                        <form method="POST" id="modalFormPesan">
                            <input type="hidden" name="id" id="md-id">
                            <input type="hidden" name="name" id="md-form-name">
                            <input type="hidden" name="price" id="md-form-price">
                            <button type="submit" name="add_to_cart" class="btn btn-warning w-100 py-3 fw-bold">
                                <i class="fas fa-shopping-cart me-2"></i> + Pesan Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SEARCH (Optional) -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <input type="text" class="form-control form-control-lg" placeholder="Cari makanan...">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Modal Detail Produk
    var modal = document.getElementById('modalDetail');
    modal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('md-name').innerText = button.getAttribute('data-name');
        document.getElementById('md-price').innerText = "Rp " + parseInt(button.getAttribute('data-price')).toLocaleString('id-ID');
        document.getElementById('md-desc').innerText = button.getAttribute('data-desc');
        document.getElementById('md-img').src = button.getAttribute('data-img');
        document.getElementById('md-id').value = button.getAttribute('data-id');
        document.getElementById('md-form-name').value = button.getAttribute('data-name');
        document.getElementById('md-form-price').value = button.getAttribute('data-price');
    });

    // Search Functionality
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        var value = e.target.value.toLowerCase();
        var cards = document.querySelectorAll('.product-card');
        cards.forEach(function(card) {
            var title = card.querySelector('.product-name').innerText.toLowerCase();
            var desc = card.querySelector('.product-desc').innerText.toLowerCase();
            if(title.indexOf(value) > -1 || desc.indexOf(value) > -1) {
                card.parentElement.style.display = '';
            } else {
                card.parentElement.style.display = 'none';
            }
        });
    });

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