<?php
class ControllerContentContacts extends Controller {
	
	private $error			= array();

	public function index() {

		$this->load->language('content/contacts');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('content/contacts');

		$data['heading_title'] = $this->language->get('heading_title');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->request->post['ip'] = $_SERVER['REMOTE_ADDR'];
			$this->model_content_contacts->addContacts($this->request->post);
			unset($this->request->post);
			$success = $this->language->get('success');

		}

		$data['success'] = '';
		if (isset($success)) {
			$data['success'] = $success;
		} else {
			$data['success'] = '';
		}
		
		$data['text_inquiry']			= $this->language->get('text_inquiry');

		$data['entry_name']				= $this->language->get('entry_name');
		$data['entry_email']			= $this->language->get('entry_email');
		$data['entry_phone']			= $this->language->get('entry_phone');
		$data['entry_question']			= $this->language->get('entry_question');
		$data['entry_submit']			= $this->language->get('entry_submit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['phone'])) {
			$data['error_phone'] = $this->error['phone'];
		} else {
			$data['error_phone'] = '';
		}

		if (isset($this->error['question'])) {
			$data['error_question'] = $this->error['question'];
		} else {
			$data['error_question'] = '';
		}

		$data['action'] = 'index.php?route=content/contacts';

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['phone'])) {
			$data['phone'] = $this->request->post['phone'];
		} else {
			$data['phone'] = '';
		}

		if (isset($this->request->post['question'])) {
			$data['question'] = $this->request->post['question'];
		} else {
			$data['question'] = '';
		}

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/content/contacts.phtml')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/content/contacts.phtml', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/content/contacts.phtml', $data));
		}
	}

	public function validate() {

		if ((utf8_strlen(trim($this->request->post['name'])) < 1) || (utf8_strlen(trim($this->request->post['name'])) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($this->request->post['phone']) < 3) || (utf8_strlen($this->request->post['phone']) > 32)) {
			$this->error['phone'] = $this->language->get('error_phone');
		}
		
		if ((utf8_strlen($this->request->post['question']) < 3) || (utf8_strlen($this->request->post['question']) > 320)) {
			$this->error['question'] = $this->language->get('error_question');
		}

		return !$this->error;
	}
}