<?php 
include 'config.php'; 

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
    /* === RESET & BASE === */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
        background-color: #0a0a0a; 
        color: white; 
        font-family: 'Segoe UI', sans-serif; 
        padding-bottom: 100px; /* Ruang untuk navbar bawah */
    }
    
    /* === HALAMAN UTAMA (Luar Modal) === */
    .page-header { 
        background: #111; 
        padding: 15px 20px; 
        border-bottom: 1px solid #222; 
        display: flex; 
        align-items: center; 
        gap: 15px; 
        position: sticky; 
        top: 0; 
        z-index: 100; 
    }
    .page-header h3 { color: #ff6600; font-size: 1.2rem; margin: 0; }
    .back-btn { color: white; font-size: 1.2rem; text-decoration: none; }
    
    .cart-item { 
        background: #1a1a1a; 
        margin: 15px; 
        padding: 20px; 
        border-radius: 12px; 
        border: 1px solid #222; 
    }
    .shop-header {
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #222;
    }
    .shop-name { font-weight: 700; color: white; font-size: 1rem; }
    .shop-badge { background: #ff6600; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
    
    .product-row {
        display: flex; gap: 15px; margin-bottom: 15px; padding: 10px 0;
        border-bottom: 1px solid #222;
    }
    .product-row:last-child { border-bottom: none; }
    .product-img {
        width: 80px; height: 80px; background: #222; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;
    }
    .product-img i { font-size: 2rem; color: #444; }
    .product-info { flex: 1; }
    .product-name { color: white; font-weight: 600; margin-bottom: 8px; font-size: 0.95rem; }
    .variant-box { background: #222; padding: 4px 10px; border-radius: 4px; display: inline-block; margin-bottom: 8px; font-size: 0.8rem; color: #888; }
    .price-section { display: flex; align-items: center; gap: 10px; }
    .current-price { color: #ff6600; font-weight: 700; font-size: 1rem; }
    
    .qty-control {
        display: flex; align-items: center; gap: 8px; margin-top: 10px;
        background: #222; border-radius: 6px; padding: 4px; width: fit-content;
    }
    .qty-btn {
        width: 28px; height: 28px; background: #333; border: none; border-radius: 4px;
        color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; text-decoration: none;
    }
    .qty-btn:hover { background: #ff6600; }
    .qty-value { width: 30px; text-align: center; color: white; font-weight: 600; }
    .remove-btn { color: #ff6b6b; text-decoration: none; font-size: 0.85rem; margin-left: auto; display: flex; align-items: center; gap: 5px; }
    
    .summary-section {
        background: #1a1a1a; margin: 15px; padding: 20px;
        border-radius: 12px; border: 1px solid #222;
    }
    .section-title { display: flex; align-items: center; gap: 10px; color: white; font-weight: 600; margin-bottom: 15px; }
    .section-title i { color: #ff6600; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; color: #888; font-size: 0.9rem; }
    .summary-row.total { border-top: 1px solid #333; padding-top: 15px; margin-top: 15px; color: white; font-size: 1.1rem; font-weight: 700; }
    .summary-row.total .amount { color: #ff6600; font-size: 1.3rem; }
    
    .bottom-bar {
        position: fixed; bottom: 0; left: 0; right: 0; background: #111;
        padding: 15px 20px; border-top: 1px solid #222;
        display: flex; align-items: center; justify-content: space-between; gap: 15px; z-index: 100;
    }
    .total-label { color: #888; font-size: 0.9rem; }
    .total-price { color: #ff6600; font-weight: 700; font-size: 1.3rem; }
    .checkout-btn {
        background: linear-gradient(135deg, #00c853 0%, #64dd17 100%); color: white;
        border: none; padding: 12px 40px; border-radius: 8px; font-weight: 700; font-size: 1rem; cursor: pointer;
    }
    
    .empty-cart { text-align: center; padding: 60px 20px; }
    .empty-cart i { font-size: 4rem; color: #333; margin-bottom: 20px; }
    
    .form-label { color: #888; font-size: 0.9rem; margin-bottom: 5px; }
    .form-control-dark {
        background: #222; border: 1px solid #333; border-radius: 8px;
        padding: 12px 15px; color: white; width: 100%; margin-bottom: 12px;
    }
    .form-control-dark:focus { outline: none; border-color: #ff6600; box-shadow: 0 0 0 3px rgba(255,102,0,0.1); }
    .form-control-dark::placeholder { color: #666; }
    textarea.form-control-dark { resize: vertical; min-height: 80px; }
    
    .payment-card {
        background: #222; border: 2px solid #333; border-radius: 10px;
        padding: 15px; margin-bottom: 12px; cursor: pointer; transition: 0.3s;
    }
    .payment-card:hover { border-color: #ff6600; }
    .payment-card.selected { border-color: #ff6600; background: #2a2a1a; }
    .payment-card .payment-info { display: flex; align-items: center; gap: 15px; }
    .payment-icon {
        width: 50px; height: 50px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;
    }
    .payment-icon.qris { background: linear-gradient(135deg, #6c5ce7, #a855f7); }
    .payment-icon.cod { background: linear-gradient(135deg, #00b894, #55efc4); }
    .payment-name { color: white; font-weight: 600; }
    .payment-desc { color: #888; font-size: 0.85rem; }
    
    .address-card {
        background: #222; border: 2px solid #333; border-radius: 10px;
        padding: 15px; margin-bottom: 12px; cursor: pointer; transition: 0.3s;
    }
    .address-card:hover { border-color: #ff6600; }
    .address-card.selected { border-color: #ff6600; background: #2a2a1a; }
    .address-name { color: white; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
    .address-phone { color: #aaa; font-size: 0.85rem; margin: 5px 0; }
    .address-detail { color: #ccc; font-size: 0.9rem; line-height: 1.4; }
    .address-city { color: #888; font-size: 0.85rem; margin-top: 5px; }
    .badge-default { background: #4a3800; color: #ffc107; font-size: 0.7rem; padding: 3px 8px; border-radius: 4px; }
    .btn-edit-addr { color: #ff6600; text-decoration: none; font-weight: 600; font-size: 0.85rem; }
    
    .qris-upload-area {
        border: 2px dashed #444; border-radius: 10px; padding: 30px;
        text-align: center; cursor: pointer; transition: 0.3s; margin-top: 10px;
    }
    .qris-upload-area:hover { border-color: #ff6600; background: #222; }
    .qris-upload-area i { font-size: 3rem; color: #666; margin-bottom: 10px; }
    .qris-upload-area p { color: #888; margin: 0; }
    .qris-preview { margin-top: 15px; }
    .qris-preview img { max-width: 200px; border-radius: 8px; border: 2px solid #ff6600; }
    
    .add-addr-btn {
        display: flex; align-items: center; gap: 10px; padding: 15px;
        border: 1px dashed #444; border-radius: 8px; color: #aaa;
        text-decoration: none; cursor: pointer; margin-bottom: 15px; background: #222; transition: 0.2s;
    }
    .add-addr-btn:hover { background: #2a2a2a; border-color: #ff6600; color: #ff6600; }
    
    .bottom-nav { 
        position: fixed; bottom: 0; width: 100%; background: #111; 
        border-top: 1px solid #222; z-index: 1000; padding: 10px 0;
    }
    .nav-item { 
        color: #666; text-decoration: none; text-align: center; font-size: 0.75rem; 
        display: flex; flex-direction: column; align-items: center; gap: 5px; 
    }
    .nav-item i { font-size: 1.5rem; }
    .nav-item.active { color: #ff6600; }

    /* === SCROLL SIMPLE - PASTI WORK === */
#checkoutModal .modal-dialog {
    margin: 0;
    height: 100vh;
    max-height: 100vh;
}

#checkoutModal .modal-content {
    height: 100vh;
    max-height: 100vh;
    border-radius: 0;
    border: none;
}

#checkoutModal .modal-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #111;
    flex-shrink: 0;
}

#checkoutModal .modal-body {
    height: calc(100vh - 60px);
    overflow-y: auto;
    overflow-x: hidden;
    padding: 20px;
    padding-bottom: 150px;
}

/* Custom Scrollbar */
#checkoutModal .modal-body::-webkit-scrollbar {
    width: 6px;
}
#checkoutModal .modal-body::-webkit-scrollbar-thumb {
    background: #ff6600;
    border-radius: 3px;
}
    
</style>
</head>
<body>

<!-- Header -->
<div class="page-header">
    <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
    <h3>Keranjang Belanja</h3>
</div>

<?php if(!empty($_SESSION['cart'])): ?>
    
    <?php 
    $grand_total = 0; 
    $total_items = 0;
    foreach($_SESSION['cart'] as $item): 
        $grand_total += ($item['price'] * $item['qty']);
        $total_items += $item['qty'];
    endforeach;
    ?>
    
    <!-- Cart Items -->
    <div class="cart-item">
        <div class="shop-header">
            <span class="shop-badge">Mall</span>
            <span class="shop-name">Texcer Hot</span>
        </div>
        
        <?php foreach($_SESSION['cart'] as $index => $item): 
            $subtotal = $item['price'] * $item['qty'];
        ?>
        <div class="product-row">
            <div class="product-img"><i class="fas fa-utensils"></i></div>
            <div class="product-info">
                <div class="product-name"><?= $item['name'] ?></div>
                <div class="variant-box">Variant: Default</div>
                <div class="price-section">
                    <span class="current-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                </div>
                <div class="qty-control">
                    <a href="?action=minus&index=<?= $index ?>" class="qty-btn"><i class="fas fa-minus"></i></a>
                    <span class="qty-value"><?= $item['qty'] ?></span>
                    <a href="?action=plus&index=<?= $index ?>" class="qty-btn"><i class="fas fa-plus"></i></a>
                </div>
            </div>
            <div style="text-align: right; min-width: 100px; display: flex; flex-direction: column; justify-content: space-between;">
                <span style="color: #ff6600; font-weight: 700;">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                <a href="?action=remove&index=<?= $index ?>" class="remove-btn"><i class="fas fa-trash"></i> Hapus</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Summary -->
    <div class="summary-section">
        <div class="section-title"><i class="fas fa-receipt"></i> Ringkasan Pesanan</div>
        <div class="summary-row">
            <span>Total Item (<?= $total_items ?>)</span>
            <span>Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
        </div>
        <div class="summary-row">
            <span>Ongkos Kirim</span>
            <span style="color: #00e5ff;">Gratis</span>
        </div>
        <div class="summary-row total">
            <span>Total Tagihan</span>
            <span class="amount">Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
        </div>
    </div>
    
    <!-- Bottom Bar -->
    <div class="bottom-bar">
        <div>
            <div class="total-label">Total</div>
            <div class="total-price">Rp <?= number_format($grand_total, 0, ',', '.') ?></div>
        </div>
        <button class="checkout-btn" data-bs-toggle="modal" data-bs-target="#checkoutModal">
            <i class="fas fa-credit-card me-2"></i>Checkout
        </button>
    </div>
    
    <!-- Checkout Modal -->
<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Checkout</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <!-- Body dengan ID khusus -->
            <div class="modal-body" id="modalScrollBody">
                <form method="POST" enctype="multipart/form-data">
                    
                    <!-- Alamat -->
                    <div class="section-title mt-0"><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</div>
                    <a href="alamat.php" class="add-addr-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Kelola Alamat</span>
                        <i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    
                    <?php if(!empty($addresses)): ?>
                        <?php foreach($addresses as $i => $addr): ?>
                        <div class="address-card <?= $addr['is_default'] ? 'selected' : '' ?>" onclick="selectAddress(<?= $i ?>)">
                            <a href="alamat.php?edit=<?= $addr['id'] ?>" class="btn-edit-addr">Edit</a>
                            <div class="address-name"><?= htmlspecialchars($addr['customer_name']) ?></div>
                            <div class="address-phone">(+62)<?= substr($addr['customer_phone'], -10) ?></div>
                            <div class="address-detail"><?= nl2br(htmlspecialchars($addr['full_address'])) ?></div>
                            <div class="address-city"><?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['province']) ?></div>
                            <?php if($addr['is_default']): ?><span class="badge-default">Default</span><?php endif; ?>
                            <input type="hidden" name="cust_name_<?= $i ?>" value="<?= htmlspecialchars($addr['customer_name']) ?>">
                            <input type="hidden" name="cust_phone_<?= $i ?>" value="<?= htmlspecialchars($addr['customer_phone']) ?>">
                            <input type="hidden" name="cust_address_<?= $i ?>" value="<?= htmlspecialchars($addr['full_address']) ?>">
                            <input type="hidden" name="cust_city_<?= $i ?>" value="<?= htmlspecialchars($addr['city']) ?>">
                            <input type="hidden" name="cust_province_<?= $i ?>" value="<?= htmlspecialchars($addr['province']) ?>">
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: #888; margin: 15px 0;">
                            <i class="fas fa-map-marker-alt mb-2 d-block" style="font-size: 2rem;"></i>
                            Belum ada alamat. <a href="alamat.php" style="color: #ff6600;">Tambahkan sekarang</a>
                        </div>
                    <?php endif; ?>
                    
                    <input type="hidden" name="cust_name" id="final_name">
                    <input type="hidden" name="cust_phone" id="final_phone">
                    <input type="hidden" name="cust_address" id="final_address">
                    <input type="hidden" name="cust_city" id="final_city">
                    <input type="hidden" name="cust_province" id="final_province">
                    
                    <!-- Pembayaran -->
                    <div class="section-title"><i class="fas fa-wallet"></i> Metode Pembayaran</div>
                    <div class="payment-card selected" onclick="selectPayment('cod', this)">
                        <div class="payment-info">
                            <div class="payment-icon cod"><i class="fas fa-money-bill-wave"></i></div>
                            <div>
                                <div class="payment-name">COD (Bayar di Tempat)</div>
                                <div class="payment-desc">Bayar saat pesanan diterima</div>
                            </div>
                            <input type="radio" name="payment_method" value="COD" checked style="margin-left: auto;">
                        </div>
                    </div>
                    <div class="payment-card" onclick="selectPayment('qris', this)">
                        <div class="payment-info">
                            <div class="payment-icon qris"><i class="fas fa-qrcode"></i></div>
                            <div>
                                <div class="payment-name">QRIS</div>
                                <div class="payment-desc">Scan QR Code untuk bayar</div>
                            </div>
                            <input type="radio" name="payment_method" value="QRIS" style="margin-left: auto;">
                        </div>
                        <?php if(isset($_SESSION['qris_image'])): ?>
                        <div class="qris-preview text-center mt-3">
                            <img src="<?= $_SESSION['qris_image'] ?>" alt="QRIS Code">
                            <p class="text-secondary mt-2" style="font-size: 0.85rem;">Scan kode di atas</p>
                        </div>
                        <?php else: ?>
                        <div class="qris-upload-area" onclick="document.getElementById('qrisInput').click()">
                            <i class="fas fa-cloud-upload-alt d-block"></i>
                            <p>Klik upload QRIS</p>
                            <input type="file" id="qrisInput" name="qris_image" accept="image/*" style="display: none;" onchange="this.form.submit()">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Catatan -->
                    <div class="section-title"><i class="fas fa-sticky-note"></i> Catatan</div>
                    <textarea name="notes" class="form-control-dark" placeholder="Catatan (opsional)"></textarea>
                    
                    <!-- Total -->
                    <div class="section-title"><i class="fas fa-receipt"></i> Total</div>
                    <div style="background: #222; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: #888;">Total Item</span>
                            <span>Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span style="color: #888;">Ongkir</span>
                            <span style="color: #00e5ff;">Gratis</span>
                        </div>
                        <hr style="border-color: #333;">
                        <div class="d-flex justify-content-between">
                            <strong style="color: white;">Total Bayar</strong>
                            <strong style="color: #ff6600; font-size: 1.3rem;">Rp <?= number_format($grand_total, 0, ',', '.') ?></strong>
                        </div>
                    </div>
                    
                    <!-- Tombol Submit (Sticky Bottom) -->
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
    
    <script>
        // Pilih Alamat
        function selectAddress(index) {
            document.querySelectorAll('.address-card').forEach(el => el.classList.remove('selected'));
            document.querySelectorAll('.address-card')[index].classList.add('selected');
            
            document.getElementById('final_name').value = document.querySelector(`input[name="cust_name_${index}"]`).value;
            document.getElementById('final_phone').value = document.querySelector(`input[name="cust_phone_${index}"]`).value;
            document.getElementById('final_address').value = document.querySelector(`input[name="cust_address_${index}"]`).value;
            document.getElementById('final_city').value = document.querySelector(`input[name="cust_city_${index}"]`).value;
            document.getElementById('final_province').value = document.querySelector(`input[name="cust_province_${index}"]`).value;
        }
        
        // Pilih Pembayaran
        function selectPayment(method, element) {
            document.querySelectorAll('.payment-card').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            element.querySelector('input[type="radio"]').checked = true;
        }
        
        // Auto select default address
        document.addEventListener('DOMContentLoaded', () => {
            const defaultAddr = document.querySelector('.address-card.selected');
            if(defaultAddr) {
                const idx = Array.from(document.querySelectorAll('.address-card')).indexOf(defaultAddr);
                selectAddress(idx);
            }
        });
    </script>

<?php else: ?>
    <div class="empty-cart">
        <i class="fas fa-shopping-cart"></i>
        <h4>Keranjang Kosong</h4>
        <p class="text-secondary">Yuk, pesan makanan favoritmu sekarang!</p>
        <a href="index.php" class="btn btn-warning mt-3" style="background: #ff6600; border: none; padding: 12px 40px; font-weight: 600; color: white; text-decoration: none; border-radius: 8px;">
            Mulai Pesan
        </a>
    </div>
<?php endif; ?>

<!-- Bottom Navigation -->
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<div class="bottom-nav d-flex justify-content-around">
    <a href="index.php" class="nav-item <?= $currentPage == 'index.php' ? 'active' : '' ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="checkout.php" class="nav-item <?= $currentPage == 'checkout.php' ? 'active' : '' ?>">
        <i class="fas fa-shopping-cart"></i>
        <span>Cart</span>
    </a>
    <a href="riwayat.php" class="nav-item <?= $currentPage == 'riwayat.php' ? 'active' : '' ?>">
        <i class="fas fa-history"></i>
        <span>Order</span>
    </a>
    <a href="admin.php" class="nav-item <?= $currentPage == 'admin.php' ? 'active' : '' ?>">
        <i class="fas fa-user-shield"></i>
        <span>Profile</span>
    </a>
</div>
<script>
// FIX SCROLL MODAL - PASTI WORK
document.getElementById('checkoutModal').addEventListener('shown.bs.modal', function () {
    const modalBody = document.getElementById('modalScrollBody');
    if (modalBody) {
        // Reset scroll position
        modalBody.scrollTop = 0;
        // Force reflow untuk aktifkan scroll
        modalBody.style.overflowY = 'hidden';
        setTimeout(() => {
            modalBody.style.overflowY = 'auto';
        }, 10);
    }
});

// Lock body scroll saat modal terbuka
document.getElementById('checkoutModal').addEventListener('show.bs.modal', function () {
    document.body.style.overflow = 'hidden';
});
document.getElementById('checkoutModal').addEventListener('hidden.bs.modal', function () {
    document.body.style.overflow = '';
});

// Fungsi select address & payment
function selectAddress(index) {
    document.querySelectorAll('.address-card').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.address-card')[index]?.classList.add('selected');
    document.getElementById('final_name').value = document.querySelector(`input[name="cust_name_${index}"]`)?.value || '';
    document.getElementById('final_phone').value = document.querySelector(`input[name="cust_phone_${index}"]`)?.value || '';
    document.getElementById('final_address').value = document.querySelector(`input[name="cust_address_${index}"]`)?.value || '';
    document.getElementById('final_city').value = document.querySelector(`input[name="cust_city_${index}"]`)?.value || '';
    document.getElementById('final_province').value = document.querySelector(`input[name="cust_province_${index}"]`)?.value || '';
}

function selectPayment(method, element) {
    document.querySelectorAll('.payment-card').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;
}

document.addEventListener('DOMContentLoaded', () => {
    const defaultAddr = document.querySelector('.address-card.selected');
    if(defaultAddr) {
        const idx = Array.from(document.querySelectorAll('.address-card')).indexOf(defaultAddr);
        selectAddress(idx);
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>