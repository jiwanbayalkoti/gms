<?php

if (!function_exists('imageExists')) {
    /**
     * Check if an image file exists in storage
     * 
     * @param string|null $path The storage path (e.g., 'profile-photos/image.jpg')
     * @return bool
     */
    function imageExists(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        try {
            $fullPath = storage_path('app/public/' . $path);
            return file_exists($fullPath) && is_file($fullPath);
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('getImageUrl')) {
    /**
     * Get the URL for an image if it exists, otherwise return null
     * 
     * @param string|null $path The storage path
     * @return string|null
     */
    function getImageUrl(?string $path): ?string
    {
        if (imageExists($path)) {
            return asset('storage/' . $path);
        }
        
        return null;
    }
}

