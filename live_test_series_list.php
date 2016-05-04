<?php 
class ControllerCatalogLiveTestSeriesList extends Controller{

	public function alert_aits()
	{
		 $this->load->model("catalog/live_test_info");
		 $list_of_alerts = $this->data['send_alert_aits'] = $this->model_catalog_live_test_info->sendAlertAits();
		 $template = new Template();
		 
		 foreach($list_of_alerts as $row)		 		
		 {
		 	$template->data['send_alert_aits'] = $row;
		 	$template_file="mail/aitsAlertReport.tpl";
		 	$subject = "Alarm: Zip File not created for ".$row['model_test_name'];
		 	$send_email_to = $row['email'];
		 	$html = $template->fetch($template_file);
		 	echo $html;
		 	$mail = new Mail();
		 	$mail->protocol = $this->config->get('config_mail_protocol');
		 	$mail->parameter = $this->config->get('config_mail_parameter');
		 	$mail->hostname = $this->config->get('config_smtp_host');
		 	$mail->username = $this->config->get('config_smtp_username');
		 	$mail->password = $this->config->get('config_smtp_password');
		 	$mail->port = $this->config->get('config_smtp_port');
		 	$mail->timeout = $this->config->get('config_smtp_timeout');
		 
		 	$mail->setFrom("support@onlinetyari.com");
		 	$mail->setSender("OnlineTyari admin");
		 	$mail->setSubject($subject);
		 	$mail->setHtml($html);
		 	//$mail->setTo("daily-content-alerts@onlinetyari.com");
		 	$mail->setTo($send_email_to);
		 	//$mail->setTo("jitendra.sahu@onlinetyari.com");
		 	if(!$debug)
		 	{
		 		$mail->send();
		 	}
		 	echo "Report sent";
		 }
		 $json=array();
		 $this->load->library('json');
		 $this->response->setOutput(Json::encode($json));
	}
	public function alert_aits_result()
	{
		$this->load->model("catalog/live_test_info");
		$list_of_alerts = $this->data['send_alert_aits_result'] = $this->model_catalog_live_test_info->sendAlertAitsResult();
		foreach($list_of_alerts as $row)
		{
			$template = new Template();
			$template->data['send_alert_aits_result'] = $row;
			$result_time = date('H:i:s Y/m/d', strtotime($row['result_date']));
			$template_file="mail/aitsAlertResultReport.tpl";
			$now_dt=$this->commonutil->getNow();
			if($now_dt<$row['result_date'])
			{
			     $subject = "Alarm: Result for ".$row['model_test_name']." to be published at ".$result_time;
			     $template->data['before_result'] = 1;
			}
			else
			{
				$subject = "Alarm: Result for ".$row['model_test_name']." not published";
				$template->data['before_result'] = 0;
			}
			$send_email_to = $row['email'];
			$template->data['result_time'] = $result_time;
			$html = $template->fetch($template_file);
			echo $html;
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
				
			$mail->setFrom("support@onlinetyari.com");
			$mail->setSender("OnlineTyari admin");
			$mail->setSubject($subject);
			$mail->setHtml($html);
			//$mail->setTo("daily-content-alerts@onlinetyari.com");
			$mail->setTo($send_email_to);
			//$mail->setTo("jitendra.sahu@onlinetyari.com");
		    $mail->send();
			echo "Report sent";
		}
		$json=array();
		$this->load->library('json');
		$this->response->setOutput(Json::encode($json));
	}
}