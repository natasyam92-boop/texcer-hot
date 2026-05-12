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

// Logika Tambah ke Cart (Dengan Variant)
if(isset($_POST['add_to_cart'])){
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $variant = $_POST['variant'] ?? '';
    $cart_item = ['id' => $id, 'name' => $name, 'price' => $price, 'qty' => 1, 'variant' => $variant];
    $found = false;
    if(isset($_SESSION['cart'])){
        foreach($_SESSION['cart'] as $key => $item){
            if($item['id'] == $id && $item['variant'] == $variant){
                $_SESSION['cart'][$key]['qty']++;
                $found = true;
                break;
            }
        }
    }
    if(!$found){
        $_SESSION['cart'][] = $cart_item;
    }
    header("Location: index.php?added=1");
    exit;
}

// Data Produk
$products = [
    // Kategori Mie
    ['id' => 1, 'name' => 'Mie Yamin', 'price' => 9000, 'category' => 'mie', 'image' => 'assets/images/mie/yamin.png', 'description' => 'Mie yamin kering dengan bumbu khas', 'has_variant' => false],
    ['id' => 2, 'name' => 'Mie Yamin Kuah', 'price' => 9000, 'category' => 'mie', 'image' => 'assets/images/mie/yamin_kuah.png', 'description' => 'Mie yamin dengan kuah kaldu gurih', 'has_variant' => false],
    
    // Kategori Mercon
    ['id' => 3, 'name' => 'Ceker Mercon Tanpa Tulang', 'price' => 15000, 'category' => 'mercon', 'image' => 'assets/images/mercon/ceker_tnpa_tulang.jpg', 'description' => 'Ceker mercon tanpa tulang super pedas', 'has_variant' => true, 'variants' => [
        ['name' => 'Medium (5 pcs)', 'price' => 15000],
        ['name' => 'Large (10 pcs)', 'price' => 25000],
        ['name' => 'Paket Nasi & 4 pcs', 'price' => 15000]
    ]],
    ['id' => 4, 'name' => 'Kuah Mercon', 'price' => 12000, 'category' => 'mercon', 'image' => 'assets/images/mercon/kuah_mercon.jpg', 'description' => 'Kuah mercon pedas nendang', 'has_variant' => false],
    
    // Kategori Dimsum
    ['id' => 5, 'name' => 'Dimsum Udang Keju', 'price' => 12000, 'category' => 'dimsum', 'image' => 'assets/images/dimsum/udang_keju.jpg', 'description' => 'Dimsum udang dengan keju leleh (3 pcs)', 'has_variant' => false],
    ['id' => 6, 'name' => 'Wonton Goreng/Rebus', 'price' => 10000, 'category' => 'dimsum', 'image' => 'assets/images/dimsum/wonton.jpg', 'description' => 'Wonton goreng atau rebus (3 pcs)', 'has_variant' => true, 'variants' => [
        ['name' => 'Goreng', 'price' => 10000],
        ['name' => 'Rebus', 'price' => 10000]
    ]],
    ['id' => 7, 'name' => 'Pangsit Isi Ayam', 'price' => 10000, 'category' => 'dimsum', 'image' => 'assets/images/dimsum/pangsit_ayam.jpg', 'description' => 'Pangsit isi ayam (5 pcs)', 'has_variant' => false],
    
    // Kategori Minuman
    ['id' => 8, 'name' => 'Es Permen Karet', 'price' => 5000, 'category' => 'minuman', 'image' => 'assets/images/minuman/es_permen_karet.jpg', 'description' => 'Minuman es rasa permen karet yang unik', 'has_variant' => false],
    ['id' => 9, 'name' => 'Susu', 'price' => 5000, 'category' => 'minuman', 'image' => 'assets/images/minuman/susu.jpg', 'description' => 'Susu segar', 'has_variant' => true, 'variants' => [
        ['name' => 'Es', 'price' => 5000],
        ['name' => 'Hangat', 'price' => 5000]
    ]],
    ['id' => 10, 'name' => 'Milo', 'price' => 8000, 'category' => 'minuman', 'image' => 'assets/images/minuman/milo.jpg', 'description' => 'Milo coklat malt', 'has_variant' => true, 'variants' => [
        ['name' => 'Es', 'price' => 8000],
        ['name' => 'Hangat', 'price' => 8000]
    ]],
    ['id' => 11, 'name' => 'Teh', 'price' => 3000, 'category' => 'minuman', 'image' => 'assets/images/minuman/teh.jpg', 'description' => 'Teh manis/segar', 'has_variant' => true, 'variants' => [
        ['name' => 'Es', 'price' => 3000],
        ['name' => 'Hangat', 'price' => 3000]
    ]],
    ['id' => 12, 'name' => 'Coklat', 'price' => 8000, 'category' => 'minuman', 'image' => 'assets/images/minuman/coklat.jpg', 'description' => 'Coklat panas/dingin', 'has_variant' => true, 'variants' => [
        ['name' => 'Es', 'price' => 8000],
        ['name' => 'Hangat', 'price' => 8000]
    ]]
];

$currentPage = basename($_SERVER['PHP_SELF']);

// Array untuk hero images (bisa ditambah)
$heroImages = [
    'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=1200',
    'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=1200',
    'https://images.unsplash.com/photo-1555126634-323283cb0025?w=1200',
    'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1200'
];

