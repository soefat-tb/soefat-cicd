<?php
session_start();
require 'config/database.php';

/* ================== */
/* PHP SECURITY ENHANCEMENTS */
/* ================== */
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'sudo') {
    $_SESSION['access_denied'] = true;
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['reset_password'])) {
            $nis = filter_input(INPUT_POST, 'nis', FILTER_SANITIZE_NUMBER_INT);
            $newPassword = $nis;
            $stmt = $koneksi->prepare("UPDATE login SET password = ?, last_updated = NOW() WHERE nis = ?");
            $stmt->bind_param("ss", $newPassword, $nis);
            if ($stmt->execute()) {
                $success = "Password untuk NIS $nis berhasil direset menjadi <strong>$newPassword</strong>";
            } else {
                $error = "Gagal mereset password: " . $stmt->error;
            }
            $stmt->close();
        }

        if (isset($_POST['delete_user'])) {
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
            $stmt = $koneksi->prepare("DELETE FROM login WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $success = "User berhasil dihapus!";
            } else {
                $error = "Gagal menghapus user: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    $search = isset($_GET['search']) ? "%".filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING)."%" : '%';
    $stmt = $koneksi->prepare("SELECT id, nis, last_updated FROM login WHERE nis LIKE ? ORDER BY id DESC");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $error = $e->getMessage();
}

if ($error) {
    $_SESSION['error'] = $error;
} elseif ($success) {
    $_SESSION['success'] = $success;
}

if ($error || $success) {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>User Management - Soefat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c8030;
            --secondary-color: #27ae60;
            --bg-color: #ffffff;
            --text-color: #333;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --spacing: 1rem;
            --border-radius: 8px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-color);
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            color: var(--text-color);
        }

        .app-container {
            flex: 1;
            background: var(--bg-color);
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            overflow-y: auto;
            padding-bottom: var(--spacing);
        }

        .header {
            background: var(--primary-color);
            color: white;
            padding: var(--spacing);
            position: sticky;
            top: 0;
            z-index: 20;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .header-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .content {
            padding: var(--spacing);
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .card {
            background: var(--bg-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: var(--spacing);
            margin-bottom: var(--spacing);
        }

        .card-header {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text-color);
        }

        .input-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: var(--spacing);
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            padding: 0.75rem;
            font-size: 1rem;
            width: 100%;
            box-sizing: border-box;
            min-height: 50px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(44, 128, 48, 0.3);
            outline: none;
        }

        .btn {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius);
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            cursor: pointer;
            min-height: 50px;
            width: auto;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background-color: var(--secondary-color);
        }

        .btn-delete {
            background-color: #dc3545;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .table-container {
            overflow-x: auto;
            margin-top: var(--spacing);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .data-table th {
            background: var(--bg-color);
            font-weight: 600;
        }

        .data-table tr:hover {
            background: #f9f9f9;
        }

        .badge {
            background: #e9ecef;
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
        }

        .bottom-nav {
            background: var(--bg-color);
            padding: 0.5rem 0;
            display: flex;
            justify-content: space-around;
            box-shadow: var(--shadow);
            position: sticky;
            bottom: 0;
            z-index: 10;
            border-top: 1px solid #ddd;
        }

        .nav-item {
            text-align: center;
            color: #666;
            text-decoration: none;
            flex: 1;
            padding: 0.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.9rem;
        }

        .nav-item i {
            font-size: 1.2rem;
            margin-bottom: 0.25rem;
        }

        .nav-item.active {
            color: var(--primary-color);
        }

        .nav-item.active i {
            color: var(--primary-color);
        }

        @media (max-width: 576px) {
            .content {
                padding: 0.75rem;
            }
            .card {
                padding: 0.75rem;
                margin-bottom: 0.75rem;
            }
            .input-group {
                flex-direction: column;
                gap: 0.75rem;
            }
            .form-control, .btn, .btn-delete {
                width: 100%;
            }
            .data-table th,
            .data-table td {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
            .header-title {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="header">
            <div class="header-title">User Management</div>
        </div>

        <div class="content">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php elseif (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">Reset Password</div>
                <form method="POST" class="input-group">
                    <input type="text" name="nis" class="form-control" placeholder="Masukkan NIS" pattern="\d+" required>
                    <button type="submit" name="reset_password" class="btn">Reset Password</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">Daftar Pengguna</div>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan NIS..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>NIS</th>
                                <th>Terakhir Update</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['nis']) ?></td>
                                    <td>
                                        <?php if ($row['last_updated']): ?>
                                            <span class="badge"><?= date('d M Y H:i', strtotime($row['last_updated'])) ?></span>
                                        <?php else: ?>
                                            <span>Belum pernah diupdate</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                            <button type="submit" name="delete_user" class="btn btn-delete">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bottom-nav">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Beranda</span>
            </a>
            <a href="user-manager.php" class="nav-item active">
                <i class="fas fa-users"></i>
                <span>Pengguna</span>
            </a>
            <a href="schedule-manager.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Jadwal</span>
            </a>
            <a href="../logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['error'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?= addslashes($_SESSION['error']) ?>',
                    confirmButtonColor: '#2c8030',
                    confirmButtonText: 'OK'
                });
                <?php unset($_SESSION['error']); ?>
            <?php elseif (isset($_SESSION['success'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses',
                    text: '<?= addslashes($_SESSION['success']) ?>',
                    confirmButtonColor: '#2c8030',
                    confirmButtonText: 'OK'
                });
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            const debounce = (func, delay) => {
                let timeout;
                return (...args) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func(...args), delay);
                };
            };

            document.querySelector('input[name="search"]')?.addEventListener('input', debounce(function(e) {
                const filter = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('.data-table tbody tr');
                rows.forEach(row => {
                    const nis = row.cells[1].textContent.toLowerCase();
                    row.style.display = nis.includes(filter) ? '' : 'none';
                });
            }, 300));
        });
    </script>
</body>
</html>