<style>
    .k-notification.my-success,
    .k-notification.my-error {
        min-width: 400px;
        max-width: 600px;
        font-size: 28px;
        font-weight: bold;
        line-height: 1.4;
        padding: 30px 35px;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        text-align: center;
    }

    .k-notification.my-success {
        background-color: #d4edda;
        color: #155724;
    }

    .k-notification.my-error {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>

<h2>Master Barang</h2>
<input type="text" id="searchNama" placeholder="Cari Nama Barang">
<button id="btnSearch">Search</button>
<button id="btnNew">New</button>
<button id="btnEdit">Edit</button>
<button id="btnDelete">Delete</button>
<div id="grid"></div>
<div id="notif"></div>

<!-- Modal Window Form -->
<div id="barangWindow" style="display:none;">
    <form id="barangForm">
        <label>Kode:</label><br>
        <input type="text" name="kode" id="kode" maxlength="10" required><br>

        <label>Nama Barang:</label><br>
        <input type="text" name="nm_barang" id="nm_barang" maxlength="100" required><br>

        <label>Harga Beli:</label><br>
        <input type="number" name="hargabeli" id="hargabeli" min="0" step="0.01" required><br>

        <label>Harga Jual 1:</label><br>
        <input type="number" name="hargajual" id="hargajual" min="0" step="0.01" required><br>

        <label>Harga Jual 2:</label><br>
        <input type="number" name="hargajual2" id="hargajual2" min="0" step="0.01" required><br>

        <label>Harga Jual 3:</label><br>
        <input type="number" name="hargajual3" id="hargajual3" min="0" step="0.01" required><br><br>

        <button type="submit" id="btnSave">Save</button>
    </form>
</div>

<script>
    kendo.culture("id-ID");
    $(document).ready(function() {


        var notif = $("#notif").kendoNotification({
            position: {
                top: "50%",
                left: "50%"
            },
            autoHideAfter: 2000,
            stacking: "down",
            templates: [{
                    type: "success",
                    template: "<div class='my-success'>#: message #</div>"
                },
                {
                    type: "error",
                    template: "<div class='my-error'>#: message #</div>"
                }
            ],
            show: function(e) {
                var element = e.element;
                var width = element.outerWidth();
                var height = element.outerHeight();
                element.css({
                    marginLeft: -width / 2,
                    marginTop: -height / 2
                });
            }
        }).data("kendoNotification");

        $("#btnSearch").kendoButton({
            themeColor: "primary",
            icon: "search"
        });
        $("#btnNew").kendoButton({
            themeColor: "primary",
            icon: "plus"
        });
        $("#btnEdit").kendoButton({
            themeColor: "primary",
            icon: "edit-tools"
        });
        $("#btnDelete").kendoButton({
            themeColor: "primary",
            icon: "trash"
        });
        $("#btnSave").kendoButton({
            themeColor: "primary",
            icon: "save"
        });

        var barangWindow = $("#barangWindow").kendoWindow({
            width: "400px",
            title: "Input Barang",
            visible: false,
            modal: true
        }).data("kendoWindow");

        var grid = $("#grid").kendoGrid({
            dataSource: {
                transport: {
                    read: {
                        url: "./master/mbarang_data.php",
                        dataType: "json",
                        data: function() {
                            return {
                                search: $("#searchNama").val()
                            };
                        }
                    }
                },
                pageSize: 10,
                schema: {
                    data: "data",
                    total: "total"
                }
            },
            pageable: true,
            sortable: true,
            selectable: "row",
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
                    width: "120px",
                    template: "#= kendo.toString(hargabeli, 'n2') #",
                    attributes: {
                        style: "text-align: right;"
                    }
                },
                {
                    field: "hargajual",
                    title: "Harga Jual 1",
                    width: "120px",
                    template: "#= kendo.toString(hargajual, 'n2') #",
                    attributes: {
                        style: "text-align: right;"
                    }
                },
                {
                    field: "hargajual2",
                    title: "Harga Jual 2",
                    width: "120px",
                    template: "#= kendo.toString(hargajual2, 'n2') #",
                    attributes: {
                        style: "text-align: right;"
                    }
                },
                {
                    field: "hargajual3",
                    title: "Harga Jual 3",
                    width: "120px",
                    template: "#= kendo.toString(hargajual3, 'n2') #",
                    attributes: {
                        style: "text-align: right;"
                    }
                }
            ]
        }).data("kendoGrid");

        $("#btnSearch").click(function() {
            grid.dataSource.read();
        });

        $("#btnNew").click(function() {
            $("#barangForm")[0].reset();
            $("#kode").prop("readonly", false);
            barangWindow.title("New Barang").center().open();
        });

        $("#btnEdit").click(function() {
            var selected = grid.select();
            if (selected.length == 0) {
                notif.show("Pilih data dulu.", "error");
                return;
            }
            var data = grid.dataItem(selected);
            $("#kode").val(data.kode).prop("readonly", true);
            $("#nm_barang").val(data.nm_barang);
            $("#hargabeli").val(data.hargabeli);
            $("#hargajual").val(data.hargajual);
            $("#hargajual2").val(data.hargajual2);
            $("#hargajual3").val(data.hargajual3);

            barangWindow.title("Edit Barang").center().open();
        });

        $("#btnDelete").click(function() {
            var selected = grid.select();
            if (selected.length == 0) {

                notif.show({
                    message: "Pilih data dulu."
                }, "error");

                return;
            }
            var data = grid.dataItem(selected);
            if (confirm("Hapus " + data.nm_barang + " ?")) {
                $.post("mbarang_crud.php", {
                    action: "delete",
                    kode: data.kode
                }, function(res) {

                    notif.show({
                        message: "Data berhasil dihapus."
                    }, "success");
                    Notifier.success(pesan);

                    grid.dataSource.read();
                });
            }
        });

        $("#barangForm").submit(function(e) {
            e.preventDefault();

            var valid = true;
            $("#barangForm input[required]").each(function() {
                if ($(this).val() == "") valid = false;
            });
            if (!valid) {
                notif.show("Isi semua field.", "error");
                return;
            }

            $.post("mbarang_crud.php", $("#barangForm").serialize(), function(res) {
                /*notif.show({
                    message: "Data berhasil disimpan."
                }, "success"); */
                Notifier.success("Data berhasil disimpan.");
                barangWindow.close();
                grid.dataSource.read();
            });
        });
    });
</script>