// Array untuk video testimonials
$videoTestimonials = [
    [
        'video' => 'assets/videos/video1.mp4',
        'thumbnail' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400',
        'user' => 'Andi Wijaya',
        'initial' => 'A',
        'product' => 'Mie Pedas Level 8',
        'rating' => 5,
        'duration' => '0:45'
    ],
    [
        'video' => 'assets/videos/video2.mp4',
        'thumbnail' => 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=400',
        'user' => 'Siti Nurhaliza',
        'initial' => 'S',
        'product' => 'Mercon Ayam',
        'rating' => 5,
        'duration' => '1:20'
    ],
    [
        'video' => 'assets/videos/video3.mp4',
        'thumbnail' => 'https://images.unsplash.com/photo-1555126634-323283cb0025?w=400',
        'user' => 'Budi Santoso',
        'initial' => 'B',
        'product' => 'Dimsum Mercon',
        'rating' => 4,
        'duration' => '0:58'
    ],
    [
        'video' => 'assets/videos/video4.mp4',
        'thumbnail' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400',
        'user' => 'Dewi Lestari',
        'initial' => 'D',
        'product' => 'Mie Goreng Pedas',
        'rating' => 5,
        'duration' => '1:05'
    ],
    [
        'video' => 'assets/videos/video5.mp4',
        'thumbnail' => 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=400',
        'user' => 'Rudi Hartono',
        'initial' => 'R',
        'product' => 'Es Teh Manis',
        'rating' => 5,
        'duration' => '0:35'
    ],
    [
        'video' => 'assets/videos/video6.mp4',
        'thumbnail' => 'https://images.unsplash.com/photo-1555126634-323283cb0025?w=400',
        'user' => 'Maya Angelina',
        'initial' => 'M',
        'product' => 'Nasi Goreng Mercon',
        'rating' => 5,
        'duration' => '1:15'
    ]
];

