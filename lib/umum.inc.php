<?php
include("koneksi.inc.php");
include('adodb5/adodb.inc.php');


// Setup database connection
$conn = NewADOConnection('mysqli');
$conn->PConnect($server, $user, $pwd, $dbx);
$key = "kuncirahasia123";
$arr_bulan = [
  1 => "Januari",
  "Februari",
  "Maret",
  "April",
  "Mei",
  "Juni",
  "Juli",
  "Agustus",
  "September",
  "Oktober",
  "November",
  "Desember"
];

function getstock_pertgl($conn, $kd_brg, $kd_sat, $tanggal_awal)
{
  $a = prdlalu($tanggal_awal);
  $bulan_lalu = $a[0];
  $tahun_lalu = $a[1];

  $tanggal = new DateTime($tanggal_awal);
  $bulan = $tanggal->format('m');
  $tahun = $tanggal->format('Y');

  // Get original unit from item master
  $rslb = $conn->Execute("SELECT kode, kd_satuan FROM m_barang WHERE kode = ?", [$kd_brg]);
  $kd_satori = $rslb->fields[1];

  // Check for unit conversion
  $kd_satkonv = '';
  $pengali = 1;

  if ($kd_sat == $kd_satori) {
    $rs = $conn->Execute("SELECT sat_tuju, pengali FROM m_konversi WHERE sat_asal = ?", [$kd_satori]);

    if ($rs && $rs->RecordCount() > 0) {
      $kd_satkonv = $rs->fields[0];
      $pengali = $rs->fields[1];
    }
  } else {
    $rs = $conn->Execute(
      "SELECT sat_tuju, pengali FROM m_konversi WHERE sat_asal = ? AND sat_tuju = ?",
      [$kd_satori, $kd_sat]
    );

    if ($rs && $rs->RecordCount() > 0) {
      $kd_satkonv = $rs->fields[0];
      $pengali = $rs->fields[1];
    }
  }
  // query old jika belum ada transaksi maka saldo awal tidak bisa tmpil
  /*
  $sql = "SELECT 
        IFNULL(sa.qty, 0) AS sa,
        COALESCE(SUM(CASE WHEN io.mvt IN ('101', '202') THEN io.qty ELSE 0 END), 0) AS masuk,
        COALESCE(SUM(CASE WHEN io.mvt IN ('201', '102') THEN io.qty ELSE 0 END), 0) AS keluar,
        IFNULL(sa.qty, 0) + (
            COALESCE(SUM(CASE WHEN io.mvt IN ('101', '202') THEN io.qty ELSE 0 END), 0) - 
            COALESCE(SUM(CASE WHEN io.mvt IN ('201', '102') THEN io.qty ELSE 0 END), 0)
        ) AS stok_tersedia   
    FROM t_journal io 
    LEFT OUTER JOIN t_saldo_awal sa 
        ON io.kd_brg = sa.kd_brg 
        AND sa.bulan = '$bulan_lalu'
        AND sa.tahun = '$tahun_lalu'
        AND io.kd_sat = sa.kd_sat
    WHERE io.kd_brg = '$kd_brg'
        AND MONTH(tgl) = '$bulan' 
        AND YEAR(tgl) = '$tahun' ";
   */
  // akhir query old
  $sql = "
  SELECT

  sa.qty AS saldo_awal,
  COALESCE(SUM(CASE WHEN io.mvt IN ('101', '202') THEN io.qty ELSE 0 END), 0) AS masuk,
  COALESCE(SUM(CASE WHEN io.mvt IN ('201', '102') THEN io.qty ELSE 0 END), 0) AS keluar,
  sa.qty + ( 
    COALESCE(SUM(CASE WHEN io.mvt IN ('101', '202') THEN io.qty ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN io.mvt IN ('201', '102') THEN io.qty ELSE 0 END), 0)
  ) AS stok_tersedia
FROM t_saldo_awal sa
LEFT JOIN t_journal io
  ON io.kd_brg = sa.kd_brg
  AND io.kd_sat = sa.kd_sat
  AND MONTH(io.tgl) = '$bulan'
  AND YEAR(io.tgl) = '$tahun'
WHERE sa.kd_brg = '$kd_brg'
    AND sa.bulan = '$bulan_lalu'
  AND sa.tahun = '$tahun_lalu'

  ";

  $group = "";
  // $group = " GROUP BY sa.kd_brg, sa.kd_sat, sa.qty ";
  $whori = "AND sa.kd_sat = '$kd_satori' $group "; // kd_sat original
  $sqlori = $sql . " " . $whori;

  // Execute query for original unit
  $rsl = $conn->Execute($sqlori);

  $hsl = [
    'saori' => $rsl->fields[0] ?? 0,
    'inori' => $rsl->fields[1] ?? 0,
    'outori' => $rsl->fields[2] ?? 0,
    'stokori' => $rsl->fields[3] ?? 0
  ];

  // Execute query for converted unit if needed
  if ($kd_satkonv != '') {
    $whkonv = "AND sa.kd_sat = '$kd_satkonv' $group"; // kd_sat converted
    $sqlkonv = $sql . " " . $whkonv;
    $rsl = $conn->Execute($sqlkonv);
    //$rsl = $conn->Execute($sql, [$bulan_lalu, $tahun_lalu, $kd_brg, $bulan, $tahun, $kd_satkonv]);

    $hsl['sakonv'] = $rsl->fields[0] ?? 0;
    $hsl['inkonv'] = $rsl->fields[1] ?? 0;
    $hsl['outkonv'] = $rsl->fields[2] ?? 0;
    $hsl['stokkonv'] = $rsl->fields[3] ?? 0;
  }

  // Calculate final stock
  if ($kd_sat != $kd_satkonv) {
    $a_ = ($kd_satkonv != '') ? ($hsl['sakonv'] / $pengali) : 0;
    $sa_ = $hsl['saori'] + $a_;
    $i_ = ($kd_satkonv != '') ? ($hsl['inkonv'] / $pengali) : 0;
    $in_ = $hsl['inori'] + $i_;
    $o_ = ($kd_satkonv != '') ? ($hsl['outkonv'] / $pengali) : 0;
    $o_ = $hsl['outori'] + $o_;
    $x = ($kd_satkonv != '') ? ($hsl['stokkonv'] / $pengali) : 0;
    $stock = $hsl['stokori'] + $x;
  } else {
    $sa_ = ($hsl['sakori'] * $pengali) + ($hsl['sakonv'] ?? 0);
    $in_ = ($hsl['inori'] * $pengali) + ($hsl['inkonv'] ?? 0);
    $o_ = ($hsl['outori'] * $pengali) + ($hsl['outkonv'] ?? 0);
    $stock = ($hsl['stokori'] * $pengali) + ($hsl['stokkonv'] ?? 0);
  }

  if ($kd_sat != $kd_satori) {
    if ($kd_satkonv == '') {
      $stock = 0;
    }
  }
  $hsl['sa'] = $sa_;
  $hsl['in'] = $i_;
  $hsl['out'] = $o_;
  $hsl['stock'] = $stock;
  return $hsl;
}



