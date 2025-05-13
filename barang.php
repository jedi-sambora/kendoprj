<!DOCTYPE html>
<html>

<head>
    <title>CRUD Barang - KendoUI</title>
    <!-- Kendo UI CSS + jQuery -->
    <link href="./lib/kendoui/content/shared/styles/examples-offline.css" rel="stylesheet">
    <link href="./lib/kendoui/styles/kendo.common.min.css" rel="stylesheet">
    <link href="./lib/kendoui/styles/kendo.rtl.min.css" rel="stylesheet">
    <link href="./lib/kendoui/styles/kendo.default.min.css" rel="stylesheet">
    <link href="./lib/kendoui/styles/kendo.default.mobile.min.css" rel="stylesheet">
    <script src="./lib/kendoui/js/jquery.min.js"></script>
    <script src="./lib/kendoui/js/jszip.min.js"></script>
    <script src="./lib/kendoui/content/shared/js/console.js"></script>
    <!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
    <script type="text/javascript" src="./lib/kendoui/js/kendo.all.min.js"></script>
    <style>
        .toolbar {
            margin-bottom: 10px;
        }
    </style>
    <script>
        // Definisikan culture Indonesia sebelum inisialisasi komponen
        kendo.cultures["id-ID"] = {
            name: "id-ID",
            numberFormat: {
                pattern: ["-n"],
                decimals: 2,
                ",": ".",
                ".": ",",
                groupSize: [3],
                symbol: "Rp"
            },
            currencies: {
                "id-ID": {
                    pattern: ["-$n", "$n"],
                    decimals: 2,
                    ",": ".",
                    ".": ",",
                    groupSize: [3],
                    symbol: "Rp"
                }
            }
        };
        kendo.culture("id-ID");
    </script>
</head>

