<?php
include("./lib/koneksi.inc.php");
include("./lib/umum.inc.php");
include('./lib/adodb5/adodb.inc.php');
include("./lib/adodb5/tohtml.inc.php");

// Koneksi MySQL dengan ADODB
$db = NewADOConnection('mysqli');
$db->Connect($server, $user, $pwd, $dbx);

header('Content-Type: application/json');
$action = $_GET['action'] ?? 'read';

try {
    switch ($action) {
        case 'read':
            $data = $db->GetAll("SELECT kode,nm_barang,hargabeli,hargajual,hargajual2,hargajual3
            FROM m_barang");
            echo json_encode(['data' => $data]);
            break;

        case 'create':
            $input = json_decode($_POST['data'], true);
            $db->Execute(
                "INSERT INTO m_barang (kode, nm_barang, hargabeli, hargajual, hargajual2, hargajual3) 
                VALUES (?, ?, ?, ?, ?, ?)",
                [
                    uniqid(), // Generate kode unik (sesuaikan jika perlu)
                    $input['nm_barang'],
                    $input['hargabeli'],
                    $input['hargajual'],
                    $input['hargajual2'],
                    $input['hargajual3']
                ]
            );
            echo json_encode(['success' => true]);
            break;

        case 'update':
            $input = json_decode($_POST['data'], true);
            $db->Execute(
                "UPDATE m_barang SET 
                nm_barang = ?, 
                hargabeli = ?, 
                hargajual = ?, 
                hargajual2 = ?, 
                hargajual3 = ? 
                WHERE kode = ?",
                [
                    $input['nm_barang'],
                    $input['hargabeli'],
                    $input['hargajual'],
                    $input['hargajual2'],
                    $input['hargajual3'],
                    $input['kode']
                ]
            );
            echo json_encode(['success' => true]);
            break;

        case 'delete':
            $input = json_decode($_POST['data'], true);
            $db->Execute("DELETE FROM m_barang WHERE kode = ?", [$input['kode']]);
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
