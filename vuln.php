<?php
// Basit veritabanı bağlantısı (PDO ile değil, bilinçli zafiyet için)
$mysqli = new mysqli("localhost", "root", "password", "testdb");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// 1. XSS ve Header Injection Açığı
if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // HTTP header içine kullanıcı girdisi yazılıyor - Header Injection açığı
    header("X-User-Name: $username");

    // XSS açığı - doğrudan HTML'e gömülüyor
    echo "<h1>Welcome, $username!</h1>";
}

// 2. SQL Injection Açığı
if (isset($_POST['email'])) {
    $email = $_POST['email'];

    // Kullanıcı girdisi doğrudan SQL sorgusuna ekleniyor
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $mysqli->query($sql);

    if ($result) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>User: " . $row['name'] . "</li>";
        }
        echo "</ul>";
    }
}

// 3. File Inclusion Açığı
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    include "pages/$page.php";  // Path Traversal ve Local File Inclusion açığı
}

// 4. Command Injection Açığı
if (isset($_GET['ping'])) {
    $host = $_GET['ping'];
    // Komut satırına doğrudan kullanıcı girdisi ekleniyor
    $cmd = "ping -c 4 " . $host;
    echo "<pre>" . shell_exec($cmd) . "</pre>";
}

// 5. Insecure File Upload
if (isset($_FILES['upload'])) {
    $uploadDir = "uploads/";
    $uploadFile = $uploadDir . basename($_FILES['upload']['name']);

    // Dosya uzantısı kontrolü yapılmıyor (zafiyet)
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadFile)) {
        echo "File uploaded successfully: " . htmlspecialchars($uploadFile);
    } else {
        echo "File upload failed.";
    }
}
?>
