<?php
include("./lib/koneksi.inc.php");
include("./lib/umum.inc.php");
include('./lib/adodb5/adodb.inc.php');
include("./lib/adodb5/tohtml.inc.php");

// Koneksi MySQL dengan ADODB
$conn = ADONewConnection('mysqli');
$conn->Connect($server, $user, $pwd, $dbx);

$rs = $conn->Execute("SELECT id, parent_id, name, url, icon FROM menu 
where tampil = 'Y' ORDER BY order_num");

$items = [];
while (!$rs->EOF) {
    $items[] = [
        "id" => $rs->fields['id'],
        "parent_id" => $rs->fields['parent_id'],
        "text" => $rs->fields['name'],
        "myurl" => $rs->fields['url'],
        "icon" => $rs->fields['icon']
    ];
    $rs->MoveNext();
}

// Fungsi rekursif untuk membentuk tree
function buildTree(array $elements, $parentId = null, $depth = 0)
{
    $branch = [];

    foreach ($elements as $element) {
        if ($element['parent_id'] == $parentId) {
            $children = ($depth < 1) ? buildTree($elements, $element['id'], $depth + 1) : [];

            $item = [
                'text' => $element['text']
            ];

            // Simpan url langsung, jika ada
            if (!empty($element['myurl'])) {
                $item['myurl'] = $element['myurl'];
            }

            if (!empty($children)) {
                $item['items'] = $children;
            }

            $branch[] = $item;
        }
    }

    return $branch;
}






echo json_encode(buildTree($items));
