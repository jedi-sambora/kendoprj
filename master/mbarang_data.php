<?php
// koneksi db
include("../lib/koneksi.inc.php");
include("../lib/umum.inc.php");
include('../lib/adodb5/adodb.inc.php');
include("../lib/adodb5/tohtml.inc.php");

// Koneksi MySQL dengan ADODB

$conn = ADONewConnection('mysqli');
$conn->Connect($server, $user, $pwd, $dbx);;

$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

$sql = "SELECT * FROM m_barang WHERE nm_barang LIKE ?";
$rs = $conn->Execute($sql, array($search));

$data = array();
while (!$rs->EOF) {
    $data[] = array(
        'kode' => $rs->fields['kode'],
        'nm_barang' => $rs->fields['nm_barang'],
        'hargabeli' => (float)$rs->fields['hargabeli'],
        'hargajual' => (float)$rs->fields['hargajual'],
        'hargajual2' => (float)$rs->fields['hargajual2'],
        'hargajual3' => (float)$rs->fields['hargajual3']
    );
    $rs->MoveNext();
}

echo json_encode(array(
    "data" => $data,
    "total" => count($data)
));
