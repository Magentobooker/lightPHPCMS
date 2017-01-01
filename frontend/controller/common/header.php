<?php
class ControllerCommonHeader extends Controller {

	public function index() {
		
		$data['title'] = $this->document->getTitle();

		$this->load->language('common/header');
		
		$data['language'] = $this->load->controller('common/language');

		$data['website_name'] = $this->config->get('config_name');

		// Menu
		$this->load->model('nav/menu');

		$this->load->model('nav/menu');

		$data['navs'] = array();

		$navs = $this->model_nav_menu->getNavs(0);

		foreach ($navs as $nav) {

			if ($nav['parent_id'] == 0) {

				// Level 2
				$children_data = array();

				$children = $this->model_nav_menu->getNavs($nav['nav_menu_id']);

				foreach ($children as $child) {

					$children_data[] = array(
						'name'  => $child['name'],
						'url'  => $child['url']
					);
				}

				// Level 1
				$data['navs'][] = array(
					'name'     => $nav['name'],
					'children' => $children_data,
					'url'     => $nav['url']
				);
			}
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/header.phtml')) {
			return $this->load->view($this->config->get('config_template') . '/template/common/header.phtml', $data);
		} else {
			return $this->load->view('default/template/common/header.phtml', $data);
		}
		
	}
}