<body>
    <div id="example">
        <!-- Toolbar (Search + Buttons) -->
        <div class="toolbar">
            <input id="searchBox" placeholder="Cari Nama Barang..." />
            <button id="btnNew" class="k-button">New</button>
            <button id="btnEdit" class="k-button">Edit</button>
            <button id="btnDelete" class="k-button">Delete</button>
        </div>

        <!-- Kendo Grid -->
        <div id="grid"></div>

        <!-- Form Popup (New/Edit) -->
        <div id="formWindow" style="display:none;">
            <form id="formBarang">
                <input type="hidden" id="kode" name="kode" />
                <div>
                    <label>Nama Barang</label>
                    <input type="text" id="nm_barang" name="nm_barang" class="k-textbox" required />
                </div>
                <div>
                    <label>Harga Beli</label>
                    <input id="hargabeli" name="hargabeli" required />
                </div>
                <div>
                    <label>Harga Jual 1</label>
                    <input id="hargajual" name="hargajual" required />
                </div>
                <div>
                    <label>Harga Jual 2</label>
                    <input id="hargajual2" name="hargajual2" />
                </div>
                <div>
                    <label>Harga Jual 3</label>
                    <input id="hargajual3" name="hargajual3" />
                </div>
                <div style="margin-top: 10px;">
                    <button type="submit" class="k-button">Simpan</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            // Inisialisasi NumericTextBox untuk input harga
            $("#hargabeli").kendoNumericTextBox({
                format: "n2",
                decimals: 2,
                culture: "id-ID"
            });

            $("#hargajual").kendoNumericTextBox({
                format: "n2",
                decimals: 2,
                culture: "id-ID"
            });

            $("#hargajual2").kendoNumericTextBox({
                format: "n2",
                decimals: 2,
                culture: "id-ID"
            });

            $("#hargajual3").kendoNumericTextBox({
                format: "n2",
                decimals: 2,
                culture: "id-ID"
            });

            // Inisialisasi Grid
            var grid = $("#grid").kendoGrid({
                dataSource: {
                    transport: {
                        read: "barang_crud.php?action=read",
                        update: {
                            url: "barang_crud.php?action=update",
                            type: "POST"
                        },
                        destroy: {
                            url: "barang_crud.php?action=delete",
                            type: "POST"
                        },
                        create: {
                            url: "barang_crud.php?action=create",
                            type: "POST"
                        },
                        parameterMap: function(data, operation) {
                            if (operation !== "read") {
                                return {
                                    data: kendo.stringify(data.models || data)
                                };
                            }
                        }
                    },
                    schema: {
                        data: "data",
                        model: {
                            id: "kode",
                            fields: {
                                kode: {
                                    type: "string"
                                },
                                nm_barang: {
                                    type: "string"
                                },
                                hargabeli: {
                                    type: "number"
                                },
                                hargajual: {
                                    type: "number"
                                },
                                hargajual2: {
                                    type: "number"
                                },
                                hargajual3: {
                                    type: "number"
                                }
                            }
                        }
                    },
                    pageSize: 10
                },
                height: 500,
                pageable: true,
                toolbar: ["search"],
                columns: [{
                        field: "kode",
                        title: "Kode",
                        width: "100px"
                    },
                    {
                        field: "nm_barang",
                        title: "Nama Barang"
                    },
                    {
                        field: "hargabeli",
                        title: "Harga Beli",
                        template: "#= kendo.toString(hargabeli, 'n2', 'id-ID') #",
                        attributes: {
                            "class": "text-right"
                        }
                    },
                    {
                        field: "hargajual",
                        title: "Harga Jual 1",
                        template: "#= kendo.toString(hargajual, 'n2', 'id-ID') #",
                        attributes: {
                            "class": "text-right"
                        }
                    },
                    {
                        field: "hargajual2",
                        title: "Harga Jual 2",
                        template: "#= kendo.toString(hargajual2, 'n2', 'id-ID') #",
                        attributes: {
                            "class": "text-right"
                        }
                    },
                    {
                        field: "hargajual3",
                        title: "Harga Jual 3",
                        template: "#= kendo.toString(hargajual3, 'n2', 'id-ID') #",
                        attributes: {
                            "class": "text-right"
                        }
                    }
                ],
                editable: false,
                selectable: "row"
            }).data("kendoGrid");

            // Search Functionality
            $("#searchBox").keyup(function() {
                var searchText = this.value.toLowerCase();
                grid.dataSource.filter({
                    logic: "or",
                    filters: [{
                        field: "nm_barang",
                        operator: "contains",
                        value: searchText
                    }]
                });
            });

            // Form Window
            var formWindow = $("#formWindow").kendoWindow({
                title: "Form Barang",
                width: "400px",
                modal: true,
                visible: false
            }).data("kendoWindow");

            // Button Events
            $("#btnNew").click(function() {
                $("#formBarang")[0].reset();
                $("#kode").val("");
                formWindow.open().center();
            });

            $("#btnEdit").click(function() {
                var selected = grid.select();
                if (selected.length) {
                    var data = grid.dataItem(selected);
                    $("#kode").val(data.kode);
                    $("#nm_barang").val(data.nm_barang);

                    // Set nilai NumericTextBox
                    var numericHargaBeli = $("#hargabeli").data("kendoNumericTextBox");
                    numericHargaBeli.value(data.hargabeli);

                    var numericHargaJual = $("#hargajual").data("kendoNumericTextBox");
                    numericHargaJual.value(data.hargajual);

                    var numericHargaJual2 = $("#hargajual2").data("kendoNumericTextBox");
                    numericHargaJual2.value(data.hargajual2);

                    var numericHargaJual3 = $("#hargajual3").data("kendoNumericTextBox");
                    numericHargaJual3.value(data.hargajual3);

                    formWindow.open().center();
                } else {
                    alert("Pilih data terlebih dahulu!");
                }
            });

            $("#btnDelete").click(function() {
                var selected = grid.select();
                if (selected.length) {
                    if (confirm("Hapus data ini?")) {
                        grid.dataSource.remove(grid.dataItem(selected));
                        grid.dataSource.sync();
                    }
                } else {
                    alert("Pilih data terlebih dahulu!");
                }
            });

            // Submit Form
            $("#formBarang").submit(function(e) {
                e.preventDefault();

                // Ambil nilai dari NumericTextBox
                var numericHargaBeli = $("#hargabeli").data("kendoNumericTextBox");
                var numericHargaJual = $("#hargajual").data("kendoNumericTextBox");
                var numericHargaJual2 = $("#hargajual2").data("kendoNumericTextBox");
                var numericHargaJual3 = $("#hargajual3").data("kendoNumericTextBox");

                var data = {
                    kode: $("#kode").val(),
                    nm_barang: $("#nm_barang").val(),
                    hargabeli: numericHargaBeli.value(),
                    hargajual: numericHargaJual.value(),
                    hargajual2: numericHargaJual2.value(),
                    hargajual3: numericHargaJual3.value()
                };

                var action = data.kode ? "update" : "create";
                $.post("barang_crud.php?action=" + action, {
                    data: JSON.stringify(data)
                }, function(response) {
                    if (response.success) {
                        grid.dataSource.read();
                        formWindow.close();
                    } else {
                        alert("Error: " + response.message);
                    }
                }, "json");
            });
        });
    </script>
</body>

</html>