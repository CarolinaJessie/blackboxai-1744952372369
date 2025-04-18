<?php
/**
 * Manage Products Page
 * 
 * This file displays all products and provides options to edit or delete them.
 */

// Include configuration file
require_once 'config.php';

// Check if user is logged in, redirect to login page if not
require_login();

// Initialize variables
$success_message = $error_message = "";

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get image path before deleting the product
    $sql = "SELECT image_path FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($image_path);
    $stmt->fetch();
    $stmt->close();
    
    // Delete product from database
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete image file if it exists
        if (!empty($image_path) && file_exists($image_path)) {
            unlink($image_path);
        }
        
        $success_message = "Produk berhasil dihapus.";
    } else {
        $error_message = "Terjadi kesalahan saat menghapus produk.";
    }
    
    $stmt->close();
}

// Get all products from database
$products = [];
$sql = "SELECT id, name, stock, price, description, image_path, created_at FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - E-Sim Store</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Admin panel specific styles */
        :root {
            --admin-primary: #4a6fff;
            --admin-secondary: #f0f4ff;
            --admin-accent: #ff6b6b;
            --admin-dark: #2c3e50;
            --admin-light: #ecf0f1;
            --admin-success: #2ecc71;
            --admin-warning: #f39c12;
            --admin-danger: #e74c3c;
        }
        
        body {
            background-color: var(--admin-secondary);
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar styles */
        .admin-sidebar {
            width: 250px;
            background-color: var(--admin-dark);
            color: white;
            padding: 1.5rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .admin-sidebar-header h2 {
            margin-bottom: 0.5rem;
        }
        
        .admin-sidebar-header p {
            opacity: 0.7;
            font-size: 0.9rem;
        }
        
        .admin-menu {
            padding: 1.5rem 0;
        }
        
        .admin-menu-item {
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .admin-menu-item:hover, .admin-menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .admin-menu-item i {
            margin-right: 0.8rem;
            width: 20px;
            text-align: center;
        }
        
        .admin-logout {
            margin-top: 2rem;
            padding: 0 1.5rem;
        }
        
        .admin-logout .btn {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            width: 100%;
        }
        
        .admin-logout .btn:hover {
            background-color: var(--admin-accent);
        }
        
        /* Main content styles */
        .admin-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }
        
        .admin-title h1 {
            margin-bottom: 0.5rem;
        }
        
        .admin-title p {
            color: var(--text-light);
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            border: 1px solid var(--admin-success);
            color: var(--admin-success);
        }
        
        .alert-danger {
            background-color: rgba(231, 76, 60, 0.1);
            border: 1px solid var(--admin-danger);
            color: var(--admin-danger);
        }
        
        /* Product table styles */
        .product-table-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            overflow-x: auto;
        }
        
        .product-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .product-table th, .product-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .product-table th {
            background-color: var(--admin-secondary);
            font-weight: 600;
        }
        
        .product-table tr:hover {
            background-color: var(--admin-secondary);
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--border-radius);
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }
        
        .btn-edit {
            background-color: var(--admin-warning);
        }
        
        .btn-delete {
            background-color: var(--admin-danger);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            margin-bottom: 1rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .admin-sidebar {
                width: 70px;
                overflow: visible;
            }
            
            .admin-sidebar-header h2, .admin-sidebar-header p, .admin-menu-item span {
                display: none;
            }
            
            .admin-menu-item i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .admin-content {
                margin-left: 70px;
            }
            
            .admin-logout {
                padding: 0 0.5rem;
            }
            
            .admin-logout .btn {
                padding: 0.5rem;
            }
            
            .admin-logout .btn i {
                margin: 0;
            }
            
            .admin-logout .btn span {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 1rem 0;
            }
            
            .admin-sidebar-header {
                padding: 0 1rem 1rem;
            }
            
            .admin-sidebar-header h2, .admin-sidebar-header p {
                display: block;
            }
            
            .admin-menu {
                display: flex;
                padding: 0;
                overflow-x: auto;
            }
            
            .admin-menu-item {
                padding: 0.8rem 1rem;
            }
            
            .admin-menu-item span {
                display: none;
            }
            
            .admin-logout {
                display: none;
            }
            
            .admin-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .product-table th:nth-child(3), 
            .product-table td:nth-child(3),
            .product-table th:nth-child(4), 
            .product-table td:nth-child(4) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h2>E-Sim Store</h2>
            <p>Admin Panel</p>
        </div>
        
        <div class="admin-menu">
            <a href="dashboard.php" class="admin-menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="add_product.php" class="admin-menu-item">
                <i class="fas fa-plus-circle"></i>
                <span>Tambah Produk</span>
            </a>
            <a href="manage_products.php" class="admin-menu-item active">
                <i class="fas fa-box"></i>
                <span>Kelola Produk</span>
            </a>
            <a href="../index.html" class="admin-menu-item" target="_blank">
                <i class="fas fa-globe"></i>
                <span>Lihat Website</span>
            </a>
        </div>
        
        <div class="admin-logout">
            <a href="logout.php" class="btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="admin-content">
        <div class="admin-header">
            <div class="admin-title">
                <h1>Kelola Produk</h1>
                <p>Lihat, edit, dan hapus produk yang ada</p>
            </div>
            <div class="admin-actions">
                <a href="add_product.php" class="btn">
                    <i class="fas fa-plus"></i> Tambah Produk
                </a>
            </div>
        </div>
        
        <?php if (!empty($success_message)) : ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Product Table -->
        <div class="product-table-container">
            <?php if (count($products) > 0) : ?>
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Stock</th>
                            <th>Harga</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product) : ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                <td><?php echo date('d M Y', strtotime($product['created_at'])); ?></td>
                                <td>
                                    <div class="product-actions">
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="#" class="btn btn-sm btn-delete" onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>Belum ada produk</h3>
                    <p>Anda belum menambahkan produk apapun. Klik tombol di bawah untuk menambahkan produk pertama Anda.</p>
                    <a href="add_product.php" class="btn" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Confirm delete function
        function confirmDelete(id, name) {
            if (confirm(`Apakah Anda yakin ingin menghapus produk "${name}"?`)) {
                window.location.href = `manage_products.php?delete=${id}`;
            }
        }
        
        // Highlight current menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.pathname;
            const menuItems = document.querySelectorAll('.admin-menu-item');
            
            menuItems.forEach(item => {
                if (item.getAttribute('href') === currentLocation.split('/').pop()) {
                    item.classList.add('active');
                } else if (item !== menuItems[2]) { // Keep "Kelola Produk" active
                    item.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
