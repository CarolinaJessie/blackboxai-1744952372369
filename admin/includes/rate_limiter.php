<?php
/**
 * Rate Limiter Class
 * 
 * This class provides rate limiting functionality to prevent brute force attacks
 * and DDoS attempts. It uses file-based storage for simplicity, but can be
 * extended to use Redis, Memcached, or a database for better performance.
 */

class RateLimiter {
    private $storage_dir;
    private $window_size;
    private $max_requests;
    private $identifier;
    
    /**
     * Constructor
     * 
     * @param string $identifier Unique identifier for the rate limit (e.g., 'login', 'api')
     * @param int $max_requests Maximum number of requests allowed in the time window
     * @param int $window_size Time window in seconds
     * @param string $storage_dir Directory to store rate limit data
     */
    public function __construct($identifier = 'default', $max_requests = 10, $window_size = 60, $storage_dir = null) {
        $this->identifier = $identifier;
        $this->max_requests = $max_requests;
        $this->window_size = $window_size;
        
        // Set storage directory
        if ($storage_dir === null) {
            $this->storage_dir = __DIR__ . '/../logs/rate_limits';
        } else {
            $this->storage_dir = $storage_dir;
        }
        
        // Create storage directory if it doesn't exist
        if (!file_exists($this->storage_dir)) {
            mkdir($this->storage_dir, 0755, true);
        }
    }
    
    /**
     * Check if the current request exceeds the rate limit
     * 
     * @param string $key Unique key for the rate limit (e.g., IP address, user ID)
     * @return bool True if the request is allowed, false if it exceeds the limit
     */
    public function check($key) {
        $file_path = $this->getFilePath($key);
        $current_time = time();
        $requests = $this->getRequests($file_path);
        
        // Remove requests outside the time window
        $requests = array_filter($requests, function($timestamp) use ($current_time) {
            return $timestamp >= ($current_time - $this->window_size);
        });
        
        // Check if the number of requests exceeds the limit
        if (count($requests) >= $this->max_requests) {
            $this->logExcess($key);
            return false;
        }
        
        // Add the current request
        $requests[] = $current_time;
        $this->saveRequests($file_path, $requests);
        
        return true;
    }
    
    /**
     * Get the remaining number of requests allowed
     * 
     * @param string $key Unique key for the rate limit
     * @return int Number of requests remaining
     */
    public function getRemainingRequests($key) {
        $file_path = $this->getFilePath($key);
        $current_time = time();
        $requests = $this->getRequests($file_path);
        
        // Remove requests outside the time window
        $requests = array_filter($requests, function($timestamp) use ($current_time) {
            return $timestamp >= ($current_time - $this->window_size);
        });
        
        return max(0, $this->max_requests - count($requests));
    }
    
    /**
     * Get the time remaining until the rate limit resets
     * 
     * @param string $key Unique key for the rate limit
     * @return int Time in seconds until the rate limit resets
     */
    public function getTimeUntilReset($key) {
        $file_path = $this->getFilePath($key);
        $current_time = time();
        $requests = $this->getRequests($file_path);
        
        if (empty($requests)) {
            return 0;
        }
        
        // Find the oldest request within the time window
        $oldest_request = min($requests);
        
        // Calculate the time until the oldest request is outside the time window
        return max(0, ($oldest_request + $this->window_size) - $current_time);
    }
    
    /**
     * Reset the rate limit for a specific key
     * 
     * @param string $key Unique key for the rate limit
     * @return bool True if the rate limit was reset, false otherwise
     */
    public function reset($key) {
        $file_path = $this->getFilePath($key);
        
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        
        return true;
    }
    
    /**
     * Get the file path for a specific key
     * 
     * @param string $key Unique key for the rate limit
     * @return string File path
     */
    private function getFilePath($key) {
        // Sanitize the key to prevent directory traversal
        $sanitized_key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        return $this->storage_dir . '/' . $this->identifier . '_' . $sanitized_key . '.json';
    }
    
    /**
     * Get the requests for a specific key
     * 
     * @param string $file_path File path for the key
     * @return array Array of request timestamps
     */
    private function getRequests($file_path) {
        if (!file_exists($file_path)) {
            return [];
        }
        
        $data = file_get_contents($file_path);
        return json_decode($data, true) ?: [];
    }
    
    /**
     * Save the requests for a specific key
     * 
     * @param string $file_path File path for the key
     * @param array $requests Array of request timestamps
     * @return bool True if the requests were saved, false otherwise
     */
    private function saveRequests($file_path, $requests) {
        return file_put_contents($file_path, json_encode($requests), LOCK_EX) !== false;
    }
    
    /**
     * Log excessive requests
     * 
     * @param string $key Unique key for the rate limit
     */
    private function logExcess($key) {
        $log_message = sprintf(
            "[%s] Rate limit exceeded for %s (identifier: %s, key: %s, limit: %d requests per %d seconds)",
            date('Y-m-d H:i:s'),
            $_SERVER['REMOTE_ADDR'],
            $this->identifier,
            $key,
            $this->max_requests,
            $this->window_size
        );
        
        error_log($log_message, 3, __DIR__ . '/../logs/rate_limit_excess.log');
    }
}
