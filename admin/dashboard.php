<?php
/**
 * Admin Dashboard Page
 * 
 * This file displays the admin dashboard and provides navigation to other admin features.
 */

// Include configuration file
require_once 'config.php';

// Check if user is logged in, redirect to login page if not
require_login();

// Get admin username
$admin_username = $_SESSION["admin_username"] ?? "Admin";

// Get product count from database
$product_count = 0;
$sql = "SELECT COUNT(*) as count FROM products";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $product_count = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - E-Sim Store</title>
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
        
        .admin-actions .btn {
            margin-left: 0.5rem;
        }
        
        /* Dashboard cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .dashboard-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--admin-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        .dashboard-card-icon i {
            font-size: 1.5rem;
        }
        
        .dashboard-card-content h3 {
            margin-bottom: 0.3rem;
            font-size: 1.8rem;
        }
        
        .dashboard-card-content p {
            color: var(--text-light);
            margin: 0;
        }
        
        /* Quick actions */
        .quick-actions {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .quick-actions h2 {
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .quick-action-item {
            background-color: var(--admin-secondary);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
            color: var(--text-color);
        }
        
        .quick-action-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow);
        }
        
        .quick-action-item i {
            font-size: 2rem;
            color: var(--admin-primary);
            margin-bottom: 1rem;
            display: block;
        }
        
        .quick-action-item h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        
        .quick-action-item p {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        /* Recent activity */
        .recent-activity {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
        }
        
        .recent-activity h2 {
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--admin-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-content h3 {
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }
        
        .activity-content p {
            font-size: 0.9rem;
            color: var(--text-light);
            margin: 0;
        }
        
        .activity-time {
            font-size: 0.8rem;
            color: var(--text-light);
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
            <a href="dashboard.php" class="admin-menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="add_product.php" class="admin-menu-item">
                <i class="fas fa-plus-circle"></i>
                <span>Tambah Produk</span>
            </a>
            <a href="manage_products.php" class="admin-menu-item">
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
                <h1>Dashboard</h1>
                <p>Selamat datang, <?php echo htmlspecialchars($admin_username); ?>!</p>
            </div>
            <div class="admin-actions">
                <a href="add_product.php" class="btn">
                    <i class="fas fa-plus"></i> Tambah Produk
                </a>
            </div>
        </div>
        
        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $product_count; ?></h3>
                    <p>Total Produk</p>
                </div>
            </div>
            
            <div class="dashboard-card">
                <div class="dashboard-card-icon" style="background-color: var(--admin-success);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3>0</h3>
                    <p>Pesanan</p>
                </div>
            </div>
            
            <div class="dashboard-card">
                <div class="dashboard-card-icon" style="background-color: var(--admin-warning);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3>0</h3>
                    <p>Pengunjung Hari Ini</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Aksi Cepat</h2>
            <div class="quick-actions-grid">
                <a href="add_product.php" class="quick-action-item">
                    <i class="fas fa-plus-circle"></i>
                    <h3>Tambah Produk</h3>
                    <p>Tambahkan produk baru ke toko</p>
                </a>
                
                <a href="manage_products.php" class="quick-action-item">
                    <i class="fas fa-edit"></i>
                    <h3>Kelola Produk</h3>
                    <p>Edit atau hapus produk yang ada</p>
                </a>
                
                <a href="../index.html" class="quick-action-item" target="_blank">
                    <i class="fas fa-globe"></i>
                    <h3>Lihat Website</h3>
                    <p>Buka halaman utama website</p>
                </a>
                
                <a href="logout.php" class="quick-action-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <h3>Logout</h3>
                    <p>Keluar dari admin panel</p>
                </a>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="recent-activity">
            <h2>Aktivitas Terbaru</h2>
            
            <div class="activity-item">
                <div class="activity-icon" style="background-color: var(--admin-success);">
                    <i class="fas fa-user"></i>
                </div>
                <div class="activity-content">
                    <h3>Login Admin</h3>
                    <p>Admin berhasil login ke sistem</p>
                    <span class="activity-time"><?php echo date('d M Y, H:i'); ?></span>
                </div>
            </div>
            
            <!-- More activity items would be dynamically generated here -->
            <div class="activity-item" style="text-align: center; padding: 2rem 0;">
                <p>Belum ada aktivitas lain yang tercatat.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Add any dashboard-specific JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Highlight current menu item
            const currentLocation = window.location.pathname;
            const menuItems = document.querySelectorAll('.admin-menu-item');
            
            menuItems.forEach(item => {
                if (item.getAttribute('href') === currentLocation.split('/').pop()) {
                    item.classList.add('active');
                } else if (item !== menuItems[0]) {
                    item.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
