<?php
class Labels{
	protected $db;
	public function __construct($db){$this->db = $db;}
	
	public function printContent($id, $frompage){
		$domainName = OUR_DOMAINNAME;
		$printingStr = '';
		if(isset($_SESSION["accounts_id"])){
			
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$accounts_id = $_SESSION["accounts_id"]??0;
			$dateformat = $_SESSION["dateformat"]??'m/d/Y';
			$timeformat = $_SESSION["timeformat"]??'12 hour';
			$currency = $_SESSION["currency"]??'৳';

			$Common = new Common($this->db);
			
			$title = $this->db->translate('Product Barcode Print');
			$orientation = 'Portrait';
			
			$lpVarData = $Common->variablesData('label_printer', $accounts_id);
			if(!empty($lpVarData) && array_key_exists('orientation', $lpVarData)){
				$orientation = $lpVarData['orientation'];
			}
						
			$printingStr .= '<!DOCTYPE html>
				<html>
				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
					<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
					
					<title>'.$title.'</title>
					<style type="text/css">
						body{font-family:Arial, sans-serif, Helvetica;margin:0; padding:0;background:#fff;color:#000;}
						@page {size:'.$orientation.';margin: 0;}
					</style>
				</head>
			<body>';
							
			//=============Default For Product Label=========//
			$pl_company_name = $pl_price = 0;
			$pl_product_name = $pl_barcode = 1;
			//=============Default For Device Label=========//
			$dl_company_name = $dl_price = 0;
			$dl_product_name = $dl_barcode = 1;
			//=============Default For Repair Ticket Label=========//
			$rtl_customer_duedate = $rtl_brand_model = $rtl_problem = $rtl_barcode = 1;
			$rtl_phone_no = $rtl_imei_serial = $rtl_mode_deails = $rtl_password = 0;		
			//=============Default For Repair Customer Label=========//
			$rcl_first_name = 1;
			$rcl_last_name = $rcl_phone_no = $rcl_ticket_no = $rcl_due_date = 0;
			
			$blVarData = $Common->variablesData('barcode_labels', $accounts_id);
			if(!empty($blVarData)){
				extract($blVarData);
			}
			
			$company_name = $product_name = $pricestr = $barcode_gen_code = '';
			
			if(strcmp($frompage, 'product')==0){
				$product_id = $id;
				$productObj = $this->db->query("SELECT p.*, manufacturer.name AS manufacture FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_id = :product_id", array('product_id'=>$product_id),1);
				if($productObj){
					$product_onerow = $productObj->fetch(PDO::FETCH_OBJ);	
					
					if($pl_company_name>0){
						$company_name = $_SESSION["company_name"];
					}
					
					if($pl_product_name>0){
						$product_name .= stripslashes(trim((string) $product_onerow->product_name));
						$manufacturer_name = stripslashes(trim((string) $product_onerow->manufacture));
						if($manufacturer_name !=''){$product_name = stripslashes(trim($manufacturer_name.' '.$product_name));}
					}
					
					if($pl_price>0){				
						$inventoryObj = $this->db->query("SELECT regular_price FROM inventory WHERE accounts_id = $accounts_id AND product_id = $product_onerow->product_id", array());
						if($inventoryObj){
							$regular_price = $inventoryObj->fetch(PDO::FETCH_OBJ)->regular_price;
							if($regular_price>0){
								$pricestr .= $currency.number_format($regular_price,2);
							}
						}
					}
					
					if($pl_barcode>0){				
						$barcode_gen_code = stripslashes(trim((string) $product_onerow->sku));
					}
				}
			}
			elseif(strcmp($frompage, 'IMEI')==0){
				$item_id = $id;
				
				$itemObj = $this->db->query("SELECT * FROM item WHERE accounts_id = $accounts_id AND item_id = :item_id", array('item_id'=>$item_id),1);
				if($itemObj){
					$item_onerow = $itemObj->fetch(PDO::FETCH_OBJ);						
					if($dl_company_name>0){
						$company_name = $_SESSION["company_name"];
					}
					
					$product_id = $item_onerow->product_id;
					$productObj = $this->db->query("SELECT p.*, manufacturer.name AS manufacture FROM product p, LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id", array());
					if($productObj){
						$product_onerow = $productObj->fetch(PDO::FETCH_OBJ);
						if($dl_product_name>0){
							$product_name .= stripslashes(trim((string) $product_onerow->product_name));
							$manufacturer_name = stripslashes(trim((string) $product_onerow->manufacture));
							if($manufacturer_name !=''){$product_name = stripslashes(trim((string) $manufacturer_name.' '.$product_name));}
							
							$colour_name = $product_onerow->colour_name;
							if($colour_name !=''){$product_name .= ' '.$colour_name;}
							
							$storage = $product_onerow->storage;
							if($storage !=''){$product_name .= ' '.$storage;}
							
							$physical_condition_name = $product_onerow->physical_condition_name;
							if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}
							
							if($item_onerow){
								if($item_onerow->carrier_name !=''){$product_name .= ' '.$item_onerow->carrier_name;}
							}						
						}
						
						if($dl_price>0){
							$inventoryObj = $this->db->query("SELECT regular_price FROM inventory WHERE accounts_id = $accounts_id AND product_id = $product_onerow->product_id", array());
							if($inventoryObj){
								$regular_price = $inventoryObj->fetch(PDO::FETCH_OBJ)->regular_price;
								if($regular_price>0){
									$pricestr .= $currency.number_format($regular_price,2);
								}
							}
						}
					}
					
					if($dl_barcode>0){
						$barcode_gen_code = $item_onerow->item_number;
					}
				}
			}
			elseif(strcmp($frompage, 'Repairs')==0){
				$repairs_id = $id;
				$repairsObj = $this->db->query("SELECT customer_id, due_datetime, due_time, properties_id, problem, lock_password, ticket_no FROM repairs WHERE accounts_id = $accounts_id AND repairs_id = :repairs_id", array('repairs_id'=>$repairs_id),1);
				if($repairsObj){
					$repairs_onerow = $repairsObj->fetch(PDO::FETCH_OBJ);						
					
					if($rtl_customer_duedate>0 || $rtl_phone_no>0){
						$customer_id = $repairs_onerow->customer_id;
						$customerObj = $this->db->query("SELECT first_name, last_name, contact_no FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = $customer_id", array());
						if($customerObj){
							$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);	
							$company_name = '';
							if($rtl_customer_duedate>0){
								$company_name = stripslashes($customerrow->first_name);
								$last_name = $customerrow->last_name;
								if($last_name !=''){$company_name .= ' '.$last_name;}
							}
							if($rtl_phone_no>0 && !empty($customerrow->contact_no)){
								if(!empty($company_name)){$company_name .= '<br>';}
								$company_name .= $customerrow->contact_no;
							}
						}
						
						if(!in_array($repairs_onerow->due_datetime, array('0000-00-00', '1000-01-01'))){
							$company_name .= ' '.date($dateformat, strtotime($repairs_onerow->due_datetime)).' '.$repairs_onerow->due_time;
						}
					}
					
					if($rtl_brand_model>0 || $rtl_imei_serial>0 || $rtl_mode_deails>0){
						$properties_id = $repairs_onerow->properties_id;
						if($properties_id>0){
							$propertiesObj = $this->db->query("SELECT * FROM properties WHERE properties_id = $properties_id AND accounts_id = $prod_cat_man AND properties_publish = 1", array());
							if($propertiesObj){
								$propertiesRow = $propertiesObj->fetch(PDO::FETCH_OBJ);	
								
								$properties_id = $propertiesRow->properties_id;
								$customers_id = $propertiesRow->customers_id;
								$brand_model_id = trim((string) $propertiesRow->brand_model_id);
								if($brand_model_id>0){
									$brandModelObj = $this->db->query("SELECT brand, model FROM brand_model WHERE brand_model_id = $brand_model_id AND accounts_id = $prod_cat_man", array());
									if($brandModelObj){
										$brandModelRow = $brandModelObj->fetch(PDO::FETCH_OBJ);	
										if($rtl_brand_model>0){
											$product_name .= trim(stripslashes("$brandModelRow->brand $brandModelRow->model"));
										}
									}
								}
								if($rtl_mode_deails>0 && $propertiesRow->more_details !=''){
									$product_name .= ' '.trim((string) $propertiesRow->more_details);
								}
								if($rtl_imei_serial>0 && $propertiesRow->imei_or_serial_no !=''){
									$product_name .= ', '.trim((string) $propertiesRow->imei_or_serial_no);
								}							
							}
						}
					}
					
					if($rtl_problem>0 && $repairs_onerow->problem !=''){
						$product_name .= ' '.trim((string) $repairs_onerow->problem);
					}
					
					if($rtl_password>0 && $repairs_onerow->lock_password !=''){
						$product_name .= ' '.trim((string) $repairs_onerow->lock_password);
					}
					
					if($rtl_barcode>0){
						$barcode_gen_code .= 'T'.$repairs_onerow->ticket_no;
					}
				}
			}
			elseif(strcmp($frompage, 'Customer')==0){
				$repairs_id = $id;
				
				$repairsObj = $this->db->query("SELECT customer_id, due_datetime, due_time, ticket_no FROM repairs WHERE accounts_id = $accounts_id AND repairs_id = :repairs_id", array('repairs_id'=>$repairs_id),1);
				if($repairsObj){
					$repairs_onerow = $repairsObj->fetch(PDO::FETCH_OBJ);
					
					$customers_id = $repairs_onerow->customer_id;
					$customerObj = $this->db->query("SELECT company, first_name, last_name, email, contact_no FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = :customers_id", array('customers_id'=>$customers_id),1);
					if($customerObj){
						$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);	
						
						if($rcl_first_name>0){
							$product_name .= stripslashes($customerrow->first_name);
						}
						if($rcl_last_name>0 && $customerrow->last_name !=''){
							$product_name .= ' '.$customerrow->last_name;
						}
						/*
						if($customerrow->company !=''){
							$product_name .= '<br />Company: '.$customerrow->company;
						}
						
						if($customerrow->email !=''){
							$product_name .= '<br />'.$customerrow->email;
						}
						*/
						if($rcl_phone_no>0 && $customerrow->contact_no !=''){
							$product_name .= '<br />Tel: '.$customerrow->contact_no;
						}
					}
				
					if($rcl_ticket_no>0){
						$product_name .= '<br />T'.$repairs_onerow->ticket_no;
					}
					if($rcl_due_date>0 && !in_array($repairs_onerow->due_datetime, array('0000-00-00', '1000-01-01'))){
						$product_name .= ' '.date($dateformat, strtotime($repairs_onerow->due_datetime)).' '.$repairs_onerow->due_time;
					}
				}
			}
					
