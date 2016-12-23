<?php
class ModelNavMenu extends Model {

	public function addNav($data) {

		$this->db->query("INSERT INTO " . DB_PREFIX . "nav_menu SET url = '" . $this->db->escape($data['url']) . "', parent_id = '" . (int)$data['parent_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

		$nav_menu_id = $this->db->getLastId();

		foreach ($data['nav_menu_name'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "nav_menu_name SET nav_menu_id = '" . (int)$nav_menu_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "nav_menu_path` WHERE nav_menu_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "nav_menu_path` SET `nav_menu_id` = '" . (int)$nav_menu_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "nav_menu_path` SET `nav_menu_id` = '" . (int)$nav_menu_id . "', `path_id` = '" . (int)$nav_menu_id . "', `level` = '" . (int)$level . "'");

		$this->cache->delete('nav_menu');

		return $nav_menu_id;
	}

	public function editNav($nav_menu_id, $data) {

		$this->db->query("UPDATE " . DB_PREFIX . "nav_menu SET url = '" . $this->db->escape($data['url']) . "', parent_id = '" . (int)$data['parent_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE nav_menu_id = '" . (int)$nav_menu_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "nav_menu_name WHERE nav_menu_id = '" . (int)$nav_menu_id . "'");

		foreach ($data['nav_menu_name'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "nav_menu_name SET nav_menu_id = '" . (int)$nav_menu_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "nav_menu_path` WHERE path_id = '" . (int)$nav_menu_id . "' ORDER BY level ASC");

		if ($query->rows) {

			foreach ($query->rows as $nav_menu_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "nav_menu_path` WHERE nav_menu_id = '" . (int)$nav_menu_path['nav_menu_id'] . "' AND level < '" . (int)$nav_menu_path['level'] . "'");

				$path = array();

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "nav_menu_path` WHERE nav_menu_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "nav_menu_path` WHERE nav_menu_id = '" . (int)$nav_menu_path['nav_menu_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "nav_menu_path` SET nav_menu_id = '" . (int)$nav_menu_path['nav_menu_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

					$level++;
				}
			}

		} else {

			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "nav_menu_path` WHERE nav_menu_id = '" . (int)$nav_menu_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "nav_menu_path` WHERE nav_menu_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "nav_menu_path` SET nav_menu_id = '" . (int)$nav_menu_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "nav_menu_path` SET nav_menu_id = '" . (int)$nav_menu_id . "', `path_id` = '" . (int)$nav_menu_id . "', level = '" . (int)$level . "'");
		}

		$this->cache->delete('nav_menu');

	}

	public function deleteNav($nav_menu_id) {

		$this->db->query("DELETE FROM " . DB_PREFIX . "nav_menu_path WHERE nav_menu_id = '" . (int)$nav_menu_id . "'");

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "nav_menu_path WHERE path_id = '" . (int)$nav_menu_id . "'");

		foreach ($query->rows as $result) {
			$this->deletenav_menu($result['nav_menu_id']);
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "nav_menu WHERE nav_menu_id = '" . (int)$nav_menu_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "nav_menu_name WHERE nav_menu_id = '" . (int)$nav_menu_id . "'");

		$this->cache->delete('nav_menu');

	}

	public function getNav($nav_menu_id) {

		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(nmn.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "nav_menu_path nmp LEFT JOIN " . DB_PREFIX . "nav_menu_name nmn ON (nmp.path_id = nmn.nav_menu_id AND nmp.nav_menu_id != nmp.path_id) WHERE nmp.nav_menu_id = nm.nav_menu_id AND nmn.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY nmp.nav_menu_id) AS path FROM " . DB_PREFIX . "nav_menu nm LEFT JOIN " . DB_PREFIX . "nav_menu_name nmn2 ON (nm.nav_menu_id = nmn2.nav_menu_id) WHERE nm.nav_menu_id = '" . (int)$nav_menu_id . "' AND nmn2.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;

	}

	public function getNavs($data = array()) {

		$sql = "SELECT nmp.nav_menu_id AS nav_menu_id, GROUP_CONCAT(nmn1.name ORDER BY nmp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, nm1.url, nm1.parent_id, nm1.sort_order FROM " . DB_PREFIX . "nav_menu_path nmp LEFT JOIN " . DB_PREFIX . "nav_menu nm1 ON (nmp.nav_menu_id = nm1.nav_menu_id) LEFT JOIN " . DB_PREFIX . "nav_menu nm2 ON (nmp.path_id = nm2.nav_menu_id) LEFT JOIN " . DB_PREFIX . "nav_menu_name nmn1 ON (nmp.path_id = nmn1.nav_menu_id) LEFT JOIN " . DB_PREFIX . "nav_menu_name nmn2 ON (nmp.nav_menu_id = nmn2.nav_menu_id) WHERE nmn1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND nmn2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND nmn2.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY nmp.nav_menu_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
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
	}

	public function getNavName($nav_menu_id) {

		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "nav_menu_name WHERE nav_menu_id = '" . (int)$nav_menu_id . "' AND language_id = '1'");

		return $query->row['name'];

	}

	public function getNavNames($nav_menu_id) {

		$nav_menu_name_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "nav_menu_name WHERE nav_menu_id = '" . (int)$nav_menu_id . "'");

		foreach ($query->rows as $result) {
			$nav_menu_name_data[$result['language_id']] = array(
				'name'             => $result['name']
			);
		}

		return $nav_menu_name_data;

	}

	public function getTotalNavs() {

		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "nav_menu");

		return $query->row['total'];
	}

}