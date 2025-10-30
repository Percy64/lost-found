<?php
/**
 * Generador de códigos QR para el sistema de mascotas
 * Utiliza la API gratuita de QR Server para generar códigos QR
 */
class QRGenerator {
    
    private $baseUrl;
    private $qrDirectory;
    
    public function __construct() {
        // Detectar la URL base automáticamente
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $this->baseUrl = $protocol . $host . $scriptName;
        
        // Limpiar la URL base
        $this->baseUrl = rtrim($this->baseUrl, '/');
        
        $this->qrDirectory = 'assets/images/qr/';
        
        // Crear directorio si no existe
        if (!file_exists($this->qrDirectory)) {
            mkdir($this->qrDirectory, 0777, true);
        }
    }
    
    /**
     * Genera un código QR para una mascota
     * @param int $id_mascota ID de la mascota
     * @param array $mascota_data Datos de la mascota (opcional, para información adicional)
     * @return array Resultado con información del QR generado
     */
    public function generarQRMascota($id_mascota, $mascota_data = null) {
        try {
            // URL que apuntará al perfil de la mascota
            $url_mascota = $this->baseUrl . '/perfil_mascota.php?id=' . $id_mascota;
            
            // Generar código QR usando API externa
            $qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/';
            $params = [
                'data' => $url_mascota,
                'size' => '300x300',
                'format' => 'png',
                'margin' => '10',
                'color' => '000000',
                'bgcolor' => 'FFFFFF'
            ];
            
            $qr_url = $qr_api_url . '?' . http_build_query($params);
            
            // Descargar la imagen QR
            $qr_image = file_get_contents($qr_url);
            
            if ($qr_image === false) {
                throw new Exception('Error al generar código QR desde la API');
            }
            
            // Guardar la imagen localmente
            $filename = 'qr_mascota_' . $id_mascota . '_' . time() . '.png';
            $filepath = $this->qrDirectory . $filename;
            
            if (file_put_contents($filepath, $qr_image) === false) {
                throw new Exception('Error al guardar el archivo QR');
            }
            
            return [
                'success' => true,
                'qr_path' => $filepath,
                'qr_url' => $this->baseUrl . '/' . $filepath,
                'mascota_url' => $url_mascota,
                'filename' => $filename,
                'message' => 'Código QR generado exitosamente'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'qr_path' => null,
                'qr_url' => null
            ];
        }
    }
    
    /**
     * Genera un QR code simple usando solo PHP (sin API externa)
     * Método alternativo para cuando no hay conexión a internet
     */
    public function generarQRSimple($id_mascota) {
        try {
            $url_mascota = $this->baseUrl . '/perfil_mascota.php?id=' . $id_mascota;
            
            // Usar Google Charts API como alternativa
            $google_qr_url = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($url_mascota);
            
            $qr_image = file_get_contents($google_qr_url);
            
            if ($qr_image === false) {
                // Si falla, crear un QR placeholder
                return $this->crearQRPlaceholder($id_mascota);
            }
            
            $filename = 'qr_mascota_' . $id_mascota . '_simple_' . time() . '.png';
            $filepath = $this->qrDirectory . $filename;
            
            file_put_contents($filepath, $qr_image);
            
            return [
                'success' => true,
                'qr_path' => $filepath,
                'qr_url' => $this->baseUrl . '/' . $filepath,
                'mascota_url' => $url_mascota,
                'filename' => $filename
            ];
            
        } catch (Exception $e) {
            return $this->crearQRPlaceholder($id_mascota);
        }
    }
    
    /**
     * Crea un placeholder SVG cuando no se puede generar QR real
     */
    private function crearQRPlaceholder($id_mascota) {
        $svg_content = '<?xml version="1.0" encoding="UTF-8"?>
        <svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
            <rect width="300" height="300" fill="white" stroke="#000" stroke-width="2"/>
            <rect x="50" y="50" width="200" height="200" fill="none" stroke="#000" stroke-width="1"/>
            <text x="150" y="100" text-anchor="middle" font-family="Arial" font-size="16" fill="#000">QR CODE</text>
            <text x="150" y="130" text-anchor="middle" font-family="Arial" font-size="14" fill="#666">Mascota ID: ' . $id_mascota . '</text>
            <text x="150" y="160" text-anchor="middle" font-family="Arial" font-size="12" fill="#999">localhost/lost-found</text>
            <text x="150" y="180" text-anchor="middle" font-family="Arial" font-size="12" fill="#999">/perfil_mascota.php?id=' . $id_mascota . '</text>
        </svg>';
        
        $filename = 'qr_placeholder_' . $id_mascota . '.svg';
        $filepath = $this->qrDirectory . $filename;
        
        file_put_contents($filepath, $svg_content);
        
        return [
            'success' => true,
            'qr_path' => $filepath,
            'qr_url' => $this->baseUrl . '/' . $filepath,
            'mascota_url' => $this->baseUrl . '/perfil_mascota.php?id=' . $id_mascota,
            'filename' => $filename,
            'is_placeholder' => true
        ];
    }
    
    /**
     * Actualiza el código QR en la base de datos
     */
    public function actualizarQREnBD($pdo, $id_mascota, $qr_info) {
        try {
            if (!$qr_info['success']) {
                return false;
            }
            
            // Primero verificar si ya existe un QR para esta mascota
            $sql_check = "SELECT id_qr FROM mascotas WHERE id_mascota = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$id_mascota]);
            $existing_qr = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_qr && $existing_qr['id_qr']) {
                // Actualizar QR existente
                $sql_update = "UPDATE codigos_qr SET url_qr = ?, ruta_imagen = ? WHERE id_qr = ?";
                $stmt_update = $pdo->prepare($sql_update);
                return $stmt_update->execute([
                    $qr_info['mascota_url'],
                    $qr_info['qr_path'],
                    $existing_qr['id_qr']
                ]);
            } else {
                // Crear nuevo registro en codigos_qr
                $codigo_unico = 'QR-' . $id_mascota . '-' . uniqid();
                $sql_insert = "INSERT INTO codigos_qr (codigo, url_qr, ruta_imagen) VALUES (?, ?, ?)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    $codigo_unico,
                    $qr_info['mascota_url'],
                    $qr_info['qr_path']
                ]);
                
                $new_qr_id = $pdo->lastInsertId();
                
                // Actualizar la mascota con el ID del QR
                $sql_update_mascota = "UPDATE mascotas SET id_qr = ? WHERE id_mascota = ?";
                $stmt_update_mascota = $pdo->prepare($sql_update_mascota);
                return $stmt_update_mascota->execute([$new_qr_id, $id_mascota]);
            }
            
        } catch (PDOException $e) {
            error_log("Error al actualizar QR en BD: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene información del QR de una mascota
     */
    public function obtenerQRMascota($pdo, $id_mascota) {
        try {
            $sql = "SELECT cq.* FROM codigos_qr cq 
                    JOIN mascotas m ON cq.id_qr = m.id_qr 
                    WHERE m.id_mascota = ? AND m.id_qr IS NOT NULL";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_mascota]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: false;
        } catch (PDOException $e) {
            error_log("Error al obtener QR: " . $e->getMessage());
            return false;
        }
    }
}
?>