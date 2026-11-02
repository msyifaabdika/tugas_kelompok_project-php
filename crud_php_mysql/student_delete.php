
<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/connection.php";

// Memastikan request menggunakan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: student_view.php");
    exit();
}

// Mengambil dan memvalidasi NIM
$nim = trim($_POST["nim"] ?? "");

if ($nim === "") {
    header("Location: student_view.php?message=" . urlencode("NIM tidak valid."));
    exit();
}

// Menghapus data menggunakan prepared statement
$query = "DELETE FROM student WHERE nim = ?";
$stmt = mysqli_prepare($connection, $query);

if (!$stmt) {
    die("Query gagal disiapkan: " . mysqli_error($connection));
}

mysqli_stmt_bind_param($stmt, "s", $nim);

if (mysqli_stmt_execute($stmt)) {

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $message = "Data mahasiswa dengan NIM " . $nim . " berhasil dihapus.";
    } else {
        $message = "Data mahasiswa tidak ditemukan.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($connection);

    header("Location: student_view.php?message=" . urlencode($message));
    exit();

} else {
    die("Gagal menghapus data: " . mysqli_stmt_error($stmt));
}
?>
