<?php
class Manage_Data{
	protected $db;
	private int $page, $totalRows, $suppliers_id;
	private string $sorting_type, $data_type, $keyword_search, $history_type;
	public string $pageTitle;
	private array $actFeeTitOpt;

	public function __construct($db){$this->db = $db;}
	
	public function export(){}
	
	public function AJ_export_MoreInfo(){
		
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$cusTypOpt = array();
		$customerTypeData = $this->db->querypagination("SELECT COALESCE(customer_type, '') AS customer_type FROM customers WHERE accounts_id = $prod_cat_man AND customers_publish = 1 GROUP BY customer_type ORDER BY customer_type ASC", array());
		if($customerTypeData){
			foreach($customerTypeData as $oneRow){
				$customer_type = stripslashes($oneRow['customer_type']);
				if(!empty($customer_type))
					$cusTypOpt[$customer_type] = '';
			}
			$cusTypOpt = array_keys($cusTypOpt);
		}
		$jsonResponse['cusTypOpt'] = $cusTypOpt;

		$catIdOpt = array();
		$productCategoryIdData = $this->db->querypagination("SELECT product.category_id, category.category_name FROM product LEFT JOIN category ON (product.category_id = category.category_id) WHERE product.accounts_id = $prod_cat_man AND product.product_publish = 1 GROUP BY product.category_id ORDER BY category.category_name ASC", array());
		if($productCategoryIdData){
			foreach($productCategoryIdData as $oneRow){
				$category_id = $oneRow['category_id'];
				$catIdOpt[$category_id] = stripslashes(trim((string) $oneRow['category_name']));
			}
		}
		$jsonResponse['catIdOpt'] = $catIdOpt;

		$manOpt = array();
		$productManufacturerData = $this->db->querypagination("SELECT COALESCE(product.manufacturer_id,0) AS manufacturer_id, manufacturer.name FROM product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) WHERE product.accounts_id = $prod_cat_man AND product.product_publish = 1 GROUP BY product.manufacturer_id ORDER BY manufacturer.name ASC", array());
		if($productManufacturerData){
			foreach($productManufacturerData as $oneRow){
				$name = stripslashes(trim((string) $oneRow['name']));
				$manufacturer_id = $oneRow['manufacturer_id'];
				$manOpt[$manufacturer_id] = $name;
			}
		}
		$jsonResponse['manOpt'] = $manOpt;

		$proManOpt = array();
		$productManufacturerData = $this->db->querypagination("SELECT COALESCE(product.manufacturer_id, 0) AS manufacturer_id, manufacturer.name FROM item, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) WHERE item.accounts_id = $accounts_id AND product.product_publish = 1 AND product.product_type = 'Mobile Devices' AND product.product_id = item.product_id GROUP BY product.manufacturer_id ORDER BY manufacturer.name ASC", array());
		if($productManufacturerData){
			foreach($productManufacturerData as $oneRow){
				$name = stripslashes(trim((string) $oneRow['name']));
				$manufacturer_id = $oneRow['manufacturer_id'];
				$proManOpt[$manufacturer_id] = $name;
			}
		}
		$jsonResponse['proManOpt'] = $proManOpt;

		$proTypOpt = array();
		$productProductTypeData = $this->db->querypagination("SELECT COALESCE(product_type,'') AS product_type FROM product WHERE accounts_id = $prod_cat_man AND product_publish = 1 GROUP BY product_type ORDER BY product_type ASC", array());
		if($productProductTypeData){
			foreach($productProductTypeData as $oneRow){
				$product_type = stripslashes(trim((string) $oneRow['product_type']));
				$proTypOpt[$product_type] = '';
			}
			$proTypOpt = array_keys($proTypOpt);
		}
		$jsonResponse['proTypOpt'] = $proTypOpt;

		$repStaOpt = array();
		$repairStatusData = $this->db->querypagination("SELECT COALESCE(status,'') AS status FROM repairs WHERE accounts_id = $accounts_id AND repairs_publish = 1 GROUP BY status ORDER BY status ASC", array());
		if($repairStatusData){
			foreach($repairStatusData as $oneRow){
				$status = stripslashes(trim((string) $oneRow['status']));
				$repStaOpt[$status] = '';
			}
			$repStaOpt = array_keys($repStaOpt);
		}
		$jsonResponse['repStaOpt'] = $repStaOpt;
		
		$useIdOpt = array();
		$getTableData = $this->db->querypagination("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id AND employee_number !='' AND pin !='' AND user_publish = 1 ORDER BY user_first_name ASC, user_last_name ASC", array());
		if($getTableData){
			foreach($getTableData as $oneRow){
				$optlabel = stripslashes(trim("$oneRow[user_first_name] $oneRow[user_last_name]"));
				$user_id = $oneRow['user_id'];
				$useIdOpt[$user_id] = $optlabel;
			}
		}
		$jsonResponse['useIdOpt'] = $useIdOpt;
		
		return json_encode($jsonResponse);
	}
	
	public function exportFieldsList(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$export_type = trim((string) $POST['export_type']??'');
		$oneRow = array();
		
		if(in_array($export_type, array('repairs', 'customer', 'pos', 'invoice', 'product_inventory', 'imei'))){

			$customFieldsSql = "SELECT field_name, field_type FROM custom_fields WHERE accounts_id = $prod_cat_man";
			if($export_type=='repairs'){
				$customFieldsSql .= " AND field_for = 'repairs'";
			}
			elseif(in_array($export_type, array('customer', 'invoice'))){
				$customFieldsSql .= " AND field_for = 'customers'";
			}
			elseif($export_type=='imei'){
				$customFieldsSql .= " AND field_for = 'devices'";
			}
			else{
				$customFieldsSql .= " AND field_for = 'product'";
			}
			$customFieldsSql .= " ORDER BY order_val ASC";
			$queryCFObj = $this->db->query($customFieldsSql, array());			
			if($queryCFObj){
				while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
					$field_type = stripslashes((string) $oneCustomFields->field_type);
					if(!in_array($field_type, array('', 'PDF', 'Picture'))){
						$field_name = stripslashes((string) $oneCustomFields->field_name);
						$oneRow[] = array($field_name, $field_name, $field_name, 0);
					}
				}
			}
		}
		