function get_plgtipe($conn, $kd_plg)
{
  $sql = "select tipe from m_pelanggan where kode='$kd_plg' ";
  $rsl =  $conn->Execute($sql);
  return $rsl->fields[0];
}
function get_harga_plg($conn, $kd_brg, $kd_sat, $kd_plg)
{
  $hargajual = array();
  $tipe_plg = get_plgtipe($conn, $kd_plg);

  $sql = "select hargabeli,hargajual,hargajual2,hargajual3,kd_satuan from m_barang where kode='$kd_brg' ";
  $rsl =  $conn->Execute($sql);
  $hargabeli = $rsl->fields[0];
  $hargajual[1] = $rsl->fields[1];
  $hargajual[2] = $rsl->fields[2];
  $hargajual[3] = $rsl->fields[3];
  $kd_satuan_ori = $rsl->fields[4];
  $hargajualnya = $hargajual[$tipe_plg];
  if ($kd_satuan_ori == $kd_sat) {
    $hargajualnya = $hargajual[$tipe_plg];
  } else {
    $rslkonv = $conn->Execute("select pengali from m_konversi where  sat_asal= '$kd_satuan_ori' and sat_tuju='$kd_sat'");
    if ($rslkonv->RecordCount() > 0) {
      $konv = $rslkonv->fields[0];
      $hargajualnya = $hargajual[$tipe_plg] / $konv;
    }
  }

  return $hargajualnya;
}



