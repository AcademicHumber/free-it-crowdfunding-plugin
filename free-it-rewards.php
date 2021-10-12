<?php

/**
 * Extends the WpCrowdFunding functionalities
 *
 *
 * @package     Free-it
 *
 */

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
        add_action('wp_loaded', array($this, 'register_product_type')); //Initialized the product type class
        register_activation_hook(__FILE__, array($this, 'install_taxonomy'));

        // Hooks 
        add_filter('product_type_selector', array($this, 'add_type_to_dropdown'));
        add_action('woocommerce_product_options_pricing', array($this, 'add_reward_fields'));
        add_action('admin_footer', array($this, 'enable_product_js'));
        // General Tab not showing up
        add_action('woocommerce_product_options_general_product_data', function () {
            echo '<div class="options_group show_if_reward clear"></div>';
        });
        add_action('woocommerce_process_product_meta_reward', array($this, 'save_reward_price'));
        add_action('woocommerce_single_product_summary', array($this, 'add_cart_button'), 15);


        // Remove WPCrowdfunding default rewards tab on single product
        $this->remove_filters_for_anonymous_class('wpcf_campaign_story_right_sidebar', 'WPCF\woocommerce\Template_Hooks', 'story_right_sidebar', 10);
    }

    // Functions

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

    // save data on submission
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

    // display add to cart button
    public function add_cart_button()
    {
        global $product;
        $id = $product->get_id();
        if (WC_Product_Factory::get_product_type($id) == 'reward')
            echo '
            <form class="cart" action="" method="post" enctype="multipart/form-data">
                <div class="quantity">
                    <label class="screen-reader-text" for="quantity_5fe8134674b0d">Quantity</label>
                    <input type="number" id="quantity_5fe8134674b0d" class="input-text qty text" step="1" min="1" max="" name="quantity" value="1" title="Qty" size="4" placeholder="" inputmode="numeric">
                </div>
                <button type="submit" name="add-to-cart" value="' . $id . '" class="single_add_to_cart_button button alt">Add to cart</button>
            </form>
        ';
    }

    /**
     * Allow to remove method for an hook when, it's a class method used and class don't have global for instanciation !
     * 
     * @author Amaury Balmer - amaury@beapi.fr
     */
    public function remove_filters_with_method_name($hook_name = '', $method_name = '', $priority = 0)
    {
        global $wp_filter;

        // Take only filters on right hook name and priority
        if (!isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority])) {
            return false;
        }

        // Loop on filters registered
        foreach ((array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array) {
            // Test if filter is an array ! (always for class/method)
            if (isset($filter_array['function']) && is_array($filter_array['function'])) {
                // Test if object is a class and method is equal to param !
                if (is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && $filter_array['function'][1] == $method_name) {
                    // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
                    if (is_a($wp_filter[$hook_name], 'WP_Hook')) {
                        unset($wp_filter[$hook_name]->callbacks[$priority][$unique_id]);
                    } else {
                        unset($wp_filter[$hook_name][$priority][$unique_id]);
                    }
                }
            }
        }

        return false;
    }

    /**
     * Allow to remove method for an hook when, it's a class method used and class don't have variable, but you know the class name :)
     * 
     * @author Amaury Balmer - amaury@beapi.fr
     */
    public function remove_filters_for_anonymous_class($hook_name = '', $class_name = '', $method_name = '', $priority = 0)
    {
        global $wp_filter;

        // Take only filters on right hook name and priority
        if (!isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority])) {
            return false;
        }

        // Loop on filters registered
        foreach ((array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array) {
            // Test if filter is an array ! (always for class/method)
            if (isset($filter_array['function']) && is_array($filter_array['function'])) {
                // Test if object is a class, class and method is equal to param !
                if (is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && get_class($filter_array['function'][0]) == $class_name && $filter_array['function'][1] == $method_name) {
                    // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
                    if (is_a($wp_filter[$hook_name], 'WP_Hook')) {
                        unset($wp_filter[$hook_name]->callbacks[$priority][$unique_id]);
                    } else {
                        unset($wp_filter[$hook_name][$priority][$unique_id]);
                    }
                }
            }
        }

        return false;
    }
}
