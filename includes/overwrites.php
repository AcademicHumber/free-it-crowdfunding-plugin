<?php

namespace Free_It;

class Wp_Crowdfunding_OverWrites
{

    function __construct()
    {
        add_action('plugins_loaded',                    array($this, 'frontend_form_process'));                //Include all of resource to the plugin 
        add_action('init',                              array($this, 'remove_default_rewards_tab'));           // Remove WPCrowdfunding default rewards tab on single product
        add_action('init',                              array($this, 'remove_default_admin_rewards_tab'));     // Remove WPCrowdfunding default rewards tab on single product admin page
        add_action('init',                              array($this, 'remove_default_rewards_processing'));    // Remove WPCrowdfunding default rewards processing on campaign publish
        add_action('wpcf_single_campaign_summary',      array($this, 'back_campaign_btn'), 20);                // Add new back campaign button

        // AJAX

        add_action('wp_ajax_free_it_donate_campaign',          array($this, 'campaign_donation_popup'));       // Generates the html for the campaign donation popup
        add_action('wp_ajax_nopriv_free_it_donate_campaign',   array($this, 'campaign_donation_popup'));       // Generates the html for the campaign donation popup

        // Include Shortcode
        $this->include_shortcode();
    }

    /**
     * Adds all Free it shortcodes
     * 
     */
    public function include_shortcode()
    {
        include_once FREE_IT_DIR_PATH . 'templates/wpcrowdfunding/shortcodes/Submit_Form.php';

        $freeit_campaign_submit_from = new \Free_It\shortcode\Campaign_Submit_Form();
    }

    /**
     * Include and overwrite campaign's front end form processing file
     */
    function frontend_form_process()
    {
        require_once FREE_IT_DIR_PATH . 'includes/Submit_Form.php';
        new \Free_It\woocommerce\Submit_Form();
    }

    /**
     * Removes frontend reward tabs from single campaign page and adds the freeit ones
     */
    public function remove_default_rewards_tab()
    {
        freeit_functions()->remove_filters_for_anonymous_class('wpcf_campaign_story_right_sidebar', 'WPCF\woocommerce\Template_Hooks', 'story_right_sidebar', 10);
        add_action('wpcf_campaign_story_right_sidebar',             array($this, 'add_rewards_to_single_campaign_sidebar'));  // Add Free It rewards to campaign sidebar

    }
    /** 
     * Adds freeit frontend reward tabs 
     */
    public function add_rewards_to_single_campaign_sidebar()
    {
        include FREE_IT_DIR_PATH . 'templates/wpcrowdfunding/tabs/rewards-sidebar-form.php';
    }


