<?php
session_start();
if (!isset($_SESSION["username"])) {
  header("Location: login.php");
  exit();
}
include("connection.php");

if (isset($_GET["nim"])) {
  $nim = mysqli_real_escape_string($connection, $_GET["nim"]);

  // Eksekusi Query DELETE
  $query = "DELETE FROM student WHERE nim='$nim'";
  $result = mysqli_query($connection, $query);

  if ($result) {
    $message = "Mahasiswa dengan NIM \"<b>$nim</b>\" berhasil dihapus.";
    header("Location: student_view.php?message=" . urlencode($message));
    exit();
  } else {
    die("Query gagal dijalankan: " . mysqli_errno($connection) . " - " . mysqli_error($connection));
  }
} else {
  header("Location: student_view.php");
  exit();
}
mysqli_close($connection);
?>