<?php

/**
 * Template to print the rewards on the campaign popup
 */
defined('ABSPATH') || exit;

$post = get_post($_POST['campaign']);

$rewards = freeit_functions()->check_post_rewards($post->ID);


$rewards_html .= '<div class="rewards">';



$campaign_rewards_a = [];
foreach ($rewards as $key => $reward) {
    $campaign_rewards_a[$key] = [
        'wpneo_rewards_pladge_amount' => get_post_meta($reward, '_freeit_rewards_pladge_amount', true),
        'wpneo_rewards_image_field'   => get_post_meta($reward, '_freeit_rewards_image_field', true),
        'wpneo_rewards_description'   => get_post_meta($reward, '_freeit_rewards_description', true),
        'wpneo_rewards_endmonth'      => get_post_meta($reward, '_freeit_rewards_endmonth', true),
        'wpneo_rewards_endyear'       => get_post_meta($reward, '_freeit_rewards_endyear', true),
        'wpneo_rewards_item_limit'    => get_post_meta($reward, '_freeit_rewards_item_limit', true),
        'reward_id'                   => $reward
    ];
}

if (is_array($campaign_rewards_a)) {
    if (count($campaign_rewards_a) > 0) {

        $i      = 0;
        $amount = array();

        $rewards_html .= '<h4>' . __('Select a reward', 'wp-crowdfunding') . '</h4>';
        foreach ($campaign_rewards_a as $key => $row) {
            $amount[$key] = $row['wpneo_rewards_pladge_amount'];
        }
        array_multisort($amount, SORT_ASC, $campaign_rewards_a);

        foreach ($campaign_rewards_a as $key => $value) {
            $key++;
            $i++;
            $quantity = '';

            $post_id    = get_the_ID();
            $min_data   = $value['wpneo_rewards_pladge_amount'];
            $max_data   = '';
            $orders     = 0;
            (!empty($campaign_rewards_a[$i]['wpneo_rewards_pladge_amount'])) ? ($max_data = $campaign_rewards_a[$i]['wpneo_rewards_pladge_amount'] - 1) : ($max_data = 9000000000);
            if ($min_data != '') {
                $orders = wpcf_campaign_order_number_data($min_data, $max_data, $post_id);
            }
            if ($value['wpneo_rewards_item_limit']) {
                $quantity = 0;
                if ($value['wpneo_rewards_item_limit'] >= $orders) {
                    $quantity = $value['wpneo_rewards_item_limit'] - $orders;
                }
            }

            $rewards_html .= '<div class="tab-rewards-wrapper';
            ($quantity === 0) ? $rewards_html .= ' disable' : $rewards_html .= '';
            $rewards_html .= '"><div class="wpneo-shadow wpneo-padding15 wpneo-clearfix"><h3>';

            if (function_exists('wc_price')) {
                $rewards_html .= wc_price($value['wpneo_rewards_pladge_amount']);
                if ('true' != get_option('wpneo_reward_fixed_price', '')) {
                    !empty($campaign_rewards_a[$i]['wpneo_rewards_pladge_amount']) ? $rewards_html .= ' - ' . wc_price($campaign_rewards_a[$i]['wpneo_rewards_pladge_amount'] - 1) : $rewards_html .= __(" or more", 'wp-crowdfunding');
                }
            }

            $rewards_html .= '</h3><div>';
            $rewards_html .= wpautop(wp_unslash($value['wpneo_rewards_description']));
            $rewards_html .= '</div>';

            if ($value['wpneo_rewards_image_field']) {
                $rewards_html .= '<div class="wpneo-rewards-image"><img src="' . wp_get_attachment_url($value["wpneo_rewards_image_field"]) . '"/>';
            }

            if (!empty($value['wpneo_rewards_endmonth']) || !empty($value['wpneo_rewards_endyear'])) {
                $month = date_i18n("F", strtotime($value['wpneo_rewards_endmonth']));
                $year = date_i18n("Y", strtotime($value['wpneo_rewards_endyear'] . '-' . $month . '-15'));

                $rewards_html .= "<h4>{$month}, {$year}</h4>";
                $rewards_html .= '<div>' . __('Estimated Delivery', 'wp-crowdfunding') . '</div>';
            }

            if (wpcf_function()->is_campaign_valid($post->ID)) {
                if (wpcf_function()->is_campaign_started($post->ID)) {
                    $rewards_html .= '<div class="tab-rewards-submit-form-style1">';
                    if ($quantity === 0) {
                        $rewards_html .= '<span class="wpneo-error">' . __('Reward no longer available.', 'wp-crowdfunding') . '</span>';
                    } else {
                        $rewards_html .= '<form enctype="multipart/form-data" method="post" class="cart">';
                        $rewards_html .= '<input type="hidden" value="' . $value['wpneo_rewards_pladge_amount'] . '" name="wpneo_donate_amount_field" />
                        <input type="hidden" value="' . json_encode($value) . '" name="wpneo_selected_rewards_checkout" />
                        <input type="hidden" value="' . $key . '" name="wpneo_rewards_index" />
                        <input type="hidden" value="' . esc_attr($post->post_author) . '" name="_cf_product_author_id">
                        <input type="hidden" value="' . $value['reward_id'] . '" name="reward_id">
                        <input type="hidden" value="' . esc_attr($post->ID) . '" name="add-to-cart">
                        <button type="submit" class="select_rewards_button">' . __('Select Reward', 'wp-crowdfunding') . '</button>';
                        $rewards_html .= '</form>';
                    }
                    $rewards_html .= '</div>';
                }
            } else {
                $rewards_html .= '<div class="overlay until-date"><div><div><span class="info-text">';

                if (wpcf_function()->is_reach_target_goal($post->ID)) {
                    $rewards_html .= __('Campaign already completed.', 'wp-crowdfunding');
                } else {
                    if (wpcf_function()->is_campaign_started($post->ID)) {
                        $rewards_html .= __('Reward is not valid.', 'wp-crowdfunding');
                    } else {
                        $rewards_html .= __('Campaign is not started.', 'wp-crowdfunding');
                    }
                }

                $rewards_html .= '</span></div></div></div>';
            }

            if ($min_data != '') {
                $rewards_html .= '<div>' . $orders . ' ' . __('backers', 'wp-crowdfunding') . '</div>';
            }

            if ($value['wpneo_rewards_item_limit']) {
                $rewards_html .= '<div>' . $quantity . __(' rewards left', 'wp-crowdfunding') . '</div>';
            }

            $rewards_html .= '</div></div></div>';
        }
    }
}
$rewards_html .= '</div>';
$rewards_html .= '<div style="clear: both"></div>';
