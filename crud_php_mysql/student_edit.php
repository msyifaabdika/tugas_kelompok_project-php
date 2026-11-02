<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}
include("connection.php");

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$arr_month = [
    "1" => "Januari", "2" => "Februari", "3" => "Maret", "4" => "April",
    "5" => "Mei", "6" => "Juni", "7" => "Juli", "8" => "Agustus",
    "9" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember"
];

$error_message = "";
$nim = "";
$name = "";
$birth_city = "";
$faculty = "FTIB";
$department = "";
$gpa = "";
$birth_date = "";
$birth_month = "";
$birth_year = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    $nim = trim($_POST["nim"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $birth_city = trim($_POST["birth_city"] ?? "");
    $faculty = trim($_POST["faculty"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $gpa_raw = trim($_POST["gpa"] ?? "");
    $gpa = $gpa_raw;
    $birth_date = trim($_POST["birth_date"] ?? "");
    $birth_month = trim($_POST["birth_month"] ?? "");
    $birth_year = trim($_POST["birth_year"] ?? "");

    $errors = [];
    if (!preg_match('/^\d{8}$/', $nim)) $errors[] = "NIM harus terdiri dari 8 angka.";
    if ($name === "") $errors[] = "Nama belum diisi.";
    if ($birth_city === "") $errors[] = "Tempat lahir belum diisi.";
    if (!in_array($faculty, ["FTIB", "FTEIC"], true)) $errors[] = "Fakultas tidak valid.";
    if ($department === "") $errors[] = "Jurusan belum diisi.";
    if ($gpa_raw === "" || !is_numeric($gpa_raw) || (float)$gpa_raw < 0 || (float)$gpa_raw > 4) {
        $errors[] = "IPK harus berupa angka antara 0,00 sampai 4,00.";
    }
    if (!ctype_digit($birth_date) || !ctype_digit($birth_month) || !ctype_digit($birth_year) ||
        !checkdate((int)$birth_month, (int)$birth_date, (int)$birth_year)) {
        $errors[] = "Tanggal lahir tidak valid.";
    }

    if (!$errors) {
        $birth_date_full = sprintf("%04d-%02d-%02d", (int)$birth_year, (int)$birth_month, (int)$birth_date);
        $gpa_value = (float)$gpa_raw;
        $stmt = mysqli_prepare($connection, "UPDATE student SET name=?, birth_city=?, birth_date=?, faculty=?, department=?, gpa=? WHERE nim=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssds", $name, $birth_city, $birth_date_full, $faculty, $department, $gpa_value, $nim);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $message = "Data mahasiswa dengan NIM " . $nim . " berhasil diperbarui.";
                header("Location: student_view.php?message=" . urlencode($message));
                exit();
            } else {
                $error_message = "Data gagal diperbarui. Silakan coba lagi.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Terjadi kesalahan saat menyiapkan query.";
        }
    } else {
        $error_message = implode(" ", $errors);
    }
} else {
    $nim = trim($_GET["nim"] ?? "");
    if ($nim === "") {
        header("Location: student_view.php");
        exit();
    }

    $stmt = mysqli_prepare($connection, "SELECT nim, name, birth_city, birth_date, faculty, department, gpa FROM student WHERE nim=?");
    if (!$stmt) {
        die("Terjadi kesalahan saat mengambil data.");
    }
    mysqli_stmt_bind_param($stmt, "s", $nim);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) !== 1) {
        mysqli_stmt_close($stmt);
        header("Location: student_view.php");
        exit();
    }
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $name = $data["name"];
    $birth_city = $data["birth_city"];
    $faculty = $data["faculty"];
    $department = $data["department"];
    $gpa = $data["gpa"];

    $parts = explode("-", $data["birth_date"]);
    $birth_year = $parts[0] ?? "";
    $birth_month = (string)(int)($parts[1] ?? 0);
    $birth_date = (string)(int)($parts[2] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Mahasiswa | StudentHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--navy:#14213d;--blue:#3267e9;--ink:#18243b;--muted:#718096;--line:#e4eaf3;--bg:#f4f7fc;--white:#fff;--green:#12805c;--red:#b42318}
*{box-sizing:border-box}body{margin:0;background:var(--bg);font-family:'DM Sans',sans-serif;color:var(--ink)}a{text-decoration:none;color:inherit}
.layout{display:flex;min-height:100vh}.sidebar{width:245px;background:var(--navy);color:#dce5f6;padding:28px 18px;display:flex;flex-direction:column;flex-shrink:0}
.brand{display:flex;align-items:center;gap:12px;padding:0 12px 34px;color:white;font:800 19px 'Manrope',sans-serif}.brand-icon{display:grid;place-items:center;background:#3267e9;width:38px;height:38px;border-radius:12px;color:white}
.nav-label{font-size:10px;letter-spacing:1.6px;color:#8192b2;font-weight:700;padding:0 13px;margin:12px 0}
.nav-link{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:11px;color:#b9c7e0;margin:3px 0;font-size:13px;font-weight:600}.nav-link:hover,.nav-link.active{background:#25375a;color:#fff}.nav-link.active{box-shadow:inset 3px 0 #6b98ff}.nav-icon{width:19px;text-align:center;font-size:16px}.sidebar-bottom{margin-top:auto;padding:18px 12px 0;border-top:1px solid #31415f;color:#aebbd2;font-size:12px;line-height:1.7}
.main{flex:1;min-width:0}.topbar{height:78px;background:white;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 42px}.crumb{font-size:12px;color:var(--muted)}.crumb b{color:var(--ink)}.profile{display:flex;align-items:center;gap:10px;font-size:12px;font-weight:700}.avatar{width:34px;height:34px;border-radius:50%;background:#e8efff;color:var(--blue);display:grid;place-items:center;font-weight:800}
.content{max-width:1050px;margin:0 auto;padding:34px 42px 48px}.back{font-size:12px;color:var(--muted);font-weight:700;display:inline-flex;gap:7px;margin-bottom:22px}.heading{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:25px}.eyebrow{font-size:11px;color:var(--blue);letter-spacing:1.3px;font-weight:800;text-transform:uppercase;margin-bottom:8px}.heading h1{font:800 28px 'Manrope',sans-serif;margin:0 0 8px;letter-spacing:-.8px}.heading p{color:var(--muted);font-size:13px;margin:0;line-height:1.6}.tag{background:#e8f7f0;color:var(--green);border-radius:20px;padding:8px 12px;font-size:11px;font-weight:800;white-space:nowrap}
.card{background:white;border:1px solid var(--line);border-radius:18px;box-shadow:0 8px 30px rgba(31,55,95,.04);overflow:hidden}.card-head{padding:23px 28px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:14px}.section-icon{width:42px;height:42px;border-radius:13px;background:#edf2ff;color:var(--blue);display:grid;place-items:center;font-size:19px}.card-head h2{font:700 15px 'Manrope',sans-serif;margin:0 0 4px}.card-head p{font-size:12px;color:var(--muted);margin:0}.card-body{padding:27px 28px 30px}.section-title{font:800 11px 'Manrope',sans-serif;text-transform:uppercase;letter-spacing:1.1px;color:#74819a;margin:3px 0 18px}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 22px}.field{min-width:0}.field.full{grid-column:1/-1}label{display:block;font-size:12px;font-weight:700;margin-bottom:8px;color:#33415c}.required{color:#e05252}input,select{width:100%;height:45px;border:1px solid #dce3ef;border-radius:10px;padding:0 13px;background:#fff;color:var(--ink);font:500 13px 'DM Sans',sans-serif;outline:none;transition:.2s}input:focus,select:focus{border-color:#7194f5;box-shadow:0 0 0 3px #eaf0ff}input[readonly]{background:#f2f5fa;color:#6d7a92;cursor:not-allowed}.hint{font-size:11px;color:#8994a8;margin-top:7px}.date-grid{display:grid;grid-template-columns:.7fr 1.5fr 1fr;gap:9px}.error{background:#fff1f0;border:1px solid #ffd4d0;color:var(--red);padding:13px 16px;border-radius:11px;font-size:12px;line-height:1.7;margin-bottom:20px}.note{display:flex;gap:11px;background:#f5f8ff;border:1px solid #e3ebff;padding:14px 16px;border-radius:12px;margin-top:24px;font-size:11px;color:#64728d;line-height:1.65}.note span{font-size:17px}.actions{display:flex;justify-content:space-between;align-items:center;gap:12px;border-top:1px solid var(--line);margin-top:28px;padding-top:22px}.btn{border:0;border-radius:10px;padding:12px 17px;font:700 12px 'DM Sans',sans-serif;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px}.btn-light{background:#f0f3f8;color:#53627b}.btn-primary{background:var(--blue);color:white;box-shadow:0 5px 12px #3267e92b}.btn-primary:hover{background:#2456d2}.footer{text-align:center;font-size:11px;color:#9aa4b5;padding:24px 0 0}
@media(max-width:850px){.sidebar{width:205px}.topbar{padding:0 24px}.content{padding:28px 24px}}@media(max-width:650px){.layout{display:block}.sidebar{width:100%;padding:14px;display:block}.brand{padding:0 8px 13px}.nav-label,.sidebar-bottom{display:none}.sidebar nav{display:flex;gap:5px}.nav-link{padding:10px;flex:1;justify-content:center;font-size:11px}.nav-icon{display:none}.topbar{height:58px;padding:0 18px}.content{padding:24px 15px}.heading h1{font-size:24px}.heading{align-items:flex-start}.tag{display:none}.card-head,.card-body{padding:20px}.form-grid{grid-template-columns:1fr;gap:17px}.field.full{grid-column:auto}.actions{flex-direction:column-reverse;align-items:stretch}.actions .btn{width:100%}}
</style>
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand"><div class="brand-icon">S</div><span>StudentHub</span></div>
    <div class="nav-label">MENU UTAMA</div>
    <nav>
      <a class="nav-link" href="index.php"><span class="nav-icon">⌂</span>Dashboard</a>
      <a class="nav-link" href="student_view.php"><span class="nav-icon">▤</span>Data Mahasiswa</a>
      <a class="nav-link" href="student_add.php"><span class="nav-icon">＋</span>Tambah Data</a>
    </nav>
    <div class="sidebar-bottom">Sistem Informasi Akademik<br>Kelola data mahasiswa dengan mudah.</div>
  </aside>
  <main class="main">
    <header class="topbar">
      <div class="crumb">Data Mahasiswa &nbsp;/&nbsp; <b>Edit Data</b></div>
      <div class="profile"><div class="avatar"><?= e(strtoupper(substr($_SESSION["username"], 0, 1))) ?></div><span><?= e($_SESSION["username"]) ?></span></div>
    </header>
    <div class="content">
      <a class="back" href="student_view.php">← &nbsp; Kembali ke data mahasiswa</a>
      <div class="heading">
        <div><div class="eyebrow">Pengelolaan data</div><h1>Edit Mahasiswa</h1><p>Perbarui informasi mahasiswa dengan teliti sebelum menyimpan perubahan.</p></div>
        <div class="tag">✦ Mode Edit</div>
      </div>
      <?php if ($error_message !== ""): ?>
        <div class="error"><strong>Periksa kembali data kamu.</strong><br><?= e($error_message) ?></div>
      <?php endif; ?>
      <section class="card">
        <div class="card-head"><div class="section-icon">✎</div><div><h2>Informasi Mahasiswa</h2><p>Pastikan semua informasi sudah benar.</p></div></div>
        <div class="card-body">
          <form action="student_edit.php" method="post">
            <div class="section-title">Identitas mahasiswa</div>
            <div class="form-grid">
              <div class="field"><label for="nim">NIM <span class="required">*</span></label><input type="text" name="nim" id="nim" value="<?= e($nim) ?>" readonly><div class="hint">NIM dikunci karena menjadi identitas data.</div></div>
              <div class="field"><label for="name">Nama lengkap <span class="required">*</span></label><input type="text" name="name" id="name" value="<?= e($name) ?>" maxlength="100" required placeholder="Masukkan nama lengkap"></div>
              <div class="field"><label for="birth_city">Tempat lahir <span class="required">*</span></label><input type="text" name="birth_city" id="birth_city" value="<?= e($birth_city) ?>" maxlength="100" required placeholder="Contoh: Surabaya"></div>
              <div class="field"><label for="birth_date">Tanggal lahir <span class="required">*</span></label><div class="date-grid">
                <select name="birth_date" id="birth_date" required aria-label="Tanggal"><?php for($i=1;$i<=31;$i++): ?><option value="<?= $i ?>" <?= ((int)$birth_date===$i)?'selected':'' ?>><?= sprintf('%02d',$i) ?></option><?php endfor; ?></select>
                <select name="birth_month" id="birth_month" required aria-label="Bulan"><?php foreach($arr_month as $key=>$value): ?><option value="<?= e($key) ?>" <?= ((int)$birth_month===(int)$key)?'selected':'' ?>><?= e($value) ?></option><?php endforeach; ?></select>
                <select name="birth_year" id="birth_year" required aria-label="Tahun"><?php for($i=(int)date("Y");$i>=1940;$i--): ?><option value="<?= $i ?>" <?= ((int)$birth_year===$i)?'selected':'' ?>><?= $i ?></option><?php endfor; ?></select>
              </div></div>
              <div class="field"><label for="faculty">Fakultas <span class="required">*</span></label><select name="faculty" id="faculty" required><option value="FTIB" <?= $faculty==="FTIB"?'selected':'' ?>>FTIB</option><option value="FTEIC" <?= $faculty==="FTEIC"?'selected':'' ?>>FTEIC</option></select></div>
              <div class="field"><label for="department">Jurusan <span class="required">*</span></label><input type="text" name="department" id="department" value="<?= e($department) ?>" maxlength="100" required placeholder="Masukkan nama jurusan"></div>
              <div class="field"><label for="gpa">IPK <span class="required">*</span></label><input type="number" name="gpa" id="gpa" value="<?= e($gpa) ?>" min="0" max="4" step="0.01" required placeholder="Contoh: 3.75"><div class="hint">Masukkan nilai dari 0,00 sampai 4,00.</div></div>
            </div>
            <div class="note"><span>ⓘ</span><div>Perubahan akan langsung diterapkan setelah tombol <b>Simpan Perubahan</b> ditekan. Periksa kembali tanggal lahir dan nilai IPK sebelum menyimpan.</div></div>
            <div class="actions"><a class="btn btn-light" href="student_view.php">Batal</a><button class="btn btn-primary" type="submit" name="submit" value="1">✓ &nbsp; Simpan Perubahan</button></div>
          </form>
        </div>
      </section>
      <div class="footer">StudentHub · Sistem Pengelolaan Data Mahasiswa</div>
    </div>
  </main>
</div>
<script>
document.getElementById('gpa').addEventListener('input', function () {
  if (this.value !== '' && Number(this.value) > 4) this.setCustomValidity('IPK maksimal 4,00.');
  else if (this.value !== '' && Number(this.value) < 0) this.setCustomValidity('IPK tidak boleh kurang dari 0.');
  else this.setCustomValidity('');
});
</script>
</body>
</html>
<?php mysqli_close($connection); ?>