// Hitung total notifikasi (contoh: pesanan baru)
$totalNotifications = 0;
if(isset($_SESSION['user_phone'])){
    $phone = $_SESSION['user_phone'];
    $notifQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE customer_phone = '$phone' AND status IN ('Menunggu Konfirmasi', 'Dikirim')");
    $totalNotifications = mysqli_fetch_assoc($notifQuery)['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Texcer Hot - Pesan Makanan Pedas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
:root {
    --primary: #8B6F4E;
    --primary-dark: #6B5637;
    --secondary: #D4A574;
    --accent: #E8B4A2;
    --bg-cream: #FDF8F3;
    --bg-white: #FFFFFF;
    --text-dark: #3D2914;
    --text-gray: #8B7355;
    --border: #E8DDD4;
    --pink-soft: #FAD4C0;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    background: var(--bg-cream);
    color: var(--text-dark);
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

/* Top Navigation */
.top-nav {
    background: var(--bg-white);
    padding: 20px 40px;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.nav-container {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.logo {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
    letter-spacing: 1px;
}

.nav-menu {
    display: flex;
    gap: 40px;
    list-style: none;
}

.nav-menu a {
    color: var(--text-gray);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: color 0.3s;
}

.nav-menu a:hover, .nav-menu a.active {
    color: var(--primary);
}

.nav-icons {
    display: flex;
    gap: 20px;
    align-items: center;
}

.nav-icons a {
    color: var(--text-dark);
    font-size: 1.2rem;
    position: relative;
    transition: color 0.3s;
}

.nav-icons a:hover {
    color: var(--primary);
}

.nav-icon-btn {
    background: none;
    border: none;
    color: var(--text-dark);
    font-size: 1.2rem;
    cursor: pointer;
    position: relative;
    transition: color 0.3s;
}

.nav-icon-btn:hover {
    color: var(--primary);
}

.cart-count, .notif-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background: var(--accent);
    color: white;
    font-size: 0.7rem;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.notif-count {
    background: #dc3545;
}

/* ✅ HERO SECTION FULL SCREEN DENGAN AUTO SLIDE */
.hero-section {
    position: relative;
    height: 100vh;
    min-height: 600px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-slider {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.hero-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 1.5s ease-in-out;
}

.hero-slide.active {
    opacity: 1;
}

.hero-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-slide::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(61,41,20,0.7) 0%, rgba(250,212,192,0.5) 100%);
}

.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 900px;
    padding: 0 40px;
}

.hero-title {
    font-size: 5rem;
    font-weight: 900;
    color: white;
    margin-bottom: 20px;
    letter-spacing: 3px;
    line-height: 1.1;
    text-shadow: 3px 3px 10px rgba(0,0,0,0.5);
    animation: fadeInUp 1s ease-out;
}

.hero-subtitle {
    font-size: 1.8rem;
    color: white;
    margin-bottom: 40px;
    font-style: italic;
    text-shadow: 2px 2px 5px rgba(0,0,0,0.3);
    animation: fadeInUp 1s ease-out 0.3s both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-btn {
    background: var(--primary);
    color: white;
    border: none;
    padding: 20px 60px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 8px 25px rgba(139, 111, 78, 0.4);
    animation: fadeInUp 1s ease-out 0.6s both;
}

.hero-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(139, 111, 78, 0.6);
}

/* Hero Dots Navigation */
.hero-dots {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 12px;
    z-index: 10;
}

.hero-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: all 0.3s;
    border: 2px solid white;
}

.hero-dot.active {
    background: white;
    width: 40px;
    border-radius: 8px;
}

.hero-dot:hover {
    background: white;
}

/* Menu Section */
.menu-section {
    max-width: 1400px;
    margin: 80px auto;
    padding: 0 40px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-dark);
}

.section-subtitle {
    font-size: 1.2rem;
    color: var(--text-gray);
    font-style: italic;
}

.my-basket-btn {
    background: var(--secondary);
    color: white;
    padding: 12px 30px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.my-basket-btn:hover {
    background: var(--primary);
    transform: translateY(-2px);
}

/* Category Tabs */
.category-tabs {
    display: flex;
    gap: 15px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.tab-btn {
    background: var(--bg-white);
    border: 2px solid var(--border);
    padding: 12px 28px;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
    color: var(--text-gray);
}

.tab-btn:hover, .tab-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}

.product-card {
    background: var(--bg-white);
    border: 3px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s;
    cursor: pointer;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    border-color: var(--secondary);
}

.product-image {
    height: 280px;
    background: var(--bg-cream);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-image i {
    font-size: 5rem;
    color: var(--primary);
    opacity: 0.3;
}

.favorite-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: white;
    border: none;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s;
    color: var(--text-gray);
    z-index: 10;
}

.favorite-btn i {
    font-size: 1.1rem;
    transition: all 0.3s;
}

.favorite-btn:hover {
    background: var(--accent);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(232, 180, 162, 0.4);
}

.favorite-btn.active {
    background: var(--accent);
    color: white;
}

.favorite-btn.active i {
    color: white;
}

.product-info {
    padding: 25px;
}

.product-name {
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 8px;
    color: var(--text-dark);
}

.product-desc {
    color: var(--text-gray);
    font-size: 0.9rem;
    margin-bottom: 15px;
    line-height: 1.5;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.product-price {
    color: var(--primary);
    font-weight: 800;
    font-size: 1.3rem;
}

.add-to-cart {
    background: var(--bg-cream);
    color: var(--primary);
    border: 2px solid var(--border);
    padding: 8px 20px;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.add-to-cart:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* Modal Variant Selection */
.variant-option {
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.variant-option:hover {
    border-color: var(--secondary);
    background: var(--bg-cream);
}

.variant-option.selected {
    border-color: var(--primary);
    background: var(--bg-cream);
}

.variant-option input[type="radio"] {
    margin-right: 10px;
}

/* ✅ VIDEO TESTIMONIALS SECTION DENGAN AUTO SCROLL */
.video-testimonials-section {
    background: var(--bg-white);
    padding: 80px 40px;
    margin-top: 80px;
    overflow: hidden;
}

.testimonials-header {
    text-align: center;
    margin-bottom: 50px;
}

.testimonials-header h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 10px;
}

.testimonials-header p {
    color: var(--text-gray);
    font-size: 1.1rem;
}

.video-container {
    position: relative;
    max-width: 1400px;
    margin: 0 auto;
}

.video-grid {
    display: flex;
    gap: 25px;
    transition: transform 0.5s ease;
}

.video-card {
    min-width: 280px;
    background: var(--bg-white);
    border: 2px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s;
    cursor: pointer;
    flex-shrink: 0;
}

.video-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    border-color: var(--secondary);
}

.video-wrapper {
    position: relative;
    height: 320px;
    background: var(--bg-cream);
    overflow: hidden;
}

.video-wrapper video,
.video-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.video-wrapper.playing .video-overlay {
    background: rgba(0,0,0,0.1);
}

.video-play-btn {
    width: 70px;
    height: 70px;
    background: rgba(255,255,255,0.95);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.video-play-btn i {
    color: var(--primary);
    font-size: 1.8rem;
    margin-left: 5px;
}

.video-wrapper.playing .video-play-btn {
    background: var(--primary);
    transform: scale(0.9);
}

.video-wrapper.playing .video-play-btn i {
    color: white;
}

.video-duration {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0,0,0,0.75);
    color: white;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

.video-info {
    padding: 15px;
}

.video-user {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.9rem;
}

.user-name {
    font-weight: 600;
    color: var(--text-dark);
    font-size: 0.95rem;
}

.video-product {
    color: var(--text-gray);
    font-size: 0.85rem;
    margin-bottom: 8px;
}

.video-rating {
    color: #ffa500;
    font-size: 0.9rem;
}

.video-rating i {
    margin-right: 2px;
}

/* Video Navigation Arrows */
.video-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: white;
    border: 2px solid var(--border);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    z-index: 10;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.video-nav:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.video-nav.prev { left: -25px; }
.video-nav.next { right: -25px; }

/* ✅ FOOTER */
.site-footer {
    background: var(--text-dark);
    color: white;
    padding: 60px 40px 30px;
    margin-top: 80px;
}

.footer-content {
    max-width: 1400px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
    margin-bottom: 40px;
}

.footer-section h3 {
    color: var(--secondary);
    font-size: 1.5rem;
    margin-bottom: 20px;
    font-weight: 700;
}

.footer-section p {
    color: rgba(255,255,255,0.8);
    line-height: 1.8;
    margin-bottom: 20px;
}

.footer-section h4 {
    color: white;
    font-size: 1.1rem;
    margin-bottom: 20px;
    font-weight: 600;
}

.footer-section a {
    display: block;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    margin-bottom: 12px;
    transition: all 0.3s;
}

.footer-section a:hover {
    color: var(--secondary);
    padding-left: 5px;
}

.footer-section i {
    margin-right: 10px;
    width: 20px;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-links a {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.social-links a:hover {
    background: var(--secondary);
    transform: translateY(-3px);
}

.footer-bottom {
    text-align: center;
    padding-top: 30px;
    border-top: 1px solid rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.6);
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 992px) {
    .nav-menu { display: none; }
    .hero-title { font-size: 3rem; }
    .products-grid { grid-template-columns: repeat(2, 1fr); }
    .video-nav.prev { left: 10px; }
    .video-nav.next { right: 10px; }
}

@media (max-width: 768px) {
    .hero-title { font-size: 2.5rem; }
    .section-title { font-size: 2rem; }
    .products-grid { grid-template-columns: 1fr; }
    .video-grid { gap: 15px; }
    .video-card { min-width: 260px; }
}

@media (max-width: 480px) {
    .hero-title { font-size: 2rem; }
    .hero-subtitle { font-size: 1.2rem; }
    .video-card { min-width: 240px; }
}
/* Modal Product Detail - Enhanced */
#modalDetail .modal-content {
    border-radius: 24px;
    border: none;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

#modalDetail .modal-body {
    padding: 0;
}

#modalDetail .col-md-6:first-child {
    background: linear-gradient(135deg, var(--bg-cream) 0%, var(--pink-soft) 100%);
    min-height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

#modalDetail .col-md-6:first-child img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

#modalDetail .col-md-6:first-child:hover img {
    transform: scale(1.05);
}

#modalDetail .col-md-6:last-child {
    padding: 50px 40px;
    background: white;
}

#modalDetail .modal-title {
    font-weight: 700;
    color: var(--text-dark);
    font-size: 2rem;
    line-height: 1.2;
}

#modalDetail #md-price {
    font-weight: 800;
    color: #E53E3E;
    font-size: 2.2rem;
    margin-bottom: 15px;
}

