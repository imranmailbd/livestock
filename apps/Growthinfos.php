<?php
class Growthinfos{
	protected $db;
	private int $page, $totalRows, $in_inventory, $product_id;
	private string $item_id, $carrier_name, $data_type, $colour_name, $physical_condition_name, $keyword_search, $history_type, $item_number;
	private array $proNamOpt, $carNamOpt, $colNamOpt, $phyConNamOpt, $actFeeTitOpt;
	
	public function __construct($db){$this->db = $db;}
		
	public function lists(){}
	
	public function view(){}
	
	private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$sin_inventory = $this->in_inventory;
		$sproduct_id = $this->product_id;
		$scarrier_name = $this->carrier_name;
		$scolour_name = $this->colour_name;
		$sphysical_condition_name = $this->physical_condition_name;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "IMEI";
		$_SESSION["list_filters"] = array('sproduct_id'=>$sproduct_id, 'scarrier_name'=>$scarrier_name, 'scolour_name'=>$scolour_name, 'sphysical_condition_name'=>$sphysical_condition_name, 'keyword_search'=>$keyword_search);
		
		$filterSql = "";
		$bindData = array();
		if($sin_inventory==0 || $sin_inventory==1){
			$filterSql .= " AND item.in_inventory = $sin_inventory";
		}
		
