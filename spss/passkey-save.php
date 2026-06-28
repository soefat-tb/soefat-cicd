<?php
session_start();
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    error_log("Received data: " . print_r($data, true));

    $conn = new mysqli("sql309.infinityfree.com", "if0_37650982", "soefat135767991", "if0_37650982_p");
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        echo json_encode(["status" => "error", "message" => "Database connection failed"]);
        exit;
    }

    $id = $conn->real_escape_string($data['id'] ?? '');
    $rawId = $conn->real_escape_string($data['rawId']);
    $type = $conn->real_escape_string($data['type']);
    $clientDataJSON = $conn->real_escape_string($data['response']['clientDataJSON']);
    $attestationObject = $conn->real_escape_string($data['response']['attestationObject']);
    $userHandle = $conn->real_escape_string(base64_decode(str_replace(['-', '_'], ['+', '/'], $data['response']['userHandle'] ?? '')));

    $credentialId = $conn->real_escape_string(base64_decode(str_replace(['-', '_'], ['+', '/'], $rawId)));
    $publicKey = $conn->real_escape_string(base64_decode(str_replace(['-', '_'], ['+', '/'], $attestationObject)));
    $nis = $conn->real_escape_string($_SESSION['selected_nis']);

    $sql = "INSERT INTO credentials (id, credential_id, public_key, rawId, type, clientDataJSON, attestationObject, user_handle, nis, created_at) 
            VALUES ('$id', '$credentialId', '$publicKey', '$rawId', '$type', '$clientDataJSON', '$attestationObject', '$userHandle', '$nis', NOW()) 
            ON DUPLICATE KEY UPDATE id=VALUES(id), clientDataJSON=VALUES(clientDataJSON)";
    if ($conn->query($sql) === TRUE) {
        error_log("Data inserted successfully: rawId=$rawId, nis=$nis");
        echo json_encode(["status" => "success"]);
    } else {
        error_log("Insert failed: " . $conn->error);
        echo json_encode(["status" => "error", "message" => "Insert failed: " . $conn->error]);
    }

    $conn->close();
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Exception: " . $e->getMessage()]);
}
?>