#modalDetail #md-desc {
    font-size: 1rem;
    line-height: 1.7;
    color: var(--text-gray);
}

#modalDetail .btn-close {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 10;
    background: white;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    opacity: 1;
    transition: all 0.3s;
}

#modalDetail .btn-close:hover {
    transform: rotate(90deg);
    background: var(--bg-cream);
}

#modalDetail button[type="submit"] {
    background: var(--primary);
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    padding: 16px;
    border-radius: 50px;
    border: none;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(139, 111, 78, 0.3);
}

#modalDetail button[type="submit"]:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 111, 78, 0.4);
}

/* Responsive untuk modal */
@media (max-width: 768px) {
    #modalDetail .col-md-6:first-child {
        min-height: 300px;
    }
    
    #modalDetail .col-md-6:last-child {
        padding: 30px 20px;
    }
    
    #modalDetail .modal-title {
        font-size: 1.5rem;
    }
    
    #modalDetail #md-price {
        font-size: 1.8rem;
    }
}
/* Modal Fullscreen untuk mobile */
@media (max-width: 768px) {
    #modalDetail .modal-dialog {
        margin: 0;
        max-width: 100%;
        height: 100%;
    }
    #modalDetail .modal-content {
        height: 100%;
        border-radius: 0;
    }
    #modalDetail .modal-body {
        padding-bottom: 80px; /* Space for bottom bar */
    }
}

/* Hide scrollbar tapi tetap bisa scroll */
#modalDetail .modal-body::-webkit-scrollbar {
    width: 6px;
}
#modalDetail .modal-body::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

/* Smooth scrolling */
#modalDetail .modal-body {
    scroll-behavior: smooth;
}
</style>
</head>
<body>

<!-- Top Navigation -->
<nav class="top-nav">
    <div class="nav-container">
        <a href="index.php" class="logo">Texcer Hot</a>
        <ul class="nav-menu">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="#menu">Menu</a></li>
            <li><a href="riwayat.php">Pesanan</a></li>
        </ul>
        <div class="nav-icons">
            <!-- Notification Icon -->
            <button class="nav-icon-btn" onclick="showNotifications()">
                <i class="fas fa-bell"></i>
                <?php if($totalNotifications > 0): ?>
                <span class="notif-count"><?= $totalNotifications ?></span>
                <?php endif; ?>
            </button>
            
            <!-- Cart Icon -->
            <a href="checkout.php">
                <i class="fas fa-shopping-bag"></i>
                <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <span class="cart-count"><?= count($_SESSION['cart']) ?></span>
                <?php endif; ?>
            </a>
            
            <!-- User/Profile Icon -->
<?php if(isset($_SESSION['user_phone'])): ?>
    <?php if(($_SESSION['role'] ?? '') === 'admin'): ?>
    <a href="dashboard.php" title="Dashboard Admin"><i class="fas fa-tachometer-alt"></i></a>
    <?php else: ?>
    <a href="profile.php" title="Profil Saya"><i class="fas fa-user"></i></a>
    <?php endif; ?>
    <a href="auth/logout.php" title="Keluar" style="margin-left: 10px;"><i class="fas fa-sign-out-alt"></i></a>
<?php else: ?>
<a href="auth/login.php" title="Masuk"><i class="fas fa-sign-in-alt"></i></a>
<?php endif; ?>
        </div>
    </div>
</nav>

<!-- ✅ HERO SECTION FULL SCREEN DENGAN AUTO SLIDE -->
<section class="hero-section">
    <div class="hero-slider">
        <?php foreach($heroImages as $index => $image): ?>
        <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>">
            <img src="<?= $image ?>" alt="Hero Image <?= $index + 1 ?>">
        </div>
        <?php endforeach; ?>
    </div>

    <div class="hero-content">
        <h1 class="hero-title">ORDER<br>MAKANAN</h1>
        <p class="hero-subtitle">Welcome to Texcer Hot</p>
        <button class="hero-btn" onclick="document.getElementById('menu').scrollIntoView({behavior: 'smooth'})">
            <i class="fas fa-fire"></i> Pesan Sekarang
        </button>
    </div>

    <div class="hero-dots">
        <?php foreach($heroImages as $index => $image): ?>
        <div class="hero-dot <?= $index === 0 ? 'active' : '' ?>" onclick="goToSlide(<?= $index ?>)"></div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Menu Section -->
