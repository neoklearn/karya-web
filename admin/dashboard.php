<?php
// public/admin/dashboard.php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: login.php");
  exit;
}

// Path ke file JSON dan folder uploads (di public)
$worksFile = __DIR__ . '/../works.json';
$uploadDir = __DIR__ . '/../uploads/';

// Pastikan folder uploads ada
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

// Ambil data karya dari works.json
$works = [];
if (file_exists($worksFile)) {
  $works = json_decode(file_get_contents($worksFile), true);
}

// Daftar kategori yang tersedia
$availableCategories = ['Cerpen', 'Ilmiyah', 'Lainnya'];
$msg = '';
$autoLogout = false;

// Proses upload karya
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload'])) {
  if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
    $tmpFile = $_FILES['pdf_file']['tmp_name'];
    $origFileName = $_FILES['pdf_file']['name'];
    $fileParts = explode('.', $origFileName);
    $ext = strtolower(end($fileParts));

    if ($ext !== 'pdf') {
      $msg = "Hanya file PDF yang diperbolehkan.";
    } else {
      $newFileName = uniqid() . '.pdf';
      $destPath = $uploadDir . $newFileName;
      if (move_uploaded_file($tmpFile, $destPath)) {
        // Ambil judul dari nama file asli tanpa ekstensi .pdf
        $title = preg_replace('/\.pdf$/i', '', $origFileName);
        $selectedCategories = isset($_POST['categories']) ? $_POST['categories'] : [];
        $genreArray = [];
        if (in_array('Cerpen', $selectedCategories) && isset($_POST['genre_input']) && trim($_POST['genre_input']) !== '') {
          // Pecah input genre dan kapitalisasi setiap kata
          $genreArray = array_map(function ($genre) {
            return ucwords(strtolower(trim($genre)));
          }, explode(',', $_POST['genre_input']));
        }
        $newWork = [
          'id'         => uniqid(),
          'title'      => $title,
          // Simpan path file relatif terhadap public (untuk link download)
          'file'       => 'uploads/' . $newFileName,
          'categories' => $selectedCategories,
          'genre'      => in_array('Cerpen', $selectedCategories) ? $genreArray : []
        ];
        $works[] = $newWork;
        file_put_contents($worksFile, json_encode($works, JSON_PRETTY_PRINT));
        $msg = "Karya berhasil diupload!";
        $autoLogout = true;
      } else {
        $msg = "Terjadi kesalahan saat mengupload file.";
      }
    }
  } else {
    $msg = "File tidak ditemukan atau terjadi error saat upload.";
  }
}

// Jika auto logout aktif, hancurkan sesi dan redirect ke index.php
if ($autoLogout) {
  session_destroy();
  header("Location: ../index.php?msg=auto_logout");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /* Non-selectable untuk seluruh halaman */
    html,
    body {
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
      font-family: Arial, sans-serif;
      background: #fff;
      margin: 0;
      padding: 20px;
    }

    h1 {
      margin-top: 0;
    }

    .form-container {
      border: 1px solid #ccc;
      padding: 15px;
      background: #f9f9f9;
      margin-bottom: 20px;
    }

    input[type="file"],
    input[type="text"] {
      padding: 8px;
      margin: 5px 0;
      width: 100%;
      box-sizing: border-box;
    }

    .checkbox-group {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
    }

    .checkbox-group label {
      margin-right: 10px;
    }

    input[type="submit"] {
      padding: 10px 15px;
      background: #007BFF;
      color: #fff;
      border: none;
      cursor: pointer;
      margin-top: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    table,
    th,
    td {
      border: 1px solid #ccc;
    }

    th,
    td {
      padding: 10px;
      text-align: left;
    }

    .action-links a {
      color: #007BFF;
      text-decoration: none;
      margin-right: 10px;
    }

    /* Navigation bar fixed di bawah dengan tombol Logout */
    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background: #007BFF;
      text-align: center;
      padding: 10px 0;
      z-index: 1000;
    }

    .bottom-nav a.logout-btn {
      color: #fff;
      text-decoration: none;
      font-size: 18px;
      font-weight: bold;
    }

    @media (max-width: 600px) {
      .checkbox-group {
        flex-direction: column;
      }
    }
  </style>
  <script>
    // Cegah penggunaan tombol back (back button)
    history.pushState(null, null, window.location.href);
    window.addEventListener('popstate', function(event) {
      alert("Anda tidak diperbolehkan kembali ke laman sebelumnya.");
      history.pushState(null, null, window.location.href);
    });
  </script>
</head>

<body>
  <h1>Dashboard Admin</h1>
  <?php if (isset($msg) && $msg != ""): ?>
    <p><?php echo htmlspecialchars($msg); ?></p>
  <?php endif; ?>
  <div class="form-container">
    <h2>Upload Karya</h2>
    <form method="POST" action="" enctype="multipart/form-data">
      <label>File PDF:</label>
      <input type="file" name="pdf_file" accept="application/pdf" required>
      <label>Pilih Kategori:</label>
      <div class="checkbox-group">
        <?php foreach ($availableCategories as $cat): ?>
          <label>
            <input type="checkbox" name="categories[]" value="<?php echo $cat; ?>" <?php if ($cat === 'Cerpen') echo 'id="cat_cerpen"'; ?>>
            <?php echo $cat; ?>
          </label>
        <?php endforeach; ?>
      </div>
      <div id="genreInputContainer" style="display: none;">
        <label>Genre (pisahkan dengan koma):</label>
        <input type="text" name="genre_input" placeholder="Masukkan genre jika Cerpen">
      </div>
      <input type="submit" name="upload" value="Upload">
    </form>
  </div>
  <h2>Daftar Karya</h2>
  <?php if (!empty($works)): ?>
    <table>
      <tr>
        <th>Judul</th>
        <th>Kategori</th>
        <th>Genre</th>
        <th>Aksi</th>
      </tr>
      <?php foreach ($works as $work): ?>
        <tr>
          <td><?php echo htmlspecialchars($work['title']); ?></td>
          <td><?php echo htmlspecialchars(implode(', ', $work['categories'])); ?></td>
          <td>
            <?php
            // Periksa apakah kategori mencakup "cerpen" (case-insensitive) dan ada genre
            $lowerCategories = array_map('strtolower', $work['categories']);
            if (in_array('cerpen', $lowerCategories) && !empty($work['genre'])) {
              echo htmlspecialchars(implode(', ', $work['genre']));
            } else {
              echo '-';
            }
            ?>
          </td>
          <td class="action-links">
            <a href="edit.php?id=<?php echo $work['id']; ?>">Edit</a>
            <a href="delete.php?id=<?php echo $work['id']; ?>" onclick="return confirm('Yakin hapus karya ini?');">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>Tidak ada karya yang diupload.</p>
  <?php endif; ?>

  <!-- Navigation Bar Fixed di Bawah dengan Tombol Logout -->
  <nav class="bottom-nav">
    <a class="logout-btn" href="../admin/logout.php">Logout</a>
  </nav>

  <script>
    // Tampilkan/matikan field Genre saat checkbox Cerpen dicek
    const cerpenCheckbox = document.getElementById('cat_cerpen');
    const genreInputContainer = document.getElementById('genreInputContainer');
    if (cerpenCheckbox) {
      cerpenCheckbox.addEventListener('change', function() {
        genreInputContainer.style.display = this.checked ? 'block' : 'none';
      });
    }
  </script>
</body>

</html>