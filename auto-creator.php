<?php

/**
 * Creates or updates rewards of the current crowdgunfind product.
 * 
 */
function manage_rewards($post_id)
{

    $reward_ids = check_post_rewards($post_id);


    // WP Crowdfunding handles rewards weird
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

add_action('woocommerce_process_product_meta_crowdfunding', 'manage_rewards');


/**
 *  Check rewards associated to the current campaign ID
 * 
 * @param int       $post_id     Post ID.
 * @param string    $key         Optional. The meta key of the campaign
 */
function check_post_rewards($post_id, $key = '_freeit_rewards_campaign_id')
{

    global $wpdb;

    $query = $wpdb->get_results($wpdb->prepare("SELECT post_id from {$wpdb->postmeta} WHERE meta_key = '{$key}' AND meta_value = %d", $post_id));

    $reward_ids = [];

    foreach ($query as $value) {
        array_push($reward_ids, $value->post_id);
    }

    return $reward_ids;
}

/**
 *  Debug function
 */
function check_post($post_id)
{
    echo '<pre>';
    print_r($_POST);
    echo '<hr>';
    echo '</pre>';

    wp_die();
}

//add_action('woocommerce_process_product_meta_crowdfunding', 'check_post');


/*add_filter('woocommerce_add_cart_item', function ($array, $int) {
    echo '<pre>';
    print_r($array);
    echo '</pre>';

    wp_die();
}, 10, 3);*/