<section class="menu-section" id="menu">
    <div class="section-header">
        <div>
            <h2 class="section-title">Menu</h2>
            <p class="section-subtitle">makanan pedas</p>
        </div>
        <a href="checkout.php" class="my-basket-btn">
            <i class="fas fa-shopping-basket"></i> My Basket
        </a>
    </div>

    <!-- Category Tabs -->
    <div class="category-tabs">
        <button class="tab-btn active" data-filter="all">Semua</button>
        <button class="tab-btn" data-filter="mie">Mie</button>
        <button class="tab-btn" data-filter="mercon">Mercon</button>
        <button class="tab-btn" data-filter="dimsum">Dimsum</button>
        <button class="tab-btn" data-filter="minuman">Minuman</button>
    </div>

    <!-- Products Grid -->
    <div class="products-grid">
        <?php foreach($products as $p): 
        $category = strtolower($p['category'] ?? 'all');
        ?>
        <div class="product-card" 
            data-bs-toggle="modal" 
            data-bs-target="#modalDetail"
            data-id="<?= $p['id'] ?>"
            data-name="<?= $p['name'] ?>"
            data-price="<?= $p['price'] ?>"
            data-desc="<?= $p['description'] ?>"
            data-img="<?= $p['image'] ?>"
            data-category="<?= $category ?>"
            data-has-variant="<?= $p['has_variant'] ? '1' : '0' ?>"
            data-variants='<?= $p['has_variant'] ? json_encode($p['variants']) : '[]' ?>'>

            <div class="product-image">
                <button class="favorite-btn" onclick="toggleFavorite(this, event)">
                    <i class="far fa-heart"></i>
                </button>
                <?php if(!empty($p['image'])): ?>
                <img src="<?= $p['image'] ?>" alt="<?= $p['name'] ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                <i class="fas fa-utensils" style="display: none; font-size: 5rem; color: var(--primary); opacity: 0.3;"></i>
                <?php else: ?>
                <i class="fas fa-utensils"></i>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <h3 class="product-name"><?= $p['name'] ?></h3>
                <p class="product-desc"><?= $p['description'] ?></p>
                <div class="product-footer">
                    <span class="product-price">Rp <?= number_format($p['price'], 0, ',', '.') ?></span>
                    <button class="add-to-cart" onclick="openVariantModal(<?= $p['id'] ?>, '<?= $p['name'] ?>', <?= $p['has_variant'] ? 'true' : 'false' ?>)">
                        <?= $p['has_variant'] ? 'Pilih Varian' : 'To Cart' ?>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="text-align: center; margin-top: 50px;">
        <button class="my-basket-btn">More</button>
    </div>
</section>

<!-- ✅ VIDEO TESTIMONIALS DENGAN AUTO SCROLL & VIDEO DARI FOLDER -->
<section class="video-testimonials-section">
    <div class="testimonials-header">
        <h2>Testimoni Video</h2>
        <p>Lihat apa kata mereka tentang Texcer Hot</p>
    </div>

    <div class="video-container">
        <button class="video-nav prev" onclick="scrollVideos(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>

        <div class="video-grid" id="videoGrid">
            <?php foreach($videoTestimonials as $index => $video): ?>
            <div class="video-card" data-index="<?= $index ?>">
                <div class="video-wrapper" onclick="toggleVideo(this, '<?= $video['video'] ?>')">
                    <img src="<?= $video['thumbnail'] ?>" alt="<?= $video['user'] ?>" class="video-thumbnail">
                    <video src="<?= $video['video'] ?>" loop muted playsinline></video>
                    <div class="video-overlay">
                        <div class="video-play-btn">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                    <span class="video-duration"><?= $video['duration'] ?></span>
                </div>
                <div class="video-info">
                    <div class="video-user">
                        <div class="user-avatar" style="background: <?= $index % 3 == 0 ? 'var(--accent)' : ($index % 3 == 1 ? '#D4A574' : '#E8B4A2') ?>;"><?= $video['initial'] ?></div>
                        <span class="user-name"><?= $video['user'] ?></span>
                    </div>
                    <div class="video-product"><?= $video['product'] ?></div>
                    <div class="video-rating">
                        <?php for($i = 0; $i < $video['rating']; $i++): ?>
                        <i class="fas fa-star"></i>
                        <?php endfor; ?>
                        <?php for($i = $video['rating']; $i < 5; $i++): ?>
                        <i class="far fa-star"></i>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <button class="video-nav next" onclick="scrollVideos(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</section>

<!-- ✅ FOOTER -->
<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>Texcer Hot</h3>
            <p>Pesan makanan pedas favoritmu sekarang! Kualitas terbaik, rasa autentik, dan pengiriman cepat ke tempatmu.</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
        <div class="footer-section">
            <h4>Link Cepat</h4>
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="#menu"><i class="fas fa-utensils"></i> Menu</a>
            <a href="riwayat.php"><i class="fas fa-history"></i> Pesanan</a>
            <a href="checkout.php"><i class="fas fa-shopping-bag"></i> Keranjang</a>
        </div>
        <div class="footer-section">
            <h4>Bantuan</h4>
            <a href="#"><i class="fas fa-question-circle"></i> FAQ</a>
            <a href="#"><i class="fas fa-file-alt"></i> Syarat & Ketentuan</a>
            <a href="#"><i class="fas fa-shield-alt"></i> Kebijakan Privasi</a>
            <a href="#"><i class="fas fa-envelope"></i> Hubungi Kami</a>
        </div>
        <div class="footer-section">
            <h4>Kontak</h4>
            <a href="#"><i class="fas fa-map-marker-alt"></i> Jl. Pedas No. 10, Jakarta</a>
            <a href="#"><i class="fas fa-phone"></i> +62 812-3456-7890</a>
            <a href="#"><i class="fas fa-envelope"></i> info@texcerhot.com</a>
            <a href="#"><i class="fas fa-clock"></i> Buka: 10.00 - 22.00</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Texcer Hot. All rights reserved. Made with <i class="fas fa-heart" style="color: var(--accent);"></i> and lots of chili 🌶️</p>
    </div>
</footer>

