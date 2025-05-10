<!DOCTYPE html>
<html>

<head>
  <title>Date Range</title>
  <meta charset="utf-8" />
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

</head>

<body>

  <h3>Contoh Penggunaan Notifikasi (Modular)</h3>

  <button id="btnSuccess">Tampilkan Success</button>
  <button id="btnError">Tampilkan Error</button>
  <button id="btnInfo">Tampilkan Info</button>

  <!-- Notifier JS -->
  <!--<script src="./ib/kendoui/jsnotifier.js"></script> -->

  <script>
    $(document).ready(function() {
      var pesan = 'oxxxxxo';
      $("#btnSuccess").click(function() {
        Notifier.success(pesan);
      });

      $("#btnError").click(function() {
        Notifier.error("Gagal menyimpan data!");
      });

      $("#btnInfo").click(function() {
        Notifier.info("Ini hanya info biasa.");
      });
    });
  </script>

</body>

</html>