			$labelwidth = 57;
			$labelheight = 31;
			$units = 'mm';
			$top_margin = $right_margin = $bottom_margin = $left_margin = 0;
			$font_size = 'Regular';
			if(!empty($lpVarData)){
				extract($lpVarData);
				if($label_size=='customSize'){
					if($units=='Inches'){
						$labelwidth = round(round($label_sizeWidth,2)*25.4);
						$labelheight = round(round($label_sizeHeight,2)*25.4);
					}
					else{
						$labelwidth = round($label_sizeWidth);
						$labelheight = round($label_sizeHeight);
					}
				}
				else{
					list($labelwidth, $labelheight) = explode('|', $label_size);
				}
			}
			
			$labelwidth = $labelwidth*3.7795275591;
			$labelheight = $labelheight*3.7795275591;
			$top_margin = intval($top_margin)+1;
			$right_margin = intval($right_margin)+3;
			$bottom_margin = intval($bottom_margin);
			$left_margin = intval($left_margin)+1;
					
			if($top_margin !=0){$labelheight = $labelheight-$top_margin;}
			if($bottom_margin !=0){$labelheight = $labelheight-$bottom_margin;}
			if($right_margin !=0){$labelwidth = $labelwidth-$right_margin;}
			if($left_margin !=0){$labelwidth = $labelwidth-$left_margin;}
			