<!-- Modal Detail Produk -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content" style="border: none; border-radius: 0;">
            
            <!-- Header dengan back button -->
            <div class="modal-header" style="border-bottom: 1px solid #e0e0e0; padding: 12px 16px; position: sticky; top: 0; background: white; z-index: 100;">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal" style="text-decoration: none; color: #333; padding: 0; font-size: 1.5rem;">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div style="flex: 1; margin: 0 12px;">
                    <input type="text" placeholder="Cari di Texcer Hot" style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 8px; background: #f5f5f5;">
                </div>
                <button class="btn btn-link" style="color: #333; padding: 0;">
                    <i class="fas fa-share-alt"></i>
                </button>
            </div>

            <div class="modal-body" style="padding: 0; overflow-y: auto; max-height: calc(100vh - 140px);">
                
                <!-- Product Image Slider -->
                <div style="position: relative; background: white;">
                    <img src="" id="md-img" class="img-fluid" style="width: 100%; max-height: 400px; object-fit: cover;">
                    <span style="position: absolute; bottom: 12px; right: 12px; background: rgba(0,0,0,0.6); color: white; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem;">1/9</span>
                </div>

                <!-- Flash Sale Banner -->
                <div style="background: linear-gradient(135deg, #FF5858 0%, #FF8A8A 100%); padding: 12px 16px; color: white;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <span style="background: white; color: #FF5858; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 0.85rem; margin-right: 8px;">-58%</span>
                            <span style="font-weight: 600;">Mulai <span id="md-price-flash" style="font-size: 1.3rem; font-weight: 800;"></span></span>
                        </div>
                        <div style="text-align: right;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-bolt" style="color: #FFD700;"></i>
                                <span style="font-weight: 700;">Flash Sale</span>
                            </div>
                            <div style="font-size: 0.8rem; opacity: 0.9;">Berakhir dalam 07:04:20</div>
                        </div>
                    </div>
                </div>

                <!-- PayLater Info -->
                <div style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0;">
                    <div style="font-size: 0.85rem; color: #666;">
                        <i class="fas fa-credit-card" style="color: #00B14F; margin-right: 6px;"></i>
                        PayLater dari Rp<span id="md-paylater">0</span>/bln | <span style="color: #FF5858;">Limit 50jt</span>
                        <i class="fas fa-chevron-right" style="float: right; color: #999;"></i>
                    </div>
                </div>

                <!-- Discount Badge -->
                <div style="padding: 8px 16px; background: #FFF0F0;">
                    <span style="background: #FFE0E0; color: #FF5858; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                        <i class="fas fa-tag" style="margin-right: 4px;"></i>Diskon Rp15rb
                    </span>
                </div>

                <!-- Product Name & Rating -->
                <div style="padding: 16px; border-bottom: 1px solid #f0f0f0;">
                    <h3 id="md-name" style="font-size: 1.1rem; font-weight: 600; margin-bottom: 8px; line-height: 1.4;"></h3>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                        <span style="color: #FFB800;">
                            <i class="fas fa-star"></i>
                            <strong style="color: #333;">4.6</strong>
                        </span>
                        <span style="color: #999;">(1,5 rb)</span>
                        <span style="color: #999;">|</span>
                        <span style="color: #999;">10rb+ terjual</span>
                        <i class="far fa-bookmark" style="margin-left: auto; color: #999; font-size: 1.2rem;"></i>
                    </div>
                </div>

                <!-- Shipping Info -->
                <div style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.9rem;">
                        <i class="fas fa-truck" style="color: #00B14F; margin-top: 3px;"></i>
                        <div style="flex: 1;">
                            <span style="background: #E0F7FA; color: #00838F; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem;">Pengiriman gratis</span>
                            <span style="color: #333; margin-left: 6px;">Dijamin tiba paling lambat pada 14-16 Mei</span>
                        </div>
                    </div>
                    <div style="margin-top: 8px; padding-left: 28px; font-size: 0.85rem; color: #666;">
                        Dapatkan voucher senilai min. Rp25K jika pesanan terlambat tiba
                        <i class="fas fa-info-circle" style="margin-left: 4px;"></i>
                    </div>
                    <div style="margin-top: 6px; padding-left: 28px; font-size: 0.85rem; color: #666;">
                        Ongkir: Rp20.500
                    </div>
                </div>

                <!-- Protection -->
                <div style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: #333;">
                        <i class="fas fa-shield-alt" style="color: #666;"></i>
                        <span>Proteksi Rusak Total | Bayar di tempat | Pengembalian Gratis</span>
                        <i class="fas fa-chevron-right" style="margin-left: auto; color: #999;"></i>
                    </div>
                </div>

                <!-- Variants -->
                <div style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-th" style="color: #666;"></i>
                        <span style="flex: 1; font-size: 0.9rem;">4 opsi tersedia</span>
                        <i class="fas fa-chevron-right" style="color: #999;"></i>
                    </div>
                </div>

                <!-- Voucher & Promo -->
                <div style="padding: 16px; border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <h4 style="font-size: 1.1rem; font-weight: 600; margin: 0;">Voucher & Promo</h4>
                        <i class="fas fa-chevron-right" style="color: #999;"></i>
                    </div>
                    <div style="display: flex; gap: 12px; overflow-x: auto;">
                        <div style="background: #F0FDF4; border: 1px solid #00B14F; border-radius: 8px; padding: 12px; min-width: 280px; flex-shrink: 0;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-truck" style="color: #00B14F; font-size: 1.5rem;"></i>
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; color: #00838F; font-size: 1.1rem;">Diskon Rp30rb</div>
                                    <div style="font-size: 0.8rem; color: #666;">untuk pesanan di atas Rp20rb</div>
                                </div>
                                <button style="background: white; border: 1px solid #00B14F; color: #00B14F; padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: 0.85rem;">Gunakan</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PayLater Section -->
                <div style="padding: 16px; border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 600; margin: 0 0 4px 0;">PayLater</h4>
                            <span style="background: #FFF0F0; color: #FF5858; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">Aktifkan batas kredit hingga Rp50 JT</span>
                        </div>
                        <i class="fas fa-chevron-right" style="color: #999;"></i>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div style="padding: 16px; border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                        <h4 style="font-size: 1.1rem; font-weight: 600; margin: 0;">Ulasan</h4>
                        <span style="color: #999; font-size: 0.9rem;">(28)</span>
                    </div>
                    
                    <!-- Review Item -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #666;">**</div>
                            <div style="color: #FFB800;">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <div style="font-size: 0.85rem; color: #666; margin-bottom: 4px;">Varian: PUTIH</div>
                        <div style="font-size: 0.9rem; color: #333; line-height: 1.5;">Kapasitas:lumayan luas, uang sm make'up kecil masi bisa masuk hih...</div>
                        <div style="display: flex; gap: 8px; margin-top: 12px; overflow-x: auto;">
                            <img src="https://via.placeholder.com/80" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            <img src="https://via.placeholder.com/80" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            <img src="https://via.placeholder.com/80" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            <div style="width: 80px; height: 80px; background: #333; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">+2</div>
                        </div>
                    </div>
                </div>

                <!-- Store Info -->
                <div style="padding: 16px; border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-store" style="font-size: 1.5rem; color: #999;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 1rem;">MY AKSA Indonesia</div>
                            <div style="font-size: 0.85rem; color: #666;">
                                <i class="fas fa-star" style="color: #00B14F;"></i> 3.6 &nbsp; 24.6K terjual
                            </div>
                        </div>
                        <button style="background: #f0f0f0; border: none; padding: 8px 16px; border-radius: 20px; font-weight: 600; color: #333;">Kunjungi</button>
                    </div>
                    <div style="margin-top: 12px; font-size: 0.85rem; color: #666;">
                        <span>100% merespons dalam 24j</span> &nbsp; 
                        <span>69% mengirim dalam 48j</span>
                    </div>
                </div>

                <!-- Komisi Section -->
                <div style="padding: 12px 16px; background: #FFF8E1; border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-fire" style="color: #FF9800;"></i>
                        <span style="flex: 1; font-size: 0.9rem;">Bagikan untuk mendapatkan <strong style="color: #FF5858;">Rp5.000</strong> per penjualan</span>
                        <i class="fas fa-chevron-up" style="color: #999;"></i>
                    </div>
                    <div style="font-size: 0.85rem; color: #999; margin-top: 4px;">Komisi 5%</div>
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="modal-footer" style="border-top: 1px solid #e0e0e0; padding: 12px 16px; background: white; position: sticky; bottom: 0;">
                <button class="btn btn-link" style="text-decoration: none; color: #333; padding: 8px;">
                    <i class="fas fa-store" style="display: block; font-size: 1.2rem;"></i>
                    <span style="font-size: 0.75rem;">Toko</span>
                </button>
                <button class="btn btn-link" style="text-decoration: none; color: #333; padding: 8px;">
                    <i class="fas fa-comment" style="display: block; font-size: 1.2rem;"></i>
                    <span style="font-size: 0.75rem;">Chat</span>
                </button>
                <button class="btn" style="background: #f0f0f0; border-radius: 50%; width: 48px; height: 48px; padding: 0; display: flex; align-items: center; justify-content: center; margin: 0 8px;">
                    <i class="fas fa-shopping-cart" style="color: #00B14F; font-size: 1.3rem;"></i>
                </button>
                <button type="submit" form="modalFormPesan" class="btn" style="background: #00B14F; color: white; border-radius: 24px; padding: 12px 32px; font-weight: 600; flex: 1; max-width: 200px;">
                    <div style="font-size: 1rem;">Beli sekarang</div>
                    <div style="font-size: 0.85rem; opacity: 0.9;"><span id="md-price-bottom"></span> | Pengiriman gratis</div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Variant Selection -->
