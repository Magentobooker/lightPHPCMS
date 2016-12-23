<?php
class ModelCatalogPage extends Model {
	public function addPage($data) {
		$this->event->trigger('pre.admin.page.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "page SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "'");

		$page_id = $this->db->getLastId();

		foreach ($data['page_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "page_description SET page_id = '" . (int)$page_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['keyword'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'page_id=" . (int)$page_id . "', keyword = '" . $this->db->escape(trim($data['keyword'])) . "'");
		}

		$this->cache->delete('page');

		$this->event->trigger('post.admin.page.add', $page_id);

		return $page_id;
	}

	public function editPage($page_id, $data) {
		$this->event->trigger('pre.admin.page.edit', $data);

		$this->db->query("UPDATE " . DB_PREFIX . "page SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "' WHERE page_id = '" . (int)$page_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "page_description WHERE page_id = '" . (int)$page_id . "'");

		foreach ($data['page_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "page_description SET page_id = '" . (int)$page_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'page_id=" . (int)$page_id . "'");

		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'page_id=" . (int)$page_id . "', keyword = '" . $this->db->escape(trim($data['keyword'])) . "'");
		}

		$this->cache->delete('page');

		$this->event->trigger('post.admin.page.edit', $page_id);
	}

	public function deletePage($page_id) {
		$this->event->trigger('pre.admin.page.delete', $page_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "page WHERE page_id = '" . (int)$page_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "page_description WHERE page_id = '" . (int)$page_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'page_id=" . (int)$page_id . "'");

		$this->cache->delete('page');

		$this->event->trigger('post.admin.page.delete', $page_id);
	}

	public function getPage($page_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'page_id=" . (int)$page_id . "') AS keyword FROM " . DB_PREFIX . "page WHERE page_id = '" . (int)$page_id . "'");

		return $query->row;
	}

	public function getPages($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "page i LEFT JOIN " . DB_PREFIX . "page_description id ON (i.page_id = id.page_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			$sort_data = array(
				'id.title',
				'i.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY id.title";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			$page_data = $this->cache->get('page.' . (int)$this->config->get('config_language_id'));

			if (!$page_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page i LEFT JOIN " . DB_PREFIX . "page_description id ON (i.page_id = id.page_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY id.title");

				$page_data = $query->rows;

				$this->cache->set('page.' . (int)$this->config->get('config_language_id'), $page_data);
			}

			return $page_data;
		}
	}

	public function getPageDescriptions($page_id) {
		$page_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_description WHERE page_id = '" . (int)$page_id . "'");

		foreach ($query->rows as $result) {
			$page_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $page_description_data;
	}

	public function getTotalPages() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "page");

		return $query->row['total'];
	}
}