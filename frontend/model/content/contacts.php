<?php
class ModelContentContacts extends Model {
	
	function addContacts($data) {

		$sql = "INSERT INTO `contacts` SET name='" . $this->db->escape(trim($data['name'])) . "', email='" . $this->db->escape(trim($data['email'])) . "', phone='" . $this->db->escape(trim($data['phone'])) . "', question='" . $this->db->escape(trim($data['question'])) . "', ip='" . $data['ip'] . "', date_added=NOW()";

		$this->db->query($sql);
		$contacts_id = $this->db->getLastId();
		
		$subject	= 'USGPH CONTACT FORM';
		$text		=  'Name: ' . trim($data['name']) . "\n\n";
		$text		.=  'Email: ' . trim($data['email']) . "\n\n";
		$text		.=  'Phone: ' . trim($data['phone']) . "\n\n";
		$text		.=  'Question: ' . trim($data['question']);
		
		$mail = new Mail($this->config->get('config_mail'));
		$mail->setTo($this->config->get('config_email'));
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender($this->config->get('config_name'));
		$mail->setSubject($subject);
		$mail->setText(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
		$mail->send();
		
		$this->db->query("UPDATE `contacts` SET email_sent = '1' WHERE contacts_id = '" . $contacts_id . "'");
		
		return $contacts_id;
	}
}