<div class="modal fade" id="modalVariant" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="border-bottom: 2px solid var(--border); background: var(--bg-cream); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title" id="mv-title" style="font-weight: 700; color: var(--text-dark);">Pilih Varian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div id="variant-options">
                    <!-- Variant options will be inserted here -->
                </div>
                <form method="POST" id="variantForm">
                    <input type="hidden" name="id" id="mv-id">
                    <input type="hidden" name="name" id="mv-name">
                    <input type="hidden" name="price" id="mv-price">
                    <input type="hidden" name="variant" id="mv-variant">
                    <button type="submit" name="add_to_cart" class="btn w-100 py-3 mt-3 fw-bold" style="border-radius: 50px; background: var(--primary); color: white; border: none; font-size: 1.1rem;" id="btnAddToCart">
                        <i class="fas fa-shopping-cart me-2"></i> Tambah ke Keranjang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ✅ HERO SLIDER AUTO CHANGE
let currentHeroSlide = 0;
const heroSlides = document.querySelectorAll('.hero-slide');
const heroDots = document.querySelectorAll('.hero-dot');

function showHeroSlide(index) {
    heroSlides.forEach((slide, i) => {
        slide.classList.remove('active');
        heroDots[i].classList.remove('active');
    });
    heroSlides[index].classList.add('active');
    heroDots[index].classList.add('active');
    currentHeroSlide = index;
}

function goToSlide(index) {
    showHeroSlide(index);
    resetHeroInterval();
}

function nextHeroSlide() {
    let next = (currentHeroSlide + 1) % heroSlides.length;
    showHeroSlide(next);
}

let heroInterval = setInterval(nextHeroSlide, 5000); // Ganti setiap 5 detik

function resetHeroInterval() {
    clearInterval(heroInterval);
    heroInterval = setInterval(nextHeroSlide, 5000);
}

// Pause on hover
document.querySelector('.hero-section').addEventListener('mouseenter', () => {
    clearInterval(heroInterval);
});

document.querySelector('.hero-section').addEventListener('mouseleave', () => {
    heroInterval = setInterval(nextHeroSlide, 5000);
});

