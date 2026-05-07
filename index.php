<?php include 'config.php'; 

// Logika Tambah ke Cart
if(isset($_POST['add_to_cart'])){
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    
    $cart_item = ['id' => $id, 'name' => $name, 'price' => $price, 'qty' => 1];

    // Cek jika barang sudah ada di session
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
    /* === MODERN GLASS & TRANSPARENT THEME === */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');

    :root {
        --accent: #ff6b35;
        --accent-hover: #ff8555;
        --text-main: #ffffff;
        --text-muted: #a0a0a0;
        --glass-bg: rgba(18, 18, 18, 0.65);
        --glass-card: rgba(30, 30, 30, 0.45);
        --glass-border: rgba(255, 255, 255, 0.08);
        --blur-strength: 16px;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

body { 
    /* Background dengan GAMBAR + overlay gelap + glow */
    background: 
        /* Overlay gelap agar teks tetap terbaca */
        linear-gradient(180deg, rgba(8,8,8,0.85) 0%, rgba(15,15,15,0.9) 100%),
        /* Gambar background utama */
        url('assets/images/bg-food.jpg'),
        /* Glow effect oranye */
        radial-gradient(circle at 15% 15%, rgba(255, 107, 53, 0.15), transparent 45%),
        radial-gradient(circle at 85% 85%, rgba(255, 133, 85, 0.1), transparent 45%);
    
    background-size: cover, cover, auto, auto;
    background-position: center center, center center, 0 0, 100% 100%;
    background-attachment: fixed, fixed, fixed, fixed;
    background-repeat: no-repeat, no-repeat, no-repeat, no-repeat;
    
    color: var(--text-main); 
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    padding-bottom: 90px;
    min-height: 100vh;
    line-height: 1.6;
}

    /* === GLASSMORPHISM UTILITY === */
    .glass {
        background: var(--glass-bg);
        backdrop-filter: blur(var(--blur-strength));
        -webkit-backdrop-filter: blur(var(--blur-strength));
        border: 1px solid var(--glass-border);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
    }

    /* === TOP NAVIGATION (TRANSPARENT) === */
    .top-nav {
        background: rgba(12, 12, 12, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--glass-border);
        padding: 15px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 100;
    }
    .menu-btn {
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--glass-border);
        color: var(--text-main);
        font-size: 20px;
        cursor: pointer;
        width: 42px; height: 42px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 12px;
        transition: 0.3s;
    }
    .menu-btn:hover { background: var(--accent); color: white; border-color: transparent; }

    .top-icons { display: flex; gap: 10px; }
    .top-icons i {
        color: var(--text-muted);
        width: 42px; height: 42px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 12px;
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--glass-border);
        transition: 0.3s;
    }
    .top-icons i:hover { background: var(--accent); color: white; border-color: transparent; transform: translateY(-2px); }

    /* === HERO SECTION === */
    .hero-section {
        text-align: center;
        padding: 90px 20px 60px;
        position: relative;
    }
    .hero-title {
        font-size: 3.8rem;
        font-weight: 800;
        background: linear-gradient(135deg, #fff 0%, var(--accent) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 15px;
        line-height: 1.05;
        letter-spacing: -1.5px;
    }
    .hero-subtitle { color: var(--text-muted); font-size: 1.15rem; margin-bottom: 45px; }

    .search-container {
        display: flex;
        gap: 12px;
        max-width: 650px;
        margin: 0 auto;
        padding: 0 20px;
    }
    .search-input {
        flex: 1;
        background: rgba(255,255,255,0.06);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        padding: 16px 24px;
        color: white;
        font-size: 1rem;
        outline: none;
        backdrop-filter: blur(10px);
        transition: 0.3s;
    }
    .search-input:focus {
        border-color: var(--accent);
        background: rgba(255,255,255,0.1);
        box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.15);
    }
    .search-input::placeholder { color: #777; }

    .filter-btn {
        background: rgba(255,255,255,0.08);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        padding: 0 28px;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        backdrop-filter: blur(10px);
        transition: 0.3s;
    }
    .filter-btn:hover { background: var(--accent); border-color: transparent; transform: translateY(-2px); }

    /* === MENU & CARDS (TRANSPARENT GLASS) === */
    .menu-section { padding: 40px 25px; }
    .section-title { font-size: 1.9rem; font-weight: 700; margin-bottom: 30px; letter-spacing: -0.5px; }

    .card-product { 
        background: var(--glass-card);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 22px; 
        overflow: hidden; 
        cursor: pointer; 
        transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
        height: 100%;
        position: relative;
    }
    .card-product:hover { 
        transform: translateY(-10px) scale(1.01); 
        border-color: rgba(255, 107, 53, 0.5);
        box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 30px rgba(255, 107, 53, 0.15);
        background: rgba(30, 30, 30, 0.6);
    }
    .card-img-top { height: 210px; object-fit: cover; border-bottom: 1px solid var(--glass-border); }
    
    .card-body { padding: 22px; }
    .card-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 10px; letter-spacing: -0.3px; }
    .price-tag { color: var(--accent); font-weight: 800; font-size: 1.3rem; margin-bottom: 18px; letter-spacing: -0.5px; }
    
    .btn-add { 
        background: linear-gradient(135deg, rgba(255,107,53,0.85), rgba(255,133,85,0.85)); 
        backdrop-filter: blur(8px);
        color: white; 
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 14px;
        padding: 13px;
        font-weight: 600;
        width: 100%;
        transition: 0.3s;
        font-size: 0.95rem;
    }
    .btn-add:hover { 
        background: var(--accent); 
        transform: scale(1.03);
        box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
    }

    /* === MODAL (GLASS) === */
    .modal-content {
        background: rgba(15, 15, 15, 0.85);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid var(--glass-border);
        border-radius: 26px;
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0,0,0,0.6);
    }
    .modal-header { border-bottom: 1px solid var(--glass-border); padding: 22px 28px; background: rgba(255,255,255,0.03); }
    .modal-body { padding: 0; }
    .btn-close-white { filter: invert(1) brightness(1.5); }

    /* === BOTTOM NAVIGATION (GLASS) === */
    .bottom-nav { 
        position: fixed; 
        bottom: 0; 
        width: 100%; 
        background: rgba(10, 10, 10, 0.8);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-top: 1px solid var(--glass-border);
        z-index: 1000;
        padding: 14px 0;
    }
    .nav-item { 
        color: var(--text-muted); 
        text-decoration: none; 
        text-align: center; 
        font-size: 0.75rem;
        transition: 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 14px;
    }
    .nav-item i { font-size: 1.45rem; transition: 0.3s; }
    .nav-item.active, .nav-item:hover { color: var(--accent); background: rgba(255,107,53,0.1); }
    .nav-item.active i { transform: translateY(-4px); }

    /* === RESPONSIVE === */
    @media (max-width: 768px) {
        .hero-title { font-size: 2.6rem; }
        .search-container { flex-direction: column; }
        .filter-btn { padding: 16px; justify-content: center; }
        .card-img-top { height: 180px; }
    }
