<?php
/**
 * Process Add Product Script
 * 
 * This file handles the processing of product form submissions.
 * It's used as an alternative to direct form processing in add_product.php
 * for cases where AJAX submissions might be preferred.
 */

// Include configuration file
require_once 'config.php';

// Check if user is logged in, redirect to login page if not
require_login();

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $response['message'] = "CSRF token validation failed";
        echo json_encode($response);
        exit;
    }
    
    // Initialize variables
    $name = $stock = $price = $description = "";
    $name_err = $stock_err = $price_err = $description_err = $image_err = "";
    
    // Validate product name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Silakan masukkan nama produk.";
        $response['errors']['name'] = $name_err;
    } else {
        $name = sanitize_input($_POST["name"]);
    }
    
    // Validate stock
    if (empty(trim($_POST["stock"]))) {
        $stock_err = "Silakan masukkan jumlah stock.";
        $response['errors']['stock'] = $stock_err;
    } elseif (!is_numeric($_POST["stock"]) || intval($_POST["stock"]) < 0) {
        $stock_err = "Stock harus berupa angka positif.";
        $response['errors']['stock'] = $stock_err;
    } else {
        $stock = intval($_POST["stock"]);
    }
    
    // Validate price
    if (empty(trim($_POST["price"]))) {
        $price_err = "Silakan masukkan harga produk.";
        $response['errors']['price'] = $price_err;
    } elseif (!is_numeric($_POST["price"]) || floatval($_POST["price"]) <= 0) {
        $price_err = "Harga harus berupa angka positif.";
        $response['errors']['price'] = $price_err;
    } else {
        $price = floatval($_POST["price"]);
    }
    
    // Validate description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Silakan masukkan deskripsi produk.";
        $response['errors']['description'] = $description_err;
    } else {
        $description = sanitize_input($_POST["description"]);
    }
    
    // Validate and process image upload using secure_file_upload function
    $image_path = "";
    if (isset($_FILES["image"])) {
        $upload_result = secure_file_upload($_FILES["image"]);
        
        if ($upload_result['success']) {
            $image_path = $upload_result['file_path'];
        } else {
            $image_err = $upload_result['message'];
            $response['errors']['image'] = $image_err;
        }
    } else {
        $image_err = "Silakan pilih gambar produk.";
        $response['errors']['image'] = $image_err;
    }
    
    // Check input errors before inserting into database
    if (empty($name_err) && empty($stock_err) && empty($price_err) && empty($description_err) && empty($image_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO products (name, stock, price, description, image_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sidss", $param_name, $param_stock, $param_price, $param_description, $param_image_path);
            
            // Set parameters
            $param_name = $name;
            $param_stock = $stock;
            $param_price = $price;
            $param_description = $description;
            $param_image_path = $image_path;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Product added successfully
                $response['success'] = true;
                $response['message'] = "Produk berhasil ditambahkan!";
                
                // Clear form data
                $name = $stock = $price = $description = "";
            } else {
                $response['message'] = "Terjadi kesalahan. Silakan coba lagi nanti.";
            }
            
            // Close statement
            $stmt->close();
        }
    } else {
        $response['message'] = "Ada kesalahan dalam data yang dimasukkan. Silakan periksa kembali.";
    }
} else {
    $response['message'] = "Metode request tidak valid.";
}

// Return JSON response for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// For non-AJAX requests, redirect with appropriate message
if ($response['success']) {
    $_SESSION['success_message'] = $response['message'];
    header("Location: manage_products.php");
} else {
    $_SESSION['error_message'] = $response['message'];
    header("Location: add_product.php");
}
exit;
