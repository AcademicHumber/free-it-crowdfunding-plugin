<?php

namespace Free_It;

if (!defined('ABSPATH')) {
    exit;
}

class FreeIT_Crowdfunding_AddOns
{
    function __construct()
    {
        add_action('new_crowd_funding_campaign_option',             array($this, 'add_minimum_funding_field'));            // Adds minimum funding field on single campaign admin form
        add_action('woocommerce_process_product_meta_crowdfunding', array($this, 'process_minimum_funding_required'));     // Process minimum required fund when campaign is published

    }

    /**
     * Adds minimum funding field on single campaign admin form
     */
    public function add_minimum_funding_field()
    {
        // Location of this campaign
        woocommerce_wp_text_input(
            array(
                'id'                => 'freeit-minimum-funding-required',
                'label'             => __('Minimum funding required', 'wp-crowdfunding'),
                'placeholder'       => __('Percentage', 'wp-crowdfunding'),
                'description'       => __('The minimum funding percentage required to start development, once is reached, the money will be transfered to campaign owner', 'wp-crowdfunding'),
                'type'              => 'number',
                'custom_attributes' => array('max' => 100, 'min' => 1, 'maxlength' => 3)
            )
        );
    }

    /**
     * Process minimum funding required when the campaign is published
     */
    public function process_minimum_funding_required($post_id)
    {
        $minimim_funding = sanitize_text_field($_POST['freeit-minimum-funding-required']);
        wpcf_function()->update_meta($post_id, 'freeit-minimum-funding-required', $minimim_funding);
    }
}
