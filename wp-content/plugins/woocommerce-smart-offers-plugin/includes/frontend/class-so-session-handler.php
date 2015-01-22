<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('SO_Session_Handler')) {

    Class SO_Session_Handler {

        function __construct() {
            
        }   
        
        /**
	 * Set variable name and it's value in session
	 */
        static function so_set_session_variables($name, $value) {
            global $woocommerce;

            $session_variables = array('sa_smart_offers_accepted_offer_ids', 'sa_smart_offers_skipped_offer_ids');

            if (isset($woocommerce->session)) {

                if (in_array($name, $session_variables)) {

                    $ids = (!isset($woocommerce->session->$name) && !is_array($woocommerce->session->$name) ) ? array() : $woocommerce->session->$name;
                    $ids[] = $value;
                    $woocommerce->session->$name = array_unique($ids);
                } else {
                    $woocommerce->session->$name = $value;
                }
            } else {

                if (in_array($name, $session_variables)) {

                    if (!isset($_SESSION [$name])) {
                        $_SESSION [$name] = array();
                    }

                    $_SESSION [$name][] = $value;
                } else {

                    $_SESSION [$name] = $value;
                }
            }
        }

        /**
	 * Check whether a variable is set or not in session
	 */
        static function check_session_set_or_not($name) {
            global $woocommerce;

            if (isset($woocommerce->session)) {
                $bool = ( isset($woocommerce->session->$name) ) ? true : false;
            } else {
                $bool = ( isset($_SESSION[$name]) ) ? true : false;
            }

            return $bool;
        }

        /**
	 * return a value of a variable set in session
	 */
        static function so_get_session_value($name) {
            global $woocommerce;

            if (isset($woocommerce->session)) {
                if (isset($woocommerce->session->$name))
                    return $woocommerce->session->$name;
            } else {
                if (isset($_SESSION[$name]))
                    return $_SESSION[$name];
            }
        }

        /**
	 * Delete the session variable
	 */
        static function so_delete_session($name) {
            global $woocommerce;
            
            if (isset($woocommerce->session)) {
                // WC 2.0
               unset($woocommerce->session->$name);
                
            } else {
                // old style
                unset($_SESSION[$name]);
            }
        }
        
        /**
	 * unset offer ids from accept/skip variable in session
	 */
        static function unset_offer_ids_from_session($offer_ids_to_unset) {
            global $woocommerce;

            //Checking whehter session is set or not.
            $skipped_session_variable = self::check_session_set_or_not('sa_smart_offers_skipped_offer_ids');
            $accepted_session_variable = self::check_session_set_or_not('sa_smart_offers_accepted_offer_ids');

            // Getting skipped/accepted ids of session.
            $skipped_ids_in_session = ( $skipped_session_variable ) ? self::so_get_session_value('sa_smart_offers_skipped_offer_ids') : array();
            $accepted_ids_in_session = ( $accepted_session_variable ) ? self::so_get_session_value('sa_smart_offers_accepted_offer_ids') : array();

            if (!empty($offer_ids_to_unset)) {

                $offer_ids_to_unset = array_unique($offer_ids_to_unset);

                if (!empty($skipped_ids_in_session) || !empty($accepted_ids_in_session)) {

                    foreach ($offer_ids_to_unset as $offer_id) {

                        if (in_array($offer_id, $skipped_ids_in_session)) {

                            $key = array_search($offer_id, $woocommerce->session->sa_smart_offers_skipped_offer_ids);
                            unset($skipped_ids_in_session[$key]);
                        }

                        if (in_array($offer_id, $accepted_ids_in_session)) {

                            $key = array_search($offer_id, $accepted_ids_in_session);
                            unset($accepted_ids_in_session[$key]);
                        }
                    }

                    if ($skipped_session_variable) {
                        if (isset($woocommerce->session)) {
                            $woocommerce->session->set('sa_smart_offers_skipped_offer_ids', $skipped_ids_in_session);
                        } else {
                            $_SESSION['sa_smart_offers_skipped_offer_ids'] = $skipped_ids_in_session;
                        }
                    }
                    if ($accepted_session_variable) {
                        if (isset($woocommerce->session)) {
                            $woocommerce->session->set('sa_smart_offers_accepted_offer_ids', $accepted_ids_in_session);
                        } else {
                            $_SESSION['sa_smart_offers_accepted_offer_ids'] = $accepted_ids_in_session;
                        }
                    }
                }
            }
        }


    }
}