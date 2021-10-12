<?php

namespace Free_It;

class Functions
{

    /**
     *  Check rewards associated to the current campaign ID
     * 
     * @param int       $post_id     Post ID.
     * @param string    $key         Optional. The meta key of the campaign
     */
    public function check_post_rewards($post_id, $key = '_freeit_rewards_campaign_id')
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