    /**
     * Removes admin reward tabs from crowdfunding's product edit page
     */
    function remove_default_admin_rewards_tab()
    {
        freeit_functions()->remove_filters_for_anonymous_class('woocommerce_product_data_panels', 'WPCF\woocommerce\Reward', 'reward_content', 10);

        add_action('woocommerce_product_data_panels', array($this, 'add_rewards_to_single_campaign_admin_tabs'));  // Add Free It rewards to campaign sidebar
    }
    /** 
     * Adds freeit admin reward tabs 
     */
    function add_rewards_to_single_campaign_admin_tabs()
    {
        global $post;

        $var = get_post_meta($post->ID, 'wpneo_reward', true);
        // $var = stripslashes($var);
        $data_array = json_decode($var, true);

        $woocommerce_meta_field = array(
            // Pledge Amount
            array(
                'id'            => 'wpneo_rewards_pladge_amount[]',
                'label'         => __('Pledge Amount', 'wp-crowdfunding'),
                'desc_tip'      => 'true',
                'type'          => 'text',
                'placeholder'   => __('Pledge Amount', 'wp-crowdfunding'),
                'value'         => '',
                'class'         => 'wc_input_price',
                'field_type'    => 'textfield'
            ),
            // Reward Image
            array(
                'id'            => 'wpneo_rewards_image_field[]',
                'label'         => __('Image Field', 'wp-crowdfunding'),
                'desc_tip'      => 'true',
                'type'          => 'image',
                'placeholder'   => __('Image Field', 'wp-crowdfunding'),
                'value'         => '',
                'class'         => '',
                'field_type'    => 'image'
            ),
            // Reward Description
            array(
                'id'            => 'wpneo_rewards_description[]',
                'label'         => __('Reward', 'wp-crowdfunding'),
                'desc_tip'      => 'true',
                'type'          => 'text',
                'placeholder'   => __('Reward Description', 'wp-crowdfunding'),
                'value'         => '',
                'field_type'    => 'textareafield',
            ),
            // Reward Month
            array(
                'id'            => 'wpneo_rewards_endmonth[]',
                'label'         => __('Estimated Delivery Month', 'wp-crowdfunding'),
                'type'          => 'text',
                'value'         => '',
                'options'       => array(
                    ''    => __('- Select -', 'wp-crowdfunding'),
                    'jan' => __('January', 'wp-crowdfunding'),
                    'feb' => __('February', 'wp-crowdfunding'),
                    'mar' => __('March', 'wp-crowdfunding'),
                    'apr' => __('April', 'wp-crowdfunding'),
                    'may' => __('May', 'wp-crowdfunding'),
                    'jun' => __('June', 'wp-crowdfunding'),
                    'jul' => __('July', 'wp-crowdfunding'),
                    'aug' => __('August', 'wp-crowdfunding'),
                    'sep' => __('September', 'wp-crowdfunding'),
                    'oct' => __('October', 'wp-crowdfunding'),
                    'nov' => __('November', 'wp-crowdfunding'),
                    'dec' => __('December', 'wp-crowdfunding'),
                ),
                'field_type'    => 'selectfield',
            ),
            // Reward Year
            array(
                'id'            => 'wpneo_rewards_endyear[]',
                'label'         => __('Estimated Delivery Year', 'wp-crowdfunding'),
                'type'          => 'text',
                'value'         => '',
                'options'       => array(
                    ''     => __('- Select -', 'wp-crowdfunding'),
                    '2019' => __('2019', 'wp-crowdfunding'),
                    '2020' => __('2020', 'wp-crowdfunding'),
                    '2021' => __('2021', 'wp-crowdfunding'),
                    '2022' => __('2022', 'wp-crowdfunding'),
                    '2023' => __('2023', 'wp-crowdfunding'),
                    '2024' => __('2024', 'wp-crowdfunding'),
                    '2025' => __('2025', 'wp-crowdfunding'),
                ),
                'field_type'    => 'selectfield',
            ),
            // Quantity (Number of Pledge Items)
            array(
                'id'            => 'wpneo_rewards_item_limit[]',
                'label'         => __('Quantity', 'wp-crowdfunding'),
                'desc_tip'      => 'true',
                'type'          => 'text',
                'placeholder'   => __('Number of Rewards(Physical Product)', 'wp-crowdfunding'),
                'value'         => '',
                'class'         => 'wc_input_price',
                'field_type'    => 'textfield'
            ),
            // Reward File
            array(
                'id'            => 'freeit_rewards_file_field[]',
                'label'         => __('File Field', 'wp-crowdfunding'),
                'desc_tip'      => 'true',
                'type'          => 'text',
                'placeholder'   => __('File Field', 'wp-crowdfunding'),
                'value'         => '',
                'class'         => '',
                'field_type'    => 'file'
            )

        );
?>

        <div id='reward_options' class='panel woocommerce_options_panel'>
            <?php
            $display = 'block';
            $meta_count = is_array($data_array) ? count($data_array) : 0;
            $field_count = count($woocommerce_meta_field);
            if ($meta_count > 0) {
                $display = 'none';
            }

            /*
            * Print without value of Reward System for clone group
            */
            echo "<div class='reward_group' style='display:" . $display . ";'>";
            echo "<div class='campaign_rewards_field_copy'>";

            foreach ($woocommerce_meta_field as $value) {
                switch ($value['field_type']) {

                    case 'textareafield':
                        woocommerce_wp_textarea_input($value);
                        break;

                    case 'selectfield':
                        woocommerce_wp_select($value);
                        break;

                    case 'image':
                        echo '<p class="form-field">';
                        echo '<label for="wpneo_rewards_image_field">' . $value["label"] . '</label>';
                        echo '<input type="hidden" class="wpneo_rewards_image_field" name="' . $value["id"] . '" value="" placeholder="' . $value["label"] . '"/>';
                        echo '<span class="wpneo-image-container"></span>';
                        echo '<button class="wpneo-image-upload-btn shorter">' . __("Upload", "wp-crowdfunding") . '</button>';
                        echo '</p>';
                        break;

                    case 'file':
                        echo '<p class="form-field">';
                        echo '<label for="wpneo_rewards_image_field">' . $value["label"] . '</label>';
                        echo '<input type="text" readonly="readonly" class="freeit_rewards_file_url_field" value="' . $value["value"] . '" placeholder="' . $value["label"] . '"/>';
                        echo '<input type="hidden" class="freeit_rewards_file_field" name="' . $value["id"] . '" value="' . $value["value"] . '"/>';
                        echo '<button class="freeit-file-upload-btn shorter">' . __("Upload", "wp-crowdfunding") . '</button>';
                        echo '</p>';
                        break;


                    default:
                        woocommerce_wp_text_input($value);
                        break;
                }
            }

            echo '<input name="remove_rewards" type="button" class="button tagadd removeCampaignRewards" value="' . __('- Remove', 'wp-crowdfunding') . '" />';
            echo '<hr>';
            echo "</div>";
            echo "</div>";


            /*
            * Print with value of Reward System
            */
            if ($meta_count > 0) {
                if (is_array($data_array) && !empty($data_array)) {
                    foreach ($data_array as $k => $v) {
                        echo "<div class='reward_group'>";
                        echo "<div class='campaign_rewards_field_copy'>";
                        foreach ($woocommerce_meta_field as $value) {
                            if (isset($v[str_replace('[]', '', $value['id'])])) {
                                // Add field value
                                $value['value'] = $v[str_replace('[]', '', $value['id'])];
                            } else {
                                $value['value'] = '';
                            }
                            switch ($value['field_type']) {

                                case 'textareafield':
                                    $value['value'] = wp_unslash($value['value']);
                                    woocommerce_wp_textarea_input($value);
                                    break;

                                case 'selectfield':
                                    woocommerce_wp_select($value);
                                    break;

                                case 'image':
                                    $image_id = $value['value'];
                                    $raw_id = $image_id;
                                    if ($image_id != 0 && $image_id != '') {
                                        $image_id = wp_get_attachment_url($image_id);
                                        $image_id = '<img width="100" src="' . $image_id . '"><span class="wpneo-image-remove">x</span>';
                                    } else {
                                        $image_id = '';
                                    }
                                    echo '<p class="form-field">';
                                    echo '<label for="wpneo_rewards_image_field">' . $value["label"] . '</label>';
                                    echo '<input type="hidden" class="wpneo_rewards_image_field" name="' . $value["id"] . '" value="' . $raw_id . '" placeholder="' . $value["label"] . '"/>';
                                    echo '<span class="wpneo-image-container">' . $image_id . '</span>';
                                    echo '<button class="wpneo-image-upload-btn shorter">' . __("Upload", "wp-crowdfunding") . '</button>';
                                    echo '</p>';
                                    break;

                                case 'file':
                                    $file_id = $value['value'];
                                    $raw_id = $file_id;
                                    if ($file_id != 0 && $file_id != '') {
                                        $file_id = wp_get_attachment_url($file_id);
                                        $value['value'] = $file_id;
                                    } else {
                                        $file_id = '';
                                    }
                                    echo '<p class="form-field">';
                                    echo '<label for="wpneo_rewards_image_field">' . $value["label"] . '</label>';
                                    echo '<input type="text" readonly="readonly" class="freeit_rewards_file_url_field" value="' . $value["value"] . '" placeholder="' . $value["label"] . '"/>';
                                    echo '<input type="hidden" class="freeit_rewards_file_field" name="' . $value["id"] . '" value="' . $value["value"] . '"/>';
                                    echo '<button class="freeit-file-upload-btn shorter">' . __("Upload", "wp-crowdfunding") . '</button>';
                                    echo '</p>';
                                    break;

                                default:
                                    woocommerce_wp_text_input($value);
                                    break;
                            }
                        }
                        echo '<input name="remove_rewards" type="button" class="button tagadd removeCampaignRewards" value="' . __('- Remove', 'wp-crowdfunding') . '" />';
                        echo '<hr>';
                        echo "</div>";
                        echo "</div>";
                    }
                }
            }
            ?>
            <div id="rewards_addon_fields"></div>
            <input name="save" type="button" class="button button-primary tagadd" id="addreward" value="<?php _e('+ Add Reward', 'wp-crowdfunding'); ?>">
        </div>

<?php
    }

