<!DOCTYPE html>
<html>

<head>
    <title>Date Range</title>
    <meta charset="utf-8">
    <link href="./lib/kendoui/content/shared/styles/examples-offline.css" rel="stylesheet">-->
    <link href="./lib/kendoui/styles/kendo.common.min.css" rel="stylesheet">
    <link href="./lib/kendoui/styles/kendo.rtl.min.css" rel="stylesheet">
    <link href="./lib/kendoui/styles/kendo.default.min.css" rel="stylesheet">
    <link href="./lib/kendoui/styles/kendo.default.mobile.min.css" rel="stylesheet">
    <script src="./lib/kendoui/js/jquery.min.js"></script>
    <script src="./lib/kendoui/js/jszip.min.js"></script>
    <script src="./lib/kendoui/content/shared/js/console.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="./lib/kendoui/js/kendo.all.min.js"></script>
    <script>

    </script>


</head>

<body>

    <a class="offline-button" href="../index.html">Back</a>


    <div id="example" style="text-align: center;">
        <div class="demo-section k-content wide" style="display: inline-block;">
            <h4>Select a date range</h4>
            <div id="daterangepicker" title="daterangepicker"></div>
        </div>
        <script>
            $(document).ready(function() {
                var start = new Date();
                var end = new Date(start.getFullYear(), start.getMonth(), start.getDate() + 20);

                $("#daterangepicker").kendoDateRangePicker({
                    range: {
                        start: start,
                        end: end
                    }
                }).data("kendoDatePicker");
            });
        </script>
    </div>



</body>

</html>