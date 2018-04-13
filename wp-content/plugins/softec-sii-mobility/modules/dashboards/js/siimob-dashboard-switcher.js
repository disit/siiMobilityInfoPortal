jQuery.noConflict()(function($) {
  "use strict";
  $(document).ready(function() {
    $("#dashboard-switcher").change(function() {
      var newUrl = $(this).val();
      var newHeight = $(this).find(":selected").data("height");

      $("#main-dashboard").attr("src", newUrl);
      $("#main-dashboard").css("height", newHeight + "px");
      $("#main-dashboard").attr("data-height", newHeight);
    });
  });
});
