<?php
class Products{
	protected $db;
	private int $page, $totalRows, $product_id;
	private string $data_type, $category_id, $manufacturer_id, $keyword_search, $history_type;
	private array $manOpt, $catOpt;
	
	public function __construct($db){$this->db = $db;}

	public function lists(){}
	
	public function view(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		return '<input type="hidden" name="accounts_id" id="accounts_id" value="'.$accounts_id.'">';
	}
	
	private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$sdata_type = $this->data_type;
		$smanufacturer_id = $this->manufacturer_id;
		$scategory_id = $this->category_id;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Products";
		$_SESSION["list_filters"] = array('sdata_type'=>$sdata_type, 'smanufacturer_id'=>$smanufacturer_id, 'scategory_id'=>$scategory_id, 'keyword_search'=>$keyword_search);
		
		$filterSql = "";
		$bindData = array();
		if($smanufacturer_id !='All'){
			$filterSql .= " AND p.manufacturer_id = :manufacturer_id";
			$bindData['manufacturer_id'] = $smanufacturer_id;
		}
		if($scategory_id !='All'){
			$filterSql .= " AND p.category_id = :category_id";
			$bindData['category_id'] = $scategory_id;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', p.product_id, manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$addiselect = $havingsql = '';
		if($sdata_type=='Low Stock'){
			$addiselect = ', i.low_inventory_alert AS low_inventory_alert';
			$havingsql = " HAVING current_inventory < low_inventory_alert";
		}
		$sqlPublish = " AND p.product_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND p.product_publish = 0";
		}
		$strextra ="SELECT p.product_id AS product_id, p.category_id, p.manufacturer_id, i.current_inventory AS current_inventory, manufacturer.name AS manufacture$addiselect FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
					WHERE i.accounts_id = $accounts_id AND p.product_type != 'Live Stocks' $sqlPublish $filterSql";
		if($sdata_type=='Available'){
			$strextra .= " AND ((p.manage_inventory_count = 0 OR p.manage_inventory_count is null) OR (p.manage_inventory_count=1 AND i.current_inventory>0) OR p.allow_backorder = 1)";
		}
		elseif($sdata_type=='Low Stock'){
			$strextra .= " AND (p.manage_inventory_count>0 AND i.current_inventory < i.low_inventory_alert)";
		}
		
		$strextra .= " AND i.product_id = p.product_id";
		
		$strextra .= " GROUP BY product_id$havingsql";