function getstockkonvz($conn, $kd_brg, $kd_sat)
{

  $tanggal_awal = date('Y-m-d');
  $a = prdlalu($tanggal_awal);
  $tanggal = new DateTime($tanggal_awal);

  $bulan = $tanggal->format('m');
  $tahun = $tanggal->format('Y');
  $sql = " select 
IFNULL(sa.qty, 0) AS sa,

-- Barang masuk (mvt 101 + 202)
COALESCE(SUM(CASE 
    WHEN io.mvt IN ('101', '202') THEN io.qty 
    ELSE 0 
END), 0) AS masuk,

-- Barang keluar (mvt 201 + 102)
COALESCE(SUM(CASE 
    WHEN io.mvt IN ('201', '102') THEN io.qty 
    ELSE 0 
END), 0) AS keluar,

-- Perhitungan stok
IFNULL(sa.qty, 0) +
  (COALESCE(SUM(CASE 
      WHEN io.mvt IN ('101', '202') THEN io.qty 
      ELSE 0 
  END), 0)
  - 
  COALESCE(SUM(CASE 
      WHEN io.mvt IN ('201', '102') THEN io.qty 
      ELSE 0 
  END), 0)
) AS stok_tersedia

    from t_journal io 
    left outer join t_saldo_awal sa 
    on io.kd_brg = sa.kd_brg 
    and sa.bulan ='$a[0]'
    and sa.tahun ='$a[1]' 
    where io.kd_brg ='$kd_brg' 
    and month(tgl) = '$bulan' 
    and year(tgl) = '$tahun'

    ";
  $rsl =  $conn->Execute($sql);
  $hsl[0] = $rsl->fields[0]; //saldo awal
  $hsl[1] = $rsl->fields[1]; //masuk
  $hsl[2] = $rsl->fields[2]; //keluar
  $hsl[3] = $rsl->fields[3]; //stock
  $rslkonv = $conn->Execute("select pengali from m_konversi where  sat_asal= '$kd_sat'");
  if ($rslkonv->RecordCount() > 0) {
    $hsl[3] = $rsl->fields[3]; //stock;
  } else {
    $rslb = $conn->Execute("select kode,kd_satuan from m_barang where kode ='$kd_brg' ");
    $kd_satasal = $rslb->fields[1];
    $rslkonv1 = $conn->Execute("select pengali from m_konversi where  sat_asal= '$kd_satasal' and sat_tuju='$kd_sat'");
    if ($rslkonv1->RecordCount() > 0) {
      $konv = $rslkonv1->fields[0];
      $hsl[3] = $rsl->fields[3] * $konv;
    }
  }

  return $hsl[3];
}


function generatekode($user_id, $trx)
{
  global $conn;
  $tanggal_transaksi = date("Ymd");
  $sqlx = "SELECT nomor_urut_terakhir FROM nomor_dokumen WHERE 
trx = '$trx' ";
  $result = $conn->Execute($sqlx);
  if ($result->RecordCount() > 0) {
    $nomor_urut = $result->fields['nomor_urut_terakhir'] + 1;
    $sql = "update nomor_dokumen set nomor_urut_terakhir = '$nomor_urut'
WHERE  trx = '$trx' ";
  } else {

    $nomor_urut = 1;
    $sql = "INSERT INTO nomor_dokumen ( tanggal, user,nomor_urut_terakhir,trx) 
VALUES ('$tanggal_transaksi', '$user_id','$nomor_urut','$trx')";
  }
  $rslx = $conn->Execute($sql);


  //echo $sql;

  $no_dokumen = $trx . sprintf("%03d", $nomor_urut);
  return $no_dokumen;
}

function getstock($kd_brg, $tanggal_awal)
{
  global $conn;
  $a = prdlalu($tanggal_awal);
  $tanggal = new DateTime($tanggal_awal);

  $bulan = $tanggal->format('m');
  $tahun = $tanggal->format('Y');
  $sql = "SELECT 
        IFNULL(sa.qty, 0) AS sa,

        -- Barang masuk (mvt 101 + 202)
        COALESCE(SUM(CASE 
            WHEN io.mvt IN ('101', '202') THEN io.qty 
            ELSE 0 
        END), 0) AS masuk,

        -- Barang keluar (mvt 201 + 102)
        COALESCE(SUM(CASE 
            WHEN io.mvt IN ('201', '102') THEN io.qty 
            ELSE 0 
        END), 0) AS keluar,

        -- Perhitungan stok
        IFNULL(sa.qty, 0) +
          (COALESCE(SUM(CASE 
              WHEN io.mvt IN ('101', '202') THEN io.qty 
              ELSE 0 
          END), 0)
          - 
          COALESCE(SUM(CASE 
              WHEN io.mvt IN ('201', '102') THEN io.qty 
              ELSE 0 
          END), 0)
        ) AS stok_tersedia
        from t_journal io 
        left outer join t_saldo_awal sa 
          on io.kd_brg = sa.kd_brg 
          and sa.bulan ='$a[0]'
        and sa.tahun ='$a[1]' 
        where io.kd_brg ='$kd_brg' 
        and month(tgl) = '$bulan' 
        and year(tgl) = '$tahun'

";
  $rsl =  $conn->Execute($sql);
  $hsl[] = $rsl->fields[0]; //saldo awal
  $hsl[] = $rsl->fields[1]; //masuk
  $hsl[] = $rsl->fields[2]; //keluar
  $hsl[] = $rsl->fields[3]; //stock
  return $hsl;
}

