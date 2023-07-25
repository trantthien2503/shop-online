<?php
/**
 * Plugin Name: Momo Payment Gateway
 * Plugin URI: https://yourwebsite.com/
 * Description: A payment gateway plugin for Momo.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com/
 */


// Register the plugin with WordPress
function momo_payment_gateway_register()
{
    include_once('momo-payment-gateway/momo-payment-gateway.php');
}
add_action('plugins_loaded', 'momo_payment_gateway_register');
add_action('woocommerce_api_wc_gateway_momo', 'process_momo_response');

function process_momo_response()
{
    $gateway = new WC_Gateway_Momo();
    $gateway->process_response();
}

function init_momo_gateway()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_Gateway_Momo extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id = 'momo';
            $this->has_fields = false;
            $this->method_title = 'Momo Payment Gateway';
            $this->method_description = 'Pay with Momo';

            $this->supports = array(
                'products',
            );

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->merchant_code = $this->get_option('merchant_code');
            $this->access_key = $this->get_option('access_key');
            $this->secret_key = $this->get_option('secret_key');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable Momo Payment Gateway', 'woocommerce'),
                    'default' => 'no',
                ),
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                    'default' => __('Momo', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => __('Description', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                    'default' => __('Pay with Momo', 'woocommerce'),
                ),
                'merchant_code' => array(
                    'title' => __('Merchant Code', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Enter your Momo merchant code.', 'woocommerce'),
                    'default' => '',
                    'desc_tip' => true,
                ),
                'access_key' => array(
                    'title' => __('Access Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Enter your Momo access key.', 'woocommerce'),
                    'default' => '',
                    'desc_tip' => true,
                ),
                'secret_key' => array(
                    'title' => __('Secret Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Enter your Momo secret key.', 'woocommerce'),
                    'default' => '',
                    'desc_tip' => true,
                ),
            );
        }

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);

            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true),
            );
        }

        public function receipt_page($order_id)
        {
            $order = wc_get_order($order_id);

            $amount = $order->get_total();
            $order_id = $order->get_id();
            $order_info = 'Order #' . $order_id;

            $request_id = uniqid();

            $return_url = $this->get_return_url($order);

            $notify_url = str_replace('https:', 'http:', add_query_arg('wc-api', 'wc_gateway_momo', home_url('/')));

            $data = array(
                'partnerCode' => $this->merchant_code,
                'accessKey' => $this->access_key,
                'requestId' => $request_id,
                'amount' => $amount,
                'orderId' => $order_id,
                'orderInfo' => $order_info,
                'returnUrl' => $return_url,
                'notifyUrl' => $notify_url,
                'extraData' => '',
            );

            $signature = hash_hmac('sha256', implode('', $data), $this->secret_key);

            $data['signature'] = $signature;

            $redirect_url = 'https://test-payment.momo.vn/gw_payment/transactionProcessor';

            $args = array(
                'body' => $data,
            );

            $response = wp_remote_post($redirect_url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                echo 'Something went wrong: ' . $error_message;
            } else {
                $response_body = wp_remote_retrieve_body($response);
                $response_data = json_decode($response_body, true);

                if ($response_data['errorCode'] == 0) {
                    $redirect_url = $response_data['payUrl'];
                    wp_redirect($redirect_url);
                    exit;
                } else {
                    $error_message = $response_data['localMessage'];
                    echo 'Something went wrong: ' . $error_message;
                }
            }
        }

        public function process_response()
        {
            $data = $_REQUEST;

            if (isset($data['errorCode']) && $data['errorCode'] == 0) {
                $order_id = $data['orderId'];
                $transaction_id = $data['transId'];

                $order = wc_get_order($order_id);

                if ($order->get_status() == 'processing') {
                    wp_send_json_success('Payment is already processed.');
                } else {
                    $order->set_transaction_id($transaction_id);
                    $order->payment_complete();
                    wp_send_json_success('Payment is processed successfully.');
                }
            } else {
                wp_send_json_error('Payment is failed.');
            }
        }
    }

    function add_momo_gateway($methods)
    {
        $methods[] = 'WC_Gateway_Momo';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_momo_gateway');
}