</style>
</head>
<body>

<!-- Top Navigation -->
<div class="top-nav">
    <button class="menu-btn">
        <i class="fas fa-bars"></i>
    </button>
    <div class="top-icons">
        <i class="fas fa-search" data-bs-toggle="modal" data-bs-target="#searchModal"></i>
        <i class="fas fa-bell"></i>
    </div>
</div>

<!-- Hero Section -->
<div class="hero-section">
    <h1 class="hero-title">Find Your<br>Favorite Food</h1>
    <p class="hero-subtitle">Pesan makanan favoritmu sekarang!</p>
    
    <div class="search-container">
        <input type="text" class="search-input" placeholder="Cari makanan..." id="searchInput">
        <button class="filter-btn">
            Filter <i class="fas fa-chevron-down"></i>
        </button>
    </div>
</div>

<!-- Menu Grid -->
<div class="menu-section">
    <h2 class="section-title">Menu Kami</h2>
    <div class="row g-3">
        <?php while($p = mysqli_fetch_assoc($products)): ?>
        <div class="col-6 col-md-3">
            <div class="card card-product" data-bs-toggle="modal" data-bs-target="#modalDetail" 
     data-id="<?= $p['id'] ?>"
     data-name="<?= $p['name'] ?>" 
     data-price="<?= $p['price'] ?>" 
     data-desc="<?= $p['description'] ?>" 
     data-img="<?= $p['image'] ?>">
                <img src="<?= $p['image'] ?>" class="card-img-top" alt="<?= $p['name'] ?>">
                <div class="card-body">
                    <h6 class="card-title"><?= $p['name'] ?></h6>
                    <p class="price-tag"><?= formatRupiah($p['price']) ?></p>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="name" value="<?= $p['name'] ?>">
                        <input type="hidden" name="price" value="<?= $p['price'] ?>">
                        <button type="submit" name="add_to_cart" class="btn btn-add">
                            <i class="fas fa-plus"></i> Pesan
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Modal Detail Produk Lengkap -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content" style="background: #1a1a1a; border: none; border-radius: 0;">
            
            <!-- Header Modal -->
            <div class="modal-header border-0" style="background: #111; position: sticky; top: 0; z-index: 10; border-radius: 0;">
                <h5 class="modal-title text-warning" id="md-name" style="font-size: 1.3rem; font-weight: 700;"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Body Modal -->
            <div class="modal-body p-0">
                
                <!-- Gambar Produk -->
                <div style="position: relative;">
                    <img src="" id="md-img" class="img-fluid w-100" style="max-height: 400px; object-fit: cover; border-radius: 15px 15px 0 0;">
                    <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3" style="font-size: 0.85rem;">
                        <i class="fas fa-fire me-1"></i>Terlaris
                    </span>
                </div>
                
                <!-- Info Produk -->
                <div style="padding: 20px 25px;">
                    
                    <!-- Harga & Stok -->
                    <div class="mb-3">
                        <h3 class="text-warning mb-1" id="md-price" style="font-size: 1.8rem; font-weight: 700;"></h3>
                        <div class="text-secondary" style="font-size: 0.9rem;">
                            <i class="fas fa-check-circle text-success me-1"></i> Stok Tersedia
                        </div>
                    </div>
                    
                    <!-- Rating & Terjual -->
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="text-white fw-bold">4.8</span>
                        <span class="text-secondary">| 127 Terjual</span>
                    </div>
                    
                    <!-- Deskripsi -->
                    <div class="mb-4">
                        <h6 class="text-white mb-2"><i class="fas fa-info-circle text-warning me-1"></i> Deskripsi Produk</h6>
                        <p id="md-desc" class="text-secondary mb-0" style="line-height: 1.6; font-size: 0.95rem;"></p>
                    </div>
                    
                    <!-- Info Tambahan (Grid 2 Kolom) -->
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div style="background: #222; padding: 12px; border-radius: 8px; text-align: center;">
                                <i class="fas fa-motorcycle text-warning mb-1 d-block"></i>
                                <div class="text-secondary" style="font-size: 0.8rem;">Pengiriman Cepat</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background: #222; padding: 12px; border-radius: 8px; text-align: center;">
                                <i class="fas fa-shield-alt text-warning mb-1 d-block"></i>
                                <div class="text-secondary" style="font-size: 0.8rem;">Garansi Uang Kembali</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Pesan (Hidden Input + Tombol) -->
                    <form method="POST" id="modalFormPesan">
                        <input type="hidden" name="id" id="md-id">
                        <input type="hidden" name="name" id="md-form-name">
                        <input type="hidden" name="price" id="md-form-price">
                        
                        <button type="submit" name="add_to_cart" class="btn btn-warning w-100 py-3 fw-bold" style="font-size: 1.1rem; background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%); border: none; border-radius: 10px;">
                            <i class="fas fa-shopping-cart me-2"></i> + Pesan Sekarang
                        </button>
                    </form>
                    
                    <!-- Info Toko & Pengiriman -->
                    <div class="mt-4 pt-3 border-top border-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary"><i class="fas fa-store me-2"></i>Texcer Hot</span>
                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Online</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary"><i class="fas fa-clock me-2"></i>Proses 1-2 jam</span>
                            <span class="text-warning"><i class="fas fa-truck me-1"></i> Gratis Ongkir</span>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Search -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <input type="text" class="form-control form-control-lg" placeholder="Cari makanan...">
            </div>
        </div>
    </div>
