<?php
// filepath: c:\Users\angel\OneDrive\Documentos\GitHub\GreenPort\submit_report.php
$servername = "localhost";
$username = "root"; // Default XAMPP user
$password = ""; // Default XAMPP password
$dbname = "BaseDatos";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function safeValue($value) {
    return trim($value);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $apellido = safeValue($_POST['apellido'] ?? '');
    $nombre = safeValue($_POST['nombre'] ?? '');
    $zona = safeValue($_POST['zona'] ?? '');
    $tipo_residuo = safeValue($_POST['tipo_residuo'] ?? '');
    $telefono = safeValue($_POST['telefono'] ?? '');
    $problema = safeValue($_POST['problema'] ?? '');

    if (!$apellido || !$nombre || !$zona || !$tipo_residuo || !$telefono || !$problema) {
        echo "Faltan datos obligatorios para enviar el reporte.";
        $conn->close();
        exit;
    }

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $savedFiles = [];
    if (!empty($_FILES['fotos']) && is_array($_FILES['fotos']['name'])) {
        $fileCount = count($_FILES['fotos']['name']);
        if ($fileCount > 3) {
            echo "Solo se permiten hasta 3 fotos.";
            $conn->close();
            exit;
        }

        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) {
                echo "Error al subir la foto " . ($_FILES['fotos']['name'][$i] ?? '') . ".";
                $conn->close();
                exit;
            }

            if ($_FILES['fotos']['size'][$i] > 4 * 1024 * 1024) {
                echo "Cada foto debe pesar como máximo 4 MB.";
                $conn->close();
                exit;
            }

            $tmpName = $_FILES['fotos']['tmp_name'][$i];
            $name = basename($_FILES['fotos']['name'][$i]);
            $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
            $targetFile = $uploadDir . '/' . uniqid('foto_', true) . '_' . $name;

            $imageInfo = getimagesize($tmpName);
            if ($imageInfo === false) {
                echo "Solo se permiten archivos de imagen.";
                $conn->close();
                exit;
            }

            if (!move_uploaded_file($tmpName, $targetFile)) {
                echo "No se pudo guardar la foto " . $name . ".";
                $conn->close();
                exit;
            }

            $savedFiles[] = 'uploads/' . basename($targetFile);
        }
    }

    $fecha = date('Y-m-d H:i:s');
    $urgencia = 'Media';
    $fotoPaths = !empty($savedFiles) ? implode(',', $savedFiles) : '';

    $stmt = $conn->prepare("INSERT INTO reports (apellido, nombre, zona, tipo_residuo, telefono, problema, fotos, fecha, urgencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        echo "Error de base de datos: " . $conn->error;
        $conn->close();
        exit;
    }

    $stmt->bind_param('sssssssss', $apellido, $nombre, $zona, $tipo_residuo, $telefono, $problema, $fotoPaths, $fecha, $urgencia);
    if ($stmt->execute()) {
        echo "Reporte enviado exitosamente.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>