<?php
/**
 * Funciones de seguridad y validación
 */

/**
 * Sanitiza un string para prevenir XSS
 * @param string $data String a sanitizar
 * @return string String sanitizado
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida un email
 * @param string $email Email a validar
 * @return bool True si es válido, false si no
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida un archivo de imagen
 * @param array $file Array $_FILES
 * @param int $max_size Tamaño máximo en bytes (default 5MB)
 * @return array ['valid' => bool, 'error' => string]
 */
function validateImageFile($file, $max_size = 5242880) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['valid' => false, 'error' => 'No se subió ningún archivo'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Error al subir el archivo'];
    }
    
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'El archivo es demasiado grande. Máximo: ' . ($max_size / 1024 / 1024) . 'MB'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['valid' => false, 'error' => 'Tipo de archivo no permitido. Solo se permiten imágenes (JPG, PNG, WEBP, GIF)'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        return ['valid' => false, 'error' => 'Extensión de archivo no permitida'];
    }
    
    // Verificar que realmente es una imagen
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        return ['valid' => false, 'error' => 'El archivo no es una imagen válida'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Genera un nombre de archivo seguro
 * @param string $original_name Nombre original del archivo
 * @return string Nombre seguro
 */
function generateSafeFileName($original_name) {
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $safe_name = uniqid() . '_' . time() . '.' . $extension;
    return $safe_name;
}

/**
 * Valida un número entero positivo
 * @param mixed $value Valor a validar
 * @param int $min Valor mínimo (default 0)
 * @param int $max Valor máximo (default null)
 * @return bool True si es válido
 */
function validatePositiveInt($value, $min = 0, $max = null) {
    $int_value = filter_var($value, FILTER_VALIDATE_INT);
    if ($int_value === false) {
        return false;
    }
    if ($int_value < $min) {
        return false;
    }
    if ($max !== null && $int_value > $max) {
        return false;
    }
    return true;
}

/**
 * Valida un número decimal positivo
 * @param mixed $value Valor a validar
 * @param float $min Valor mínimo (default 0)
 * @return bool True si es válido
 */
function validatePositiveFloat($value, $min = 0) {
    $float_value = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($float_value === false) {
        return false;
    }
    if ($float_value < $min) {
        return false;
    }
    return true;
}

