<?php
class ControllerCommonHeader extends Controller {

	public function index() {
		
		$data['title'] = $this->document->getTitle();

		$this->load->language('common/header');
		
		$data['language'] = $this->load->controller('common/language');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/header.phtml')) {
			return $this->load->view($this->config->get('config_template') . '/template/common/header.phtml', $data);
		} else {
			return $this->load->view('default/template/common/header.phtml', $data);
		}
		
	}
}