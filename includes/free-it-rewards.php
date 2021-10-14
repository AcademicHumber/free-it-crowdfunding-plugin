<?php

/**
 * Extends the WpCrowdFunding functionalities
 *
 *
 * @package     Free-It
 *
 */

namespace Free_It;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class FreeIt_CrowdFunding
{


    protected static $_instance = null;
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        $this->setup_plugin();
    }

    public function setup_plugin()
    {
        //Register Rewards product type
        add_action('wp_loaded',                                     array($this, 'register_product_type'));                   //Initialized the product type class
        register_activation_hook(__FILE__,                          array($this, 'install_taxonomy'));                        // Install rewards taxonomy        
        add_filter('product_type_selector',                         array($this, 'add_type_to_dropdown'));                    // Add rewards type to product types dropdown 
        add_action('woocommerce_product_options_pricing',           array($this, 'add_reward_fields'));                       // Create Reward fields for the product type
        add_action('admin_footer',                                  array($this, 'enable_product_js'));                       // Add JS for product type changes
        add_action('woocommerce_process_product_meta_reward',       array($this, 'save_reward_price'));                       // Save reward information to database
        add_action('woocommerce_process_product_meta_crowdfunding', array($this, 'manage_rewards_crud'));                     // Manage rewards creation or update based on campaign's data
        add_action('woocommerce_single_product_summary',            array($this, 'add_view_campaign_button'), 15);            // Add rewards tab on single campaign 
        add_filter('woocommerce_add_cart_item',                     array($this, 'add_reward_to_crowdfunding_order'), 15, 3); // Add reward item to crowdfunding order
        add_action('init',                                          array($this, 'remove_default_rewards_tab'));              // Remove WPCrowdfunding default rewards tab on single product
        add_action('wpcf_campaign_story_right_sidebar',             array($this, 'add_rewards_to_single_campaign_sidebar'));  // Add Free It rewards to campaign sidebar
    }


    /**
     * Registering Reward product type in product post woocommerce
     */
    public function register_product_type()
    {
        require_once FREE_IT_DIR_PATH . 'includes/WC_Product_Type.php';
    }

    // add the product type as a taxonomy
    public function install_taxonomy()
    {
        // If there is no reward product type taxonomy, add it.
        if (!get_term_by('slug', 'reward', 'product_type')) {
            wp_insert_term('reward', 'product_type');
        }
    }

    // add the product type to the dropdown
    public function add_type_to_dropdown($types)
    {
        $types['reward'] = __('Reward', 'free-it');

        return $types;
    }

    // Add reward fields
    public function add_reward_fields()
    {
?>
        <div class='options_group show_if_reward'>
            <?php


            $reward_meta_field = array(
                // Campaign ID
                array(
                    'id'            => '_freeit_rewards_campaign_id',
                    'label'         => __('Campaign ID', 'wp-crowdfunding'),
                    'desc_tip'      => 'true',
                    'type'          => 'text',
                    'placeholder'   => __('Campaign ID', 'wp-crowdfunding'),
                    'value'         => '',
                    'class'         => 'wc_input_price',
                    'field_type'    => 'textfield'
                ),
                // Pledge Amount
                array(
                    'id'            => '_freeit_rewards_pladge_amount',
                    'label'         => __('Pledge Amount', 'wp-crowdfunding'),
                    'desc_tip'      => 'true',
                    'type'          => 'text',
                    'placeholder'   => __('Pledge Amount', 'wp-crowdfunding'),
                    'value'         => '',
                    'class'         => 'wc_input_price',
                    'field_type'    => 'textfield',
                    'data_type'     => 'price'
                ),
                // Reward Image
                array(
                    'id'            => '_freeit_rewards_image_field',
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
                    'id'            => '_freeit_rewards_description',
                    'label'         => __('Reward', 'wp-crowdfunding'),
                    'desc_tip'      => 'true',
                    'type'          => 'text',
                    'placeholder'   => __('Reward Description', 'wp-crowdfunding'),
                    'value'         => '',
                    'field_type'    => 'textareafield',
                ),
                // Reward Month
                array(
                    'id'            => '_freeit_rewards_endmonth',
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
                    'id'            => '_freeit_rewards_endyear',
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
                    'id'            => '_freeit_rewards_item_limit',
                    'label'         => __('Quantity', 'wp-crowdfunding'),
                    'desc_tip'      => 'true',
                    'type'          => 'text',
                    'placeholder'   => __('Number of Rewards(Physical Product)', 'wp-crowdfunding'),
                    'value'         => '',
                    'class'         => 'wc_input_price',
                    'field_type'    => 'textfield'
                ),

            );

            echo "<div class='free-it-reward_group'>";
            global $post;
            $product = wc_get_product($post->ID);

            foreach ($reward_meta_field as $value) {
                $value['value'] = $product->get_meta($value['id']);

                switch ($value['field_type']) {

                    case 'textareafield':
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

                    default:
                        woocommerce_wp_text_input($value);
                        break;
                }
            }

            echo '</div>';

            ?>
        </div>

        <?php

        // General Tab not showing up
        add_action('woocommerce_product_options_general_product_data', function () {
            echo '<div class="options_group show_if_reward clear"></div>';
        });
    }

    // add show_if_reward class to options_group
    public function enable_product_js()
    {
        global $post, $product_object;

        if (!$post) {
            return;
        }

        if ('product' != $post->post_type) :
            return;
        endif;

        $is_reward = $product_object && 'reward' === $product_object->get_type() ? true : false;

        ?>
        <script type='text/javascript'>
            jQuery(document).ready(function() {
                //for Price tab
                jQuery('#general_product_data .pricing').addClass('show_if_reward');

                <?php if ($is_reward) { ?>
                    jQuery('#general_product_data .pricing').show();
                <?php } ?>
            });
        </script>
<?php
    }

    // Save data on submission
    public function save_reward_price($post_id)
    {
        if (!empty($_POST['_freeit_rewards_pladge_amount'])) {

            // Get data

            $campaign_id      = $_POST['_freeit_rewards_campaign_id'];
            $pladge_amount    = $_POST['_freeit_rewards_pladge_amount'];
            $image_field      = $_POST['_freeit_rewards_image_field'];
            $description      = $_POST['_freeit_rewards_description'];
            $end_month        = $_POST['_freeit_rewards_endmonth'];
            $end_year         = $_POST['_freeit_rewards_endyear'];
            $item_limit       = $_POST['_freeit_rewards_item_limit'];

            // Update post metas

            update_post_meta($post_id, '_freeit_rewards_campaign_id', $campaign_id);
            update_post_meta($post_id, '_freeit_rewards_pladge_amount', $pladge_amount);
            update_post_meta($post_id, '_freeit_rewards_image_field', $image_field);
            update_post_meta($post_id, '_freeit_rewards_description', $description);
            update_post_meta($post_id, '_freeit_rewards_endmonth', $end_month);
            update_post_meta($post_id, '_freeit_rewards_endyear', $end_year);
            update_post_meta($post_id, '_freeit_rewards_item_limit', $item_limit);
        }
    }

    // Display view campaign button on reward single product page
    public function add_view_campaign_button()
    {
        global $product;

        if ($product->get_type() == 'reward') {
            $reward_id = $product->get_id();
            $campaign_id = get_post_meta($reward_id, '_freeit_rewards_campaign_id', true);


            echo '<a href="' . get_permalink($campaign_id) . '" class="button alt freeit_button">' . __('View campaign', 'freeit') . '</a>';
        }
    }

    /**
     * Creates or updates rewards of the current crowdfunding product.
     * 
     */
    public function manage_rewards_crud($post_id)
    {

        $reward_ids = freeit_functions()->check_post_rewards($post_id);


        // WP Crowdfunding handles rewards on a weird way
        // If it is only 1 reward, it goes on the [0] index
        // But if there are 2 or more rewards, the reward info starts at [1] index

        for ($i = 0; $i < count($_POST['wpneo_rewards_pladge_amount']); $i++) {
            // Check if campaign has rewards

            if (!empty($_POST['wpneo_rewards_pladge_amount'][$i])) {

                $reward_data = [
                    'title'             => $_POST['post_title'] . ' campaign $' . $_POST['wpneo_rewards_pladge_amount'][$i] . ' reward',
                    'campaign_id'       => $post_id,
                    'pladge_amount'     => $_POST['wpneo_rewards_pladge_amount'][$i],
                    'image'             => $_POST['wpneo_rewards_image_field'][$i],
                    'description'       => $_POST['wpneo_rewards_description'][$i],
                    'endmonth'          => $_POST['wpneo_rewards_endmonth'][$i],
                    'endyear'           => $_POST['wpneo_rewards_endyear'][$i],
                    'item_limit'        => $_POST['wpneo_rewards_item_limit'][$i],
                ];


                // If there are more than 1 reward, reduce the index (it should not enter on the first iteration)

                $index = count($_POST['wpneo_rewards_pladge_amount']) > 1 ? ($i - 1) : $i;

                if (!empty($reward_ids[$index])) {

                    //Update post

                    $reward_args = [
                        'ID'       => $reward_ids[$index],
                        'post_title'    => $reward_data['title'],
                        'post_content'  => $reward_data['description'],
                        'post_status'   => 'publish',
                        'post_type'     => "product",
                        'meta_input'    => [
                            '_freeit_rewards_campaign_id'   => $reward_data['campaign_id'],
                            '_freeit_rewards_pladge_amount' => $reward_data['pladge_amount'],
                            '_freeit_rewards_image_field'   => $reward_data['image'],
                            '_thumbnail_id'                 => $reward_data['image'],
                            '_freeit_rewards_description'   => $reward_data['description'],
                            '_freeit_rewards_endmonth'      => $reward_data['endmonth'],
                            '_freeit_rewards_endyear'       => $reward_data['endyear'],
                            '_freeit_rewards_item_limit'    => $reward_data['item_limit'],

                        ]
                    ];

                    $reward_id = wp_update_post($reward_args);

                    // echo "updated post: " . $reward_id;
                } else {

                    //Create new reward post

                    $reward_args = [
                        'post_title'    => $reward_data['title'],
                        'post_content'  => $reward_data['description'],
                        'post_status'   => 'publish',
                        'post_type'     => "product",
                        'meta_input'    => [
                            '_freeit_rewards_campaign_id'   => $reward_data['campaign_id'],
                            '_freeit_rewards_pladge_amount' => $reward_data['pladge_amount'],
                            '_freeit_rewards_image_field'   => $reward_data['image'],
                            '_thumbnail_id'                 => $reward_data['image'],
                            '_freeit_rewards_description'   => $reward_data['description'],
                            '_freeit_rewards_endmonth'      => $reward_data['endmonth'],
                            '_freeit_rewards_endyear'       => $reward_data['endyear'],
                            '_freeit_rewards_item_limit'    => $reward_data['item_limit'],

                        ]
                    ];



                    $reward_id = wp_insert_post($reward_args);

                    wp_set_object_terms($reward_id, 'reward', 'product_type');

                    // echo "Created post: " . $reward_id;
                }
            }
        }
    }


    public function remove_default_rewards_tab()
    {
        freeit_functions()->remove_filters_for_anonymous_class('wpcf_campaign_story_right_sidebar', 'WPCF\woocommerce\Template_Hooks', 'story_right_sidebar', 10);
    }

    public function add_rewards_to_single_campaign_sidebar()
    {
        include FREE_IT_DIR_PATH . 'templates/wpcrowdfunding/tabs/rewards-sidebar-form.php';
    }

    /**
     * Add reward item to crowdfunding order
     * 
     * @param $product
     * @param $quantity
     * @return mixed
     */
    function add_reward_to_crowdfunding_order($product, $quantity)
    {
        if ($product['data']->get_type() == 'crowdfunding') {
            if (isset($_POST['reward_id'])) {
                WC()->cart->add_to_cart($_POST['reward_id']);
            }
        }

        return $product;
    }
}
