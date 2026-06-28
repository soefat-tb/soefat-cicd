<?php
session_start();
header('Content-Type: application/json');
try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    error_log("=== LOGIN VERIFICATION START ===");
    error_log("Received data: " . print_r($data, true));
    error_log("Raw input: " . file_get_contents('php://input'));
    
    if (!$data) {
        throw new Exception("No JSON data received");
    }
    
    if (!isset($data['rawId']) || empty($data['rawId'])) {
        throw new Exception("No rawId in request");
    }
    
    $conn = new mysqli("sql309.infinityfree.com", "if0_37650982", "soefat135767991", "if0_37650982_p");
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    $rawId = $conn->real_escape_string($data['rawId']);
    error_log("Looking for rawId: " . $rawId);
    
    $result = $conn->query("SELECT * FROM credentials WHERE rawId = '$rawId'");
    error_log("Query result rows: " . ($result ? $result->num_rows : 'Query failed'));
    
    if ($result && $result->num_rows > 0) {
        $cred = $result->fetch_assoc();
        error_log("Found credential: " . print_r($cred, true));
        
        // Validasi ke tabel siswa
        $nis = $conn->real_escape_string($cred['nis']);
        $stmt = $conn->prepare("SELECT nis, nama FROM siswa WHERE nis = ?");
        $stmt->bind_param("s", $nis);
        $stmt->execute();
        $siswa_result = $stmt->get_result();
        
        if ($siswa_result && $siswa_result->num_rows > 0) {
            $siswa = $siswa_result->fetch_assoc();
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = $cred['id'];
            $_SESSION['credential_id'] = $rawId;
            $_SESSION['auth_method'] = 'passkey_login';
            $_SESSION['login_time'] = time();
            $_SESSION['nis'] = $siswa['nis']; // Set nis dari siswa
            $_SESSION['nama_lengkap'] = $siswa['nama']; // Tambah nama buat konsistensi
            
            error_log("Session created for user: " . $cred['id'] . ", NIS: " . $siswa['nis']);
            
            $response = ["status" => "success", "message" => "Login verification successful"];
            error_log("Sending SUCCESS response: " . json_encode($response));
            echo json_encode($response);
        } else {
            $response = ["status" => "error", "message" => "NIS tidak ditemukan di data siswa untuk rawId: " . $rawId];
            error_log("Sending ERROR response: " . json_encode($response));
            echo json_encode($response);
        }
        $stmt->close();
    } else {
        $response = ["status" => "error", "message" => "Credential not found for rawId: " . $rawId];
        error_log("Sending ERROR response: " . json_encode($response));
        echo json_encode($response);
    }
    
    $conn->close();
    error_log("=== LOGIN VERIFICATION END ===");
} catch (Exception $e) {
    $error_response = ["status" => "error", "message" => "Exception: " . $e->getMessage()];
    error_log("EXCEPTION in login-verify.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode($error_response);
}
?>