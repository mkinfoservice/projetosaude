<?php
/**
 * api/Upload.php
 * Handler seguro para upload de arquivos
 */

require_once __DIR__ . '/config.php';

class Upload {
    private $uploadPath;
    private $maxSize;
    private $allowedExtensions;
    private $allowedMimes;
    private $errors = [];
    
    public function __construct($path = null) {
        $this->uploadPath = $path ?? UPLOAD_PATH;
        $this->maxSize = UPLOAD_MAX_SIZE;
        $this->allowedExtensions = UPLOAD_ALLOWED_EXTENSIONS;
        $this->allowedMimes = UPLOAD_ALLOWED_MIMES;
        
        $this->ensureUploadDir();
    }
    
    private function ensureUploadDir() {
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
        
        // Criar .htaccess se não existir
        $htaccess = $this->uploadPath . '.htaccess';
        if (!file_exists($htaccess)) {
            $content = "Options -Indexes\n<FilesMatch \"\.(php|php\d|phtml|phar|pl|py|jsp|asp|sh|cgi)$\">\nDeny from all\n</FilesMatch>";
            file_put_contents($htaccess, $content);
        }
    }
    
    // ===== UPLOAD ÚNICO =====
    
    public function upload($inputName, $prefix, $entityId, $fieldName = null) {
        if (!isset($_FILES[$inputName])) {
            $this->errors[] = 'Nenhum arquivo enviado';
            return false;
        }
        
        $file = $_FILES[$inputName];
        
        // Verificar erros do PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }
        
        // Validar tamanho
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = 'Arquivo muito grande. Máximo: ' . ($this->maxSize / 1024 / 1024) . 'MB';
            return false;
        }
        
        // Validar extensão
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions)) {
            $this->errors[] = 'Extensão não permitida: .' . $ext;
            return false;
        }
        
        // Validar MIME type REAL
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedMimes)) {
            $this->errors[] = 'Tipo de arquivo não permitido: ' . $mimeType;
            return false;
        }
        
        // Sanitizar nome e gerar nome único
        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalName);
        $uniqueName = sprintf(
            '%s_%s_%d_%s_%s.%s',
            strtoupper($prefix),
            $fieldName ?: 'DOC',
            $entityId,
            date('YmdHis'),
            bin2hex(random_bytes(4)),
            $ext
        );
        
        $destination = $this->uploadPath . $uniqueName;
        
        // Mover arquivo
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            log_system('ERROR', 'UPLOAD_MOVE_FAILED', [
                'file' => $file['name'],
                'destination' => $destination
            ]);
            $this->errors[] = 'Falha ao salvar arquivo';
            return false;
        }
        
        // Definir permissões
        chmod($destination, 0644);
        
        log_system('INFO', 'FILE_UPLOADED', [
            'filename' => $uniqueName,
            'original' => $file['name'],
            'size' => $file['size'],
            'mime' => $mimeType,
            'prefix' => $prefix,
            'entity_id' => $entityId
        ]);
        
        return [
            'filename' => $uniqueName,
            'original_name' => $file['name'],
            'path' => '/assets/uploads/' . $uniqueName,
            'size' => $file['size'],
            'mime' => $mimeType,
            'ext' => $ext
        ];
    }
    
    // ===== UPLOAD MÚLTIPLO =====
    
    public function uploadMultiple($filesConfig) {
        $results = [];
        $allSuccess = true;
        
        foreach ($filesConfig as $inputName => $config) {
            $result = $this->upload(
                $inputName,
                $config['prefix'],
                $config['entity_id'],
                $config['field_name'] ?? null
            );
            
            $results[$inputName] = $result;
            if (!$result) $allSuccess = false;
        }
        
        return $allSuccess ? $results : false;
    }
    
    // ===== DELETE =====
    
    public function delete($filename) {
        $path = $this->uploadPath . basename($filename);
        
        if (!file_exists($path)) {
            return true; // Já não existe
        }
        
        if (!unlink($path)) {
            log_system('ERROR', 'FILE_DELETE_FAILED', ['filename' => $filename]);
            return false;
        }
        
        log_system('INFO', 'FILE_DELETED', ['filename' => $filename]);
        return true;
    }
    
    // ===== VALIDAÇÕES =====
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getLastError() {
        return end($this->errors) ?: 'Erro desconhecido';
    }
    
    private function getUploadErrorMessage($code) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo excede limit do php.ini',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo excede limite do formulário',
            UPLOAD_ERR_PARTIAL => 'Upload parcial',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever no disco',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão PHP'
        ];
        return $errors[$code] ?? 'Erro desconhecido (' . $code . ')';
    }
    
    // ===== GETTERS =====
    
    public function getUploadPath() {
        return $this->uploadPath;
    }
    
    public function getPublicUrl($filename) {
        return '/assets/uploads/' . basename($filename);
    }
}
?>