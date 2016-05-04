<?php
class ModelCatalogLiveTestInfo extends Model {
	public function sendAlertAits()
	{
		$this->load->model("catalog/product");
		$this->load->model("report/zip_status_report");
		$this->load->model("user/user");
		$now_dt=$this->commonutil->getNow();
		$nowtime=new DateTime('+5 days',new DateTimeZone('Asia/Calcutta'));
		$five_dt_time = $nowtime->format("Y-m-d H:i:s");
		$live_test_series_id = 64;
		$sql = "SELECT product_id from oc_product_to_category where category_id = ".$live_test_series_id;
		$product = array();
		$result = $this->db_rd->query($sql);
		foreach ($result->rows as $row)
		{
			 $product[] = $row['product_id'];
		}
		$query = "SELECT tmi.model_test_id ,ttp.product_id, tmi.model_test_name , tmi.test_launch_date from test_type_product as ttp INNER JOIN test_model_info as tmi ON ttp.test_type_id = tmi.test_type_id
				 where tmi.test_launch_date between '".$now_dt."' and '".$five_dt_time."' and ttp.product_id IN (".implode(",",$product).")";
		$result_eng = $this->en_exam_rd_db->query($query);
		$result_hin = $this->hi_exam_rd_db->query($query);
		$list  = array();
		foreach ($result_eng->rows as $row)
		{
		    $zip_status = $this->model_report_zip_status_report->checkZip($row['model_test_id'],1);
			if(!$zip_status)
			{
				    $user_id = $this->model_catalog_product->getProductOwner($row['product_id']);
				    $user = $this->model_user_user->getUser($user_id);
			        $list [] = array(
					                 'model_test_id' => $row['model_test_id'],
					                 'model_test_name' => $row['model_test_name'],
					                 'test_launch_date' => $row['test_launch_date'],
					                 'zip_status' => $zip_status,
			        		         'email' => $user['email'],
			        		         'name' => $user['firstname']." ".$user['lastname']
			        );
			}
		}
		foreach ($result_hin->rows as $row)
		{
			$zip_status = $this->model_report_zip_status_report->checkZip($row['model_test_id'],2);
			if(!$zip_status)
			{
				    $user_id = $this->model_catalog_product->getProductOwner($row['product_id']);
				    $user = $this->model_user_user->getUser($user_id);
			        $list [] = array(
					                 'model_test_id' => $row['model_test_id'],
					                 'model_test_name' => $row['model_test_name'],
					                 'test_launch_date' => $row['test_launch_date'],
					                 'zip_status' => $zip_status,
			        		         'email' => $user['email'],
			        		         'name' => $user['firstname']." ".$user['lastname']
			        );
			}
		}
		return $list;
	}
	public function sendAlertAitsResult()
	{	
		$this->load->model("catalog/product");
		$this->load->model("user/user");
		$now_dt=$this->commonutil->getNow();
		$nowtime=new DateTime('+2 hours',new DateTimeZone('Asia/Calcutta'));
		$two_hr_time = $nowtime->format("Y-m-d H:i:s");
		$live_test_series_id = 64;
		$sql = "SELECT optc.product_id from oc_product_to_category as optc
			    INNER JOIN ot_live_test_series_product as oltsp ON optc.product_id = oltsp.product_id
			    INNER JOIN ot_live_test_series as olts ON oltsp.lt_id = olts.lt_id 
				where olts.lt_reg_result < '".$two_hr_time."' and optc.category_id = ".$live_test_series_id;
		$product = array();
		$result = $this->db_rd->query($sql);
		foreach ($result->rows as $row)
		{
			$product[] = $row['product_id'];
		}
		$query = "SELECT tmi.model_test_id ,ttp.product_id, tmi.model_test_name , tmi.test_launch_date from test_type_product as ttp INNER JOIN test_model_info as tmi ON ttp.test_type_id = tmi.test_type_id
				 where ttp.product_id IN (".implode(",",$product).")";
		$result_eng = $this->en_exam_rd_db->query($query);
		$result_hin = $this->hi_exam_rd_db->query($query);
		$list  = array();
		foreach ($result_eng->rows as $row)
		{
			$result_status = $this->getResultStatus($row['model_test_id'],1);
			if($result_status<=0)
			{
				$user_id = $this->model_catalog_product->getProductOwner($row['product_id']);
				$user = $this->model_user_user->getUser($user_id);
				$result_date = $this->getResultDateFromProductId($row['product_id']);
				$list [] = array(
						'model_test_id' => $row['model_test_id'],
						'model_test_name' => $row['model_test_name'],
					    'result_date' => $result_date,
						'email' => $user['email'],
						'name' => $user['firstname']." ".$user['lastname']
				);
			}
		}
		foreach ($result_hin->rows as $row)
		{
			$result_status = $this->getResultStatus($row['model_test_id'],2);
			if($result_status<=0)
			{
				$user_id = $this->model_catalog_product->getProductOwner($row['product_id']);
				$user = $this->model_user_user->getUser($user_id);
				$result_date = $this->getResultDateFromProductId($row['product_id']);
				$list [] = array(
						'model_test_id' => $row['model_test_id'],
						'model_test_name' => $row['model_test_name'],
						'result_date' => $result_date,
						'email' => $user['email'],
						'name' => $user['firstname']." ".$user['lastname']
				);
			}
		}
		return $list;
	}
	public function getResultDateFromProductId($prod_id)
	{
		$sql = "SELECT olts.lt_reg_result from ot_live_test_series_product as oltsp INNER JOIN ot_live_test_series as olts ON oltsp.lt_id = olts.lt_id
				where oltsp.product_id  = ".$prod_id;
		$result = $this->db_rd->query($sql);
		return $result->rows[0]['lt_reg_result'];
	}
	public function getResultStatus($model_test_id,$lang_id)
	{
		$query = "SELECT count(*) as total from ot_ais_result_data where mock_test_id = ".$model_test_id." and lang_id = ".$lang_id." and status = 1";
		$result=$this->analysis_rd_db->query($query);
		return $result->rows[0]['total'];
	}
	public function validateEnabling($product_id)
	{
		$result= array();
		$query = "Select lt_id from ot_live_test_series_product where product_id = ".$product_id;
		$sql = $this->db_rd->query($query);
		$validation = array();
		$error = array();
	    
		if($sql->num_rows>0)
		{
			 $validation['linked'] = 1;
		}
		else
		{
			$validation['linked'] = 0;
			$error['linked'] = "Not linked with any live Test";
		}
	
		$result['validation'] = $validation;
		$result['error'] = $error;
		return $result;
	}
}
?>