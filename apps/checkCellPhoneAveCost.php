<?php
$serverPath = getcwd();
if($serverPath=='/home/machouse'){
	define('OUR_DOMAINNAME', 'machouse.com.bd');
	define('COMPANYNAME', 'Dazzle PVT. Ltd.');
}
else{
	define('OUR_DOMAINNAME', 'machousel.com.bd');
	define('COMPANYNAME', 'SK POS ERP');
}
require_once ("$serverPath/apps/Db.php");
require_once ("$serverPath/apps/Common.php");

class checkCellPhoneAveCost{
	protected $db;
	public function __construct($db){
		$this->db = $db;
	}
	
	public function check5ITCartMobileAveCost($starttime, $runTimeLimit, $totalChecked){
		$accIDsSql = "";
		$Common = new Common($this->db);
		$date = date('Y-m-d');
		$sqlquery = "SELECT po.accounts_id, po.supplier_id, po.po_number, po.lot_ref_no, poi.created_on, poi.po_items_id, po.transfer, poi.product_id, poi.received_qty, poi.cost FROM accounts, po, po_items poi WHERE $accIDsSql po.transfer >0 AND poi.item_type = 'livestocks' AND poi.check_ave_cost < '$date' AND accounts.accounts_id = po.accounts_id AND po.po_id = poi.po_id ORDER BY poi.po_items_id ASC LIMIT 0, 300";
		$query = $this->db->querypagination($sqlquery, array());
		if($query){
			foreach($query as $oneRow){
				$totalChecked++;
				
				$totalRunTime = time()-$starttime;
				if($totalRunTime>$runTimeLimit){
					$this->writeIntoLog("Total $totalChecked IT Cart Rows checked within $totalRunTime Seconds. Last PO Items ID: $oneRow[po_items_id]");
					return '';
				}
				else{
					
                    if($oneRow['transfer']==2){
						$accounts_id = $oneRow['supplier_id'];
					}
					else{
						$accounts_id = $oneRow['accounts_id'];
					}
					                    
                    $po_items_id = $oneRow['po_items_id'];
                    $created_on = $oneRow['created_on'];
                    $transfer = $oneRow['transfer'];
                    $poCost = $newAveCost = 0.00;
                           
                    $product_id = $oneRow['product_id'];
                    $received_qty = $oneRow['received_qty'];
                    $poCost = $oneRow['cost'];

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
                        if($transfer == 2){
                            $itemSql = "SELECT item_id, item_number FROM item WHERE item_id IN (".implode(', ', array_keys($itemIds)).")";
                            $itemObj = $this->db->query($itemSql, array());
                            if($itemObj){
                                while($itemOneRow = $itemObj->fetch(PDO::FETCH_OBJ)){
                                    $itemIds[$itemOneRow->item_id] = $itemOneRow->item_number;
                                }
                            }
                            
                            foreach($itemIds as $item_id=>$item_number){
                                //======================//
                                $checkDateTime = $created_on;
                                $purcAveData = array();
                                $purchaseAveCost = 0.00;
                                $item_id = 0;
                                $testingStr = '';
                                $itemIds2 = array();
                                $itemSql = "SELECT item_id FROM item WHERE accounts_id = $accounts_id AND item_number = '$item_number' AND product_id = $product_id";
                                $itemObj = $this->db->query($itemSql, array());
                                if($itemObj){			
                                    while($itemsrow = $itemObj->fetch(PDO::FETCH_OBJ)){
                                        $itemIds2[] = array($itemsrow->item_id, $product_id);
                                    }
                                }
                                if(empty($itemIds2)){
                                    $itemSql = "SELECT item_id, product_id FROM item WHERE accounts_id = $accounts_id AND item_number = '$item_number'";
                                    $itemObj = $this->db->query($itemSql, array());
                                    if($itemObj){
                                        while($itemsrow = $itemObj->fetch(PDO::FETCH_OBJ)){
                                            $itemIds2[] = array($itemsrow->item_id, $itemsrow->product_id);
                                        }
                                    }
                                }

                                if(!empty($itemIds2)){
                                    foreach($itemIds2 as $imeiInfo){
                                        if($imeiInfo[1]==$product_id){
                                            $item_id = $imeiInfo[0];
                                        }
                                    }
                                    if($item_id ==0){
                                        $item_id = $itemIds2[0][0];
                                        $product_id = $itemIds2[0][1];
                                    }
                                }
                                
                                if($item_id>0){
                                    $oneIMEIAveCost = $Common->oneIMEIAveCost(0, $item_id, $checkDateTime);
                                    $purchaseAveCost = $oneIMEIAveCost[0];
                                }
                                
                                //======================//
                                $totalAveCost += $purchaseAveCost;
                            }
                        }
                        else{
                            foreach($itemIds as $item_id=>$itemVal){
                                $oneIMEIAveCost = $Common->oneIMEIAveCost(0, $item_id, $created_on);
                                $totalAveCost += $oneIMEIAveCost[0];
                            }
                        }
                    }
                    $newAveCost = $totalAveCost;
                    if($newQty != $received_qty){
                        $newAveCost = $poCost;
                    }
                    elseif($newQty>1){
                        $newAveCost = round($totalAveCost/$newQty,2);
                    }
                    if($received_qty<0){$newQty = $newQty*(-1);}
                    
                    $updateData = array('check_ave_cost'=>$date);
					if($newAveCost != $poCost){					
						$updateData['cost'] = $newAveCost;
					}					
					if($received_qty != $newQty){					
						$updateData['received_qty'] = $newQty;
					}					
					$updateData = $this->db->update('po_items', $updateData, $po_items_id);
                    
                    $cost = number_format($poCost,2);
                    $newCost = number_format($newAveCost,2);
                    $cartQty = $received_qty;
                    if($cost !=$newCost || $cartQty != $newQty){
                        $message = "Account ID: $oneRow[accounts_id], IT No, $oneRow[po_number], PO Item ID:  $oneRow[po_items_id]";
                        if($cost !=$newCost){$message .= ", Cart Cost: $cost, New Calculated Cost: $newCost";}
                        if($cartQty != $newQty){$message .= ", Cart Qty: $cartQty, New Qty: $newQty (UPDATED)";}
                        $this->writeIntoLog($message);
                    }
				}
			}
		}
		
