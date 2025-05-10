// notifier.js

var Notifier = (function () {
  var notificationWidget;

  function initNotification() {
    if (!notificationWidget) {
      $("<span id='notification'></span>").appendTo("body");
      notificationWidget = $("#notification")
        .kendoNotification({
          stacking: "down",
          autoHideAfter: 3000,
          position: {
            pinned: true,
            top: 30,
            right: 30,
          },
          templates: [
            {
              type: "success",
              template:
                "<div class='k-notification-success'><span class='icon'>✔️</span><span>#: message #</span></div>",
            },
            {
              type: "error",
              template:
                "<div class='k-notification-error'><span class='icon'>❌</span><span>#: message #</span></div>",
            },
            {
              type: "info",
              template:
                "<div class='k-notification-info'><span class='icon'>ℹ️</span><span>#: message #</span></div>",
            },
          ],
        })
        .data("kendoNotification");
    }
  }

  function show(type, message) {
    initNotification();
    if (["success", "error", "info"].includes(type)) {
      notificationWidget.show({ message: message }, type);
    } else {
      console.error("Notifier error: Tipe notifikasi tidak valid.");
    }
  }

  return {
    success: function (message) {
      show("success", message);
    },
    error: function (message) {
      show("error", message);
    },
    info: function (message) {
      show("info", message);
    },
  };
})();
