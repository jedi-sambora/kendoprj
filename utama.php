<!DOCTYPE html>
<html>

<head>
    <title>Aplikasi Penjualan</title>
    <link
        href="./lib/kendoui/content/shared/styles/examples-offline.css"
        rel="stylesheet" />
    <link href="./lib/kendoui/styles/kendo.common.min.css" rel="stylesheet" />
    <link href="./lib/kendoui/styles/kendo.rtl.min.css" rel="stylesheet" />
    <link href="./lib/kendoui/styles/kendo.default.min.css" rel="stylesheet" />
    <link href="./lib/kendoui/styles/notif.css" rel="stylesheet" />
    <link
        href="./lib/kendoui/styles/kendo.default.mobile.min.css"
        rel="stylesheet" />
    <script src="./lib/kendoui/js/jquery.min.js"></script>
    <script src="./lib/kendoui/js/jszip.min.js"></script>
    <script src="./lib/kendoui/content/shared/js/console.js"></script>
    <script src="./lib/kendoui/js/notif.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script
        type="text/javascript"
        src="./lib/kendoui/js/kendo.all.min.js">
    </script>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
        }

        #menuPanel {
            border-bottom: 1px solid #ccc;
        }

        #contentPanel {
            padding: 20px;
        }

        #menuPanel {
            display: flex;
            justify-content: center;
            /* Untuk mengatur menu di tengah horizontal */
            width: 100%;
            /* Menjamin lebar 100% dari container */
        }

        .k-menu {
            display: flex;
            justify-content: center;
            /* Memastikan item menu ditata secara horizontal */
        }

        .k-item {
            margin: 0 10px;
            /* Menambahkan jarak antar item menu */
        }

        #contentPanel {
            display: flex;
            justify-content: center;
            /* Center secara horizontal */
            align-items: top;
            /* Center secara vertikal */
            height: 100vh;
            /* Membuat container setinggi viewport */
            width: 100%;
            /* Lebar 100% dari halaman */
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <
        <div id="menuPanel">
        </div>
        <div id="contentPanel">Silakan pilih menu di atas.</div>

        <script>
            $(document).ready(function() {
                $.getJSON("get_menu.php", function(menuData) {
                    var menu = $("#menuPanel").kendoMenu({
                        dataSource: menuData,
                        select: function(e) {
                            e.preventDefault(); // cegah redirect

                            // ambil langsung data dari item
                            var itemElement = e.item;
                            var dataItem = e.sender.dataSource.view();

                            // recursive cari berdasarkan teks yang cocok
                            function findItemByText(items, text) {
                                for (var i = 0; i < items.length; i++) {
                                    if (items[i].text === text) return items[i];
                                    if (items[i].items) {
                                        var found = findItemByText(items[i].items, text);
                                        if (found) return found;
                                    }
                                }
                                return null;
                            }

                            var clickedText = $(itemElement).text().trim();
                            var matchedItem = findItemByText(dataItem, clickedText);

                            if (matchedItem && matchedItem.myurl) {
                                console.log("Memuat:", matchedItem.myurl);
                                $("#contentPanel").load(matchedItem.myurl);
                            } else {
                                console.log("Data item tidak ditemukan atau tidak ada myurl");
                            }
                        }
                    }).data("kendoMenu");
                });
            });
        </script>

        </script>
</body>

</html>