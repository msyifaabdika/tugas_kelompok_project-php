<?php
session_start();

// Jika sudah login, langsung arahkan ke student_view.php
if (isset($_SESSION["username"])) {
    header("Location: student_view.php");
    exit();
}

$error_message = "";
$username = "";
$password = "";

if (isset($_POST["submit"])) {
    $username = htmlentities(strip_tags(trim($_POST["username"])));
    $password = htmlentities(strip_tags(trim($_POST["password"])));

    if (empty($username)) {
        $error_message .= "- Username belum diisi <br>";
    }

    if (empty($password)) {
        $error_message .= "- Password belum diisi <br>";
    }

    if ($error_message === "") {
        include("connection.php");

        $username = mysqli_real_escape_string($connection, $username);
        $password = mysqli_real_escape_string($connection, $password);
        $password_sha1 = sha1($password);

        $query = "SELECT * FROM admin WHERE username = '$username' AND password = '$password_sha1'";
        $result = mysqli_query($connection, $query);

        if (mysqli_num_rows($result) == 0) {
            $error_message .= "- Username dan/atau Password tidak sesuai";
        } else {
            // Jika login sukses, buat sesi
            $_SESSION["username"] = $username;
            header("Location: student_view.php");
            exit();
        }

        mysqli_free_result($result);
        mysqli_close($connection);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - Data Mahasiswa</title>
  <link rel="stylesheet" href="assets/login.css">
</head>
<body>
  <div class="container">
    <h1>Data Mahasiswa</h1>
    <?php
      if ($error_message !== "")
        echo "<div class='error'>$error_message</div>";
    ?>
    <form action="login.php" method="post">
      <fieldset>
        <legend>Login</legend>
        <p>
          <label for="username">Username : </label>
          <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>">
        </p>
        <p>
          <label for="password">Password : </label>
          <!-- Perbaikan: Menggunakan variabel $password, bukan $username -->
          <input type="password" name="password" id="password" value="<?php echo htmlspecialchars($password); ?>">
        </p>
        <p>
          <input type="submit" name="submit" value="Log In">
        </p>
      </fieldset>
    </form>
  </div>
</body>
</html>