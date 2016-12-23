<?php
class ControllerCommonMenu extends Controller {

	public function index() {
	
		$this->load->language('common/menu');
		
		$data['text_dashboard']		= $this->language->get('text_dashboard');
		
		
		$data['text_catalog']		= $this->language->get('text_catalog');
		$data['text_category']		= $this->language->get('text_category');
		$data['text_product']		= $this->language->get('text_product');
		$data['text_page']			= $this->language->get('text_page');
		$data['text_nav_menu']		= $this->language->get('text_nav_menu');
		$data['text_menu']			= $this->language->get('text_menu');
		$data['text_system']		= $this->language->get('text_system');
		$data['text_setting']		= $this->language->get('text_setting');
		$data['text_design']		= $this->language->get('text_design');
		$data['text_layout']		= $this->language->get('text_layout');
		$data['text_user'] 			= $this->language->get('text_users');
		$data['text_users'] 		= $this->language->get('text_users');
		$data['text_user_group']	= $this->language->get('text_user_group');
		$data['text_localisation'] 	= $this->language->get('text_localisation');
		$data['text_language'] 		= $this->language->get('text_language');
		$data['text_extension'] 	= $this->language->get('text_extension');
		$data['text_tools'] 		= $this->language->get('text_tools');
		$data['text_error_log'] 	= $this->language->get('text_error_log');

		$data['home'] 				= $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL');
		$data['category'] 			= $this->url->link('catalog/category', 'token=' . $this->session->data['token'], 'SSL');
		$data['product'] 			= $this->url->link('catalog/product', 'token=' . $this->session->data['token'], 'SSL');
		$data['page'] 				= $this->url->link('catalog/page', 'token=' . $this->session->data['token'], 'SSL');
		$data['menu'] 				= $this->url->link('nav/menu', 'token=' . $this->session->data['token'], 'SSL');
		$data['setting'] 			= $this->url->link('setting/store', 'token=' . $this->session->data['token'], 'SSL');
		$data['layout'] 			= $this->url->link('design/layout', 'token=' . $this->session->data['token'], 'SSL');
		$data['user']				= $this->url->link('user/user', 'token=' . $this->session->data['token'], 'SSL');
		$data['user_group']			= $this->url->link('user/user_permission', 'token=' . $this->session->data['token'], 'SSL');
		$data['language'] 			= $this->url->link('localisation/language', 'token=' . $this->session->data['token'], 'SSL');
		$data['error_log'] 			= $this->url->link('tool/error_log', 'token=' . $this->session->data['token'], 'SSL');

		return $this->load->view('common/menu.phtml', $data);
	}
}
