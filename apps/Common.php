<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Common{
	protected $db;
	public function __construct($db){$this->db = $db;}
	
	//==============Commonly Used on Multiple Places================//		
	public function calAvailCr($customers_id, $credit_limit, $isArray=0){
	
		$returnstr = 0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$totalUsedCard = 0;
		$sqlquery = "SELECT SUM(CASE WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS taxableTotal, 
					SUM(CASE WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS nonTaxableTotal,
					pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2, pos.pos_id 
					FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos.is_due>0 AND pos.pos_id = pos_cart.pos_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' or (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) 
					 AND pos.customer_id = :customers_id GROUP BY pos.pos_id ORDER BY pos.sales_datetime DESC, pos.invoice_no DESC";
		$query = $this->db->querypagination($sqlquery, array('customers_id'=>$customers_id),1);
		if($query){
			foreach($query as $oneRow){
				$pos_id = $oneRow['pos_id'];
				$taxable_total = $oneRow['taxableTotal'];
				$totalnontaxable = $oneRow['nonTaxableTotal'];
				$taxes_total1 = $this->calculateTax($taxable_total, $oneRow['taxes_percentage1'], $oneRow['tax_inclusive1']);
				$taxes_total2 = $this->calculateTax($taxable_total, $oneRow['taxes_percentage2'], $oneRow['tax_inclusive2']);

				$tax_inclusive1 = $oneRow['tax_inclusive1'];
				$tax_inclusive2 = $oneRow['tax_inclusive2'];
					
				$taxestotal = $taxes_total1+$taxes_total2;
				$grand_total = $taxable_total+$taxestotal+$totalnontaxable;
				if($tax_inclusive1>0){
					$grand_total -= $taxes_total1;
				}
				if($tax_inclusive2>0){
					$grand_total -= $taxes_total2;
				}
				
				$totalpayment = 0;
				$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
				$queryObj = $this->db->query($sqlquery, array());
				if($queryObj){
					$totalpayment = $queryObj->fetch(PDO::FETCH_OBJ)->totalpayment;
				}
				
				if($totalpayment<$grand_total){
					$totalUsedCard += $grand_total-$totalpayment;
				}
			}
		}
		
		$available_credit = $credit_limit-$totalUsedCard;
		$returnData = array('login'=>'', 'message'=>'', 'available_credit'=>$available_credit);
		if($isArray>0){
			return $returnData;
		}
		else{
			return json_encode($returnData);
		}
	}
	
	public function updateProdAveCost($accounts_id, $product_id, $it=0){
		$POST = json_decode(file_get_contents('php://input'), true);
		$postSubmit = $POST['postSubmit']??'';
		$ave_cost = $this->productAvgCost($accounts_id, $product_id, 1, $it);
		$inventoryObj = $this->db->query("SELECT inventory_id FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
		if($inventoryObj){
			$inventory_id = $inventoryObj->fetch(PDO::FETCH_OBJ)->inventory_id;
			$this->db->update('inventory', array('ave_cost'=>$ave_cost), $inventory_id);
		}
		if(!empty($postSubmit)){
			return json_encode(array('login'=>''));
		}
	}
	
	public function updateProdInventory($accounts_id, $product_id){
		$POST = json_decode(file_get_contents('php://input'), true);
		$postSubmit = $POST['postSubmit']??'';
		$prodAvgCostData = $this->productAvgCost($accounts_id, $product_id, 1, 1);
		$newCost = $prodAvgCostData[0];
		$newInv = $prodAvgCostData[2];
		$inventoryObj = $this->db->query("SELECT inventory_id FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
		if($inventoryObj){
			$inventory_id = $inventoryObj->fetch(PDO::FETCH_OBJ)->inventory_id;
			$this->db->update('inventory', array('current_inventory'=>$newInv), $inventory_id);
		}
		if(!empty($postSubmit)){
			return json_encode(array('login'=>'', 'newCost'=>$newCost, 'newInv'=>$newInv));
		}
	}
	
	public function updateMobileAvgCost($accounts_id, $po_items_id, $created_on){
		$itemIds = array();
		$pociSql = "SELECT item_id FROM po_cart_item WHERE po_items_id = $po_items_id ORDER BY po_cart_item_id ASC";
		$pociObj = $this->db->query($pociSql, array());
		if($pociObj){
			while($pociOneRow = $pociObj->fetch(PDO::FETCH_OBJ)){
				$itemIds[$pociOneRow->item_id] = 0;
			}
		}
		if(!empty($itemIds)){
			$posSql = "SELECT pos.sales_datetime, pc.pos_cart_id FROM pos, pos_cart pc, pos_cart_item pci WHERE pci.item_id IN (".implode(', ', array_keys($itemIds)).") AND pos.sales_datetime >='$created_on' AND pc.item_type = 'livestocks' AND pos.invoice_no>0 AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2)) AND pos.pos_id = pc.pos_id AND pc.pos_cart_id = pci.pos_cart_id GROUP BY pc.pos_cart_id ORDER BY pc.pos_cart_id ASC";
			$posObj = $this->db->querypagination($posSql, array());
			if($posObj){
				foreach($posObj as $oneRow){						
					//$this->db->writeIntoLog("cartCellphoneAveCost: $oneRow[pos_cart_id]");
					$this->cartCellphoneAveCost($oneRow['pos_cart_id'], $oneRow['sales_datetime'], 1);
				}
			}
		
			$toAccountsId = $toPoNo = $toProductid = 0;
			$sqlPOItem = "SELECT poi.po_items_id, poi.created_on, poi.product_id, po.transfer, po.supplier_id, po.lot_ref_no, po.accounts_id FROM po, po_items poi, po_cart_item poci WHERE po.accounts_id = $accounts_id AND poci.item_id IN (".implode(', ', array_keys($itemIds)).") AND po.transfer = 1 AND po.po_id = poi.po_id AND poi.po_items_id = poci.po_items_id GROUP BY poi.po_items_id ORDER BY poi.po_items_id ASC";
			$poItemObj = $this->db->querypagination($sqlPOItem, array());
			if($poItemObj){
				foreach($poItemObj as $poItemRow){
					//$this->db->writeIntoLog("itCartCellphoneAveCost: $poItemRow[po_items_id]");
					$this->itCartCellphoneAveCost($poItemRow['po_items_id'], $poItemRow['created_on'], 1, $poItemRow['transfer'], $poItemRow['accounts_id']);
					$toAccountsId = $poItemRow['supplier_id'];
					$toPoNo = $poItemRow['lot_ref_no'];
					$toProductid = $poItemRow['product_id'];
				}
			}
			
			//=============To Accounts=============//
			if($toAccountsId >0 && $toPoNo >0 && $toProductid >0){
				$toPoItemsId = 0;
				$toCreated_on = $created_on;
				$sqlPOItem2 = "SELECT poi.po_items_id, poi.created_on, po.transfer, po.accounts_id FROM po, po_items poi WHERE po.accounts_id = $toAccountsId AND po.po_number = $toPoNo AND poi.product_id = $toProductid AND po.transfer = 2 AND po.po_id = poi.po_id GROUP BY poi.po_items_id ORDER BY poi.po_items_id ASC";
				$poItemObj2 = $this->db->querypagination($sqlPOItem2, array());
				if($poItemObj2){
					foreach($poItemObj2 as $poItemRow2){
						//$this->db->writeIntoLog("itCartCellphoneAveCost: $poItemRow2[po_items_id]");
						$this->itCartCellphoneAveCost($poItemRow2['po_items_id'], $poItemRow2['created_on'], 1, $poItemRow2['transfer'], $poItemRow2['accounts_id']);
						$toPoItemsId = $poItemRow2['po_items_id'];
						$toCreated_on = $poItemRow2['created_on'];
					}
				}
				
				if($toPoItemsId>0){
					$toItemIds = array();
					$pociSql2 = "SELECT item_id FROM po_cart_item WHERE po_items_id = $toPoItemsId ORDER BY po_cart_item_id ASC";
					$pociObj2 = $this->db->query($pociSql2, array());
					if($pociObj2){
						while($pociOneRow2 = $pociObj2->fetch(PDO::FETCH_OBJ)){
							$toItemIds[$pociOneRow2->item_id] = 0;
						}
					}

					if(!empty($toItemIds)){
						$sqlquery2 = "SELECT pos.sales_datetime, pc.pos_cart_id FROM pos, pos_cart pc, pos_cart_item pci WHERE pci.item_id IN (".implode(', ', array_keys($toItemIds)).") AND pos.sales_datetime >='$toCreated_on' AND pc.item_type = 'livestocks' AND pos.invoice_no>0 AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2)) AND pos.pos_id = pc.pos_id AND pc.pos_cart_id = pci.pos_cart_id GROUP BY pc.pos_cart_id ORDER BY pc.pos_cart_id ASC";
						$query2 = $this->db->querypagination($sqlquery2, array());
						if($query2){
							foreach($query2 as $oneRow2){						
								//$this->db->writeIntoLog("cartCellphoneAveCost: $oneRow2[pos_cart_id]");
								$this->cartCellphoneAveCost($oneRow2['pos_cart_id'], $oneRow2['sales_datetime'], 1);
							}
						}
					}
				}
			}
		}
	}
	
	public function itCartCellphoneAveCost($po_items_id, $created_on, $costUpdate=0, $transfer = 1, $accounts_id = 0){
		$poCost = $newAveCost = 0.00;
		$IMEIStr = '';
		$newQty = $received_qty = 0;
		
		$sqlPOItems = "SELECT product_id, received_qty, cost FROM po_items WHERE po_items_id = $po_items_id AND item_type = 'livestocks' ORDER BY po_items_id ASC";
		$poItemsObj = $this->db->query($sqlPOItems, array());
		if($poItemsObj){
			while($poItemsRow = $poItemsObj->fetch(PDO::FETCH_OBJ)){
				
				$product_id = $poItemsRow->product_id;
				$received_qty = $poItemsRow->received_qty;
				$poCost = $poItemsRow->cost;
				$totalAveCost = $newQty = 0;
				$itemIds = array();
				$pociSql = "SELECT item_id FROM po_cart_item WHERE po_items_id = $po_items_id ORDER BY po_cart_item_id ASC";
				$pociObj = $this->db->query($pociSql, array());
				if($pociObj){
					while($pociOneRow = $pociObj->fetch(PDO::FETCH_OBJ)){
						$newQty++;
						$itemIds[$pociOneRow->item_id] = '';
					}
				}
				
				if(!empty($itemIds)){
					$moreStr = '';
					if($transfer == 2){
						$itemSql = "SELECT item_id, item_number FROM item WHERE item_id IN (".implode(', ', array_keys($itemIds)).")";
						$itemObj = $this->db->query($itemSql, array());
						if($itemObj){
							while($itemOneRow = $itemObj->fetch(PDO::FETCH_OBJ)){
								$itemIds[$itemOneRow->item_id] = $itemOneRow->item_number;
							}
						}
						
						foreach($itemIds as $item_id=>$item_number){
							$oneIMEIAveCost = $this->oneITIMEIAveCost($accounts_id, $product_id, $item_number, $created_on);
							$totalAveCost += $oneIMEIAveCost[0];
							$poNo = '';
							if($costUpdate==0){
								$purcAveData = $oneIMEIAveCost[1];
								if(!empty($purcAveData)){										
									foreach($purcAveData as $dateTime=>$poCostNo){
										if(count($purcAveData)==1){
											$poNo = $poCostNo[1];
										}
										elseif($created_on !='' && $created_on>=$dateTime){
											$poNo = $poCostNo[1];
										}
									}
								}
							}
							$moreStr = "<br>Acc ID: $accounts_id, po#: $poNo, IMEI: <a href=\"/IMEI/view/$item_number\" style=\"color: #009; text-decoration: underline;\" title=\"View IMEI details\">$item_number</a>, Original Cost: $oneIMEIAveCost[0], $oneIMEIAveCost[2]";
						}
					}
					else{
						foreach($itemIds as $item_id=>$itemVal){
							$oneIMEIAveCost = $this->oneIMEIAveCost(0, $item_id, $created_on);
							$totalAveCost += $oneIMEIAveCost[0];
							if($costUpdate==0){
								$purcAveData = $oneIMEIAveCost[1];
								if(!empty($purcAveData)){
									foreach($purcAveData as $dateTime=>$poCostNo){
										if($created_on !='' && $created_on>=$dateTime){
											$itemSql = "SELECT item_number FROM item WHERE item_id = $item_id";
											$itemObj = $this->db->query($itemSql, array());
											if($itemObj){
												$item_number = $itemObj->fetch(PDO::FETCH_OBJ)->item_number;
									
												$moreStr = "<br>po#: $poCostNo[1], IMEI: <a href=\"/IMEI/view/$item_number\" style=\"color: #009; text-decoration: underline;\" title=\"View IMEI details\">$item_number</a>, Date: $dateTime, Original Cost: $poCostNo[0]";
											}
										}
									}
								}
							}
						}
					}
					$IMEIStr .= $moreStr;
				}
				$newAveCost = $totalAveCost;
				if($newQty != $received_qty){
					$newAveCost = $poCost;
				}
				elseif($newQty>1){
					$newAveCost = round($totalAveCost/$newQty,2);
				}
				if($received_qty<0){$newQty = $newQty*(-1);}
				if($costUpdate==1){
					$updateData = array();					
					if($newAveCost != $poCost){					
						$updateData['cost'] = $newAveCost;
					}
					
					if($received_qty != $newQty){					
						$updateData['received_qty'] = $newQty;
					}
					
					if(!empty($updateData)){
						$this->db->update('po_items', $updateData, $po_items_id);
					}
				}
			}
		}
		return array($poCost, $newAveCost, $received_qty, $newQty, $IMEIStr);
	}
	
	public function getProductAvgCost($product_id, $sales_datetime){
		$costUpdate = $newInv = 0;
		$newCost = 0.00;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$tabledate = '1000-01-01';
		$product_type = 'Standard';
		$sku = $poOrSalesNoStr = '';
		$manage_inventory_count = 1;
		$productObj = $this->db->query("SELECT sku, product_type, manage_inventory_count FROM product WHERE product_id = $product_id", array());
		if($productObj){
			while($oneRow = $productObj->fetch(PDO::FETCH_OBJ)){
				$sku = $oneRow->sku;
				$product_type = $oneRow->product_type;
				$manage_inventory_count = $oneRow->manage_inventory_count;
			}
		}
		
		if($product_type=='Standard'){
			if($manage_inventory_count==0){
				$inventoryObj = $this->db->query("SELECT * FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
				if($inventoryObj){
					$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
					$ave_cost = $inventoryrow->ave_cost;
					$ave_cost_is_percent = $inventoryrow->ave_cost_is_percent;
					if($ave_cost<0){$ave_cost = 0;}
					if($ave_cost_is_percent==1){
						$regular_price = $inventoryrow->regular_price;
						$ave_cost = $regular_price*0.01*$ave_cost;
					}
					$newCost = $ave_cost;
				}
				$poOrSalesNoStr = ' - (Count Inventory : No)';
			}
			else{
				$sqlquery = "SELECT pos_cart.pos_cart_id AS id, 'posreturn' AS tablename, pos.created_on AS tabledate, pos_cart.shipping_qty AS qtyChanged, pos_cart.ave_cost AS aveCost, pos.invoice_no AS poOrSalesNo 
					FROM pos, pos_cart 
					WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = $product_id AND pos_cart.shipping_qty<0 AND pos.sales_datetime < '$sales_datetime' AND pos.pos_id = pos_cart.pos_id ";
				$sqlquery .= "UNION 
					SELECT pos_cart.pos_cart_id AS id, 'pos' AS tablename, pos.sales_datetime AS tabledate, pos_cart.shipping_qty AS qtyChanged, pos_cart.ave_cost AS aveCost, pos.invoice_no AS poOrSalesNo 
					FROM pos, pos_cart 
					WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = $product_id AND pos.sales_datetime < '$sales_datetime' AND pos_cart.shipping_qty>0 AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2)) AND pos.pos_id = pos_cart.pos_id 
				UNION 
					SELECT po_items.po_items_id AS id, 'po' AS tablename, po_items.created_on AS tabledate, po_items.received_qty AS qtyChanged, po_items.cost AS aveCost, po.po_number AS poOrSalesNo 
					FROM po, po_items 
					WHERE po.accounts_id = $accounts_id AND po_items.product_id = $product_id AND po.transfer = 0 AND po.po_datetime < '$sales_datetime' AND po_items.received_qty>0 AND po.po_id = po_items.po_id 
				UNION 
					SELECT po_items.po_items_id AS id, 'poreturn' AS tablename, po_items.created_on AS tabledate, po_items.received_qty AS qtyChanged, po_items.cost AS aveCost, po.po_number AS poOrSalesNo  
					FROM po, po_items 
					WHERE po.accounts_id = $accounts_id AND po_items.product_id = $product_id AND po.transfer = 0 AND po.po_datetime < '$sales_datetime' AND po_items.received_qty<0 AND po.po_id = po_items.po_id 
				UNION 
					SELECT po_items.po_items_id AS id, 'poTransTo' AS tablename, po_items.created_on AS tabledate, (Case When po.status = 'Open' Then po_items.received_qty*-1 Else po_items.received_qty End) AS qtyChanged, po_items.cost AS aveCost, po.po_number AS poOrSalesNo  
					FROM po, po_items 
					WHERE po.accounts_id = $accounts_id AND po_items.product_id = $product_id AND po.transfer = 1 AND po.po_datetime < '$sales_datetime' AND po_items.received_qty !=0 AND po.po_id = po_items.po_id 
				UNION 
					SELECT po_items.po_items_id AS id, 'po' AS tablename, po_items.created_on AS tabledate, po_items.received_qty AS qtyChanged, po_items.cost AS aveCost, po.po_number AS poOrSalesNo  
					FROM po, po_items 
					WHERE po.accounts_id = $accounts_id AND po_items.product_id = $product_id AND po.transfer = 2 AND po.po_datetime < '$sales_datetime' AND po_items.received_qty !=0 AND po.po_id = po_items.po_id 
				UNION 
					SELECT notes.notes_id AS id, 'notes' AS tablename, notes.last_updated AS tabledate, Replace(note, ' inventory has been adjusted $sku', '') AS qtyChanged, 'Calculate' AS aveCost, 0 AS poOrSalesNo  
					FROM notes 
					WHERE notes.accounts_id = $accounts_id AND notes.table_id = $product_id AND notes.note_for = 'product' AND notes.note LIKE '% inventory has been adjusted %' AND notes.last_updated < '$sales_datetime' ";
				$sqlquery .= "ORDER BY tabledate ASC";
				
				$query = $this->db->querypagination($sqlquery, array());
				if($query){
					$prevInvQty = 0;
					$prevAvgCost = 0.00;
					
					foreach($query as $gonerow){
						$id = $gonerow['id'];
						$tablename = $gonerow['tablename'];
						$tabledate = $gonerow['tabledate'];
						if(is_numeric($gonerow['qtyChanged'])){$qtyChanged = $gonerow['qtyChanged'];}
						else{$qtyChanged = floatval($gonerow['qtyChanged']);}
						
						$aveCost = $gonerow['aveCost'];
						$poOrSalesNo = $gonerow['poOrSalesNo'];
						
						if(in_array($tablename, array('posreturn', 'pos'))){
							$qtyChanged = $qtyChanged*(-1);
						}
						else{
							$poOrSalesNoStr = "<a href=\"/Purchase_orders/edit/$poOrSalesNo\">p$poOrSalesNo</a>";
						}
						
						$newInvQty = $prevInvQty+$qtyChanged;
						$newAveCost = $aveCost;
						if($aveCost=='Calculate' ||  in_array($tablename, array('poreturn', 'pos', 'poTransTo'))){
							$newAveCost = $prevAvgCost;
							if($aveCost=='Calculate'){$aveCostStr = '&nbsp;';}							
							else{$aveCostStr = number_format($aveCost,2);}
						}
						else{
							$aveCostStr = number_format($aveCost,2);
							if($prevInvQty>0 && $newInvQty !=0){
								$newAveCost = round((round($prevInvQty*$prevAvgCost,2)+round($qtyChanged*$aveCost,2))/$newInvQty,2);
							}
						}
						$prevInvQty = $newInv = $newInvQty;
						$prevAvgCost = $newCost = $newAveCost;
					}
				}
				
				$OrdersQty = 0;
				$ordersObj = $this->db->query("SELECT pos.invoice_no, SUM(pos_cart.shipping_qty) AS OrdersQty, pos.sales_datetime AS tabledate FROM pos, pos_cart WHERE pos_cart.item_id = $product_id AND pos.accounts_id = $accounts_id AND pos.sales_datetime < '$sales_datetime' AND pos.pos_type = 'Order' AND pos.order_status=1 AND pos_cart.shipping_qty>0 AND pos.pos_id = pos_cart.pos_id GROUP BY pos.pos_id ORDER BY pos.pos_id ASC", array());
				if($ordersObj){
					while($ordersRow = $ordersObj->fetch(PDO::FETCH_OBJ)){
						$OrderQty = $ordersRow->OrdersQty;
						if(!$OrderQty || $OrderQty=='NULL'){$OrderQty = 0;}
						$OrdersQty += $OrderQty;
					}
				}
				if($OrdersQty !=0){
					if($costUpdate==1){
						$newInv -= $OrdersQty;
					}
					else{
						$newInvQty = $newInv-$OrdersQty;					
						$newInv -= $OrdersQty;
					}
				}
				
				$RepairsQty = 0;
				$repairsObj = $this->db->query("SELECT pos.invoice_no, SUM(pos_cart.shipping_qty) AS RepairQty, pos.sales_datetime AS tabledate FROM pos, pos_cart WHERE pos_cart.item_id = $product_id AND pos.accounts_id = $accounts_id AND pos.sales_datetime < '$sales_datetime' AND pos.pos_type = 'Repairs' AND pos.order_status=1 AND pos_cart.shipping_qty>0 AND pos.pos_id = pos_cart.pos_id GROUP BY pos.pos_id ORDER BY pos.pos_id ASC", array());
				if($repairsObj){
					while($repairsRow = $repairsObj->fetch(PDO::FETCH_OBJ)){
						$RepairQty = $repairsRow->RepairQty;
						if(!$RepairQty || $RepairQty=='NULL'){$RepairQty = 0;}
						$RepairsQty += $RepairQty;
					}
				}
				if($RepairsQty !=0){
					if($costUpdate==1){
						$newInv -= $RepairsQty;
					}
					else{
						$newInvQty = $newInv-$RepairsQty;					
						$newInv -= $RepairsQty;
					}
				}
			}
		}
		else{
			$poOrSalesNoStr = " - ($product_type)";
		}
        return array($newCost, $poOrSalesNoStr);
	}
	
	function getOTProductAvgCost($pos_cart_id){
		$newCost = 0.00;
		$poOrSalesNoStr = '';
		$productObj = $this->db->query("SELECT po_id, cost FROM po_items WHERE product_id = $pos_cart_id AND item_type = 'one_time'", array());
		if($productObj){
			while($oneRow = $productObj->fetch(PDO::FETCH_OBJ)){
				$newCost = $oneRow->cost;
				$po_id = $oneRow->po_id;
				$poObj = $this->db->query("SELECT po_number FROM po WHERE po_id = $po_id", array());
				if($poObj){
					$po_number = $poObj->fetch(PDO::FETCH_OBJ)->po_number;
					$poOrSalesNoStr = "<a href=\"/Purchase_orders/edit/$po_number\">p$po_number</a>";
				}
			}
		}
		return array($newCost, $poOrSalesNoStr);
	}
	
	public function productAvgCost($accounts_id, $product_id, $costUpdate=0, $cron=0){
		$returnmsg = $cronStr = '';
		$newCost = 0.00;
		$newInv = 0;

		$product_type = 'Standard';
		$sku = '';
		$manage_inventory_count = 1;

		$productObj = $this->db->query("SELECT sku, product_type, manage_inventory_count FROM product WHERE product_id = $product_id", array());
		if($productObj){
			while($oneRow = $productObj->fetch(PDO::FETCH_OBJ)){
				$sku = $oneRow->sku;
				$product_type = $oneRow->product_type;
				$manage_inventory_count = $oneRow->manage_inventory_count;
			}
		}

		$tabledata = array();
		if($product_type=='Standard' && $manage_inventory_count==1){
			$sqlquery = "SELECT pos_cart.pos_cart_id AS id, 'posreturn' AS tablename, pos.created_on AS tabledate, 'Sales Invoice Returned' AS activity_feed_title, pos_cart.shipping_qty AS qtyChanged, pos_cart.ave_cost AS aveCost, pos.invoice_no AS poOrSalesNo 
				FROM pos, pos_cart 
				WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = $product_id AND pos.pos_id = pos_cart.pos_id AND pos_cart.shipping_qty<0 
			UNION 
				SELECT pos_cart.pos_cart_id AS id, 'pos' AS tablename, pos.sales_datetime AS tabledate, 'Sales Invoice Created' AS activity_feed_title, pos_cart.shipping_qty AS qtyChanged, pos_cart.ave_cost AS aveCost, pos.invoice_no AS poOrSalesNo 
				FROM pos, pos_cart 
				WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = $product_id AND pos.pos_id = pos_cart.pos_id AND pos_cart.shipping_qty>0 AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2)) 
			UNION 
				SELECT po_items.po_items_id AS id, 'po' AS tablename, po_items.created_on AS tabledate, 'Purchase Order Created' AS activity_feed_title, po_items.received_qty AS qtyChanged, po_items.cost AS aveCost, po.po_number AS poOrSalesNo 
				FROM po, po_items 
				WHERE po.accounts_id = $accounts_id AND po_items.product_id = $product_id AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_items.received_qty>0 
			UNION 
				SELECT po_items.po_items_id AS id, 'poreturn' AS tablename, po_items.created_on AS tabledate, 'Purchase Return Created' AS activity_feed_title, po_items.received_qty AS qtyChanged, po_items.cost AS aveCost, po.po_number AS poOrSalesNo  
				FROM po, po_items 
				WHERE po.accounts_id = $accounts_id AND po_items.product_id = $product_id AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_items.received_qty<0 
			UNION 
				SELECT po_items.po_items_id AS id, 'poTransTo' AS tablename, po_items.created_on AS tabledate, 'Inventory Transfer To' AS activity_feed_title, (Case When po.status = 'Open' Then po_items.received_qty*-1 Else po_items.received_qty End) AS qtyChanged, po_items.cost AS aveCost, po.po_number AS poOrSalesNo  
				FROM po, po_items 
				WHERE po.accounts_id = $accounts_id AND po_items.product_id = $product_id AND po.transfer = 1 AND po.po_id = po_items.po_id AND po_items.received_qty !=0 
			UNION 
				SELECT po_items.po_items_id AS id, 'po' AS tablename, po_items.created_on AS tabledate, 'Inventory Transfer From' AS activity_feed_title, po_items.received_qty AS qtyChanged, po_items.cost AS aveCost, po.po_number AS poOrSalesNo  
				FROM po, po_items 
				WHERE po.accounts_id = $accounts_id AND po_items.product_id = $product_id AND po.transfer = 2 AND po.po_id = po_items.po_id AND po_items.received_qty!=0 
			UNION 
				SELECT notes.notes_id AS id, 'notes' AS tablename, last_updated AS tabledate, 'Inventory has been adjusted' AS activity_feed_title, Replace(note, ' inventory has been adjusted $sku', '') AS qtyChanged, 'Calculate' AS aveCost, 0 AS poOrSalesNo  
				FROM notes 
				WHERE accounts_id = $accounts_id AND table_id = $product_id AND note_for = 'product' AND note LIKE '% inventory has been adjusted %' 
			ORDER BY tabledate ASC";
			
			$query = $this->db->querypagination($sqlquery, array());
			if($query){
				$prevInvQty = 0;
				$prevAvgCost = 0.00;
				foreach($query as $gonerow){
					$id = $gonerow['id'];
					$tablename = $gonerow['tablename'];
					$activity_feed_title = stripslashes(trim((string) $gonerow['activity_feed_title']));
					
					if(is_numeric($gonerow['qtyChanged'])){$qtyChanged = $gonerow['qtyChanged'];}
					else{$qtyChanged = floatval($gonerow['qtyChanged']);}
					
					$aveCost = $gonerow['aveCost'];
					$poOrSalesNo = $gonerow['poOrSalesNo'];
					
					if(in_array($tablename, array('posreturn', 'pos'))){
						$qtyChanged = $qtyChanged*(-1);
						$poOrSalesNoStr = "<a href=\"/Invoices/view/$poOrSalesNo\">s$poOrSalesNo</a>";
					}
					else{
						$poOrSalesNoStr = "<a href=\"/Purchase_orders/edit/$poOrSalesNo\">p$poOrSalesNo</a>";
					}
					
					$newInvQty = $prevInvQty+$qtyChanged;
					$newAveCost = $aveCost;
					if($aveCost=='Calculate' ||  in_array($tablename, array('poreturn', 'pos', 'poTransTo'))){
						$newAveCost = $prevAvgCost;
						if($aveCost=='Calculate'){$aveCostStr = '&nbsp;';}							
						else{$aveCostStr = number_format($aveCost,2);}
					}
					else{
						$aveCostStr = number_format($aveCost,2);
						if($prevInvQty>0 && $newInvQty !=0){
							$newAveCost = round((round($prevInvQty*$prevAvgCost,2)+round($qtyChanged*$aveCost,2))/$newInvQty,2);
						}
					}
					$cls = "";
					if(in_array($tablename, array('poreturn', 'pos', 'poTransTo')) && number_format($prevAvgCost,2) != number_format($aveCost,2)){
						$cls = 'bgyellow';
					}
					if($costUpdate==1){
						if($tablename=='pos'){
							if($prevAvgCost > $aveCost || $prevAvgCost < $aveCost){
								$this->db->update('pos_cart', array('ave_cost'=>$prevAvgCost), $id);
								if($cron>0){
									$cronStr .= "
								Inv No: ".strip_tags($poOrSalesNoStr).", Prev Calculate Cost: ".number_format($prevAvgCost,2).", POS Avg Cost: $aveCostStr, New Calculate Avg Cost: ".number_format($newAveCost,2)." (Updated)";
										}
									}
									
								}
								elseif(in_array($tablename, array('poreturn', 'poTransTo'))){
									if($prevAvgCost > $aveCost || $prevAvgCost < $aveCost){
										//$this->db->update('po_items', array('cost'=>$prevAvgCost), $id);
										if($cron>0){
											$cronStr .= "
									PO No: ".strip_tags($poOrSalesNoStr).", Prev Calculate Cost: ".number_format($prevAvgCost,2).", PO Avg Cost: $aveCostStr, New Calculate Avg Cost: ".number_format($newAveCost,2)." (Updated)";
								}
							}
							//=============For To Account PO Item=============//
							$poItemObj1 =  $this->db->query("SELECT po_id FROM po_items WHERE po_items_id = $id", array());
							if($poItemObj1){
								$po_id = $poItemObj1->fetch(PDO::FETCH_OBJ)->po_id;
							
								$poObj1 =  $this->db->query("SELECT supplier_id, lot_ref_no FROM po WHERE po_id = $po_id", array());
								if($poObj1){
									$poRow1 = $poObj1->fetch(PDO::FETCH_OBJ);
									$toaccounts_id = $poRow1->supplier_id;
									$topo_number = intval($poRow1->lot_ref_no);
									
									if($topo_number>0){
										$poItemObj2 = $this->db->query("SELECT po_items.po_items_id, po_items.cost FROM po, po_items WHERE po.po_number = $topo_number AND po.accounts_id = $toaccounts_id AND po_items.product_id = $product_id AND po.po_id = po_items.po_id LIMIT 0, 1", array());
										if($poItemObj2){
											while($row = $poItemObj2->fetch(PDO::FETCH_OBJ)){
												$toPoItemsId = $row->po_items_id;
												$toCost = $row->cost;
												if(number_format($prevAvgCost,2) != number_format($toCost,2)){
													$this->db->update('po_items', array('cost'=>$prevAvgCost), $toPoItemsId);
													
													$this->updateProdAveCost($toaccounts_id, $product_id);
												}
											}
										}
									}
								}
							}
						}						
					}
					else{
						$tabledata[] = array($cls, $gonerow['tabledate'], $activity_feed_title, $poOrSalesNoStr, $prevInvQty, number_format($prevAvgCost,2), $qtyChanged, $aveCostStr, $newInvQty, number_format($newAveCost,2));
					}
					$prevInvQty = $newInv = $newInvQty;
					$prevAvgCost = $newCost = $newAveCost;
				}
			}
			
			$OrdersQty = 0;
			$invoiceNos = array();
			$ordersObj = $this->db->query("SELECT pos.invoice_no, SUM(pos_cart.shipping_qty) AS OrdersQty FROM pos, pos_cart WHERE pos_cart.item_id = $product_id AND pos.accounts_id = $accounts_id AND pos.pos_type = 'Order' AND pos.order_status=1 AND pos_cart.shipping_qty>0 AND pos.pos_id = pos_cart.pos_id GROUP BY pos.pos_id ORDER BY pos.pos_id ASC", array());
			if($ordersObj){
				while($ordersRow = $ordersObj->fetch(PDO::FETCH_OBJ)){
					$OrderQty = $ordersRow->OrdersQty;
					if(!$OrderQty || $OrderQty=='NULL'){$OrderQty = 0;}
					$OrdersQty += $OrderQty;
					$invoiceNos[] = $ordersRow->invoice_no;
				}
			}
			if($OrdersQty !=0){
				if($costUpdate==1){
					$newInv -= $OrdersQty;
				}
				else{
					$newInvQty = $newInv-$OrdersQty;
					$invoiceNosStr = implode(', ', $invoiceNos);
					$tabledata[] = array(1, '', 'Order Open Qty', $invoiceNosStr, $newInv, '', $OrdersQty, '', $newInvQty, '');
					
					$newInv -= $OrdersQty;
				}
			}
			
			$RepairsQty = 0;
			$invoiceNos = array();
			$repairsObj = $this->db->query("SELECT pos.invoice_no, SUM(pos_cart.shipping_qty) AS RepairQty FROM pos, pos_cart WHERE pos_cart.item_id = $product_id AND pos.accounts_id = $accounts_id AND pos.pos_type = 'Repairs' AND pos.order_status=1 AND pos_cart.shipping_qty>0 AND pos.pos_id = pos_cart.pos_id GROUP BY pos.pos_id ORDER BY pos.pos_id ASC", array());
			if($repairsObj){
				while($repairsRow = $repairsObj->fetch(PDO::FETCH_OBJ)){
					$RepairQty = $repairsRow->RepairQty;
					if(!$RepairQty || $RepairQty=='NULL'){$RepairQty = 0;}
					$RepairsQty += $RepairQty;
					$invoiceNos[] = $repairsRow->invoice_no;
				}
			}
			if($RepairsQty !=0){
				if($costUpdate==1){
					$newInv -= $RepairsQty;
				}
				else{
					$newInvQty = $newInv-$RepairsQty;
					
					$invoiceNosStr = implode(', ', $invoiceNos);
					$tabledata[] = array(1, '', 'Repairs Open Qty', $invoiceNosStr, $newInv, '', $RepairsQty, '', $newInvQty, '');
					
					$newInv -= $RepairsQty;
				}
			}			
		}
		
		$returnmsg = $tabledata;		
		if($costUpdate==1){
			if($cron==1){
				$returnmsg = array($newCost, $cronStr, $newInv);
			}
			else
				$returnmsg = $newCost;
		}		
        return $returnmsg;
	}
		
	public function cartCellphoneAveCost($pos_cart_id, $sales_datetime, $costUpdate=0){
		$salesCost = $newAveCost = 0.00;
		$newQty = $shippingQty = 0;
		$IMEIStr = '';
		$sqlPosCart = "SELECT item_id, shipping_qty, ave_cost FROM pos_cart WHERE pos_cart_id = $pos_cart_id AND item_type = 'livestocks' ORDER BY pos_cart_id ASC";
		$posCartObj = $this->db->query($sqlPosCart, array());
		if($posCartObj){
			while($posCartRow = $posCartObj->fetch(PDO::FETCH_OBJ)){
				$product_id = $posCartRow->item_id;
				$shipping_qty = $posCartRow->shipping_qty;
				$salesCost = $posCartRow->ave_cost;
				
				$shippingQty = $newQty = $totalAveCost = 0;
				$pciSql = "SELECT item_id FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id ORDER BY pos_cart_item_id ASC";
				$pciObj = $this->db->query($pciSql, array());
				if($pciObj){
					while($pciOneRow = $pciObj->fetch(PDO::FETCH_OBJ)){
						$oneIMEIAveCost = $this->oneIMEIAveCost(0, $pciOneRow->item_id, $sales_datetime);
						$shippingQty++;
						$totalAveCost += $oneIMEIAveCost[0];
						
						if($costUpdate==0){
							$moreStr = "";
							$purcAveData = $oneIMEIAveCost[1];							
							if(!empty($purcAveData)){			
								$newQty++;
								foreach($purcAveData as $dateTime=>$poCostNo){
									if($sales_datetime !='' && $sales_datetime>=$dateTime){
										$itemSql = "SELECT item_number FROM item WHERE item_id = $pciOneRow->item_id";
										$itemObj = $this->db->query($itemSql, array());
										if($itemObj){
											
											$item_number = $itemObj->fetch(PDO::FETCH_OBJ)->item_number;
											//
											$moreStr = "<br>po#: $poCostNo[1], IMEI: <a href=\"/IMEI/view/$item_number\" style=\"color: #009; text-decoration: underline;\" title=\"View IMEI details\">$item_number</a>, Date: $dateTime, PO Cost: $poCostNo[0]";
										}
									}
								}
							}
							$IMEIStr .= $moreStr;
						}
						else{
						    $purcAveData = $oneIMEIAveCost[1];							
							if(!empty($purcAveData)){			
								$newQty++;
							}
						}
					}
				}

				$newAveCost = $totalAveCost;	
				if($shipping_qty<0){$shipping_qty = $shipping_qty*(-1);}
				
				if($newQty != $shippingQty){
					$newAveCost = $salesCost;
				}
				elseif($shipping_qty>1){
					$newAveCost = round($totalAveCost/$shipping_qty,2);
				}
				
				if($newAveCost != $salesCost){					
					if($costUpdate==1){
						$this->db->update('pos_cart',array('ave_cost'=>$newAveCost), $pos_cart_id);
					}
				}
			}
		}
		return array($salesCost, $newAveCost, $IMEIStr, $shippingQty, $newQty);
	}
	
	public function mobileProdAveCost($accounts_id, $product_id, $extraSql=''){
		$tAveCost = $tInventoryCount = 0;
		$sqlItem = "SELECT item_id FROM item WHERE accounts_id = $accounts_id AND product_id = $product_id$extraSql ORDER BY created_on ASC";
		$query = $this->db->query($sqlItem, array());
		if($query){
			while($itemOneRow = $query->fetch(PDO::FETCH_OBJ)){
				$itemAveCost = $this->oneIMEIAveCost(0, $itemOneRow->item_id, date('Y-m-d H:i:s'));
				$tAveCost += $itemAveCost[0];
				$tInventoryCount++;
			}
		}
		$aveCost = $tAveCost;
		if($tInventoryCount>1){
			$aveCost = round($tAveCost/$tInventoryCount,2);
		}
		$salesCost = 0;
		return array($aveCost, $salesCost);
	}
	
	public function oneIMEIAveCost($product_id, $item_id, $checkDateTime = '', $repeat = 0){
		$purcAveData = array();
		$purchaseAveCost = $found = 0;
		$testingStr = '';
		if($product_id>0){
			$poItemsSql = "SELECT po.po_number, poi.created_on, poi.cost FROM po, po_items poi, po_cart_item poci WHERE poci.item_id = $item_id AND poi.product_id = $product_id AND po.transfer = 0 AND po.po_id = poi.po_id AND poi.po_items_id = poci.po_items_id ORDER BY poi.created_on ASC";
			$poItemsObj = $this->db->query($poItemsSql, array());
			if($poItemsObj){
				while($po_itemsrow = $poItemsObj->fetch(PDO::FETCH_OBJ)){
					$purchaseAveCost = $po_itemsrow->cost;
					$purcAveData[$po_itemsrow->created_on] = array($purchaseAveCost, $po_itemsrow->po_number);
				}
			}
		}

		if(!empty($purcAveData)){
			ksort($purcAveData);
			foreach($purcAveData as $dateTime=>$costInfo){				
				if($checkDateTime !='' && $checkDateTime>=$dateTime){
					$found++;
					$purchaseAveCost = $costInfo[0];
				}
			}
		}
		
		if($found==0){
			$poItemsSql = "SELECT po.po_number, poi.created_on, poi.cost FROM po, po_items poi, po_cart_item poci WHERE poci.item_id = $item_id AND po.transfer IN (0, 2) AND po.po_id = poi.po_id AND poi.po_items_id = poci.po_items_id ORDER BY poi.created_on ASC";
			$poItemsObj = $this->db->query($poItemsSql, array());
			if($poItemsObj){
				while($po_itemsrow = $poItemsObj->fetch(PDO::FETCH_OBJ)){
					$purchaseAveCost = $po_itemsrow->cost;
					$purcAveData[$po_itemsrow->created_on] = array($purchaseAveCost, $po_itemsrow->po_number);
				}
			}	
			if(!empty($purcAveData)){
				ksort($purcAveData);
				foreach($purcAveData as $dateTime=>$costInfo){				
					if($checkDateTime !='' && $checkDateTime>=$dateTime){
						$found++;
						$purchaseAveCost = $costInfo[0];
					}
				}
			}
		}		
		return array($purchaseAveCost, $purcAveData, $testingStr);
	}
	
	public function oneITIMEIAveCost($accounts_id, $product_id, $item_number, $checkDateTime = '', $repeat=0){
		$purcAveData = array();
		$purchaseAveCost = 0.00;
		$item_id = 0;
		$testingStr = '';
		$itemIds = array();
		$itemSql = "SELECT item_id FROM item WHERE accounts_id = $accounts_id AND item_number = '$item_number' AND product_id = $product_id";
		$itemObj = $this->db->query($itemSql, array());
		if($itemObj){			
			while($itemsrow = $itemObj->fetch(PDO::FETCH_OBJ)){
				$itemIds[] = array($itemsrow->item_id, $product_id);
			}
		}
		if(empty($itemIds)){
			$itemSql = "SELECT item_id, product_id FROM item WHERE accounts_id = $accounts_id AND item_number = '$item_number'";
			$itemObj = $this->db->query($itemSql, array());
			if($itemObj){
				while($itemsrow = $itemObj->fetch(PDO::FETCH_OBJ)){
					$itemIds[] = array($itemsrow->item_id, $itemsrow->product_id);
				}
			}
		}
		if(!empty($itemIds)){
			foreach($itemIds as $imeiInfo){
				if($imeiInfo[1]==$product_id){
					$item_id = $imeiInfo[0];
				}
			}
			if($item_id ==0){
				$item_id = $itemIds[0][0];
				$product_id = $itemIds[0][1];
			}
		}
		
		if($item_id>0){
			$oneIMEIAveCost = $this->oneIMEIAveCost(0, $item_id, $checkDateTime, $repeat);
			$purchaseAveCost = $oneIMEIAveCost[0];
			$purcAveData = $oneIMEIAveCost[1];
			$testingStr = $oneIMEIAveCost[2];
		}
		return array($purchaseAveCost, $purcAveData, $testingStr);
	}
	
	public function getOneRowInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnData = array();
		$returnData['login'] = '';
		
		$tableName = $POST['tableName']??'';
		$tableId = intval($POST['tableId']??0);
		$tableIdName = $tableName.'_id';
		$tableObj = $this->db->querypagination("SELECT * FROM $tableName WHERE $tableIdName = $tableId", array());
		if(!empty($tableObj)){
			foreach ($tableObj[0] as $key=>$value) {
				$returnData[$key] = stripslashes($value);
			}
		}
		return json_encode($returnData);
	}
	
	public function AJremoveData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		
		$returnData = array();
		$returnData['login'] = '';
		
		$tableName = $POST['tableName']??'';
		$tableId = intval($POST['tableId']??0);
		$nameVal = $POST['nameVal']??'';
		$publishVal = intval($POST['publishVal']??0);

		$publishName = $tableName.'_publish';
		if($tableName=='user'){$publishName .= 'ed';}
		
		$savemsg = "";

		$updatetable = $this->db->update($tableName, array($publishName=>$publishVal), $tableId);
		if($updatetable){
			if(!in_array($tableName, array('brand_model'))){
				if($publishVal==0){
					$activity_feed_title = ucfirst($tableName).' '.$this->db->translate('Archived');
				}
				else{
					$activity_feed_title = ucfirst($tableName).' '.$this->db->translate('Actived');
				}
				
				$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
				$activity_feed_link = "/Manage_Data/$tableName/";
				$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
				
				$afData = array('created_on' => date('Y-m-d H:i:s'),
								'last_updated' => date('Y-m-d H:i:s'),
								'accounts_id' => $_SESSION["accounts_id"],
								'user_id' => $_SESSION["user_id"],
								'activity_feed_title' => $activity_feed_title,
								'activity_feed_name' => $nameVal,
								'activity_feed_link' => $activity_feed_link,
								'uri_table_name' => $tableName,
								'uri_table_field_name' =>$publishName,
								'field_value' => 0);
				$this->db->insert('activity_feed', $afData);
			}
			$savemsg = 'archive-success';
		}
		return json_encode(array('login'=>'', 'returnStr'=>$savemsg));
	}

	public function variablesData($variableName, $accounts_id){
		$returnData = array();
		if($variableName=='account_setup'){
			$returnData = array('currency'=>'৳', 'timezone'=>'America/New_York', 'dateformat'=>'m/d/y', 'timeformat'=>'12 hour', 'language'=>'English');
		}
		elseif($variableName=='payment_options'){
			$returnData = array('payment_options'=>'Cash||Check||Visa||MasterCard||AMEX||Discover||Other||Debit Card');
		}
		elseif($variableName=='product_setup'){
			$returnData = array('carriers'=>'AT & T||Bell||Rogers||Telus||TMobile||Unlocked||Verizon', 'conditions'=>'A||B||C||D||New');
		}
		elseif($variableName=='label_printer'){
			$returnData = array('labelSizeMissing'=> true, 'label_size'=>'', 'fontFamily'=>'Arial', 'fontSize'=>'Regular', 'units'=>'mm', 'label_sizeWidth'=>'', 'label_sizeHeight'=>'', 'top_margin'=>5, 'right_margin'=>3, 'bottom_margin'=>5, 'left_margin'=>3, 'font_size'=>'Regular', 'orientation'=>'Portrait');
		}
		elseif($variableName=='barcode_labels'){
$deviceLabel = "{{ProductName}}
{{Barcode}}";
$productLabel = '{{ProductName}}
{{Barcode}}';
$repairCustomerLabel = "{{FirstName}} {{LastName}}
{{PhoneNo}}
{{TicketNo}}
{{DueDate}}";
$repairTicketLabel = "{{FirstName}} {{LastName}} {{DueDate}}
{{BrandModel}}
{{MoreDeails}}
{{ImeiSerial}}
{{Problem}}
{{Barcode}}";
			$returnData = array('deviceLabel'=>$deviceLabel, 
			'productLabel'=>$productLabel, 
			'repairCustomerLabel'=>$repairCustomerLabel, 
			'repairTicketLabel'=>$repairTicketLabel);
		}
		elseif($variableName=='multiple_drawers'){
			$returnData = array('multiple_cash_drawers'=>0, 'cash_drawers'=>'');
		}
		$varObj = $this->db->query("SELECT variables_id, value FROM variables WHERE accounts_id = $accounts_id AND name = '$variableName'", array());
		if($varObj){
			$varRow = $varObj->fetch(PDO::FETCH_OBJ);
			$value = $varRow->value;
			$returnData['variables_id'] = $varRow->variables_id;
			if(!empty($value)){
				if(serialize($returnData)==$value){
					$this->db->delete('variables', 'variables_id', $varRow->variables_id);
				}
				else{
					$value = preg_replace_callback(
						'/(?<=^|\{|;)s:(\d+):\"(.*?)\";(?=[asbdiO]\:\d|N;|\}|$)/s',
						function($m){
							return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
						},
						$value
					);
					//$value = preg_replace_callback('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $value);
					$varData = unserialize($value);
					if(!empty($varData)){
						foreach($varData as $index=>$ivalue){
							$returnData[$index] = $ivalue;
						}
					}

					if($variableName=='label_printer'){
						$returnData['labelSizeMissing'] = false;
					}
				}
			}
		}
		else{
			$returnData['variables_id'] = 0;
		}
		return $returnData;
    }
	
    public function removeVariables(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';
		$message = "<p>".str_replace('COMPANYNAME', COMPANYNAME, $this->db->translate('You could not remove SMS Integration information before accessing Your SMS to COMPANYNAME.'))."</p>";
		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = intval($POST['variables_id']??0);
		if($variables_id>0){
			$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND variables_id = :variables_id", array('variables_id'=>$variables_id),1);
			if($varObj){
				$update = $this->db->delete('variables', 'variables_id', $variables_id);
				if($update){
					$savemsg = 'remove-success';
				}
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg,
			'message'=>$message);
		return json_encode($array);
    }
	
	public function getOrderRepairShipQty($accounts_id, $product_id){
		$totalShipQty = 0;
		$sqlquery = "SELECT SUM(pos_cart.shipping_qty) AS totalShipQty FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = $product_id AND pos_cart.shipping_qty !=0 AND pos.pos_id = pos_cart.pos_id AND pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 1 GROUP BY pos_cart.item_id";
		$query = $this->db->querypagination($sqlquery, array());
		if($query){
			foreach($query as $gonerow){
				$totalShipQty += $gonerow['totalShipQty'];
			}
		}
		return $totalShipQty;
	}

	public function AJmergeTableData(){
		//category, menufacturer, repair_problem, brand_model, vendors, expense_type, customer_type
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = $savemsg = '';
		$id = 0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$fromTableData_id = intval($POST['fromTableData_id']??0);
		$toTableData_id = intval($POST['toTableData_id']??0);
		$tableName = $POST['tableName']??'';
		$tableIDName = $tableName.'_id';
		$tablePublishName = $tableName.'_publish';

		$fromTableObj = $this->db->query("SELECT * FROM $tableName WHERE $tableIDName = :id", array('id'=>$fromTableData_id), 1);
		if($fromTableObj){
			$fromTableRow = $fromTableObj->fetch(PDO::FETCH_OBJ);
			$toTableObj = $this->db->query("SELECT * FROM $tableName WHERE $tableIDName = :id", array('id'=>$toTableData_id), 1);
			if($toTableObj){
				$toTableRow = $toTableObj->fetch(PDO::FETCH_OBJ);
				
				$updateData = array($tablePublishName=>0);
				if($tableName == 'repair_problems'){
					if(!empty($fromTableRow->additional_disclaimer) && empty($toTableRow->additional_disclaimer))
						$updateData['additional_disclaimer'] = $fromTableRow->additional_disclaimer;
				}
				elseif($tableName == 'brand_model'){
					if(!empty($fromTableRow->model) && empty($toTableRow->model))
						$updateData['model'] = $fromTableRow->model;
				}				
				$update = $this->db->update($tableName, $updateData, $fromTableData_id);
				if($update){
					$id = $fromTableData_id;
					$savemsg = 'Success';

					if(in_array($tableName, array('category', 'manufacturer', 'repair_problems', 'brand_model', 'vendors', 'expense_type', 'customer_type'))){ 
						$filterSql = "SELECT activity_feed_id FROM activity_feed WHERE accounts_id = $accounts_id AND uri_table_name = '$tableName' AND activity_feed_link LIKE CONCAT('/Manage_Data/$tableName/view/', :id)";
						$tableObj = $this->db->query($filterSql, array('id'=>$fromTableData_id));
						if($tableObj){
							while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
								$activity_feed_link = '/Manage_Data/'.$tableName.'/view/'.$toTableData_id;
								$this->db->update('activity_feed', array('activity_feed_link'=>$activity_feed_link), $oneRow->activity_feed_id);
							}
						}
					}
					
					if(in_array($tableName, array('category', 'manufacturer'))){ 
						$filterSql = "SELECT product_id FROM product WHERE accounts_id = $prod_cat_man AND $tableIDName = :id";
						$tableObj = $this->db->query($filterSql, array('id'=>$fromTableData_id));
						if($tableObj){
							while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
								$this->db->update('product', array($tableIDName=>$toTableData_id), $oneRow->product_id);
							}
						}

						$filterSql = "SELECT stock_take_id FROM stock_take WHERE accounts_id = $accounts_id AND $tableIDName = :id";
						$tableObj = $this->db->query($filterSql, array('id'=>$fromTableData_id));
						if($tableObj){
							while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
								$this->db->update('stock_take', array($tableIDName=>$toTableData_id), $oneRow->stock_take_id);
							}
						}						
					}
					elseif($tableName == 'repair_problems'){
						$filterSql = "SELECT repairs_id FROM repairs WHERE accounts_id = $accounts_id AND problem = :problem";
						$tableObj = $this->db->query($filterSql, array('problem'=>$fromTableRow->name));
						if($tableObj){
							while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
								$this->db->update('repairs', array('problem'=>$toTableRow->name), $oneRow->repairs_id);
							}
						}					
					}
					elseif($tableName == 'brand_model'){
						$filterSql = "SELECT properties_id FROM properties WHERE accounts_id = $accounts_id AND $tableIDName = :id";
						$tableObj = $this->db->query($filterSql, array('id'=>$fromTableData_id));
						if($tableObj){
							while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
								$this->db->update('properties', array($tableIDName=>$toTableData_id), $oneRow->properties_id);
							}
						}
					}
					elseif($tableName == 'vendors'){
						$filterSql = "SELECT expenses_id FROM expenses WHERE accounts_id = $accounts_id AND $tableIDName = :id";
						$tableObj = $this->db->query($filterSql, array('id'=>$fromTableData_id));
						if($tableObj){
							while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
								$this->db->update('expenses', array($tableIDName=>$toTableData_id), $oneRow->expenses_id);
							}
						}					
					}
					elseif($tableName == 'expense_type'){
						$filterSql = "SELECT expenses_id FROM expenses WHERE accounts_id = $accounts_id AND expense_type = :expense_type";
						$tableObj = $this->db->query($filterSql, array('expense_type'=>$fromTableRow->name));
						if($tableObj){
							while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
								$this->db->update('expenses', array('expense_type'=>$toTableRow->name), $oneRow->expenses_id);
							}
						}					
					}
					elseif($tableName == 'customer_type'){
						$filterSql = "SELECT customers_id FROM customers WHERE accounts_id = $accounts_id AND customer_type = :customer_type";
						$tableObj = $this->db->query($filterSql, array('customer_type'=>$fromTableRow->name));
						if($tableObj){
							while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
								$this->db->update('customers', array('expense_type'=>$toTableRow->name), $oneRow->customers_id);
							}
						}					
					}

				}

				$conditionarray = array();
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$conditionarray['last_updated'] = date('Y-m-d H:i:s');
				$conditionarray['accounts_id'] = $_SESSION["accounts_id"];
				$conditionarray['user_id'] = $_SESSION["user_id"];
				$conditionarray['activity_feed_title'] = '';
				$conditionarray['activity_feed_name'] = '';
				$conditionarray['activity_feed_link'] = '';
				if($tableName=='category'){
					$conditionarray['activity_feed_title'] = $this->db->translate('Merge this').' '.$tableName.' "'.$fromTableRow->category_name.'" with  "'.$toTableRow->category_name.'"';					
					$conditionarray['activity_feed_name'] = $fromTableRow->category_name;
					$conditionarray['activity_feed_link'] = "/Manage_Data/$tableName/view/$toTableData_id";
				}
				elseif($tableName=='brand_model'){
					$conditionarray['activity_feed_title'] = $this->db->translate('Merge this').' '.str_replace('_', ' ', $tableName).' "'.$fromTableRow->brand.' '.$fromTableRow->model.'" with  "'.$toTableRow->brand.' '.$toTableRow->model.'"';					
					$conditionarray['activity_feed_name'] = $fromTableRow->brand.' '.$fromTableRow->model;
					$conditionarray['activity_feed_link'] = "/Manage_Data/$tableName/view/$toTableData_id";
				}
				elseif(in_array($tableName, array('manufacturer', 'repair_problems', 'vendors', 'expense_type', 'customer_type'))){ 
					$conditionarray['activity_feed_title'] = $this->db->translate('Merge this').' '.str_replace('_', ' ', $tableName).' "'.$fromTableRow->name.'" with  "'.$toTableRow->name.'"';					
					$conditionarray['activity_feed_name'] = $fromTableRow->name;
					$conditionarray['activity_feed_link'] = "/Manage_Data/$tableName/view/$toTableData_id";
				}
				$conditionarray['activity_feed_title'] = $this->db->checkCharLen('activity_feed.activity_feed_title', $conditionarray['activity_feed_title']);
				$conditionarray['activity_feed_name'] = $this->db->checkCharLen('activity_feed.activity_feed_name', $conditionarray['activity_feed_name']);
				$conditionarray['activity_feed_link'] = $this->db->checkCharLen('activity_feed.activity_feed_link', $conditionarray['activity_feed_link']);
				
				$conditionarray['uri_table_name'] = $tableName;
				$conditionarray['uri_table_field_name'] = $tablePublishName;
				$conditionarray['field_value'] = 0;
				
				$this->db->insert('activity_feed', $conditionarray);
			}			
		}
		return json_encode(array('login'=>'', 'savemsg'=>$savemsg, 'id'=>$id));
	}

	//=====================custom_fields=================//
	public function load_custom_fields($field_for = 'customers'){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		
		$sqlquery = "SELECT * FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = '$field_for' AND custom_fields_publish = 1 ORDER BY order_val ASC";
		$query = $this->db->querypagination($sqlquery, array());
		$tabledata = array();
		if($query){
			$s = 0;
			$precustom_fields_id = 0;
			foreach($query as $oneRow){
				$s++;
				$custom_fields_id = $oneRow['custom_fields_id'];
				$field_for = $oneRow['field_for'];
				$order_val = $oneRow['order_val'];
				$field_name = stripslashes($oneRow['field_name']);
				$field_required = stripslashes($oneRow['field_required']);
				$required = 'No';
				if($field_required>0){$required = 'Yes';}
				$field_type = stripslashes($oneRow['field_type']);
				$custom_fields_publish = $oneRow['custom_fields_publish'];
				
				$tabledata[] = array($custom_fields_id, $field_for, $order_val, $precustom_fields_id, $field_name, $required, $field_type);
				$precustom_fields_id = $custom_fields_id;
			}
		}

		return $tabledata;
	}
	
	public function customFormFields($field_for, $custom_data){
		$responseData = array();
		if(isset($_SESSION["prod_cat_man"])){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$accounts_id = $_SESSION["accounts_id"]??0;
			
			$queryCFObj = $this->db->query("SELECT * FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = '$field_for' ORDER BY order_val ASC", array());
			if($queryCFObj){
				if(!empty($custom_data)){$custom_data = unserialize($custom_data);}
				while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
					$custom_fields_id = $oneCustomFields->custom_fields_id;
					$field_name = trim((string) stripslashes($oneCustomFields->field_name));
					$field_required = stripslashes($oneCustomFields->field_required);
					
					$field_type = stripslashes($oneCustomFields->field_type);
					$parameters = stripslashes($oneCustomFields->parameters);
					$value = '';
					if(!empty($custom_data) && array_key_exists($field_name, $custom_data)){
						$value = $custom_data[$field_name];
						if(in_array($field_type, array('Picture', 'PDF'))){
							if($value !=''){
								$prodImg = "./assets/accounts/a_$accounts_id/$value";
								if(file_exists($prodImg)){
									$value = "/assets/accounts/a_$accounts_id/$value";
								}else{
									$value = '';
								}
							}
						}
					}
					$oneRow = array();
					$oneRow['custom_fields_id'] = intval($custom_fields_id);
					$oneRow['field_name'] = $field_name;
					$oneRow['required'] = intval($field_required);
					$oneRow['field_type'] = $field_type;
					$oneRow['parameters'] = $parameters;
					$oneRow['value'] = $value;
					
					$responseData[] = $oneRow;
				}
			}
		}
		return $responseData;
	}

	public function customViewInfo($field_for, $custom_data){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$customFields = 0;
		if($custom_data !=''){
			$custom_data = unserialize($custom_data);
		}
		$customFieldNames = $customFieldData = array();
		$cqueryObj = $this->db->query("SELECT custom_fields_id, field_type, field_name, field_required FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = '$field_for' ORDER BY order_val ASC", array());
		if($cqueryObj){
			while($oneCFRow = $cqueryObj->fetch(PDO::FETCH_OBJ)){
				$customFieldNames[$oneCFRow->field_name] = array($oneCFRow->custom_fields_id, $oneCFRow->field_type, intval($oneCFRow->field_required));
			}
			$customFields = count($customFieldNames);
		}
		if($customFields>0){
			foreach($customFieldNames as $oneFieldName=>$oneFieldMoreInfo){
				$custom_fields_id = $oneFieldMoreInfo[0];
				$field_type = $oneFieldMoreInfo[1];
				$field_required = $oneFieldMoreInfo[2];
				
				$labelVal = '';
				if(!empty($custom_data) && array_key_exists($oneFieldName, $custom_data)){
					$labelVal = $custom_data[$oneFieldName];
					unset($custom_data[$oneFieldName]);
					if(in_array($field_type, array('Picture', 'PDF'))){
						if($labelVal !=''){
							$prodImg = "./assets/accounts/a_$accounts_id/$labelVal";
							if(file_exists($prodImg)){
								if($field_type==='PDF'){
									$prodImg = "<div class=\"customPDF\"><a target=\"_blank\" href=\"/assets/accounts/a_$accounts_id/$labelVal\" title=\"View PDF File\">";
									$prodImg .= "<img src=\"/assets/images/pdfFile.png\" alt=\"View PDF File\">";
									$prodImg .= "</a></div>";
								}
								else{
									$prodImg = "<div class=\"customPicture\"><a target=\"_blank\" href=\"/assets/accounts/a_$accounts_id/$labelVal\" title=\"View Picture\">";
									$prodImg .= "<img src=\"/assets/images/photoFile.png\" alt=\"View Picture\">";
									$prodImg .= "</a></div>";
								}
								$labelVal = $prodImg;
							}else{
								$labelVal = '';
							}
						}
					}
					else{
						$labelVal = nl2br($labelVal);
					}
				}
				if($field_required>0){
					$oneFieldName .= '<span class="errormsg">*</span>';
				}
				$customFieldData[$oneFieldName] = $labelVal;
			}
			
			if(!empty($custom_data)){
				foreach($custom_data as $oneFieldName=>$labelVal){
					$customFieldData[$oneFieldName] = nl2br($labelVal);
				}
			}
		}
		return array($customFields, $customFieldData);
	}
	
	public function postCustomFormFields($field_for, $id=0){
		//$POST = $_POST;//json_decode(file_get_contents('php://input'), true);
		$custom_data = array();
		if(isset($_SESSION["prod_cat_man"])){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$accounts_id = $_SESSION["accounts_id"]??0;
			
			$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name, field_type FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = '$field_for' ORDER BY order_val ASC", array());
			if($queryCFObj){
				while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
					$custom_fields_id = $oneCustomFields->custom_fields_id;
					$field_type = stripslashes($oneCustomFields->field_type);
					if(in_array($field_type, array('Picture', 'PDF'))){
						$fileprename = "$field_for-$id-CFId-$custom_fields_id-";
						$fieldname = 'cf'.$custom_fields_id;
						$value = '';
						$existingFile = "./assets/accounts/a_$accounts_id/$fileprename";
						if(!isset($_FILES) || !array_key_exists($fieldname, $_FILES) || !is_uploaded_file($_FILES[$fieldname]['tmp_name'])){
							$pics = glob($existingFile."*.jpg");
							if(!$pics){
								$pics = glob($existingFile."*.png");
								if(!$pics){
									$pics = glob($existingFile."*.pdf");
								}
							}
							if($pics){
								foreach($pics as $onePicture){
									if(file_exists($onePicture))
										$value = str_replace("./assets/accounts/a_$accounts_id/", '', $onePicture);
								}
							}
						}						
						else{

							$fileSize = floor(filesize($_FILES[$fieldname]['tmp_name'])/1024);//KB
							$fileSize = floor($fileSize/1024);//MB
							
							if($fileSize<=10){
								
								$pics = glob($existingFile."*.jpg");
								if(!$pics){
									$pics = glob($existingFile."*.png");
									if(!$pics){
										$pics = glob($existingFile."*.pdf");
									}
								}
								if($pics){
									foreach($pics as $onePicture){
										if(file_exists($onePicture))
											unlink($onePicture);
									}
								}

								ini_set('memory_limit', '500M');
								$frompage = $field_for;			
								$folderpath = "./assets/accounts/a_".$accounts_id;
								if(strcmp($frompage, 'products')==0){
									$folderpath = "./assets/accounts/a_".$accounts_id;
								}
								if(!is_dir($folderpath)){mkdir($folderpath, 0777);}

								$filename = $_FILES[$fieldname]["name"];
								$efilename = explode('.', $filename);
								$ext = strtolower($efilename[count($efilename) - 1]); 
								
								if(in_array(strtoupper($ext), array('PDF'))){//, 'DOC', 'DOCX', 'PPTX', 'XLSX'
									$tmpFileName = $_FILES[$fieldname]['tmp_name'];
									$filename = $fileprename.substr(time(),7,3).".".$ext;
									$attachedpath = "$folderpath/$filename";
																		
									if(move_uploaded_file($tmpFileName, $attachedpath)){
										$value = $filename;
									}
								}
								else{
									$imagename = $fileprename.substr(time(),7,3).'.png';
									$width = 1024;
									$height = 1024;
									$savemsg = '';
									$image_info = getimagesize($_FILES[$fieldname]['tmp_name']);
									$imageType = $image_info[2];//1=gif, 2=jpg/jpeg, 3=png, 
									if ($imageType > 3 ) {
										$savemsg = 'fileNotSupported';
									}
									$orig_width = $image_info[0];
									$orig_height = $image_info[1];
									//======Update Image Size=========//
									$source_aspect_ratio = 1;
									if($orig_height !=0){
										$source_aspect_ratio = $orig_width / $orig_height;
									}
									
									$thumbnail_aspect_ratio = 1;
									if($height !=0){
										$thumbnail_aspect_ratio = $width / $height;
									}
									if ($orig_width <= $width && $orig_height <= $height) {
										$thumbnail_image_width = $orig_width;
										$thumbnail_image_height = $orig_height;
									} elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
										$thumbnail_image_width = (int) ($height * $source_aspect_ratio);
										$thumbnail_image_height = $height;
									} else {
										$thumbnail_image_width = $width;
										$thumbnail_image_height = (int) ($width / $source_aspect_ratio);
									}
									
									$width = $thumbnail_image_width;
									$height = $thumbnail_image_height;
									
									//=========Create Image==============//
									$new_image = imagecreatetruecolor( $width, $height );
									$white = imagecolorallocate($new_image, 255, 255, 255);
									imagefill($new_image, 0, 0, $white);
									
									if ($imageType == '1' ) {
										$image = imagecreatefromgif($_FILES[$fieldname]['tmp_name']);
									}
									elseif ($imageType == '2' ) {
										$image = imagecreatefromjpeg($_FILES[$fieldname]['tmp_name']);
									}
									elseif ($imageType == '3' ) {
										$image = imagecreatefrompng($_FILES[$fieldname]['tmp_name']);
									}
									else{
										$savemsg = 'fileNotSupported';
									}
								
									if(empty($savemsg)){
										$transparent = imagecolorallocatealpha( $new_image, 0, 0, 0, 127 );
										imagefill( $new_image, 0, 0, $transparent );

										imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
										imagepng($new_image, $folderpath.'/'.$imagename, 0);
										imagedestroy($new_image);
										imagedestroy($image);
										
										$picturesrc = str_replace('./', '/', $folderpath.'/'.$imagename);
										
										//==============Image Compression and replace=================//
										if(extension_loaded('imagick')){
											//=============Testing White BG Code=================//
											$hasAlpha = $this->hasAlpha(imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . $picturesrc));
											//var_dump($hasAlpha);
											//=============Testing White BG Code=================//
											
											$im = new Imagick($_SERVER['DOCUMENT_ROOT'] . $picturesrc);
											$im->optimizeImageLayers(); // Optimize the image layers			
											$im->setImageCompression(Imagick::COMPRESSION_JPEG);// Compression and quality
											$im->setImageCompressionQuality(0);
											
											$imagename = str_replace('.png', '.jpg', $imagename);
											$picturesrc1 = str_replace('.png', '.jpg', $picturesrc);
											$im->writeImages($_SERVER['DOCUMENT_ROOT'] . $picturesrc1, true);// Write the image back
											unlink('.'.$picturesrc); // delete file		
											$picturesrc = $picturesrc1;
										}
										$value = str_replace("/assets/accounts/a_$accounts_id/", '', $picturesrc);										
									}
								}								
							}
							else{
								//$this->db->writeIntoLog('Large File: '.$onePicture);
							}						
						}
						$custom_data[stripslashes(trim((string) $oneCustomFields->field_name))] = trim((string)$value);
					}
					else{
						$custom_data[stripslashes(trim((string) $oneCustomFields->field_name))] = trim((string)  array_key_exists('cf'.$custom_fields_id, $_POST) ? $_POST['cf'.$custom_fields_id]:'');
					}
				}
			}
		}
		
		return serialize($custom_data);
	}
	
	//===============AJautoComplete==============//	
	public function AJautoComplete_employee(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$keyword_search = $POST['keyword_search']??'';
		$fieldIdName = $POST['fieldIdName']??'';
		$extrastr = "";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searchs = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searchs[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searchs) ) {
				$extrastr .= " AND TRIM(CONCAT_WS(' ', ";
				if($fieldIdName=='assign_to'){$extrastr .= "user.user_first_name, user.user_last_name";}
				else{$extrastr .= "user_first_name, user_last_name";}
				$extrastr .= " )) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searchs[$num]);
				$num++;
			}
		}
					
		$responseData = array();
		if($fieldIdName=='assign_to'){
			$Sql = "SELECT user.user_first_name, user.user_last_name FROM user, repairs WHERE user.accounts_id = $accounts_id AND repairs.assign_to = user.user_id AND user.user_publish =1 $extrastr GROUP BY user.user_id ORDER BY user.user_first_name ASC, user.user_last_name ASC";
		}
		else{
			$Sql = "SELECT user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id AND user_publish =1 $extrastr ORDER BY user_first_name ASC, user_last_name ASC";
		}
		$tableObj = $this->db->query($Sql, $bindData);
		if($tableObj){
			while($onerow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$first_name = stripslashes($onerow->user_first_name);
				$last_name = stripslashes($onerow->user_last_name);
				$label = addslashes(trim((string) "$first_name $last_name"));
				
				$responseData[] =array('label' => $label);
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$responseData));
	}
	
	public function AJautoComplete_product(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$keyword_search = $POST['keyword_search']??0;
		$CellPhone = $POST['CellPhone']??1;
		$extrastr = "";
		$bindData = array();
		if($CellPhone==0){
			$extrastr .= " AND p.product_type != 'Live Stocks'";
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searchs = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searchs[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searchs) ) {
				$extrastr .= " AND TRIM(CONCAT_WS(' ', p.sku, manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searchs[$num]);
				$num++;
			}
		}
		
		$responseData = array();
		$sql = "SELECT p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_publish = 1 $extrastr ORDER BY CONCAT(manufacture, ' ', p.product_name, ' ', p.colour_name, ' ', p.storage, ' ', p.physical_condition_name) ASC";
		$queryObj = $this->db->query($sql, $bindData);
		if($queryObj){
			while($onerow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$name = stripslashes((string) $onerow->manufacture);
				$product_name = trim(stripslashes($name.' '.$onerow->product_name));
				
				$colour_name = $onerow->colour_name;
				if($colour_name !=''){$product_name .= ' '.$colour_name;}
				
				$storage = $onerow->storage;
				if($storage !=''){$product_name .= ' '.$storage;}
				
				$physical_condition_name = $onerow->physical_condition_name;
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

				$sku = $onerow->sku;
				$label = "$product_name ($sku)";
				
				$responseData[] =array('label' => $label, 'sku' => $sku);
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$responseData));
	}


	public function AJautoComplete_lsproduct(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$keyword_search = $POST['keyword_search']??0;
		$CellPhone = $POST['CellPhone']??1;
		$extrastr = "";
		$bindData = array();
		if($CellPhone==0){
			$extrastr .= " AND p.product_type != 'Live Stocks'";
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searchs = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searchs[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searchs) ) {
				$extrastr .= " AND TRIM(CONCAT_WS(' ', p.sku, manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name, item.tag, item.alt_tag)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searchs[$num]);
				$num++;
			}
		}
		
		$responseData = array();
		$sql = "SELECT p.sku, manufacturer.name AS manufacture, p.product_id AS id, p.product_name, p.colour_name, p.storage, p.physical_condition_name, item.tag, item.alt_tag FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) LEFT JOIN item ON (p.product_id = item.product_id) WHERE p.accounts_id = $prod_cat_man AND p.product_publish = 1 $extrastr ORDER BY CONCAT(manufacture, ' ', p.product_name, ' ', p.colour_name, ' ', p.storage, ' ', p.physical_condition_name) ASC";
		$queryObj = $this->db->query($sql, $bindData);
		if($queryObj){
			while($onerow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$name = stripslashes((string) $onerow->manufacture);
				$product_name = trim(stripslashes($name.' '.$onerow->product_name));
				$tag = trim(stripslashes($name.' '.$onerow->tag));
				
				$pid = $onerow->id;

				$colour_name = $onerow->colour_name;
				if($colour_name !=''){$product_name .= ' '.$colour_name;}
				
				$storage = $onerow->storage;
				if($storage !=''){$product_name .= ' '.$storage;}
				
				$physical_condition_name = $onerow->physical_condition_name;
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

				$sku = $onerow->sku;
				// $label = "$product_name ($sku)";
				$label = "$tag ($sku)";
				
				$responseData[] =array('label' => $label, 'sku' => $sku, 'type' => 'lsproduct', 'id'=>$pid);
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$responseData));
	}


	public function AJautoComplete_plsproduct(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$keyword_search = $POST['keyword_search']??0;
		$CellPhone = $POST['CellPhone']??1;
		$extrastr = "";
		$bindData = array();
		if($CellPhone==0){
			$extrastr .= " AND p.product_type != 'Live Stocks'";
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searchs = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searchs[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searchs) ) {
				$extrastr .= " AND TRIM(CONCAT_WS(' ', p.sku, manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name, item.tag, item.alt_tag)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searchs[$num]);
				$num++;
			}
		}
		
		$responseData = array();
		$sql = "SELECT p.sku, manufacturer.name AS manufacture, p.product_id AS id, p.product_name, p.colour_name, p.storage, p.physical_condition_name, item.tag, item.alt_tag FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) LEFT JOIN item ON (p.product_id = item.product_id) WHERE p.accounts_id = $prod_cat_man AND p.product_publish = 1 $extrastr ORDER BY CONCAT(manufacture, ' ', p.product_name, ' ', p.colour_name, ' ', p.storage, ' ', p.physical_condition_name) ASC";
		$queryObj = $this->db->query($sql, $bindData);
		if($queryObj){
			while($onerow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$name = stripslashes((string) $onerow->manufacture);
				$product_name = trim(stripslashes($name.' '.$onerow->product_name));
				$tag = trim(stripslashes($name.' '.$onerow->tag));
				
				$pid = $onerow->id;
				
				$colour_name = $onerow->colour_name;
				if($colour_name !=''){$product_name .= ' '.$colour_name;}
				
				$storage = $onerow->storage;
				if($storage !=''){$product_name .= ' '.$storage;}
				
				$physical_condition_name = $onerow->physical_condition_name;
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

				$sku = $onerow->sku;
				// $label = "$product_name ($sku)";
				$label = "$tag ($sku)";
				
				$responseData[] =array('label' => $label, 'sku' => $sku, 'type' => 'plsproduct', 'id'=>$pid);
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$responseData));
	}

	
	public function AJautoComplete_supplier(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$keyword_search = $POST['keyword_search']??'';
		$fieldIdName = $POST['fieldIdName']??'';
		$extrastr = "";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searchs = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searchs[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searchs) ) {
				$extrastr .= " AND TRIM(CONCAT_WS(' ', ";
				if($fieldIdName=='posupplier'){$extrastr .= " s.company, s.contact_no, s.email";}
				else{$extrastr .= " company, contact_no, email";}
				$extrastr .= " )) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searchs[$num]);
				$num++;
			}
		}
		
		$responseData = array();
		if($fieldIdName=='posupplier'){
			$supplierSql = "SELECT s.suppliers_id, s.company, s.contact_no, s.email FROM suppliers s, po WHERE po.accounts_id = $accounts_id $extrastr AND po.po_publish = 1 AND po.supplier_id = s.suppliers_id GROUP BY po.supplier_id ORDER BY s.company ASC, s.email ASC";
		}
		else{
			$supplierSql = "SELECT suppliers_id, company, contact_no, email FROM suppliers WHERE accounts_id = $prod_cat_man $extrastr AND suppliers_publish = 1 ORDER BY company ASC, email ASC";
		}
		$supplierObj = $this->db->querypagination($supplierSql, $bindData);
		if($supplierObj){
			foreach($supplierObj as $onerow){
				$company = stripslashes($onerow['company']);
				$scontact_no = $onerow['contact_no'];
				$semail = $onerow['email'];
				$label = "$company";
				
				if($scontact_no !=''){$label .= " ($scontact_no)";}
				elseif($semail !=''){$label .= " ($semail)";}

				$responseData[] =array('id' => $onerow['suppliers_id'], 'contact_no' => $onerow['contact_no'], 'email' => $onerow['email'], 'label' => $label);
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$responseData));
	}
	
	public function AJautoComplete_cartProduct(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$results = array();
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$keyword_search = addslashes(trim((string) $POST['keyword_search']??''));
		$frompage = $GLOBALS['segment4name'];
		//===========For only IMEI in Inventory===========//
		if($frompage != 'Purchase_orders'){
			$bindData = array();
			$sql = "SELECT item.item_number, manufacturer.name AS manufacture, product.product_name, product.colour_name, product.storage, product.physical_condition_name 
					FROM item, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) 
					WHERE item.accounts_id = $accounts_id AND product.product_id = item.product_id 
						AND item.in_inventory = 1 AND item.item_publish = 1 AND product.product_publish = 1";
			
			$keyword_searchData = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searchData[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searchData) ) {
				$sql .= " AND TRIM(CONCAT_WS(' ', item.item_number, product.sku, manufacturer.name, product.product_name, product.colour_name, product.storage, product.physical_condition_name)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searchData[$num]);
				$num++;
			}			 
			$sql .= " GROUP BY item.item_id ORDER BY manufacturer.name ASC, product.product_name ASC, product.colour_name ASC, product.storage ASC, product.physical_condition_name ASC, item_number ASC";
			$query = $this->db->querypagination($sql, $bindData);
			if($query){					
				foreach($query as $onerow){
					$name = stripslashes(trim((string) $onerow['manufacture']));
					$product_name = stripslashes($name.' '.$onerow['product_name']);
					
					$colour_name = $onerow['colour_name'];
					if($colour_name !=''){$product_name .= ' '.$colour_name;}
					
					$storage = $onerow['storage'];
					if($storage !=''){$product_name .= ' '.$storage;}
					
					$physical_condition_name = $onerow['physical_condition_name'];
					if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

					$item_number = $onerow['item_number'];
					$stockQty = 1;
					$str1 = trim((string) "$product_name - IMEI $item_number");
			
					$results[] = array('label'=>$str1, 'labelval'=>$item_number,'stockQty'=>$stockQty, 'am'=>'');
				}
			}
		}
	
		//===========For Mobile Product / SKU===============//
		$sql = "SELECT p.product_id, p.sku, p.product_type, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.manage_inventory_count, COUNT(item.item_id) AS stockQty 
				FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id)";
		if($frompage == 'POS'){$sql .= " INNER";}
		else{$sql .= " LEFT";}
		$sql .= " JOIN item ON (p.product_id = item.product_id AND item.in_inventory = 1 AND item.item_publish = 1) 
				WHERE i.accounts_id = $accounts_id AND p.product_type = 'Live Stocks'";
		
		$keyword_searchData = explode (" ", $keyword_search);
		if ( strpos($keyword_search, " ") === false ) {$keyword_searchData[0] = $keyword_search;}
		$bindData = array();
		$num = 0;
		while ( $num < sizeof($keyword_searchData) ) {
			$sql .= " AND TRIM(CONCAT_WS(' ', p.sku, manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name)) LIKE CONCAT('%', :keyword_search$num, '%')";
			$bindData['keyword_search'.$num] = trim((string) $keyword_searchData[$num]);
			$num++;
		}
		$sql .= " AND i.product_id = p.product_id AND p.product_publish = 1 GROUP BY p.sku";				
		$query1 = $this->db->querypagination($sql, $bindData);
		if($query1){
			foreach($query1 as $onerow){
				$name = stripslashes((string)$onerow['manufacture']);
				$product_name = stripslashes($name.' '.$onerow['product_name']);
				
				$colour_name = $onerow['colour_name'];
				if($colour_name !=''){$product_name .= ' '.$colour_name;}
				
				$storage = $onerow['storage'];
				if($storage !=''){$product_name .= ' '.$storage;}
				
				$physical_condition_name = $onerow['physical_condition_name'];
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

				$sku = $onerow['sku'];
				$product_type = $onerow['product_type'];
				$label = trim((string) "$product_name - SKU $sku");
				$manage_inventory_count = intval($onerow['manage_inventory_count']);
				$stockQty = $onerow['stockQty'];
			
				$results[] = array('label'=>$label,'labelval'=>$sku,'stockQty'=>$stockQty, 'am'=>'');
			}
		}
		
		//============For Not Live Stocks===========//
		$sql = "SELECT p.product_id, p.sku, p.product_type, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.manage_inventory_count, i.current_inventory 
				FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id";
		if($frompage == 'Purchase_orders'){
			$sql .= " AND p.product_type = 'Standard'";
		}
		else{
			$sql .= " AND p.product_type != 'Live Stocks'";
		}
		if($frompage == 'POS'){
			$sql .= " AND ((p.manage_inventory_count = 0 OR p.manage_inventory_count is null) OR (p.manage_inventory_count=1 AND i.current_inventory>0) OR p.allow_backorder = 1)"; 
		}			
		
		$keyword_searchData = explode (" ", $keyword_search);
		if ( strpos($keyword_search, " ") === false ) {$keyword_searchData[0] = $keyword_search;}
		$bindData = array();
		$num = 0;
		while ( $num < sizeof($keyword_searchData) ) {
			$sql .= " AND TRIM(CONCAT_WS(' ', p.sku, manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name)) LIKE CONCAT('%', :keyword_search$num, '%')";
			$bindData['keyword_search'.$num] = trim((string) $keyword_searchData[$num]);
			$num++;
		}
		$sql .= " AND i.product_id = p.product_id AND p.product_publish = 1 GROUP BY p.sku";				
		$query1 = $this->db->querypagination($sql, $bindData);
		if($query1){
			foreach($query1 as $onerow){
				$name = stripslashes(trim((string) $onerow['manufacture']));
				$product_name = stripslashes($name.' '.$onerow['product_name']);
				
				$colour_name = $onerow['colour_name'];
				if($colour_name !=''){$product_name .= ' '.$colour_name;}
				
				$storage = $onerow['storage'];
				if($storage !=''){$product_name .= ' '.$storage;}
				
				$physical_condition_name = $onerow['physical_condition_name'];
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

				$sku = $onerow['sku'];
				$product_type = $onerow['product_type'];
				$label = trim((string) "$product_name - SKU $sku");
				$manage_inventory_count = intval($onerow['manage_inventory_count']);
				if($manage_inventory_count==1){
					$stockQty = $onerow['current_inventory'];
				}
				else{
					$stockQty = "*";
				}
				
				$results[] = array('label'=>$label,'labelval'=>$sku,'stockQty'=>$stockQty, 'am'=>'');
			}
		}
		
		if(!empty($results)){
			usort($results, $this->build_sorter('label'));
		}
		return json_encode(array('login'=>'', 'returnStr'=>$results));
	}
		
	public function AJautoComplete_IMEI($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_cart_id = intval($POST['pos_cart_id']??0);		
		$keyword_search = addslashes($POST['keyword_search']);
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$autoCompleteData = array();
		if($frompage=='Inventory_Transfer'){
			$pos_cartObj = $this->db->query("SELECT product_id FROM po_items WHERE po_items_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
			if($pos_cartObj){
				$product_id = $pos_cartObj->fetch(PDO::FETCH_OBJ)->product_id;
				
				$sqlitem = "SELECT item_number FROM item WHERE product_id = $product_id AND accounts_id = $accounts_id AND item_number LIKE CONCAT('%', :keyword_search, '%') AND in_inventory = 1 ORDER BY item_number ASC";
				$itemquery = $this->db->query($sqlitem, array('keyword_search'=>$keyword_search));
				if($itemquery){
					while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
						$autoCompleteData[] = array('label'=>$itemrow->item_number, 'id'=>$pos_cart_id);
					}
				}
			}
		}
		else{
			$pos_cartObj = $this->db->query("SELECT item_id FROM pos_cart WHERE pos_cart_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
			if($pos_cartObj){
				$product_id = $pos_cartObj->fetch(PDO::FETCH_OBJ)->item_id;
				
				$sqlitem = "SELECT item_number FROM item WHERE product_id = $product_id AND accounts_id = $accounts_id AND item_number LIKE CONCAT('%', :keyword_search, '%') AND in_inventory = 1 ORDER BY item_number ASC";
				$itemquery = $this->db->query($sqlitem, array('keyword_search'=>$keyword_search));
				if($itemquery){
					while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
						$autoCompleteData[] = array('label'=>$itemrow->item_number, 'id'=>$pos_cart_id);
					}
				}
			}

		}
		return json_encode(array('login'=>'', 'returnStr'=>$autoCompleteData));
	}
		
	public function AJautoComplete_customer_name(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$keyword_search = $POST['keyword_search']??'';
		$frompage = $POST['frompage']??'';
		$fromcustomers_id = intval($POST['fromcustomers_id']??0);
		$extrastr = "";
		if($frompage=='Customers' && $fromcustomers_id>0){$extrastr .= " AND customers_id != $fromcustomers_id";}
		if($frompage=='Accounts_Receivables'){$extrastr .= " AND credit_limit = 0";}
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searchs = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searchs[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searchs) ) {
				$extrastr .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searchs[$num]);
				$num++;
			}
		}
		
		$customers_results = array();
		$customerssql = "SELECT company, first_name, last_name, email, contact_no, credit_limit, customers_id, alert_message FROM customers WHERE accounts_id = $prod_cat_man AND customers_publish = 1 $extrastr";
		$customersquery = $this->db->query($customerssql, $bindData);
		if($customersquery){
			while($onerow = $customersquery->fetch(PDO::FETCH_OBJ)){
				$customers_id = $onerow->customers_id;
				
				$name = trim((string) stripslashes($onerow->company));
				$email = trim((string) stripslashes($onerow->email));
				$contact_no = trim((string) stripslashes($onerow->contact_no));
				$first_name = trim((string) stripslashes($onerow->first_name));
				if($name !=''){$name .= ', ';}
				$name .= $first_name;
				$last_name = trim((string) stripslashes($onerow->last_name));
				if($name !=''){$name .= ' ';}
				$name .= $last_name;
				
				if($email !=''){
					$name .= " ($email)";
				}
				elseif($contact_no !=''){
					$name .= " ($contact_no)";
				}
				$credit_limit = $onerow->credit_limit;
				$alert_message = trim((string) stripslashes($onerow->alert_message));
				//======================Here calculate all invoiced credit ===============//
				$customers_results[] =array('id' => $customers_id,
											'email' => $email,
											'contact_no' => $contact_no,
											'crlimit' => $credit_limit,
											'am' => $alert_message,
											'label' => $name
											);
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$customers_results));
	}
		
	public function AJautoComplete_invoice_no(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$results = array();
		$accounts_id = $_SESSION["accounts_id"]??0;
		$invoice_no = trim((string) $POST['keyword_search']);

		$sql = "SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id AND invoice_no LIKE CONCAT('%', :invoice_no, '%') AND (pos_type = 'Sale' OR (pos_type IN ('Order', 'Repairs') AND order_status = 2)) AND pos_publish = 1 ORDER BY invoice_no ASC";
		$query = $this->db->query($sql, array('invoice_no'=>$invoice_no),1);
		if($query){
			while($onerow=$query->fetch(PDO::FETCH_OBJ)){
				$results[] = array('label'=>$onerow->invoice_no);
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$results));		
	}


	public function AJautoComplete_productNew(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$keyword_search = $POST['keyword_search']??'';
		$fieldIdName = $POST['fieldIdName']??'';
		$extrastr = "";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searchs = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searchs[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searchs) ) {
				// $extrastr .= " AND TRIM(CONCAT_WS(' ', ";
				// if($fieldIdName=='posupplier'){$extrastr .= " s.company, s.contact_no, s.email";}
				// else{$extrastr .= " company, contact_no, email";}
				// $extrastr .= " company, contact_no, email";
				$extrastr .= " LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searchs[$num]);
				$num++;
			}
		}
		
		$responseData = array();
		if($fieldIdName=='posupplier'){
			// $supplierSql = "SELECT s.suppliers_id, s.company, s.contact_no, s.email FROM suppliers s, po WHERE po.accounts_id = $accounts_id $extrastr AND po.po_publish = 1 AND po.supplier_id = s.suppliers_id GROUP BY po.supplier_id ORDER BY s.company ASC, s.email ASC";
			$pedigreeSql = "SELECT s.product_id, s.product_name FROM product s, item WHERE item.product_id = $accounts_id $extrastr AND po.item_publish = 1 AND item.product_id = s.product_id GROUP BY item.product_id ORDER BY s.name ASC, s.product_id ASC";
		}
		else{
			$pedigreeSql = "SELECT product_id, product_name, category_id FROM product WHERE accounts_id = $prod_cat_man $extrastr AND product_publish = 1 ORDER BY product_name ASC, product_id ASC";
		}
		$pedigreeObj = $this->db->querypagination($pedigreeSql, $bindData);
		if($pedigreeObj){
			foreach($pedigreeObj as $onerow){
				$product_name = stripslashes($onerow['product_name']);
				$product_id = $onerow['product_id'];
				$category_id = $onerow['category_id'];
				$label = "$product_name";
				
				if($product_id !=''){$label .= " ($product_id)";}
				elseif($category_id !=''){$label .= " ($category_id)";}

				$responseData[] =array('id' => $onerow['product_id'], 'product_name' => $onerow['product_name'], 'category_id' => $onerow['category_id'], 'label' => $label);
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$responseData));
	}
	
	public function getTaxesData($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$taxes_id = intval($POST['taxes_id']??0);
		$pos_id = intval($POST['pos_id']??0);
		$taxid = intval($POST['taxid']??0);
		$fieldName = "taxes_id$taxid";
		
		$taxesData = array();
		$taxesData['login'] = '';
		$taxesData['taxes_id'] = '';
		$taxesData['taxes_name'] = '';
		$taxesData['taxes_percentage'] = '';
		$taxesData['default_tax'] = '';
		$taxesData['tax_inclusive'] = '';
		$returnData['login'] = '';
		if(!isset($_SESSION["accounts_id"])){
			$returnData['login'] = 'session_ended';
			$accounts_id = 0;
		}
		else{
			$accounts_id = $_SESSION["accounts_id"]??0;
		}
		if($accounts_id>0){
			$taxesObj = $this->db->query("SELECT * FROM taxes WHERE taxes_id = :taxes_id AND accounts_id = $accounts_id ", array('taxes_id'=>$taxes_id),1);
			if($taxesObj){
				$taxesRow = $taxesObj->fetch(PDO::FETCH_OBJ);
				
				$taxesData['taxes_id'] = $taxes_id;
				$taxesData['taxes_name'] = trim((string) $taxesRow->taxes_name);
				$taxesData['taxes_percentage'] = trim((string) $taxesRow->taxes_percentage);
				$taxesData['default_tax'] = trim((string) $taxesRow->default_tax);
				$taxesData['tax_inclusive'] = trim((string) $taxesRow->tax_inclusive);
			}
		}
		
		if($pos_id==0){
			$pos_id = $_SESSION["pos_id"]??0;
		}
		$taxesData['pos_id'] = $pos_id;
		if($pos_id>0){
			$updateData = array();
			$updateData['last_updated'] = date('Y-m-d H:i:s');
			
			if(in_array($fieldName, array('taxes_id1', 'taxes_id2'))){
				$taxes_name = $taxesData['taxes_name'];
				$taxes_name = $this->db->checkCharLen('pos.taxes_name'.$taxid, $taxes_name);
				
				$updateData['taxes_name'.$taxid] = $taxes_name;
				$updateData['taxes_percentage'.$taxid] = $taxesData['taxes_percentage'];
				$updateData['tax_inclusive'.$taxid] = $taxesData['tax_inclusive'];
				$taxesData['action'] = 'Updated';

				$posObj = $this->db->querypagination("SELECT * FROM pos WHERE accounts_id = $accounts_id AND pos_id = $pos_id", array());

				$taxesData['test'] = '1';
				$updatetable = $this->db->update('pos', $updateData, $pos_id);
				if($updatetable){
					$record_for='pos';
					$record_id = $pos_id;
					$changed = array();
					unset($updateData['last_updated']);
					foreach($updateData as $fieldName=>$fieldValue){
						$prevFieldVal = $posObj[0][$fieldName];
						if($prevFieldVal != $fieldValue){
							$changed[$fieldName] = array($prevFieldVal, $fieldValue);
						}
					}
					
					if(!empty($changed)){
						$moreInfo = array();
						$teData = array();
						$teData['created_on'] = date('Y-m-d H:i:s');
						$teData['accounts_id'] = $_SESSION["accounts_id"];
						$teData['user_id'] = $_SESSION["user_id"];
						$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', $record_for);
						$teData['record_id'] = $record_id;
						$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
						$this->db->insert('track_edits', $teData);
					}
				}
			}
		}
		
		return json_encode($taxesData);
	}
	
	public function updatePosTax($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;			
		$pos_id = intval($POST['pos_id']??0);
		
		$taxes_name1 = $POST['taxes_name1']??'';
		$taxes_name1 = $this->db->checkCharLen('pos.taxes_name1', $taxes_name1);
		$taxes_percentage1 = $POST['taxes_percentage1']??0;
		$tax_inclusive1 = intval($POST['tax_inclusive1']??0);
		
		$taxes_name2 = $POST['taxes_name2']??'';
		$taxes_name2 = $this->db->checkCharLen('pos.taxes_name2', $taxes_name2);
		$taxes_percentage2 = $POST['taxes_percentage2']??0;
		$tax_inclusive2 = intval($POST['tax_inclusive2']??0);
		
		$returnval = 0;
		$updatePOSData = array(	'last_updated'=>date('Y-m-d H:i:s'),
								'taxes_name1'=>$taxes_name1,
								'taxes_percentage1'=>$taxes_percentage1,
								'tax_inclusive1'=>$tax_inclusive1,
								'taxes_name2'=>$taxes_name2,
								'taxes_percentage2'=>$taxes_percentage2,
								'tax_inclusive2'=>$tax_inclusive2
								);

		$posObj = $this->db->querypagination("SELECT * FROM pos WHERE accounts_id = $accounts_id AND pos_id = $pos_id", array());

		$updatetable = $this->db->update('pos', $updatePOSData, $pos_id);
		if($updatetable && $accounts_id !=''){
			$taxes_id1 = 0;
			$taxObj = $this->db->querypagination("SELECT taxes_id FROM taxes WHERE accounts_id = $accounts_id AND taxes_name = '$taxes_name1' AND taxes_percentage = $taxes_percentage1 ORDER BY taxes_name ASC LIMIT 0,1", array());
			if($taxObj){
				$taxes_id1 = $taxObj[0]['taxes_id'];
			}
			$_SESSION["taxes_id1"] = $taxes_id1;
			
			$taxes_id2 = 0;
			$taxObj = $this->db->querypagination("SELECT taxes_id FROM taxes WHERE accounts_id = $accounts_id AND taxes_name = '$taxes_name2' AND taxes_percentage = $taxes_percentage2 ORDER BY taxes_name ASC LIMIT 0,1", array());
			if($taxObj){
				$taxes_id2 = $taxObj[0]['taxes_id'];
			}
			$_SESSION["taxes_id2"] = $taxes_id2;
			
			$record_for='pos';
			$record_id = $pos_id;
			$repairs_id = 0;
			$repairsObj = $this->db->query("SELECT repairs_id FROM repairs WHERE pos_id = :pos_id AND accounts_id = $accounts_id", array('pos_id'=>$pos_id),1);
			if($repairsObj){
				$repairs_id = $repairsObj->fetch(PDO::FETCH_OBJ)->repairs_id;
				$this->db->update('repairs', array('last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
				$record_for='repairs';
				$record_id = $repairs_id;
			}
			
			$changed = array();
			unset($updatePOSData['last_updated']);
			foreach($updatePOSData as $fieldName=>$fieldValue){
				$prevFieldVal = $posObj[0][$fieldName];
				if($prevFieldVal != $fieldValue){
					$changed[$fieldName] = array($prevFieldVal, $fieldValue);
				}
			}
			
			if(!empty($changed)){
				$moreInfo = array();
				$teData = array();
				$teData['created_on'] = date('Y-m-d H:i:s');
				$teData['accounts_id'] = $_SESSION["accounts_id"];
				$teData['user_id'] = $_SESSION["user_id"];
				$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', $record_for);
				$teData['record_id'] = $record_id;
				$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
				$this->db->insert('track_edits', $teData);
			}
			
			$returnval++;
		}
		
		return json_encode(array('login'=>'', 'responseData'=>$returnval));
	}	

	//========================Notes========================//
	public function AJget_notesData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$returnStr = array();
		$note_for = $POST['note_for']??'';
		$table_id = intval($POST['table_id']??0);
		
		if($note_for=='pos' && $table_id==0 && isset($_SESSION["pos_id"])){
			$table_id = $_SESSION["pos_id"];
		}
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['editPer'] = 1;
		$jsonResponse['tabledata'] = array();
		$mainUserId = 0;
		$usersObj3 = $this->db->query("SELECT user_id FROM user WHERE accounts_id = $accounts_id AND user_publish = 1 AND is_admin = 1", array());
		if($usersObj3){
			$mainUserId = $usersObj3->fetch(PDO::FETCH_OBJ)->user_id;
		}
		$jsonResponse['mainUserId'] = $mainUserId;
		$jsonResponse['user_id'] = $_SESSION["user_id"]??0;

		if($table_id>0){
			$Notes = new Notes($this->db);
			$Notes->note_for = $note_for;
			$Notes->table_id = $table_id;		
			$jsonResponse['tabledata'] = $Notes->showNotesData(1);
		}
		return json_encode($jsonResponse);
	}
	
	public function AJget_notesPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$notes_id = intval($POST['notes_id']??0);
		$notesData = array();
		$notesData['login'] = '';
		$notesData['notes_id'] = 0;
		$notesData['publics'] = 0;
		$notesData['note'] = '';
		$notesObj = $this->db->query("SELECT notes_id, publics, note FROM notes WHERE notes_id = :notes_id AND accounts_id = $accounts_id", array('notes_id'=>$notes_id),1);
		if($notesObj){
			$notesObj = $notesObj->fetch(PDO::FETCH_OBJ);

			$notesData['notes_id'] = intval($notesObj->notes_id);
			$notesData['publics'] = intval($notesObj->publics);
			$notesData['note'] = trim((string) stripslashes($notesObj->note));
		}
		return json_encode($notesData);
	}
	
	public function AJsave_notes(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$returnStr = '';
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		if(isset($POST)){
			$notes_id = intval($POST['notes_id']??0);
			$note_for = $this->db->checkCharLen('notes.note_for', $POST['note_for']??'');
			$table_id = intval($POST['table_id']??0);
			$publics = intval($POST['publics']??0);
			$note = addslashes($POST['note']??'');
			
			if($note_for=='pos' && $table_id==0){
				
				if(isset($_SESSION["pos_id"]) && $_SESSION["pos_id"]>0){$table_id = $_SESSION["pos_id"];}
				else{
					
					$user_id = $employee_id = $_SESSION["user_id"];
					$customer_id = 0;
					if($customer_id == 0 && isset($_SESSION["customer_id"])){
						$customer_id = $_SESSION["customer_id"];
					}
					if(isset($_SESSION["employee_id"])){
						$employee_id = $_SESSION["employee_id"];
					}
					if($customer_id==0 || $customer_id==''){
						$customerObj = $this->db->query("SELECT default_customer FROM accounts WHERE accounts_id = $accounts_id", array());
						if($customerObj){
							$customer_id = $customerObj->fetch(PDO::FETCH_OBJ)->default_customer;
						}
					}
					$taxes_name1 = $taxes_name2 = '';
					$taxes_percentage1 = $tax_inclusive1 = $taxes_percentage2 = $tax_inclusive2 = 0;
					if(isset($_SESSION["taxes_id1"])){
						$taxes_id1 = $_SESSION["taxes_id1"];
						if($taxes_id1>0){
							$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = $taxes_id1 ORDER BY taxes_id DESC LIMIT 0, 1", array());
							if($taxesObj){
								$taxes_name1 = $taxesObj[0]['taxes_name'];
								$taxes_percentage1 = $taxesObj[0]['taxes_percentage'];
								$tax_inclusive1 = $taxesObj[0]['tax_inclusive'];
							}				
						}
					}
					if(isset($_SESSION["taxes_id2"])){
						$taxes_id2 = $_SESSION["taxes_id2"];
						if($taxes_id2>0){
							$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = $taxes_id2 ORDER BY taxes_id DESC LIMIT 0, 1", array());
							if($taxesObj){
								$taxes_name2 = $taxesObj[0]['taxes_name'];
								$taxes_percentage2 = $taxesObj[0]['taxes_percentage'];
								$tax_inclusive2 = $taxesObj[0]['tax_inclusive'];
							}				
						}
					}
					
					if($taxes_name1 =='' || $taxes_name2 == ''){
						$taxesObj = $this->db->query("SELECT * FROM taxes WHERE accounts_id = $accounts_id AND taxes_publish = 1 ORDER BY taxes_name ASC", array());
						if($taxesObj){
							$no_of_default_rows = 0;
							while($taxesonerow = $taxesObj->fetch(PDO::FETCH_OBJ)){
								
								$staxes_name = $taxesonerow->taxes_name;
								$staxes_percentage = $taxesonerow->taxes_percentage;							
								$default_tax = $taxesonerow->default_tax;
								$tax_inclusive = $taxesonerow->tax_inclusive;
								if($taxes_name1==''){
									$taxes_name1 = $staxes_name;
									$taxes_percentage1 = $staxes_percentage;
									$tax_inclusive1 = $tax_inclusive;
								}
								
								if($default_tax>0){
									$no_of_default_rows++;
									if($no_of_default_rows==1 && $taxes_name1==''){
										$taxes_name1 = $staxes_name;
										$taxes_percentage1 = $staxes_percentage;
										$tax_inclusive1 = $tax_inclusive;
									}
									
									if($no_of_default_rows==2 && $taxes_name2==''){
										$taxes_name2 = $staxes_name;
										$taxes_percentage2 = $staxes_percentage;
										$tax_inclusive2 = $tax_inclusive;
									}
								}
							}
						}
					}
					
					$posData = array('invoice_no' => 0, 
									'sales_datetime' => date('Y-m-d H:i:s'), 
									'employee_id' => $employee_id, 
									'customer_id' => $customer_id, 
									'taxes_name1' => $taxes_name1,
									'taxes_percentage1' => $taxes_percentage1,
									'tax_inclusive1' => $tax_inclusive1,
									'taxes_name2' => $taxes_name2,
									'taxes_percentage2' => $taxes_percentage2,
									'tax_inclusive2' => $tax_inclusive2,
									'pos_type' => 'Sale', 
									'created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'user_id' => $user_id, 
									'accounts_id' => $accounts_id, 
									'pos_publish' => 0, 
									'credit_days' => 0, 
									'is_due' => 0, 
									'status' => 'New');
	
					$pos_id = $this->db->insert('pos', $posData);
					$_SESSION["pos_id"] = $table_id = $pos_id;
				}
			}
			
			if($table_id>0 && !empty($note_for)){
				$noteData = array();
				$noteData['table_id'] = $table_id;
				$noteData['note_for'] = $note_for;
				$noteData['last_updated'] =date('Y-m-d H:i:s');
				$noteData['accounts_id'] = $accounts_id;			
				$noteData['user_id'] = $_SESSION["user_id"];
				$noteData['note'] = $note;
				$noteData['publics'] = $publics;
				
				if($notes_id==0){
					$noteData['created_on'] = date('Y-m-d H:i:s');
					$notes_id = $this->db->insert('notes', $noteData);
					if($notes_id){
						if(!in_array($note_for, array('expenses', 'commissions', 'stock_take'))){
							$updaterepairs = $this->db->update($note_for, array('last_updated'=>date('Y-m-d H:i:s')), $table_id);
						}
						$savemsg = 'Add';
					}
					else{
						$returnStr = 'errorOnAdding';
					}
				}
				else{
					$update = $this->db->update('notes', $noteData, $notes_id);
					if($update){
						if(!in_array($note_for, array('expenses', 'commissions', 'stock_take'))){
							$updaterepairs = $this->db->update($note_for, array('last_updated'=>date('Y-m-d H:i:s')), $table_id);
						}
						$savemsg = 'Add';
					}
				}
			}
		}
		return json_encode(array('login'=>'', 'savemsg'=>$savemsg, 'returnStr'=>$returnStr));
	}	
	
	public function AJsave_digitalSignature(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		
		$savemsg = $returnStr = '';
		$digital_signature_id = intval($POST['digital_signature_id']??0);
		$for_table = $POST['for_table']??'';
		$note = $POST['note']??'';
		$table_id = intval($POST['table_id']??0);
		
		if($for_table=='pos' && $table_id==0){
			
			if(isset($_SESSION["pos_id"])){$table_id = $_SESSION["pos_id"];}
			else{
				
				$customer_id = $_SESSION["customer_id"]??0;
				$employee_id = $_SESSION["employee_id"]??$user_id;
				
				if($customer_id==0 || $customer_id==''){
					$customerObj = $this->db->query("SELECT default_customer FROM accounts WHERE accounts_id = $accounts_id", array());
					if($customerObj){
						$customer_id = $customerObj->fetch(PDO::FETCH_OBJ)->default_customer;
					}
				}
				$taxes_name1 = $taxes_name2 = '';
				$taxes_percentage1 = $tax_inclusive1 = $taxes_percentage2 = $tax_inclusive2 = 0;
				if(isset($_SESSION["taxes_id1"])){
					$taxes_id1 = $_SESSION["taxes_id1"];
					if($taxes_id1>0){
						$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = $taxes_id1 ORDER BY taxes_id DESC LIMIT 0, 1", array());
						if($taxesObj){
							$taxes_name1 = $taxesObj[0]['taxes_name'];
							$taxes_percentage1 = $taxesObj[0]['taxes_percentage'];
							$tax_inclusive1 = $taxesObj[0]['tax_inclusive'];
						}				
					}
				}
				if(isset($_SESSION["taxes_id2"])){
					$taxes_id2 = $_SESSION["taxes_id2"];
					if($taxes_id2>0){
						$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = $taxes_id2 ORDER BY taxes_id DESC LIMIT 0, 1", array());
						if($taxesObj){
							$taxes_name2 = $taxesObj[0]['taxes_name'];
							$taxes_percentage2 = $taxesObj[0]['taxes_percentage'];
							$tax_inclusive2 = $taxesObj[0]['tax_inclusive'];
						}				
					}
				}
				
				if($taxes_name1 =='' || $taxes_name2 == ''){
					$taxesObj = $this->db->query("SELECT * FROM taxes WHERE accounts_id = $accounts_id AND taxes_publish = 1 ORDER BY taxes_name ASC", array());
					if($taxesObj){
						$no_of_default_rows = 0;
						while($taxesonerow = $taxesObj->fetch(PDO::FETCH_OBJ)){
							
							$staxes_name = $taxesonerow->taxes_name;
							$staxes_percentage = $taxesonerow->taxes_percentage;							
							$default_tax = $taxesonerow->default_tax;
							$tax_inclusive = $taxesonerow->tax_inclusive;
							if($taxes_name1==''){
								$taxes_name1 = $staxes_name;
								$taxes_percentage1 = $staxes_percentage;
								$tax_inclusive1 = $tax_inclusive;
							}
							
							if($default_tax>0){
								$no_of_default_rows++;
								if($no_of_default_rows==1 && $taxes_name1==''){
									$taxes_name1 = $staxes_name;
									$taxes_percentage1 = $staxes_percentage;
									$tax_inclusive1 = $tax_inclusive;
								}
								
								if($no_of_default_rows==2 && $taxes_name2==''){
									$taxes_name2 = $staxes_name;
									$taxes_percentage2 = $staxes_percentage;
									$tax_inclusive2 = $tax_inclusive;
								}
							}
						}
					}
				}
				
				$posData = array('invoice_no' => 0, 
								'sales_datetime' => date('Y-m-d H:i:s'), 
								'employee_id' => $employee_id, 
								'customer_id' => $customer_id, 
								'taxes_name1' => $taxes_name1,
								'taxes_percentage1' => $taxes_percentage1,
								'tax_inclusive1' => $tax_inclusive1,
								'taxes_name2' => $taxes_name2,
								'taxes_percentage2' => $taxes_percentage2,
								'tax_inclusive2' => $tax_inclusive2,
								'pos_type' => 'Sale', 
								'created_on' => date('Y-m-d H:i:s'),
								'last_updated' => date('Y-m-d H:i:s'),
								'user_id' => $user_id, 
								'accounts_id' => $accounts_id, 
								'pos_publish' => 0, 
								'credit_days' => 0, 
								'is_due' => 0, 
								'status' => 'New');

				$pos_id = $this->db->insert('pos', $posData);
				$_SESSION["pos_id"] = $table_id = $pos_id;
			}
		}
		
		if($note !=''){
			$for_table = $this->db->checkCharLen('digital_signature.for_table', $for_table);
			$noteData = array();
			$noteData['for_table'] = $for_table;
			$noteData['table_id'] = $table_id;
			$noteData['note'] = addslashes($note);
			$noteData['user_id'] = $user_id;
			$noteData['accounts_id'] = $accounts_id;			
			
			if($digital_signature_id==0){
				$noteData['created_on'] = date('Y-m-d H:i:s');
				$noteData['last_updated'] =date('Y-m-d H:i:s');
				$digital_signature_id = $this->db->insert('digital_signature', $noteData);
				if($digital_signature_id){
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('digital_signature', $noteData, $digital_signature_id);
				if($update){
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnEditing';
				}
			}
		}
	
		$returnData = array( 'login'=>'', 'returnStr'=>$returnStr, 'id'=>$digital_signature_id, 'note'=>$note, 'savemsg'=>$savemsg);
		return json_encode($returnData);
	}
	
	//==========Time Clock=================//		
	public function checkValidEmpNumber(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$employeeData = array();
		$employeeData['login'] = '';
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$userEmpNo = $POST['userEmpNo']??'';
		
		$employeeData['showDateTime'] = date('Y-m-d H:i:s');
		$employeeData['validEmpNumber'] = 0;
		$employeeData['user_id'] = 0;
		$employeeData['time_clock_id'] = 0;
		$employeeData['clockinorout'] = '1';
		
		if(!empty($userEmpNo)){
			$employeeObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id AND employee_number = BINARY :userEmpNo AND user_publish = 1", array('userEmpNo'=>$userEmpNo));
			if($employeeObj){
				$employeeRow = $employeeObj->fetch(PDO::FETCH_OBJ);
				$employeeData['user_id'] = $employeeRow->user_id;
				$employeeData['validEmpNumber'] = 1;
				$employeeData['empName'] = trim(stripslashes("$employeeRow->user_first_name $employeeRow->user_last_name"));
				
				$time_clockObj = $this->db->query("SELECT time_clock_id FROM time_clock WHERE accounts_id = $accounts_id AND user_id = $employeeRow->user_id AND clocked_out IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00')", array());
				if($time_clockObj){
					$time_clockRow = $time_clockObj->fetch(PDO::FETCH_OBJ);
					$employeeData['time_clock_id'] = $time_clockRow->time_clock_id;
					$employeeData['clockinorout'] = '2';
				}
			}
		}
		return json_encode($employeeData);
	}
	
	public function AJsave_timeClock(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = 'Error';
				
		$accounts_id = $_SESSION["accounts_id"]??0;
		$tuser_id = intval($POST['tuser_id']??0);
		$pin = $POST['pin']??'';
		$time_clock_id = intval($POST['time_clock_id']??0);
		
		$employeeObj = $this->db->query("SELECT user_id FROM user WHERE user_id = :tuser_id AND pin = :pin AND accounts_id = $accounts_id AND user_publish = 1", array('tuser_id'=>$tuser_id, 'pin'=>$pin));
		if($employeeObj){
			$time_clockData = array();
			$time_clockData['accounts_id'] = $accounts_id;
			$time_clockData['user_id'] = $tuser_id;
			if($time_clock_id>0){
				$time_clockData['clocked_out'] = date('Y-m-d H:i:00');
				$this->db->update('time_clock', $time_clockData, $time_clock_id);
				$returnStr = 'Clock_out';
			}
			else{
				$time_clockData['clocked_in'] = date('Y-m-d H:i:00');
				$time_clockData['clocked_out'] = '1000-01-01 00:00:00';
				$this->db->insert('time_clock', $time_clockData);
				$returnStr = 'Clock_in';
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}

	//==================Properties:: Brand Model================//
	public function checkAndReturnBrand($newBrand){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$newBrand = trim((string) $newBrand);
		
		$sql = "SELECT brand_model_id, brand FROM brand_model WHERE accounts_id = $prod_cat_man AND UPPER(brand) = :brand ORDER BY UPPER(TRIM(brand)) ASC";
		$brandModelObj = $this->db->query($sql, array('brand'=>strtoupper($newBrand)));
		if($brandModelObj){
			$prevBrand = trim((string) $newBrand);
			$prevBrandModelId = 0;
			while($oneRow = $brandModelObj->fetch(PDO::FETCH_OBJ)){
				$brand_model_id = $oneRow->brand_model_id;
				$brand = trim((string) $oneRow->brand);

				if(strtoupper(trim((string) $prevBrand))==strtoupper(trim((string) $brand)) && $prevBrand != $brand){
					$updatedBrand = trim((string) $prevBrand);
					if (ctype_upper(substr($brand, 0, 1))) {
						$updatedBrand = trim((string) $brand);
						if($prevBrandModelId>0){
							$this->db->update('brand_model', array('brand'=>$updatedBrand), $prevBrandModelId);
						}
					}
					else{
						$this->db->update('brand_model', array('brand'=>$updatedBrand), $brand_model_id);
					}
					$prevBrand = $updatedBrand;
				}
				else{
					$prevBrand = $brand;
				}
				$prevBrandModelId = $brand_model_id;
			}
			$newBrand = trim((string) $prevBrand);
		}
		return $newBrand;			
	}
	
	//=================Commonly Used=========//
	public function getOneRowFields($tableName, $condition, $returnFields){
		$returnStr = '';
		$filters = array();
		foreach($condition as $fieldName=>$fieldVal){$filters[] = "$fieldName='$fieldVal'";}
		$FieldSql = "SELECT ";
		if(is_array($returnFields)){$FieldSql .= implode(', ', $returnFields);}
		else{$FieldSql .= $returnFields;}
		$FieldSql .= " FROM $tableName WHERE ".implode(' AND ', $filters)." LIMIT 0, 1";		
		$FieldObj = $this->db->query($FieldSql, array());
		if($FieldObj){
			while($FieldRow = $FieldObj->fetch(PDO::FETCH_OBJ)){
				if(is_array($returnFields)){
					$fieldsVal = array();
					foreach($returnFields as $fieldName){$fieldsVal[] = $FieldRow->$fieldName;}
					$returnStr .= implode(' ', $fieldsVal);
				}
				else{$returnStr .= $FieldRow->$returnFields;}
			}
		}
		return stripslashes(trim((string) $returnStr));
	}
	
	public function build_sorter($key){
		return function ($a, $b) use ($key) {
			return strcmp($a[$key], $b[$key]);
		};
	}
	
	public function calculateTax($taxable_total, $taxes_rate, $tax_inclusive){
		$returntax = 0.00;
		$taxes_percentage = $taxes_rate*0.01;
		if($tax_inclusive>0){
			$returntax = round($taxable_total-$taxable_total/($taxes_percentage+1),2);
		}
		else{
			$returntax = round($taxable_total*$taxes_percentage,2);
		}
		return $returntax;
	}
	
    public function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
	
	public function gen_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
	
			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),
	
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,
	
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,
	
			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
	
	public function time_since($since) {
		$chunks = array(array(60 * 60 * 24 * 365 , 'year'),
						array(60 * 60 * 24 * 30 , 'month'),
						array(60 * 60 * 24 * 7, 'week'),
						array(60 * 60 * 24 , 'day'),
						array(60 * 60 , 'hour'),
						array(60 , 'minute'),
						array(1 , 'second')
					);
	
		for ($i = 0, $j = count($chunks); $i < $j; $i++) {
			$seconds = $chunks[$i][0];
			$name = $chunks[$i][1];
			if (($count = floor($since / $seconds)) != 0) {
				break;
			}
		}
	
		$print = ($count == 1) ? '1 '.$name : "$count {$name}s";
		return $print.' ago';
	}	
		
	public function uploadpicture(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$returnStr = $savemsg = '';
		
		if(!isset($_FILES) || !array_key_exists('filename', $_FILES)){
			$savemsg = 'noFile';
		}
		
		if (!is_uploaded_file($_FILES['filename']['tmp_name'])){
			$savemsg = 'invalid';
			$returnStr = $_FILES['filename']['tmp_name'];
      	}
		
		if(!isset($_SESSION["accounts_id"])){
			$returnStr = 'session_ended';
		}
		else{
			$returnStr = '';
			$fileSize = floor(filesize($_FILES['filename']['tmp_name'])/1024);//KB
			$fileSize = floor($fileSize/1024);//MB
			
			if($fileSize>6){
				$savemsg = 'largeFile';
                $returnStr = $fileSize;
			}
			
			ini_set('memory_limit', '500M');
			
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$accounts_id = $_SESSION["accounts_id"]??0;
			$frompage = $_POST['frompage'];			
			$folderpath = "./assets/accounts/a_".$accounts_id;
			if(strcmp($frompage, 'products')==0){
			    $folderpath = "./assets/accounts/a_".$prod_cat_man;
			}
			if(!is_dir($folderpath)){mkdir($folderpath, 0777);}
			
			$fileprename = $_POST['fileprename']??'';
			$oldfilename = $_POST['oldfilename']??'';

			$filename = $_FILES["filename"]["name"];
			$efilename = explode('.', $filename);
			$ext = strtolower($efilename[count($efilename) - 1]); 
			
			if(in_array(strtoupper($ext), array('PDF'))){//, 'DOC', 'DOCX', 'PPTX', 'XLSX'
				$tmpFileName = $_FILES['filename']['tmp_name'];
				$filename = $fileprename.substr(time(),7,3).".".$ext;
				$attachedpath = "$folderpath/$filename";
				
				if(!empty($oldfilename)){
					$oldattachedpath = "$folderpath/$oldfilename";
					if (file_exists($oldattachedpath)){
						if(unlink($oldattachedpath)){
							$returnStr = "'$oldfilename' file removed successfully.";
						}
					}
				}
				
				if(move_uploaded_file($tmpFileName, $attachedpath)){
					$returnStr = $filename;
				}
				else{
					$returnStr = 'Could not upload File "'. $filename.'"';
				}
			}
			else{
				$imagename = $fileprename.substr(time(),7,3).'.png';
				$width = 200;
				$height = 100;
				if($fileprename=='app_logo_'){
					$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
					if($varObj){
						$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
						$variables_id = $variablesData->variables_id;
						$value = $variablesData->value;
						if(!empty($value)){
							$value = unserialize($value);
							if(array_key_exists('logo_size', $value)){
								$logo_size = $value['logo_size'];
								if($logo_size=='Large Logo'){
									$width = 350;
									$height = 150;
								}
							}
						}
						$submit = 'Update';
					}
				}
				if(strcmp($frompage, 'products')==0){
					$width = 350;
					$height = 350;
				}
				elseif(strcmp($frompage, 'repairs')==0){
					$width = 900;
					$height = 900;
				}
				elseif(in_array($frompage, array('homepage', 'all_pages_header'))){
					$width = 600;
					$height = 200;
				}
				elseif(strcmp($frompage, 'fieldImages')==0){
					$width = 1024;
					$height = 1024;
				}
				elseif(strcmp($frompage, 'home_page_body')==0){
					$width = 750;
					$height = 600;
				}
				
				$image_info = getimagesize($_FILES['filename']['tmp_name']);
				$imageType = $image_info[2];//1=gif, 2=jpg/jpeg, 3=png, 
				if ($imageType > 3 ) {
					$savemsg = 'fileNotSupported';
				}
				$orig_width = $image_info[0];
				$orig_height = $image_info[1];
				//======Update Image Size=========//
				$source_aspect_ratio = 1;
				if($orig_height !=0){
					$source_aspect_ratio = $orig_width / $orig_height;
				}
				
				$thumbnail_aspect_ratio = 1;
				if($height !=0){
					$thumbnail_aspect_ratio = $width / $height;
				}
				if ($orig_width <= $width && $orig_height <= $height) {
					$thumbnail_image_width = $orig_width;
					$thumbnail_image_height = $orig_height;
				} elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
					$thumbnail_image_width = (int) ($height * $source_aspect_ratio);
					$thumbnail_image_height = $height;
				} else {
					$thumbnail_image_width = $width;
					$thumbnail_image_height = (int) ($width / $source_aspect_ratio);
				}
				
				$width = $thumbnail_image_width;
				$height = $thumbnail_image_height;
				
				//=========Create Image==============//
				$new_image = imagecreatetruecolor( $width, $height );
				$white = imagecolorallocate($new_image, 255, 255, 255);
				imagefill($new_image, 0, 0, $white);
				
				if ($imageType == '1' ) {
					$image = imagecreatefromgif($_FILES['filename']['tmp_name']);
				}
				elseif ($imageType == '2' ) {
					$image = imagecreatefromjpeg($_FILES['filename']['tmp_name']);
				}
				elseif ($imageType == '3' ) {
					$image = imagecreatefrompng($_FILES['filename']['tmp_name']);
				}
				else{
					$returnStr = 'File not supported ("'. $filename.'")';
					$savemsg = 'fileNotSupported';
				}
			
				if(empty($returnStr)){
					$transparent = imagecolorallocatealpha( $new_image, 0, 0, 0, 127 );
					imagefill( $new_image, 0, 0, $transparent );

					imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
					imagepng($new_image, $folderpath.'/'.$imagename, 0);
					imagedestroy($new_image);
					imagedestroy($image);
					
					$picturesrc = str_replace('./', '/', $folderpath.'/'.$imagename);
					
					//==============Image Compression and replace=================//
					if (extension_loaded('imagick')){				
						//=============Testing White BG Code=================//
						$hasAlpha = $this->hasAlpha(imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . $picturesrc));
						//var_dump($hasAlpha);
						//=============Testing White BG Code=================//
						
						$im = new Imagick($_SERVER['DOCUMENT_ROOT'] . $picturesrc);
						$im->optimizeImageLayers(); // Optimize the image layers			
						$im->setImageCompression(Imagick::COMPRESSION_JPEG);// Compression and quality
						$im->setImageCompressionQuality(0);
						
						$imagename = str_replace('.png', '.jpg', $imagename);
						$picturesrc1 = str_replace('.png', '.jpg', $picturesrc);
						$im->writeImages($_SERVER['DOCUMENT_ROOT'] . $picturesrc1, true);// Write the image back
						unlink('.'.$picturesrc); // delete file		
						$picturesrc = $picturesrc1;
					}
					$returnStr = $picturesrc;
					if(in_array($frompage, array('home_page_body', 'fieldImages'))){
						if($oldfilename !=''){
							$attachedpath1 = '.'.$oldfilename;
							if(file_exists($attachedpath1))
								unlink($attachedpath1);
						}
					}
					if(in_array($frompage, array('products'))){
						$filePath = "$folderpath/$fileprename";
						$pics = glob($filePath."*.jpg");
						if($pics){
							foreach($pics as $onePicture2){
								$prodImg = str_replace("./assets/accounts/a_$prod_cat_man/", '', $onePicture2);
								if($prodImg != $imagename){
									if(file_exists($onePicture2))
										unlink($onePicture2);
								}
							}
						}
					}
					elseif(strcmp($frompage, 'repairs')==0){
						$repairsidexp = explode('-', $fileprename);
						if(count($repairsidexp>0)){
							$ticket_no = str_replace('a_t', '', $repairsidexp[0]);
							if($ticket_no>0){
								$repairs_id = 0;
								$repairsObj = $this->db->query("SELECT repairs_id FROM repairs WHERE ticket_no = :ticket_no AND accounts_id = $accounts_id", array('ticket_no'=>$ticket_no),1);
								if($repairsObj){
									$repairs_id = $repairsObj->fetch(PDO::FETCH_OBJ)->repairs_id;
								}
								
								if($repairs_id !='' && $repairs_id>0){
									$this->db->update('repairs', array('last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
								}
							}
						}
					}
				}
			}
		}
		$jsonResponse['returnStr'] = $returnStr;
		$jsonResponse['savemsg'] = $savemsg;

		return json_encode($jsonResponse);
	}
	
	public function hasAlpha($imgdata) {
		$w = imagesx($imgdata);
		$h = imagesy($imgdata);

		if($w>50 || $h>50){ //resize the image to save processing if larger than 50px:
			$thumb = imagecreatetruecolor(10, 10);
			imagealphablending($thumb, FALSE);
			imagecopyresized( $thumb, $imgdata, 0, 0, 0, 0, 10, 10, $w, $h );
			$imgdata = $thumb;
			$w = imagesx($imgdata);
			$h = imagesy($imgdata);
		}
		//run through pixels until transparent pixel is found:
		for($i = 0; $i<$w; $i++) {
			for($j = 0; $j < $h; $j++) {
				$rgba = imagecolorat($imgdata, $i, $j);
				if(($rgba & 0x7F000000) >> 24) return true;
			}
		}
		return false;
	}
	
	public function AJremove_Picture(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		
		$repairs_id = intval($POST['repairs_id']??0);
		$picturepath = $POST['picturepath']??'';
		
		$attachedpath = '.'.$picturepath;
		if (file_exists($attachedpath)){
			unlink($attachedpath);
			if($repairs_id>0){
				$this->db->update('repairs', array('last_updated'=>date('Y-m-d H:i:s')), $repairs_id);											
			}
		}
		$returnStr = 'Ok';
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}	
	
	public function AJarchive_tableRow(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$tablename = $POST['tablename']??'';
		$tableidvalue = intval($POST['tableidvalue']??0);
		$publishname = $POST['publishname']??'';
		if($tableidvalue==0){
			$returnStr = "error";
		}
		else{
			$user_id = $_SESSION["user_id"]??0;
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$accounts_id = $_SESSION["accounts_id"]??0;
			
			$updatetable = $this->db->update($tablename, array($publishname=>0), $tableidvalue);
			if($updatetable>0){
				$conditionarray = array();
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$conditionarray['last_updated'] = date('Y-m-d H:i:s');
				$conditionarray['accounts_id'] = $_SESSION["accounts_id"];
				$conditionarray['user_id'] = $_SESSION["user_id"];
				$conditionarray['activity_feed_title'] = '';
				$conditionarray['activity_feed_name'] = '';
				$conditionarray['activity_feed_link'] = '';
				if($tablename=='category'){
					$conditionarray['activity_feed_title'] = $this->db->translate('Category archived');
					$category_name = '';
					if($tableidvalue>0){
						$categoryObj = $this->db->query("SELECT category_name FROM category WHERE category_id = :category_id AND accounts_id = $prod_cat_man", array('category_id'=>$tableidvalue),1);
						if($categoryObj){
							$category_name = $categoryObj->fetch(PDO::FETCH_OBJ)->category_name;
						}
					}
					
					$conditionarray['activity_feed_name'] = $category_name;
					$conditionarray['activity_feed_link'] = "/Manage_Data/category/view/$tableidvalue";
				}
				elseif($tablename=='manufacturer'){
					$conditionarray['activity_feed_title'] = $this->db->translate('Manufacturer archived');
					$manufacturer = '';
					if($tableidvalue>0){
						$manufacturerObj = $this->db->query("SELECT name FROM manufacturer WHERE manufacturer_id = :manufacturer_id AND accounts_id = $prod_cat_man", array('manufacturer_id'=>$tableidvalue),1);
						if($manufacturerObj){
							$manufacturer = $manufacturerObj->fetch(PDO::FETCH_OBJ)->name;
						}
					}
										
					$conditionarray['activity_feed_name'] = $manufacturer;
					$conditionarray['activity_feed_link'] = "/Manage_Data/manufacturer/view/$tableidvalue";
				}
				elseif($tablename=='taxes'){
					$conditionarray['activity_feed_title'] = $this->db->translate('Tax archived');
					$taxes = '';
					if($tableidvalue>0){
						$taxesObj = $this->db->query("SELECT taxes_name, taxes_percentage, tax_inclusive FROM taxes WHERE taxes_id = :taxes_id AND accounts_id = $accounts_id", array('taxes_id'=>$tableidvalue),1);
						if($taxesObj){
							$taxesrow = $taxesObj->fetch(PDO::FETCH_OBJ);
							$tiStr = '';
							if($taxesrow->tax_inclusive>0){$tiStr = ' Inclusive';}
				
							$taxes = trim("$taxesrow->taxes_name ($taxesrow->taxes_percentage%$tiStr)");
						}
					}
					
					$conditionarray['activity_feed_name'] = $taxes;
					$conditionarray['activity_feed_link'] = "/Getting_Started/taxes/view/$tableidvalue";
				}
				elseif($tablename=='user'){
					$conditionarray['activity_feed_title'] = $this->db->translate('User archived');
					$username = '';
					if($tableidvalue>0){
						$userObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = :user_id", array('user_id'=>$tableidvalue),1);
						if($userObj){
							$userrow = $userObj->fetch(PDO::FETCH_OBJ);
							$username = trim("$userrow->user_first_name $userrow->user_last_name");
						}
					}
					
					$conditionarray['activity_feed_name'] = $username;
					$conditionarray['activity_feed_link'] = "/Settings/setup_users/view/$tableidvalue";
				}
				
				if(!empty($conditionarray)){
					
					$activity_feed_title = $conditionarray['activity_feed_title'];
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$conditionarray['activity_feed_title'] = $activity_feed_title;
					
					$activity_feed_link = $conditionarray['activity_feed_link'];
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					$conditionarray['activity_feed_link'] = $activity_feed_link;
					
					$conditionarray['uri_table_name'] = $tablename;
					$conditionarray['uri_table_field_name'] = $publishname;
					$conditionarray['field_value'] = 0;
					
					$this->db->insert('activity_feed', $conditionarray);	
				}
				
				$returnStr = 'archive-success';
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJunArchive_tableRow(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$tablename = $POST['tablename']??'';
		$tableidvalue = intval($POST['tableidvalue']??0);
		$publishname = $POST['publishname']??'';
		if($tableidvalue==0){
			$returnStr = "error";
		}
		else{
			$user_id = $_SESSION["user_id"]??0;
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$accounts_id = $_SESSION["accounts_id"]??0;
			
			$updatetable = $this->db->update($tablename, array($publishname=>1), $tableidvalue);
			if($updatetable>0){
				$conditionarray = array();
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$conditionarray['last_updated'] = date('Y-m-d H:i:s');
				$conditionarray['accounts_id'] = $_SESSION["accounts_id"];
				$conditionarray['user_id'] = $_SESSION["user_id"];
				$conditionarray['activity_feed_title'] = '';
				$conditionarray['activity_feed_name'] = '';
				$conditionarray['activity_feed_link'] = '';
				if($tablename=='category'){
					$conditionarray['activity_feed_title'] = $this->db->translate('Category').' '.$this->db->translate('Unarchive');
					$category_name = '';
					if($tableidvalue>0){
						$categoryObj = $this->db->query("SELECT category_name FROM category WHERE category_id = :category_id AND accounts_id = $prod_cat_man", array('category_id'=>$tableidvalue),1);
						if($categoryObj){
							$category_name = $categoryObj->fetch(PDO::FETCH_OBJ)->category_name;
						}
					}
					
					$conditionarray['activity_feed_name'] = $category_name;
					$conditionarray['activity_feed_link'] = "/Manage_Data/category/view/$tableidvalue";
				}
				elseif($tablename=='manufacturer'){
					$conditionarray['activity_feed_title'] = $this->db->translate('Manufacturer').' '.$this->db->translate('Unarchive');
					$manufacturer = '';
					if($tableidvalue>0){
						$manufacturerObj = $this->db->query("SELECT name FROM manufacturer WHERE manufacturer_id = :manufacturer_id AND accounts_id = $prod_cat_man", array('manufacturer_id'=>$tableidvalue),1);
						if($manufacturerObj){
							$manufacturer = $manufacturerObj->fetch(PDO::FETCH_OBJ)->name;
						}
					}
										
					$conditionarray['activity_feed_name'] = $manufacturer;
					$conditionarray['activity_feed_link'] = "/Manage_Data/manufacturer/view/$tableidvalue";
				}
				elseif($tablename=='product'){
					$conditionarray['activity_feed_title'] = $this->db->translate('Product Unarchive');
					$product_name = '';
					if($tableidvalue>0){
						$categoryObj = $this->db->query("SELECT product_name FROM product WHERE product_id = :product_id AND accounts_id = $prod_cat_man", array('product_id'=>$tableidvalue),1);
						if($categoryObj){
							$product_name = $categoryObj->fetch(PDO::FETCH_OBJ)->product_name;
						}
					}
					
					$conditionarray['activity_feed_name'] = $product_name;
					$conditionarray['activity_feed_link'] = "/Products/view/$tableidvalue";
				}
				elseif($tablename=='taxes'){
					$conditionarray['activity_feed_title'] = $this->db->translate('Tax').' '.$this->db->translate('Unarchive');
					$taxes = '';
					if($tableidvalue>0){
						$taxesObj = $this->db->query("SELECT taxes_name, taxes_percentage, tax_inclusive FROM taxes WHERE taxes_id = :taxes_id AND accounts_id = $accounts_id", array('taxes_id'=>$tableidvalue),1);
						if($taxesObj){
							$taxesrow = $taxesObj->fetch(PDO::FETCH_OBJ);
							$tiStr = '';
							if($taxesrow->tax_inclusive>0){$tiStr = ' Inclusive';}
				
							$taxes = trim("$taxesrow->taxes_name ($taxesrow->taxes_percentage%$tiStr)");
						}
					}
					
					$conditionarray['activity_feed_name'] = $taxes;
					$conditionarray['activity_feed_link'] = "/Getting_Started/taxes/view/$tableidvalue";
				}
				elseif($tablename=='user'){
					$conditionarray['activity_feed_title'] = $this->db->translate('User').' '.$this->db->translate('Unarchive');
					$username = '';
					if($tableidvalue>0){
						$userObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = :user_id", array('user_id'=>$tableidvalue),1);
						if($userObj){
							$userrow = $userObj->fetch(PDO::FETCH_OBJ);
							$username = trim("$userrow->user_first_name $userrow->user_last_name");
						}
					}
					
					$conditionarray['activity_feed_name'] = $username;
					$conditionarray['activity_feed_link'] = "/Settings/setup_users/view/$tableidvalue";
				}
				
				if(!empty($conditionarray)){
					
					$activity_feed_title = $conditionarray['activity_feed_title'];
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$conditionarray['activity_feed_title'] = $activity_feed_title;
					
					$activity_feed_link = $conditionarray['activity_feed_link'];
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					$conditionarray['activity_feed_link'] = $activity_feed_link;
					
					$conditionarray['uri_table_name'] = $tablename;
					$conditionarray['uri_table_field_name'] = $publishname;
					$conditionarray['field_value'] = 0;
					
					$this->db->insert('activity_feed', $conditionarray);	
				}
				
				$returnStr = 'unarchive-success';
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJremove_tableRow(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = $savemsg = '';
		
		$product_id = 0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$tableName = $POST['tableName']??'';
		if($tableName == 'forms'){
			$tableIdValue = $POST['tableIdValue']??'';
		}
		else{
			$tableIdValue = intval($POST['tableIdValue']??0);
		}		
		$description = $POST['description']??'';
		
		$tableIdName = $tableName.'_id';
		$delete_tablerow = false;
		if($tableName == 'forms'){
			$formsInfo = explode('||', $tableIdValue);
			if(count($formsInfo)>1){
				$forms_id = $formsInfo[0];
				$order_val = $formsInfo[1];

				$formFieldsObj = $this->db->query("SELECT form_definitions FROM forms WHERE accounts_id = $prod_cat_man AND forms_id = :forms_id", array('forms_id'=>$forms_id),1);
				if($formFieldsObj){
					$form_definitions = $formFieldsObj->fetch(PDO::FETCH_OBJ)->form_definitions;
					$form_definitions = unserialize($form_definitions);
					if(is_array($form_definitions)){
						$newDefinitions = array();
						foreach($form_definitions as $oneFieldRow){
							$order_vals = $oneFieldRow['order_val'];
							if($order_vals==$order_val){}
							else{
								$newDefinitions[] = $oneFieldRow;
							}
						}
						
						$form_definitions = serialize($newDefinitions);
						$this->db->update('forms', array('form_definitions'=>$form_definitions), $forms_id);
						$delete_tablerow = true;
					}
				}
			}
		}
		else{
			if($tableName == 'time_clock'){
				$dateformat = $_SESSION["dateformat"]??'m/d/Y';
				$timeformat = $_SESSION["timeformat"]??'12 hour';
	
				$queryObj = $this->db->query("SELECT * FROM $tableName WHERE accounts_id=$accounts_id AND $tableIdName=:tableIdValue", array('tableIdValue'=>$tableIdValue));
				if($queryObj){				
					while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
						$user_id = $oneRow->user_id;
						$clocked_in = $oneRow->clocked_in;
						$clocked_out = $oneRow->clocked_out;
	
						$weekDay = $clockInDate = $clockInTime = '';
						if(!in_array($clocked_in, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))){
							$weekDay = date('l', strtotime($clocked_in));
							$clockInDate = date($dateformat, strtotime($clocked_in));
							$clockInTime = date('g:i a', strtotime($clocked_in));
							if($timeformat=='24 hour'){$clockInTime =  date('H:i', strtotime($clocked_in));}
							else{$clockInTime =  date('g:i a', strtotime($clocked_in));}
						}
	
						$notesData = array();
						$notesData[] = $this->db->translate('Day of the Week').': '.$weekDay;
						$notesData[] = $this->db->translate('Clock In Date').': '.$clockInDate;
						$notesData[] = $this->db->translate('Clock In Time').': '.$clockInTime;
	
						$clockOutDate = $clockOutTime = $times = '';
						if(!in_array($clocked_out, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))){
							$clockOutDate = date($dateformat, strtotime($clocked_out));
							if($timeformat=='24 hour'){$clockOutTime =  date('H:i', strtotime($clocked_out));}
							else{$clockOutTime =  date('g:i a', strtotime($clocked_out));}
	
							$totalTimePerDay = strtotime($clocked_out)-strtotime($clocked_in);
							$hours = 0;
							$minutes = 0;
							if($totalTimePerDay>0){
								$totalMinutes = floor($totalTimePerDay/60);
								if($totalMinutes>0){
									$hours = floor($totalMinutes/60);
									$minutes = ($totalMinutes%60);
								}
							}
	
							$times = "$hours hrs $minutes min";
						}
	
						$notesData[] = $this->db->translate('Clock Out Date').': '.$clockOutDate;
						$notesData[] = $this->db->translate('Clock Out Time').': '.$clockOutTime;
						$notesData[] = $this->db->translate('Time').': '.$times;
	
						$notes = "<b>".$this->db->translate('Remove Time Clock')."</b>: ".implode(', ', $notesData);
						
						$noteData=array('table_id'=> $user_id,
										'note_for'=> 'user',
										'created_on'=> date('Y-m-d H:i:s'),
										'last_updated'=> date('Y-m-d H:i:s'),
										'accounts_id'=> $_SESSION["accounts_id"],
										'user_id'=> $_SESSION["user_id"],
										'note'=> $notes,
										'publics'=>0);
						$this->db->insert('notes', $noteData);
					}
				}
			}
			$delete_tablerow = $this->db->delete($tableName, $tableIdName, $tableIdValue);
		}
		
		if($delete_tablerow){				
			$savemsg = 'Done';
			$activity_feed_title = $this->db->translate('Remove')." $description";
			$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
			$activity_feed_link = "";
			$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
			
			$afData = array('created_on' => date('Y-m-d H:i:s'),
							'last_updated' => date('Y-m-d H:i:s'),
							'accounts_id' => $_SESSION["accounts_id"],
							'user_id' => $_SESSION["user_id"],
							'activity_feed_title' => $activity_feed_title,
							'activity_feed_name' => $description,
							'activity_feed_link' => $activity_feed_link,
							'uri_table_name' => $tableName,
							'uri_table_field_name' =>"",
							'field_value' => 1
							);
			$this->db->insert('activity_feed', $afData);
			
		}
		else{
			$savemsg = 'error';
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}	
	
	public function showOnPOInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		
		$product_id = intval($POST['product_id']??0);
		$product_type = trim((string) $POST['product_type']??'');
		 
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$needData = array();
		$ordersObj = $this->db->query("SELECT (pos_cart.qty-pos_cart.shipping_qty) AS needQty, pos.invoice_no FROM pos, pos_cart WHERE pos_cart.item_id = $product_id AND pos.accounts_id = $accounts_id AND pos.pos_type = 'Order' AND pos.order_status=1 AND pos_cart.qty>pos_cart.shipping_qty AND pos.pos_id = pos_cart.pos_id", array());
		if($ordersObj){
			while($needOrder = $ordersObj->fetch(PDO::FETCH_OBJ)){
				$needQty = $needOrder->needQty;
				$invoice_no = $needOrder->invoice_no;
				$needData[] = array($needQty, $invoice_no, 'Open Order', 0);
			}
		}
		
		$repairsObj = $this->db->query("SELECT (pos_cart.qty-pos_cart.shipping_qty) AS needQty, repairs.repairs_id, repairs.ticket_no, repairs.status FROM pos, pos_cart, repairs WHERE pos_cart.item_id = $product_id AND repairs.status NOT IN ('Finished', 'Invoiced', 'Cancelled') AND pos.accounts_id = $accounts_id AND pos.pos_type = 'Repairs' AND pos.order_status=1 AND pos.pos_id = pos_cart.pos_id AND pos.pos_id = repairs.pos_id", array());
		if($repairsObj){
			while($needRepair = $repairsObj->fetch(PDO::FETCH_OBJ)){
				$needData[] = array($needRepair->needQty, $needRepair->ticket_no, $needRepair->status, $needRepair->repairs_id);
			}
		}
		$jsonResponse['needData'] = $needData;
		
		$have = 0;
		$IMEIlink = '';
		if($product_type=='Live Stocks'){
			$itemObj = $this->db->query("SELECT count(item_id) as counttotalrows FROM item WHERE product_id = $product_id AND accounts_id = $accounts_id AND item_publish = 1 AND in_inventory = 1", array());
			if($itemObj){
				$have = $itemObj->fetch(PDO::FETCH_OBJ)->counttotalrows;
			}
		}
		else{
			$inventoryObj = $this->db->query("SELECT current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
			if($inventoryObj){
				$have = $inventoryObj->fetch(PDO::FETCH_OBJ)->current_inventory;
			}
		}
		$jsonResponse['have'] = $have;		

		$onPOData = array();		
		$poObj = $this->db->querypagination("SELECT po.po_number, po.lot_ref_no, po.date_expected, SUM(po_items.ordered_qty-po_items.received_qty) AS totalOnPO FROM po, po_items WHERE po_items.product_id = $product_id AND po.accounts_id = $accounts_id AND po.status = 'Open' AND po_items.ordered_qty>po_items.received_qty AND po.po_id = po_items.po_id GROUP BY po.po_number ORDER BY po.po_number ASC", array());
		if($poObj){
			foreach($poObj as $onerow){
				$onPOData[] = array(intval($onerow['totalOnPO']), intval($onerow['po_number']), $onerow['lot_ref_no'], $onerow['date_expected']);				
			}
		}

		$jsonResponse['onPOData'] = $onPOData;

		return json_encode($jsonResponse);
	}
	
	public function makewords($numval){
		$currency = $_SESSION["currency"]??'৳';
		$numval = str_replace(',','',number_format(round($numval,2), 2));
		$moneystr = "";
		
		// handle the Crore
		$crval = (integer)($numval / 10000000);
		if($crval > 0)  {
			$moneystr = $this->getwords($crval) . " Crore";
		}
		
		// handle the Lakh
		$workval = $numval - ($crval * 10000000); // get rid of millions
		$milval = (integer)($workval / 100000);
		if($milval > 0)  {
			$moneystr = $this->getwords($milval) . " Lacs";
		}
		 
		// handle the thousands
		$workval = $numval - ($milval * 100000); // get rid of millions
		$thouval = (integer)($workval / 1000);
		if($thouval > 0)  {
			$workword = $this->getwords($thouval);
			if ($moneystr == "")    {
				$moneystr = $workword . " Thousand";
			}
			else{
				$moneystr .= " " . $workword . " Thousand";
			}
		}
		 
		// handle all the rest of the dollars
		$workval = $workval - ($thouval * 1000); // get rid of thousands
		$tensval = (integer)($workval);
		if ($moneystr == ""){
			if ($tensval > 0){
				$moneystr = $this->getwords($tensval);
			}else{
				$moneystr = "Zero";
			}
		}
		else{ // non zero values in hundreds AND up
			$workword = $this->getwords($tensval);
			$moneystr .= " " . $workword;
		}
		 
		// plural or singular 'dollar'
		$workval = (integer)($numval);
		if ($currency=='$'){
			$moneystr .= " Dollar";
		}else{
			$moneystr .= " Taka";
		}
	
		
		// do the cents - use printf so that we get the
		// same rounding as printf
		$workstr = sprintf("%3.2f",$numval); // convert to a string
		$intstr = substr($workstr,strlen($numval) - 2, 2);
		$workint = (integer)($intstr);
		if ($workint>1){
			$moneystr .= " &amp; ";
		}
		if ($workint == 0){
			//$moneystr .= "Zero";
		}
		else{
		  $moneystr .= $this->getwords($workint);
		}
		if ($workint>1){
			if($currency=='$'){
				$moneystr .= " Cent";
				if ($workint>1){$moneystr .= "s";}
			}
			else{
				$moneystr .= " Paisa";
			}
		}
		 
		// done 
		return $moneystr.'.';
	}
	
	public function getwords($workval){
		$numwords = array(1 => "One",
						  2 => "Two",
						  3 => "Three",
						  4 => "Four",
						  5 => "Five",
						  6 => "Six",
						  7 => "Seven",
						  8 => "Eight",
						  9 => "Nine",
						  10 => "Ten",
						  11 => "Eleven",
						  12 => "Twelve",
						  13 => "Thirteen",
						  14 => "Fourteen",
						  15 => "Fifteen",
						  16 => "Sixteen",
						  17 => "Seventeen",
						  18 => "Eighteen",
						  19 => "Nineteen",
						  20 => "Twenty",
						  30 => "Thirty",
						  40 => "Forty",
						  50 => "Fifty",
						  60 => "Sixty",
						  70 => "Seventy",
						  80 => "Eighty",
						  90 => "Ninety");
		 
		// handle the 100's
		$retstr = "";
		$hundval = (integer)($workval / 100);
		if ($hundval > 0){
		  $retstr = $numwords[$hundval] . " Hundred";
		}
		 
		// handle units AND teens
		$workstr = "";
		$tensval = $workval - ($hundval * 100); // dump the 100's
		 
		// do the teens
		if (($tensval < 20) && ($tensval > 0)){
		  $workstr = $numwords[$tensval];
		   // got to break out the units AND tens
		}
		else{
		  	$tempval = ((integer)($tensval / 10)) * 10; // dump the units
		  	
			if($tempval > 0)
		  		$workstr = $numwords[$tempval]; // get the tens
			else
				$workstr = '';
				
		  $unitval = $tensval - $tempval; // get the unit value
		  if ($unitval > 0){
			$workstr .= " " . $numwords[$unitval];
			}
		}
		 
		// join the parts together 
		if ($workstr != ""){
			  if ($retstr != ""){
				$retstr .= " " . $workstr;
			  }else{
				$retstr = $workstr;
				}
		}
	
		return $retstr;
	}
	
	public function taka_format($amount = 0, $floatPoints=2){
		if($floatPoints==0){$amount = round($amount);}
		else{$amount = round($amount, $floatPoints);}
		$negYN = 1;
		if($amount<0){$negYN = -1;}
		$amount = $amount*$negYN;
		$tmp = explode(".",$amount);  // for float or double values
		$strMoney = "";
		$amount = $tmp[0];
		$strMoney .= substr($amount, -3,3 ) ;
		$amount = substr($amount, 0,-3 ) ;
		while(strlen($amount)>0)
		{
			$strMoney = substr($amount, -2,2 ).",".$strMoney;
			$amount = substr($amount, 0,-2 );
		}
		$floatVal = 0;
		if(isset($tmp[1])){
			$floatVal = $tmp[1];
		}
		$floatVal = sprintf("%02d", $floatVal);
		
		if($negYN<0){$strMoney = '-'.$strMoney;}
		if($floatPoints>0){
			$strMoney .= '.'.$floatVal;
		}
		return $strMoney;
	}
	
}
?>