    /**
     * Removes reward procesing system and add support for files on rewards
     */
    function remove_default_rewards_processing()
    {
        freeit_functions()->remove_filters_for_anonymous_class('woocommerce_process_product_meta', 'WPCF\woocommerce\Reward', 'reward_action', 10);
        add_action('woocommerce_process_product_meta', array($this, 'freeit_rewards_processing'), 15);
    }
    /**
     * Save Reward tab Data(Woocommerce).
     * Add support for reward files
     */
    function freeit_rewards_processing($post_id)
    {

        if (!empty($_POST['wpneo_rewards_pladge_amount'])) {
            $data             = array();
            $pladge_amount    = $_POST['wpneo_rewards_pladge_amount'];
            $image_field      = $_POST['wpneo_rewards_image_field'];
            $description      = $_POST['wpneo_rewards_description'];
            $end_month        = $_POST['wpneo_rewards_endmonth'];
            $end_year         = $_POST['wpneo_rewards_endyear'];
            $item_limit       = $_POST['wpneo_rewards_item_limit'];
            $file_field       = $_POST['freeit_rewards_file_field'];

            $field_count      = count($pladge_amount);
            for ($i = 0; $i < $field_count; $i++) {
                if (!empty($pladge_amount[$i])) {
                    $data[] = array(
                        'wpneo_rewards_pladge_amount'   => intval($pladge_amount[$i]),
                        'wpneo_rewards_image_field'     => intval($image_field[$i]),
                        'wpneo_rewards_description'     => $description[$i],
                        'wpneo_rewards_endmonth'        => esc_html($end_month[$i]),
                        'wpneo_rewards_endyear'         => esc_html($end_year[$i]),
                        'wpneo_rewards_item_limit'      => esc_html($item_limit[$i]),
                        'freeit_rewards_file_field'     => esc_html($file_field[$i]),
                    );
                }
            }
            $data_json = json_encode($data, JSON_UNESCAPED_UNICODE);
            wpcf_function()->update_meta($post_id, 'wpneo_reward', wp_slash($data_json));
        }
    }

    /**
     * Adds new back campaign button, it generates a popup for backing the campaign with donation or rewards
     */

    function back_campaign_btn()
    {
        global $post;
        echo '<button class="freeit-back-campaign-btn " data-campaign="' . $post->ID . '">' . __('Back Campaign', 'wp-crowdfunding') . '</button>';
    }

    /**
     * Returns the html for the campaign donation popup, it contains donation button and rewards
     */
    function campaign_donation_popup()
    {
        $contribution_html = '<div id="contribution-box"><h4>Make a donation</h4></div>';
        include FREE_IT_DIR_PATH . 'templates/popup/rewards-list.php';

        $pop_up_html = $contribution_html . $rewards_html;
        die(json_encode(array('success' => 1, 'message' => $pop_up_html, 'title' => 'Back this camapaign')));
    }
}
