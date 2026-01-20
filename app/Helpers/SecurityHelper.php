<?php

if (!function_exists('sanitize_input')) {
    /**
     * Sanitize user input to prevent XSS attacks.
     * 
     * @param mixed $input
     * @param bool $allowHtml Whether to allow HTML (default: false)
     * @return mixed
     */
    function sanitize_input($input, bool $allowHtml = false)
    {
        if (is_array($input)) {
            return array_map(function ($item) use ($allowHtml) {
                return sanitize_input($item, $allowHtml);
            }, $input);
        }

        if (is_string($input)) {
            // Remove null bytes
            $input = str_replace("\0", '', $input);
            
            // Trim whitespace
            $input = trim($input);
            
            if (!$allowHtml) {
                // Strip HTML tags and encode special characters
                $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
            } else {
                // Allow HTML but sanitize it
                $input = strip_tags($input, '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6>');
            }
        }

        return $input;
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize filename to prevent directory traversal and other attacks.
     * 
     * @param string $filename
     * @return string
     */
    function sanitize_filename(string $filename): string
    {
        // Remove path components
        $filename = basename($filename);
        
        // Remove null bytes
        $filename = str_replace("\0", '', $filename);
        
        // Remove special characters except dots, hyphens, underscores
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Limit length
        if (strlen($filename) > 255) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($name, 0, 255 - strlen($ext) - 1) . '.' . $ext;
        }
        
        return $filename;
    }
}

if (!function_exists('validate_file_type')) {
    /**
     * Validate file type by checking MIME type and extension.
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param array $allowedMimes
     * @param array $allowedExtensions
     * @return bool
     */
    function validate_file_type($file, array $allowedMimes, array $allowedExtensions): bool
    {
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Check MIME type
        if (!in_array($mimeType, $allowedMimes)) {
            return false;
        }
        
        // Check extension
        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }
        
        // Additional check: verify file content matches extension
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);
        
        return $detectedMime === $mimeType;
    }
}

if (!function_exists('sanitize_sql_input')) {
    /**
     * Sanitize input for SQL queries (use with caution, prefer Eloquent).
     * 
     * @param string $input
     * @return string
     */
    function sanitize_sql_input(string $input): string
    {
        // Remove SQL injection patterns
        $patterns = [
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\bSELECT\b.*\bFROM\b)/i',
            '/(\bINSERT\b.*\bINTO\b)/i',
            '/(\bUPDATE\b.*\bSET\b)/i',
            '/(\bDELETE\b.*\bFROM\b)/i',
            '/(\bDROP\b.*\bTABLE\b)/i',
            '/(\bEXEC\b|\bEXECUTE\b)/i',
            '/(\bSCRIPT\b)/i',
            '/(\bJAVASCRIPT\b)/i',
            '/(\bVBSCRIPT\b)/i',
            '/(\bONLOAD\b)/i',
        ];
        
        $input = preg_replace($patterns, '', $input);
        
        // Escape special characters
        $input = addslashes($input);
        
        return $input;
    }
}
