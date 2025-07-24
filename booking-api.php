<?php
// booking-api-mysql.php
header('Content-Type: application/json');
require_once __DIR__ . '/db-mysql.php';
$conn = getMysqlConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Gagal koneksi database']);
    exit;
}
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
if ($action === 'save') {
    $input = json_decode(file_get_contents('php://input'), true);
    $date = $input['date'] ?? '';
    $hour = $input['hour'] ?? '';
    $unit = $input['unit'] ?? '';
    $name = $input['name'] ?? '';
    $hourEnd = $input['hourEnd'] ?? '';
    $whatsapp = $input['whatsapp'] ?? '';
    // Validasi: unit yang sama tidak boleh punya 2 booking dalam 1 minggu yang sama
    if ($unit && $date) {
        $week = date('W', strtotime($date));
        $year = date('o', strtotime($date));
        $stmt = $conn->prepare('SELECT COUNT(*) FROM bookings WHERE unit = ? AND YEAR(date) = ? AND WEEK(date, 1) = ?');
        $stmt->bind_param('sii', $unit, $year, $week);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count >= 2 || ($count >= 1 && $hourEnd !== '')) {
            echo json_encode(['success' => false, 'error' => 'Total booking lebih dari 2 dalam minggu yang sama']);
            exit;
        }
    }
    if ($date && $hour !== '' && $unit && $name && $whatsapp) {
        // Upsert booking
        $stmt = $conn->prepare('INSERT INTO bookings (date, hour, unit, name, whatsapp) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), whatsapp=VALUES(whatsapp)');
        $stmt->bind_param('sisss', $date, $hour, $unit, $name, $whatsapp);
        $ok = $stmt->execute();
        $stmt->close();
        if ($ok) {
            if ($hourEnd!='') {
                $stmt = $conn->prepare('INSERT INTO bookings (date, hour, unit, name, whatsapp) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), whatsapp=VALUES(whatsapp)');
                $stmt->bind_param('sisss', $date, $hourEnd, $unit, $name, $whatsapp);
                $ok = $stmt->execute();
                $stmt->close();
                if ($ok) {
                    echo json_encode(['success' => true]);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal simpan data']);
                    exit;
                }
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Gagal simpan data']);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Input tidak valid']);
        exit;
    }
} elseif ($action === 'load') {
    // Tambahan: filter hari ini jika ada parameter today=1
    $where = '';
    $params = [];
    $types = '';
    if (isset($_GET['today']) && $_GET['today'] == '1') {
        $today = date('Y-m-d');
        $where = 'WHERE date = ?';
        $params[] = $today;
        $types .= 's';
    }
    $sql = 'SELECT date, hour, unit, name, whatsapp FROM bookings ' . $where;
    $stmt = $conn->prepare($sql);
    if ($where) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $key = $row['date'] . '_' . str_pad($row['hour'], 2, '0', STR_PAD_LEFT) . '_' . $row['unit'];
        $bookings[$key] = [
            'name' => $row['name'],
            'whatsapp' => $row['whatsapp'],
            'unit' => $row['unit'],
            'date' => $row['date'],
            'hour' => $row['hour']
        ];
    }
    $stmt->close();
    echo json_encode(['success' => true, 'bookings' => $bookings]);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}
