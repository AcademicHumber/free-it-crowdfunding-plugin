<?php

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