</div>

<!-- Bottom Navigation -->
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<div class="bottom-nav d-flex justify-content-around">
    <!-- Home -->
    <a href="index.php" class="nav-item <?= $currentPage == 'index.php' ? 'active' : '' ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    
    <!-- Order = Riwayat Pesanan (pakai icon history) -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Modal Detail
    // Modal Detail Produk
var modal = document.getElementById('modalDetail');
modal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    
    // Isi data modal
    document.getElementById('md-name').innerText = button.getAttribute('data-name');
    document.getElementById('md-price').innerText = "Rp " + parseInt(button.getAttribute('data-price')).toLocaleString('id-ID');
    document.getElementById('md-desc').innerText = button.getAttribute('data-desc');
    document.getElementById('md-img').src = button.getAttribute('data-img');
    
    // Isi hidden form untuk pesan
    document.getElementById('md-id').value = button.getAttribute('data-id');
    document.getElementById('md-form-name').value = button.getAttribute('data-name');
    document.getElementById('md-form-price').value = button.getAttribute('data-price');
});

    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        var value = e.target.value.toLowerCase();
        var cards = document.querySelectorAll('.card-product');
        
        cards.forEach(function(card) {
            var title = card.querySelector('.card-title').innerText.toLowerCase();
            if(title.indexOf(value) > -1) {
                card.parentElement.style.display = '';
            } else {
                card.parentElement.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>