		$totalRows = 0;
		$manOpts = $catOpts = array();
		$queryObj = $this->db->query($strextra, $bindData);
		if($queryObj){
			$totalRows = $queryObj->rowCount();
			while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$catOpts[$oneRow->category_id] = '';
				if($oneRow->manufacturer_id>0){
					$manOpts[$oneRow->manufacturer_id] = stripslashes(trim((string) $oneRow->manufacture));
				}
			}
		}
		if(!empty($manOpts)){asort($manOpts);}

		$catOpt = array();			
		if(count($catOpts)>0){
			$catStr = "SELECT category_id, category_name FROM category WHERE category_id IN (".implode(', ', array_keys($catOpts)).") AND accounts_id = $prod_cat_man ORDER BY category_name ASC";
			$catQuery = $this->db->query($catStr, array());
			if($catQuery){
				while($oneRow=$catQuery->fetch(PDO::FETCH_OBJ)){
					$catOpt[$oneRow->category_id] = stripslashes(trim((string) $oneRow->category_name));
				}
			}
		}
		
		$this->totalRows = $totalRows;
		$this->manOpt = $manOpts;
		$this->catOpt = $catOpt;
	}
	
    private function loadTableRows(){
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'$';
		$limit = $_SESSION["limit"]??'auto';
		$Carts = new Carts($this->db);
		
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$sdata_type = $this->data_type;
		$smanufacturer_id = $this->manufacturer_id;
		$scategory_id = $this->category_id;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
				
		$filterSql = '';			
		$bindData = array();
		if($smanufacturer_id !='All'){
			$filterSql .= " AND p.manufacturer_id = :manufacturer_id";
			$bindData['manufacturer_id'] = $smanufacturer_id;
		}
		if($scategory_id !='All'){
			$filterSql .= " AND p.category_id = :category_id";
			$bindData['category_id'] = $scategory_id;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', p.product_id, manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		$addiselect = $havingsql = '';
		if($sdata_type=='Low Stock'){
			$addiselect = ', i.low_inventory_alert AS low_inventory_alert';
			$havingsql = " HAVING current_inventory < low_inventory_alert";
		}
		$sqlPublish = " AND p.product_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND p.product_publish = 0";
		}
		
		$strextra ="SELECT p.product_id, p.product_type, p.sku, p.category_id, i.current_inventory AS current_inventory, manufacturer.name AS manufacture, p.product_name as product_name, p.colour_name AS colour_name, p.storage AS storage, p.physical_condition_name AS physical_condition_name, i.regular_price, p.manage_inventory_count, i.current_inventory, p.allow_backorder, i.low_inventory_alert AS low_inventory_alert 
					FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
					WHERE i.accounts_id = $accounts_id AND p.product_type != 'Live Stocks' $sqlPublish $filterSql";
		if($sdata_type=='Available'){
			$strextra .= " AND ((p.manage_inventory_count = 0 OR p.manage_inventory_count is null) OR (p.manage_inventory_count=1 AND i.current_inventory>0) OR p.allow_backorder = 1)";
		}
		elseif($sdata_type=='Low Stock'){
			$strextra .= " AND (p.manage_inventory_count>0 AND i.current_inventory < i.low_inventory_alert)";
		}
		$strextra .= " AND i.product_id = p.product_id GROUP BY p.product_id";
		$strextra .= " ORDER BY manufacture ASC, product_name ASC, colour_name ASC, storage ASC, physical_condition_name ASC";
		
		$sqlquery = "$strextra LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			$categoryIds = array();
			foreach($query as $rowproduct){
				if ($rowproduct['category_id']>0 && !in_array($rowproduct['category_id'], $categoryIds)) {
					$categoryIds[] = $rowproduct['category_id'];
				}
			}

			$categoryList = array();
			if(count($categoryIds)>0){
				$catStr = "SELECT category_id, category_name FROM category WHERE category_id IN (".implode(', ', $categoryIds).") AND accounts_id = $prod_cat_man ORDER BY category_name ASC";
				$catQuery = $this->db->query($catStr, array());
				if($catQuery){
					while($oneRow = $catQuery->fetch(PDO::FETCH_OBJ)){
						$categoryList[$oneRow->category_id] = stripslashes(trim((string) $oneRow->category_name));
					}
				}
			}

			foreach($query as $rowproduct){

				$product_id = intval($rowproduct['product_id']);
				$product_type = $rowproduct['product_type'];
				$sku = stripslashes($rowproduct['sku']);
				
				$category_name = '';
				if($rowproduct['category_id']>0 && array_key_exists($rowproduct['category_id'], $categoryList)){$category_name = $categoryList[$rowproduct['category_id']];}

				$manufacturer_name = stripslashes(trim((string) $rowproduct['manufacture']));
				$product_name = stripslashes(trim((string) $rowproduct['product_name']));

				$colour_name = stripslashes(trim((string) $rowproduct['colour_name']));
				if($colour_name !=''){$product_name .= ' '.$colour_name;}

				$storage = $rowproduct['storage'];
				if($storage !=''){$product_name .= ' '.$storage;}

				$physical_condition_name = stripslashes(trim((string) $rowproduct['physical_condition_name']));
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

				$alertclass = '';
				$regular_price = round($rowproduct['regular_price'],2);
				$current_inventory = 0;
				$manage_inventory_count = intval($rowproduct['manage_inventory_count']);
				if($manage_inventory_count>0){
					$current_inventory = $rowproduct['current_inventory'];
					$allow_backorder = $rowproduct['allow_backorder'];
					$low_inventory_alert = $rowproduct['low_inventory_alert'];

					if($current_inventory<$low_inventory_alert){
						$alertclass = ' alert alert-danger';
					}
				}

				$NeedHaveOnPOInfo = array();
				$NeedHaveOnPOInfo['product_type'] = $product_type;
				$NeedHaveOnPOInfo['manage_inventory_count'] = $manage_inventory_count;
				$NeedHaveOnPOInfo['need'] = 0;
				$NeedHaveOnPOInfo['have'] = $current_inventory;
				$NeedHaveOnPOInfo['onPO'] = 0;
				
				$NeedHaveOnPO = $current_inventory;
				if(in_array($product_type, array('Standard')) && $manage_inventory_count>0){
					$NHPInfo = $Carts->NeedHaveOnPO($product_id, $product_type, 1);
					$NeedHaveOnPOInfo['need'] = $NHPInfo[0];
					$NeedHaveOnPOInfo['have'] = $NHPInfo[1];
					$NeedHaveOnPOInfo['onPO'] = $NHPInfo[2];
				}
				$tabledata[] = array($product_id, $alertclass, $manufacturer_name, $product_name, $sku, $category_name, $regular_price, $NeedHaveOnPOInfo);
			}
		}
		return $tabledata;
    }
	
	private function filterHAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$sproduct_id = $this->product_id;
		$shistory_type = $this->history_type;
		$filterSql = '';
		$bindData = array();
		$bindData['sproduct_id'] = $sproduct_id;
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Product Created')==0){
				$filterSql = "SELECT COUNT(product_id) AS totalrows FROM product 
							WHERE product_id = :sproduct_id AND accounts_id = $prod_cat_man";
			}
			elseif(strcmp($shistory_type, 'Sales Invoice Created')==0){
				$filterSql = "SELECT COUNT(pos.pos_id) AS totalrows FROM pos, pos_cart 
						WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = :sproduct_id AND pos.pos_id = pos_cart.pos_id AND ((pos.pos_type = 'Sale' AND pos.order_status =1) OR (pos.pos_type in ('Order', 'Repairs') AND pos.order_status =2))";
			}			
			elseif(strcmp($shistory_type, 'Order Created')==0){
				$filterSql = "SELECT COUNT(pos.pos_id) AS totalrows FROM pos, pos_cart 
						WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = :sproduct_id AND pos.pos_id = pos_cart.pos_id AND pos.pos_type = 'Order' AND pos.order_status =1";
			}			
			elseif(strcmp($shistory_type, 'Repair Created')==0){
				$filterSql = "SELECT COUNT(pos.pos_id) AS totalrows FROM pos, pos_cart 
						WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = :sproduct_id AND pos.pos_id = pos_cart.pos_id AND pos.pos_type = 'Repairs' AND pos.order_status = 1";
			}			
			elseif(strcmp($shistory_type, 'Purchase Order Created')==0){
				$filterSql = "SELECT COUNT(po.po_id) AS totalrows FROM po, po_items 
							WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 0 AND po.po_id = po_items.po_id AND po.po_publish = 1";
			}
			elseif(strcmp($shistory_type, 'Inventory Transfer To')==0){
				$filterSql = "SELECT COUNT(po.po_id) AS totalrows FROM po, po_items 
							WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 1 AND po.po_id = po_items.po_id AND po.po_publish = 1";
			}
			elseif(strcmp($shistory_type, 'Inventory Transfer From')==0){
				$filterSql = "SELECT COUNT(po.po_id) AS totalrows FROM po, po_items 
							WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 2 AND po.po_id = po_items.po_id AND po.po_publish = 1";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'product' AND table_id = :sproduct_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'product' AND record_id = :sproduct_id";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'product' AND activity_feed_link = CONCAT('/Products/view/', :sproduct_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'product' AND activity_feed_link = CONCAT('/Products/view/', :sproduct_id) 
						UNION ALL 
							SELECT COUNT(product_id) AS totalrows FROM product 
							WHERE product_id = :sproduct_id AND accounts_id = $prod_cat_man 
						UNION ALL 
							SELECT COUNT(pos.pos_id) AS totalrows FROM pos, pos_cart 
							WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = :sproduct_id AND pos.pos_id = pos_cart.pos_id AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status in (1,2))) 
						UNION ALL 
							SELECT COUNT(po.po_id) AS totalrows FROM po, po_items 
							WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 0 AND po.po_id = po_items.po_id AND po.po_publish = 1 
						UNION ALL 
							SELECT COUNT(po.po_id) AS totalrows FROM po, po_items 
							WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 1 AND po.po_id = po_items.po_id AND po.po_publish = 1 
						UNION ALL 
							SELECT COUNT(po.po_id) AS totalrows FROM po, po_items 
							WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 2 AND po.po_id = po_items.po_id AND po.po_publish = 1 
						UNION ALL 
							SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'product' AND record_id = :sproduct_id 
						UNION ALL SELECT COUNT(notes_id) AS totalrows FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'product' AND table_id = :sproduct_id";
		}
		
		$totalRows = 0;
		$tableObj = $this->db->query($filterSql, $bindData);
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$totalRows += $oneRow->totalrows;
			}
		}		
		$this->totalRows = $totalRows;
	}
	
    private function loadHTableRows(){
		
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sproduct_id = $this->product_id;
		$shistory_type = $this->history_type;
	
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$bindData = array();
		$bindData['sproduct_id'] = $sproduct_id;            
		
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Product Created')==0){
				$filterSql = "SELECT 'product' as tablename, created_on as tabledate, product_id as table_id, 'Product Created' as activity_feed_title FROM product 
							WHERE product_id = :sproduct_id AND accounts_id = $prod_cat_man";
			}
			elseif(strcmp($shistory_type, 'Sales Invoice Created')==0){
				$filterSql = "SELECT 'pos' as tablename, pos.created_on as tabledate, pos.pos_id as table_id, 'Sales Invoice Created' as activity_feed_title 
						FROM pos, pos_cart 
						WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = :sproduct_id AND pos.pos_id = pos_cart.pos_id AND ((pos_type = 'Sale' AND order_status =1) OR (pos_type in ('Order', 'Repairs') AND order_status =2))";
			}
			elseif(strcmp($shistory_type, 'Order Created')==0){
				$filterSql = "SELECT 'pos' as tablename, pos.created_on as tabledate, pos.pos_id as table_id, 'Order Created' as activity_feed_title FROM pos, pos_cart 
						WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = :sproduct_id AND pos.pos_id = pos_cart.pos_id AND pos.pos_type = 'Order' AND pos.order_status =1";
			}			
			elseif(strcmp($shistory_type, 'Repair Created')==0){
				$filterSql = "SELECT 'pos' as tablename, pos.created_on as tabledate, pos.pos_id as table_id, 'Repair Created' as activity_feed_title FROM pos, pos_cart 
						WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = :sproduct_id AND pos.pos_id = pos_cart.pos_id AND pos.pos_type = 'Repairs' AND pos.order_status = 1";
			}
			elseif(strcmp($shistory_type, 'Purchase Order Created')==0){
				$filterSql = "SELECT 'po' as tablename, po_items.created_on as tabledate, po.po_id as table_id, 'Purchase Order Created' as activity_feed_title 
						FROM po, po_items 
						WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 0 AND po.po_id = po_items.po_id AND po.po_publish = 1";
			}
			elseif(strcmp($shistory_type, 'Inventory Transfer To')==0){
				$filterSql = "SELECT 'po' as tablename, po_items.created_on as tabledate, po.po_id as table_id, 'Inventory Transfer To' as activity_feed_title 
						FROM po, po_items 
						WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 1 AND po.po_id = po_items.po_id AND po.po_publish = 1";
			}
			elseif(strcmp($shistory_type, 'Inventory Transfer From')==0){
				$filterSql = "SELECT 'po' as tablename, po_items.created_on as tabledate, po.po_id as table_id, 'Inventory Transfer From' as activity_feed_title 
						FROM po, po_items 
						WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 2 AND po.po_id = po_items.po_id AND po.po_publish = 1";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on as tabledate,  notes_id as table_id, 'Notes Created' as activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'product' AND table_id = :sproduct_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'product' AND record_id = :sproduct_id";
			}
			else{
				$filterSql = "SELECT 'activity_feed' as tablename, created_on as tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed 
					WHERE accounts_id = $accounts_id AND uri_table_name = 'product' AND activity_feed_link = CONCAT('/Products/view/', :sproduct_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
		}
		else{
			$filterSql = "SELECT 'activity_feed' as tablename, created_on as tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed 
					WHERE accounts_id = $accounts_id AND uri_table_name = 'product' AND activity_feed_link = CONCAT('/Products/view/', :sproduct_id) 
					UNION ALL 
						SELECT 'product' as tablename, created_on as tabledate, product_id as table_id, 'Product Created' as activity_feed_title FROM product 
							WHERE product_id = :sproduct_id AND accounts_id = $prod_cat_man 
					UNION ALL 
						SELECT 'pos' as tablename, pos.created_on as tabledate, pos.pos_id as table_id, 
							(Case When pos.pos_type = 'Order' AND pos.order_status = 1 Then 'Order Created' 
							When pos.pos_type = 'Repairs' AND pos.order_status = 1 Then 'Repair Created' 
							Else 'Sales Invoice Created' End) as activity_feed_title 
						FROM pos, pos_cart 
						WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = :sproduct_id AND pos.pos_id = pos_cart.pos_id AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status in (1,2))) 
					UNION ALL 
						SELECT 'po' as tablename, po_items.created_on as tabledate, po.po_id as table_id, 'Purchase Order Created' as activity_feed_title 
						FROM po, po_items 
						WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 0 AND po.po_id = po_items.po_id AND po.po_publish = 1 
					UNION ALL 
						SELECT 'po' as tablename, po_items.created_on as tabledate, po.po_id as table_id, 'Inventory Transfer To' as activity_feed_title 
						FROM po, po_items 
						WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 1 AND po.po_id = po_items.po_id AND po.po_publish = 1 
					UNION ALL 
						SELECT 'po' as tablename, po_items.created_on as tabledate, po.po_id as table_id, 'Inventory Transfer From' as activity_feed_title 
						FROM po, po_items 
						WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 2 AND po.po_id = po_items.po_id AND po.po_publish = 1 
					UNION ALL SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'product' AND record_id = :sproduct_id 
					UNION ALL 
						SELECT 'notes' AS tablename, created_on as tabledate,  notes_id as table_id, 'Notes Created' as activity_feed_title FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'product' AND table_id = :sproduct_id";
		}
		$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
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
			foreach($query as $gonerow){
				$tablename = $gonerow['tablename'];
				$table_id = $gonerow['table_id'];
				$activity_feed_title = stripslashes(trim((string) $gonerow['activity_feed_title']));
				
				if(strcmp($tablename,'po')==0){
					$subSql = "SELECT po_items.po_items_id, po_items.product_id, po.status, po_items.received_qty, po.po_number, po_items.created_on, po.user_id, po.transfer, po.supplier_id FROM po, po_items WHERE po.po_id = $table_id AND po_items.product_id = $sproduct_id AND po.accounts_id = $accounts_id AND po.po_id = po_items.po_id";
					$subQueryObj = $this->db->query($subSql, array());
					if($subQueryObj){
						while($oneRow = $subQueryObj->fetch(PDO::FETCH_OBJ)){
							$po_items_id = $oneRow->po_items_id;
							$received_qty = $oneRow->received_qty;
							if($oneRow->transfer==1 && $oneRow->status=='Open'){
								$received_qty = -$oneRow->received_qty;
							}
							$activity_feed_name = "($received_qty) ";
							$productObj = $this->db->query("SELECT manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.product_type FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_id = $oneRow->product_id", array());
							if($productObj){
								$product_row = $productObj->fetch(PDO::FETCH_OBJ);

								$product_name = stripslashes($product_row->product_name);
								$product_name = stripslashes(trim((string) $product_row->manufacture.' '.$product_name));

								$colour_name = $product_row->colour_name;
								if($colour_name !=''){$product_name .= ' '.$colour_name;}

								$storage = $product_row->storage;
								if($storage !=''){$product_name .= ' '.$storage;}

								$physical_condition_name = $product_row->physical_condition_name;
								if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

								$activity_feed_name .= $product_name;
								$product_type = $product_row->product_type;
							}

							$activity_feed_link = '/Purchase_orders/edit/'.$oneRow->po_number;
							if($oneRow->transfer>0){
								$activity_feed_link = '/Inventory_Transfer/edit/'.$oneRow->po_number;
								$supplier_id = $oneRow->supplier_id;
								$supplierssqlquery = $this->db->query("SELECT company_subdomain FROM accounts WHERE accounts_id = $supplier_id AND status != 'SUSPENDED'", array());
								if($supplierssqlquery){
									$activity_feed_title .= ' '.stripslashes(trim((string) $supplierssqlquery->fetch(PDO::FETCH_OBJ)->company_subdomain));
								}
							}

							$userName = $userIdNames[$oneRow->user_id]??'';
							
							$tabledata[] =  array($activity_feed_name, $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $oneRow->po_number);
						
						}
					}
				}
				else{
					$getHMoreInfo = $Activity_Feed->getHMoreInfo($table_id, $tablename, $userIdNames, $activity_feed_title, $sproduct_id, 'product_id');
					if(!empty($getHMoreInfo)){
						$tabledata[] = $getHMoreInfo;
					}
				}
			}
		}

		return $tabledata;
    }
	
	public function AJget_ProductsPopup(){	
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$product_id = intval($POST['product_id']??0);

		$Common = new Common($this->db);
		
		$product_type = $colour_name = $storage = $physical_condition_name = $alert_message = '';
		$category_id = $manufacturer_id = $low_inventory_alert = $require_serial_no = $manage_inventory_count = $allow_backorder = $ave_cost_is_percent = 0;
		$taxable = 1;
		
		$productData = array();
		$cnc = $cne = 0;
		if(!empty($_SESSION["allowed"]) && array_key_exists(5, $_SESSION["allowed"]) && in_array('cnc', $_SESSION["allowed"][5])) {
			$cnc = 1;
		}
		if(!empty($_SESSION["allowed"]) && array_key_exists(5, $_SESSION["allowed"]) && in_array('cne', $_SESSION["allowed"][5])) {
			$cne = 1;
		}
		
		$productData['cnc'] = $cnc;
		$productData['cne'] = $cne;
		$productData['login'] = '';	
		$productData['disabled'] = '';
		$productData['regular_price'] = 0.00;
		$productData['minimum_price'] = 0.00;
		$productData['ave_cost'] = 0.00;
		$productData['current_inventory'] = 0;
		$productData['current_inventoryReadonly'] = '';			
		$productData['product_name'] = '';
		$productData['sku'] = '';
		$productData['description'] = '';
		$productData['add_description'] = '';
		$custom_data = '';
		if($product_id>0 && $prod_cat_man>0){
			$queryObj = $this->db->query("SELECT * FROM product WHERE product_id = :product_id AND accounts_id=$prod_cat_man AND product_publish=1", array('product_id'=>$product_id),1);
			if($queryObj){
				$productRow = $queryObj->fetch(PDO::FETCH_OBJ);
				$product_id = $productRow->product_id;
				$product_type = $productRow->product_type;
				$category_id = $productRow->category_id;
				$manufacturer_id = $productRow->manufacturer_id;
				$colour_name = stripslashes(trim((string) $productRow->colour_name));
				$storage = $productRow->storage;
				$physical_condition_name = stripslashes(trim((string) $productRow->physical_condition_name));
				$taxable = $productRow->taxable;
				$require_serial_no = $productRow->require_serial_no;
				$manage_inventory_count = $productRow->manage_inventory_count;
				$allow_backorder = $productRow->allow_backorder;
				$custom_data = $productRow->custom_data;
				$productData['disabled'] = ' disabled';
				$productData['product_name'] = stripslashes(trim((string) $productRow->product_name));
				$productData['sku'] = $productRow->sku;
				$productData['add_description'] = stripslashes(trim((string) $productRow->add_description));
				$alert_message = stripslashes(trim((string) $productRow->alert_message));
				
				$queryInvObj = $this->db->query("SELECT * FROM inventory WHERE product_id = $product_id AND accounts_id=$accounts_id", array());
				if($queryInvObj){
					$inventoryRow = $queryInvObj->fetch(PDO::FETCH_OBJ);
					
					$low_inventory_alert = $inventoryRow->low_inventory_alert;
					$ave_cost_is_percent = $inventoryRow->ave_cost_is_percent;
					if($manage_inventory_count>0){$ave_cost_is_percent = 0;}
				
					$productData['regular_price'] = round($inventoryRow->regular_price,2);
					$productData['minimum_price'] = round($inventoryRow->minimum_price,2);
					
					$productData['ave_cost'] = round($inventoryRow->ave_cost,2);
					
					$productData['current_inventory'] = floatval($inventoryRow->current_inventory);
					$productData['current_inventoryReadonly'] = ' readonly';
				}					
			}
		}
		
		$productData['product_type'] = $product_type;
		$productData['alert_message'] = $alert_message;
				
		$productData['category_id'] = intval($category_id);
		$catOpt = array();
		if($prod_cat_man>0){
			$sqlcategory = "SELECT category_id, category_name FROM category WHERE accounts_id = $prod_cat_man AND (category_publish = 1 OR (category_id = $category_id AND category_publish = 0)) ORDER BY category_name ASC";
			$categoryquery = $this->db->query($sqlcategory, array());
			if($categoryquery){
				while($onecategoryrow = $categoryquery->fetch(PDO::FETCH_OBJ)){
					$ocategory_id = $onecategoryrow->category_id;
					$ocategory_name = stripslashes(trim((string) $onecategoryrow->category_name));
					$catOpt[$ocategory_id] = $ocategory_name;
				}
			}
		}
		$productData['catOpt'] = $catOpt;
		
		$productData['manufacturer_id'] = intval($manufacturer_id);
		$manOpt = array();
		if($prod_cat_man>0){
			$sqlmanufacturer = "SELECT manufacturer_id, name FROM manufacturer WHERE accounts_id = $prod_cat_man AND (manufacturer_publish = 1 OR (manufacturer_publish = 0 AND manufacturer_id = :manufacturer_id)) ORDER BY name ASC";
			$manufacturerquery = $this->db->query($sqlmanufacturer, array('manufacturer_id'=> $manufacturer_id));
			if($manufacturerquery){
				while($onemanufacturerrow = $manufacturerquery->fetch(PDO::FETCH_OBJ)){
					$omanufacturer_id = $onemanufacturerrow->manufacturer_id;
					$omanufacturer_name = stripslashes(trim((string) $onemanufacturerrow->name));
					if($omanufacturer_name !=''){
						$manOpt[$omanufacturer_id] = $omanufacturer_name;
					}
				}
			}
		}
		$productData['manOpt'] = $manOpt;
		
		$productData['colour_name'] = $colour_name;
		$colourNameData = $this->colourNameData();
		
		$sqlColNam = "SELECT colour_name FROM product WHERE accounts_id = $prod_cat_man AND product_publish = 1 GROUP BY colour_name ORDER BY colour_name ASC";
		$colNamObj = $this->db->query($sqlColNam, array());
		if($colNamObj){
			while($colNamRow = $colNamObj->fetch(PDO::FETCH_OBJ)){
				if(!empty($colNamRow->colour_name) && !in_array($colNamRow->colour_name, $colourNameData)){
					$colourNameData[] = stripslashes(trim((string) $colNamRow->colour_name));
				}
			}
		}
		$colNamOpt = array();
		if (!empty($colourNameData)){
			sort($colourNameData);
			foreach ($colourNameData as $oneOption) {
				$colNamOpt[] = $oneOption;
			}
		}
		$productData['colour_name'] = $colour_name;
		$productData['colNamOpt'] = $colNamOpt;
		
		$productData['storage'] = $storage;
		$productData['physical_condition_name'] = $physical_condition_name;
		$conditionsData = array();
		$conditionsData = array('A', 'B', 'C', 'D', 'New');
		if($accounts_id>0){			
			$vData = $Common->variablesData('product_setup', $accounts_id);
			if(!empty($vData) && array_key_exists('conditions', $vData)){
				$conditionsData = explode('||',$vData['conditions']);
			}
		}
		$phyConNamOpt = array();
		if (!empty($conditionsData)) {
			foreach ($conditionsData as $oneOption) {
				if(!empty($oneOption)){
					$phyConNamOpt[] = $oneOption;
				}
			}
		}
		$productData['phyConNamOpt'] = $phyConNamOpt;
		
		$productData['taxable'] = intval($taxable);				
		$productData['require_serial_no'] = intval($require_serial_no);		
		$productData['manage_inventory_count'] = intval($manage_inventory_count);
		if($manage_inventory_count>0){$ave_cost_is_percent = 0;}		
		$productData['ave_cost_is_percent'] = intval($ave_cost_is_percent);
		$productData['allow_backorder'] = intval($allow_backorder);		
		$productData['low_inventory_alert'] = intval($low_inventory_alert);
		$productData['product_id'] = intval($product_id);
		$productData['customFieldsData'] = $Common->customFormFields('product', $custom_data);
		
		return json_encode($productData);
	}	
	
	public function AJsave_Products(){
		$POST = $_POST;
		$id = 0;
		$sku = $savemsg = $returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$Common = new Common($this->db);
		
		$product_id = intval($POST['product_id']??0);
		$category_id = intval($POST['category_id']??0);
		$product_type = $POST['product_type']??'';
		$product_type = $this->db->checkCharLen('product.product_type', $product_type);
		$category_name = trim((string) $POST['category_name']??'');
		$category_name = $this->db->checkCharLen('category.category_name', $category_name);
		$manufacturer_id = intval($POST['manufacturer_id']??0);
		$manufacture = addslashes(trim((string) $POST['manufacture']??''));
		$manufacture = $this->db->checkCharLen('manufacturer.name', $manufacture);
		$product_name = addslashes(trim((string) $POST['product_name']??''));
		$product_name = $this->db->checkCharLen('product.product_name', $product_name);
		$colour_name = addslashes(trim((string) $POST['colour_name']??''));
		$colour_name = $this->db->checkCharLen('product.colour_name', $colour_name);
		$colour_name2 = addslashes(trim((string) $POST['colour_name2']??''));
		$colour_name2 = $this->db->checkCharLen('product.colour_name', $colour_name2);
		$sku = addslashes(trim((string) $POST['sku']??''));
		$sku = $this->db->checkCharLen('product.sku', $sku);
		$require_serial_no = intval($POST['require_serial_no']??0);
		$manage_inventory_count = intval($POST['manage_inventory_count']??0);
		$add_description = addslashes(trim((string) $POST['add_description']??''));
		$storage = addslashes(trim((string) $POST['storage']??''));
		$storage = $this->db->checkCharLen('product.storage', $storage);
		$physical_condition_name = addslashes(trim((string) array_key_exists('physical_condition_name', $POST)?$POST['physical_condition_name']:''));
		$physical_condition_name = $this->db->checkCharLen('product.physical_condition_name', $physical_condition_name);
		
		$ave_cost = floatval($POST['ave_cost']??0);
		if(empty($ave_cost)){
			$ave_cost = 0.00;
		}
		if($ave_cost != floatval($ave_cost)){
			$returnStr .= 'Invalid Average Price. Please enter valid Average Price.<br>';
		}
		$ave_cost_is_percent = intval($POST['ave_cost_is_percent']??0);
		$regular_price = floatval($POST['regular_price']??0.00);
		if(empty($regular_price)){
			$regular_price = 0.00;
		}
		elseif($regular_price != floatval($regular_price)){
			$returnStr .= 'Invalid Price (<b>'.$regular_price.'</b>). Please enter valid Price.<br>';
		}
		
		$minimum_price = floatval($POST['minimum_price']??0.00);
		if(empty($minimum_price)){
			$minimum_price = 0.00;
		}
		$taxable = intval($POST['taxable']??0);
		$low_inventory_alert = intval($POST['low_inventory_alert']??0);
		$allow_backorder = intval($POST['allow_backorder']??0);
		$alert_message = addslashes(trim((string) $POST['alert_message']??''));
		
		$last_updated = $created_on = date('Y-m-d H:i:s');
					
		if(!empty($category_name)){
			$queryCatObj = $this->db->query("SELECT category_id FROM category WHERE accounts_id = $prod_cat_man AND UPPER(category_name) = :category_name", array('category_name'=>strtoupper($category_name)));
			if($queryCatObj){
				$category_id = $queryCatObj->fetch(PDO::FETCH_OBJ)->category_id;
			}
			else{
				$category_name = $this->db->checkCharLen('category.category_name', $category_name);
				$categoryData = array('category_name' => $category_name,
					'created_on' => date('Y-m-d H:i:s'),
					'last_updated' => date('Y-m-d H:i:s'),
					'accounts_id' => $prod_cat_man,
					'user_id' => $user_id
				);
				$category_id = $this->db->insert('category', $categoryData);
			}
		}
		if(!empty($manufacture)){
			$queryManuObj = $this->db->query("SELECT manufacturer_id FROM manufacturer WHERE accounts_id = $prod_cat_man AND UPPER(name) = :name", array('name'=>strtoupper($manufacture)));
			if($queryManuObj){
				$manufacturer_id = $queryManuObj->fetch(PDO::FETCH_OBJ)->manufacturer_id;
			}
			else{
				$manufacturerData = array('name' => $manufacture,
					'created_on' => date('Y-m-d H:i:s'),
					'last_updated' => date('Y-m-d H:i:s'),
					'accounts_id' => $prod_cat_man,
					'user_id' => $user_id
				);
				$manufacturer_id = $this->db->insert('manufacturer', $manufacturerData);
			}
		}
		if($colour_name == '' && $colour_name2 !=''){$colour_name = $colour_name2;}
		$sku = str_replace(' ', '-', strtoupper($sku));
		if($sku =='' && $product_id>0){$sku = $product_id;}
		if($storage==0){$storage = '';}
		
		$productdata = array();
		$productdata['category_id'] = $category_id;
		$productdata['product_type'] = $product_type;
		$productdata['accounts_id'] = $prod_cat_man;
		$productdata['last_updated'] = $last_updated;
		$productdata['manufacturer_id'] = intval($manufacturer_id);
		$productdata['manufacture'] = '';
		$productdata['product_name'] = $product_name;
		$productdata['colour_name'] = $colour_name;
		$productdata['sku'] = $sku;
		$productdata['require_serial_no'] = $require_serial_no;
		$productdata['manage_inventory_count'] = intval($manage_inventory_count);
		$productdata['add_description'] = $add_description;
		$productdata['alert_message'] = $alert_message;
		if($manage_inventory_count==0){
			$low_inventory_alert = 0;
		}
		$productdata['physical_condition_name'] = $physical_condition_name;
		$productdata['storage'] = $storage;
		$productdata['taxable'] = $taxable;
		$productdata['custom_data'] = '';
		
		$inventorydata = array();
		$inventorydata['accounts_id'] = $accounts_id;
		
		if($ave_cost<0){$ave_cost = 0.00;}
		$inventorydata['ave_cost'] = round($ave_cost,2);
		$inventorydata['ave_cost_is_percent'] = $ave_cost_is_percent;
		$inventorydata['regular_price'] = round($regular_price,2);
		$inventorydata['minimum_price'] = round($minimum_price,2);
		$inventorydata['low_inventory_alert'] = $low_inventory_alert;
		$inventorydata['prices_enabled'] = 0;
		
		if($product_id>0){
			$todaydate = date('Y-m-d');
			$prodPriceSql = "SELECT COUNT(product_prices_id) AS totalrows FROM product_prices WHERE accounts_id = $accounts_id AND product_id = $product_id AND (start_date IN ('0000-00-00', '1000-01-01') OR (start_date <= '$todaydate' AND end_date >= '$todaydate'))";
			$prices_enabled = 0;
			$prodPriceObj = $this->db->query($prodPriceSql, array());
			if($prodPriceObj){
				$prices_enabled = $prodPriceObj->fetch(PDO::FETCH_OBJ)->totalrows;
			}
			if($prices_enabled>0){
				$inventorydata['prices_enabled'] = 1;
			}
		}

		if($manage_inventory_count>0){
			$inventorydata['ave_cost_is_percent'] = $ave_cost_is_percent = 0;
			$productdata['allow_backorder'] = $allow_backorder;
		}
		else{
			$productdata['allow_backorder'] = 0;
		}
		
		if(empty($returnStr) && $product_id==0){
			if($manage_inventory_count>0){
				$inventorydata['ave_cost'] = 0.00;
			}
			$inventorydata['current_inventory'] = 0;
			$productdata['user_id'] = $_SESSION["user_id"];
			$productdata['created_on'] = date('Y-m-d H:i:s');
			$productdata['description'] = '';
			
			$totalrows1 = $product_publish1 = 0;
			$queryProdObj = $this->db->query("SELECT product_id, product_publish FROM product WHERE accounts_id = $prod_cat_man AND manufacturer_id = :manufacturer_id AND product_name = :product_name AND colour_name = :colour_name AND storage = :storage AND physical_condition_name = :physical_condition_name ", array('manufacturer_id'=>$manufacturer_id, 'product_name'=>$product_name, 'colour_name'=>$colour_name, 'storage'=>$storage, 'physical_condition_name'=>$physical_condition_name));
			if($queryProdObj){
				while($oneProdRow = $queryProdObj->fetch(PDO::FETCH_OBJ)){
					$totalrows1 = $oneProdRow->product_id;
					$product_publish1 = $oneProdRow->product_publish;
				}
			}

			$totalrows2 = $product_publish2 = 0;
			if(!empty($sku)){
				$queryProd2Obj = $this->db->query("SELECT product_id, product_publish FROM product WHERE accounts_id = $prod_cat_man AND sku = :sku", array('sku'=>$sku));
				if($queryProd2Obj){
					while($oneProdRow = $queryProd2Obj->fetch(PDO::FETCH_OBJ)){
						$totalrows2 = $oneProdRow->product_id;
						$product_publish2 = $oneProdRow->product_publish;
					}
				}
			}

			if($totalrows1>0){
				if($product_publish1>0){
					$savemsg = 'Name_Already_Exist';
				}
				else{
					$savemsg = 'Name_ExistInArchive';
				}
			}
			elseif($totalrows2>0){
				if($product_publish2>0){
					$savemsg = 'SKU_Already_Exist';
				}
				else{
					$savemsg = 'SKU_ExistInArchive';
				}
			}
			else{
				$product_id = $this->db->insert('product', $productdata);
				if($product_id){
					$custom_data = $Common->postCustomFormFields('product');
					
					$this->db->update('product', array('custom_data'=>$custom_data), $product_id);

					$inventorydata['product_id'] = $product_id;
					$inventory_id = $this->db->insert('inventory', $inventorydata);
					if($inventory_id){
						$locationquery = $this->db->query("SELECT accounts_id FROM accounts WHERE (location_of IN ($accounts_id, $prod_cat_man) OR accounts_id IN ($accounts_id, $prod_cat_man)) AND accounts_id != $accounts_id AND status != 'SUSPENDED'", array());
						if($locationquery){
							while($oneUserRow = $locationquery->fetch(PDO::FETCH_OBJ)){
								$inventorydata['accounts_id'] = $oneUserRow->accounts_id;
								$this->db->insert('inventory', $inventorydata);
							}
						}
					}

					if($sku ==''){
						$sku = $product_id;
						$this->db->update('product', array('sku'=>$product_id), $product_id);
					}

					$savemsg = 'add-success';
					$id = $product_id;
				}
				else{
					$savemsg = 'error-adding-product';
				}
			}
		}
		elseif(empty($returnStr)){
			$totalrows1 = 0;
			$queryProdObj = $this->db->query("SELECT COUNT(product_id) AS totalrows FROM product WHERE accounts_id = $prod_cat_man AND manufacturer_id = :manufacturer_id AND product_name = :product_name AND colour_name = :colour_name AND storage = :storage AND physical_condition_name = :physical_condition_name AND product_publish = 1 AND product_id != :product_id", array('manufacturer_id'=>$manufacturer_id, 'product_name'=>$product_name, 'colour_name'=>$colour_name, 'storage'=>$storage, 'physical_condition_name'=>$physical_condition_name, 'product_id'=>$product_id));
			if($queryProdObj){
				$totalrows1 = $queryProdObj->fetch(PDO::FETCH_OBJ)->totalrows;
			}

			$totalrows2 = 0;
			$queryProd2Obj = $this->db->query("SELECT COUNT(product_id) AS totalrows FROM product WHERE accounts_id = $prod_cat_man AND sku = :sku AND product_publish = 1 AND product_id != :product_id", array('sku'=>$sku, 'product_id'=>$product_id));
			if($queryProd2Obj){
				$totalrows2 = $queryProd2Obj->fetch(PDO::FETCH_OBJ)->totalrows;
			}

			if($totalrows1>0){
				$savemsg = 'name-already-exist';
			}
			elseif($totalrows2>0){
				$savemsg = 'sku-already-exist';
			}
			else{
				$productdata['custom_data'] = $Common->postCustomFormFields('product');

				$prodObj = $this->db->querypagination("SELECT * FROM product WHERE accounts_id = $prod_cat_man AND product_id = $product_id", array());
				$changed = array();
				$update = $this->db->update('product', $productdata, $product_id);
				if($update){
					if($prodObj){
						unset($productdata['last_updated']);
						foreach($productdata as $fieldName=>$fieldValue){
							$prevFieldVal = $prodObj[0][$fieldName];
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
								if($fieldName=='category_id'){
									$fieldName = 'category';
									if($prevFieldVal==0){$prevFieldVal = '';}
									elseif($prevFieldVal>0){
										$queryCatObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $prevFieldVal", array());
										if($queryCatObj){
											$prevFieldVal = $queryCatObj->fetch(PDO::FETCH_OBJ)->category_name;
										}
									}
									if($fieldValue==0){$fieldValue = '';}
									elseif($fieldValue>0){
										$queryCatObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $fieldValue", array());
										if($queryCatObj){
											$fieldValue = $queryCatObj->fetch(PDO::FETCH_OBJ)->category_name;
										}
									}
								}
								elseif($fieldName=='manufacturer_id'){
									$fieldName = 'manufacture';
									if($prevFieldVal==0){$prevFieldVal = '';}
									elseif($prevFieldVal>0){
										$queryManObj = $this->db->query("SELECT name FROM manufacturer WHERE manufacturer_id = $prevFieldVal", array());
										if($queryManObj){
											$prevFieldVal = $queryManObj->fetch(PDO::FETCH_OBJ)->name;
										}
									}
									if($fieldValue==0){$fieldValue = '';}
									elseif($fieldValue>0){
										$queryManObj = $this->db->query("SELECT name FROM manufacturer WHERE manufacturer_id = $fieldValue", array());
										if($queryManObj){
											$fieldValue = $queryManObj->fetch(PDO::FETCH_OBJ)->name;
										}
									}
								}
								$changed[$fieldName] = array($prevFieldVal, $fieldValue);
							}
						}						
					}
				}

				$oldregular_price = $oldave_cost = 0.00;
				$oldave_cost_is_percent = 0;
				$queryInvObj = $this->db->querypagination("SELECT * FROM inventory WHERE accounts_id = $accounts_id AND product_id = $product_id", array());
				if($queryInvObj){
					$inventory_id = $queryInvObj[0]['inventory_id'];
					$oldregular_price = $queryInvObj[0]['regular_price'];
					$oldave_cost = $queryInvObj[0]['ave_cost'];
					$oldave_cost_is_percent = $queryInvObj[0]['ave_cost_is_percent'];
					
					$update = $this->db->update('inventory', $inventorydata, $inventory_id);
					if($update){
						foreach($inventorydata as $fieldName=>$fieldValue){
							$prevFieldVal = $queryInvObj[0][$fieldName];
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
								$changed[$fieldName] = array($prevFieldVal, $fieldValue);
							}
						}
					}	
				}
				
				if(!empty($changed)){
					$moreInfo = array();
					$teData = array();
					$teData['created_on'] = date('Y-m-d H:i:s');
					$teData['accounts_id'] = $_SESSION["accounts_id"];
					$teData['user_id'] = $_SESSION["user_id"];
					$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'product');
					$teData['record_id'] = $product_id;
					$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
					$this->db->insert('track_edits', $teData);							
				}
				
				if($accounts_id==$prod_cat_man){
					$locationquery = $this->db->query("SELECT accounts_id FROM accounts WHERE location_of = $prod_cat_man AND status != 'SUSPENDED'", array());
					if($locationquery){
						while($oneUserRow = $locationquery->fetch(PDO::FETCH_OBJ)){
							$laccounts_id = $oneUserRow->accounts_id;
							$queryInvObj = $this->db->query("SELECT inventory_id, regular_price, ave_cost, ave_cost_is_percent FROM inventory WHERE accounts_id = $laccounts_id AND product_id = :product_id", array('product_id'=>$product_id),1);
							if($queryInvObj){
								$inventoryRow = $queryInvObj->fetch(PDO::FETCH_OBJ);
								$inventory_id = $inventoryRow->inventory_id;
								$loldregular_price = $inventoryRow->regular_price;
								$loldave_cost = $inventoryRow->ave_cost;
								$loldave_cost_is_percent = $inventoryRow->ave_cost_is_percent;
								$locUpdateData = array();
								if($oldregular_price==$loldregular_price){
									$locUpdateData['regular_price'] = $regular_price;
								}
								if($oldave_cost==$loldave_cost && $oldave_cost_is_percent == $loldave_cost_is_percent){
									$locUpdateData['ave_cost'] = $ave_cost;
									$locUpdateData['ave_cost_is_percent'] = $ave_cost_is_percent;
								}
								
								if(!empty($locUpdateData)){
									$this->db->update('inventory', $locUpdateData, $inventory_id);
								}
							}
						}
					}
				}

				$id = $product_id;
				$savemsg = 'update-success';
			}
		}
	
		$array = array( 'login'=>'','id'=>intval($id),'sku'=>$sku,'savemsg'=>$savemsg, 'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
    public function colourNameData(){
		$returnarray =array('Black',
							'Blue',
							'Bronze',
							'Brown',
							'Gold',
							'Gray',
							'Green',
							'Orange',
							'Pink',
							'Purple',
							'Red',
							'Silver',
							'White',
							'Yellow',
							'Other'
							);
				
		return $returnarray;
	}
	
	public function AJget_ManufOpt(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$manufacturer_id = intval($POST['manufacturer_id']??0);
		$ManufOpt = array();
		$sqlmanufacturer = "SELECT manufacturer_id, name FROM manufacturer WHERE accounts_id = $prod_cat_man AND (manufacturer_publish = 1 OR (manufacturer_publish = 0 AND manufacturer_id = :manufacturer_id)) ORDER BY name ASC";
		$manufacturerquery = $this->db->query($sqlmanufacturer, array('manufacturer_id'=>$manufacturer_id));
		if($manufacturerquery){
			while($onemanufacturerrow = $manufacturerquery->fetch(PDO::FETCH_OBJ)){
				$omanufacturer_id = $onemanufacturerrow->manufacturer_id;
				$omanufacturer_name = stripslashes(trim((string) $onemanufacturerrow->name));
				if($omanufacturer_name !=''){
					$ManufOpt[$omanufacturer_id] = $omanufacturer_name;
				}
			}			
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>json_encode($ManufOpt)));
	}
	
	public function prints(){
		$segment4name = $GLOBALS['segment4name'];		
		$segment5name = intval($GLOBALS['segment5name']);
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		
		$htmlStr = "";
		$itemObj = $this->db->query("SELECT product_id FROM product WHERE product_id = :product_id AND accounts_id = $prod_cat_man", array('product_id'=>$segment5name),1);
		if($itemObj){			
			$product_id = $itemObj->fetch(PDO::FETCH_OBJ)->product_id;		
			$Printing = new Printing($this->db);	
			if($segment4name=='barcode'){
				$htmlStr = $Printing->labelsInfo('product');
			}
			else if($segment4name == 'label_MoreInfo'){
				$jsonResponse = array();
				$jsonResponse['login'] = '';
				$commonInfo = $Printing->labelsInfo('product', 'commonInfo');
				$jsonResponse['commonInfo'] = $commonInfo;
				
				$CompanyName = $_SESSION["company_name"];
				$ProductName = $Price = $Barcode = $custom_data = '';
				if($product_id>0){
					$productObj = $this->db->query("SELECT p.*, manufacturer.name AS manufacture FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_id = :product_id", array('product_id'=>$product_id),1);
					if($productObj){
						$product_onerow = $productObj->fetch(PDO::FETCH_OBJ);
						
						$ProductName .= stripslashes(trim((string) $product_onerow->product_name));
						$manufacturer_name = stripslashes(trim((string) $product_onerow->manufacture));
						if($manufacturer_name !=''){$ProductName = stripslashes(trim($manufacturer_name.' '.$ProductName));}
						
						$inventoryObj = $this->db->query("SELECT regular_price FROM inventory WHERE accounts_id = $accounts_id AND product_id = $product_onerow->product_id", array());
						if($inventoryObj){
							$regular_price = $inventoryObj->fetch(PDO::FETCH_OBJ)->regular_price;
							if($regular_price>0){
								$Price .= $currency.number_format($regular_price,2);
							}
						}
						
						$Barcode = stripslashes(trim((string) $product_onerow->sku));
						$custom_data = $product_onerow->custom_data;
					}
				}
				
				$jsonResponse['CompanyName'] = $CompanyName;
				$jsonResponse['ProductName'] = $ProductName;
				$jsonResponse['Price'] = $Price;
				$jsonResponse['Barcode'] = $Barcode;
				$Common = new Common($this->db);
				$jsonResponse['customFieldsData'] = $Common->customFormFields('product', $custom_data);

				return json_encode($jsonResponse);
			}
		}
		return $htmlStr;
	}
	
	public function AJsave_PO(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$id = $po_number = 0;
		$savemsg = $message = '';
		$suppliers_id = intval($POST['supplier_id']??0);
		$cost = floatval($POST['cost']??0);
		$ordered_qty = floatval($POST['ordered_qty']??0);
		$ordered_qty_total = floatval($POST['ordered_qty_total']??0);
		$bulkimei = $POST['bulkimei']??'';
		$product_id = intval($POST['po_product_id']??0);
		$product_type = $POST['po_product_type']??'';

		$po_datetime = $last_updated = date('Y-m-d H:i:s');
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$status = 'Closed';

		$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory, ave_cost FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
		if($inventoryObj){
			$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);

			$current_inventory = $inventoryrow->current_inventory;
			$Common = new Common($this->db);
			$orderRepairShipQty = $Common->getOrderRepairShipQty($accounts_id, $product_id);
			$current_inventory += $orderRepairShipQty;
							
			$ave_cost = $inventoryrow->ave_cost;
			$currentproducttotalcost = round($current_inventory*$ave_cost,2);

			$poInsert = true;

			if($poInsert){
				//=============collect user last new Ticket no================//
				$po_number = 1;
				$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $accounts_id ORDER BY po_number DESC LIMIT 0, 1", array());
				if($poObj){
					$po_number = $poObj[0]['po_number']+1;
				}
				
				$poData = array();
				$poData['po_datetime'] = date('Y-m-d H:i:s');
				$poData['last_updated'] = date('Y-m-d H:i:s');
				$poData['po_number'] = $po_number;
				$lot_ref_no = $this->db->checkCharLen('po.lot_ref_no', '');
				$poData['lot_ref_no'] = $lot_ref_no;
				$poData['paid_by'] = '';
				$poData['supplier_id'] = intval($suppliers_id);
				$poData['date_expected'] = date('Y-m-d');
				$poData['return_po'] = 0;
				$status = $this->db->checkCharLen('po.status', $status);
				$poData['status'] = $status;
				$poData['accounts_id'] = $accounts_id;
				$poData['user_id'] = $user_id;
				$poData['tax_is_percent'] = 0;
				$poData['taxes'] = 0.000;
				$poData['shipping'] = 0.00;
				$suppliers_invoice_no = $this->db->checkCharLen('po.suppliers_invoice_no', '');
				$poData['suppliers_invoice_no'] = $suppliers_invoice_no;
				$poData['invoice_date'] = date('Y-m-d');
				$poData['date_paid'] = date('Y-m-d');
				$poData['transfer'] = 0;
				
				$po_id = $this->db->insert('po', $poData);
				if($po_id){
					$item_type = 'product';
					$item_type = $this->db->checkCharLen('po_items.item_type', $item_type);
					$poiData =array('created_on'=>date('Y-m-d H:i:s'),
									'user_id'=>$_SESSION["user_id"],
									'po_id'=>$po_id,
									'product_id'=>$product_id,
									'item_type'=>$item_type,
									'cost'=>round($cost,2),
									'ordered_qty'=>$ordered_qty,
									'received_qty'=>$ordered_qty);
					$po_items_id = $this->db->insert('po_items', $poiData);
					if($po_items_id){

						$new_current_inventory = $current_inventory+$ordered_qty;
						$new_ave_cost = $ave_cost;
						if($current_inventory <=0){
							$new_ave_cost = $cost;
						}
						elseif($new_current_inventory !=0){
							$newInvTotalCost = round($cost*$ordered_qty,2);
							$new_ave_cost = round(($currentproducttotalcost+$newInvTotalCost)/$new_current_inventory,2);
							
						}
						$this->db->update('inventory', array('current_inventory'=>($new_current_inventory-$orderRepairShipQty), 'ave_cost' => $new_ave_cost), $inventoryrow->inventory_id);
						$savemsg = 'saved';
					}

					$id = $po_id;
				}
				else{
					$savemsg = 'errorAddPO';
				}
			}
		}
		else{
			$savemsg = 'noInventory';
		}

		$array = array( 'login'=>'', 'id'=>$id,'po_number'=>$po_number,
			'savemsg'=>$savemsg,
			'message'=>$message);
		return json_encode($array);
    }
	
	public function AJget_ProductsDescPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$descData = array();
		$descData['login'] = '';
		$product_id = intval($POST['product_id']??0);
		$description = '';
		if($product_id>0){
			$queryObj = $this->db->query("SELECT description FROM product WHERE product_id = $product_id", array());
			if($queryObj){
				$description = $queryObj->fetch(PDO::FETCH_OBJ)->description;						
			}
		}
		$descData['description'] = $description;
		return json_encode($descData);
	}

	public function AJsave_ProductsDesc(){
		if(!isset($_SESSION["prod_cat_man"])){
			echo json_encode(array('login'=>'session_ended'));
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$id = 0;
			$savemsg = $message = '';
			$user_id = $_SESSION["user_id"]??0;
			$accounts_id = $_SESSION["accounts_id"]??0;
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$product_id = intval($POST['product_id']??0);
			$description = addslashes(trim((string) $POST['description']??''));

			$update = $this->db->update('product', array('description'=>$description), $product_id);
			if($update){
				$queryObj = $this->db->query("SELECT p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id AND p.accounts_id=$prod_cat_man", array());
				if($queryObj){
					$productRow = $queryObj->fetch(PDO::FETCH_OBJ);
					
					$activity_feed_title = $this->db->translate('Product was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Products/view/$product_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $accounts_id,
									'user_id' => $user_id,
									'activity_feed_title' => $activity_feed_title,
									'activity_feed_name' => stripslashes(trim($productRow->manufacture.' '.$productRow->product_name.' '.$productRow->colour_name.' '.$productRow->storage.' '.$productRow->physical_condition_name)),
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "product",
									'uri_table_field_name' =>"product_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
				}
				$savemsg = 'update-success';
			}
			
			$array = array( 'login'=>'', 'id'=>$id, 'savemsg'=>$savemsg);
			return json_encode($array);
		}
	}

	public function AJget_ProductsPricePopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$priceData = array();
		$priceData['login'] = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$product_prices_id = intval($POST['product_prices_id']??0);
		
		$price_type = $type_match = '';
		if(!isset($_SESSION["accounts_id"])){
			$priceData['login'] = 'session_ended';
		}
		$priceData['is_percent'] = 1;
		$priceData['price'] = 0.00;
		$priceData['start_date'] = '';
		$priceData['end_date'] = '';
		
		if($product_prices_id>0){
			$ppObj = $this->db->query("SELECT * FROM product_prices WHERE accounts_id = $accounts_id AND product_prices_id = :product_prices_id", array('product_prices_id'=>$product_prices_id),1);
			if($ppObj){
				$oneRow = $ppObj->fetch(PDO::FETCH_OBJ);
				$price_type = trim((string) $oneRow->price_type);
				$type_match = trim((string) $oneRow->type_match);
				$priceData['is_percent'] = intval($oneRow->is_percent);
				$priceData['price'] = round($oneRow->price,2);
				$priceData['start_date'] = $oneRow->start_date;
				$priceData['end_date'] = $oneRow->end_date;
			}
		}
		$priceData['price_type'] = $price_type;
		$priceData['type_match'] = $type_match;
		
		$priceTypeOpt = array();
		$query = array('Customer Type', 'Sale', 'Quantity');
		if($query){
			foreach($query as $label){
				$priceTypeOpt[] = $label;
			}
		}
		$priceData['price_typeOptions'] = $priceTypeOpt;
		
		$customer_type = '';
		if(strcmp($price_type, 'Customer Type')==0){
			$customer_type = $type_match;
		}
		
		$customerTypeOpt = array();
		if(isset($_SESSION["accounts_id"])){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$sql = "SELECT name FROM customer_type WHERE accounts_id = $prod_cat_man AND (customer_type_publish = 1 OR (name = :name AND customer_type_publish = 0)) ORDER BY name ASC";
			$query = $this->db->query($sql, array('name'=>$customer_type));
			if($query){
				while($onerow = $query->fetch(PDO::FETCH_OBJ)){
					$label = stripslashes(trim((string) $onerow->name));
					if($label !=''){
						$customerTypeOpt[] = $label;
					}
				}
			}
		}
		
		$priceData['customer_typeOptions'] = $customerTypeOpt;
		return json_encode($priceData);
	}
	
	public function AJsave_ProductsPrice(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$product_prices_id = intval($POST['product_prices_id']??0);
		$price_type = $POST['price_type']??'';
		$product_id = intval($POST['product_id']??0);
		$type_match = '';
		if($price_type=='Customer Type'){$type_match = trim((string) $POST['customer_type']??'');}
		else if($price_type=='Quantity'){$type_match = trim((string) $POST['type_match']??'');}
		$is_percent = intval($POST['is_percent']??0);
		$price = floatval($POST['price']??0);
		$start_date = trim((string) $POST['start_date']??'1000-01-01');
		if(!in_array($start_date, array('', '1000-01-01'))){$start_date = date('Y-m-d', strtotime($start_date));}
		else{$start_date = '1000-01-01';}
		
		$end_date = trim((string) $POST['end_date']??'1000-01-01');
		if(!in_array($end_date, array('', '1000-01-01'))){$end_date = date('Y-m-d', strtotime($end_date));}
		else{$end_date = '1000-01-01';}
		$price_type = $this->db->checkCharLen('product_prices.price_type', $price_type);
		$type_match = $this->db->checkCharLen('product_prices.type_match', $type_match);
										
		$conditionarray = array();
		$conditionarray['accounts_id'] = $accounts_id;
		$conditionarray['user_id'] = $user_id;
		$conditionarray['product_id'] = $product_id;
		$conditionarray['price_type'] = $price_type;			
		$conditionarray['type_match'] = $type_match;
		$conditionarray['is_percent'] = $is_percent;
		$conditionarray['price'] = $price;			
		$conditionarray['start_date'] = $start_date;
		$conditionarray['end_date'] = $end_date;

		$bindData = array('product_id'=>$product_id, 'price_type'=>$price_type, 'type_match'=>$type_match, 'start_date'=>$start_date, 'end_date'=>$end_date);
		$duplCheckSql = "SELECT COUNT(product_prices_id) AS totalrows FROM product_prices WHERE accounts_id = $accounts_id AND product_id = :product_id AND price_type = :price_type AND type_match = :type_match AND start_date = :start_date AND end_date = :end_date";
		if($product_prices_id>0){
			$duplCheckSql .= " AND product_prices_id != :product_prices_id";
			$bindData['product_prices_id'] = $product_prices_id;
		}
		$totalrows = 0;
		$queryObj = $this->db->query($duplCheckSql, $bindData);
		if($queryObj){
			$totalrows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		if($totalrows>0){
			$savemsg = 'priceInfoExist';
		}
		else{
			$prices_enabled = 0;
			if($product_prices_id==0){			
			
				$conditionarray['created_on'] = date('Y-m-d H:i:s');

				$product_prices_id = $this->db->insert('product_prices', $conditionarray);
				if($product_prices_id){
					$prices_enabled = 1;

					$is_percentstr = $currency.$price;
					if($is_percent>0){$is_percentstr = $price.'%';}
					$activity_feed_name = stripslashes(trim((string) "$price_type, $type_match, $is_percentstr"));
					
					$activity_feed_title = $this->db->translate('Product price has been added');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Products/view/$product_id";
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
					$savemsg = 'errorAddingPrice';
				}
			}
			else{
				$oldPPObj = $this->db->query("SELECT * FROM product_prices WHERE accounts_id = $accounts_id AND product_prices_id = :product_prices_id", array('product_prices_id'=>$product_prices_id),1);
				if($oldPPObj){
					$oldPPRow = $oldPPObj->fetch(PDO::FETCH_OBJ);
				}
				$update = $this->db->update('product_prices', $conditionarray, $product_prices_id);
				if($update && $oldPPObj){
					$prices_enabled = 1;

					$oldprice_type = $oldPPRow->price_type;
					$oldtype_match = $oldPPRow->type_match;
					$oldstart_date = $oldPPRow->start_date;
					$oldend_date = $oldPPRow->end_date;
					$oldis_percent = $oldPPRow->is_percent;
					$oldprice = $oldPPRow->price;
					if($oldprice_type==$price_type && $oldtype_match == $type_match && $oldstart_date == $start_date && $oldend_date==$end_date && $accounts_id==$prod_cat_man && ($oldis_percent != $is_percent || $oldprice != $price)){
						$locationquery = $this->db->query("SELECT accounts_id FROM accounts WHERE location_of = $prod_cat_man AND status != 'SUSPENDED'", array());
						if($locationquery){
							while($oneUserRow = $locationquery->fetch(PDO::FETCH_OBJ)){
								$laccounts_id = $oneUserRow->accounts_id;
								$queryObj = $this->db->query("SELECT COUNT(product_prices_id) AS totalrows FROM product_prices WHERE accounts_id = $accounts_id AND product_id = :product_id AND price_type = :price_type AND type_match = :type_match AND start_date = :start_date AND end_date = :end_date AND product_prices_id != :product_prices_id", array('product_id'=>$product_id, 'price_type'=>$price_type, 'type_match'=>$type_match, 'start_date'=>$start_date, 'end_date'=>$end_date, 'product_prices_id'=>$product_prices_id));
								if($queryObj){
									$totalrows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
								}
								$locPPObj = $this->db->query("SELECT product_prices_id, price FROM product_prices WHERE accounts_id = $laccounts_id AND product_id = :product_id AND price_type = :price_type AND type_match = :type_match AND start_date = :start_date AND end_date = :end_date", array('product_id'=>$product_id, 'price_type'=>$oldprice_type, 'type_match'=>$oldtype_match, 'start_date'=>$oldstart_date, 'end_date'=>$oldend_date));
								if($locPPObj){
									$locPPRow = $locPPObj->fetch(PDO::FETCH_OBJ);
									$loldproduct_prices_id = $locPPRow->product_prices_id;
									$loldprice = $locPPRow->price;

									if($oldprice==$loldprice){
										$this->db->update('product_prices', array('price'=>$price), $loldproduct_prices_id);
									}
								}
							}
						}
					}
				}
			}

			if($product_prices_id>0){
				$this->checkLocationProdPrice($conditionarray);

				$queryInvObj = $this->db->query("SELECT inventory_id FROM inventory WHERE product_id = :product_id AND accounts_id = $accounts_id", array('product_id'=>$product_id),1);
				if($queryInvObj){
					$inventory_id = $queryInvObj->fetch(PDO::FETCH_OBJ)->inventory_id;
					if($inventory_id>0){
						$this->db->update('inventory', array('prices_enabled'=>$prices_enabled), $inventory_id);
					}
				}
			}
		}

		$array = array( 'login'=>'', 'product_prices_id'=>$product_prices_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
	}

	function checkLocationProdPrice($prodPriceData){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		if($prod_cat_man==$accounts_id){

			$queryObj = $this->db->query("SELECT accounts.accounts_id, user.user_id FROM accounts, user WHERE accounts.location_of = :location_of AND accounts.status != 'SUSPENDED' AND user.is_admin = 1 AND accounts.accounts_id = user.accounts_id", array('location_of'=>$accounts_id),1);
			if($queryObj){
				while($onerow=$queryObj->fetch(PDO::FETCH_OBJ)){
					$subaccounts_id = intval($onerow->accounts_id);
					$subuser_id = intval($onerow->user_id);
					//$this->db->writeIntoLog('subaccounts_id : '.$subaccounts_id);
					$bindData = array('product_id'=>$prodPriceData['product_id'], 'price_type'=>$prodPriceData['price_type'], 'type_match'=>$prodPriceData['type_match'], 'start_date'=>$prodPriceData['start_date'], 'end_date'=>$prodPriceData['end_date']);
					$duplCheckSql = "SELECT COUNT(product_prices_id) AS totalrows FROM product_prices WHERE accounts_id = $subaccounts_id AND product_id = :product_id AND price_type = :price_type AND type_match = :type_match AND start_date = :start_date AND end_date = :end_date";
					$totalrows = 0;
					$queryObj = $this->db->query($duplCheckSql, $bindData);
					if($queryObj){
						$totalrows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
					}
					if($totalrows == 0){
						$conditionarray = $prodPriceData;
						$conditionarray['created_on'] = date('Y-m-d H:i:s');
						$conditionarray['accounts_id'] = $subaccounts_id;
						$conditionarray['user_id'] = $subuser_id;
						$product_prices_id = $this->db->insert('product_prices', $conditionarray);
					}
				}						
			}
		}
	}

	//========================ASync========================//	
	public function AJgetPage(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$segment4name = $GLOBALS['segment4name'];
		$sdata_type = $POST['sdata_type']??'All';
		$smanufacturer_id = $POST['smanufacturer_id']??'';
		$scategory_id = $POST['scategory_id']??'';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->manufacturer_id = $smanufacturer_id;
		$this->category_id = $scategory_id;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['manOpt'] = $this->manOpt;
			$jsonResponse['catOpt'] = $this->catOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_view_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$product_id = intval($POST['product_id']??0);
		$productObj = $this->db->query("SELECT * FROM product WHERE product_id = :product_id AND accounts_id = $prod_cat_man", array('product_id'=>$product_id),1);
		if($productObj){
			$productarray = $productObj->fetch(PDO::FETCH_OBJ);
			$product_id = $productarray->product_id;
			$jsonResponse['product_publish'] = intval($productarray->product_publish);
			$Carts = new Carts($this->db);

			$list_filters = array();
			if(isset($_SESSION["list_filters"])){
				$list_filters = $_SESSION["list_filters"];
			}
		
			$shistory_type = $list_filters['shistory_type']??'';
			$product_type = $productarray->product_type;
			$jsonResponse['product_type'] = $product_type;
			
			$storage = $productarray->storage;
			$jsonResponse['storage'] = $storage;
			
			$inventory_id = 0;
			$jsonResponse['inventoryObj'] = false;
			$jsonResponse['prices_enabled'] = 0;
			
			$minimum_price = 0.00;
			$inventoryObj = $this->db->query("SELECT * FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
			if($inventoryObj){
				$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
				$inventory_id = $inventoryrow->inventory_id;
				$minimum_price = $inventoryrow->minimum_price;
				$jsonResponse['inventoryObj'] = true;
				$jsonResponse['prices_enabled'] = intval($inventoryrow->prices_enabled);
			}
			$jsonResponse['minimum_price'] = $minimum_price;
			
			$sku = $productarray->sku;
			$jsonResponse['sku'] = $sku;
			
			$category_id = $productarray->category_id;
			$category_name = '';
			if($category_id>0){
				$categoryObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $category_id AND accounts_id = $prod_cat_man", array());
				if($categoryObj){
					$category_name = $categoryObj->fetch(PDO::FETCH_OBJ)->category_name;
				}
			}
			$jsonResponse['category_name'] = $category_name;
			
			$manufacturer_id = $productarray->manufacturer_id;
			$manufacture = '';
			if($manufacturer_id>0){
				$manObj = $this->db->query("SELECT name FROM manufacturer WHERE manufacturer_id = $manufacturer_id AND accounts_id = $prod_cat_man", array());
				if($manObj){
					$manufacture = $manObj->fetch(PDO::FETCH_OBJ)->name;
				}
			}
			
			$product_name = stripslashes(trim((string) "$manufacture $productarray->product_name"));
			
			$colour_name = $productarray->colour_name;
			if(!empty($colour_name)){$product_name .= ' '.$colour_name;}
			
			$storage = $productarray->storage;
			if(!empty($storage)){$product_name .= ' '.$storage;}
			
			$physical_condition_name = $productarray->physical_condition_name;
			if(!empty($physical_condition_name)){$product_name .= ' '.$physical_condition_name;}
			
			$jsonResponse['product_name'] = $product_name;
			
			$category_id = $productarray->category_id;
			$allow_backorder = $current_inventory = $low_inventory_alert = 0;
			
			$manage_inventory_count = intval($productarray->manage_inventory_count);
			if($manage_inventory_count>0 && $inventoryObj){
				$current_inventory = $inventoryrow->current_inventory;						
				$allow_backorder = $productarray->allow_backorder;
				$low_inventory_alert = $inventoryrow->low_inventory_alert;
			}
			$jsonResponse['allow_backorder'] = intval($allow_backorder);
			$jsonResponse['manage_inventory_count'] = $manage_inventory_count;
			
			$ave_cost = 0.00;
			if($inventoryObj){
				$ave_cost = round($inventoryrow->ave_cost,2);
			}
			
			$jsonResponse['current_inventory'] = floatval($current_inventory);

			$prodImg = '';
			$defaultImageSRC = '';
			$category_namepic = str_replace('/', '-', $category_name);
			if($category_namepic=='Accessories' || $category_namepic=='Parts' || $category_namepic=='Batteries' || $category_namepic=='Labor-Services' || $category_name=='Unlocking'){
				$prodImg = $category_namepic.'.png';
				$defaultImageSRC = '/assets/images/'.$category_namepic.'.png';
			}
			else{
				$prodImg = 'default.png';
				$defaultImageSRC = '/assets/images/default.png';
			}
			$jsonResponse['defaultImageSRC'] = $defaultImageSRC;

			$prodImgUrl = '';
			$filePath = "./assets/accounts/a_$prod_cat_man/prod_$product_id".'_';
			$pics = glob($filePath."*.jpg");
			if($pics){
				foreach($pics as $onePicture){
					$prodImg = str_replace("./assets/accounts/a_$prod_cat_man/", '', $onePicture);
					$prodImgUrl = str_replace('./', '/', $onePicture);
				}
			}
			else{
				$prodImgUrl = $defaultImageSRC;
			}
			$jsonResponse['prodImgUrl'] = $prodImgUrl;

			$jsonResponse['allowed'] = $_SESSION["allowed"];
			$regular_price = 0.00;
			$ave_cost_is_percent = 0;
			if($inventoryObj){
				$regular_price = round($inventoryrow->regular_price,2);					
				$ave_cost_is_percent = intval($inventoryrow->ave_cost_is_percent);
			}
			$jsonResponse['regular_price'] = $regular_price;
			$jsonResponse['ave_cost_is_percent'] = $ave_cost_is_percent;
			$jsonResponse['ave_cost'] = $ave_cost;
			$jsonResponse['taxable'] = intval($productarray->taxable);
			$jsonResponse['require_serial_no'] = intval($productarray->require_serial_no);

			$jsonResponse['ave_cost'] = $ave_cost;
			$jsonResponse['low_inventory_alert'] = intval($low_inventory_alert);

			$NeedHaveOnPOInfo = array();
			$NeedHaveOnPOInfo['product_type'] = $product_type;
			$NeedHaveOnPOInfo['manage_inventory_count'] = intval($manage_inventory_count);
			$NeedHaveOnPOInfo['need'] = 0;
			$NeedHaveOnPOInfo['have'] = 0;
			$NeedHaveOnPOInfo['onPO'] = 0;
			if(in_array($product_type, array('Standard')) && $manage_inventory_count>0){
				$NHPInfo = $Carts->NeedHaveOnPO($product_id, $product_type, 1);
				$NeedHaveOnPOInfo['need'] = $NHPInfo[0];
				$NeedHaveOnPOInfo['have'] = $NHPInfo[1];
				$NeedHaveOnPOInfo['onPO'] = $NHPInfo[2];
			}
			
			$jsonResponse['NeedHaveOnPO'] = $NeedHaveOnPOInfo;

			$supOpt = array();
			$supplierssql = "SELECT company, email, suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND company !='' AND suppliers_publish = 1 ORDER BY company ASC, email ASC";
			$suppliersquery = $this->db->query($supplierssql, array());
			if($suppliersquery){
				while($onerow=$suppliersquery->fetch(PDO::FETCH_OBJ)){
					$company = stripslashes($onerow->company);																			
					if($onerow->email !='')
						$company .= " ($onerow->email)";				
					
					$supOpt[$onerow->suppliers_id] = $company;
				}
			}
			
			$jsonResponse['supOpt'] = $supOpt;

			$viewLocationInvInfo = array();
			if($manage_inventory_count>0 && !in_array($product_type, array('Labor/Services'))){
				$strextra = "SELECT accounts_id, company_subdomain FROM accounts WHERE (accounts_id = $prod_cat_man OR location_of = $prod_cat_man) AND accounts_id NOT IN ($accounts_id) AND status != 'SUSPENDED'";
				$query = $this->db->query($strextra, array());				
				if($query){
					while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
						
						$user_id = $oneRow->accounts_id;
						$company_subdomain = stripslashes($oneRow->company_subdomain);					
						$current_inventory = 0;
						
						if($manage_inventory_count>0){
							$inventoryObj2 = $this->db->query("SELECT current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $user_id", array());
							if($inventoryObj2){
								$current_inventory = $inventoryObj2->fetch(PDO::FETCH_OBJ)->current_inventory;
							}
						}
						
						$viewLocationInvInfo[$company_subdomain] = $current_inventory;
					}
				}
			}

			$jsonResponse['viewLocationInvInfo'] = $viewLocationInvInfo;
			$Common = new Common($this->db);
			$cusDataInfo = $Common->customViewInfo('product', $productarray->custom_data);
			$jsonResponse['customFields'] = $cusDataInfo[0];
			$jsonResponse['viewCustomInfo'] = $cusDataInfo[1];	

			$prodPer = $prodPer2 = 1;
			if(!empty($_SESSION["allowed"]) && array_key_exists(5, $_SESSION["allowed"]) && in_array('cne', $_SESSION["allowed"][5])) {
				$prodPer = 0;
			}
			if(!empty($_SESSION["allowed"]) && array_key_exists(5, $_SESSION["allowed"]) && in_array('cnc', $_SESSION["allowed"][5])) {
				$prodPer2 = 0;
			}
			$costPer = 1;
			if(!empty($_SESSION["allowed"]) && !array_key_exists(6, $_SESSION["allowed"])) {
				$costPer = 0;
			}
			$jsonResponse['prodPer'] = $prodPer;
			$jsonResponse['prodPer2'] = $prodPer2;
			$jsonResponse['costPer'] = intval($costPer);

			$productAveCost = array();
			$aveCostPermission = 0;
			if(($_SESSION["admin_id"] >0 || $accounts_id<=6) && $product_type =='Standard' && $manage_inventory_count>0){
				$productAveCost = $Common->productAvgCost($accounts_id, $product_id, 0);
				$aveCostPermission = 1;
			}			
			$jsonResponse['aveCostPermission'] = $aveCostPermission;
			$jsonResponse['productAveCost'] = $productAveCost;
			$isDesYes = 0;
			if(!empty($productarray->description)){$isDesYes = 1;}
			$jsonResponse['isDesYes'] = $isDesYes;

			$productPrices = array();
			$strextra = "SELECT * FROM product_prices WHERE accounts_id = $accounts_id AND product_id = $product_id AND product_prices_publish = 1";
			$query = $this->db->query($strextra, array());						
			if($query){
				while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
					$product_prices_id = $oneRow->product_prices_id;
					$price_type = $oneRow->price_type;
					$type_match = $oneRow->type_match;
					
					$is_percent = intval($oneRow->is_percent);
					$price = round($oneRow->price,2);
					$datestr = '';
					if(!in_array($oneRow->start_date, array('0000-00-00', '1000-01-01'))){
						$datestr .= $oneRow->start_date;
					}
					if(!in_array($oneRow->end_date, array('0000-00-00', '1000-01-01'))){
						$datestr .= " - $oneRow->end_date";
					}
					
					$productPrices[] = array($product_prices_id, $price_type, $type_match, $is_percent, $datestr, $price);
				}
			}
			$jsonResponse['productPrices'] = $productPrices;

			$actFeeTitOpt = array();
			$Sql = "SELECT activity_feed_title AS afTitle FROM activity_feed 
			WHERE accounts_id = $accounts_id AND uri_table_name = 'product' AND activity_feed_link = CONCAT('/Products/view/', :sproduct_id) 
			UNION ALL 
				SELECT 'Product Created' AS afTitle FROM product 
				WHERE product_id = :sproduct_id AND accounts_id = $prod_cat_man 
			UNION ALL 
				SELECT (Case When pos.pos_type = 'Order' AND pos.order_status = 1 Then 'Order Created' 
					When pos.pos_type = 'Repairs' AND pos.order_status = 1 Then 'Repair Created' 
					Else 'Sales Invoice Created' End) AS afTitle FROM pos, pos_cart 
				WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = :sproduct_id AND pos.pos_id = pos_cart.pos_id AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status in (1,2))) 
			UNION ALL 
				SELECT 'Purchase Order Created' AS afTitle FROM po, po_items 
				WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 0 AND po.po_id = po_items.po_id AND po.po_publish = 1 
			UNION ALL 
				SELECT 'Inventory Transfer To' AS afTitle FROM po, po_items 
				WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 1 AND po.po_id = po_items.po_id AND po.po_publish = 1 
			UNION ALL 
				SELECT 'Inventory Transfer From' AS afTitle FROM po, po_items 
				WHERE po.accounts_id = $accounts_id AND po_items.product_id = :sproduct_id AND po.transfer = 2 AND po.po_id = po_items.po_id AND po.po_publish = 1 
			UNION ALL 
				SELECT 'Track Edits' AS afTitle FROM track_edits 
				WHERE accounts_id = $accounts_id AND record_for = 'product' AND record_id = :sproduct_id 
			UNION ALL SELECT 'Notes Created' AS afTitle FROM notes 
				WHERE accounts_id = $accounts_id AND note_for = 'product' AND table_id = :sproduct_id";
			$tableObj = $this->db->query($Sql, array('sproduct_id'=>$product_id));
			if($tableObj){
				$actFeeTitOpts = array();
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$actFeeTitOpts[$oneRow->afTitle] = '';
				}
				ksort($actFeeTitOpts);
				$actFeeTitOpt = array_keys($actFeeTitOpts);
			}
			$jsonResponse['actFeeTitOpt'] = $actFeeTitOpt;
		}
		else{
			$jsonResponse['login'] = 'Products/lists/';
		}
		return json_encode($jsonResponse);
	}
	
	public function AJgetHPage(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$segment4name = $GLOBALS['segment4name'];
		$sproduct_id = intval($POST['sproduct_id']??0);
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->product_id = $sproduct_id;
		$this->history_type = $shistory_type;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;			
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResponse['tableRows'] = $this->loadHTableRows();
		
		return json_encode($jsonResponse);
	}
	
    public function AJ_showProductRow(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sku = $POST['sku']??'';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$returnArray = array();
		$returnArray['login'] = '';
		$returnArray['returnStr'] = "notFound";

		$sql = "SELECT p.*, manufacturer.name AS manufacture FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_publish = 1 AND p.product_type != 'Live Stocks' AND p.sku = :sku ORDER BY p.sku ASC LIMIT 0, 1";
		$query = $this->db->querypagination($sql, array('sku'=>addslashes($sku)));
		if($query){
			foreach($query as $productrow){
				$returnArray['returnStr'] = 'Ok';
				$returnArray['product_id'] = $product_id = intval($productrow['product_id']);
				$returnArray['manufacture'] = stripslashes(trim((string) $productrow['manufacture']));
				$returnArray['product_name'] = stripslashes(trim((string) $productrow['product_name']));
				$returnArray['sku'] = stripslashes(trim((string) $productrow['sku']));
				$current_inventory = 0;
				$inventoryObj = $this->db->query("SELECT current_inventory FROM inventory WHERE accounts_id = $accounts_id AND product_id = $product_id", array());
				if($inventoryObj){
					$current_inventory = $inventoryObj->fetch(PDO::FETCH_OBJ)->current_inventory;
				}
				$returnArray['current_inventory'] = $current_inventory;
			}
		}
		return json_encode($returnArray);
    }
	  
    public function AJsave_adjust_inventory(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$created_on = $last_updated = date('Y-m-d H:i:s');
		$product_id = intval($POST['product_id']??0);
		$existing_inventory = $POST['existing_inventory']??0;
		$new_inventory = $POST['new_inventory']??0;
		$total_inventory = $POST['total_inventory']??0;
		$adjust_type = $POST['adjust_type']??'Add';
		if(strcmp($adjust_type, 'Subtract')==0){
			$new_inventory = $new_inventory*(-1);
		}
		$inventory_id = $prevInventory = 0;
		$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE accounts_id = $accounts_id AND product_id = :product_id", array('product_id'=>$product_id),1);
		if($inventoryObj){
			$invRow = $inventoryObj->fetch(PDO::FETCH_OBJ);
			$inventory_id =  $invRow->inventory_id;
			$prevInventory =  $invRow->current_inventory;
		}
		
		if($inventory_id>0){
			$total_inventory = $new_inventory+$prevInventory;
			$updateproduct = $this->db->update('inventory', array('current_inventory'=>$total_inventory), $inventory_id);
			if($updateproduct){
				$returnStr = 'Ok';
				$sku = '';
				$productObj = $this->db->query("SELECT sku FROM product WHERE product_id = :product_id", array('product_id'=>$product_id),1);
				if($productObj){
					$sku = $productObj->fetch(PDO::FETCH_OBJ)->sku;
				}
				$note_for = $this->db->checkCharLen('notes.note_for', 'product');
				$noteData=array('table_id'=> $product_id,
								'note_for'=> $note_for,
								'created_on'=> date('Y-m-d H:i:s'),
								'last_updated'=> date('Y-m-d H:i:s'),
								'accounts_id'=> $_SESSION["accounts_id"],
								'user_id'=> $_SESSION["user_id"],
								'note'=> "$new_inventory inventory has been adjusted $sku",
								'publics'=>0);
				$notes_id = $this->db->insert('notes', $noteData);
				
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'current_inventory'=>$total_inventory));
    }
	
    public function AJ_product_archive(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnmsg = "";
		$savemsg = "";
		$sku = addslashes(trim((string) $POST['sku']??''));
		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$returnval = 0;
		
		$sql = "SELECT * FROM product WHERE accounts_id = $prod_cat_man AND sku = :sku AND product_publish = 1 ORDER BY sku ASC";
		$query = $this->db->query($sql, array('sku'=>$sku));
		if($query){
			$productrow = $query->fetch(PDO::FETCH_OBJ);
			$product_id = $productrow->product_id;
			$product_name = $productrow->product_name;

			$colour_name = $productrow->colour_name;
			if($colour_name !=''){$product_name .= ' '.$colour_name;}

			$storage = $productrow->storage;
			if($storage !=''){$product_name .= ' '.$storage;}

			$physical_condition_name = $productrow->physical_condition_name;
			if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

			$product_id = $productrow->product_id;
			$product_type = $productrow->product_type;
			$countOrders = $countRepairs = $countInventory = $countPO = $locationCount = 0;
			$accSql = "SELECT accounts_id FROM accounts WHERE (accounts_id = $prod_cat_man OR location_of = $prod_cat_man) AND status != 'SUSPENDED'";
			$query = $this->db->query($accSql, array());
			if($query){
				while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
					$accountsId = $oneRow->accounts_id;
					$locationCount++;
					$ordersArray = $this->db->querypagination("SELECT pos.pos_id FROM pos, pos_cart WHERE pos_cart.item_id = $product_id AND pos.accounts_id = $accountsId AND pos.pos_type = 'Order' AND pos.order_status=1 AND pos.pos_id = pos_cart.pos_id GROUP BY pos.pos_id", array());
					if($ordersArray){
						$countOrders += count($ordersArray);
					}
					
					$repairsArray = $this->db->querypagination("SELECT pos.pos_id FROM pos, pos_cart, repairs WHERE pos_cart.item_id = $product_id AND repairs.status NOT IN ('Invoiced', 'Cancelled') AND pos.accounts_id = $accountsId AND pos.pos_type = 'Repairs' AND pos.order_status=1 AND pos.pos_id = pos_cart.pos_id AND pos.pos_id = repairs.pos_id GROUP BY pos.pos_id", array());
					if($repairsArray){
						$countRepairs += count($repairsArray);
					}
					
					if($product_type=='Standard'){
						$inventoryObj = $this->db->query("SELECT current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accountsId", array());
						if($inventoryObj){
							$countInventory += $inventoryObj->fetch(PDO::FETCH_OBJ)->current_inventory;
						}
					}
					else{
						$itemObj = $this->db->query("SELECT count(item_id) as counttotalrows FROM item WHERE product_id = $product_id AND accounts_id = $accountsId AND item_publish = 1 AND in_inventory = 1", array());
						if($itemObj){
							$countInventory += $itemObj->fetch(PDO::FETCH_OBJ)->counttotalrows;
						}
					}
					$poSql = "SELECT po.po_id FROM po, po_items WHERE po_items.product_id = $product_id AND po.accounts_id = $accountsId AND po.status = 'Open' AND po.po_id = po_items.po_id GROUP BY po.po_id";
					$poArray = $this->db->querypagination($poSql, array());
					if($poArray){
						$countPO += count($poArray);
					}
				}
			}
			
			if($countOrders >0 || $countRepairs >0 || $countInventory >0 || $countPO>0){
				$savemsg = 'reasonOfNotRemoving';
				$returnmsg = "<p>You could not remove this product because:</p><ul>";
				
				$onAccount = "into your account.";
				if($locationCount>1){$onAccount = "into your account/sub accounts.";}

				if($countInventory >0){$returnmsg .= "<li>There are $countInventory Inventory  $onAccount</li>";}
				if($countOrders >0){$returnmsg .= "<li>There are $countOrders Open Order $onAccount</li>";}
				if($countRepairs >0){$returnmsg .= "<li>There are $countRepairs Open Repair $onAccount</li>";}
				if($countPO >0){$returnmsg .= "<li>There are $countPO Open PO $onAccount</li>";}
				$returnmsg .= "</ul>";
			}
			else{
				if($countInventory<0){
					$sku = $productrow->sku;
					
					$note_for = $this->db->checkCharLen('notes.note_for', 'product');
					$noteData=array('table_id'=> $product_id,
									'note_for'=> $note_for,
									'created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> date('Y-m-d H:i:s'),
									'accounts_id'=> $_SESSION["accounts_id"],
									'user_id'=> $_SESSION["user_id"],
									'note'=> "$countInventory inventory has been adjusted $sku",
									'publics'=>0);
					$notes_id = $this->db->insert('notes', $noteData);
					
					$inventoryObj = $this->db->query("SELECT inventory_id FROM inventory WHERE product_id = $product_id AND accounts_id = $accountsId", array());
					if($inventoryObj){
						$inventory_id = $inventoryObj->fetch(PDO::FETCH_OBJ)->inventory_id;
						$this->db->update('inventory', array('current_inventory'=>0), $inventory_id);
					}
				}
				
				$updatetable = $this->db->update('product', array('product_publish'=>0), $product_id);
				if($updatetable){
					$filePath = "./assets/accounts/a_$accounts_id/prod_$product_id".'_';
					$pics = glob($filePath."*.jpg");
					if($pics){
						foreach($pics as $onePicture){
							if (file_exists($onePicture))
								unlink($onePicture);
						}
					}
					
					$activity_feed_title = 'Product archived';
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Products/view/$product_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' => $activity_feed_title,
									'activity_feed_name' => $product_name,
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "product",
									'uri_table_field_name' =>"product_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
					$returnmsg = 'archive-success';
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnmsg, 'savemsg'=>$savemsg));
    }
	
	//================Joining Class==============//
	public function AJget_SuppliersPopup(){
		$Suppliers = new Suppliers($this->db);
		return $Suppliers->AJget_SuppliersPopup();
	}
	
	public function AJsave_Suppliers(){
		$Suppliers = new Suppliers($this->db);
		return $Suppliers->AJsave_Suppliers();
	}
}
?>