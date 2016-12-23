<?php

class ControllerCommonHome extends Controller {
	public function index() {
		
		$this->load->language('common/home');
		
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));
		
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/home.phtml')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/common/home.phtml', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/common/home.phtml', $data));
		}

	}
}