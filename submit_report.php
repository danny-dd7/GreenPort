<?php
// filepath: c:\Users\angel\OneDrive\Documentos\GitHub\GreenPort\submit_report.php
$servername = "localhost";
$username = "root"; // Default XAMPP user
$password = ""; // Default XAMPP password
$dbname = "reportes";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $zona = $_POST['zona'];
    $fecha = $_POST['fecha'];
    $tipo_residuo = $_POST['tipo_residuo'];
    $urgencia = $_POST['urgencia'];

    $sql = "INSERT INTO reports (zona, fecha, tipo_residuo, urgencia) VALUES ('$zona', '$fecha', '$tipo_residuo', '$urgencia')";

    if ($conn->query($sql) === TRUE) {
        echo "Reporte guardado exitosamente.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>