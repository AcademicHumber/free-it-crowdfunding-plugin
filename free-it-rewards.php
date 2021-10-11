<?php

/**
 * Extends the WC_Settings_Page class
 *
 *
 * @package     Free-it
 *
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('WC_Product_reward')) {
    /**
     * Add custom product type
     */

    class WC_Product_Reward extends WC_Product_Simple
    {

        // Return the product type
        public function get_type()
        {
            return 'reward';
        }

        // Set pladge amount field as product price

        public function get_price($context = 'view')
        {

            $price = $this->get_meta('_freeit_rewards_pladge_amount');
            return $price;
        }

        // Add downloadable capabilities

        public function get_downloadable($context = 'view')
        {
            return true;
        }

        // Set main image ID as reward image

        public function get_image_id($context = 'view')
        {

            $image_id = $this->get_meta('_freeit_rewards_image_field');
            return $image_id;
        }
    }
}


// Hooks 

add_filter('product_type_selector', 'add_type_to_dropdown');
add_action('woocommerce_product_options_pricing', 'add_reward_fields');
add_action('admin_footer', 'enable_product_js');
add_action('woocommerce_process_product_meta_reward', 'save_reward_price');
add_action('woocommerce_single_product_summary', 'add_cart_button', 15);


// Functions

// add the product type as a taxonomy
function install_taxonomy()
{
    // If there is no reward product type taxonomy, add it.
    if (!get_term_by('slug', 'reward', 'product_type')) {
        wp_insert_term('reward', 'product_type');
    }
}
register_activation_hook(__FILE__, 'install_taxonomy');

// add the product type to the dropdown
function add_type_to_dropdown($types)
{
    $types['reward'] = __('Reward', 'free-it');

    return $types;
}

// Add reward fields
function add_reward_fields()
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

// General Tab not showing up
add_action('woocommerce_product_options_general_product_data', function () {
    echo '<div class="options_group show_if_reward clear"></div>';
});

// add show_if_reward class to options_group
function enable_product_js()
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
function save_reward_price($post_id)
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
function add_cart_button()
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
