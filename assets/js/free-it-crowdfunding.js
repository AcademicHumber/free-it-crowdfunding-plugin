/*========================================================================
 * Free It Crowdfunding
 *======================================================================== */
jQuery(document).ready(function ($) {
  function countRemovesBtn(btn) {
    var rewards_count = $(btn).length;
    if (rewards_count > 1) {
      $(btn).show();
    } else {
      $(btn).hide();
      if (btn == ".removeCampaignRewards") {
        $(".reward_group").show();
      }
      if (btn == ".removecampaignupdate") {
        $("#campaign_update_field").show();
      }
    }
    $(btn).first().hide();
  }

  $("body").on("click", ".freeit-file-upload-btn", function (e) {
    e.preventDefault();
    var that = $(this);
    var file = wp
      .media({
        title: "Upload a file",
        multiple: false,
      })
      .open()
      .on("select", function (e) {
        var uploaded_file = file.state().get("selection").first();
        var uploaded_url = uploaded_file.toJSON().url;
        uploaded_file = uploaded_file.toJSON().id;
        $(that).parent().find(".freeit_rewards_file_field").val(uploaded_file);
        $(that)
          .parent()
          .find(".freeit_rewards_file_url_field")
          .val(uploaded_url);
      });
  });
});
