<?php
class ModelContentPage extends Model {
	public function getPage($page_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "page p LEFT JOIN " . DB_PREFIX . "page_description pd ON (p.page_id = pd.page_id) WHERE p.page_id = '" . (int)$page_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1'");

		return $query->row;
	}
}