		$totalRunTime = time()-$starttime;
		if($totalRunTime>$runTimeLimit){
			$this->writeIntoLog("Total $totalChecked PO Items checked within $totalRunTime Seconds.");
			return '';
		}
		else{			
			//$this->check5ITCartMobileAveCost($starttime, $runTimeLimit, $totalChecked);
		}
	}
	
	public function check5CartMobileAveCost($starttime, $runTimeLimit, $totalChecked){
		$accIDsSql = "";
		$date = date('Y-m-d');
		$Common = new Common($this->db);
		$sqlquery = "SELECT pos.accounts_id, pos.invoice_no, pos.sales_datetime, pc.pos_cart_id, pc.item_id, pc.shipping_qty, pc.ave_cost FROM accounts, pos, pos_cart pc WHERE $accIDsSql pc.item_type = 'livestocks' AND pc.check_ave_cost < '$date' AND pc.shipping_qty>0 AND pos.invoice_no>0 AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2)) AND accounts.accounts_id = pos.accounts_id AND pos.pos_id = pc.pos_id ORDER BY pc.pos_cart_id ASC LIMIT 0, 500";
		$query = $this->db->querypagination($sqlquery, array());
		if($query){
			foreach($query as $oneRow){
				$totalChecked++;
				$totalRunTime = time()-$starttime;
				if($totalRunTime>$runTimeLimit){
					$this->writeIntoLog(date('Y-m-d H:i:s').", Total $totalChecked POS Cart checked within $totalRunTime Seconds. Last POS Cart ID:  $oneRow[pos_cart_id]");
					return '';
				}
				else{
					
                    $pos_cart_id = $oneRow['pos_cart_id'];
                    $sales_datetime = $oneRow['sales_datetime'];
                    
                    $product_id = $oneRow['item_id'];
                    $shipping_qty = $oneRow['shipping_qty'];
                    $salesCost = $oneRow['ave_cost'];
                    
                    $shippingQty = $newQty = $totalAveCost = 0;
                    
                    $pciSql = "SELECT item_id FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id ORDER BY pos_cart_item_id ASC";
                    $pciObj = $this->db->query($pciSql, array());
                    if($pciObj){
                        while($pciOneRow = $pciObj->fetch(PDO::FETCH_OBJ)){

                            $oneIMEIAveCost = $Common->oneIMEIAveCost(0, $pciOneRow->item_id, $sales_datetime);
                            
                            $shippingQty++;
                            $totalAveCost += $oneIMEIAveCost[0];
                            
                            $purcAveData = $oneIMEIAveCost[1];							
                            if(!empty($purcAveData)){			
                                $newQty++;
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
                    
                    $updateData = array('check_ave_cost'=>$date);
					if($newAveCost != $salesCost){
						$updateData['ave_cost'] =$newAveCost;
					}
					$this->db->update('pos_cart', $updateData, $pos_cart_id);

                    $cost = number_format($salesCost,2);
                    $newCost = number_format($newAveCost,2);
                    $cartQty = $shippingQty;
                    if($cost !=$newCost || $cartQty != $newQty){
                        $message = "Account ID: $oneRow[accounts_id], Invoice No: $oneRow[invoice_no], POS Cart ID:  $oneRow[pos_cart_id]";
                        if($cost !=$newCost){$message .= ", Cart Ave Cost: $cost, New Calculated Cost: $newCost";}
                        if($cartQty != $newQty){$message .= ", Cart Qty: $cartQty, New Qty: $newQty (Not UPDATED)";}
                        $this->writeIntoLog($message);
                    }
				}
			}
		}
		
		$totalRunTime = time()-$starttime;
		if($totalRunTime>$runTimeLimit){
			$this->writeIntoLog(date('Y-m-d H:i:s').", Total $totalChecked POS Cart checked within $totalRunTime Seconds.");
			return '';
		}
		else{			
			$this->check5CartMobileAveCost($starttime, $runTimeLimit, $totalChecked);
		}
	}
	
	public function writeIntoLog($message){
		if($message !=''){
			$fileName = './ave-cost-device-'.date('D');
			if(is_array($message)){$message = implode(', ', $message);}
			file_put_contents($fileName, "$message\n",FILE_APPEND);
		}
	}

}

$timezone = 'Asia/Dhaka';
date_default_timezone_set($timezone);
$cronstarttime = time();

$db = new Db();
$checkCellPhoneAveCost = new checkCellPhoneAveCost($db);
$checkCellPhoneAveCost->writeIntoLog('Update Done : ');
$date = date('Y-m-d');
$Str = "";
$update = "UPDATE";
$starttime = time();
$runTimeLimit = 30;
$totalITCount = 0;
$accIDsSql = "";
$sqlquery = "SELECT COUNT(poi.po_items_id) as totalITCount FROM accounts, po, po_items poi WHERE $accIDsSql poi.item_type = 'livestocks' AND poi.check_ave_cost < '$date' AND po.transfer>0 AND accounts.accounts_id = po.accounts_id AND po.po_id = poi.po_id ORDER BY poi.po_items_id ASC";
$query = $db->querypagination($sqlquery, array());
if($query){
	foreach($query as $oneRow){
		$totalITCount = $oneRow['totalITCount'];
	}
}
//if($totalITCount>0){
	//$checkCellPhoneAveCost->check5ITCartMobileAveCost($starttime, $runTimeLimit, 0);
//}
//else{
	$totalITCount = 0;
	$accIDsSql = "";
	$sqlquery = "SELECT COUNT(pc.pos_cart_id) as totalITCount FROM accounts, pos, pos_cart pc WHERE $accIDsSql pc.item_type = 'livestocks' AND pc.check_ave_cost < '$date' AND pos.invoice_no>0 AND accounts.accounts_id = pos.accounts_id AND pos.pos_id = pc.pos_id ORDER BY pc.pos_cart_id  ASC";
	$query = $db->querypagination($sqlquery, array());
	if($query){
		foreach($query as $oneRow){
			$totalITCount = $oneRow['totalITCount'];
		}
	}
	if($totalITCount>0){
		$checkCellPhoneAveCost->check5CartMobileAveCost($starttime, $runTimeLimit, 0);
	}
//}
?>