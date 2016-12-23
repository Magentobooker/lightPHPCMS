<?php
class ControllerContentPage extends Controller {
	public function index() {
		$this->load->language('content/page');

		$this->load->model('content/page');

		if (isset($this->request->get['page_id'])) {
			$page_id = (int)$this->request->get['page_id'];
		} else {
			$page_id = 0;
		}

		$page_info = $this->model_content_page->getPage($page_id);

		if ($page_info) {
			$this->document->setTitle($page_info['meta_title'] . ' - ' . $this->config->get('config_meta_title'));
			$this->document->setDescription($page_info['meta_description'] . ' - ' . $this->config->get('config_meta_description'));
			$this->document->setKeywords($page_info['meta_keyword'] . ' - ' . $this->config->get('config_meta_keyword'));

			$data['heading_title'] = $page_info['title'];

			$data['description'] = html_entity_decode($page_info['description'], ENT_QUOTES, 'UTF-8');

			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/content/page.phtml')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/content/page.phtml', $data));
			} else {
				$this->response->setOutput($this->load->view('default/template/content/page.phtml', $data));
			}
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('content/page', 'page_id=' . $page_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.phtml')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/error/not_found.phtml', $data));
			} else {
				$this->response->setOutput($this->load->view('default/template/error/not_found.phtml', $data));
			}
		}
	}
}