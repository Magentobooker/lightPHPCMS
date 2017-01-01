<?php
class ModelNavMenu extends Model {

	public function getNavs($parent_id = 0) {

		$sql = "SELECT nmp.nav_menu_id AS nav_menu_id, nmn2.name AS name, nm1.url, nm1.parent_id, nm1.sort_order FROM " . DB_PREFIX . "nav_menu_path nmp LEFT JOIN " . DB_PREFIX . "nav_menu nm1 ON (nmp.nav_menu_id = nm1.nav_menu_id) LEFT JOIN " . DB_PREFIX . "nav_menu nm2 ON (nmp.path_id = nm2.nav_menu_id) LEFT JOIN " . DB_PREFIX . "nav_menu_name nmn1 ON (nmp.path_id = nmn1.nav_menu_id) LEFT JOIN " . DB_PREFIX . "nav_menu_name nmn2 ON (nmp.nav_menu_id = nmn2.nav_menu_id) WHERE nm1.parent_id = '" . $parent_id . "' AND nmn1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND nmn2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

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

}