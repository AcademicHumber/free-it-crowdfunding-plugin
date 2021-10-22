/*========================================================================
 * Free It Crowdfunding FrontEnd JS
 *======================================================================== */
jQuery(document).ready(function ($) {
  $("body").on("click", ".freeit-back-campaign-btn", function (e) {
    e.preventDefault();
    let that = $(this);
    let campaignID = that.data("campaign");
    let campaignDonationBtn = $(".wpneo-single-sidebar.ASA").get();

    $.ajax({
      type: "POST",
      url: wpcf_ajax_object.ajax_url,
      data: { action: "free_it_donate_campaign", campaign: campaignID },
      success: function (data) {
        wpcf_modal(data);
        $("#contribution-box").append(campaignDonationBtn);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        wpcf_modal({ success: 0, message: "Error" });
      },
    });
  });
});
