<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$worksFile = __DIR__ . '/../works.json';
$works = [];
if (file_exists($worksFile)) {
    $works = json_decode(file_get_contents($worksFile), true);
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$editId = $_GET['id'];
$workToEdit = null;
$workIndex = null;
foreach ($works as $index => $work) {
    if ($work['id'] == $editId) {
        $workToEdit = $work;
        $workIndex = $index;
        break;
    }
}
if (!$workToEdit) {
    header("Location: dashboard.php");
    exit;
}

$availableCategories = ['Cerpen', 'Ilmiyah', 'Lainnya'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    // Ambil kategori yang dipilih
    $selectedCategories = isset($_POST['categories']) ? $_POST['categories'] : [];

    // Proses input genre: hanya jika "Cerpen" ada di kategori dan input genre tidak kosong
    $genreArray = [];
    if (in_array('Cerpen', $selectedCategories) && isset($_POST['genre_input']) && trim($_POST['genre_input']) !== "") {
        $genreArray = array_map(function ($genre) {
            return ucwords(strtolower(trim($genre)));
        }, explode(',', $_POST['genre_input']));
    }

    // Update data karya pada indeks yang sesuai
    $works[$workIndex]['categories'] = $selectedCategories;
    $works[$workIndex]['genre'] = in_array('Cerpen', $selectedCategories) ? $genreArray : [];

    // Simpan kembali ke file JSON
    file_put_contents($worksFile, json_encode($works, JSON_PRETTY_PRINT));
    $msg = "Data karya berhasil diperbarui!";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Karya</title>
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

        label {
            display: block;
            margin: 10px 0 5px;
        }

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

        a {
            text-decoration: none;
            color: #007BFF;
        }
    </style>
</head>

<body>
    <h1>Edit Karya</h1>
    <?php if ($msg): ?>
        <p><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>
    <div class="form-container">
        <!-- Tampilkan judul karya (tidak bisa diedit) -->
        <p><strong>Judul:</strong> <?php echo htmlspecialchars($workToEdit['title']); ?></p>
        <!-- Tampilkan kategori yang sudah dipilih -->
        <p><strong>Kategori Saat Ini:</strong> <?php echo htmlspecialchars(implode(', ', $workToEdit['categories'])); ?></p>
        <!-- Form Edit -->
        <form method="POST" action="">
            <div class="checkbox-group">
                <?php foreach ($availableCategories as $cat): ?>
                    <label>
                        <input type="checkbox" name="categories[]" value="<?php echo $cat; ?>" <?php echo in_array($cat, $workToEdit['categories']) ? 'checked' : ''; ?>>
                        <?php echo $cat; ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div>
                <label>Genre (pisahkan dengan koma, hanya jika Cerpen):</label>
                <input type="text" name="genre_input" placeholder="Masukkan genre jika Cerpen" value="<?php echo (in_array('Cerpen', array_map('strtolower', $workToEdit['categories'])) && isset($workToEdit['genre'])) ? htmlspecialchars(implode(', ', $workToEdit['genre'])) : ''; ?>">
            </div>
            <input type="submit" name="edit" value="Update">
        </form>
        <p><a href="dashboard.php">Kembali ke Dashboard</a></p>
    </div>
</body>

</html>