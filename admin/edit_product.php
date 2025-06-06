<?php
/**
 * Edit Product Page
 * 
 * This file handles the form for editing existing products.
 */

// Include configuration file
require_once 'config.php';

// Check if user is logged in, redirect to login page if not
require_login();

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_products.php");
    exit;
}

$id = intval($_GET['id']);

// Initialize variables
$name = $stock = $price = $description = $image_path = "";
$name_err = $stock_err = $price_err = $description_err = $image_err = "";
$success_message = "";

// Get product data from database
$sql = "SELECT name, stock, price, description, image_path FROM products WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($name, $stock, $price, $description, $image_path);
            $stmt->fetch();
        } else {
            // Product not found
            header("Location: manage_products.php");
            exit;
        }
    } else {
        echo "Terjadi kesalahan. Silakan coba lagi nanti.";
        exit;
    }
    
    $stmt->close();
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }
    
    // Validate product name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Silakan masukkan nama produk.";
    } else {
        $name = sanitize_input($_POST["name"]);
    }
    
    // Validate stock
    if (empty(trim($_POST["stock"]))) {
        $stock_err = "Silakan masukkan jumlah stock.";
    } elseif (!is_numeric($_POST["stock"]) || intval($_POST["stock"]) < 0) {
        $stock_err = "Stock harus berupa angka positif.";
    } else {
        $stock = intval($_POST["stock"]);
    }
    
    // Validate price
    if (empty(trim($_POST["price"]))) {
        $price_err = "Silakan masukkan harga produk.";
    } elseif (!is_numeric($_POST["price"]) || floatval($_POST["price"]) <= 0) {
        $price_err = "Harga harus berupa angka positif.";
    } else {
        $price = floatval($_POST["price"]);
    }
    
    // Validate description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Silakan masukkan deskripsi produk.";
    } else {
        $description = sanitize_input($_POST["description"]);
    }
    
    // Check if a new image is uploaded
    $new_image_path = $image_path; // Default to current image path
    
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] != 4) { // 4 means no file was uploaded
        $allowed_types = ["image/jpeg", "image/jpg", "image/png", "image/gif"];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Check file type
        if (!in_array($_FILES["image"]["type"], $allowed_types)) {
            $image_err = "Hanya file gambar (JPG, PNG, GIF) yang diperbolehkan.";
        }
        // Check file size
        elseif ($_FILES["image"]["size"] > $max_size) {
            $image_err = "Ukuran file tidak boleh lebih dari 5MB.";
        } else {
            // Generate unique filename
            $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_dir = "uploads/";
            $upload_path = $upload_dir . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_path)) {
                // Delete old image if it exists and is not the default image
                if (!empty($image_path) && file_exists($image_path) && strpos($image_path, 'default') === false) {
                    unlink($image_path);
                }
                
                $new_image_path = $upload_path;
            } else {
                $image_err = "Terjadi kesalahan saat mengunggah file.";
            }
        }
    }
    
    // Check input errors before updating database
    if (empty($name_err) && empty($stock_err) && empty($price_err) && empty($description_err) && empty($image_err)) {
        // Prepare an update statement
        $sql = "UPDATE products SET name = ?, stock = ?, price = ?, description = ?, image_path = ? WHERE id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sidssi", $param_name, $param_stock, $param_price, $param_description, $param_image_path, $param_id);
            
            // Set parameters
            $param_name = $name;
            $param_stock = $stock;
            $param_price = $price;
            $param_description = $description;
            $param_image_path = $new_image_path;
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Product updated successfully
                $success_message = "Produk berhasil diperbarui!";
                
                // Refresh product data
                $image_path = $new_image_path;
            } else {
                $error_message = "Terjadi kesalahan. Silakan coba lagi nanti.";
            }
            
            // Close statement
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - E-Sim Store</title>
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
        
        /* Form styles */
        .form-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--admin-primary);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-group .error-message {
            color: var(--admin-danger);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn-secondary {
            background-color: var(--admin-light);
            color: var(--admin-dark);
        }
        
        .btn-secondary:hover {
            background-color: #ddd;
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
        
        .image-preview {
            max-width: 300px;
            max-height: 200px;
            margin-top: 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
        }
        
        .current-image-container {
            margin-top: 1rem;
        }
        
        .current-image-container p {
            margin-bottom: 0.5rem;
            font-weight: 500;
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
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
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
                <h1>Edit Produk</h1>
                <p>Perbarui informasi produk</p>
            </div>
            <div class="admin-actions">
                <a href="manage_products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        
        <!-- Form Container -->
        <div class="form-container">
            <?php if (!empty($success_message)) : ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="form-group">
                    <label for="name">Nama Produk</label>
                    <input type="text" id="name" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
                    <span class="error-message"><?php echo $name_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock Produk</label>
                    <input type="number" id="stock" name="stock" class="form-control <?php echo (!empty($stock_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($stock); ?>" min="0">
                    <span class="error-message"><?php echo $stock_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="price">Harga Produk (Rp)</label>
                    <input type="number" id="price" name="price" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($price); ?>" min="0" step="1000">
                    <span class="error-message"><?php echo $price_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="description">Deskripsi Produk</label>
                    <textarea id="description" name="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($description); ?></textarea>
                    <span class="error-message"><?php echo $description_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="image">Gambar Produk</label>
                    
                    <?php if (!empty($image_path)) : ?>
                        <div class="current-image-container">
                            <p>Gambar Saat Ini:</p>
                            <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Current Product Image" class="image-preview">
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" id="image" name="image" class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>" accept="image/*" style="margin-top: 1rem;">
                    <span class="error-message"><?php echo $image_err; ?></span>
                    <img id="imagePreview" class="image-preview" src="#" alt="Preview Gambar Baru" style="display: none;">
                    <p class="help-text" style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--text-light);">
                        Biarkan kosong jika tidak ingin mengubah gambar. Format yang didukung: JPG, PNG, GIF. Ukuran maksimal: 5MB.
                    </p>
                </div>
                
                <div class="form-actions">
                    <a href="manage_products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Image preview for new uploads
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('image');
            const imagePreview = document.getElementById('imagePreview');
            
            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                } else {
                    imagePreview.style.display = 'none';
                }
            });
            
            // Form validation
            const productForm = document.getElementById('productForm');
            
            productForm.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validate name
                const name = document.getElementById('name').value.trim();
                if (name === '') {
                    document.getElementById('name').nextElementSibling.textContent = 'Silakan masukkan nama produk.';
                    isValid = false;
                } else {
                    document.getElementById('name').nextElementSibling.textContent = '';
                }
                
                // Validate stock
                const stock = document.getElementById('stock').value.trim();
                if (stock === '') {
                    document.getElementById('stock').nextElementSibling.textContent = 'Silakan masukkan jumlah stock.';
                    isValid = false;
                } else if (isNaN(stock) || parseInt(stock) < 0) {
                    document.getElementById('stock').nextElementSibling.textContent = 'Stock harus berupa angka positif.';
                    isValid = false;
                } else {
                    document.getElementById('stock').nextElementSibling.textContent = '';
                }
                
                // Validate price
                const price = document.getElementById('price').value.trim();
                if (price === '') {
                    document.getElementById('price').nextElementSibling.textContent = 'Silakan masukkan harga produk.';
                    isValid = false;
                } else if (isNaN(price) || parseFloat(price) <= 0) {
                    document.getElementById('price').nextElementSibling.textContent = 'Harga harus berupa angka positif.';
                    isValid = false;
                } else {
                    document.getElementById('price').nextElementSibling.textContent = '';
                }
                
                // Validate description
                const description = document.getElementById('description').value.trim();
                if (description === '') {
                    document.getElementById('description').nextElementSibling.textContent = 'Silakan masukkan deskripsi produk.';
                    isValid = false;
                } else {
                    document.getElementById('description').nextElementSibling.textContent = '';
                }
                
                // Validate image (only if a new image is selected)
                const image = document.getElementById('image');
                if (image.files.length > 0) {
                    const file = image.files[0];
                    const fileType = file.type;
                    const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    
                    if (!validImageTypes.includes(fileType)) {
                        document.getElementById('image').nextElementSibling.textContent = 'Hanya file gambar (JPG, PNG, GIF) yang diperbolehkan.';
                        isValid = false;
                    } else if (file.size > 5 * 1024 * 1024) { // 5MB
                        document.getElementById('image').nextElementSibling.textContent = 'Ukuran file tidak boleh lebih dari 5MB.';
                        isValid = false;
                    } else {
                        document.getElementById('image').nextElementSibling.textContent = '';
                    }
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