			$font_sizeOptions = array('Small'=>'11', 'Regular'=>'12', 'Large'=>'13');
			$fontsize = $font_sizeOptions[$font_size];
			$lineheight = $fontsize;
			$marginCSS = '';
			if($top_margin !=0 || $right_margin !=0 || $bottom_margin !=0 || $left_margin !=0){
				$marginCSS = 'margin:'.$top_margin.'px '.$right_margin.'px '.$bottom_margin.'px '.$left_margin.'px;';
			}
			$printingStr .= '<div style="width:'.$labelwidth.'px; height:'.$labelheight.'px;page-break-after: always;">
					<div style="text-align:justify;width:'.$labelwidth.'px; height:'.$labelheight.'px;overflow:hidden; position:relative;font-size:'.$fontsize.'px;color:#000;line-height:'.$lineheight.'px; background:#fff;'.$marginCSS.'">        
						<div style="margin:0 auto;text-align:center;margin:1px 0; overflow:hidden;">';
							if($company_name !=''){
								$printingStr .= '<div style="width:100%; height:'.$lineheight.'px;overflow:hidden;">'.$company_name.'</div>';
							}
							if($product_name !=''){
								$printingStr .= $product_name;
							}
							if($pricestr !=''){
								if($labelheight <=94){$printingStr .= ', ';}
								else{$printingStr .= '<br />';}
								$printingStr .= $pricestr;
							}
						
			if($barcode_gen_code !=''){
				$rbarcode_gen_code = str_replace('&', '_and_', $barcode_gen_code);
				$rbarcode_gen_code = str_replace('+', '_plus_', $rbarcode_gen_code);
				$rbarcode_gen_code = str_replace('/', '_bksls_', $rbarcode_gen_code);
				$height = 25;
				$bottomHight = $height+$fontsize;
				$printingStr .= "</div>
						<div style=\"width:98%;text-align:center; height:".$bottomHight."px; overflow:hidden; position:absolute; bottom:0px; left:1%; z-index:10;\">
							<img style=\"max-width:95%;\" src=\"http://$domainName/Createbarcode/$rbarcode_gen_code/$height\" alt=\"$barcode_gen_code\"></img>
							<div style=\"clear:both;width:100%; height:0px;\"></div>
							$barcode_gen_code";
			}
							
			$printingStr .= '</div>
						</div>
					</div>
				</body>
			</html>';
			
		}
		
		return $printingStr;
	}
	
}
?>