		if($sproduct_id>0){
			$productObj = $this->db->query("SELECT manufacturer.name AS manufacture, p.product_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = :product_id AND p.accounts_id = $prod_cat_man", array('product_id'=>$sproduct_id),1);
			if($productObj){
				$productRow = $productObj->fetch(PDO::FETCH_OBJ);
				$productName = addslashes(stripslashes(trim($productRow->manufacture.' '.$productRow->product_name)));
				$filterSql .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, product.product_name)) LIKE CONCAT('%', :productName, '%')";
				$bindData['productName'] = $productName;
			}
		}

		if($scarrier_name !=''){
			$filterSql .= " AND item.carrier_name = :scarrier_name";
			$bindData['scarrier_name'] = $scarrier_name;
		}

		if($scolour_name !=''){
			$filterSql .= " AND product.colour_name = :scolour_name";
			$bindData['scolour_name'] = $scolour_name;
		}

		if($sphysical_condition_name !=''){
			$filterSql .= " AND product.physical_condition_name = :sphysical_condition_name";
			$bindData['sphysical_condition_name'] = $sphysical_condition_name;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, product.product_name, product.colour_name, product.storage, product.physical_condition_name, product.sku, item.item_number)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$queryObj = $this->db->query("SELECT COUNT(product.product_id) AS totalrows FROM item, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) WHERE item.accounts_id = $accounts_id $filterSql AND product.product_id = item.product_id", $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
		}

		$proNamOpt = $carNamOpts = $colNamOpts = $phyConNamOpts = array();
		$expSql = "SELECT product.product_id, TRIM(CONCAT_WS(' ', manufacturer.name, product.product_name)) AS productName, product.physical_condition_name, item.carrier_name, product.colour_name FROM item, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) WHERE item.accounts_id = $accounts_id $filterSql AND product.product_id = item.product_id GROUP BY productName, product.physical_condition_name, item.carrier_name, product.colour_name";
		//$proNamOpt[] = $expSql;
		$query = $this->db->querypagination($expSql, $bindData);
		if($query){
			foreach($query as $oneRow){
				$productName = stripslashes(trim((string) $oneRow['productName']));
				if(empty($proNamOpt) || !in_array($productName, $proNamOpt)){
					$proNamOpt[$oneRow['product_id']] = $productName;
				}
				$carNamOpts[trim((string) $oneRow['carrier_name'])] = '';
				$colNamOpts[trim((string) $oneRow['colour_name'])] = '';
				$phyConNamOpts[trim((string) $oneRow['physical_condition_name'])] = '';
			}
			asort($proNamOpt);
			ksort($carNamOpts);
			ksort($colNamOpts);
			ksort($phyConNamOpts);
		}			
			
		$carNamOpt = array();	
		if(!empty($carNamOpts)){			
			foreach($carNamOpts as $optlabel=>$value){
				if($optlabel !=''){
					$carNamOpt[] = $optlabel;
				}
			}
		}
		
		$colNamOpt = array();
		if(!empty($colNamOpts)){			
			foreach($colNamOpts as $optlabel=>$value){
				if($optlabel !=''){
					$colNamOpt[] = $optlabel;
				}
			}
		}
		
		$phyConNamOpt = array();
		if(!empty($phyConNamOpts)){			
			foreach($phyConNamOpts as $optlabel=>$val){
				if($optlabel !=''){
					$phyConNamOpt[] = $optlabel;
				}
			}
		}
		
		$this->totalRows = $totalRows;
		$this->proNamOpt = $proNamOpt;
		$this->carNamOpt = $carNamOpt;
		$this->colNamOpt = $colNamOpt;
		$this->phyConNamOpt = $phyConNamOpt;
	}

	public function AJgetPage_lists($segment4name){
		
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
			$this->filterAndOptions_growthinfos();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}


	private function filterAndOptions_growthinfos(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Manage_Data";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND product_growthinfo_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND product_growthinfo_publish = 0";
		}
		
		$filterSql = "FROM product_growthinfo WHERE product_growthinfo_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND weight LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(product_growthinfo_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}



	public function AJsave_growthinfos(){
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';

		$livestock_product_id  = $POST['product_id[]'];
		$growth  = $POST['livestock_height[]'];
		$weight  = $POST['livestock_weight[]'];
		$review_date  = $POST['review_date_blk'];


		$growthinfoarray_all = array();
		foreach($livestock_product_id as $key => $product_id){

			$growthinfoarray = array();
			$growthinfoarray['product_id'] = $livestock_product_id[$key];
			$growthinfoarray['growth'] = $growth[$key];
			$growthinfoarray['weight'] = $weight[$key];
			$growthinfoarray['review_date'] = date('Y-m-d H:i:s', strtotime($review_date));
			$growthinfoarray['last_updated'] = date('Y-m-d H:i:s');
			$growthinfoarray['accounts_id'] = $prod_cat_man;
			$growthinfoarray['user_id'] = $user_id;

			/**
			 * duplicate check
			 */
			$duplSql = "SELECT * FROM product_growthinfo WHERE product_id = ".$livestock_product_id[$key]." AND  DATE(review_date) = :growthinfo_review_date";
			$bindData = array('growthinfo_review_date'=> date('Y-m-d', strtotime($review_date)));
			$duplRows = 0;
			$growthinfoObj = $this->db->querypagination($duplSql, $bindData);
			if($growthinfoObj){
				foreach($growthinfoObj as $onerow){
					$duplRows = 1;
					$product_growthinfo_publish = $onerow['product_growthinfo_publish'];
					$product_growthinfo_id = $onerow['product_growthinfo_id'];

				}
			}

			if($duplRows>0){

				$update = $this->db->update('product_growthinfo', $growthinfoarray, $product_growthinfo_id);
				
				// if($update){
				// 	$activity_feed_title = $this->db->translate('lsnipplesizescore was edited');
				// 	$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
				// 	$activity_feed_link = "/Manage_Data/lsnipplesizescore/view/$lsnipplesizescore_id";
				// 	$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
				// 	$afData = array('created_on' => date('Y-m-d H:i:s'),
				// 					'last_updated' => date('Y-m-d H:i:s'),
				// 					'accounts_id' => $_SESSION["accounts_id"],
				// 					'user_id' => $_SESSION["user_id"],
				// 					'activity_feed_title' =>  $activity_feed_title,
				// 					'activity_feed_name' => $lsnipplesizescore_name,
				// 					'activity_feed_link' => $activity_feed_link,
				// 					'uri_table_name' => "lsnipplesizescore",
				// 					'uri_table_field_name' =>"lsnipplesizescore_publish",
				// 					'field_value' => 1);
				// 	$this->db->insert('activity_feed', $afData);
					
				// 	$savemsg = 'Update';
				// }

			} else {	
				
				$growthinfoarray['created_on'] = date('Y-m-d H:i:s');
				$product_growthinfo_id  = $this->db->insert('product_growthinfo', $growthinfoarray);

				if($product_growthinfo_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}

			}				

			$growthinfoarray_all[] = $growthinfoarray;
			// var_dump($growthinfoarray_all);exit;	

		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));

	}
	

    private function loadTableRows(){
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"]??'auto';
		$Common = new Common($this->db);
		
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$sin_inventory = $this->in_inventory;
		$sproduct_id = $this->product_id;
		$scarrier_name = $this->carrier_name;			
		$scolour_name = $this->colour_name;			
		$sphysical_condition_name = $this->physical_condition_name;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}

		$filterSql = "FROM item, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) WHERE item.accounts_id = $accounts_id AND product.product_id = item.product_id";

		$bindData = array();
		if(in_array($sin_inventory, array(0,1))){
			$filterSql .= " AND item.in_inventory = $sin_inventory";
		}

		if($sproduct_id>0){
			$productObj = $this->db->query("SELECT manufacturer.name AS manufacture, p.product_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = :product_id AND p.accounts_id = $prod_cat_man", array('product_id'=>$sproduct_id),1);
			if($productObj){
				$productRow = $productObj->fetch(PDO::FETCH_OBJ);
				$productName = addslashes(stripslashes(trim($productRow->manufacture.' '.$productRow->product_name)));
				$filterSql .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, product.product_name)) LIKE CONCAT('%', :productName, '%')";
				$bindData['productName'] = $productName;
			}
		}

		if($scarrier_name !=''){
			$filterSql .= " AND item.carrier_name = :scarrier_name";
			$bindData['scarrier_name'] = $scarrier_name;
		}

		if($scolour_name !=''){
			$filterSql .= " AND product.colour_name = :scolour_name";
			$bindData['scolour_name'] = $scolour_name;
		}

		if($sphysical_condition_name !=''){
			$filterSql .= " AND product.physical_condition_name = :sphysical_condition_name";
			$bindData['sphysical_condition_name'] = $sphysical_condition_name;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, product.product_name, product.colour_name, product.storage, product.physical_condition_name, product.sku, item.item_number)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT item.item_id, item.accounts_id, item.created_on, item.last_updated, item.item_number, item.in_inventory, item.is_pos, product.product_id, manufacturer.name AS manufacture, product.product_name, product.colour_name, product.storage, product.physical_condition_name, item.carrier_name 
					$filterSql 
					ORDER BY manufacturer.name ASC, product.product_name ASC, product.colour_name ASC, product.storage ASC, product.physical_condition_name ASC 
					LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		
		$tabledata = array();
		if($query){
			foreach($query as $oneitemrow){

				$item_id = $oneitemrow['item_id'];
				$item_number = $oneitemrow['item_number'];
				$physical_condition_name = $oneitemrow['physical_condition_name'];

				$product_id = $oneitemrow['product_id'];
				$storage = $oneitemrow['storage'];
				$is_pos = $oneitemrow['is_pos'];
				$product_name = stripslashes($oneitemrow['product_name']);
				if($storage ==''){
					$storage = "&nbsp;";
				}
				$manufacture = $oneitemrow['manufacture'];
				$product_name = stripslashes(trim($manufacture.' '.$product_name));

				$carrier_name = $oneitemrow['carrier_name'];
				$colour_name = $oneitemrow['colour_name'];

				$po_number = '';
				$poObj = $this->db->querypagination("SELECT po.po_number FROM po_cart_item poci, po_items poi LEFT JOIN po ON (po.po_id = poi.po_id) WHERE poci.item_id = $item_id AND poci.po_items_id = poi.po_items_id  ORDER BY poci.po_cart_item_id DESC LIMIT 0,1", array());
				if($poObj){
					$poRow = $poObj[0];
					$po_number = $poRow['po_number'];
				}

				$in_inventory = $oneitemrow['in_inventory'];
				$since = time() - strtotime($oneitemrow['created_on']);
				$created_on = $Common->time_since($since);

				$invoice_no = 0;
				if($in_inventory==0){
					$pciObj = $this->db->querypagination("SELECT pos.invoice_no FROM pos_cart_item pci, pos_cart pc LEFT JOIN pos ON (pos.pos_id = pc.pos_id) WHERE pci.item_id = $item_id AND pci.pos_cart_id = pc.pos_cart_id  ORDER BY pci.pos_cart_item_id DESC LIMIT 0,1", array());
					if($pciObj){
						$pciRow = $pciObj[0];
						$invoice_no = $pciRow['invoice_no'];						
					}
				}
				// $tabledata[] = array(str_replace('/', '%5C', $item_number), $product_name, $colour_name, $storage, $physical_condition_name, $carrier_name, $item_number, $po_number, $created_on, $invoice_no, intval($in_inventory));
				$tabledata[] = array(str_replace('/', '%5C', $item_id), $product_name, $colour_name, $item_number, null, null);
			}
		}

		return $tabledata;
    }
	
	public function AJgetPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$item_id = intval($POST['item_id']??0);
		
		$Common = new Common($this->db);
		
		$imeiData = array();
		$imeiData['login'] = '';
		$imeiData['in_inventory'] = 0;
		
		$custom_data = '';
		$itemObj = $this->db->query("SELECT * FROM item WHERE accounts_id = $accounts_id AND item_id = :item_id", array('item_id'=>$item_id),1);
		if($itemObj){
			$item_onerow = $itemObj->fetch(PDO::FETCH_OBJ);						
			$imeiData['in_inventory'] = intval($item_onerow->in_inventory);
			$product_id = $item_onerow->product_id;
			$productOptions = array();
			$productsql = "SELECT p.product_id, p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name  
							FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
							WHERE p.accounts_id = $prod_cat_man AND p.product_type = 'Live Stocks' AND (p.product_publish = 1 OR (p.product_id = $product_id AND p.product_publish = 0)) GROUP BY p.product_id 
							ORDER BY manufacture ASC, p.product_name ASC, p.colour_name ASC, p.storage ASC, p.physical_condition_name ASC";
			$allproducts = $this->db->querypagination($productsql, array());
			if($allproducts){
				foreach($allproducts as $onerow){	
					$opproduct_id = $onerow['product_id'];
					$manufacture = stripslashes(trim((string) $onerow['manufacture']));
					$name =  stripslashes(trim($manufacture.' '.$onerow['product_name']));
					
					$colour_name = $onerow['colour_name'];
					if($colour_name !=''){$name .= ' '.$colour_name;}
					
					$storage = $onerow['storage'];
					if($storage !=''){$name .= ' '.$storage;}
					
					$physical_condition_name = $onerow['physical_condition_name'];
					if($physical_condition_name !=''){$name .= ' '.$physical_condition_name;}
					
					if($name !=''){
						$selected = '';
						if($opproduct_id==$product_id){$selected = ' selected="selected"';}
						$productOptions[$opproduct_id] = "$name ($onerow[sku])";
					}
				}
			}
			$imeiData['productOptions'] = $productOptions;
			
			$carrier_name = $item_onerow->carrier_name;
			$imeiData['carrier_name'] = $carrier_name;
			$carriersData = array();
			$Common = new Common($this->db);
			$vData = $Common->variablesData('product_setup', $accounts_id);
			if(!empty($vData) && array_key_exists('carriers', $vData)){
				$carriersData = array();
				if(strlen($vData['carriers'])>6){
					$carriersData = explode('||',$vData['carriers']);
				}
			}
			if(!in_array('', $carriersData)){
				$carriersData[] = '';
			}
			
			$imeiData['carrNamOpt'] = $carriersData;
			$custom_data = trim((string) $item_onerow->custom_data);
		}
		$imeiData['customFieldsData'] = $Common->customFormFields('devices', $custom_data);
		
		return json_encode($imeiData);
	}
	
	public function AJsave_IMEI(){
		$POST = $_POST;//json_decode(file_get_contents('php://input'), true);
		$id = 0;
		$savemsg = $message = '';
		//$this->db->writeIntoLog("POST: ".json_encode($POST));
		$item_id = intval($POST['item_id']??0);
		$in_inventory = intval($POST['in_inventory']??0);
		$product_id = intval($POST['product_id']??0);
		$item_number = $this->db->checkCharLen('item.item_number', strtoupper(trim((string) addslashes($POST['item_number']??''))));
		$carrier_name = $this->db->checkCharLen('item.carrier_name', $POST['carrier_name']??'');
		
		if($item_id=='' || $item_id==0)	{
			$savemsg = 'error';
			$message .= 'No_IMEI';
		}
		elseif($product_id=='' || $product_id==0)	{
			$savemsg = 'error';
			$message .= 'Missing_Model';
		}
		else{
			
			$user_id = $_SESSION["user_id"]??0;
			$accounts_id = $_SESSION["accounts_id"]??0;
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$Common = new Common($this->db);
			$Olditem_number = '';
			$queryObj = $this->db->query("SELECT item_number FROM item WHERE item_id = :item_id", array('item_id'=>$item_id));
			if($queryObj){
				$Olditem_number = $queryObj->fetch(PDO::FETCH_OBJ)->item_number;
			}
			$totalrows = 0;
			if($Olditem_number != $item_number){
				$countItemSql = "SELECT COUNT(item_id) AS totalrows FROM item WHERE accounts_id = $accounts_id AND item_number = :item_number AND item_id != :item_id";
				if($in_inventory>0){
					$countItemSql .= " AND in_inventory = $in_inventory";
				}
				else{
					$countItemSql .= " AND product_id = $product_id";
				}
				$queryObj = $this->db->query($countItemSql, array('item_number'=>$item_number, 'item_id'=>$item_id));
				if($queryObj){
					$totalrows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
				}
			}
			if($totalrows>0){
				$savemsg = 'error';
				$message = 'Name_Already_Exist';
			}
			else{

				$conditionarray = array('item_number' => $item_number,
					'product_id' => $product_id,
					'carrier_name' => $carrier_name,
					'last_updated' => date('Y-m-d H:i:s')
				);

				$conditionarray['custom_data'] = $Common->postCustomFormFields('devices', $item_id);
				
				$oneTRowObj = $this->db->querypagination("SELECT * FROM item WHERE item_id = $item_id", array());
				
				$update = $this->db->update('item', $conditionarray, $item_id);
				if($update){
					$changed = array();
					if($oneTRowObj){
						unset($conditionarray['last_updated']);
						foreach($conditionarray as $fieldName=>$fieldValue){
							$prevFieldVal = $oneTRowObj[0][$fieldName];
							if($fieldName=='custom_data'){
								if(strlen($prevFieldVal)<10 && strlen($fieldValue)<10){}
								elseif($prevFieldVal != $fieldValue){
									if($prevFieldVal=='1000-01-01'){$prevFieldVal = '';}
									if($fieldValue=='1000-01-01'){$fieldValue = '';}
									$changed[$fieldName] = array($prevFieldVal, $fieldValue);
								}
							}
							elseif($prevFieldVal != $fieldValue){
								if($prevFieldVal=='1000-01-01'){$prevFieldVal = '';}
								if($fieldValue=='1000-01-01'){$fieldValue = '';}
								if($fieldName=='product_id'){
									$Common = new Common($this->db);
									$fieldName = 'Model';
									if($prevFieldVal==0){$prevFieldVal = '';}
									elseif($prevFieldVal>0){
										$sqlPM = "SELECT manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p 
										LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
										WHERE p.product_id = $prevFieldVal";
										$productObj = $this->db->query($sqlPM, array());
										if($productObj){
											$productRow = $productObj->fetch(PDO::FETCH_OBJ);
											$prevFieldVal = stripslashes(trim("$productRow->manufacture $productRow->product_name $productRow->colour_name $productRow->storage $productRow->physical_condition_name"));
										}
									}
									if($fieldValue==0){$fieldValue = '';}
									elseif($fieldValue>0){
										$sqlPM = "SELECT manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p 
										LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
										WHERE p.product_id = $fieldValue";
										$productObj = $this->db->query($sqlPM, array());
										if($productObj){
											$productRow = $productObj->fetch(PDO::FETCH_OBJ);
											$fieldValue = stripslashes(trim("$productRow->manufacture $productRow->product_name $productRow->colour_name $productRow->storage $productRow->physical_condition_name"));
										}
									}
								}
								elseif($fieldName=='custom_data'){
									
									$custom_data1 = $custom_data2 = array();
									if(!empty($prevFieldVal)){$custom_data1 = unserialize($prevFieldVal);}
									if(!empty($fieldValue)){$custom_data2 = unserialize($fieldValue);}
									if(!empty($custom_data1) || !empty($custom_data2)){
										
										if(!empty($custom_data1) && !empty($custom_data2)){
											$mergeData = array_merge_recursive($custom_data1, $custom_data2);
												
											foreach($mergeData as $mKey=>$mValue){
												if(array_key_exists($mKey, $custom_data1) && array_key_exists($mKey, $custom_data2)){
													$twoData = $mValue;
													
													if($mValue[0] ==$mValue[1]){
														unset($custom_data1[$mKey]);
														unset($custom_data2[$mKey]);
													}
												}
											}
										}
										elseif(!empty($custom_data1)){
											foreach($custom_data1 as $mKey=>$mValue){
												if($custom_data1[$mKey] == ''){
													unset($custom_data1[$mKey]);
												}
											}
										}
										elseif(!empty($custom_data2)){
											foreach($custom_data2 as $mKey=>$mValue){
												if($custom_data2[$mKey] == ''){
													unset($custom_data2[$mKey]);
												}
											}
										}
												
										if(!empty($custom_data1)){$prevFieldVal = serialize($custom_data1);}
										else{$prevFieldVal = '';}
										if(!empty($custom_data2)){$fieldValue = serialize($custom_data2);}
										else{$fieldValue = '';}									
									}
								}
								$changed[$fieldName] = array($prevFieldVal, $fieldValue);
							}
						}						
					}
					
					if(!empty($changed)){
						$moreInfo = $teData = array();
						$teData['created_on'] = date('Y-m-d H:i:s');
						$teData['accounts_id'] = $_SESSION["accounts_id"];
						$teData['user_id'] = $_SESSION["user_id"];
						$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'item');
						$teData['record_id'] = $item_id;
						$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
						$this->db->insert('track_edits', $teData);							
					}
				}

				$id = $item_id;
				$savemsg = 'update-success';
			}
		}
	
		$array = array( 'login'=>'','id'=>$id,'item_number'=>str_replace('/', '%5C', $item_number),
			'savemsg'=>$savemsg,
			'message'=>$message);
		echo json_encode($array);
	}
	
	public function AJremove_IMEI(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$item_id = intval($POST['item_id']??0);
		$imeimessage = $POST['imeimessage']??'';

		$returnstr = '';

		$queryObj = $this->db->query("SELECT item_id FROM item WHERE accounts_id = $accounts_id AND item_id = :item_id", array('item_id'=>$item_id),1);
		if($queryObj){
			$itemrow = $queryObj->fetch(PDO::FETCH_OBJ);

			$item_id = $itemrow->item_id;

			$updateitem = $this->db->update('item', array('in_inventory'=>0, 'last_updated'=>date('Y-m-d H:i:s')), $item_id);
			if($updateitem){
				$notes = 'REMOVED FROM INVENTORY';
				if($imeimessage !=''){
					$notes .= '<br />'.$imeimessage;
				}
				$note_for = $this->db->checkCharLen('notes.note_for', 'item');
				$noteData=array('table_id'=> $item_id,
								'note_for'=> $note_for,
								'created_on'=> date('Y-m-d H:i:s'),
								'last_updated'=> date('Y-m-d H:i:s'),
								'accounts_id'=> $_SESSION["accounts_id"],
								'user_id'=> $_SESSION["user_id"],
								'note'=> $notes,
								'publics'=>0);
				$notes_id = $this->db->insert('notes', $noteData);
				
				$returnstr = 'Ok';
			}			
		}		
		return json_encode(array('login'=>'', 'returnStr'=>$returnstr));
	}
	
	private function filterHAndOptions(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$sitem_id = $this->item_id;
		$sitem_number = $this->item_number;
		$sproduct_id = $this->product_id;
		$shistory_type = $this->history_type;
		$filterSql = '';
		$likeSql = "";
		if(count(explode(', ', $sitem_id))>1){
			foreach(explode(', ', $sitem_id) as $oneItemId) {
				$likeSql .= " AND activity_feed_link LIKE '%/$oneItemId'";
			}
		}
		else{
			$likeSql .= " AND activity_feed_link LIKE '%/$sitem_id'";
		}

		$propertiesIds = array();
		if($sitem_number !=''){
			$filterSql = "SELECT properties_id FROM properties WHERE accounts_id = $accounts_id AND imei_or_serial_no = :imei_or_serial_no";
			$tableObj = $this->db->query($filterSql, array('imei_or_serial_no'=>$sitem_number));
			if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$propertiesIds[] = $oneRow->properties_id;
				}
			}		
		}
		
		$bindData = array();            
		if($shistory_type !=''){
			
			if(strcmp($shistory_type, 'IMEI Created')==0){
				$filterSql = "SELECT COUNT(item_id) AS totalrows FROM item WHERE accounts_id = $accounts_id AND item_id IN ($sitem_id)";
			}
			elseif(strcmp($shistory_type, 'Sales Invoice Returned')==0){
				$filterSql = "SELECT COUNT(pos.pos_id) AS totalrows FROM pos, pos_cart, pos_cart_item WHERE pos.accounts_id = $accounts_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'livestocks' 
							 AND pos.pos_id = pos_cart.pos_id AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 0";
				$bindData['sproduct_id'] = $sproduct_id;
			}
			elseif(in_array($shistory_type, array('Order Created', 'Repair Created', 'Sales Invoice Created'))){
				$filterSql = "SELECT COUNT(pos.pos_id) AS totalrows FROM pos, pos_cart, pos_cart_item WHERE pos.accounts_id = $accounts_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'livestocks' AND pos.pos_id = pos_cart.pos_id AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 1";
				$bindData['sproduct_id'] = $sproduct_id;
			}
			elseif(strcmp($shistory_type, 'Repairs Property Created')==0){
				$filterSql = "SELECT COUNT(repairs_id) AS totalrows FROM repairs WHERE accounts_id = $accounts_id AND properties_id IN (".implode(', ', $propertiesIds).")";
			}
			elseif(strcmp($shistory_type, 'Purchase Order Created')==0){
				$filterSql = "SELECT COUNT(po.po_id) AS totalrows FROM po, po_items, po_cart_item WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_cart_item.po_items_id = po_items.po_items_id AND po_items.received_qty>0";
			}
			elseif(strcmp($shistory_type, 'Purchase Return Created')==0){
				$filterSql = "SELECT COUNT(po.po_id) AS totalrows FROM po, po_items, po_cart_item WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_cart_item.return_po_items_id = po_items.po_items_id AND po_items.received_qty<0";
			}
			elseif(strcmp($shistory_type, 'Inventory Transfer To')==0){
				$filterSql = "SELECT COUNT(po.po_id) AS totalrows FROM po, po_items, po_cart_item WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 1 AND po.po_id = po_items.po_id AND po_cart_item.return_po_items_id = po_items.po_items_id AND po_items.received_qty<0";
			}
			elseif(strcmp($shistory_type, 'Inventory Transfer From')==0){
				$filterSql = "SELECT COUNT(po.po_id) AS totalrows FROM po, po_items, po_cart_item WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 2 AND po.po_id = po_items.po_id AND po_cart_item.po_items_id = po_items.po_items_id AND po_items.received_qty!=0";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'item' AND record_id IN ($sitem_id)";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes WHERE accounts_id = $accounts_id AND note_for = 'item' AND table_id IN ($sitem_id)";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed WHERE accounts_id = $accounts_id AND uri_table_name = 'item' $likeSql";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
		}
		else{
			$repairsSql='';
			if(!empty($propertiesIds)){
				$repairsSql = " UNION ALL SELECT COUNT(repairs_id) AS totalrows FROM repairs WHERE accounts_id = $accounts_id AND properties_id IN (".implode(', ', $propertiesIds).")";
			}
			
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed WHERE accounts_id = $accounts_id AND uri_table_name = 'item' $likeSql 
					UNION ALL 
						SELECT COUNT(item_id) AS totalrows FROM item WHERE accounts_id = $accounts_id AND item_id IN ($sitem_id) $repairsSql 
					UNION ALL 
						SELECT COUNT(pos.pos_id) AS totalrows FROM pos, pos_cart, pos_cart_item WHERE pos.accounts_id = $accounts_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'livestocks' 
							 AND pos.pos_id = pos_cart.pos_id AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 0 
					UNION ALL 
						SELECT COUNT(pos.pos_id) AS totalrows FROM pos, pos_cart, pos_cart_item WHERE pos.accounts_id = $accounts_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'livestocks' 
							AND pos.pos_id = pos_cart.pos_id AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 1 
					UNION ALL 
						SELECT COUNT(po.po_id) AS totalrows FROM po, po_items, po_cart_item 
						WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_cart_item.po_items_id = po_items.po_items_id AND po_items.received_qty>0  
					UNION ALL 
						SELECT COUNT(po.po_id) AS totalrows FROM po, po_items, po_cart_item 
						WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_cart_item.return_po_items_id = po_items.po_items_id AND po_items.received_qty<0 
					UNION ALL 
						SELECT COUNT(po.po_id) AS totalrows FROM po, po_items, po_cart_item 
						WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 1 AND po.po_id = po_items.po_id AND po_cart_item.return_po_items_id = po_items.po_items_id AND po_items.received_qty<0  
					UNION ALL 
						SELECT COUNT(po.po_id) AS totalrows FROM po, po_items, po_cart_item 
						WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 2 AND po.po_id = po_items.po_id AND po_cart_item.po_items_id = po_items.po_items_id AND po_items.received_qty!=0 
					UNION ALL 
						SELECT COUNT(track_edits_id) AS totalrows FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'item' AND record_id IN ($sitem_id) 
					UNION ALL 
						SELECT COUNT(notes_id) AS totalrows FROM notes WHERE accounts_id = $accounts_id AND note_for = 'item' AND table_id IN ($sitem_id)";
		}
		 
		$totalRows = 0;
		$tableObj = $this->db->query($filterSql, $bindData);
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$totalRows += $oneRow->totalrows;
			}
		}		
		$this->totalRows = $totalRows;
		$repairsSql='';
		if(!empty($propertiesIds)){
			$repairsSql = " UNION ALL SELECT 'Repairs Property Created' AS afTitle FROM repairs WHERE accounts_id = $accounts_id AND properties_id IN (".implode(', ', $propertiesIds).")";
		}
		
		$actFeeTitOpt = array();			
		$Sql = "SELECT activity_feed_title AS afTitle FROM activity_feed WHERE accounts_id = $accounts_id AND uri_table_name = 'item' $likeSql 
			UNION ALL 
				SELECT 'IMEI Created' AS afTitle FROM item WHERE accounts_id = $accounts_id AND item_id IN ($sitem_id) $repairsSql 
			UNION ALL 
				SELECT 'Sales Invoice Returned' AS afTitle FROM pos, pos_cart, pos_cart_item WHERE pos.accounts_id = $accounts_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'livestocks' 
					AND pos.pos_id = pos_cart.pos_id AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 0 
			UNION ALL 
				SELECT (Case When pos.pos_type = 'Order' AND pos.order_status = 1 Then 'Order Created' When pos.pos_type = 'Repairs' AND pos.order_status != 2 Then 'Repair Created' 
						Else 'Sales Invoice Created' End) AS afTitle 
				FROM pos, pos_cart, pos_cart_item WHERE pos.accounts_id = $accounts_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'livestocks' 
					AND pos.pos_id = pos_cart.pos_id AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 1 
			UNION ALL 
				SELECT 'Purchase Order Created' AS afTitle FROM po, po_items, po_cart_item 
				WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_cart_item.po_items_id = po_items.po_items_id AND po_items.received_qty>0  
			UNION ALL 
				SELECT 'Purchase Return Created' AS afTitle FROM po, po_items, po_cart_item 
				WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_cart_item.return_po_items_id = po_items.po_items_id AND po_items.received_qty<0 
			UNION ALL 
				SELECT 'Inventory Transfer To' AS afTitle FROM po, po_items, po_cart_item 
				WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 1 AND po.po_id = po_items.po_id AND po_cart_item.return_po_items_id = po_items.po_items_id AND po_items.received_qty<0  
			UNION ALL 
				SELECT 'Inventory Transfer From' AS afTitle FROM po, po_items, po_cart_item 
				WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 2 AND po.po_id = po_items.po_id AND po_cart_item.po_items_id = po_items.po_items_id AND po_items.received_qty!=0 
			UNION ALL 
				SELECT 'Track Edits' AS afTitle FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'item' AND record_id IN ($sitem_id) 
			UNION ALL 
				SELECT 'Notes Created' AS afTitle FROM notes WHERE accounts_id = $accounts_id AND note_for = 'item' AND table_id IN ($sitem_id)";
		$tableObj = $this->db->query($Sql, array());
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
	
   private function loadHTableRows(){
		
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sitem_id = $this->item_id;
		$sitem_number = $this->item_number;
		$sproduct_id = $this->product_id;
		$shistory_type = $this->history_type;
	
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$bindData = array();
		$likeSql = "";
		if(count(explode(', ', $sitem_id))>1){
			foreach(explode(', ', $sitem_id) as $oneItemId) {
				$likeSql .= " AND activity_feed_link LIKE '%/$oneItemId'";
			}
		}
		else{
			$likeSql .= " AND activity_feed_link LIKE '%/$sitem_id'";
		}

		$propertiesIds = array();
		if($sitem_number !=''){
			$filterSql = "SELECT properties_id FROM properties WHERE accounts_id = $accounts_id AND imei_or_serial_no = :imei_or_serial_no";
			$tableObj = $this->db->query($filterSql, array('imei_or_serial_no'=>$sitem_number));
			if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$propertiesIds[] = $oneRow->properties_id;
				}
			}		
		}
		
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'IMEI Created')==0){
				$filterSql = "SELECT 'item' as tablename, created_on AS tabledate, item_id as table_id, 'IMEI Created' as activity_feed_title FROM item 
						WHERE accounts_id = $accounts_id AND item_id IN ($sitem_id)";
			}
			elseif(strcmp($shistory_type, 'Sales Invoice Returned')==0){
				$filterSql = "SELECT 'posreturn' as tablename, pos.sales_datetime AS tabledate, pos.pos_id as table_id, 
							'Sales Invoice Returned' as activity_feed_title 
						FROM pos, pos_cart, pos_cart_item 
						WHERE pos.accounts_id = $accounts_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'livestocks' 
							 AND pos.pos_id = pos_cart.pos_id AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 0";
			}
			elseif(in_array($shistory_type, array('Order Created', 'Repair Created', 'Sales Invoice Created'))){
				$filterSql = "SELECT 'pos' as tablename, pos.sales_datetime AS tabledate, pos.pos_id as table_id, 
							(Case When pos.pos_type = 'Order' AND pos.order_status = 1 Then 'Order Created' 
								When pos.pos_type = 'Repairs' AND pos.order_status != 2 Then 'Repair Created' 
								Else 'Sales Invoice Created' End) as activity_feed_title 
						FROM pos, pos_cart, pos_cart_item 
						WHERE pos.accounts_id = $accounts_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'livestocks' 
							AND pos.pos_id = pos_cart.pos_id AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 1";
			}
			elseif(strcmp($shistory_type, 'Repairs Property Created')==0){
				$filterSql = "SELECT 'repairs' AS tablename, created_on AS tabledate, repairs_id AS table_id, 'Repairs Property Created' as activity_feed_title FROM repairs WHERE accounts_id = $accounts_id AND properties_id IN (".implode(', ', $propertiesIds).")";
			}
			elseif(strcmp($shistory_type, 'Purchase Order Created')==0){
				$filterSql = "SELECT 'po' as tablename, po_items.created_on AS tabledate, po.po_id as table_id, 'Purchase Order Created' as activity_feed_title 
						FROM po, po_items, po_cart_item 
						WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_cart_item.po_items_id = po_items.po_items_id AND po_items.received_qty>0";
			}
			elseif(strcmp($shistory_type, 'Purchase Return Created')==0){
				$filterSql = "SELECT 'poreturn' as tablename, po_items.created_on AS tabledate, po.po_id as table_id, 'Purchase Return Created' as activity_feed_title 
						FROM po, po_items, po_cart_item 
						WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_cart_item.return_po_items_id = po_items.po_items_id AND po_items.received_qty<0";
			}
			elseif(strcmp($shistory_type, 'Inventory Transfer To')==0){
				$filterSql = "SELECT 'poreturn' as tablename, po_items.created_on AS tabledate, po.po_id as table_id, 'Inventory Transfer To' as activity_feed_title 
						FROM po, po_items, po_cart_item 
						WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 1 AND po.po_id = po_items.po_id AND po_cart_item.return_po_items_id = po_items.po_items_id AND po_items.received_qty<0";
			}
			elseif(strcmp($shistory_type, 'Inventory Transfer From')==0){
				$filterSql = "SELECT 'po' as tablename, po_items.created_on AS tabledate, po.po_id as table_id, 'Inventory Transfer From' as activity_feed_title 
						FROM po, po_items, po_cart_item 
						WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 2 AND po.po_id = po_items.po_id AND po_cart_item.po_items_id = po_items.po_items_id AND po_items.received_qty!=0";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on as tabledate,  notes_id as table_id, 'Notes Created' as activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'item' AND table_id IN ($sitem_id)";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'item' AND record_id IN ($sitem_id)";
			}
			else{
				$filterSql = "SELECT 'activity_feed' as tablename, created_on AS tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed 
					WHERE accounts_id = $accounts_id AND uri_table_name = 'item' $likeSql";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
		}
		else{

			$repairsSql='';
			if(!empty($propertiesIds)){
				$repairsSql = " SELECT 'repairs' AS tablename, created_on AS tabledate, repairs_id AS table_id, 'Repairs Property Created' as activity_feed_title FROM repairs WHERE accounts_id = $accounts_id AND properties_id IN (".implode(', ', $propertiesIds).") UNION ALL";
			}
			
			$filterSql = "SELECT 'activity_feed' as tablename, created_on AS tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed";
			$filterSql .= " WHERE accounts_id = $accounts_id AND uri_table_name = 'item' $likeSql";
			$filterSql .= " UNION ALL";
			$filterSql .= " SELECT 'item' as tablename, created_on AS tabledate, item_id as table_id, 'IMEI Created' as activity_feed_title FROM item";
			$filterSql .= " WHERE accounts_id = $accounts_id AND item_id IN ($sitem_id)";
			$filterSql .= " UNION ALL $repairsSql";
			$filterSql .= " SELECT 'posreturn' as tablename, pos.sales_datetime AS tabledate, pos.pos_id as table_id, 'Sales Invoice Returned' as activity_feed_title FROM pos, pos_cart, pos_cart_item";
			$filterSql .= " WHERE pos.accounts_id = $accounts_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'livestocks' AND pos.pos_id = pos_cart.pos_id AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 0";
			$filterSql .= " UNION ALL";
			$filterSql .= " SELECT 'pos' as tablename, pos.sales_datetime AS tabledate, pos.pos_id as table_id, (Case When pos.pos_type = 'Order' AND pos.order_status = 1 Then 'Order Created' When pos.pos_type = 'Repairs' AND pos.order_status != 2 Then 'Repair Created' Else 'Sales Invoice Created' End) as activity_feed_title FROM pos, pos_cart, pos_cart_item";
			$filterSql .= " WHERE pos.accounts_id = $accounts_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'livestocks' AND pos.pos_id = pos_cart.pos_id AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 1";
			$filterSql .= " UNION ALL";
			$filterSql .= " SELECT 'po' as tablename, po_items.created_on AS tabledate, po.po_id as table_id, 'Purchase Order Created' as activity_feed_title FROM po, po_items, po_cart_item";
			$filterSql .= " WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_cart_item.po_items_id = po_items.po_items_id AND po_items.received_qty>0";
			$filterSql .= " UNION ALL";
			$filterSql .= " SELECT 'poreturn' as tablename, po_items.created_on AS tabledate, po.po_id as table_id, 'Purchase Return Created' as activity_feed_title FROM po, po_items, po_cart_item";
			$filterSql .= " WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_cart_item.return_po_items_id = po_items.po_items_id AND po_items.received_qty<0";
			$filterSql .= " UNION ALL";
			$filterSql .= " SELECT 'poreturn' as tablename, po_items.created_on AS tabledate, po.po_id as table_id, 'Inventory Transfer To' as activity_feed_title FROM po, po_items, po_cart_item";
			$filterSql .= " WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 1 AND po.po_id = po_items.po_id AND po_cart_item.return_po_items_id = po_items.po_items_id AND po_items.received_qty<0";
			$filterSql .= " UNION ALL";
			$filterSql .= " SELECT 'po' as tablename, po_items.created_on AS tabledate, po.po_id as table_id, 'Inventory Transfer From' as activity_feed_title FROM po, po_items, po_cart_item";
			$filterSql .= " WHERE po.accounts_id = $accounts_id AND po_cart_item.item_id IN ($sitem_id) AND po.transfer = 2 AND po.po_id = po_items.po_id AND po_cart_item.po_items_id = po_items.po_items_id AND po_items.received_qty!=0";
			$filterSql .= " UNION ALL";
			$filterSql .= " SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits";
			$filterSql .= " WHERE accounts_id = $accounts_id AND record_for = 'item' AND record_id IN ($sitem_id)";
			$filterSql .= " UNION ALL";
			$filterSql .= " SELECT 'notes' AS tablename, created_on as tabledate,  notes_id as table_id, 'Notes Created' as activity_feed_title FROM notes";
			$filterSql .= " WHERE accounts_id = $accounts_id AND note_for = 'item' AND table_id IN ($sitem_id)";
		}
		
		$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($filterSql, $bindData);

		$tabledata = array();
		//$tabledata[] = $filterSql;
		if($query){
			$userIdNames = array();
			$userObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id", array());
			if($userObj){
				while($userOneRow = $userObj->fetch(PDO::FETCH_OBJ)){
					$userIdNames[$userOneRow->user_id] = trim("$userOneRow->user_first_name $userOneRow->user_last_name");
				}
			}					
			$Activity_Feed = new Activity_Feed($this->db);
			foreach($query as $ponerow){
				$tablename = $ponerow['tablename'];
				$table_id = $ponerow['table_id'];
				$tabledate = $ponerow['tabledate'];
				$activity_feed_title = $ponerow['activity_feed_title'];
				
				$getHMoreInfo = $Activity_Feed->getHMoreInfo($table_id, $tablename, $userIdNames, $activity_feed_title, $sitem_id);
				if(!empty($getHMoreInfo)){
					$getHMoreInfo[3] = $tabledate;
					$tabledata[] = $getHMoreInfo;
				}
			}
		}

		return $tabledata;
    }


	public function AJget_LivestocksGrowthInfoPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$growthData = array();
		$growthData['login'] = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$product_growthinfo_id = intval($POST['product_growthinfo_id']??0);
		
		$price_type = $type_match = '';

		if(!isset($_SESSION["accounts_id"])){
			$growthData['login'] = 'session_ended';
		}

		$growthData['growth'] = 0.00;
		$growthData['weight'] = 0.00;
		$growthData['review_date'] = '';
		
		if($product_growthinfo_id>0){
			$ppObj = $this->db->query("SELECT * FROM product_growthinfo WHERE accounts_id = $accounts_id AND product_growthinfo_id = :product_growthinfo_id", array('product_growthinfo_id'=>$product_growthinfo_id),1);
			if($ppObj){
				$oneRow = $ppObj->fetch(PDO::FETCH_OBJ);
				$growthData['growth'] = round($oneRow->growth,2);
				$growthData['weight'] = round($oneRow->weight,2);
				$growthData['review_date'] = date('Y-m-d', strtotime($oneRow->review_date));
			}
		}
		
		return json_encode($growthData);
	}




	private function loadHTableRowsGrowth(){
		
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sitem_id = $this->item_id;
		$sproduct_id = $this->product_id;
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$bindData = array();

		$filterSql = "SELECT * FROM product_growthinfo";
		$filterSql .= " WHERE accounts_id = $accounts_id AND product_id = $sproduct_id";		
		$filterSql .= " ORDER BY review_date DESC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($filterSql, $bindData);

		$tabledata = array();
		// $tabledata[] = $query;
		if($query){

			foreach($query as $ponerow){

				$growth_info_id  = $ponerow['product_growthinfo_id'];
				$review_date = $ponerow['review_date'];
				$growth = $ponerow['growth'];
				$weight = $ponerow['weight'];
				$product_id = $ponerow['product_id'];
				
				$getHMoreInfo = array($growth_info_id, $review_date, $growth, $weight, $product_id);
				if(!empty($getHMoreInfo)){
					$tabledata[] = $getHMoreInfo;
				}

			}
		}

		return $tabledata;
    }
	


	public function AJsave_LivestocksGrowth(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		
		$product_growthinfo_id = intval($POST['product_growthinfo_id']??0);
		$product_id = intval($POST['product_id']??0);		
		$growth = floatval($POST['growth']??0);
		$weight = floatval($POST['weight']??0);
		$review_date = date('Y-m-d H:i:s', strtotime($POST['review_date']));
		// $start_date = trim((string) $POST['start_date']??'1000-01-01');
		// if(!in_array($start_date, array('', '1000-01-01'))){$start_date = date('Y-m-d', strtotime($start_date));}
		// else{$start_date = '1000-01-01';}	
		
		$growth = $this->db->checkCharLen('product_growthinfo.growth', $growth);

		$conditionarray = array();
		$conditionarray['accounts_id'] = $accounts_id;
		$conditionarray['user_id'] = $user_id;
		$conditionarray['product_id'] = $product_id;
		$conditionarray['growth'] = $growth;			
		$conditionarray['weight'] = $weight;			
		$conditionarray['review_date'] = $review_date;

		$bindData = array('product_id'=>$product_id, 'growth'=>$growth, 'review_date'=>$review_date);
		$duplCheckSql = "SELECT COUNT(product_growthinfo_id) AS totalrows FROM product_growthinfo WHERE accounts_id = $accounts_id AND product_id = :product_id AND growth = :growth AND review_date = :review_date";
		if($product_growthinfo_id>0){
			$duplCheckSql .= " AND product_growthinfo_id != :product_growthinfo_id";
			$bindData['product_growthinfo_id'] = $product_growthinfo_id;
		}
		$totalrows = 0;
		$queryObj = $this->db->query($duplCheckSql, $bindData);
		if($queryObj){
			$totalrows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		if($totalrows>0){
			$savemsg = 'growthInfoExist';
		}
		else{
			
			if($product_growthinfo_id==0){			
			
				$conditionarray['created_on'] = date('Y-m-d H:i:s');

				$product_growthinfo_id = $this->db->insert('product_growthinfo', $conditionarray);

				if($product_growthinfo_id){
					
					$activity_feed_name = stripslashes(trim((string) "$growth, $weight"));
					
					$activity_feed_title = $this->db->translate('Livestock weight info has been added');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Livestocks/view/$product_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $accounts_id,
									'user_id' => $user_id,
									'activity_feed_title' => $activity_feed_title,
									'activity_feed_name' => $activity_feed_name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "product",
									'uri_table_field_name' =>"product_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
				}
				else{
					$savemsg = 'errorAddingWeightInfo';
				}
			}
			else{

				// $oldPPObj = $this->db->query("SELECT * FROM product_growthinfo WHERE accounts_id = $accounts_id AND product_growthinfo_id = :product_growthinfo_id", array('product_growthinfo_id'=>$product_growthinfo_id),1);
				// if($oldPPObj){
				// 	$oldPPRow = $oldPPObj->fetch(PDO::FETCH_OBJ);
				// }

				$update = $this->db->update('product_growthinfo', $conditionarray, $product_growthinfo_id);

				
			}


		}

		$array = array( 'login'=>'', 'product_growthinfo_id'=>$product_growthinfo_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
	}

	
	
	
	 public function prints($segment4name, $segment5name){
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';

		$htmlStr = "";
		$itemObj = $this->db->query("SELECT * FROM item WHERE item_id = :item_id AND accounts_id = $accounts_id", array('item_id'=>$segment5name),1);
		if($itemObj){	
			$item_onerow = $itemObj->fetch(PDO::FETCH_OBJ);
			$item_id = $item_onerow->item_id;	
			$Printing = new Printing($this->db);		
			if($segment4name=='barcode'){
				$htmlStr = $Printing->labelsInfo('IMEI');
			}
			else if($segment4name == 'label_MoreInfo'){
				$custom_data = $item_onerow->custom_data;

				$jsonResponse = array();
				$jsonResponse['login'] = '';
				
				$commonInfo = $Printing->labelsInfo('IMEI', 'commonInfo');
				$jsonResponse['commonInfo'] = $commonInfo;
				
				$CompanyName = $_SESSION["company_name"];
				$ProductName = $Price = $Barcode = '';
								
				$product_id = $item_onerow->product_id;
				if($product_id>0){
					$product_id = $item_onerow->product_id;
					$productObj = $this->db->query("SELECT p.*, manufacturer.name AS manufacture FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_id = :product_id", array('product_id'=>$product_id),1);
					if($productObj){
						$product_onerow = $productObj->fetch(PDO::FETCH_OBJ);
						$ProductName .= stripslashes(trim((string) $product_onerow->product_name));
						$manufacturer_name = stripslashes(trim((string) $product_onerow->manufacture));
						if($manufacturer_name !=''){$ProductName = stripslashes(trim($manufacturer_name.' '.$ProductName));}
						
						$colour_name = $product_onerow->colour_name;
						if($colour_name !=''){$ProductName .= ' '.$colour_name;}
						
						$storage = $product_onerow->storage;
						if($storage !=''){$ProductName .= ' '.$storage;}
						
						$physical_condition_name = $product_onerow->physical_condition_name;
						if($physical_condition_name !=''){$ProductName .= ' '.$physical_condition_name;}
						
						if($item_onerow){
							if($item_onerow->carrier_name !=''){$ProductName .= ' '.$item_onerow->carrier_name;}
						}	
						
						$inventoryObj = $this->db->query("SELECT regular_price FROM inventory WHERE accounts_id = $accounts_id AND product_id = $product_onerow->product_id", array());
						if($inventoryObj){
							$regular_price = $inventoryObj->fetch(PDO::FETCH_OBJ)->regular_price;
							if($regular_price>0){
								$Price .= $currency.number_format($regular_price,2);
							}
						}
					}
				}
				
				$Barcode = stripslashes(trim((string) $item_onerow->item_number));

				$jsonResponse['CompanyName'] = $CompanyName;
				$jsonResponse['ProductName'] = $ProductName;
				$jsonResponse['Price'] = $Price;
				$jsonResponse['Barcode'] = $Barcode;
				$Common = new Common($this->db);
				$jsonResponse['customFieldsData'] = $Common->customFormFields('devices', $custom_data);

				return json_encode($jsonResponse);
			}
		}
		return $htmlStr;
	}

	//========================ASync========================//	
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		
		$sin_inventory = $POST['sin_inventory']??'';
		$sproduct_id = intval($POST['sproduct_id']??0);
		$scarrier_name = $POST['scarrier_name']??'';
		$scolour_name = $POST['scolour_name']??'';
		$sphysical_condition_name = $POST['sphysical_condition_name']??'';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->in_inventory = $sin_inventory;
		if(!is_int($sproduct_id)){$this->db->writeIntoLog("Error on IMEI#494: A/C Id: $_SESSION[accounts_id], sproduct_id: $sproduct_id, ASCII: ".ord($sproduct_id).", segment2name: IMEI, segment3name: AJgetPage");$sproduct_id = 0;}
		$this->product_id = $sproduct_id;
		$this->carrier_name = $scarrier_name;
		$this->colour_name = $scolour_name;
		$this->physical_condition_name = $sphysical_condition_name;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['proNamOpt'] = $this->proNamOpt;
			$jsonResponse['carNamOpt'] = $this->carNamOpt;
			$jsonResponse['colNamOpt'] = $this->colNamOpt;
			$jsonResponse['phyConNamOpt'] = $this->phyConNamOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}
	
	public function AJgetHPage($segment4name){

		$accounts_id = $_SESSION["accounts_id"]??0;
		$POST = json_decode(file_get_contents('php://input'), true);
		$sproduct_id = intval($POST['sproduct_id']??0);
		$sitem_id = $POST['sitem_id']??'';
		$item_number = $POST['item_number']??'';
		
		if(!empty($item_number)){
			$itemIds = array();
			$itemObj = $this->db->query("SELECT item_id FROM item WHERE item_number = :item_number AND accounts_id = $accounts_id ORDER BY item_id DESC", array('item_number'=>str_replace('%5C', '/', $item_number)));
			if($itemObj){
				while($oneItemRow = $itemObj->fetch(PDO::FETCH_OBJ)){
					$itemIds[$oneItemRow->item_id] = '';
				}
			}

			if(!empty($itemIds)){
				$sitem_id = implode(', ', array_keys($itemIds));
			}
		}

		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		if(!is_int($sproduct_id)){$this->db->writeIntoLog("Error on IMEI#1064: A/C Id: $_SESSION[accounts_id], sproduct_id: $sproduct_id, ASCII: ".ord($sproduct_id).", segment2name: $GLOBALS[segment2name], segment3name: $GLOBALS[segment3name]");$sproduct_id = 0;}
		$this->product_id = $sproduct_id;
		$this->item_id = (string) $sitem_id;
		$this->item_number = $item_number;
		$this->history_type = $shistory_type;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;	
			$jsonResponse['actFeeTitOpt'] = $this->actFeeTitOpt;		
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResponse['tableRows'] = $this->loadHTableRows();
		
		return json_encode($jsonResponse);
	}

	public function AJgetHPage_Growth($segment4name){

		$accounts_id = $_SESSION["accounts_id"]??0;
		$POST = json_decode(file_get_contents('php://input'), true);
		$sproduct_id = intval($POST['sproduct_id']??0);
		$sitem_id = $POST['sitem_id']??'';
		$item_number = $POST['item_number']??'';
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);

		// var_dump($POST);exit;
		
		if(!is_int($sproduct_id)){$this->db->writeIntoLog("Error on IMEI#1064: A/C Id: $_SESSION[accounts_id], sproduct_id: $sproduct_id, ASCII: ".ord($sproduct_id).", segment2name: $GLOBALS[segment2name], segment3name: $GLOBALS[segment3name]");$sproduct_id = 0;}
		$this->product_id = $sproduct_id;
		$this->item_id = (string) $sitem_id;
		$this->item_number = $item_number;
		$this->history_type = $shistory_type;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;	
			$jsonResponse['actFeeTitOpt'] = $this->actFeeTitOpt;		
		}

		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResponse['tableRows'] = $this->loadHTableRowsGrowth();
		
		return json_encode($jsonResponse);
	}
		
	public function AJ_view_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$item_number = $POST['item_number']??'';

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$itemObj = $this->db->query("SELECT * FROM item WHERE item_number = :item_number AND accounts_id = $accounts_id ORDER BY item_id DESC LIMIT 0, 1", array('item_number'=>str_replace('%5C', '/', $item_number)));
		if($itemObj){
			$itemarray = $itemObj->fetch(PDO::FETCH_OBJ);			
			$item_id = $itemarray->item_id;
			
			$item_number = $itemarray->item_number;

			$product_id = $itemarray->product_id;				
			$product_name = $sku_number = $storage = $colour_name = $imei_imageURL = '';
			if($product_id>0){
				$sqlPM = "SELECT manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku FROM product p 
				LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
				WHERE p.product_id = $product_id";
				$productObj = $this->db->query($sqlPM, array());
				if($productObj){
					$product_row = $productObj->fetch(PDO::FETCH_OBJ);	
			
					$product_name = stripslashes($product_row->product_name);
					$sku_number = stripslashes($product_row->sku);
					
					$product_name = stripslashes(trim($product_row->manufacture.' '.$product_name));
							
					$colour_name = $product_row->colour_name;
					if($colour_name !=''){$product_name .= ' '.$colour_name;}
					
					$storage = $product_row->storage;
					if($storage !=''){$product_name .= ' '.$storage;}
					
					$physical_condition_name = $product_row->physical_condition_name;
					if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}
				}
			}
			
			$filePath = "./assets/accounts/a_$accounts_id/prod_$product_id".'_';
			$pics = glob($filePath."*.jpg");
			if($pics){
				foreach($pics as $onePicture){
					$imei_imageURL = str_replace('./', '/', $onePicture);
				}
			}
			$colorlockcarrier = '';
			
			if($imei_imageURL =='')
				$imei_imageURL = '/assets/images/Batteries.png';
			
			$carrier_name = $itemarray->carrier_name;
			if($carrier_name !=''){
				$colorlockcarrier .= ' '.$carrier_name;
			}
			
			$cost = 0.00;
			$po_number = array();
			$Common = new Common($this->db);
			$poCostInfo = $Common->oneIMEIAveCost(0, $item_id, date('Y-m-d H:i:s'));
			if(!empty($poCostInfo)){
				
				$cost = round($poCostInfo[0],2);
				$purcAveData = $poCostInfo[1];
				if(!empty($purcAveData)){
					foreach($purcAveData as $dateTime=>$costInfo){
						$po_number[] = $costInfo[1];
					}
				}			
			}
			
			$sales_datetime = $orderInvNo = $invoiceNo = '';
			$in_inventory = $itemarray->in_inventory;
			if($in_inventory==0){
				$posObj =$this->db->querypagination("SELECT pos.sales_datetime, pos.invoice_no, pos.pos_type, pos.order_status FROM pos_cart_item pci, pos_cart pc, pos WHERE pci.item_id = $item_id AND pci.pos_cart_id = pc.pos_cart_id AND pc.pos_id = pos.pos_id LIMIT 0, 1", array());
				if($posObj){
					$posrow = $posObj[0];
					$sales_datetime = $posrow['sales_datetime'];
					$invoiceNo = $posrow['invoice_no'];
					if($posrow['pos_type']=='Order' && $posrow['order_status']==1){
						$orderInvNo = 1;
					}
				}
			}

			$is_admin = 0;
			if(isset($_SESSION["is_admin"])){
				$is_admin = $_SESSION["is_admin"];
			}
			if($is_admin>0){
				//$product_name .= " || $is_admin";
			}
			$jsonResponse['item_id'] = intval($item_id);
			$jsonResponse['product_name'] = $product_name;
			$jsonResponse['imei_imageURL'] = $imei_imageURL;
			$jsonResponse['item_number'] = $item_number;
			$jsonResponse['colorlockcarrier'] = $colorlockcarrier;
			$jsonResponse['product_id'] = intval($product_id);
			$jsonResponse['sku_number'] = $sku_number;
			$jsonResponse['created_on'] = $itemarray->created_on;
			$jsonResponse['po_number'] = $po_number;
			$jsonResponse['allowed'] = '';
			if(empty($_SESSION["allowed"]) || (!empty($_SESSION["allowed"]) && array_key_exists(6, $_SESSION["allowed"]))){
				$jsonResponse['allowed'] = '1';
			}
			$jsonResponse['cost'] = $cost;
			$jsonResponse['sales_datetime'] = $sales_datetime;
			$jsonResponse['invoiceNo'] = $invoiceNo;
			$jsonResponse['orderInvNo'] = $orderInvNo;
			
			$jsonResponse['in_inventory'] = intval($in_inventory);
			
			$cusDataInfo = $Common->customViewInfo('devices', $itemarray->custom_data);
			$jsonResponse['customFields'] = $cusDataInfo[0];
			$jsonResponse['customFieldData'] = $cusDataInfo[1];		
		}
		else{
			$jsonResponse['login'] = 'Growthinfos/lists';
		}
		return json_encode($jsonResponse);
	}
	
}
?>