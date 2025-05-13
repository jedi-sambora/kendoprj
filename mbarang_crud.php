<?php
// koneksi db
include("./lib/koneksi.inc.php");
include("./lib/umum.inc.php");
include('./lib/adodb5/adodb.inc.php');
include("./lib/adodb5/tohtml.inc.php");

// Koneksi MySQL dengan ADODB
$conn = ADONewConnection('mysqli');
$conn->Connect($server, $user, $pwd, $dbx);

$action = isset($_POST['action']) ? $_POST['action'] : '';

$kode = isset($_POST['kode']) ? trim($_POST['kode']) : '';
$nm_barang = isset($_POST['nm_barang']) ? trim($_POST['nm_barang']) : '';
$hargabeli = isset($_POST['hargabeli']) ? (float)$_POST['hargabeli'] : 0;
$hargajual = isset($_POST['hargajual']) ? (float)$_POST['hargajual'] : 0;
$hargajual2 = isset($_POST['hargajual2']) ? (float)$_POST['hargajual2'] : 0;
$hargajual3 = isset($_POST['hargajual3']) ? (float)$_POST['hargajual3'] : 0;

if ($action == 'delete') {
    $sql = "DELETE FROM m_barang WHERE kode = ?";
    $conn->Execute($sql, array($kode));
    echo "Data berhasil dihapus.";
} else {
    // validasi sederhana
    if ($kode == '' || $nm_barang == '' || $hargabeli <= 0 || $hargajual <= 0) {
        echo "Isi data dengan benar.";
        exit;
    }

    // cek ada / tidak
    $cek = $conn->GetOne("SELECT count(*) FROM m_barang WHERE kode = ?", array($kode));
    if ($cek > 0) {
        // update
        $sql = "UPDATE m_barang SET nm_barang=?, hargabeli=?, hargajual=?, hargajual2=?, hargajual3=? WHERE kode=?";
        $conn->Execute($sql, array($nm_barang, $hargabeli, $hargajual, $hargajual2, $hargajual3, $kode));
        echo "Data berhasil diupdate.";
    } else {
        // insert
        $sql = "INSERT INTO m_barang (kode, nm_barang, hargabeli, hargajual, hargajual2, hargajual3) VALUES (?, ?, ?, ?, ?, ?)";
        $conn->Execute($sql, array($kode, $nm_barang, $hargabeli, $hargajual, $hargajual2, $hargajual3));
        echo "Data berhasil disimpan.";
    }
}
