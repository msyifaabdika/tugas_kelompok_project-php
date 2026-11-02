<?php
  session_start();
  if (!isset($_SESSION["username"])) {
     header("Location: login.php");
     exit();
  }

  include("connection.php"); //[cite: 1]
  
  if (isset($_GET["message"])) {
      $message = $_GET["message"];
  }

  // Pengembangan: Menambahkan logika pencarian data mahasiswa
  $search = "";
  if (isset($_GET["search"])) {
      $search = trim($_GET["search"]);
      $search_escape = mysqli_real_escape_string($connection, $search);
      $query = "SELECT * FROM student WHERE nim LIKE '%$search_escape%' OR name LIKE '%$search_escape%' ORDER BY nim ASC";
  } else {
      $query = "SELECT * FROM student ORDER BY nim ASC"; //[cite: 9]
  }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Mahasiswa</title>
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
  <div class="container">
    <div id="header">
      <h1 id="logo">Data Mahasiswa</h1>
    </div>
    <hr>
    <nav>
      <ul>
        <li><a href="student_view.php">Tampil</a></li>
        <li><a href="student_add.php">Tambah</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </nav>
    
    <?php
      if (isset($message)) {
          echo "<div class='pesan'>$message</div>";
      }
    ?>

    <h2>Tabel Data Mahasiswa</h2>

    <!-- Pengembangan: Form Pencarian Sederhana -->
    <form action="student_view.php" method="get" style="margin-bottom: 15px;">
      <label for="search">Cari Mahasiswa (NIM / Nama): </label>
      <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Masukkan keyword...">
      <input type="submit" value="Cari">
      <?php if (!empty($search)): ?>
        <a href="student_view.php" style="margin-left: 10px;">Reset</a>
      <?php endif; ?>
    </form>

    <table border="1">
    <tr>
      <th>NIM</th>
      <th>Nama</th>
      <th>Tempat Lahir</th>
      <th>Tanggal Lahir</th>
      <th>Fakultas</th>
      <th>Jurusan</th>
      <th>IPK</th>
      <th>Aksi</th>
    </tr>
    <?php
      $result = mysqli_query($connection, $query);
      if(!$result) {
          die ("Query Error: ".mysqli_errno($connection)." - ".mysqli_error($connection));
      }
    
      $total_data = mysqli_num_rows($result);

      if ($total_data > 0) {
        while($data = mysqli_fetch_assoc($result)){ 
          $birth_date = strtotime($data["birth_date"]);
          $formatted_date = date("d-m-Y", $birth_date);
          
          echo "<tr>";
          echo "<td>$data[nim]</td>";
          echo "<td>$data[name]</td>";
          echo "<td>$data[birth_city]</td>";
          echo "<td>$formatted_date</td>";
          echo "<td>$data[faculty]</td>";
          echo "<td>$data[department]</td>";
          echo "<td>$data[gpa]</td>";
          echo "<td>
            <a href='student_edit.php?nim=$data[nim]'>Edit</a> | 
            <a href='student_delete.php?nim=$data[nim]' onclick=\"return confirm('Apakah Anda yakin ingin menghapus data ini?')\">Hapus</a>
          </td>";
          echo "</tr>";
        }
      } else {
        echo "<tr><td colspan='8' style='text-align: center;'>Data mahasiswa tidak ditemukan.</td></tr>";
      }
      
      mysqli_free_result($result);
      mysqli_close($connection);
    ?>
    </table>
    
    <!-- Pengembangan: Informasi jumlah data -->
    <p style="margin-top: 10px; font-size: 0.9em; color: #555;">
      Total data ditampilkan: <b><?php echo $total_data; ?></b> mahasiswa.
    </p>
  </div>
</body>
</html>