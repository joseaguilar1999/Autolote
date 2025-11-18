<?php
/**
 * Funciones de utilidad para paginación
 */

/**
 * Genera los links de paginación
 * @param int $current_page Página actual
 * @param int $total_pages Total de páginas
 * @param string $base_url URL base para los links (puede incluir otros parámetros)
 * @param array $query_params Parámetros adicionales para incluir en la URL
 * @return string HTML con los links de paginación
 */
function generatePagination($current_page, $total_pages, $base_url, $query_params = []) {
    if ($total_pages <= 1) {
        return '';
    }
    
    // Función auxiliar para construir la URL con el parámetro de página
    $buildUrl = function($page) use ($base_url, $query_params) {
        $params = array_merge($query_params, ['page' => $page]);
        $separator = (strpos($base_url, '?') !== false) ? '&' : '?';
        return $base_url . $separator . http_build_query($params);
    };
    
    $html = '<nav aria-label="Paginación">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Botón Anterior
    if ($current_page > 1) {
        $prev_page = $current_page - 1;
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($buildUrl($prev_page)) . '">';
        $html .= '<i class="bi bi-chevron-left"></i> Anterior';
        $html .= '</a></li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link"><i class="bi bi-chevron-left"></i> Anterior</span>';
        $html .= '</li>';
    }
    
    // Números de página
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    if ($start > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($buildUrl(1)) . '">1</a>';
        $html .= '</li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $html .= '<li class="page-item active">';
            $html .= '<span class="page-link">' . $i . '</span>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . htmlspecialchars($buildUrl($i)) . '">' . $i . '</a>';
            $html .= '</li>';
        }
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($buildUrl($total_pages)) . '">' . $total_pages . '</a>';
        $html .= '</li>';
    }
    
    // Botón Siguiente
    if ($current_page < $total_pages) {
        $next_page = $current_page + 1;
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($buildUrl($next_page)) . '">';
        $html .= 'Siguiente <i class="bi bi-chevron-right"></i>';
        $html .= '</a></li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link">Siguiente <i class="bi bi-chevron-right"></i></span>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Calcula la información de paginación
 * @param int $total_items Total de elementos
 * @param int $items_per_page Elementos por página
 * @param int $current_page Página actual
 * @return array Array con información de paginación
 */
function getPaginationInfo($total_items, $items_per_page, $current_page) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'total_items' => $total_items,
        'items_per_page' => $items_per_page,
        'offset' => $offset,
        'start_item' => $offset + 1,
        'end_item' => min($offset + $items_per_page, $total_items)
    ];
}