function prdlalu($tanggal_awal)
{
  //$tanggal_awal = '2024-12-05';

  // Membuat objek DateTime
  $tanggal = new DateTime($tanggal_awal);

  // Mengurangi 1 bulan
  $tanggal->modify('-1 month');

  // Mendapatkan bulan dan tahun saja
  $bulan = $tanggal->format('m'); // Output: 11
  $tahun = $tanggal->format('Y'); // Output: 2024
  $arrprd[] = $bulan;
  $arrprd[] = $tahun;
  // Menampilkan hasil
  return $arrprd;
}
function prdaktif()
{
  global $conn;
  $prdaktif = $conn->Execute("select MONTHNAME(STR_TO_DATE(bulan, '%m')) AS bulan,tahun
     ,bulan as bulan2 from m_periode
   where aktif = 'Y' ");
  $nmbl = $prdaktif->fields['bulan'];
  $tha = $prdaktif->fields['tahun'];
  $nmbl = date('F', mktime(0, 0, 0, $prdaktif->fields['bulan2'], 1));
  $thbl = $nmbl . ' - ' . $tha;
  return $thbl;
}

function lognya($msg)
{  //menulis log ke dalam file error.log
  // Lokasi file log berada di folder logs pada root proyek
  $log_file = dirname(__DIR__) . "/logs/error.log"; // dirname(__DIR__) naik 1 level dari folder transaksi

  // Pastikan folder logs sudah ada
  if (!file_exists(dirname($log_file))) {
    mkdir(dirname($log_file), 0777, true);
  }

  $fp = fopen($log_file, 'w'); // Buka file dalam mode tulis
  fwrite($fp, ''); // Hapus isi file
  fwrite($fp, $msg); // Tulis pesan log
  fclose($fp);
}
function angkamysql($angka)
{  //echo "oke";
  $angka_tanpa_koma = str_replace(".", "", $angka); // Menghilangkan titik
  $hasil = str_replace(",", ".", $angka_tanpa_koma); // Mengganti koma dengan titik
  return  $hasil;
}

function idtbl($usx)
{
  $timestamp = time(); // Mengubah timestamp menjadi string 
  $timestampStr = (string)$timestamp; // Mengambil 10 karakter pertama dari timestamp 
  $timestampChar10 = substr($timestampStr, 0, 10); // Menambahkan kata "user" ke awal 10 karakter dari timestamp
  $userString =  $usx . $timestampChar10; // Mengacak karakter dalam $userString 
  $rdmx = str_shuffle($userString);
  return $rdmx;
}

function cleartemp_journal($kd_usr, $trx)
{
  global $conn;
  //  $sql = "delete from temp_journal where id = '$idtbl' ";
  $sql = "delete from temp_journal where kd_usr = '$kd_usr' 
            and trx like  '$trx' ";
  //$conn->debug=true;
  $x = $conn->Execute($sql);
}

function copy2journal_sell($kd_usr, $trx, $nodoc)
{
  global $conn;
  try {
    $conn->StartTrans();
    $rows = $conn->GetAll("SELECT * FROM temp_journal 
      where kd_usr = '$kd_usr' and trx = '$trx' ");
    if ($rows) {
      foreach ($rows as $row) {
        $sql = "
          INSERT INTO t_journal 
          (`kd_brg`, `kd_usr`, `qty`, `kd_sat`, `tgl`, `mvt`, 
           `no_doc`, `ref_doc`,  `kd_supcust`, `hargajual`, 
           `tgl_update`)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?,  ?, ?, ?)";

        $result = $conn->Execute($sql, [
          $row['kd_brg'],
          $row['kd_usr'],
          $row['qty'],
          $row['kd_sat'],
          $row['tgl'],
          $row['mvt'],
          $nodoc,
          $row['ref_doc'],
          $row['kd_supcust'],
          $row['hargajual'],
          $row['tgl_update']
        ]);
      } // foreach
      // Commit transaksi
      $rsdel  = $conn->Execute("delete from temp_journal where kd_usr = '$kd_usr' and trx = '$trx'  ");
      $conn->CompleteTrans();
      $pesan = "Data berhasil di simpan.";
      $sukses = true;
    } //($rows)

  } catch (Exception $e) {
    // Rollback jika terjadi kesalahan
    $conn->FailTrans();
    $conn->CompleteTrans();
    $sukses = false;
    $pesan = "Gagal Simpan : " . $conn->ErrorMsg();
  }
  if ($sukses == true)
    $rs_ = $conn->Execute("delete from temp_journal kd_usr = '$kd_usr' and trx = '$trx'  ");
  // $hsl =  json_encode(["success" => $sukses, "message" => $pesan]);
  $hsl =  $pesan;
  return $hsl;
}

function copy2journal_02($kd_usr, $trx, $idtbl)
{
  global $conn;
  try {
    $conn->StartTrans();
    $rows = $conn->GetAll("SELECT * FROM temp_journal 
      where kd_usr = '$kd_usr' and trx = '$trx' ");
    if ($rows) {
      foreach ($rows as $row) {
        $sql = "
          INSERT INTO t_journal 
          (`kd_brg`, `kd_usr`, `qty`, `kd_sat`, `tgl`, `mvt`, 
           `no_doc`, `ref_doc`, `hargabeli`, `kd_supcust`, `hargajual`, 
           `tgl_update`)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $result = $conn->Execute($sql, [
          $row['kd_brg'],
          $row['kd_usr'],
          $row['qty'],
          $row['kd_sat'],
          $row['tgl'],
          $row['mvt'],
          $row['no_doc'],
          $row['ref_doc'],
          $row['hargabeli'],
          $row['kd_supcust'],
          $row['hargajual'],
          $row['tgl_update']
        ]);
        $kd_brg = $row['kd_brg'];
        $hargabeli = $row['hargabeli'];
        $hargajual = $row['hargajual'];
        $rsb = $conn->Execute("update m_barang set hargajual = '$hargajual',
                        hargabeli = '$hargabeli'
                        where kode = '$kd_brg' ");
      } // foreach
      // Commit transaksi
      $rsdel  = $conn->Execute("delete from temp_journal where kd_usr = '$kd_usr' and trx = '$trx'  ");
    } //($rows)

  } catch (Exception $e) {
    // Rollback jika terjadi kesalahan
    $conn->FailTrans();
    $conn->CompleteTrans();
    $sukses = false;
    $sql = "Gagal SimpanX : " . $conn->ErrorMsg();
  }
  if ($sukses == true)
    $rs_ = $conn->Execute("delete from temp_journal where id='$idtbl' ");
  $hsl =  json_encode(["success" => $sukses, "message" => $sql]);
  return $hsl;
}

function generatenodok($tanggal_transaksi, $user_id, $trx)
{
  global $conn;

  if ($trx <> 'BRG') {
    $sqlx = "SELECT nomor_urut_terakhir FROM nomor_dokumen WHERE tanggal = '$tanggal_transaksi'
               and trx = '$trx' ";
    $result = $conn->Execute($sqlx);
    if ($result->RecordCount() > 0) {
      $nomor_urut = $result->fields['nomor_urut_terakhir'] + 1;
      $sql = "update nomor_dokumen set nomor_urut_terakhir = '$nomor_urut'
       WHERE tanggal = '$tanggal_transaksi'
               and trx = '$trx' ";
    } else {

      $nomor_urut = 1;
      $sql = "INSERT INTO nomor_dokumen (tanggal, user, nomor_urut_terakhir,trx) 
                VALUES ('$tanggal_transaksi', '$user_id','$nomor_urut','$trx')";
    }
    $rslx = $conn->Execute($sql);
    $no_dokumen = $trx . sprintf("%02d", $user_id) . "-" . date('Ymd', strtotime($tanggal_transaksi)) . "-" . sprintf("%04d", $nomor_urut);
  } else {
    $tanggal_transaksi = date("Ymd");
    //$user_id = 'xx';
    $sqlx = "SELECT nomor_urut_terakhir FROM nomor_dokumen WHERE 
               trx = 'BRG' ";
    $result = $conn->Execute($sqlx);
    if ($result->RecordCount() > 0) {
      $nomor_urut = $result->fields['nomor_urut_terakhir'] + 1;
      $sql = "update nomor_dokumen set nomor_urut_terakhir = '$nomor_urut'
       WHERE  trx = '$trx' ";
    } else {

      $nomor_urut = 1;
      $sql = "INSERT INTO nomor_dokumen ( tanggal, user,nomor_urut_terakhir,trx) 
                VALUES ('$tanggal_transaksi', '$user_id','$nomor_urut','$trx')";
    }
    $rslx = $conn->Execute($sql);


    //echo $sql;
    $no_dokumen = 'TSP' . sprintf("%04d", $nomor_urut);
  }

  return $no_dokumen;
}