// Category Filter
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const filter = this.getAttribute('data-filter');
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(card => {
            const category = card.getAttribute('data-category');
            if(filter === 'all' || category === filter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// Favorite button
function toggleFavorite(btn, event) {
    event.stopPropagation();
    btn.classList.toggle('active');
    const icon = btn.querySelector('i');
    if(btn.classList.contains('active')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
    }
}

// Modal Detail
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

// Variant Modal
const variantModal = new bootstrap.Modal(document.getElementById('modalVariant'));

function openVariantModal(id, name, hasVariant) {
    if(!hasVariant) {
        // Jika tidak ada varian, langsung submit
        document.getElementById('md-id').value = id;
        document.getElementById('md-form-name').value = name;
        // Find product price
        const product = <?= json_encode($products) ?>.find(p => p.id === id);
        document.getElementById('md-form-price').value = product.price;
        document.getElementById('modalFormPesan').submit();
    } else {
        // Show variant modal
        const product = <?= json_encode($products) ?>.find(p => p.id === id);
        document.getElementById('mv-title').innerText = 'Pilih Varian - ' + name;
        document.getElementById('mv-id').value = product.id;
        document.getElementById('mv-name').value = product.name;
        
        const variantOptions = document.getElementById('variant-options');
        variantOptions.innerHTML = '';
        
        product.variants.forEach((variant, index) => {
            const div = document.createElement('div');
            div.className = 'variant-option' + (index === 0 ? ' selected' : '');
            div.innerHTML = `
                <label style="cursor: pointer; display: flex; align-items: center; width: 100%; margin: 0;">
                    <input type="radio" name="variant_select" value="${variant.name}" data-price="${variant.price}" ${index === 0 ? 'checked' : ''} style="margin-right: 10px;">
                    <span style="flex: 1;">${variant.name}</span>
                    <span style="font-weight: 700; color: var(--primary);">Rp ${variant.price.toLocaleString('id-ID')}</span>
                </label>
            `;
            variantOptions.appendChild(div);
        });
        
        // Set default variant
        const defaultVariant = product.variants[0];
        document.getElementById('mv-price').value = defaultVariant.price;
        document.getElementById('mv-variant').value = defaultVariant.name;
        
        // Add event listeners
        document.querySelectorAll('.variant-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.variant-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                document.getElementById('mv-price').value = radio.dataset.price;
                document.getElementById('mv-variant').value = radio.value;
            });
        });
        
        variantModal.show();
    }
}

// ✅ VIDEO TESTIMONIAL AUTO SCROLL & PLAY
let videoScrollPosition = 0;
const videoGrid = document.getElementById('videoGrid');
const videoCards = document.querySelectorAll('.video-card');
const cardWidth = 305; // 280px + 25px gap

function scrollVideos(direction) {
    const maxScroll = videoGrid.scrollWidth - videoGrid.parentElement.offsetWidth;
    videoScrollPosition += direction * cardWidth;

    if(videoScrollPosition < 0) videoScrollPosition = 0;
    if(videoScrollPosition > maxScroll) videoScrollPosition = maxScroll;

    videoGrid.style.transform = `translateX(-${videoScrollPosition}px)`;
}

// Auto scroll videos
let videoAutoScroll = setInterval(() => {
    const maxScroll = videoGrid.scrollWidth - videoGrid.parentElement.offsetWidth;
    if(videoScrollPosition >= maxScroll) {
        videoScrollPosition = 0;
    } else {
        videoScrollPosition += cardWidth;
        if(videoScrollPosition > maxScroll) videoScrollPosition = maxScroll;
    }
    videoGrid.style.transform = `translateX(-${videoScrollPosition}px)`;
}, 4000); // Scroll setiap 4 detik

// Pause auto scroll on hover
videoGrid.addEventListener('mouseenter', () => {
    clearInterval(videoAutoScroll);
});

videoGrid.addEventListener('mouseleave', () => {
    videoAutoScroll = setInterval(() => {
        const maxScroll = videoGrid.scrollWidth - videoGrid.parentElement.offsetWidth;
        if(videoScrollPosition >= maxScroll) {
            videoScrollPosition = 0;
        } else {
            videoScrollPosition += cardWidth;
            if(videoScrollPosition > maxScroll) videoScrollPosition = maxScroll;
        }
        videoGrid.style.transform = `translateX(-${videoScrollPosition}px)`;
    }, 4000);
});

// ✅ VIDEO PLAY/PAUSE - SUARA HANYA KELUAR SAAT DIKLIK
function toggleVideo(wrapper, videoSrc) {
    const video = wrapper.querySelector('video');
    const thumbnail = wrapper.querySelector('.video-thumbnail');
    const isPlaying = !video.paused;

    // Pause all other videos
    document.querySelectorAll('.video-wrapper video').forEach(v => {
        if(v !== video) {
            v.pause();
            v.currentTime = 0;
            v.muted = true;
            v.parentElement.classList.remove('playing');
            v.parentElement.querySelector('.video-thumbnail').style.display = 'block';
        }
    });

    if(isPlaying) {
        // Pause video
        video.pause();
        video.muted = true;
        wrapper.classList.remove('playing');
        thumbnail.style.display = 'block';
    } else {
        // Play video with sound
        video.play();
        video.muted = false;
        wrapper.classList.add('playing');
        thumbnail.style.display = 'none';
    }
}

// Reset video when ended
document.querySelectorAll('.video-wrapper video').forEach(video => {
    video.addEventListener('ended', function() {
        this.pause();
        this.currentTime = 0;
        this.muted = true;
        this.parentElement.classList.remove('playing');
        this.parentElement.querySelector('.video-thumbnail').style.display = 'block';
    });
});

// Notification function
function showNotifications() {
    alert('Fitur notifikasi akan menampilkan pesanan Anda yang sedang diproses dan dikirim.');
    // Implementasi notifikasi yang lebih kompleks bisa ditambahkan di sini
}

// Show success message if added to cart
<?php if(isset($_GET['added']) && $_GET['added'] == '1'): ?>
setTimeout(() => {
    alert('Produk berhasil ditambahkan ke keranjang!');
}, 500);
<?php endif; ?>
</script>

</body>
</html>
