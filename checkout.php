<?php 
include 'config.php'; 
include 'functions.php';

// ✅ INISIALISASI VARIABEL TOTAL (WAJIB - Agar tidak error saat cart kosong)
$grand_total = 0;
$total_items = 0;

// 1. Logika Update Quantity & Hapus
if(isset($_GET['action'])){
    $index = $_GET['index'];
    $action = $_GET['action'];
    
    if(isset($_SESSION['cart'][$index])){
        if($action == 'plus'){
            $_SESSION['cart'][$index]['qty']++;
        } elseif($action == 'minus'){
            if($_SESSION['cart'][$index]['qty'] > 1){
                $_SESSION['cart'][$index]['qty']--;
            } else {
                unset($_SESSION['cart'][$index]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            }
        } elseif($action == 'remove'){
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }
    header("Location: checkout.php");
    exit;
}

// 2. Upload QRIS Image
if(isset($_POST['upload_qris'])){
    if(isset($_FILES['qris_image']) && $_FILES['qris_image']['error'] == 0){
        $target_dir = "assets/images/";
        $target_file = $target_dir . "qris_" . time() . ".jpg";
        if(move_uploaded_file($_FILES['qris_image']['tmp_name'], $target_file)){
            $_SESSION['qris_image'] = $target_file;
            echo "<script>alert('QRIS berhasil diupload!'); window.location='checkout.php';</script>";
        }
    }
}

// 3. Logika Checkout
if(isset($_POST['process_order'])){
    $name = $_POST['cust_name'];
    $phone = $_POST['cust_phone'];
    $_SESSION['user_phone'] = $phone;
    $address = $_POST['cust_address'];
    $city = $_POST['cust_city'];
    $province = $_POST['cust_province'];
    $payment_method = $_POST['payment_method'] ?? 'COD';
    $notes = $_POST['notes'];
    $total = 0;
    
    if(!empty($_SESSION['cart'])){
        foreach($_SESSION['cart'] as $item){
            $total += ($item['price'] * $item['qty']);
        }

        $sql_order = "INSERT INTO orders (customer_name, customer_phone, customer_address, customer_city, customer_province, total_price, status, payment_method) 
                      VALUES ('$name', '$phone', '$address', '$city', '$province', '$total', 'Pending', '$payment_method')";
        
        if(mysqli_query($conn, $sql_order)){
            $order_id = mysqli_insert_id($conn);
            
            // ✅ BUAT NOMOR ORDER
            $order_number = 'ORD' . str_pad($order_id, 10, '0', STR_PAD_LEFT);
            mysqli_query($conn, "UPDATE orders SET order_number = '$order_number' WHERE id = $order_id");
            
            // ✅ BUAT NOTIFIKASI
            notifyOrderCreated($conn, $phone, $order_id, $order_number);
            // Debug (hapus nanti kalau sudah jalan)
if(!$notif_result){
    error_log("Notifikasi gagal: " . mysqli_error($conn));
}

            foreach($_SESSION['cart'] as $item){
                $sub = $item['price'] * $item['qty'];
                $sql_item = "INSERT INTO order_items (order_id, product_name, price, qty, subtotal) 
                             VALUES ('$order_id', '{$item['name']}', '{$item['price']}', '{$item['qty']}', '$sub')";
                mysqli_query($conn, $sql_item);
            }

            unset($_SESSION['cart']);
            header("Location: checkout.php?success=1");
            exit;
        } else {
            echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// Notifikasi sukses
if(isset($_GET['success']) && $_GET['success'] == '1'){
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    Swal.fire({
        icon: 'success',
        title: '🎉 Pesanan Berhasil!',
        text: 'Selamat! Pesanan Anda telah berhasil dibuat.',
        confirmButtonColor: '#ff6600',
        confirmButtonText: 'Lihat Pesanan'
    }).then((result) => {
        if(result.isConfirmed){
            window.location = 'riwayat.php';
        }
    });
    </script>";
}

// Ambil alamat user
$addr_phone = $_SESSION['user_phone'] ?? '';
$addresses = [];
if(!empty($addr_phone)){
    $res = mysqli_query($conn, "SELECT * FROM addresses WHERE customer_phone = '$addr_phone' ORDER BY is_default DESC, id DESC");
    while($row = mysqli_fetch_assoc($res)) $addresses[] = $row;
}
$default_addr = !empty($addresses) ? $addresses[0] : null;

$currentPage = basename($_SERVER['PHP_SELF']);

// ✅ HITUNG TOTAL CART (Hanya jika cart tidak kosong)
if(!empty($_SESSION['cart'])){
    foreach($_SESSION['cart'] as $item){
        $grand_total += ($item['price'] * $item['qty']);
        $total_items += $item['qty'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Texcer Hot</title>
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
            right: 8px;
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

        /* === CART HEADER === */
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        .cart-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent);
        }
        .cart-count {
            background: var(--bg-card);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        /* === CART ITEMS === */
        .cart-shop {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
        }
        .shop-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .shop-badge {
            background: var(--accent);
            color: white;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .shop-name {
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* === PRODUCT IN CART === */
        .cart-product {
            display: flex;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
            align-items: center;
        }
        .cart-product:last-child {
            border-bottom: none;
        }
        .product-checkbox {
            width: 22px;
            height: 22px;
            cursor: pointer;
        }
        .product-image {
            width: 100px;
            height: 100px;
            background: #2a2a2a;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: 0.3s;
        }
        .product-image:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-image i {
            font-size: 3rem;
            color: var(--accent);
            opacity: 0.5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .product-details {
            flex: 1;
        }
        .product-name {
            font-weight: 600;
            font-size: 1.05rem;
            margin-bottom: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        .product-name:hover {
            color: var(--accent);
        }
        .product-variant {
            background: #2a2a2a;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }
        .product-price {
            color: var(--accent);
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 10px;
        }
        .product-original-price {
            color: var(--text-secondary);
            text-decoration: line-through;
            font-size: 0.9rem;
            margin-left: 8px;
        }
        .product-discount {
            background: #ffe0e0;
            color: #ff4444;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .qty-control {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
        }
        .qty-btn {
            width: 32px;
            height: 32px;
            background: #333;
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            font-weight: 600;
        }
        .qty-btn:hover {
            background: var(--accent);
        }
        .qty-value {
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }
        .product-actions {
            text-align: right;
            min-width: 120px;
        }
        .product-subtotal {
            color: var(--accent);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        .btn-remove {
            color: #ff4757;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-left: auto;
            transition: 0.3s;
        }
        .btn-remove:hover {
            opacity: 0.8;
        }

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
            .cart-product { flex-direction: column; align-items: flex-start; }
            .product-actions { 
                width: 100%; 
                display: flex; 
                justify-content: space-between; 
                align-items: center;
                margin-top: 15px;
            }
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
    
    <?php if(!empty($_SESSION['cart'])): ?>
        
        <!-- Cart Header -->
        <div class="cart-header">
            <h1 class="cart-title">Keranjang Belanja</h1>
            <span class="cart-count"><?= $total_items ?> item</span>
        </div>
        
        <!-- Cart Items -->
        <div class="cart-shop">
            <div class="shop-info">
                <span class="shop-badge">Mall</span>
                <span class="shop-name">Texcer Hot</span>
            </div>
            
            <?php foreach($_SESSION['cart'] as $index => $item): 
                $subtotal = $item['price'] * $item['qty'];
            ?>
            <div class="cart-product">
                <input type="checkbox" class="product-checkbox" checked>
                
                <div class="product-image" onclick="showProductDetail(<?= $item['id'] ?>)">
                    <?php if(!empty($item['image'])): ?>
                        <img src="<?= $item['image'] ?>" alt="<?= $item['name'] ?>">
                    <?php else: ?>
                        <i class="fas fa-utensils"></i>
                    <?php endif; ?>
                </div>
                
                <div class="product-details">
                    <h3 class="product-name" onclick="showProductDetail(<?= $item['id'] ?>)"><?= $item['name'] ?></h3>
                    <span class="product-variant">Variant: Default</span>
                    <div class="product-price">
                        Rp <?= number_format($item['price'], 0, ',', '.') ?>
                        <span class="product-original-price">Rp <?= number_format($item['price'] * 1.2, 0, ',', '.') ?></span>
                        <span class="product-discount">-20%</span>
                    </div>
                    <div class="qty-control">
                        <button class="qty-btn" onclick="updateQty(<?= $index ?>, -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="qty-value"><?= $item['qty'] ?></span>
                        <button class="qty-btn" onclick="updateQty(<?= $index ?>, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <div class="product-actions">
                    <div class="product-subtotal">Rp <?= number_format($subtotal, 0, ',', '.') ?></div>
                    <button class="btn-remove" onclick="removeItem(<?= $index ?>)">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
    <?php else: ?>
        <div style="text-align: center; padding: 100px 20px;">
            <i class="fas fa-shopping-cart" style="font-size: 5rem; color: var(--accent); opacity: 0.3; margin-bottom: 20px;"></i>
            <h2 style="margin-bottom: 10px;">Keranjang Kosong</h2>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">Yuk, pesan makanan favoritmu sekarang!</p>
            <a href="index.php" class="btn btn-warning" style="background: var(--accent); border: none; padding: 12px 40px; font-weight: 600; border-radius: 12px;">
                Mulai Pesan
            </a>
        </div>
    <?php endif; ?>

    <!-- Bottom Fixed Bar -->
    <div style="position: fixed; bottom: 0; left: 250px; right: 0; background: var(--bg-card); border-top: 1px solid var(--border-color); padding: 15px 30px; display: flex; align-items: center; justify-content: space-between; z-index: 99;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-secondary);">
                <input type="checkbox" id="selectAll" style="width: 20px; height: 20px; cursor: pointer;" checked onchange="toggleSelectAll()">
                <span>Semua</span>
            </label>
        </div>
        
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="text-align: right;">
                <div style="color: var(--text-secondary); font-size: 0.9rem;">Total</div>
                <!-- ✅ NULL-SAFE: Gunakan ?? 0 agar tidak error -->
                <div style="color: var(--accent); font-weight: 700; font-size: 1.3rem;" id="bottomTotal">
                    Rp <?= number_format($grand_total ?? 0, 0, ',', '.') ?>
                </div>
            </div>
            <button class="checkout-btn" onclick="showCheckoutModal()" style="padding: 12px 40px; margin: 0;">
                <i class="fas fa-credit-card me-2"></i>Checkout
            </button>
        </div>
    </div>

    <!-- Spacer untuk bottom bar -->
    <div style="height: 100px;"></div>

</main>

<!-- Modal Detail Produk -->
<div class="modal fade" id="productDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProductName">Detail Produk</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <img src="" id="modalProductImage" class="img-fluid rounded" style="width:100%; max-height:400px; object-fit:cover;">
                    </div>
                    <div class="col-md-6">
                        <h3 class="text-warning mb-3" id="modalProductPrice"></h3>
                        <p class="text-secondary mb-4" id="modalProductDesc"></p>
                        
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
                        
                        <button class="btn btn-warning w-100 py-3 fw-bold" onclick="window.location='index.php'">
                            <i class="fas fa-shopping-cart me-2"></i> Tambah ke Keranjang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Checkout</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalScrollBody">
                <form method="POST" enctype="multipart/form-data">
                    
                    <!-- Alamat -->
                    <div class="section-title mt-0 mb-3"><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</div>
                    <a href="alamat.php" class="btn btn-outline-warning w-100 mb-3">
                        <i class="fas fa-plus-circle me-2"></i>Kelola Alamat
                    </a>
                    
                    <?php if(!empty($addresses)): ?>
                        <?php foreach($addresses as $i => $addr): ?>
                        <div class="address-card mb-2 <?= $addr['is_default'] ? 'selected' : '' ?>" onclick="selectAddress(<?= $i ?>)">
                            <a href="alamat.php?edit=<?= $addr['id'] ?>" class="text-warning float-end">Edit</a>
                            <div class="text-white fw-bold"><?= htmlspecialchars($addr['customer_name']) ?></div>
                            <div class="text-secondary small">(+62)<?= substr($addr['customer_phone'], -10) ?></div>
                            <div class="text-light small mt-1"><?= nl2br(htmlspecialchars($addr['full_address'])) ?></div>
                            <div class="text-secondary small"><?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['province']) ?></div>
                            <?php if($addr['is_default']): ?><span class="badge bg-warning text-dark mt-2">Default</span><?php endif; ?>
                            <input type="hidden" name="cust_name_<?= $i ?>" value="<?= htmlspecialchars($addr['customer_name']) ?>">
                            <input type="hidden" name="cust_phone_<?= $i ?>" value="<?= htmlspecialchars($addr['customer_phone']) ?>">
                            <input type="hidden" name="cust_address_<?= $i ?>" value="<?= htmlspecialchars($addr['full_address']) ?>">
                            <input type="hidden" name="cust_city_<?= $i ?>" value="<?= htmlspecialchars($addr['city']) ?>">
                            <input type="hidden" name="cust_province_<?= $i ?>" value="<?= htmlspecialchars($addr['province']) ?>">
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-secondary py-4">
                            <i class="fas fa-map-marker-alt fa-2x mb-2"></i>
                            <p>Belum ada alamat. <a href="alamat.php" class="text-warning">Tambahkan</a></p>
                        </div>
                    <?php endif; ?>
                    
                    <input type="hidden" name="cust_name" id="final_name">
                    <input type="hidden" name="cust_phone" id="final_phone">
                    <input type="hidden" name="cust_address" id="final_address">
                    <input type="hidden" name="cust_city" id="final_city">
                    <input type="hidden" name="cust_province" id="final_province">
                    
                    <!-- Pembayaran -->
                    <div class="section-title mb-3 mt-4"><i class="fas fa-wallet"></i> Metode Pembayaran</div>
                    <div class="payment-card selected mb-2" onclick="selectPayment('cod', this)">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #00b894, #55efc4); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-white fw-bold">COD (Bayar di Tempat)</div>
                                <div class="text-secondary small">Bayar saat pesanan diterima</div>
                            </div>
                            <input type="radio" name="payment_method" value="COD" checked>
                        </div>
                    </div>
                    <div class="payment-card mb-2" onclick="selectPayment('qris', this)">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #6c5ce7, #a855f7); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-white fw-bold">QRIS</div>
                                <div class="text-secondary small">Scan QR Code untuk bayar</div>
                            </div>
                            <input type="radio" name="payment_method" value="QRIS">
                        </div>
                        <?php if(isset($_SESSION['qris_image'])): ?>
                        <div class="text-center mt-3">
                            <img src="<?= $_SESSION['qris_image'] ?>" alt="QRIS" style="max-width: 200px; border: 2px solid #ff6600; border-radius: 8px;">
                            <p class="text-secondary small mt-2">Scan kode di atas</p>
                        </div>
                        <?php else: ?>
                        <div class="text-center mt-3 p-3 border border-dashed border-secondary rounded" onclick="document.getElementById('qrisInput').click()" style="cursor: pointer;">
                            <i class="fas fa-cloud-upload-alt fa-2x text-secondary d-block mb-2"></i>
                            <span class="text-secondary small">Klik upload QRIS</span>
                            <input type="file" id="qrisInput" name="qris_image" accept="image/*" style="display: none;" onchange="this.form.submit()">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Catatan -->
                    <div class="section-title mb-3 mt-4"><i class="fas fa-sticky-note"></i> Catatan</div>
                    <textarea name="notes" class="form-control bg-dark text-white border-secondary mb-4" rows="3" placeholder="Catatan (opsional)"></textarea>
                    
                    <!-- Total -->
                    <div class="section-title mb-3"><i class="fas fa-receipt"></i> Total Pembayaran</div>
                    <div style="background: #222; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: #888;">Total Item</span>
                            <!-- ✅ NULL-SAFE di modal juga -->
                            <span>Rp <?= number_format($grand_total ?? 0, 0, ',', '.') ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span style="color: #888;">Ongkir</span>
                            <span style="color: #00e5ff;">Gratis</span>
                        </div>
                        <hr style="border-color: #333;">
                        <div class="d-flex justify-content-between">
                            <strong style="color: white;">Total Bayar</strong>
                            <strong style="color: #ff6600; font-size: 1.3rem;">Rp <?= number_format($grand_total ?? 0, 0, ',', '.') ?></strong>
                        </div>
                    </div>
                    
                    <!-- Tombol Submit -->
                    <div style="position: sticky; bottom: 0; background: #1a1a1a; padding: 15px 0; border-top: 2px solid #333; margin-top: 20px;">
                        <button type="submit" name="process_order" class="btn btn-warning w-100 py-3 fw-bold" style="background: linear-gradient(135deg, #ff6600, #ff8533); border: none; border-radius: 10px;">
                            <i class="fas fa-check me-2"></i>Buat Pesanan
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Update Quantity
function updateQty(index, change) {
    window.location.href = `?action=${change > 0 ? 'plus' : 'minus'}&index=${index}`;
}

// Remove Item
function removeItem(index) {
    if(confirm('Hapus item dari keranjang?')) {
        window.location.href = `?action=remove&index=${index}`;
    }
}

// Show Product Detail
function showProductDetail(productId) {
    window.location.href = `index.php?product=${productId}`;
}

// Show Checkout Modal
function showCheckoutModal() {
    const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
    modal.show();
}

// Select Address
function selectAddress(index) {
    document.querySelectorAll('.address-card').forEach(el => {
        el.classList.remove('selected');
        el.style.borderColor = '#333';
    });
    const cards = document.querySelectorAll('.address-card');
    if(cards[index]) {
        cards[index].classList.add('selected');
        cards[index].style.borderColor = '#ff6600';
        
        document.getElementById('final_name').value = document.querySelector(`input[name="cust_name_${index}"]`)?.value || '';
        document.getElementById('final_phone').value = document.querySelector(`input[name="cust_phone_${index}"]`)?.value || '';
        document.getElementById('final_address').value = document.querySelector(`input[name="cust_address_${index}"]`)?.value || '';
        document.getElementById('final_city').value = document.querySelector(`input[name="cust_city_${index}"]`)?.value || '';
        document.getElementById('final_province').value = document.querySelector(`input[name="cust_province_${index}"]`)?.value || '';
    }
}

// Select Payment
function selectPayment(method, element) {
    document.querySelectorAll('.payment-card').forEach(el => {
        el.classList.remove('selected');
        el.style.borderColor = '#333';
    });
    element.classList.add('selected');
    element.style.borderColor = '#ff6600';
    element.querySelector('input[type="radio"]').checked = true;
}

// Auto select default address on load
document.addEventListener('DOMContentLoaded', () => {
    const defaultAddr = document.querySelector('.address-card.selected');
    if(defaultAddr) {
        const idx = Array.from(document.querySelectorAll('.address-card')).indexOf(defaultAddr);
        selectAddress(idx);
    }
});

// Toggle Select All
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updateTotal();
}

// Update Total berdasarkan checkbox yang dicentang
function updateTotal() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    let total = 0;
    
    checkboxes.forEach(cb => {
        const productRow = cb.closest('.cart-product');
        const priceText = productRow.querySelector('.product-subtotal').innerText;
        const price = parseFloat(priceText.replace(/[^0-9]/g, ''));
        total += price;
    });
    
    document.getElementById('bottomTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
}

// Event listener untuk checkbox individual
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateTotal);
    });
});
</script>

</body>
</html>