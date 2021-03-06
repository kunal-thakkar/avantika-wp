<?php

defined('ABSPATH') || die('Access Denied');

class EcommercewdControllerOrders extends EcommercewdController {
  ////////////////////////////////////////////////////////////////////////////////////////
  // Events                                                                             //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Constants                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Variables                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Constructor & Destructor                                                           //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Public Methods                                                                     //
  ////////////////////////////////////////////////////////////////////////////////////////
  public function displayorder($params) {
    $this->check_user_privileges();
    parent::display($params);
  }

  public function displayorders($params) {
    $this->check_user_privileges();
    parent::display($params);
  }

  public function printorder($params) {
    $this->check_user_privileges();
    parent::display($params);	
  }

	public function pdfinvoice() {
		$model = WDFHelper::get_model();
		$order_row = $model->get_print_order();
	
		$pdfinvoice_model = WDFHelper::get_model('pdfinvoice');
		$options = $pdfinvoice_model->get_invoice_options();
		
		EcommercewdOrder::get_pdf_invoice($order_row,$options);
	}
  ////////////////////////////////////////////////////////////////////////////////////////
  // Getters & Setters                                                                  //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Private Methods                                                                    //
  ////////////////////////////////////////////////////////////////////////////////////////
  private function check_user_privileges() {
    // If not registered users can't checkout and user is not logged in goto login page.
    $model_options = WDFHelper::get_model('options');
    $options = $model_options->get_options();

    if (($options->checkout_allow_guest_checkout == 0) && (!is_user_logged_in())) {
      $model_options->enqueue_message(__('Please login', 'wde'), 'warning');
      wp_redirect(get_permalink($options->option_usermanagement_page));
      exit;
    }
  }
  ////////////////////////////////////////////////////////////////////////////////////////
  // Listeners                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
}