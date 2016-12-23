<?php
class ControllerCommonFooter extends Controller {

	public function index() {
		
		$this->load->language('common/footer');
		
		$data['powered'] = sprintf($this->language->get('text_powered'), date('Y', time()), '', '');
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/footer.phtml')) {
			return $this->load->view($this->config->get('config_template') . '/template/common/footer.phtml', $data);
		} else {
			return $this->load->view('default/template/common/footer.phtml', $data);
		}
		
	}
}