		return json_encode(array('login'=>'', 'fieldsList'=>$oneRow));
	}
	
   	public function export_data_csv(){

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		if(!isset($_POST)){
			return array($this->db->translate('There is no form submit'));
		}

		$export_type = $_POST['export_type']??'';
		$date_range = $_POST['date_range']??'';
		$scustomer_type = $_POST['customer_type']??'';
		$invoice_salesman = $_POST['invoice_salesman']??'';
		$scategory_id = $_POST['category_id']??'';
		$smanufacturer_id = $_POST['manufacturer_id']??'';
		$imanufacturer_id = $_POST['imanufacturer_id']??'';
		$sproduct_type = $_POST['product_type']??'';
		$sstatus = $_POST['status']??'';
		$ssku = $_POST['sku']??'';
		$save_cost = $_POST['ave_cost']??'';
		$sregular_price = $_POST['regular_price']??'';
		$staxable = $_POST['taxable']??'';
		$slow_inventory_alert = $_POST['low_inventory_alert']??'';
		$user_id = $_POST['user_id']??'';
		$fieldsnameArray = $_POST['fieldsname']??array();
		$cffieldsnameArray = $_POST['cffieldsname']??array();
		$Common = new Common($this->db);
		
		$startdate = $enddate = '';
		if($date_range !=''){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
					$startdate = date('Y-m-d', strtotime($date_rangearray[0])).' 00:00:00';
					$enddate = date('Y-m-d', strtotime($date_rangearray[1])).' 23:59:59';
			}
		}
		
		$newline = "\r\n";
		$enclosure = '"';
		$delim = ",";

		$titleNames = $fieldNames = $cftitleNames = $cffieldNames = array();
		if(!empty($fieldsnameArray)){
			foreach($fieldsnameArray as $oneField){
					list($fieldname, $titlename) = explode(':', $oneField);
					if($fieldname !=''){
						$titleNames[] = str_replace('ID', 'Id', $titlename);
						$fieldNames[] = $fieldname;
					}
			}
		}
		
		if(!empty($cffieldsnameArray)){
            foreach($cffieldsnameArray as $oneField){
                list($cffieldname, $cftitlename) = explode(':', $oneField);
                if($cffieldname !=''){
					if($cftitlename=='ID'){$cftitlename = 'Id';}
					$titleNames[] = $cftitlename;
                    $cffieldNames[] = $cffieldname;
                }				
            }
        }
		
		$data = array();
		$data[] = $titleNames;
		if($export_type == 'customer') {            
			$customFields = $commonFields = array();
			$queryCFObj = $this->db->query("SELECT field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
			if($queryCFObj){
				while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
					$customFields[] = stripslashes($oneCustomFields->field_name);
				}
			}
		
			if(!empty($customFields)){
				$commonFields = array_intersect($customFields, $titleNames);
				if(!empty($commonFields)){
					$fieldNames = array_diff($fieldNames, $customFields);
					$fieldNames[] = 'custom_data';
				}
			}
		
			$sql = "SELECT ".implode(', ', $fieldNames)." FROM customers WHERE accounts_id = $prod_cat_man AND customers_publish = 1";
			$bindData = array();
			if($startdate !='' && $enddate !=''){
					$sql .= " AND (created_on BETWEEN :startdate AND :enddate)";
					$bindData['startdate']= $startdate;
					$bindData['enddate']= $enddate;
			}
			if($scustomer_type !='All'){
					$sql .= " AND customer_type = :customer_type";
					$bindData['customer_type']= $scustomer_type;
			}
			$sql .= " ORDER BY TRIM(CONCAT_WS(' ', first_name, last_name)) ASC";
			//$this->db->writeIntoLog($sql);
			$query = $this->db->query($sql, $bindData);
			if($query){
				$userIds = array();
				if(in_array('user_id', $fieldNames)){
					$tableObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id IN ($prod_cat_man, $accounts_id)", array());
					if($tableObj){
						while($tableOneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$userIds[$tableOneRow->user_id] = stripslashes(trim("$tableOneRow->user_first_name $tableOneRow->user_last_name"));
						}
					}
				}

					while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
						$rowData = array();
						foreach($fieldNames as $oneName){
							if($oneName=='shipping_country' && $oneRow->$oneName==0){
									$rowData[] = '';
							}
							elseif($oneName=='user_id'){
								$rowData[] = array_key_exists($oneRow->$oneName, $userIds) ? $userIds[$oneRow->$oneName]:'';
							}
							elseif($oneName=='custom_data' && $oneRow->$oneName !=''){
									if(!empty($commonFields)){
										$custom_data = unserialize($oneRow->$oneName);
										foreach($commonFields as $oneFieldName){
											$value = '';
											if(!empty($custom_data) && array_key_exists($oneFieldName, $custom_data)){
													$value = $custom_data[$oneFieldName];
											}
											$rowData[] = $value;
										}
									}
									else{
										foreach($commonFields as $oneFieldName){
											$rowData[] = '';
										}
									}
							}
							else{
								$rowData[] = stripslashes(trim((string) $oneRow->$oneName));
							}
						}
						$data[] = $rowData;
					}
			}
		}
      elseif($export_type == 'product_inventory') {
			$customFields = $commonFields = array();
			$queryCFObj = $this->db->query("SELECT field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'product' ORDER BY order_val ASC", array());
			if($queryCFObj){
					while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
						$customFields[] = stripslashes($oneCustomFields->field_name);
					}
			}
			if(!empty($customFields)){
				foreach($customFields as $oneFieldName){
					if($oneFieldName=='ID'){$oneFieldName = 'Id';}
					if(in_array($oneFieldName, $titleNames)){
						$commonFields[] = $oneFieldName;
					}
				}
				if(!empty($commonFields)){
					$fieldNames = array_diff($fieldNames, $customFields);
					$fieldNames[] = 'p.custom_data';
				}
			}
			
			$product_type = '';
			if(!in_array('p.product_type', $fieldNames)){
				$product_type .= ', p.product_type';
			}
         	if(!in_array('p.product_id', $fieldNames)){
				$product_type .= ', p.product_id';
			}
			$sql = "SELECT ".implode(', ', $fieldNames)."$product_type 
				FROM inventory i, product p 
					LEFT JOIN category ON (category.category_id = p.category_id) 
				WHERE i.accounts_id = $accounts_id AND p.product_publish = 1 AND i.product_id = p.product_id";
			$bindData = array();
			if($startdate !='' && $enddate !=''){
					$sql .= " AND (p.created_on BETWEEN :startdate AND :enddate)";
					$bindData['startdate']= $startdate;
					$bindData['enddate']= $enddate;
			}
			if($scategory_id !=''){
					$sql .= " AND p.category_id = :category_id";
					$bindData['category_id']= $scategory_id;
			}
			if($smanufacturer_id !=''){
					$sql .= " AND p.manufacturer_id = :manufacturer_id";
					$bindData['manufacturer_id']= $smanufacturer_id;
			}
			if($sproduct_type !=''){
					$sql .= " AND p.product_type = :product_type";
					$bindData['product_type']= $sproduct_type;
			}

			$sql .= " GROUP BY p.product_id ORDER BY TRIM(CONCAT_WS(' ', p.product_name, p.colour_name, p.storage, p.physical_condition_name)) ASC";
			$query = $this->db->querypagination($sql, $bindData);
			if($query){
				foreach($query as $oneRow){
					$product_id = $oneRow['product_id'];
					$product_type = $oneRow['product_type'];
					$rowData = array();
			
					foreach($fieldNames as $oneName){
						$oneName = str_replace('p.', '', $oneName);
						$oneName = str_replace('i.', '', $oneName);
						$oneName = str_replace('category.', '', $oneName);
						if($oneName=='ave_cost'){
							$ave_cost = 0;
							if(strcmp($product_type, 'Mobile Devices')==0){
								$aveCostData = $Common->mobileProdAveCost($accounts_id, $product_id, ' AND item_publish = 1 AND in_inventory = 1');
								$ave_cost = $aveCostData[0];
							}
							else{
								$ave_cost = $oneRow['ave_cost'];
							}
							$rowData[] = $ave_cost;
						}
						elseif($oneName=='current_inventory'){
							$current_inventory = 0;
							if(strcmp($product_type, 'Mobile Devices')==0){
								$itemObj = $this->db->query("SELECT COUNT(item_id) AS current_inventory FROM item WHERE product_id = $product_id AND accounts_id = $accounts_id AND item_publish = 1 AND in_inventory = 1", array());
								if($itemObj){
									$current_inventory = $itemObj->fetch(PDO::FETCH_OBJ)->current_inventory;
								}
							}
							else{
								$current_inventory = $oneRow['current_inventory'];
							}
							$rowData[] = $current_inventory;
						}
						elseif($oneName=='manufacturer_id'){
							$manufacturer_id = $oneRow[$oneName];
							$manufacturerName = '';
							$manuObj = $this->db->query("SELECT name FROM manufacturer WHERE manufacturer_id = $manufacturer_id", array());
							if($manuObj){
								$manufacturerName = $manuObj->fetch(PDO::FETCH_OBJ)->name;
							}
							$rowData[] = stripslashes(trim((string) $manufacturerName));
						}
						elseif(in_array($oneName, array('taxable', 'require_serial_no', 'manage_inventory_count', 'allow_backorder'))){
							if(trim((string) $oneRow[$oneName])==1){$rowData[] = 'Yes';}
							else{$rowData[] = 'No';}
						}
						elseif($oneName=='custom_data' && !empty($oneRow[$oneName])){
                     		if(!empty($commonFields)){
								$customData = unserialize($oneRow[$oneName]);
								foreach($commonFields as $oneFieldName){
									$value = '';
									if(!empty($customData) && array_key_exists($oneFieldName, $customData)){
											$value = $customData[$oneFieldName];
									}
									$rowData[] = $value;
								}
							}
							else{
								foreach($commonFields as $oneFieldName){
									$rowData[] = '';
								}
							}
						}
						else{
							$rowData[] = stripslashes(trim((string) $oneRow[$oneName]));
						}
               		}

               		$data[] = $rowData;
            	}
         	}			
		}
      elseif($export_type == 'pos') {
         	$ifieldNames = array();
			$ifieldNames[] = 'pos.pos_id, pos_cart.pos_cart_id';
			if(in_array('invoice_no', $fieldNames)){
					$ifieldNames[] = 'pos.invoice_no';
			}
			if(in_array('invoice_salesman', $fieldNames)){
					$ifieldNames[] = 'pos.user_id';
			}
			if(in_array('first_name', $fieldNames) || in_array('contact_no', $fieldNames) || in_array('address', $fieldNames) || in_array('email', $fieldNames) || in_array('customer_type', $fieldNames)){
					$ifieldNames[] = 'pos.customer_id';
			}
			if(in_array('sales_datetime', $fieldNames)){
					$ifieldNames[] = 'pos.sales_datetime';
			}

			$addtable = '';
			if($scategory_id !='' || $smanufacturer_id !='' || $sproduct_type !='' || $ssku !='' || in_array('product_type', $fieldNames) || in_array('manufacturer_id', $fieldNames) || in_array('category_id', $fieldNames) || in_array('sku', $fieldNames)){
				$addtable = ', product';
				if(in_array('product_type', $fieldNames) ){
					$ifieldNames[] = 'product.product_type';
				}
				if(in_array('manufacturer_id', $fieldNames) ){
					$ifieldNames[] = 'product.manufacturer_id';
				}
				if(in_array('category_id', $fieldNames) ){
					$ifieldNames[] = 'product.category_id';
				}
				if(in_array('sku', $fieldNames) ){
					$ifieldNames[] = 'product.sku';
				}
			}

			if(in_array('description', $fieldNames) ){
					$ifieldNames[] = 'pos_cart.description';
			}

			if(in_array('shipping_qty', $fieldNames) || in_array('sales_price', $fieldNames) || in_array('discount', $fieldNames) || in_array('ave_cost', $fieldNames) || in_array('total', $fieldNames) || in_array('profit', $fieldNames)){
					$ifieldNames[] = 'pos_cart.item_type, pos_cart.taxable, pos_cart.shipping_qty, pos_cart.sales_price, pos_cart.discount_is_percent, pos_cart.discount, pos_cart.ave_cost, pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2';
			}

			$sql = "SELECT ".implode(', ', $ifieldNames)." 
				FROM pos, pos_cart$addtable 
				WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2))";
			$bindData = array();
			if($startdate !='' && $enddate !=''){
					$sql .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
					$bindData['startdate']= $startdate;
					$bindData['enddate']= $enddate;
			}
			if($scategory_id !=''){
					$sql .= " AND product.category_id = :category_id";
					$bindData['category_id']= $scategory_id;
			}
			if($smanufacturer_id !=''){
					$sql .= " AND product.manufacturer_id = :manufacturer_id";
					$bindData['manufacturer_id']= $smanufacturer_id;
			}
			if($sproduct_type !=''){
					$sql .= " AND product.product_type = :product_type";
					$bindData['product_type']= $sproduct_type;
			}
			if($addtable !=''){
					$sql .= " AND pos_cart.item_id = product.product_id";
			}

			$sql .= " AND pos.pos_id = pos_cart.pos_id ORDER BY pos.sales_datetime DESC, pos_cart.pos_cart_id ASC";
			//$this->db->writeIntoLog('sql: '.$sql.", bindData:".json_encode($bindData));
			$posData = $this->db->querypagination($sql, $bindData);
			if($posData){

				$customerData = $categoryData = array();
				if((in_array('pos.customer_id', $ifieldNames) && (in_array('first_name', $fieldNames) || in_array('contact_no', $fieldNames) || in_array('address', $fieldNames) || in_array('customer_type', $fieldNames) || in_array('customer_type', $fieldNames))) || in_array('product.category_id', $ifieldNames) || in_array('pos.user_id', $ifieldNames)){
					$customerIds = $categoryIds = $userIds = array();
					foreach($posData as $oneRow1){
						if(in_array('pos.customer_id', $ifieldNames) && (empty($customerIds) || !in_array($oneRow1['customer_id'], $customerIds))){
								$customerIds[] = $oneRow1['customer_id'];
						}
						if(in_array('product.category_id', $ifieldNames) && (empty($categoryIds) || !in_array($oneRow1['category_id'], $categoryIds))){
								$categoryIds[] = $oneRow1['category_id'];
						}
						if(in_array('pos.user_id', $ifieldNames)){
								$userIds[$oneRow1['user_id']] = '';
						}
					}

					if(!empty($customerIds)){
						$customerFields = array();
						if(in_array('first_name', $fieldNames)){array_push($customerFields, "first_name", "last_name");}
						if(in_array('contact_no', $fieldNames)){array_push($customerFields, "contact_no");}
						if(in_array('address', $fieldNames)){array_push($customerFields, "shipping_address_one", "shipping_address_two", "shipping_city", "shipping_state", "shipping_zip", "shipping_country");}
						if(in_array('email', $fieldNames)){array_push($customerFields, "email");}
						if(in_array('customer_type', $fieldNames)){array_push($customerFields, "customer_type");}
						if(!empty($customerFields)){
								$customerSql = "SELECT customers_id, ".implode(', ', $customerFields)." FROM customers WHERE customers_id in (".implode(', ', $customerIds).")";
								$customersObj = $this->db->query($customerSql, array());
								if($customersObj){
									while($customerrow = $customersObj->fetch(PDO::FETCH_OBJ)){
										$customerRow = array();
										if(in_array('first_name', $fieldNames)){
												$customerRow['first_name'] = trim(stripslashes("$customerrow->first_name $customerrow->last_name"));
										}
										if(in_array('contact_no', $fieldNames)){
												$customerRow['contact_no'] = trim((string) stripslashes($customerrow->contact_no));
										}
										if(in_array('address', $fieldNames)){
												$address = '';
												if($customerrow->shipping_address_one !=''){$address .= $customerrow->shipping_address_one;}
												if($customerrow->shipping_address_two !=''){
													if($address != ''){$address .= ', ';}
													$address .= $customerrow->shipping_address_two;
												}
												if($customerrow->shipping_city !=''){
													if($address != ''){$address .= ', ';}
													$address .= $customerrow->shipping_city;
												}
												if($customerrow->shipping_state !=''){
													if($address != ''){$address .= ', ';}
													$address .= $customerrow->shipping_state;
												}
												if($customerrow->shipping_zip !=''){
													if($address != ''){$address .= ' ';}
													$address .= $customerrow->shipping_zip;
												}
												if(!in_array($customerrow->shipping_country, array('', '0'))){
													if($address != ''){$address .= ', ';}
													$address .= $customerrow->shipping_country;
												}
												$customerRow['address'] = $address;
										}
										if(in_array('email', $fieldNames)){
												$customerRow['email'] = trim((string) stripslashes($customerrow->email));
										}
										if(in_array('customer_type', $fieldNames)){
												$customerRow['customer_type'] = trim((string) stripslashes($customerrow->customer_type));
										}
										
										$customerData[$customerrow->customers_id] = $customerRow;
									}
								}
						}
					}
				
					if(!empty($userIds)){
						$tableObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE user_id IN (".implode(', ', array_keys($userIds)).")", array());
						if($tableObj){
								while($tableOneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
									$userIds[$tableOneRow->user_id] = stripslashes(trim("$tableOneRow->user_first_name $tableOneRow->user_last_name"));
								}
						}
					}

					if(!empty($categoryIds)){
						$categoryObj = $this->db->query("SELECT category_id, category_name FROM category WHERE category_id in (".implode(', ', $categoryIds).")", array());
						if($categoryObj){
								while($categoryRow = $categoryObj->fetch(PDO::FETCH_OBJ)){
									$categoryData[$categoryRow->category_id] = $categoryRow->category_name;
								}
						}
					}
				}

				foreach($posData as $oneRow3){
					$rowData = array();

					$pos_id = $oneRow3['pos_id'];
					$pos_cart_id = $oneRow3['pos_cart_id'];

					if(in_array('invoice_no', $fieldNames)){
						$rowData[] = $oneRow3['invoice_no'];
					}
					if(in_array('pos.user_id', $ifieldNames)){
						$Salesman = '';
						if(!empty($userIds) && array_key_exists($oneRow3['user_id'], $userIds)){
								$Salesman = $userIds[$oneRow3['user_id']];
						}
						$rowData[] = $Salesman;
					}
					
					if(in_array('sales_datetime', $fieldNames)){
						if($timeformat=='24 hour'){$invoiceDate =  date($dateformat.' H:i', strtotime($oneRow3['sales_datetime']));}
						else{$invoiceDate =  date($dateformat.' g:i a', strtotime($oneRow3['sales_datetime']));}
						$rowData[] = $invoiceDate;
					}


					if(in_array('first_name', $fieldNames)){
						if(!empty($customerData) && array_key_exists($oneRow3['customer_id'], $customerData)){
								$rowData[] = $customerData[$oneRow3['customer_id']]['first_name'];
						}
						else{$rowData[] = '';}
					}
					if(in_array('contact_no', $fieldNames)){
						if(!empty($customerData) && array_key_exists($oneRow3['customer_id'], $customerData)){
								$rowData[] = $customerData[$oneRow3['customer_id']]['contact_no'];
						}
						else{$rowData[] = '';}
					}
					if(in_array('address', $fieldNames)){
						if(!empty($customerData) && array_key_exists($oneRow3['customer_id'], $customerData)){
								$rowData[] = $customerData[$oneRow3['customer_id']]['address'];
						}
						else{$rowData[] = '';}
					}
					if(in_array('email', $fieldNames)){
						if(!empty($customerData) && array_key_exists($oneRow3['customer_id'], $customerData)){
								$rowData[] = $customerData[$oneRow3['customer_id']]['email'];
						}
						else{$rowData[] = '';}
					}
					if(in_array('customer_type', $fieldNames)){
						if(!empty($customerData) && array_key_exists($oneRow3['customer_id'], $customerData)){
								$rowData[] = $customerData[$oneRow3['customer_id']]['customer_type'];
						}
						else{$rowData[] = '';}
					}

					if(in_array('product_type', $fieldNames) ){
						$rowData[] = $oneRow3['product_type'];
					}
					if(in_array('manufacturer_id', $fieldNames)){
						$manufacturer_id = $oneRow3['manufacturer_id'];
						$manufacturerName = '';
						$manuObj = $this->db->query("SELECT name FROM manufacturer WHERE manufacturer_id = $manufacturer_id", array());
						if($manuObj){
							$manufacturerName = $manuObj->fetch(PDO::FETCH_OBJ)->name;
						}
						$rowData[] = stripslashes(trim((string) $manufacturerName));
					}
					if(in_array('product.category_id', $ifieldNames)){//in_array('category_id', $fieldNames)
						$category_name = '';
						if(!empty($categoryData) && array_key_exists($oneRow3['category_id'], $categoryData)){
								$category_name = $categoryData[$oneRow3['category_id']];
						}
						$rowData[] = $category_name;
					}
					if(in_array('description', $fieldNames)){
						$description = stripslashes(trim((string) $oneRow3['description']));
						if(in_array('product.sku', $ifieldNames)){
							$description = trim(str_replace('('.$oneRow3['sku'].')', '', $description));
						}
						$rowData[] = $description;
					}
					if(in_array('product.sku', $ifieldNames)){
						$rowData[] = $oneRow3['sku'];
					}
				
					if(in_array('shipping_qty', $fieldNames) || in_array('sales_price', $fieldNames) || in_array('discount', $fieldNames) || in_array('ave_cost', $fieldNames) || in_array('total', $fieldNames) || in_array('profit', $fieldNames)){

						$shipping_qty = $oneRow3['shipping_qty'];
						if(in_array('shipping_qty', $fieldNames)){
								$rowData[] = $shipping_qty;
						}

						$sales_price = $oneRow3['sales_price'];
						if(in_array('sales_price', $fieldNames)){
								$rowData[] = number_format($sales_price,2);
						}

						$qtyvalue = round($sales_price*$shipping_qty,2);

						$discount_is_percent = $oneRow3['discount_is_percent'];
						$discount = $oneRow3['discount'];
						if($discount_is_percent>0){
								$discount_value = round($qtyvalue*0.01*$discount,2);
						}
						else{
								$discount_value = round($discount*$shipping_qty,2);
						}

						if(in_array('discount', $fieldNames)){
								$rowData[] = number_format($discount_value,2);
						}

						$total = $qtyvalue-$discount_value;
						if(in_array('total', $fieldNames)){
								$rowData[] = number_format($total,2);
						}

						$item_type = $oneRow3['item_type'];
						$ave_cost = $oneRow3['ave_cost'];

						$qtycost = round($ave_cost*$shipping_qty,2);
						
						if(in_array('ave_cost', $fieldNames)){
								$rowData[] = number_format($qtycost,2);
						}

						if(in_array('profit', $fieldNames)){

							$qtyprofitval = $total-$qtycost;

							$taxable = $oneRow3['taxable'];
							$totalexcl = $total;
							if($taxable>0){
								$tax_inclusive1 = $oneRow3['tax_inclusive1'];
								if($tax_inclusive1>0){
									$taxes_total1 = $Common->calculateTax($total, $oneRow3['taxes_percentage1'], $tax_inclusive1);
									$qtyprofitval = $qtyprofitval-$taxes_total1;
									$totalexcl = $totalexcl-$taxes_total1;
								}
								$tax_inclusive2 = $oneRow3['tax_inclusive2'];
								if($tax_inclusive2>0){
									$taxes_total2 = $Common->calculateTax($total, $oneRow3['taxes_percentage2'], $tax_inclusive2);
									$qtyprofitval = $qtyprofitval-$taxes_total2;
									$totalexcl = $totalexcl-$taxes_total2;
								}
							}

							$qtyprofit = 0;
							if(($totalexcl) !=0){
								$qtyprofit = round(($qtyprofitval*100)/($totalexcl),2);
							}
							$qtyprofitstr = number_format($qtyprofitval,2);
							if($qtyprofitval<0 ){
								$qtyprofitstr = '-'.number_format($qtyprofitval*(-1),2);
							}
							if($qtyprofit<0 ){
								$qtyprofitstr .= ' ('.round($qtyprofit*(-1)).'%)';
							}
							else{
								$qtyprofitstr .= ' ('.round($qtyprofit).'%)';
							}

							$rowData[] = $qtyprofitstr;
						}
					}

					$data[] = $rowData;
				}
            }
        }
        elseif($export_type == 'po') {

			$ifieldNames = array();
            $ifieldNames[] = 'po.po_id, po_items.po_items_id';
            if(in_array('po_number', $fieldNames)){
                $ifieldNames[] = 'po.po_number';
            }
			if(in_array('created_by_username', $fieldNames)){
                $ifieldNames[] = 'po.user_id';
            }
            if(in_array('suppilername', $fieldNames)){
                $ifieldNames[] = 'suppliers.first_name, suppliers.last_name';
            }
            if(in_array('suppliers_invoice_no', $fieldNames)){
                $ifieldNames[] = 'po.suppliers_invoice_no';
            }
            if(in_array('suppliers_invoice_date', $fieldNames)){
                $ifieldNames[] = 'po.invoice_date';
            }
            if(in_array('date_paid', $fieldNames)){
                $ifieldNames[] = 'po.date_paid';
            }
            if(in_array('paid_by', $fieldNames)){
                $ifieldNames[] = 'po.paid_by';
            }
            if(in_array('po_datetime', $fieldNames)){
                $ifieldNames[] = 'po.po_datetime';
            }
			
            $addtable = '';
            if($scategory_id !='' || $smanufacturer_id !='' || $sproduct_type !='' || $ssku !='' || in_array('product_type', $fieldNames) || in_array('manufacturer_id', $fieldNames) || in_array('category_id', $fieldNames) || in_array('sku', $fieldNames)){
                $addtable = ', product';
                if(in_array('product_type', $fieldNames) ){
                    $ifieldNames[] = 'product.product_type';
                }
                if(in_array('manufacturer_id', $fieldNames) ){
                    $ifieldNames[] = 'product.manufacturer_id';
                }
                if(in_array('category_id', $fieldNames) ){
                    $ifieldNames[] = 'product.category_id';
                }
                if(in_array('product_name', $fieldNames) ){
                    $ifieldNames[] = 'product.product_name, product.colour_name, product.storage, product.physical_condition_name';
                }
                if(in_array('sku', $fieldNames) ){
                    $ifieldNames[] = 'product.sku';
                }
            }

            if($addtable == '' && in_array('product_name', $fieldNames) ){
                $ifieldNames[] = 'po_items.product_id';
            }

            if(in_array('received_qty', $fieldNames) || in_array('cost', $fieldNames) || in_array('total', $fieldNames)){
                $ifieldNames[] = 'po_items.item_type, po_items.received_qty, po_items.cost';
            }

            $sql = "SELECT ".implode(', ', $ifieldNames)." 
					FROM po_items$addtable, po";
            if(in_array('suppilername', $fieldNames)){
                $sql .= " LEFT JOIN suppliers ON (po.supplier_id = suppliers.suppliers_id)";
            }
            $sql .= " WHERE po.accounts_id = $accounts_id AND po.po_publish = 1";
            $bindData = array();
            if($startdate !='' && $enddate !=''){
                $sql .= " AND (po.po_datetime BETWEEN :startdate AND :enddate)";
                $bindData['startdate'] = $startdate;
                $bindData['enddate'] = $enddate;
            }
            if($scategory_id !=''){
                $sql .= " AND product.category_id = :category_id";
                $bindData['category_id'] = $scategory_id;
            }
            if($smanufacturer_id !=''){
                $sql .= " AND product.manufacturer_id = :manufacturer_id";
                $bindData['manufacturer_id'] = $smanufacturer_id;
            }
            if($sproduct_type !=''){
                $sql .= " AND product.product_type = :product_type";
                $bindData['product_type']= $sproduct_type;
            }
            if($addtable !=''){
                $sql .= " AND po_items.product_id = product.product_id";
            }
            $sql .= " AND po.po_id = po_items.po_id 
					ORDER BY po.po_datetime DESC, po_items.po_items_id ASC";
            $poData = $this->db->querypagination($sql, $bindData);
            if($poData){
                $categoryData = $productData = array();
                if(in_array('category_id', $fieldNames) || ($addtable == '' && (in_array('product_name', $fieldNames) || in_array('sku', $fieldNames)))){
                    $categoryIds = $productIds = $userIds = array();
                    foreach($poData as $oneRow1){
                        if(in_array('created_by_username', $fieldNames)){
                            $userIds[$oneRow1['user_id']] = '';
                        }
                        if(in_array('category_id', $fieldNames) && !in_array($oneRow1['category_id'], $categoryIds)){
                            array_push($categoryIds, $oneRow1['category_id']);
                        }

                        if($addtable == '' && (in_array('product_name', $fieldNames) || in_array('sku', $fieldNames)) && !in_array($oneRow1['product_id'], $productIds)){
                            array_push($productIds, $oneRow1['product_id']);
                        }
                    }

                    if(!empty($userIds)){
                        $tableObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE user_id in (".implode(', ', array_keys($userIds)).")", array());
                        if($tableObj){
                            while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
                                $userIds[$oneRow->user_id] = trim(stripslashes("$oneRow->user_first_name $oneRow->user_last_name"));
                            }
                        }
                    }

                    if(!empty($categoryIds)){
                        $categoryObj = $this->db->query("SELECT category_id, category_name FROM category WHERE category_id in (".implode(', ', $categoryIds).")", array());
                        if($categoryObj){
                            while($categoryRow = $categoryObj->fetch(PDO::FETCH_OBJ)){
                                $categoryData[$categoryRow->category_id] = $categoryRow->category_name;
                            }
                        }
                    }

                    if(!empty($productIds)){
                        $productObj = $this->db->query("SELECT product_id, product_name, colour_name, storage, physical_condition_name, sku FROM product WHERE product_id in (".implode(', ', $productIds).")", array());
                        if($productObj){
                            while($productRow = $productObj->fetch(PDO::FETCH_OBJ)){
                                $product_name = stripslashes(trim((string) $productRow->product_name));
                                $colour_name = stripslashes(trim((string) $productRow->colour_name));
                                $storage = stripslashes(trim((string) $productRow->storage));
                                $physical_condition_name = stripslashes(trim((string) $productRow->physical_condition_name));

                                if($colour_name !=''){$product_name .= ' '.$colour_name;}
                                if($storage !=''){$product_name .= ' '.$storage;}
                                if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

                                $productData[$productRow->product_id] = array($product_name, $productRow->sku);
                            }
                        }
                    }
                }

                foreach($poData as $oneRow){
                    $rowData = array();

                    $po_id = $oneRow['po_id'];
                    $po_items_id = $oneRow['po_items_id'];

                    if(in_array('po_number', $fieldNames)){
                        $rowData[] = 'P'.$oneRow['po_number'];
                    }

					if(in_array('created_by_username', $fieldNames)){
						$user_id = $oneRow['user_id'];
						$userName = array_key_exists($user_id, $userIds)?$userIds[$user_id]:'';
						$rowData[] = $userName;
					}

                    if(in_array('suppilername', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['first_name'].' '.$oneRow['last_name']));
                    }
					if(in_array('suppliers_invoice_no', $fieldNames)){
						$rowData[] = $oneRow['suppliers_invoice_no'];
					}
					if(in_array('suppliers_invoice_date', $fieldNames)){
						$invoice_date =  '';
						if(!in_array($oneRow['invoice_date'], array('0000-00-00', '1000-01-01'))){
							$invoice_date =  date($dateformat, strtotime($oneRow['invoice_date']));
						}
                        $rowData[] = $invoice_date;
					}
					if(in_array('date_paid', $fieldNames)){
						$date_paid =  '';
						if(!in_array($oneRow['date_paid'], array('0000-00-00', '1000-01-01'))){
							$date_paid =  date($dateformat, strtotime($oneRow['date_paid']));
						}
                        $rowData[] = $date_paid;
					}
					if(in_array('paid_by', $fieldNames)){
						$rowData[] = $oneRow['paid_by'];
					}
                    if(in_array('po_datetime', $fieldNames)){
                        if($timeformat=='24 hour'){$po_datetime =  date($dateformat.' H:i', strtotime($oneRow['po_datetime']));}
                        else{$po_datetime =  date($dateformat.' g:i a', strtotime($oneRow['po_datetime']));}
                        $rowData[] = $po_datetime;
                    }

                    if($addtable != ''){
                        if(in_array('product_type', $fieldNames) ){
                            $rowData[] = $oneRow['product_type'];
                        }
                        if(in_array('manufacturer_id', $fieldNames) ){
							$manufacturer_id = $oneRow['manufacturer_id'];
							$manufacturerName = '';
							$manuObj = $this->db->query("SELECT name FROM manufacturer WHERE manufacturer_id = $manufacturer_id", array());
							if($manuObj){
								$manufacturerName = $manuObj->fetch(PDO::FETCH_OBJ)->name;
							}
							$rowData[] = stripslashes(trim((string) $manufacturerName));
                        }
                        if(in_array('category_id', $fieldNames) ){
                            $category_name = '';
                            if(!empty($categoryData) && array_key_exists($oneRow['category_id'], $categoryData)){
                                $category_name = $categoryData[$oneRow['category_id']];
                            }
                            $rowData[] = $category_name;
                        }

                        if(in_array('product_name', $fieldNames) ){
                            $product_name = stripslashes(trim((string) $oneRow['product_name']));
                            $colour_name = stripslashes(trim((string) $oneRow['colour_name']));
                            $storage = stripslashes(trim((string) $oneRow['storage']));
                            $physical_condition_name = stripslashes(trim((string) $oneRow['physical_condition_name']));

                            if($colour_name !=''){$product_name .= ' '.$colour_name;}
                            if($storage !=''){$product_name .= ' '.$storage;}
                            if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

                            $rowData[] = $product_name;
                        }

                        if(in_array('sku', $fieldNames) ){
                            $sku = stripslashes(trim((string) $oneRow['sku']));
                            $rowData[] = $sku;
                        }
                    }

                    if($addtable == '' && (in_array('product_name', $fieldNames) || in_array('sku', $fieldNames)) ){
                        $product_name = $sku = '';
                        if(!empty($productData) && array_key_exists($oneRow['product_id'], $productData)){
                            $product_name = $productData[$oneRow['product_id']][0];
                            $sku = $productData[$oneRow['product_id']][1];
                        }
						if(in_array('product_name', $fieldNames)){
                        	$rowData[] = $product_name;
						}
						if(in_array('sku', $fieldNames)){
                        	$rowData[] = $sku;
						}
                    }

                    if(in_array('received_qty', $fieldNames) || in_array('cost', $fieldNames) || in_array('total', $fieldNames)){

                        $received_qty = $oneRow['received_qty'];
                        if(in_array('received_qty', $fieldNames)){
                            $rowData[] = $received_qty;
                        }

                        $cost = $oneRow['cost'];
                        if(in_array('cost', $fieldNames)){
                            $rowData[] = number_format($cost,2);
                        }

                        $total = round($received_qty*$cost,2);
                        if(in_array('total', $fieldNames)){
                            $rowData[] = number_format($total,2);
                        }
                    }

                    $data[] = $rowData;
                }
            }
        }
        elseif($export_type == 'imei') {
            $customFields = $commonFields = array();
            $queryCFObj = $this->db->query("SELECT field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'devices' ORDER BY order_val ASC", array());
            if($queryCFObj){
                while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
                    $customFields[] = stripslashes($oneCustomFields->field_name);
                }
            }

			if(!empty($customFields)){
				foreach($customFields as $oneFieldName){
					if($oneFieldName=='ID'){$oneFieldName = 'Id';}
					if(in_array($oneFieldName, $titleNames)){
						$commonFields[] = $oneFieldName;
					}
				}
				
				if(!empty($commonFields)){
					$fieldNames = array_diff($fieldNames, $customFields);
					$fieldNames[] = 'item.custom_data';
				}
			}
			
			$categoryJoin = "";
            if(in_array('category.category_name', $fieldNames)){
                $categoryJoin = "LEFT JOIN category ON (category.category_id = p.category_id)";
            }

            $extrtable = $extrcond = $isPOS = '';
            if(in_array('i.low_inventory_alert', $fieldNames) || in_array('i.regular_price', $fieldNames)){
                if(in_array('i.regular_price', $fieldNames)){
                    $isPOS = ', item.is_pos';
                }
                if(in_array('i.regular_price', $fieldNames) && !in_array('item.item_id', $fieldNames)){
                    $isPOS .= ', item.item_id';
                }
                $extrtable .= ', inventory i';
                $extrcond .= " AND i.product_id = p.product_id AND i.accounts_id = $accounts_id";
            }
			
			if(in_array('created_by_username', $fieldNames)){
				$key = array_search ('created_by_username', $fieldNames);
				$fieldNames[$key] = 'item.user_id';
            }
			if(in_array('created_on_date', $fieldNames)){
				$key = array_search ('created_on_date', $fieldNames);
				$fieldNames[$key] = 'po_items.created_on';
            }

            if(in_array('po_items.cost', $fieldNames) || in_array('po.po_number', $fieldNames) || in_array('po.lot_ref_no', $fieldNames) || in_array('po_items.created_on', $fieldNames)){
                $extrtable .= ', po, po_items, po_cart_item';
                $extrcond .= " AND po_items.po_items_id = po_cart_item.po_items_id AND po.po_id = po_items.po_id AND po_cart_item.item_id = item.item_id";
            }

            $sql = "SELECT ".implode(', ', $fieldNames)."$isPOS 
					FROM item$extrtable, product p $categoryJoin 
					WHERE item.accounts_id = $accounts_id AND p.product_publish = 1$extrcond";
            $bindData = array();
            if($startdate !='' && $enddate !=''){
                $sql .= " AND (item.created_on BETWEEN :startdate AND :enddate)";
                $bindData['startdate']= $startdate;
                $bindData['enddate']= $enddate;
            }

            $sql .= " AND item.product_id = p.product_id GROUP BY item.item_id ORDER BY CONCAT(p.product_name, ' ', p.colour_name, ' ',p.storage, ' ', p.physical_condition_name) ASC";
            //$this->db->writeIntoLog($sql);
			$query = $this->db->querypagination($sql, $bindData);
            if($query){
                $itemPrice = array();
                if(in_array('i.regular_price', $fieldNames)){
                    foreach($query as $oneRow1){
                        if($oneRow1['is_pos']>0)
                            $itemPrice[$oneRow1['item_id']] = 0.00;
                    }

                    if(!empty($itemIds)){
                        $pcSql = "SELECT pc.sales_price, pci.item_id FROM pos_cart_item pci, pos_cart pc WHERE pci.item_id in (".implode(', ', array_keys($itemPrice)).") AND pc.pos_cart_id = pci.pos_cart_id GROUP BY pci.item_id ORDER BY pci.pos_cart_item_id DESC";
                        $pos_cartObj = $this->db->querypagination($pcSql, array());
                        if($pos_cartObj){
                            foreach($pos_cartObj as $onePCRow){
                                $itemPrice[$onePCRow['item_id']] = $onePCRow['sales_price'];
                            }
                        }
                    }
                }

				$userIds = array();
				$tableObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id", array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$userIds[$oneRow->user_id] = trim(stripslashes("$oneRow->user_first_name $oneRow->user_last_name"));
					}
				}

                foreach($query as $oneRow){
                    $rowData = array();
                    foreach($fieldNames as $oneName){
                        $oneName = str_replace('p.', '', $oneName);
                        $oneName = str_replace('item.', '', $oneName);
                        $oneName = str_replace('po.', '', $oneName);
                        $oneName = str_replace('category.', '', $oneName);
                        $oneName = str_replace('i.', '', $oneName);
                        $oneName = str_replace('po_items.', '', $oneName);
						$itemId = $oneRow['item_id']??0;

						if($oneName=='user_id'){
							$user_id = $oneRow['user_id'];
							$userName = array_key_exists($user_id, $userIds)?$userIds[$user_id]:'';
							$rowData[] = $userName;
						}
                        elseif($oneName=='regular_price' && array_key_exists($itemId, $itemPrice)){
                            $rowData[] = $itemPrice[$itemId];
                        }
                        elseif($oneName=='cost'){
							$cost = 0.00;
							$poCostInfo = $Common->oneIMEIAveCost(0, $itemId, date('Y-m-d H:i:s'));
							if(!empty($poCostInfo)){										
								$cost = round($poCostInfo[0],2);
							}
							$rowData[] = $cost;
						}
						elseif(in_array($oneName, array('taxable', 'in_inventory'))){
							if(trim((string) $oneRow[$oneName])==1){$rowData[] = 'Yes';}
							else{$rowData[] = 'No';}
						}
						elseif(in_array($oneName, array('manufacturer_id'))){
							$manufacturer_id = $oneRow['manufacturer_id'];
							$manufacturerName = '';
							$manuObj = $this->db->query("SELECT name FROM manufacturer WHERE manufacturer_id = $manufacturer_id", array());
							if($manuObj){
								$manufacturerName = $manuObj->fetch(PDO::FETCH_OBJ)->name;
							}
							$rowData[] = stripslashes(trim((string) $manufacturerName));
						}
						elseif($oneName=='custom_data' && !empty($oneRow[$oneName])){
                            if(!empty($commonFields)){
								$customData = unserialize($oneRow[$oneName]);
								foreach($commonFields as $oneFieldName){
									$value = '';
									if(!empty($customData) && array_key_exists($oneFieldName, $customData)){
											$value = $customData[$oneFieldName];
									}
									$rowData[] = $value;
                              	}
                            }
                            else{
                                foreach($commonFields as $oneFieldName){
                                    $rowData[] = '';
                                }
                            }
                        }						
                        else{
                            $rowData[] = stripslashes(trim((string) $oneRow[$oneName]));
                        }
                    }

                    $data[] = $rowData;
                }
            }
        }
        elseif($export_type == 'invoice') {
            $ifieldNames = $itableNames = array();
				$ifieldNames[] = 'pos.pos_id';
            $itableNames[] = 'pos';

				$customFields = $commonFields = array();
            $queryCFObj = $this->db->query("SELECT field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
            if($queryCFObj){
                while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
                    $customFields[] = stripslashes($oneCustomFields->field_name);
                }
            }
			
            if(in_array('date', $fieldNames) || in_array('time', $fieldNames)){
                $ifieldNames[] = 'pos.sales_datetime';
            }
            if(in_array('invoice_no', $fieldNames)){
                $ifieldNames[] = 'pos.invoice_no';
            }
            if(in_array('customername', $fieldNames) ){
                $itableNames[] = 'customers';
                $ifieldNames[] = 'customers.first_name, customers.last_name';
            }
            if(in_array('customeremail', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.email';
            }
            if(in_array('customerphone', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.contact_no';
            }
            if(in_array('customeraddress', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.shipping_address_one, customers.shipping_address_two, customers.shipping_city, customers.shipping_state, customers.shipping_zip, customers.shipping_country';
            }			
			if(!empty($customFields)){
				foreach($customFields as $oneFieldName){
					if($oneFieldName=='ID'){$oneFieldName = 'Id';}
					if(in_array($oneFieldName, $titleNames)){
						$commonFields[] = $oneFieldName;
					}
				}
				if(!empty($commonFields)){
					$fieldNames = array_diff($fieldNames, $customFields);
					if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
					$ifieldNames[] = 'customers.custom_data';
				}
            }

            if(in_array('salesname', $fieldNames)){
                $itableNames[] = 'user';
                $ifieldNames[] = 'user.user_first_name, user.user_last_name';
            }
            $pos_carttable = '';
            $pos_cartwhere = "";
            if(in_array('taxable_total', $fieldNames) || in_array('taxes_total', $fieldNames) || in_array('nontaxable_total', $fieldNames) || in_array('grand_total', $fieldNames)){
                $ifieldNames[] = 'pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2, SUM(CASE WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS taxableTotal, SUM(CASE WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS nonTaxableTotal';
                $pos_carttable = 'pos_cart, ';
                $pos_cartwhere = "AND pos.pos_id = pos_cart.pos_id";
            }

            $sql = "SELECT ".implode(', ', $ifieldNames)." FROM $pos_carttable pos";
            if(in_array('customers', $itableNames)){
                $sql .= " LEFT JOIN customers ON (pos.customer_id = customers.customers_id)";
            }
            if(in_array('user', $itableNames)){
                $sql .= " LEFT JOIN user ON (pos.employee_id = user.user_id)";
            }
            $sql .= " WHERE pos.accounts_id = $accounts_id $pos_cartwhere AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2))";
            $bindData = array();
            if($startdate !='' && $enddate !=''){
                $sql .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
                $bindData['startdate']= $startdate;
                $bindData['enddate']= $enddate;
            }

            $sql .= " GROUP BY pos.pos_id ORDER BY pos.sales_datetime DESC";

            $query = $this->db->querypagination($sql, $bindData);
            if($query){
                foreach($query as $oneRow){
                    $rowData = array();

                    if(in_array('date', $fieldNames)){
                        $rowData[] = date($dateformat, strtotime($oneRow['sales_datetime']));
                    }

                    if(in_array('time', $fieldNames)){
                        if($timeformat=='24 hour'){$time =  date('H:i', strtotime($oneRow['sales_datetime']));}
                        else{$time =  date('g:i a', strtotime($oneRow['sales_datetime']));}

                        $rowData[] = $time;
                    }

                    if(in_array('invoice_no', $fieldNames)){
                        $rowData[] = $oneRow['invoice_no'];
                    }

                    if(in_array('customername', $fieldNames)){
                        $rowData[] = stripslashes(trim($oneRow['first_name'].' '.$oneRow['last_name']));
                    }
                    if(in_array('customeremail', $fieldNames)){
                        $rowData[] = $oneRow['email'];
                    }
                    if(in_array('customerphone', $fieldNames)){
                        $rowData[] = $oneRow['contact_no'];
                    }
                    if(in_array('customeraddress', $fieldNames)){
                        $customeraddress = stripslashes($oneRow['shipping_address_one']);
                        if($oneRow['shipping_address_two'] !=''){
                            $customeraddress .= ' '.$oneRow['shipping_address_two'];
                        }
                        if($oneRow['shipping_city'] !=''){
                            $customeraddress .= ' '.$oneRow['shipping_city'];
                        }
                        if($oneRow['shipping_state'] !=''){
                            $customeraddress .= ' '.$oneRow['shipping_state'];
                        }
                        if($oneRow['shipping_zip'] !=''){
                            $customeraddress .= ' '.$oneRow['shipping_zip'];
                        }
                        if($oneRow['shipping_country'] !='' && $oneRow['shipping_country'] !='0'){
                            $customeraddress .= ' '.$oneRow['shipping_country'];
                        }

                        $rowData[] = $customeraddress;
                    }

						  if(array_key_exists('custom_data', $oneRow) && !empty($commonFields)){
								//$this->db->writeIntoLog(json_encode($commonFields));
								$customData = unserialize($oneRow['custom_data']);
								//$this->db->writeIntoLog(json_encode($customData));
								foreach($commonFields as $oneFieldName){
									if($oneFieldName=='Id'){
										$oneFieldName = 'ID';
									}
									
									$value = '';
									if(!empty($customData) && array_key_exists($oneFieldName, $customData)){
											$value = $customData[$oneFieldName];
									}
									$rowData[] = $value;
								}
						  }
                    if(in_array('salesname', $fieldNames)){
                        $rowData[] = stripslashes(trim($oneRow['user_first_name'].' '.$oneRow['user_last_name']));
                    }

                    if($pos_carttable !=''){
                        $taxable_total = $oneRow['taxableTotal'];
                        $totalnontaxable = $oneRow['nonTaxableTotal'];
                        if(in_array('taxable_total', $fieldNames)){
                            $taxable_totalstr = number_format($taxable_total,2);
                            if($taxable_total <0 ){
                                $taxable_totalstr = '-'.number_format($taxable_total*(-1),2);
                            }
                            $rowData[] = $taxable_totalstr;
                        }

                        $taxes_total1 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage1'], $oneRow['tax_inclusive1']);
                        $taxes_total2 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage2'], $oneRow['tax_inclusive2']);

                        $tax_inclusive1 = $oneRow['tax_inclusive1'];
                        $tax_inclusive2 = $oneRow['tax_inclusive2'];

                        $taxestotal = $taxes_total1+$taxes_total2;
                        if(in_array('taxes_total', $fieldNames)){
                            $taxestotalstr = number_format($taxestotal,2);
                            if($taxestotal <0 ){
                                $taxestotalstr = '-'.number_format($taxestotal*(-1),2);
                            }
                            $rowData[] = $taxestotalstr;
                        }

                        if(in_array('nontaxable_total', $fieldNames)){
                            $totalnontaxablestr = number_format($totalnontaxable,2);
                            if($totalnontaxable <0 ){
                                $totalnontaxablestr = '-'.number_format($totalnontaxable*(-1),2);
                            }
                            $rowData[] = $totalnontaxablestr;
                        }

                        if(in_array('grand_total', $fieldNames)){
                            $grand_total = $taxable_total+$taxestotal+$totalnontaxable;
                            if($tax_inclusive1>0){
                                $grand_total -= $taxes_total1;
                            }
                            if($tax_inclusive2>0){
                                $grand_total -= $taxes_total2;
                            }

                            $grand_totalstr = number_format($grand_total,2);
                            if($grand_total <0 ){
                                $grand_totalstr = '-'.number_format($grand_total*(-1),2);
                            }
                            $rowData[] = $grand_totalstr;
                        }
                    }

                    $data[] = $rowData;
                }
            }
        }
        elseif($export_type =='order') {
            $ifieldNames = $itableNames = array();
			$ifieldNames[] = 'pos.pos_id';
            $itableNames[] = 'pos';

            if(in_array('date', $fieldNames) || in_array('time', $fieldNames)){
                $ifieldNames[] = 'pos.sales_datetime';
            }
            if(in_array('invoice_no', $fieldNames)){
                $ifieldNames[] = 'pos.invoice_no';
            }
            if(in_array('customername', $fieldNames) ){
                $itableNames[] = 'customers';
                $ifieldNames[] = 'customers.first_name, customers.last_name';
            }
            if(in_array('customercompany', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.company';
            }
            if(in_array('customeremail', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.email';
            }
            if(in_array('customerphone', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.contact_no';
            }
            if(in_array('customersecondary_phone', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.secondary_phone';
            }
            if(in_array('customerfax', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.fax';
            }
            if(in_array('customercustomer_type', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.customer_type';
            }
			if(in_array('customershipping_address_one', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.shipping_address_one';
            }
			if(in_array('customershipping_address_two', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.shipping_address_two';
            }
			if(in_array('customershipping_city', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.shipping_city';
            }
			if(in_array('customershipping_state', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.shipping_state';
            }
			if(in_array('customershipping_zip', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.shipping_zip';
            }
			if(in_array('customershipping_country', $fieldNames)){
                if(!in_array('customers', $itableNames)){$itableNames[] = 'customers';}
                $ifieldNames[] = 'customers.shipping_country';
            }
			
            if(in_array('salesname', $fieldNames)){
                $itableNames[] = 'user';
                $ifieldNames[] = 'user.user_first_name, user.user_last_name';
            }
            $pos_carttable = '';
            $pos_cartwhere = "";
            if(in_array('taxable_total', $fieldNames) || in_array('taxes_total', $fieldNames) || in_array('nontaxable_total', $fieldNames) || in_array('grand_total', $fieldNames)){
                $ifieldNames[] = 'pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2, SUM(CASE WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS taxableTotal, SUM(CASE WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS nonTaxableTotal';
                $pos_carttable = 'pos_cart, ';
                $pos_cartwhere = "AND pos.pos_id = pos_cart.pos_id";
            }

            $sql = "SELECT ".implode(', ', $ifieldNames)." 
					FROM $pos_carttable pos";
            if(in_array('customers', $itableNames)){
                $sql .= " LEFT JOIN customers ON (pos.customer_id = customers.customers_id)";
            }
            if(in_array('user', $itableNames)){
                $sql .= " LEFT JOIN user ON (pos.employee_id = user.user_id)";
            }
            $sql .= " WHERE pos.accounts_id = $accounts_id $pos_cartwhere AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2))";
            $bindData = array();
            if($startdate !='' && $enddate !=''){
                $sql .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
                $bindData['startdate']= $startdate;
                $bindData['enddate']= $enddate;
            }

            $sql .= " GROUP BY pos.pos_id ORDER BY pos.sales_datetime DESC";

            $query = $this->db->querypagination($sql, $bindData);
            if($query){
                foreach($query as $oneRow){
                    $rowData = array();

                    if(in_array('date', $fieldNames)){
                        $rowData[] = date($dateformat, strtotime($oneRow['sales_datetime']));
                    }

                    if(in_array('time', $fieldNames)){
                        if($timeformat=='24 hour'){$time =  date('H:i', strtotime($oneRow['sales_datetime']));}
                        else{$time =  date('g:i a', strtotime($oneRow['sales_datetime']));}

                        $rowData[] = $time;
                    }

                    if(in_array('invoice_no', $fieldNames)){
                        $rowData[] = $oneRow['invoice_no'];
                    }

                    if(in_array('customername', $fieldNames)){
                        $rowData[] = stripslashes(trim($oneRow['first_name'].' '.$oneRow['last_name']));
                    }
                    if(in_array('customercompany', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['company']));
                    }                    
                    if(in_array('customeremail', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['email']));
                    }
                    if(in_array('customerphone', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['contact_no']));
                    } 
					if(in_array('customersecondary_phone', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['secondary_phone']));
                    }                    
                    if(in_array('customerfax', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['fax']));
                    }                    
                    if(in_array('customercustomer_type', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['customer_type']));
                    }                    
                    if(in_array('customershipping_address_one', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['shipping_address_one']));
                    }                    
                    if(in_array('customershipping_address_two', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['shipping_address_two']));
                    }                    
                    if(in_array('customershipping_city', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['shipping_city']));
                    }
                    if(in_array('customershipping_state', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['shipping_state']));
                    }
                    if(in_array('customershipping_zip', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['shipping_zip']));
                    } 
                    if(in_array('customershipping_country', $fieldNames)){
                        $rowData[] = stripslashes(trim((string) $oneRow['shipping_country']));
                    } 
					//=================//
                    if(in_array('salesname', $fieldNames)){
                        $rowData[] = stripslashes(trim($oneRow['user_first_name'].' '.$oneRow['user_last_name']));
                    }

                    if($pos_carttable !=''){
                        $taxable_total = $oneRow['taxableTotal'];
                        $totalnontaxable = $oneRow['nonTaxableTotal'];
                        if(in_array('taxable_total', $fieldNames)){
                            $taxable_totalstr = number_format($taxable_total,2);
                            if($taxable_total <0 ){
                                $taxable_totalstr = '-'.number_format($taxable_total*(-1),2);
                            }
                            $rowData[] = $taxable_totalstr;
                        }

                        $taxes_total1 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage1'], $oneRow['tax_inclusive1']);
                        $taxes_total2 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage2'], $oneRow['tax_inclusive2']);

                        $tax_inclusive1 = $oneRow['tax_inclusive1'];
                        $tax_inclusive2 = $oneRow['tax_inclusive2'];

                        $taxestotal = $taxes_total1+$taxes_total2;
                        if(in_array('taxes_total', $fieldNames)){
                            $taxestotalstr = number_format($taxestotal,2);
                            if($taxestotal <0 ){
                                $taxestotalstr = '-'.number_format($taxestotal*(-1),2);
                            }
                            $rowData[] = $taxestotalstr;
                        }

                        if(in_array('nontaxable_total', $fieldNames)){
                            $totalnontaxablestr = number_format($totalnontaxable,2);
                            if($totalnontaxable <0 ){
                                $totalnontaxablestr = '-'.number_format($totalnontaxable*(-1),2);
                            }
                            $rowData[] = $totalnontaxablestr;
                        }

                        if(in_array('grand_total', $fieldNames)){
                            $grand_total = $taxable_total+$taxestotal+$totalnontaxable;
                            if($tax_inclusive1>0){
                                $grand_total -= $taxes_total1;
                            }
                            if($tax_inclusive2>0){
                                $grand_total -= $taxes_total2;
                            }

                            $grand_totalstr = number_format($grand_total,2);
                            if($grand_total <0 ){
                                $grand_totalstr = '-'.number_format($grand_total*(-1),2);
                            }
                            $rowData[] = $grand_totalstr;
                        }
                    }

                    $data[] = $rowData;
                }
            }
        }
        elseif($export_type == 'repairs') {
            $ifieldNames = $itableNames = array();
			$ifieldNames[] = 'repairs.repairs_id';
            $itableNames[] = 'repairs';

            $repairsFields = array('ticket_no', 'problem', 'created_on', 'status', 'last_updated', 'bin_location', 'lock_password');
            $repairComFields = array_intersect($repairsFields, $fieldNames);
            if(!empty($repairComFields)){
                foreach($repairComFields as $oneRepField){
                    if(in_array($oneRepField, $fieldNames)){$ifieldNames[] = 'repairs.'.$oneRepField;}
                }
            }

            if(in_array('techassigned', $fieldNames)){
                $itableNames[] = 'user';
                $ifieldNames[] = 'user.user_first_name, user.user_last_name';
            }
            if(in_array('imei_or_serial_no', $fieldNames) || in_array('brand', $fieldNames) || in_array('model', $fieldNames) || in_array('more_details', $fieldNames)){
                $itableNames[] = 'properties';
                if(in_array('imei_or_serial_no', $fieldNames)){$ifieldNames[] = 'properties.imei_or_serial_no';}
                if(in_array('brand', $fieldNames) || in_array('model', $fieldNames)){
                    $ifieldNames[] = 'properties.brand_model_id';
                }
                if(in_array('more_details', $fieldNames)){$ifieldNames[] = 'properties.more_details';}
            }

            $customFields = $commonFields = array();
            $queryCFObj = $this->db->query("SELECT field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'repairs' ORDER BY order_val ASC", array());
            if($queryCFObj){
                while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
                    $customFields[] = stripslashes($oneCustomFields->field_name);
                }
            }
				if(!empty($customFields)){
					foreach($customFields as $oneFieldName){
						if($oneFieldName=='ID'){$oneFieldName = 'Id';}
						if(in_array($oneFieldName, $titleNames)){
							$commonFields[] = $oneFieldName;
						}
					}
					if(!empty($commonFields)){
						$fieldNames = array_diff($fieldNames, $customFields);
						$ifieldNames[] = 'repairs.custom_data';
					}
            }
				
            $customersFields = array('company', 'first_name', 'last_name', 'email', 'contact_no', 'secondary_phone',
                'fax', 'customer_type', 'shipping_address_one', 'shipping_address_two', 'shipping_city',
                'shipping_state', 'shipping_zip', 'shipping_country');
            $custComFields = array_intersect($customersFields, $fieldNames);
            if(!empty($custComFields)){
                $itableNames[] = 'customers';
                foreach($customersFields as $oneCustField){
                    if(in_array($oneCustField, $fieldNames)){$ifieldNames[] = 'customers.'.$oneCustField;}
                }
            }

            $addtable = '';
            /*if($irstartdate !='' && $irenddate !=''){
                $addtable = ' pos,';
            }*/
            $sql = "SELECT ".implode(', ', $ifieldNames)." 
					FROM$addtable repairs";
            if(in_array('customers', $itableNames)){
                $sql .= " LEFT JOIN customers ON (repairs.customer_id = customers.customers_id)";
            }
            if(in_array('user', $itableNames)){
                $sql .= " LEFT JOIN user ON (repairs.assign_to = user.user_id)";
            }
            if(in_array('properties', $itableNames)){
                $sql .= " LEFT JOIN properties ON (repairs.properties_id = properties.properties_id)";
            }
            $sql .= " WHERE repairs.accounts_id = $accounts_id AND repairs.repairs_publish = 1";
            $bindData = array();
            if($startdate !='' && $enddate !=''){
                $sql .= " AND (repairs.created_on BETWEEN :startdate AND :enddate)";
                $bindData['startdate']= $startdate;
                $bindData['enddate']= $enddate;
            }
            if($sstatus !=''){
                $sql .= " AND repairs.status = :status";
                $bindData['status'] = $sstatus;
            }
            /*if($irstartdate !='' && $irenddate !=''){
                 $sql .= " AND (pos.sales_datetime BETWEEN :irstartdate AND :irenddate) AND pos.invoice_no>0 AND pos.pos_id = repairs.pos_id";
                 $bindData['irstartdate'] = $irstartdate;
                 $bindData['irenddate'] = $irenddate;
            }
            */
            $sql .= " GROUP BY repairs.repairs_id ORDER BY repairs.ticket_no DESC";
            $query = $this->db->querypagination($sql, $bindData);
            if($query){

                if(in_array('brand', $fieldNames) || in_array('model', $fieldNames)){
                    
					$brandModelIds = array();
                    foreach($query as $repairsOneRow){
                        if(empty($brandModelIds) || !in_array($repairsOneRow['brand_model_id'], $brandModelIds)){$brandModelIds[] = $repairsOneRow['brand_model_id'];}
                    }
                    $brandModelData = array();
                    if(!empty($brandModelIds)){
                        $bmSql = "SELECT brand_model_id, brand, model FROM brand_model WHERE brand_model_id IN (".implode(', ', $brandModelIds).")";
                        $bmObj = $this->db->query($bmSql, array());
                        if($bmObj){
                            while($bmOneRow = $bmObj->fetch(PDO::FETCH_OBJ)){
                                $brandModelData[$bmOneRow->brand_model_id] = array(trim("$bmOneRow->brand"), trim("$bmOneRow->model"));
                            }
                        }
                    }
                }
				foreach($query as $oneRow){
                    $rowData = array();

                    if(in_array('ticket_no', $fieldNames)){
                        $rowData[] = 'T'.$oneRow['ticket_no'];
                    }

                    if(in_array('techassigned', $fieldNames)){
                        $rowData[] = stripslashes(trim($oneRow['user_first_name'].' '.$oneRow['user_last_name']));
                    }

                    if(in_array('problem', $fieldNames)){
                        $rowData[] = stripslashes($oneRow['problem']);
                    }
                    if(in_array('imei_or_serial_no', $fieldNames)){
                        $rowData[] = $oneRow['imei_or_serial_no'];
                    }
                    if(in_array('brand', $fieldNames) || in_array('model', $fieldNames)){
                        if(!empty($brandModelData) && array_key_exists($oneRow['brand_model_id'], $brandModelData)){
                            $brandModel = $brandModelData[$oneRow['brand_model_id']];
                            if(in_array('brand', $fieldNames)){
                                $rowData[] = trim((string) stripslashes("$brandModel[0]"));
                            }
                            if(in_array('model', $fieldNames)){
                                $rowData[] = trim((string) stripslashes("$brandModel[1]"));
                            }
                        }
                        else{
                            if(in_array('brand', $fieldNames)){$rowData[] = '';}
                            if(in_array('model', $fieldNames)){$rowData[] = '';}
                        }
                    }
                    if(in_array('more_details', $fieldNames)){
                        $rowData[] = trim((string) stripslashes("$oneRow[more_details]"));
                    }

                    if(in_array('created_on', $fieldNames)){
                        $rowData[] = date($dateformat, strtotime($oneRow['created_on']));
                    }

                    if(in_array('status', $fieldNames)){$rowData[] = stripslashes($oneRow['status']);}

                    if(in_array('last_updated', $fieldNames)){
                        $rowData[] = date($dateformat, strtotime($oneRow['last_updated']));
                    }
                    if(in_array('bin_location', $fieldNames)){$rowData[] = stripslashes($oneRow['bin_location']);}
                    if(in_array('lock_password', $fieldNames)){$rowData[] = stripslashes($oneRow['lock_password']);}


                    if(!empty($commonFields)){
                        $custom_data = '';
                        if($oneRow['custom_data'] !=''){
                            $custom_data = unserialize($oneRow['custom_data']);
                        }
                        foreach($commonFields as $oneFieldName){
                            $value = '';
                            if(!empty($custom_data) && array_key_exists($oneFieldName, $custom_data)){
                                $value = $custom_data[$oneFieldName];
                            }
                            $rowData[] = $value;
                        }
                    }

                    if(!empty($custComFields)){
                        foreach($customersFields as $oneCustField){
                            if(in_array($oneCustField, $fieldNames)){$rowData[] = stripslashes(trim((string) $oneRow[$oneCustField]));}
                        }
                    }

                    $data[] = $rowData;
                }
            }
        }
		elseif($export_type == 'petty_cash') {
            
			$sql = "SELECT ".implode(', ', $fieldNames)." FROM petty_cash WHERE accounts_id = $prod_cat_man AND petty_cash_publish = 1";
            $bindData = array();
            if($startdate !='' && $enddate !=''){
                $sql .= " AND (eod_date BETWEEN :startdate AND :enddate)";
                $bindData['startdate']= substr($startdate, 0,10);
                $bindData['enddate']= substr($enddate, 0,10);
            }
            $sql .= " ORDER BY eod_date ASC, petty_cash_id ASC";
            $query = $this->db->query($sql, $bindData);
            if($query){
                while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
                    $rowData = array();
                    foreach($fieldNames as $oneName){
                        if($oneName=='eod_date'){
							if(in_array($oneRow->$oneName, array('0000-00-00', '1000-01-01'))){
								$rowData[] = '';
							}
							else{$rowData[] = date($dateformat, strtotime($oneRow->$oneName));}                            
                        }
						elseif($oneName=='add_sub'){
							if($oneRow->$oneName=='-1'){$rowData[] = 'Subtract';}
							else{$rowData[] = 'Addition';}
						}
                        else{
                            $rowData[] = stripslashes(trim((string) $oneRow->$oneName));
                        }
                    }
                    $data[] = $rowData;
                }
            }
        }
		elseif($export_type == 'expenses') {
            $sql = "SELECT ".implode(', ', $fieldNames)." FROM expenses WHERE accounts_id = $prod_cat_man AND expenses_publish = 1";
            $bindData = array();
            if($startdate !='' && $enddate !=''){
                $sql .= " AND (bill_date BETWEEN :startdate AND :enddate)";
                $bindData['startdate']= substr($startdate, 0,10);
                $bindData['enddate']= substr($enddate, 0,10);
            }
            $sql .= " ORDER BY bill_date ASC, expenses_id ASC";
            $query = $this->db->query($sql, $bindData);
            if($query){
				$vendorsIds = array();
				if(in_array('vendors_id', $fieldNames)){
					$getTableSql = "SELECT vendors_id, name FROM vendors WHERE accounts_id = $prod_cat_man AND vendors_publish = 1";
					$getTableObj = $this->db->query($getTableSql, array());
					if($getTableObj){
						while($getTableRow = $getTableObj->fetch(PDO::FETCH_OBJ)){
							$vendorsIds[$getTableRow->vendors_id] = stripslashes(trim((string) "$getTableRow->name"));
						}
					}
				}
				
                while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
                    $rowData = array();
                    foreach($fieldNames as $oneName){
                        if($oneName=='bill_date' || $oneName=='bill_paid'){
							if(in_array($oneRow->$oneName, array('0000-00-00', '1000-01-01'))){
								$rowData[] = '';
							}
							else{$rowData[] = date($dateformat, strtotime($oneRow->$oneName));}                            
                        }
						elseif($oneName=='vendors_id'){
							if(array_key_exists($oneRow->$oneName, $vendorsIds)){$rowData[] = $vendorsIds[$oneRow->$oneName];}
							else{$rowData[] = '';}
						}
                        else{
                            $rowData[] = stripslashes(trim((string) $oneRow->$oneName));
                        }
                    }
                    $data[] = $rowData;
                }
            }
        }
		elseif($export_type == 'time_clock') {
			$userTable = '';
			$selectFields = array_flip($fieldNames);
			$userFields = array('user_first_name', 'employee_number', 'pin');
			$newFields = array();
			foreach($userFields as $userField){
				if(array_key_exists($userField, $selectFields)){
					$newFields['user.'.$userField] = '';
				}
			}
			
			$tcFields = array('clock_in_date'=>'clocked_in', 'clock_in_time'=>'clocked_in', 'clock_out_date'=>'clocked_out', 'clock_out_time'=>'clocked_out');
			if(!empty($newFields)){
				$userTable = 'user';
				foreach($tcFields as $tcField=>$tctField){
					if(array_key_exists($tcField, $selectFields)){
						$newFields['time_clock.'.$tctField] = '';
					}
				}				
			}
			else{
				foreach($tcFields as $tcField=>$tctField){
					if(array_key_exists($tcField, $selectFields)){
						$newFields[$tctField] = '';
					}
				}
			}
			
			if($userTable =='user'){				
				$sql = "SELECT ".implode(', ', array_keys($newFields))." FROM time_clock, user WHERE time_clock.accounts_id = $prod_cat_man AND user.user_publish = 1 AND user.user_id = time_clock.user_id";
				$bindData = array();
				if(!empty($user_id)){
					$sql .= " AND user.user_id = :user_id";
					$bindData['user_id']= $user_id;
				}
				if($startdate !='' && $enddate !=''){
					$sql .= " AND (time_clock.clocked_in BETWEEN :startdate AND :enddate)";
					$bindData['startdate']= $startdate;
					$bindData['enddate']= $enddate;
				}
				$sql .= " ORDER BY time_clock.clocked_in ASC, time_clock.time_clock_id ASC";				
            }
			else{				
				$sql = "SELECT ".implode(', ', array_keys($newFields))." FROM time_clock WHERE accounts_id = $prod_cat_man";
				$bindData = array();
				if(!empty($user_id)){
					$sql .= " AND user_id = :user_id";
					$bindData['user_id']= $user_id;
				}
				
				if($startdate !='' && $enddate !=''){
					$sql .= " AND (clocked_in BETWEEN :startdate AND :enddate)";
					$bindData['startdate']= $startdate;
					$bindData['enddate']= $enddate;
				}
				$sql .= " ORDER BY clocked_in ASC, time_clock_id ASC";            
			}			
			$query = $this->db->query($sql, $bindData);
            if($query){				
                while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
                    $rowData = array();
                    foreach($newFields as $oneName=>$oneVal){
                        $oneName = str_replace('time_clock.', '', $oneName);
						$oneName = str_replace('user.', '', $oneName);
						if($oneName=='clocked_in' || $oneName=='clocked_out'){
							if(in_array($oneRow->$oneName, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))){
								$oneNameVal = 0;
							}
							else{$oneNameVal = strtotime($oneRow->$oneName);}
							
							if($oneNameVal>0){
								if($oneName=='clocked_in'){
									if(in_array('clock_in_date', $fieldNames)){
										$rowData[] = date($dateformat, $oneNameVal);
									}
									if(in_array('clock_in_time', $fieldNames)){
										$rowData[] = date('h:i A', $oneNameVal);
									}
								}
								elseif($oneName=='clocked_out'){
									if(in_array('clock_out_date', $fieldNames)){
										$rowData[] = date($dateformat, $oneNameVal);
									}
									if(in_array('clock_out_time', $fieldNames)){
										$rowData[] = date('h:i A', $oneNameVal);
									}
								}
							}							
                        }
                        else{
                            $rowData[] = stripslashes(trim((string) $oneRow->$oneName));
                        }
                    }
                    $data[] = $rowData;
                }
            }
		}
		
      return $data;
      //force_download($filename, $data);
   	}	
	
	public function archive_Data(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
    	$accounts_id = $_SESSION['accounts_id']??0;

		return "<input type=\"hidden\" id=\"prod_cat_man\" value=\"$prod_cat_man\" />
		<input type=\"hidden\" id=\"accounts_id\" value=\"$accounts_id\" />";
	}

   	public function AJ_suppliers_archive(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnmsg = '';
		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$suppliers_id = intval($POST['suppliers_id']??0);
		$suppliers_name = $POST['suppliers_name']??'';
		if($suppliers_name !=''){
			if($suppliers_id>0){
				$sql = "SELECT company, email FROM suppliers WHERE accounts_id = $prod_cat_man AND suppliers_id = :suppliers_id ORDER BY suppliers_id ASC";
				$query = $this->db->query($sql, array('suppliers_id'=>$suppliers_id),1);
				if($query){
					$onesuppliersrrow = $query->fetch(PDO::FETCH_OBJ);
					$autosuppliers_name = stripslashes($onesuppliersrrow->company);

					if($onesuppliersrrow->email !='')
						$autosuppliers_name .= " ($onesuppliersrrow->email)";

					if($suppliers_name !="$autosuppliers_name"){
						$customers_id = 0;
					}
				}
			}

			if($suppliers_id==0 && $suppliers_name != ''){

				$autosuppliers_name = $suppliers_name;
				$email = '';
				if(strpos($suppliers_name, ' (') !== false) {
					$scustomerexplode = explode(' (', $suppliers_name);
					if(count($scustomerexplode)>1){
						$autosuppliers_name = trim((string) $scustomerexplode[0]);
						$email = str_replace(')', '', $scustomerexplode[1]);
					}
				}

				$strextra = " AND company LIKE CONCAT('%', :autosuppliers_name, '%')";
				$bindData['autosuppliers_name'] = $autosuppliers_name;
				if($email !=''){
					$strextra .= " AND TRIM(CONCAT_WS(' ', email, contact_no)) = :email";
					$bindData['email'] = $email;
				}
				$strextra .= " GROUP BY company, email";
				$sqlquery = "SELECT suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND suppliers_publish = 1 $strextra LIMIT 0, 1";
				$query = $this->db->querypagination($sqlquery, $bindData);
				if($query){
					foreach($query as $onegrouprow){
						$suppliers_id = $onegrouprow['suppliers_id'];
					}
				}
			}
		}

		if($suppliers_id>0){

			$sql = "SELECT company, email FROM suppliers WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man AND suppliers_publish = 1 ORDER BY suppliers_id ASC LIMIT 0,1";
			$query = $this->db->querypagination($sql, array('suppliers_id'=>$suppliers_id),1);
			if($query){
				foreach($query as $onerow){
					$autosuppliers_name = stripslashes($onerow['company']);

					if($onerow['email'] !='')
						$autosuppliers_name .= " ($onerow[email])";

					$updatetable = $this->db->update('suppliers', array('suppliers_publish'=>0), $suppliers_id);
					if($updatetable){
						$activity_feed_title = "Supplier archived";
						$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
						$activity_feed_link = "/Manage_Data/sview/$suppliers_id";
						$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
						
						$afData = array('created_on' => date('Y-m-d H:i:s'),
										'last_updated' => date('Y-m-d H:i:s'),
										'accounts_id' => $_SESSION["accounts_id"],
										'user_id' => $_SESSION["user_id"],
										'activity_feed_title' => $activity_feed_title,
										'activity_feed_name' => $autosuppliers_name,
										'activity_feed_link' => $activity_feed_link,
										'uri_table_name' => "suppliers",
										'uri_table_field_name' =>"suppliers_publish",
										'field_value' => 0);
						$this->db->insert('activity_feed', $afData);
						
						$returnmsg = 'archive-success';
					}
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnmsg));
   	}
	
	public function AJget_SuppliersPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$suppliers_id = intval($POST['suppliers_id']??0);
		
		$suppliersData = array();
		$suppliersData['login'] = '';
		$suppliersData['suppliers_id'] = 0;
		$suppliersData['company'] = '';
		$suppliersData['first_name'] = '';
		$suppliersData['last_name'] = '';
		$suppliersData['email'] = '';
		$suppliersData['offers_email'] = 0;
		$suppliersData['contact_no'] = '';
		$suppliersData['secondary_phone'] = '';
		$suppliersData['fax'] = '';
		$suppliersData['shipping_address_one'] = '';
		$suppliersData['shipping_address_two'] = '';
		$suppliersData['shipping_city'] = '';
		$suppliersData['shipping_state'] = '';
		$suppliersData['shipping_zip'] = '';
		$suppliersData['shipping_country'] = '';			
		$suppliersData['created_on'] = '';
		$suppliersData['last_updated'] = '';
		$suppliersData['accounts_id'] = '';
		$suppliersData['website'] = '';
		
		if($suppliers_id>0 && $prod_cat_man>0){
			
			$suppliersObj = $this->db->query("SELECT * FROM suppliers WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man", array('suppliers_id'=>$suppliers_id),1);
			if($suppliersObj){
				$suppliersRow = $suppliersObj->fetch(PDO::FETCH_OBJ);
				$suppliersData['suppliers_id'] = $suppliers_id;				
				$suppliersData['company'] = stripslashes(trim((string) $suppliersRow->company));
				$suppliersData['first_name'] = stripslashes(trim((string) $suppliersRow->first_name));
				$suppliersData['last_name'] = stripslashes(trim((string) $suppliersRow->last_name));
				$suppliersData['email'] = trim((string) $suppliersRow->email);
				$suppliersData['offers_email'] = intval($suppliersRow->offers_email);
				$suppliersData['contact_no'] = trim((string) $suppliersRow->contact_no);
				$suppliersData['secondary_phone'] = trim((string) $suppliersRow->secondary_phone);
				$suppliersData['fax'] = trim((string) $suppliersRow->fax);
				
				$suppliersData['shipping_address_one'] = trim((string) $suppliersRow->shipping_address_one);
				$suppliersData['shipping_address_two'] = trim((string) $suppliersRow->shipping_address_two);
				$suppliersData['shipping_city'] = trim((string) $suppliersRow->shipping_city);
				$suppliersData['shipping_state'] = trim((string) $suppliersRow->shipping_state);
				$suppliersData['shipping_zip'] = trim((string) $suppliersRow->shipping_zip);
				$suppliersData['shipping_country'] = trim((string) $suppliersRow->shipping_country);
				if($suppliersRow->shipping_country=='0'){
					$suppliersData['shipping_country'] = '';
				}
					
				$suppliersData['created_on'] = $suppliersRow->created_on;
				$suppliersData['last_updated'] = $suppliersRow->last_updated;
				$suppliersData['accounts_id'] = $suppliersRow->accounts_id;
				$suppliersData['website'] = stripslashes(trim((string) $suppliersRow->website));
			}
		}
		
		return json_encode($suppliersData);
	}
	
	public function AJsave_Suppliers(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = $supplier_name = '';
		$suppliers_id = intval($POST['suppliers_id']??0);
		$frompage = addslashes(trim((string) $POST['frompage']??''));
		$first_name = addslashes(trim((string) $POST['first_name']??''));
		$first_name = $this->db->checkCharLen('suppliers.first_name', $first_name);
		
		$last_name = addslashes(trim((string) $POST['last_name']??''));
		$last_name = $this->db->checkCharLen('suppliers.last_name', $last_name);
		
		$company = addslashes(trim((string) $POST['company']??''));
		$company = $this->db->checkCharLen('suppliers.company', $company);
		
		$email = trim((string) $POST['email']??'');
		$email = $this->db->checkCharLen('suppliers.email', $email);
		
		$user_id = $_SESSION["user_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$suppliersData = array();
		$suppliersData['first_name'] = $first_name;
		$suppliersData['company'] = $company;
		
		$suppliersData['last_name'] = $last_name;
		$suppliersData['email'] = $email;	
		$offers_email = intval($POST['offers_email']??0);
		$suppliersData['offers_email'] = $offers_email;
		$suppliersData['contact_no'] = $this->db->checkCharLen('suppliers.contact_no', trim((string) $POST['contact_no']??''));
		$suppliersData['secondary_phone'] = $this->db->checkCharLen('suppliers.secondary_phone', trim((string) $POST['secondary_phone']??''));
		$suppliersData['fax'] = $this->db->checkCharLen('suppliers.fax', trim((string) $POST['fax']??''));
		$suppliersData['shipping_address_one'] = $this->db->checkCharLen('suppliers.shipping_address_one', trim((string) $POST['shipping_address_one']??''));
		$suppliersData['shipping_address_two'] = $this->db->checkCharLen('suppliers.shipping_address_two', trim((string) $POST['shipping_address_two']??''));
		$suppliersData['shipping_city'] = $this->db->checkCharLen('suppliers.shipping_city', trim((string) $POST['shipping_city']??''));
		$suppliersData['shipping_state'] = $this->db->checkCharLen('suppliers.shipping_state', trim((string) $POST['shipping_state']??''));
		$suppliersData['shipping_zip'] = $this->db->checkCharLen('suppliers.shipping_zip', trim((string) $POST['shipping_zip']??''));
		$suppliersData['shipping_country'] = $this->db->checkCharLen('suppliers.shipping_country', trim((string) $POST['shipping_country']??''));
		$suppliersData['website'] = $this->db->checkCharLen('suppliers.website', trim((string) $POST['website']??''));
		$suppliersData['accounts_id'] = $prod_cat_man;
		$suppliersData['user_id'] = $user_id;

		if($suppliers_id==0){
			$oldsuppliers_id = $suppliers_publish = 0;
			$suppliersObj = $this->db->query("SELECT suppliers_id, suppliers_publish FROM suppliers WHERE accounts_id = $prod_cat_man AND company = :company AND email = :email", array('company'=>$company, 'email'=>$email));
			if($suppliersObj){
				while($onerow = $suppliersObj->fetch(PDO::FETCH_OBJ)){
					$oldsuppliers_id = $onerow->suppliers_id;
					$suppliers_publish = intval($onerow->suppliers_publish);
				}
			}
			if($oldsuppliers_id>0){
				if($suppliers_publish>0){
					$savemsg = 'nameEmailExist';
				}
				else{
					$savemsg = 'nameEmailExistInArchive';
				}
			}
			else{
				$suppliersData['created_on'] = date('Y-m-d H:i:s');
				$suppliersData['last_updated'] = date('Y-m-d H:i:s');
					
				$suppliers_id = $this->db->insert('suppliers', $suppliersData);
				if($suppliers_id){
					
					$id = $suppliers_id;
					$savemsg = 'add-success';
				}
				else{
					$savemsg = 'errorAdding';
				}
			}				
		}
		else{
			$oldsuppliers_id = $suppliers_publish = 0;
			$suppliersObj = $this->db->query("SELECT suppliers_id, suppliers_publish FROM suppliers WHERE accounts_id = $prod_cat_man AND company = :company AND email = :email AND suppliers_id != :suppliers_id", array('company'=>$company, 'email'=>$email, 'suppliers_id'=>$suppliers_id));
			if($suppliersObj){
				while($onerow = $suppliersObj->fetch(PDO::FETCH_OBJ)){
					$oldsuppliers_id = $onerow->suppliers_id;
					$suppliers_publish = intval($onerow->suppliers_publish);
				}
			}
			if($oldsuppliers_id>0){
				if($suppliers_publish>0){
					$savemsg = 'companyEmailExist';
				}
				else{
					$savemsg = 'nameEmailExistInArchive';
				}
			}
			else{
				$oneTRowObj = $this->db->querypagination("SELECT * FROM suppliers WHERE suppliers_id = $suppliers_id", array());
				
				$update = $this->db->update('suppliers', $suppliersData, $suppliers_id);
				if($update){
					$changed = array();
					foreach($suppliersData as $fieldName=>$fieldValue){
						$prevFieldVal = $oneTRowObj[0][$fieldName];
						if($prevFieldVal != $fieldValue){
							if($prevFieldVal=='1000-01-01'){$prevFieldVal = '';}
							if($fieldValue=='1000-01-01'){$fieldValue = '';}
							$changed[$fieldName] = array($prevFieldVal, $fieldValue);
						}
					}
					if(!empty($changed)){
						$moreInfo = $teData = array();
						$teData['created_on'] = date('Y-m-d H:i:s');
						$teData['accounts_id'] = $_SESSION["accounts_id"];
						$teData['user_id'] = $_SESSION["user_id"];
						$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'suppliers');
						$teData['record_id'] = $suppliers_id;
						$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
						$this->db->insert('track_edits', $teData);							
					}
				}
				
				$id = $suppliers_id;
				$savemsg = 'update-success';
				
			}
			
			if($company !='')
				$supplier_name .= "$company, ";
			$supplier_name .= $first_name.' '.$last_name;
			if($email !='')
				$supplier_name .= " ($email)";
		}			
	
		$supplierOpt = array();
		if($frompage=='addpo' || $frompage=='Products'){
			$supplierssql = "SELECT company, email, suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND company !='' AND (suppliers_publish = 1 OR (suppliers_id = :suppliers_id AND suppliers_publish = 0)) ORDER BY company ASC, email ASC";
			$suppliersquery = $this->db->query($supplierssql, array('suppliers_id'=>$suppliers_id));
			if($suppliersquery){
				while($onerow = $suppliersquery->fetch(PDO::FETCH_OBJ)){
					$opsuppliers_id = $onerow->suppliers_id;
					$optLabel = stripslashes($onerow->company);
					
					if($onerow->email !='')
						$optLabel .= " ($onerow->email)";	
					$supplierOpt[$opsuppliers_id] = $optLabel;
				}
			}
		}		
		
		$array = array( 'login'=>'',
						'savemsg'=>$savemsg,
						'suppliers_id'=>$suppliers_id,
						'supplier_name'=>$supplier_name,
						'supplierOpt'=>$supplierOpt);
		return json_encode($array);
	}
	
    public function AJ_pos_archive(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnmsg = '';
		$invoice_no = intval($POST['invoice_no']??0);
		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;

		$sql = "SELECT pos_id, invoice_no FROM pos WHERE accounts_id = $accounts_id AND invoice_no = :invoice_no AND (pos_type = 'Sale' OR (pos_type IN ('Order', 'Repairs') AND order_status = 2)) AND pos_publish = 1 ORDER BY invoice_no ASC LIMIT 0,1";
		$query = $this->db->querypagination($sql, array('invoice_no'=>$invoice_no),1);
		if($query){
			foreach($query as $onerow){
				$pos_id = $onerow['pos_id'];
				$invoice_no = $onerow['invoice_no'];

				$updatetable = $this->db->update('pos', array('pos_publish'=>0, 'status'=>'Archived'), $pos_id);
				if($updatetable){
					$activity_feed_title = $this->db->translate('Sales invoice archived');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Invoices/view/$invoice_no";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' => $activity_feed_title,
									'activity_feed_name' => $invoice_no,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "pos",
									'uri_table_field_name' =>"pos_publish",
									'field_value' => 0);
					$this->db->insert('activity_feed', $afData);
					
					$returnmsg = 'archive-success';
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnmsg));
    }
    
    public function confirmMDArchive(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = '';
		$tableName = $POST['tableName']??'';
		$tableId = intval($POST['tableId']??0);
		$description = $POST['description']??'';
		$user_id = $_SESSION["user_id"]??0;
		$publishName = $tableName.'_publish';

		$updatetable = $this->db->update($tableName, array($publishName=>0), $tableId);
		if($updatetable){
			$activity_feed_title = ucfirst(str_replace('_', ' ', $tableName))." archived";
			$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
			$activity_feed_link = "/Manage_Data/$tableName";
			$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
			
			$afData = array('created_on' => date('Y-m-d H:i:s'),
							'last_updated' => date('Y-m-d H:i:s'),
							'accounts_id' => $_SESSION["accounts_id"],
							'user_id' => $_SESSION["user_id"],
							'activity_feed_title' => $activity_feed_title,
							'activity_feed_name' => $description,
							'activity_feed_link' => $activity_feed_link,
							'uri_table_name' => $tableName,
							'uri_table_field_name' =>$publishName,
							'field_value' => 1);
			$this->db->insert('activity_feed', $afData);
			
			$savemsg = 'archive-success';
		}
		return json_encode(array('login'=>'', 'returnStr'=>$savemsg));
    }


	//========================For lsnipplesizescore module=======================//    		
	public function lsnipplesizescore(){}
	
	public function AJsave_lsnipplesizescore(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$lsnipplesizescore_id = intval($POST['lsnipplesizescore_id']??0);
		$lsnipplesizescore_name = addslashes($POST['lsnipplesizescore_name']??'');
		$lsnipplesizescore_name = $this->db->checkCharLen('lsnipplesizescore.lsnipplesizescore_name', $lsnipplesizescore_name);
		
		$conditionarray = array();
		$conditionarray['lsnipplesizescore_name'] = $lsnipplesizescore_name;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT lsnipplesizescore_publish, lsnipplesizescore_id FROM lsnipplesizescore WHERE accounts_id = $prod_cat_man AND UPPER(lsnipplesizescore_name) = :lsnipplesizescore_name";
		$bindData = array('lsnipplesizescore_name'=>strtoupper($lsnipplesizescore_name));
		if($lsnipplesizescore_id>0){
			$duplSql .= " AND lsnipplesizescore_id != :lsnipplesizescore_id";
			$bindData['lsnipplesizescore_id'] = $lsnipplesizescore_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = $lsnipplesizescore_publish = 0;
		$lsnipplesizescoreObj = $this->db->querypagination($duplSql, $bindData);
		if($lsnipplesizescoreObj){
			foreach($lsnipplesizescoreObj as $onerow){
				$duplRows = 1;
				$lsnipplesizescore_publish = $onerow['lsnipplesizescore_publish'];
			}
		}
		
		if($duplRows>0){
			$savemsg = 'error';
			if($lsnipplesizescore_publish>0){
				$returnStr = 'Name_Already_Exist';
			}
			else{
				$returnStr = 'Name_ExistInArchive';
			}
		}
		else{			
			if($lsnipplesizescore_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$lsnipplesizescore_id = $this->db->insert('lsnipplesizescore', $conditionarray);
				if($lsnipplesizescore_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('lsnipplesizescore', $conditionarray, $lsnipplesizescore_id);
				if($update){
					$activity_feed_title = $this->db->translate('lsnipplesizescore was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/lsnipplesizescore/view/$lsnipplesizescore_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $lsnipplesizescore_name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "lsnipplesizescore",
									'uri_table_field_name' =>"lsnipplesizescore_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_lsnipplesizescore($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_lsnipplesizescore();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_lsnipplesizescore();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_lsnipplesizescore(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND lsnipplesizescore_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND lsnipplesizescore_publish = 0";
		}
		
		$filterSql = "FROM lsnipplesizescore WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND lsnipplesizescore_name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(lsnipplesizescore_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
	private function loadTableRows_lsnipplesizescore(){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$limit = $_SESSION["limit"];
			$page = $this->page;
			$totalRows = $this->totalRows;
			$sdata_type = $this->data_type;
			$keyword_search = $this->keyword_search;
			
			$starting_val = ($page-1)*$limit;
			if($starting_val>$totalRows){$starting_val = 0;}
			$sqlPublish = " AND lsnipplesizescore_publish = 1";
			if($sdata_type=='Archived'){
				$sqlPublish = " AND lsnipplesizescore_publish = 0";
			}
			
			$filterSql = "FROM lsnipplesizescore WHERE accounts_id = $prod_cat_man $sqlPublish";
			$bindData = array();
			if($keyword_search !=''){
				$keyword_search = addslashes(trim((string) $keyword_search));
				if ( $keyword_search == "" ) { $keyword_search = " "; }
				$keyword_searches = explode (" ", $keyword_search);
				if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
				$num = 0;
				while ( $num < sizeof($keyword_searches) ) {
					$filterSql .= " AND lsnipplesizescore_name LIKE CONCAT('%', :keyword_search$num, '%')";
					$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
					$num++;
				}
			}
			
			$sqlquery = "SELECT * $filterSql ORDER BY lsnipplesizescore_name ASC LIMIT $starting_val, $limit";
			$query = $this->db->querypagination($sqlquery, $bindData);
			$tabledata = array();
			if($query){
				foreach($query as $onerow){

					$lsnipplesizescore_id = $onerow['lsnipplesizescore_id'];
					$lsnipplesizescore_name = trim((string) stripslashes($onerow['lsnipplesizescore_name']));
					$tabledata[] = array($lsnipplesizescore_id, $lsnipplesizescore_name);
				}
			}
			return $tabledata;
	}
	
	//========================For lsbcscore module=======================//    		
	public function lsbcscore(){}
	
	public function AJsave_lsbcscore(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$lsbcscore_id = intval($POST['lsbcscore_id']??0);
		$lsbcscore_name = addslashes($POST['lsbcscore_name']??'');
		$lsbcscore_name = $this->db->checkCharLen('lsbcscore.lsbcscore_name', $lsbcscore_name);
		
		$conditionarray = array();
		$conditionarray['lsbcscore_name'] = $lsbcscore_name;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT lsbcscore_publish, lsbcscore_id FROM lsbcscore WHERE accounts_id = $prod_cat_man AND UPPER(lsbcscore_name) = :lsbcscore_name";
		$bindData = array('lsbcscore_name'=>strtoupper($lsbcscore_name));
		if($lsbcscore_id>0){
			$duplSql .= " AND lsbcscore_id != :lsbcscore_id";
			$bindData['lsbcscore_id'] = $lsbcscore_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = $lsbcscore_publish = 0;
		$lsbcscoreObj = $this->db->querypagination($duplSql, $bindData);
		if($lsbcscoreObj){
			foreach($lsbcscoreObj as $onerow){
				$duplRows = 1;
				$lsbcscore_publish = $onerow['lsbcscore_publish'];
			}
		}
		
		if($duplRows>0){
			$savemsg = 'error';
			if($lsbcscore_publish>0){
				$returnStr = 'Name_Already_Exist';
			}
			else{
				$returnStr = 'Name_ExistInArchive';
			}
		}
		else{			
			if($lsbcscore_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$lsbcscore_id = $this->db->insert('lsbcscore', $conditionarray);
				if($lsbcscore_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('lsbcscore', $conditionarray, $lsbcscore_id);
				if($update){
					$activity_feed_title = $this->db->translate('lsbcscore was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/lsbcscore/view/$lsbcscore_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $lsbcscore_name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "lsbcscore",
									'uri_table_field_name' =>"lsbcscore_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_lsbcscore($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_lsbcscore();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_lsbcscore();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_lsbcscore(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND lsbcscore_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND lsbcscore_publish = 0";
		}
		
		$filterSql = "FROM lsbcscore WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND lsbcscore_name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(lsbcscore_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
	private function loadTableRows_lsbcscore(){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$limit = $_SESSION["limit"];
			$page = $this->page;
			$totalRows = $this->totalRows;
			$sdata_type = $this->data_type;
			$keyword_search = $this->keyword_search;
			
			$starting_val = ($page-1)*$limit;
			if($starting_val>$totalRows){$starting_val = 0;}
			$sqlPublish = " AND lsbcscore_publish = 1";
			if($sdata_type=='Archived'){
				$sqlPublish = " AND lsbcscore_publish = 0";
			}
			
			$filterSql = "FROM lsbcscore WHERE accounts_id = $prod_cat_man $sqlPublish";
			$bindData = array();
			if($keyword_search !=''){
				$keyword_search = addslashes(trim((string) $keyword_search));
				if ( $keyword_search == "" ) { $keyword_search = " "; }
				$keyword_searches = explode (" ", $keyword_search);
				if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
				$num = 0;
				while ( $num < sizeof($keyword_searches) ) {
					$filterSql .= " AND lsbcscore_name LIKE CONCAT('%', :keyword_search$num, '%')";
					$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
					$num++;
				}
			}
			
			$sqlquery = "SELECT * $filterSql ORDER BY lsbcscore_name ASC LIMIT $starting_val, $limit";
			$query = $this->db->querypagination($sqlquery, $bindData);
			$tabledata = array();
			if($query){
				foreach($query as $onerow){

					$lsbcscore_id = $onerow['lsbcscore_id'];
					$lsbcscore_name = trim((string) stripslashes($onerow['lsbcscore_name']));
					$tabledata[] = array($lsbcscore_id, $lsbcscore_name);
				}
			}
			return $tabledata;
	}

	//========================For lsclassification module=======================//    		
	public function lsclassification(){}
	
	public function AJsave_lsclassification(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$lsclassification_id = intval($POST['lsclassification_id']??0);
		$lsclassification_name = addslashes($POST['lsclassification_name']??'');
		$lsclassification_name = $this->db->checkCharLen('lsclassification.lsclassification_name', $lsclassification_name);
		
		$conditionarray = array();
		$conditionarray['lsclassification_name'] = $lsclassification_name;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT lsclassification_publish, lsclassification_id FROM lsclassification WHERE accounts_id = $prod_cat_man AND UPPER(lsclassification_name) = :lsclassification_name";
		$bindData = array('lsclassification_name'=>strtoupper($lsclassification_name));
		if($lsclassification_id>0){
			$duplSql .= " AND lsclassification_id != :lsclassification_id";
			$bindData['lsclassification_id'] = $lsclassification_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = $lsclassification_publish = 0;
		$lsclassificationObj = $this->db->querypagination($duplSql, $bindData);
		if($lsclassificationObj){
			foreach($lsclassificationObj as $onerow){
				$duplRows = 1;
				$lsclassification_publish = $onerow['lsclassification_publish'];
			}
		}
		
		if($duplRows>0){
			$savemsg = 'error';
			if($lsclassification_publish>0){
				$returnStr = 'Name_Already_Exist';
			}
			else{
				$returnStr = 'Name_ExistInArchive';
			}
		}
		else{			
			if($lsclassification_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$lsclassification_id = $this->db->insert('lsclassification', $conditionarray);
				if($lsclassification_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('lsclassification', $conditionarray, $lsclassification_id);
				if($update){
					$activity_feed_title = $this->db->translate('lsclassification was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/lsclassification/view/$lsclassification_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $lsclassification_name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "lsclassification",
									'uri_table_field_name' =>"lsclassification_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_lsclassification($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_lsclassification();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_lsclassification();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_lsclassification(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND lsclassification_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND lsclassification_publish = 0";
		}
		
		$filterSql = "FROM lsclassification WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND lsclassification_name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(lsclassification_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
	private function loadTableRows_lsclassification(){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$limit = $_SESSION["limit"];
			$page = $this->page;
			$totalRows = $this->totalRows;
			$sdata_type = $this->data_type;
			$keyword_search = $this->keyword_search;
			
			$starting_val = ($page-1)*$limit;
			if($starting_val>$totalRows){$starting_val = 0;}
			$sqlPublish = " AND lsclassification_publish = 1";
			if($sdata_type=='Archived'){
				$sqlPublish = " AND lsclassification_publish = 0";
			}
			
			$filterSql = "FROM lsclassification WHERE accounts_id = $prod_cat_man $sqlPublish";
			$bindData = array();
			if($keyword_search !=''){
				$keyword_search = addslashes(trim((string) $keyword_search));
				if ( $keyword_search == "" ) { $keyword_search = " "; }
				$keyword_searches = explode (" ", $keyword_search);
				if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
				$num = 0;
				while ( $num < sizeof($keyword_searches) ) {
					$filterSql .= " AND lsclassification_name LIKE CONCAT('%', :keyword_search$num, '%')";
					$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
					$num++;
				}
			}
			
			$sqlquery = "SELECT * $filterSql ORDER BY lsclassification_name ASC LIMIT $starting_val, $limit";
			$query = $this->db->querypagination($sqlquery, $bindData);
			$tabledata = array();
			if($query){
				foreach($query as $onerow){

					$lsclassification_id = $onerow['lsclassification_id'];
					$lsclassification_name = trim((string) stripslashes($onerow['lsclassification_name']));
					$tabledata[] = array($lsclassification_id, $lsclassification_name);
				}
			}
			return $tabledata;
	}

	//========================For lssection module=======================//    		
	public function lssection(){}
	
	public function AJsave_lssection(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$lssection_id = intval($POST['lssection_id']??0);
		$lssection_name = addslashes($POST['lssection_name']??'');
		$lssection_name = $this->db->checkCharLen('lssection.lssection_name', $lssection_name);
		
		$conditionarray = array();
		$conditionarray['lssection_name'] = $lssection_name;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT lssection_publish, lssection_id FROM lssection WHERE accounts_id = $prod_cat_man AND UPPER(lssection_name) = :lssection_name";
		$bindData = array('lssection_name'=>strtoupper($lssection_name));
		if($lssection_id>0){
			$duplSql .= " AND lssection_id != :lssection_id";
			$bindData['lssection_id'] = $lssection_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = $lssection_publish = 0;
		$lssectionObj = $this->db->querypagination($duplSql, $bindData);
		if($lssectionObj){
			foreach($lssectionObj as $onerow){
				$duplRows = 1;
				$lssection_publish = $onerow['lssection_publish'];
			}
		}
		
		if($duplRows>0){
			$savemsg = 'error';
			if($lssection_publish>0){
				$returnStr = 'Name_Already_Exist';
			}
			else{
				$returnStr = 'Name_ExistInArchive';
			}
		}
		else{			
			if($lssection_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$lssection_id = $this->db->insert('lssection', $conditionarray);
				if($lssection_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('lssection', $conditionarray, $lssection_id);
				if($update){
					$activity_feed_title = $this->db->translate('lssection was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/lssection/view/$lssection_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $lssection_name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "lssection",
									'uri_table_field_name' =>"lssection_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_lssection($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_lssection();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_lssection();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_lssection(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND lssection_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND lssection_publish = 0";
		}
		
		$filterSql = "FROM lssection WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND lssection_name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(lssection_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
	private function loadTableRows_lssection(){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$limit = $_SESSION["limit"];
			$page = $this->page;
			$totalRows = $this->totalRows;
			$sdata_type = $this->data_type;
			$keyword_search = $this->keyword_search;
			
			$starting_val = ($page-1)*$limit;
			if($starting_val>$totalRows){$starting_val = 0;}
			$sqlPublish = " AND lssection_publish = 1";
			if($sdata_type=='Archived'){
				$sqlPublish = " AND lssection_publish = 0";
			}
			
			$filterSql = "FROM lssection WHERE accounts_id = $prod_cat_man $sqlPublish";
			$bindData = array();
			if($keyword_search !=''){
				$keyword_search = addslashes(trim((string) $keyword_search));
				if ( $keyword_search == "" ) { $keyword_search = " "; }
				$keyword_searches = explode (" ", $keyword_search);
				if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
				$num = 0;
				while ( $num < sizeof($keyword_searches) ) {
					$filterSql .= " AND lssection_name LIKE CONCAT('%', :keyword_search$num, '%')";
					$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
					$num++;
				}
			}
			
			$sqlquery = "SELECT * $filterSql ORDER BY lssection_name ASC LIMIT $starting_val, $limit";
			$query = $this->db->querypagination($sqlquery, $bindData);
			$tabledata = array();
			if($query){
				foreach($query as $onerow){

					$lssection_id = $onerow['lssection_id'];
					$lssection_name = trim((string) stripslashes($onerow['lssection_name']));
					$tabledata[] = array($lssection_id, $lssection_name);
				}
			}
			return $tabledata;
	}


	//========================For lsbreed module=======================//    		
	public function lsbreed(){}
	
	public function AJsave_lsbreed(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$lsbreed_id = intval($POST['lsbreed_id']??0);
		$lsbreed_name = addslashes($POST['lsbreed_name']??'');
		$lsbreed_name = $this->db->checkCharLen('lsbreed.lsbreed_name', $lsbreed_name);
		
		$conditionarray = array();
		$conditionarray['lsbreed_name'] = $lsbreed_name;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT lsbreed_publish, lsbreed_id FROM lsbreed WHERE accounts_id = $prod_cat_man AND UPPER(lsbreed_name) = :lsbreed_name";
		$bindData = array('lsbreed_name'=>strtoupper($lsbreed_name));
		if($lsbreed_id>0){
			$duplSql .= " AND lsbreed_id != :lsbreed_id";
			$bindData['lsbreed_id'] = $lsbreed_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = $lsbreed_publish = 0;
		$lsbreedObj = $this->db->querypagination($duplSql, $bindData);
		if($lsbreedObj){
			foreach($lsbreedObj as $onerow){
				$duplRows = 1;
				$lsbreed_publish = $onerow['lsbreed_publish'];
			}
		}
		
		if($duplRows>0){
			$savemsg = 'error';
			if($lsbreed_publish>0){
				$returnStr = 'Name_Already_Exist';
			}
			else{
				$returnStr = 'Name_ExistInArchive';
			}
		}
		else{			
			if($lsbreed_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$lsbreed_id = $this->db->insert('lsbreed', $conditionarray);
				if($lsbreed_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('lsbreed', $conditionarray, $lsbreed_id);
				if($update){
					$activity_feed_title = $this->db->translate('lsbreed was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/lsbreed/view/$lsbreed_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $lsbreed_name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "lsbreed",
									'uri_table_field_name' =>"lsbreed_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_lsbreed($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_lsbreed();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_lsbreed();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_lsbreed(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND lsbreed_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND lsbreed_publish = 0";
		}
		
		$filterSql = "FROM lsbreed WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND lsbreed_name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(lsbreed_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
	private function loadTableRows_lsbreed(){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$limit = $_SESSION["limit"];
			$page = $this->page;
			$totalRows = $this->totalRows;
			$sdata_type = $this->data_type;
			$keyword_search = $this->keyword_search;
			
			$starting_val = ($page-1)*$limit;
			if($starting_val>$totalRows){$starting_val = 0;}
			$sqlPublish = " AND lsbreed_publish = 1";
			if($sdata_type=='Archived'){
				$sqlPublish = " AND lsbreed_publish = 0";
			}
			
			$filterSql = "FROM lsbreed WHERE accounts_id = $prod_cat_man $sqlPublish";
			$bindData = array();
			if($keyword_search !=''){
				$keyword_search = addslashes(trim((string) $keyword_search));
				if ( $keyword_search == "" ) { $keyword_search = " "; }
				$keyword_searches = explode (" ", $keyword_search);
				if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
				$num = 0;
				while ( $num < sizeof($keyword_searches) ) {
					$filterSql .= " AND lsbreed_name LIKE CONCAT('%', :keyword_search$num, '%')";
					$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
					$num++;
				}
			}
			
			$sqlquery = "SELECT * $filterSql ORDER BY lsbreed_name ASC LIMIT $starting_val, $limit";
			$query = $this->db->querypagination($sqlquery, $bindData);
			$tabledata = array();
			if($query){
				foreach($query as $onerow){

					$lsbreed_id = $onerow['lsbreed_id'];
					$lsbreed_name = trim((string) stripslashes($onerow['lsbreed_name']));
					$tabledata[] = array($lsbreed_id, $lsbreed_name);
				}
			}
			return $tabledata;
	}

	//========================For lslocation module=======================//    		
	public function lslocation(){}
	
	public function AJsave_lslocation(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$lslocation_id = intval($POST['lslocation_id']??0);
		$lslocation_name = addslashes($POST['lslocation_name']??'');
		$lslocation_name = $this->db->checkCharLen('lslocation.lslocation_name', $lslocation_name);
		
		$conditionarray = array();
		$conditionarray['lslocation_name'] = $lslocation_name;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT lslocation_publish, lslocation_id FROM lslocation WHERE accounts_id = $prod_cat_man AND UPPER(lslocation_name) = :lslocation_name";
		$bindData = array('lslocation_name'=>strtoupper($lslocation_name));
		if($lslocation_id>0){
			$duplSql .= " AND lslocation_id != :lslocation_id";
			$bindData['lslocation_id'] = $lslocation_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = $lslocation_publish = 0;
		$lslocationObj = $this->db->querypagination($duplSql, $bindData);
		if($lslocationObj){
			foreach($lslocationObj as $onerow){
				$duplRows = 1;
				$lslocation_publish = $onerow['lslocation_publish'];
			}
		}
		
		if($duplRows>0){
			$savemsg = 'error';
			if($lslocation_publish>0){
				$returnStr = 'Name_Already_Exist';
			}
			else{
				$returnStr = 'Name_ExistInArchive';
			}
		}
		else{			
			if($lslocation_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$lslocation_id = $this->db->insert('lslocation', $conditionarray);
				if($lslocation_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('lslocation', $conditionarray, $lslocation_id);
				if($update){
					$activity_feed_title = $this->db->translate('lslocation was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/lslocation/view/$lslocation_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $lslocation_name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "lslocation",
									'uri_table_field_name' =>"lslocation_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_lslocation($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_lslocation();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_lslocation();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_lslocation(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND lslocation_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND lslocation_publish = 0";
		}
		
		$filterSql = "FROM lslocation WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND lslocation_name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(lslocation_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
	private function loadTableRows_lslocation(){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$limit = $_SESSION["limit"];
			$page = $this->page;
			$totalRows = $this->totalRows;
			$sdata_type = $this->data_type;
			$keyword_search = $this->keyword_search;
			
			$starting_val = ($page-1)*$limit;
			if($starting_val>$totalRows){$starting_val = 0;}
			$sqlPublish = " AND lslocation_publish = 1";
			if($sdata_type=='Archived'){
				$sqlPublish = " AND lslocation_publish = 0";
			}
			
			$filterSql = "FROM lslocation WHERE accounts_id = $prod_cat_man $sqlPublish";
			$bindData = array();
			if($keyword_search !=''){
				$keyword_search = addslashes(trim((string) $keyword_search));
				if ( $keyword_search == "" ) { $keyword_search = " "; }
				$keyword_searches = explode (" ", $keyword_search);
				if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
				$num = 0;
				while ( $num < sizeof($keyword_searches) ) {
					$filterSql .= " AND lslocation_name LIKE CONCAT('%', :keyword_search$num, '%')";
					$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
					$num++;
				}
			}
			
			$sqlquery = "SELECT * $filterSql ORDER BY lslocation_name ASC LIMIT $starting_val, $limit";
			$query = $this->db->querypagination($sqlquery, $bindData);
			$tabledata = array();
			if($query){
				foreach($query as $onerow){

					$lslocation_id = $onerow['lslocation_id'];
					$lslocation_name = trim((string) stripslashes($onerow['lslocation_name']));
					$tabledata[] = array($lslocation_id, $lslocation_name);
				}
			}
			return $tabledata;
	}
	
    //========================For lsgroups module=======================//    		
	public function lsgroups(){}
	
	public function AJsave_lsgroups(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$lsgroups_id = intval($POST['lsgroups_id']??0);
		$lsgroups_name = addslashes($POST['lsgroups_name']??'');
		$lsgroups_name = $this->db->checkCharLen('lsgroups.lsgroups_name', $lsgroups_name);
		
		$conditionarray = array();
		$conditionarray['lsgroups_name'] = $lsgroups_name;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT lsgroups_publish, lsgroups_id FROM lsgroups WHERE accounts_id = $prod_cat_man AND UPPER(lsgroups_name) = :lsgroups_name";
		$bindData = array('lsgroups_name'=>strtoupper($lsgroups_name));
		if($lsgroups_id>0){
			$duplSql .= " AND lsgroups_id != :lsgroups_id";
			$bindData['lsgroups_id'] = $lsgroups_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = $lsgroups_publish = 0;
		$lsgroupsObj = $this->db->querypagination($duplSql, $bindData);
		if($lsgroupsObj){
			foreach($lsgroupsObj as $onerow){
				$duplRows = 1;
				$lsgroups_publish = $onerow['lsgroups_publish'];
			}
		}
		
		if($duplRows>0){
			$savemsg = 'error';
			if($lsgroups_publish>0){
				$returnStr = 'Name_Already_Exist';
			}
			else{
				$returnStr = 'Name_ExistInArchive';
			}
		}
		else{			
			if($lsgroups_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$lsgroups_id = $this->db->insert('lsgroups', $conditionarray);
				if($lsgroups_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('lsgroups', $conditionarray, $lsgroups_id);
				if($update){
					$activity_feed_title = $this->db->translate('lsgroups was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/lsgroups/view/$lsgroups_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $lsgroups_name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "lsgroups",
									'uri_table_field_name' =>"lsgroups_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_lsgroups($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_lsgroups();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_lsgroups();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_lsgroups(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND lsgroups_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND lsgroups_publish = 0";
		}
		
		$filterSql = "FROM lsgroups WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND lsgroups_name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(lsgroups_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
   private function loadTableRows_lsgroups(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		$sqlPublish = " AND lsgroups_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND lsgroups_publish = 0";
		}
		
		$filterSql = "FROM lsgroups WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND lsgroups_name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * $filterSql ORDER BY lsgroups_name ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){

				$lsgroups_id = $onerow['lsgroups_id'];
				$lsgroups_name = trim((string) stripslashes($onerow['lsgroups_name']));
				$tabledata[] = array($lsgroups_id, $lsgroups_name);
			}
		}
		return $tabledata;
   }
	
	/************suppliers***********/
	public function suppliers(){}
    
    private function filterAndOptionsSupplier(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$sorting_type = $this->sorting_type;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('sorting_type'=>$sorting_type, 'keyword_search'=>$keyword_search);
		$sqlPublish = " AND suppliers_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND suppliers_publish = 0";
		}
		
		$filterSql = "FROM suppliers WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no, secondary_phone, fax)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(suppliers_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;
	}
	
    private function loadTableRowsSupplier(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];		
		$page = $this->page;
		$totalRows = $this->totalRows;
		$ssorting_type = $this->sorting_type;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$sortingTypeData = array(0=>'TRIM(UPPER(CONCAT_WS(\' \', company, first_name, last_name))) ASC', 
								1=>'company ASC', 
								2=>'first_name ASC', 
								3=>'last_name ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}

		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$sqlPublish = " AND suppliers_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND suppliers_publish = 0";
		}
		$filterSql = "FROM suppliers WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no, secondary_phone, fax)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT suppliers_id, accounts_id, created_on, company, first_name, last_name, email, contact_no $filterSql";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		
		$query = $this->db->querypagination($sqlquery, $bindData);
		$i = $starting_val+1;

		$tabledata = array();
		if($query){
			foreach($query as $oneRow){
				$suppliers_id = $oneRow['suppliers_id'];
				
				$name = stripslashes($oneRow['company']);
				$first_name = stripslashes($oneRow['first_name']);
				if($name !=''){$name .= ', ';}
				$name .= $first_name;
				$last_name = stripslashes($oneRow['last_name']);
				if($name !=''){$name .= ' ';}
				$name .= $last_name;
				
				$email = $oneRow['email'];
				if($email==''){$email = '&nbsp;';}
				$contact_no = $oneRow['contact_no'];
				if($contact_no==''){$contact_no = '&nbsp;';}
				
				$tabledata[] = array($suppliers_id, $name, $email, $contact_no);
			}
		}
		return $tabledata;
    }
	
	public function AJgetPageSupplier($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sorting_type = $POST['sorting_type']??0;
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->sorting_type = $sorting_type;
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptionsSupplier();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRowsSupplier();
		
		return json_encode($jsonResponse);
	}	
	
	public function sview(){}
	
	public function AJ_sview_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$suppliers_id = intval($POST['suppliers_id']??0);

		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['allowed'] = $_SESSION["allowed"];
			
		$suppliersObj = $this->db->query("SELECT * FROM suppliers WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man", array('suppliers_id'=>$suppliers_id),1);
		if($suppliersObj){
			$list = false;
			$suppliersarray = $suppliersObj->fetch(PDO::FETCH_OBJ);
			$list_filters = $_SESSION["list_filters"]??array();
			$shistory_type = $list_filters['shistory_type']??'';
		
			$suppliers_id = $suppliersarray->suppliers_id;
			$secondary_phone = $suppliersarray->secondary_phone;
			
			$shipping_address = '';
			$shipping_address_one = $suppliersarray->shipping_address_one;
			if($shipping_address_one !=''){
				$shipping_address .= $shipping_address_one;
			}
			$shipping_address_two = $suppliersarray->shipping_address_two;
			if($shipping_address_two !=''){
				if($shipping_address != ''){$shipping_address .= '<br />';}
				$shipping_address .= $shipping_address_two;
			}
			$shipping_city = $suppliersarray->shipping_city;
			if($shipping_city !=''){
				if($shipping_address != ''){$shipping_address .= '<br />';}
				$shipping_address .= $shipping_city;
			}
			$shipping_state = $suppliersarray->shipping_state;
			if($shipping_state !=''){
				if($shipping_address != ''){$shipping_address .= ' ';}
				$shipping_address .= $shipping_state;
			}
			$shipping_zip = $suppliersarray->shipping_zip;
			if($shipping_zip !=''){
				if($shipping_zip != ''){$shipping_address .= ' ';}
				$shipping_address .= $shipping_zip;
			}
			$shipping_country = $suppliersarray->shipping_country;
			if($shipping_country !='' || $shipping_country !='0'){
				if($shipping_address != ''){$shipping_address .= '<br />'.$shipping_country;}
			}
			
			$company = stripslashes($suppliersarray->company);
			$email = $suppliersarray->email;
			$contact_no = $suppliersarray->contact_no;
			$suppliers_publish = $suppliersarray->suppliers_publish;
			
			$jsonResponse['company'] = $company;
			$jsonResponse['first_name'] = stripslashes(trim((string) $suppliersarray->first_name));
			$jsonResponse['last_name'] = stripslashes(trim((string) $suppliersarray->last_name));
			$jsonResponse['email'] = $email;
			$jsonResponse['contact_no'] = $contact_no;
			$jsonResponse['shipping_address'] = $shipping_address;
			$jsonResponse['suppliers_publish'] = intval($suppliers_publish);
			$jsonResponse['suppliers_id'] = $suppliers_id;
			$jsonResponse['suppliers_publish'] = $suppliers_publish;
			
		}

		return json_encode($jsonResponse);
	}
    
	private function filterHAndOptionsSupplier(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$suppliers_id = $this->suppliers_id;
		$shistory_type = $this->history_type;
		
		$bindData = array();
		$bindData['suppliers_id'] = $suppliers_id;
		$totalRows = 0;
		$actFeedTitleArray = array();
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Supplier Created')==0){
				$filterSql = "SELECT COUNT(suppliers_id) AS totalrows FROM suppliers 
						WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man";
			}
			elseif(strcmp($shistory_type, 'Purchase Order Created')==0){
				$filterSql = "SELECT COUNT(po_id) AS totalrows FROM po 
						WHERE accounts_id = $accounts_id AND supplier_id = :suppliers_id AND transfer = 0";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Manage_Data/sview/', :suppliers_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Manage_Data/sview/', :suppliers_id)  
					UNION ALL SELECT COUNT(suppliers_id) AS totalrows FROM suppliers 
						WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man 
					UNION ALL SELECT COUNT(po_id) AS totalrows FROM po 
						WHERE accounts_id = $accounts_id AND supplier_id = :suppliers_id AND transfer = 0 
					UNION ALL 
						SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id 
					UNION ALL SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id";
		}
	
		$totalRows = 0;
		$tableObj = $this->db->query($filterSql, $bindData);
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$totalRows += $oneRow->totalrows;
			}
		}		
		$this->totalRows = $totalRows;

		$actFeeTitOpt = array();
		$Sql = "SELECT activity_feed_title AS afTitle FROM activity_feed 
			WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Manage_Data/sview/', :suppliers_id)  
		UNION ALL SELECT 'Supplier Created' AS afTitle FROM suppliers 
			WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man 
		UNION ALL SELECT 'Purchase Order Created' AS afTitle FROM po 
			WHERE accounts_id = $accounts_id AND supplier_id = :suppliers_id AND transfer = 0 
		UNION ALL 
			SELECT 'Track Edits' AS afTitle FROM track_edits 
			WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id 
		UNION ALL SELECT 'Notes Created' AS afTitle FROM notes 
			WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id";
		$tableObj = $this->db->query($Sql, array('suppliers_id'=>$suppliers_id));
		if($tableObj){
			$actFeeTitOpts = array();
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$actFeeTitOpts[$oneRow->afTitle] = '';
			}
			ksort($actFeeTitOpts);
			$actFeeTitOpt = array_keys($actFeeTitOpts);
		}
		$this->actFeeTitOpt = $actFeeTitOpt;
	}
	
    private function loadHTableRowsSuppliers(){
        
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$suppliers_id = $this->suppliers_id;
		$shistory_type = $this->history_type;
	
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$bindData = array();
		$bindData['suppliers_id'] = $suppliers_id;
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Supplier Created')==0){
				$filterSql = "SELECT 'suppliers' as tablename, created_on as tabledate, suppliers_id as table_id, 'Supplier Created' as activity_feed_title FROM suppliers 
							WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man";
			}
			elseif(strcmp($shistory_type, 'Purchase Order Created')==0){
				$filterSql = "SELECT 'po' as tablename, po_datetime as tabledate, po_id as table_id, 'Purchase Order Created' as activity_feed_title FROM po 
							WHERE accounts_id = $accounts_id and supplier_id = :suppliers_id AND transfer = 0";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on as tabledate,  notes_id as table_id, 'Notes Created' as activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id";
			}
			else{
				$filterSql = "SELECT 'activity_feed' as tablename, created_on as tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Manage_Data/sview/', :suppliers_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$filterSql = "SELECT 'activity_feed' as tablename, created_on as tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Manage_Data/sview/', :suppliers_id)  
						UNION ALL SELECT 'suppliers' as tablename, created_on as tabledate, suppliers_id as table_id, 'Supplier Created' as activity_feed_title FROM suppliers 
							WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man 
						UNION ALL SELECT 'po' as tablename, po_datetime as tabledate, po_id as table_id, 'Purchase Order Created' as activity_feed_title FROM po 
							WHERE accounts_id = $accounts_id and supplier_id = :suppliers_id AND transfer = 0 
						UNION ALL SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id 
						UNION ALL SELECT 'notes' AS tablename, created_on as tabledate,  notes_id as table_id, 'Notes Created' as activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id 
						ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		$query = $this->db->querypagination($filterSql, $bindData);
		$tabledata = array();
		if($query){
			$userIdNames = array();
			$userObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id", array());
			if($userObj){
				while($userOneRow = $userObj->fetch(PDO::FETCH_OBJ)){
					$userIdNames[$userOneRow->user_id] = trim("$userOneRow->user_first_name $userOneRow->user_last_name");
				}
			}					
			$Activity_Feed = new Activity_Feed($this->db);
			foreach($query as $grpOneRow){
				$activity_feed_title = $grpOneRow['activity_feed_title'];
				$tablename = $grpOneRow['tablename'];
				$table_id = $grpOneRow['table_id'];
				$getHMoreInfo = $Activity_Feed->getHMoreInfo($table_id, $tablename, $userIdNames, $activity_feed_title);
				if(!empty($getHMoreInfo)){
					$tabledata[] = $getHMoreInfo;
				}
			}
		}
		return $tabledata;
    }
		
	public function AJgetHPageSupplier($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$suppliers_id = intval($POST['suppliers_id']??0);
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->suppliers_id = $suppliers_id;
		$this->history_type = $shistory_type;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptionsSupplier();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;	
			$jsonResponse['actFeeTitOpt'] = $this->actFeeTitOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResponse['tableRows'] = $this->loadHTableRowsSuppliers();
		
		return json_encode($jsonResponse);
	}
	
	public function AJmergeSupplier(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = $savemsg = '';
		$id = 0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$suppliers_id = intval($POST['fromsuppliers_id']??0);
		$tosuppliers_id = intval($POST['tosuppliers_id']??0);
		$fromCustObj = $this->db->query("SELECT * FROM suppliers WHERE suppliers_id = :suppliers_id", array('suppliers_id'=>$suppliers_id), 1);
		if($fromCustObj){
			$fromCustRow = $fromCustObj->fetch(PDO::FETCH_OBJ);
			$toCustObj = $this->db->query("SELECT * FROM suppliers WHERE suppliers_id = :suppliers_id", array('suppliers_id'=>$tosuppliers_id), 1);
			if($toCustObj){
				$toCustRow = $toCustObj->fetch(PDO::FETCH_OBJ);
				
				$updateData = array();
				if(!empty($fromCustRow->last_name) && empty($toCustRow->last_name)){
					$updateData['last_name'] = $fromCustRow->last_name;
				}
				if(!empty($fromCustRow->email) && empty($toCustRow->email)){
					$updateData['email'] = $fromCustRow->email;
				}
				if(!empty($fromCustRow->company) && empty($toCustRow->company)){
					$updateData['company'] = $fromCustRow->company;
				}
				if(!empty($fromCustRow->contact_no)){
					if(empty($toCustRow->contact_no)){
						$updateData['contact_no'] = $fromCustRow->contact_no;
					}
					elseif(empty($toCustRow->secondary_phone)){
						$updateData['contact_no'] = $fromCustRow->contact_no;
					}
				}
				if(!empty($updateData)){
					$this->db->update('suppliers', $updateData, $tosuppliers_id);
				}
				$update = $this->db->update('suppliers', array('suppliers_publish'=>0), $suppliers_id);
				if($update){
					$id = $suppliers_id;
					$savemsg = 'Success';
					$filterSql = "SELECT activity_feed_id FROM activity_feed WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Manage_Data/sview/', :suppliers_id)";
					$tableObj = $this->db->query($filterSql, array('suppliers_id'=>$suppliers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$activity_feed_link = '/Customers/view/'.$tosuppliers_id;
							$this->db->update('activity_feed', array('activity_feed_link'=>$activity_feed_link), $oneRow->activity_feed_id);
						}
					}
					
					$filterSql = "SELECT po_id FROM po WHERE accounts_id = $accounts_id AND supplier_id = :suppliers_id";
					$tableObj = $this->db->query($filterSql, array('suppliers_id'=>$suppliers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$this->db->update('po', array('supplier_id'=>$tosuppliers_id), $oneRow->po_id);
						}
					}
					
					$filterSql = "SELECT track_edits_id FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id ";
					$tableObj = $this->db->query($filterSql, array('suppliers_id'=>$suppliers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$this->db->update('track_edits', array('record_id'=>$tosuppliers_id), $oneRow->track_edits_id);
						}
					}
					
					$filterSql = "SELECT notes_id FROM notes WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id";
					$tableObj = $this->db->query($filterSql, array('suppliers_id'=>$suppliers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$this->db->update('notes', array('table_id'=>$tosuppliers_id), $oneRow->notes_id);
						}
					}
					
					$note_for = $this->db->checkCharLen('notes.note_for', 'suppliers');
					$noteData=array('table_id'=> $suppliers_id,
									'note_for'=> $note_for,
									'created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> date('Y-m-d H:i:s'),
									'accounts_id'=> $_SESSION["accounts_id"],
									'user_id'=> $_SESSION["user_id"],
									'note'=> "This supplier's all information has been merged to $toCustRow->first_name $toCustRow->last_name",
									'publics'=>0);
					$notes_id = $this->db->insert('notes', $noteData);
					
				}
			}			
		}
		return json_encode(array('login'=>'', 'savemsg'=>$savemsg, 'id'=>$id));
	}

    //========================For Category module=======================//    		
	public function category(){}
	
	public function AJsave_category(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$category_id = intval($POST['category_id']??0);
		$category_name = addslashes($POST['category_name']??'');
		$category_name = $this->db->checkCharLen('category.category_name', $category_name);
		
		$conditionarray = array();
		$conditionarray['category_name'] = $category_name;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT category_publish, category_id FROM category WHERE accounts_id = $prod_cat_man AND UPPER(category_name) = :category_name";
		$bindData = array('category_name'=>strtoupper($category_name));
		if($category_id>0){
			$duplSql .= " AND category_id != :category_id";
			$bindData['category_id'] = $category_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = $category_publish = 0;
		$categoryObj = $this->db->querypagination($duplSql, $bindData);
		if($categoryObj){
			foreach($categoryObj as $onerow){
				$duplRows = 1;
				$category_publish = $onerow['category_publish'];
			}
		}
		
		if($duplRows>0){
			$savemsg = 'error';
			if($category_publish>0){
				$returnStr = 'Name_Already_Exist';
			}
			else{
				$returnStr = 'Name_ExistInArchive';
			}
		}
		else{			
			if($category_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$category_id = $this->db->insert('category', $conditionarray);
				if($category_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('category', $conditionarray, $category_id);
				if($update){
					$activity_feed_title = $this->db->translate('Category was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/category/view/$category_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $category_name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "category",
									'uri_table_field_name' =>"category_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_category($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_category();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_category();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_category(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND category_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND category_publish = 0";
		}
		
		$filterSql = "FROM category WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND category_name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(category_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
    private function loadTableRows_category(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		$sqlPublish = " AND category_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND category_publish = 0";
		}
		
		$filterSql = "FROM category WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND category_name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * $filterSql ORDER BY category_name ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){

				$category_id = $onerow['category_id'];
				$category_name = trim((string) stripslashes($onerow['category_name']));
				$tabledata[] = array($category_id, $category_name);
			}
		}
		return $tabledata;
    }
	
	//========================For Manufacturer module=======================//    		
	public function manufacturer(){}
	
	public function AJsave_manufacturer(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$manufacturer_id = intval($POST['manufacturer_id']??0);
		$name = $this->db->checkCharLen('manufacturer.name', addslashes($POST['name']??''));
		
		$conditionarray = array();
		$conditionarray['name'] = $name;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT manufacturer_publish, manufacturer_id FROM manufacturer WHERE accounts_id = $prod_cat_man AND UPPER(name) = :name";
		$bindData = array('name'=>strtoupper($name));
		if($manufacturer_id>0){
			$duplSql .= " AND manufacturer_id != :manufacturer_id";
			$bindData['manufacturer_id'] = $manufacturer_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = 0;
		$manufacturerObj = $this->db->querypagination($duplSql, $bindData);
		if($manufacturerObj){
			foreach($manufacturerObj as $onerow){
				$duplRows = 1;
				$manufacturer_publish = $onerow['manufacturer_publish'];
				if($manufacturer_id==0 && $manufacturer_publish==0){
					$manufacturer_id = $onerow['manufacturer_id'];
					$this->db->update('manufacturer', array('manufacturer_publish'=>1), $manufacturer_id);
					$duplRows = 0;
					$savemsg = 'Update';
				}
			}
		}
		
		if($duplRows>0 || empty($name)){
			$savemsg = 'error';
			$returnStr = 'Name_Already_Exist';
		}
		else{			
			if($manufacturer_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$manufacturer_id = $this->db->insert('manufacturer', $conditionarray);
				if($manufacturer_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('manufacturer', $conditionarray, $manufacturer_id);
				if($update){
					$activity_feed_title = $this->db->translate('Manufacturer was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/manufacturer/view/$manufacturer_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "manufacturer",
									'uri_table_field_name' =>"manufacturer_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_manufacturer($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_manufacturer();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_manufacturer();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_manufacturer(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND manufacturer_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND manufacturer_publish = 0";
		}
		$filterSql = "FROM manufacturer WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(manufacturer_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
    private function loadTableRows_manufacturer(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}

		$sqlPublish = " AND manufacturer_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND manufacturer_publish = 0";
		}
		$filterSql = "FROM manufacturer WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * $filterSql ORDER BY name ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){

				$manufacturer_id = $onerow['manufacturer_id'];
				$manufacturer_name = trim((string) stripslashes($onerow['name']));
				$tabledata[] = array($manufacturer_id, $manufacturer_name);
			}
		}
		return $tabledata;
    }
	    
    //========================For Repair_Problems module=======================//    		
	public function repair_problems(){}
	
	public function AJsave_repair_problems(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$repair_problems_id = intval($POST['repair_problems_id']??0);
		$name = addslashes($POST['name']??'');
		$name = $this->db->checkCharLen('repair_problems.name', $name);
			
		$additional_disclaimer = addslashes($POST['additional_disclaimer']??'');
		
		$conditionarray = array();
		$conditionarray['name'] = $name;
		$conditionarray['additional_disclaimer'] = $additional_disclaimer;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT repair_problems_publish, repair_problems_id FROM repair_problems WHERE accounts_id = $prod_cat_man AND UPPER(name) = :name";
		$bindData = array('name'=>strtoupper($name));
		if($repair_problems_id>0){
			$duplSql .= " AND repair_problems_id != :repair_problems_id";
			$bindData['repair_problems_id'] = $repair_problems_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = $repair_problems_publish = 0;
		$repair_problemsObj = $this->db->querypagination($duplSql, $bindData);
		if($repair_problemsObj){
			foreach($repair_problemsObj as $onerow){
				$duplRows = 1;
				$repair_problems_publish = $onerow['repair_problems_publish'];
			}
		}
		
		if($duplRows>0 || empty($name)){
			$savemsg = 'error';
			
			if($repair_problems_publish>0){
				$returnStr = 'Name_Already_Exist';
			}
			else{
				$returnStr = 'Name_ExistInArchive';
			}
		}
		else{			
			if($repair_problems_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$repair_problems_id = $this->db->insert('repair_problems', $conditionarray);
				if($repair_problems_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('repair_problems', $conditionarray, $repair_problems_id);
				if($update){
					$activity_feed_title = $this->db->translate('Repair problems was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/repair_problems/view/$repair_problems_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "repair_problems",
									'uri_table_field_name' =>"repair_problems_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_repair_problems($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_repair_problems();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_repair_problems();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_repair_problems(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND repair_problems_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND repair_problems_publish = 0";
		}		
		$filterSql = "FROM repair_problems WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', name, additional_disclaimer)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(repair_problems_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
    private function loadTableRows_repair_problems(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$sqlPublish = " AND repair_problems_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND repair_problems_publish = 0";
		}		
		$filterSql = "FROM repair_problems WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', name, additional_disclaimer)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * $filterSql ORDER BY name ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){

				$repair_problems_id = $onerow['repair_problems_id'];
				$name = trim((string) stripslashes($onerow['name']));
				$additional_disclaimer = trim((string) stripslashes($onerow['additional_disclaimer']));
				$tabledata[] = array($repair_problems_id, $name, $additional_disclaimer);
			}
		}
		return $tabledata;
    }
	
    //========================For Brand_Model module=======================//    		
	public function brand_model(){}
	
	public function AJsave_brand_model(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$brand_model_id = intval($POST['brand_model_id']??0);
		$brand = addslashes(trim((string) $POST['brand']??''));
		$brand = $this->db->checkCharLen('brand_model.brand', $brand);
		$Common = new Common($this->db);
		$brand = $Common->checkAndReturnBrand($brand);
		$model = addslashes(trim((string) $POST['model']??''));
		$model = $this->db->checkCharLen('brand_model.model', $model);
		
		$conditionarray = array();
		$conditionarray['brand'] = $brand;
		$conditionarray['model'] = $model;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT brand_model_publish, brand_model_id FROM brand_model WHERE accounts_id = $prod_cat_man AND UPPER(brand) = :brand AND UPPER(model) = :model";
		$bindData = array('brand'=>strtoupper($brand), 'model'=>strtoupper($model));
		if($brand_model_id>0){
			$duplSql .= " AND brand_model_id != :brand_model_id";
			$bindData['brand_model_id'] = $brand_model_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = 0;
		$brand_modelObj = $this->db->querypagination($duplSql, $bindData);
		if($brand_modelObj){
			foreach($brand_modelObj as $onerow){
				$duplRows = 1;
				$brand_model_publish = $onerow['brand_model_publish'];
				if($brand_model_publish==0){
					$brand_model_id = $onerow['brand_model_id'];
					$this->db->update('brand_model', array('brand_model_publish'=>1), $brand_model_id);
					$duplRows = 0;
					$savemsg = 'Update';
				}
			}
		}
		
		if($duplRows>0 || empty($brand)){
			$savemsg = 'error';
			$returnStr = 'Name_Already_Exist';
		}
		else{			
			if($brand_model_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$brand_model_id = $this->db->insert('brand_model', $conditionarray);
				if($brand_model_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('brand_model', $conditionarray, $brand_model_id);
				if($update){					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_brand_model($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_brand_model();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_brand_model();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_brand_model(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);
		
		$sqlPublish = " AND brand_model_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND brand_model_publish = 0";
		}		
		$filterSql = "FROM brand_model WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', brand, model)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(brand_model_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
    private function loadTableRows_brand_model(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$sqlPublish = " AND brand_model_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND brand_model_publish = 0";
		}		
		$filterSql = "FROM brand_model WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', brand, model)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * $filterSql ORDER BY brand ASC, model ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){

				$brand_model_id = $onerow['brand_model_id'];
				$brand = trim((string) stripslashes($onerow['brand']));
				$model = trim((string) stripslashes($onerow['model']));
				$tabledata[] = array($brand_model_id, $brand, $model);
			}
		}
		return $tabledata;
    }
	
	//========================For Vendors module=======================//    		
	public function vendors(){}
	
	public function AJsave_vendors(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$vendors_id = intval($POST['vendors_id']??0);
		$name = addslashes($POST['name']??'');
		$name = $this->db->checkCharLen('vendors.name', $name);
		$conditionarray = array();
		$conditionarray['name'] = $name;
		//$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT vendors_publish, vendors_id FROM vendors WHERE accounts_id = $prod_cat_man AND UPPER(name) = :name";
		$bindData = array('name'=>strtoupper($name));
		if($vendors_id>0){
			$duplSql .= " AND vendors_id != :vendors_id";
			$bindData['vendors_id'] = $vendors_id;
		}
		$duplSql .= " LIMIT 0, 1";
		
		$duplRows = 0;
		$vendorsObj = $this->db->querypagination($duplSql, $bindData);
		if($vendorsObj){
			foreach($vendorsObj as $onerow){
				$duplRows = 1;
				$vendors_publish = $onerow['vendors_publish'];
				if($vendors_id==0 && $vendors_publish==0){
					$vendors_id = $onerow['vendors_id'];
					$this->db->update('vendors', array('vendors_publish'=>1), $vendors_id);
					$duplRows = 0;
					$savemsg = 'Update';
				}
			}
		}
		
		if($duplRows>0 || empty($name)){
			$savemsg = 'error';
			$returnStr = 'Name_Already_Exist';
		}
		else{			
			if($vendors_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$vendors_id = $this->db->insert('vendors', $conditionarray);
				if($vendors_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('vendors', $conditionarray, $vendors_id);
				if($update){
					$activity_feed_title = $this->db->translate('Vendor was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/vendors/view/$vendors_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "vendors",
									'uri_table_field_name' =>"vendors_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_vendors($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_vendors();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_vendors();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_vendors(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);
		
		$sqlPublish = " AND vendors_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND vendors_publish = 0";
		}
		$filterSql = "FROM vendors WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(vendors_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
    private function loadTableRows_vendors(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$sqlPublish = " AND vendors_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND vendors_publish = 0";
		}
		$filterSql = "FROM vendors WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * $filterSql ORDER BY name ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){

				$vendors_id = $onerow['vendors_id'];
				$vendors_name = trim((string) stripslashes($onerow['name']));
				$tabledata[] = array($vendors_id, $vendors_name);
			}
		}
		return $tabledata;
    }
	 
	//========================For Expense_type module=======================//    		
	public function expense_type(){}
	
	public function AJsave_expense_type(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$expense_type_id = intval($POST['expense_type_id']??0);
		$name = $this->db->checkCharLen('expense_type.name', addslashes($POST['name']??''));
		
		$conditionarray = array();
		$conditionarray['name'] = $name;
		//$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT expense_type_publish, expense_type_id FROM expense_type WHERE accounts_id = $prod_cat_man AND UPPER(name) = :name";
		$bindData = array('name'=>strtoupper($name));
		if($expense_type_id>0){
			$duplSql .= " AND expense_type_id != :expense_type_id";
			$bindData['expense_type_id'] = $expense_type_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = 0;
		$expense_typeObj = $this->db->querypagination($duplSql, $bindData);
		if($expense_typeObj){
			foreach($expense_typeObj as $onerow){
				$duplRows = 1;
				$expense_type_publish = $onerow['expense_type_publish'];
				if($expense_type_id==0 && $expense_type_publish==0){
					$expense_type_id = $onerow['expense_type_id'];
					$this->db->update('expense_type', array('expense_type_publish'=>1), $expense_type_id);
					$duplRows = 0;
					$savemsg = 'Update';
				}
			}
		}
		
		if($duplRows>0 || empty($name)){
			$savemsg = 'error';
			$returnStr = 'Name_Already_Exist';
		}
		else{			
			if($expense_type_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$expense_type_id = $this->db->insert('expense_type', $conditionarray);
				if($expense_type_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('expense_type', $conditionarray, $expense_type_id);
				if($update){
					$activity_feed_title = $this->db->translate('Expense type was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/expense_type/view/$expense_type_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "expense_type",
									'uri_table_field_name' =>"expense_type_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_expense_type($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_expense_type();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_expense_type();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_expense_type(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);
		
		$sqlPublish = " AND expense_type_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND expense_type_publish = 0";
		}
		$filterSql = "FROM expense_type WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(expense_type_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
    private function loadTableRows_expense_type(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$sqlPublish = " AND expense_type_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND expense_type_publish = 0";
		}
		$filterSql = "FROM expense_type WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * $filterSql ORDER BY name ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){

				$expense_type_id = $onerow['expense_type_id'];
				$expense_type_name = trim((string) stripslashes($onerow['name']));
				$tabledata[] = array($expense_type_id, $expense_type_name);
			}
		}
		return $tabledata;
		
    }
	
	//========================For Customer_Type module=======================//    		
	public function customer_type(){}
	
	public function AJsave_customer_type(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$customer_type_id = intval($POST['customer_type_id']??0);
		$name = $POST['name']??'';
		$name = $this->db->checkCharLen('customer_type.name', $name);
			
		$conditionarray = array();
		$conditionarray['name'] = $name;
		//$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT customer_type_publish, customer_type_id FROM customer_type WHERE accounts_id = $prod_cat_man AND UPPER(name) = :name";
		$bindData = array('name'=>strtoupper($name));
		if($customer_type_id>0){
			$duplSql .= " AND customer_type_id != :customer_type_id";
			$bindData['customer_type_id'] = $customer_type_id;
		}
		$duplSql .= " LIMIT 0, 1";
		$duplRows = 0;
		$customer_typeObj = $this->db->querypagination($duplSql, $bindData);
		if($customer_typeObj){
			foreach($customer_typeObj as $onerow){
				$duplRows = 1;
				$customer_type_publish = $onerow['customer_type_publish'];
				if($customer_type_id==0 && $customer_type_publish==0){
					$customer_type_id = $onerow['customer_type_id'];
					$this->db->update('customer_type', array('customer_type_publish'=>1), $customer_type_id);
					$duplRows = 0;
					$savemsg = 'Update';
				}
			}
		}
		
		if($duplRows>0 || empty($name)){
			$savemsg = 'error';
			$returnStr = 'Name_Already_Exist';
		}
		else{			
			if($customer_type_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$customer_type_id = $this->db->insert('customer_type', $conditionarray);
				if($customer_type_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('customer_type', $conditionarray, $customer_type_id);
				if($update){
					$activity_feed_title = $this->db->translate('Customer type was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Manage_Data/customer_type/view/$customer_type_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' =>  $activity_feed_title,
									'activity_feed_name' => $name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "customer_type",
									'uri_table_field_name' =>"customer_type_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_customer_type($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_customer_type();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_customer_type();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_customer_type(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);
		
		$sqlPublish = " AND customer_type_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND customer_type_publish = 0";
		}
		$filterSql = "FROM customer_type WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(customer_type_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
    private function loadTableRows_customer_type(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$sqlPublish = " AND customer_type_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND customer_type_publish = 0";
		}
		$filterSql = "FROM customer_type WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * $filterSql ORDER BY name ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){

				$customer_type_id = $onerow['customer_type_id'];
				$customer_type_name = trim((string) stripslashes($onerow['name']));
				$tabledata[] = array($customer_type_id, $customer_type_name);
			}
		}
		return $tabledata;
    }
	 
    //========================For eu_gdpr module=======================//
	public function eu_gdpr(){}

	public function AJ_eu_gdpr_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);

		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$canGDPRAddRemove = 0;
		if($prod_cat_man==$accounts_id){$canGDPRAddRemove = 1;}
		$jsonResponse['canGDPRAddRemove'] = $canGDPRAddRemove;

		$variables_id = $eu_gdprMonth = 0;
		$readonly = '';
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $prod_cat_man AND name = 'eu_gdpr' AND value !=''", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				$readonly = ' readonly';
				extract($value);
			}
		}
		$jsonResponse['eu_gdprMonth'] = $eu_gdprMonth;
		$jsonResponse['readonly'] = $readonly;
		$jsonResponse['variables_id'] = $variables_id;

		$variables_id = 0;
		$marketing_data = '';
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'marketing_data'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		$jsonResponse['marketing_data'] = $marketing_data;
		$jsonResponse['variables_id2'] = $variables_id;

		return json_encode($jsonResponse);
	}
    
    public function AJsave_eu_gdpr(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$eu_gdprMonth = $POST['eu_gdprMonth']??0;
		$variables_id = intval($POST['variables_id']??0);
		if($variables_id==0){
			$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='eu_gdpr'", array());
			if($queryObj){
				$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
			}
		}

		$valueData['eu_gdprMonth'] = $eu_gdprMonth;

		$value = serialize($valueData);
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'eu_gdpr'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}

		$array = array( 'login'=>'', 'savemsg'=>$savemsg,
			'variables_id'=>$variables_id);
		return json_encode($array);
    }

    public function removePerData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$customers_id = 0;
		$savemsg = 'error';
		$customers_id = intval($POST['customers_id']??0);
		if($customers_id>0){
			$updateData = array('customers_publish'=>0,
				'first_name'=>$this->db->checkCharLen('customers.first_name', 'GDPR Hidden'),
				'last_name'=>'',
				'email'=>'',
				'company'=>'',
				'contact_no'=>'',
				'secondary_phone'=>'',
				'fax'=>'',
				'customer_type'=>'',
				'shipping_address_one'=>'',
				'shipping_address_two'=>'',
				'shipping_city'=>'',
				'shipping_state'=>'',
				'shipping_zip'=>'',
				'shipping_country'=>'',
				'offers_email'=>'0',
				'website'=>'',
				'credit_limit'=>'0',
				'credit_days'=>'0'
			);
			$update = $this->db->update('customers', $updateData, $customers_id);

			$savemsg = 'remove-success';

		}
	
		$array = array( 'login'=>'', 'id'=>$customers_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
    }

    public function exportPerData(){
        $returnArray = array();
        if(!isset($_SESSION["prod_cat_man"])){
            $returnArray[] = 'session_ended';
        }
        else{
			$POST = $_POST;
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
            $accounts_id = $_SESSION["accounts_id"]??0;
            $currency = $_SESSION["currency"]??'৳';
            $dateformat = str_replace('y', 'Y', $_SESSION["dateformat"]);
            $customers_id = intval($POST['customer_id']??0);
			//$returnArray[] = $customers_id;
            if($customers_id>0){
                $customersObj = $this->db->query("SELECT * FROM customers WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man", array('customers_id'=>$customers_id),1);
                if($customersObj){
                    $customersRow = $customersObj->fetch(PDO::FETCH_OBJ);

                    $returnArray[] = $this->db->translate('First Name').': '.trim((string) $customersRow->first_name);
                    $returnArray[] = $this->db->translate('Last Name').': '.trim((string) $customersRow->last_name);

                    $address = trim((string) $customersRow->shipping_address_one);
                    if($customersRow->shipping_address_two !=''){
                        if($address !=''){$address .= ', ';}
                        $address .= trim((string) $customersRow->shipping_address_two);
                    }
                    if($customersRow->shipping_city !=''){
                        if($address !=''){$address .= ', ';}
                        $address .= trim((string) $customersRow->shipping_city);
                    }
                    if($customersRow->shipping_state !=''){
                        if($address !=''){$address .= ', ';}
                        $address .= trim((string) $customersRow->shipping_state);
                    }
                    if($customersRow->shipping_zip !=''){
                        if($address !=''){$address .= '-';}
                        $address .= trim((string) $customersRow->shipping_zip);
                    }
                    if($customersRow->shipping_country !=''){
                        if($address !=''){$address .= ', ';}
                        $address .= trim((string) $customersRow->shipping_country);
                    }
                    $returnArray[] = $this->db->translate('Address').': '.trim((string) $address);

                    //=====================For Customer Invoice====================//
                    $posInfo = array();
                    $posSql = "SELECT pos_id, sales_datetime, invoice_no FROM pos WHERE accounts_id = $accounts_id AND customer_id = $customersRow->customers_id AND pos_publish = 1 AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2)) ORDER BY pos_id ASC";
                    $posObj = $this->db->query($posSql, array());
                    if($posObj){
                        while($onePosRow = $posObj->fetch(PDO::FETCH_OBJ)){
                            $posInfo[] = date($dateformat, strtotime($onePosRow->sales_datetime)).' s'.$onePosRow->invoice_no;

                            $pos_id = $onePosRow->pos_id;
                            $pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_id = $pos_id", array());
                            if($pos_cartObj){
                                while($row = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
                                    $description = stripslashes(trim((string) $row->description));
                                    $posInfo[] = $description." $currency$row->sales_price";
                                }
                            }
                        }
                    }
					
                    if(!empty($posInfo)){
                        $returnArray[] = '';
                        $returnArray[] = $this->db->translate('// Invoice Information //');

                        foreach($posInfo as $onePOSRow){
                            $returnArray[] = $onePOSRow;
                        }
                    }

                    //=====================For Customer Repair====================//
                    $repairInfo = array();
                    $repairsSql = "SELECT created_on, ticket_no, properties_id FROM repairs WHERE accounts_id = $accounts_id AND customer_id = $customersRow->customers_id AND repairs_publish = 1 ORDER BY repairs_id ASC";
                    $repairsObj = $this->db->query($repairsSql, array());
                    if($repairsObj){
                        while($oneRepairRow = $repairsObj->fetch(PDO::FETCH_OBJ)){
                            $repairInfo[] = date($dateformat, strtotime($oneRepairRow->created_on)).' t'.$oneRepairRow->ticket_no;

                            $properties_id = $oneRepairRow->properties_id;
                            $propertiesObj = $this->db->query("SELECT * FROM properties pt LEFT JOIN brand_model bm ON (pt.brand_model_id = bm.brand_model_id) WHERE pt.properties_id = $properties_id", array());
                            if($propertiesObj){
                                while($row = $propertiesObj->fetch(PDO::FETCH_OBJ)){
                                    $repairInfo[] = $this->db->translate('Brand Model').": $row->brand $row->model";
                                    $repairInfo[] = $this->db->translate('IMEI').": $row->imei_or_serial_no";
                                    $repairInfo[] = $this->db->translate('More Data').": $row->more_details";
                                }
                            }
                        }
                    }
                    if(!empty($repairInfo)){
                        $returnArray[] = '';
                        $returnArray[] = $this->db->translate('Repair Information');

                        foreach($repairInfo as $oneRepairRow){
                            $returnArray[] = $oneRepairRow;
                        }
                    }
                }
            }
        }
        return $returnArray;
    }

    public function AJsaveMarketingData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';

		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = intval($POST['variables_id']??0);
		if($variables_id==0){
			$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='marketing_data'", array());
			if($queryObj){
				$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
			}
		}
		$marketing_data = addslashes(trim((string) $POST['marketing_data']??''));
		$value = serialize(array('marketing_data'=>$marketing_data));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'marketing_data'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
    }
}
?>