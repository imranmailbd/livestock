
CREATE TABLE IF NOT EXISTS `lsnipplesizescore` (
  `lsnipplesizescore_id` int NOT NULL AUTO_INCREMENT,
  `lsnipplesizescore_publish` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  `accounts_id` int NOT NULL,
  `user_id` int NOT NULL,
  `lsnipplesizescore_name` varchar(35) COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`lsnipplesizescore_id`),
  KEY `created_by` (`accounts_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;


/*======lsnipplesizescore Module======*/
async function lsnipplesizescore(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Nipple Size Score')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top: 0; background: #fff; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let lsnipplesizescoreContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                let lsnipplesizescoreRow = cTag('div', {class: "flexSpaBetRow"});
                    const lsnipplesizescoreHeaderColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    lsnipplesizescoreHeaderColumn.appendChild(subHeader_Search_Bar(Translate('lsnipplesizescore'),Translate('Search Nipple Size Score'),filter_Manage_Data_lsnipplesizescore));
                        const lsnipplesizescoreTableRow = cTag('div', {class: "flexSpaBetRow"});
                            const lsnipplesizescoreTableColumn = cTag('div', {class: "columnXS12"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Live Stock Nipple Size Score');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            lsnipplesizescoreTableColumn.appendChild(listTable);
                        lsnipplesizescoreTableRow.appendChild(lsnipplesizescoreTableColumn);
                    lsnipplesizescoreHeaderColumn.appendChild(lsnipplesizescoreTableRow);
                    addPaginationRowFlex(lsnipplesizescoreHeaderColumn);
                lsnipplesizescoreRow.appendChild(lsnipplesizescoreHeaderColumn);

                    const addProductlsnipplesizescore = cTag('div', {class: "columnXS12 columnMD5"});
                        let productlsnipplesizescoreHeader = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;",  id: "formtitle"});
                        productlsnipplesizescoreHeader.innerHTML =Translate('Add New Nipple Size Score');
                    addProductlsnipplesizescore.appendChild(productlsnipplesizescoreHeader);

                        const addProductlsnipplesizescoreForm = cTag('form', {'action': "#", name: "frmlsnipplesizescore", id: "frmlsnipplesizescore", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        addProductlsnipplesizescoreForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'lsnipplesizescore_name',AJsave_lsnipplesizescore));
                            const addProductlsnipplesizescoreRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let lsnipplesizescoreLabel = cTag('label', {'for': "lsnipplesizescore_name"});
                                lsnipplesizescoreLabel.innerHTML = Translate('Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                lsnipplesizescoreLabel.appendChild(requiredField);
                            addProductlsnipplesizescoreRow.appendChild(lsnipplesizescoreLabel);
                                let input = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "lsnipplesizescore_name", id: "lsnipplesizescore_name",  'value': "", 'size': 35, 'maxlength': 35});
                            addProductlsnipplesizescoreRow.appendChild(input);
                        addProductlsnipplesizescoreForm.appendChild(addProductlsnipplesizescoreRow);
                        addProductlsnipplesizescoreForm.appendChild(controller_bar('lsnipplesizescore_id',resetForm_lsnipplesizescore));
                    addProductlsnipplesizescore.appendChild(addProductlsnipplesizescoreForm);
                lsnipplesizescoreRow.appendChild(addProductlsnipplesizescore);
            callOutDiv.appendChild(lsnipplesizescoreRow);
        lsnipplesizescoreContainer.appendChild(callOutDiv);
    parentRow.appendChild(lsnipplesizescoreContainer);
    showTableData.appendChild(parentRow);   

    addCustomeEventListener('filter',filter_Manage_Data_lsnipplesizescore);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_lsnipplesizescore);
    addCustomeEventListener('reset',resetForm_lsnipplesizescore);
    getSessionData();    
    filter_Manage_Data_lsnipplesizescore(true);
}

async function filter_Manage_Data_lsnipplesizescore(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_lsnipplesizescore/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Nipple Size Score'), 'align':'left'}],'lsnipplesizescore',filter_Manage_Data_lsnipplesizescore,resetForm_lsnipplesizescore);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_lsnipplesizescore(){
    const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_lsnipplesizescore';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Nipple Size Score'), 'align':'left'}],'lsnipplesizescore',filter_Manage_Data_lsnipplesizescore,resetForm_lsnipplesizescore);
        onClickPagination();
    }
}

async function AJsave_lsnipplesizescore(event=false){
    if(event){event.preventDefault();}

	let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmlsnipplesizescore");
    const url = '/'+segment1+'/AJsave_lsnipplesizescore';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
            resetForm_lsnipplesizescore();
            if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
            }
            else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
            }
            filter_Manage_Data_lsnipplesizescore();	
        }
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occurred while adding new lsnipplesizescore! Please try again.'), Translate('Ok'));
		}
		else if(data.returnStr=='Name_Already_Exist'){
			alert_dialog(Translate('Alert message'), Translate('This name already exists! Please try again with a different name.'), Translate('Ok'));
		}
		else if(data.returnStr=='Name_ExistInArchive'){
			alert_dialog(Translate('Alert message'), Translate('This name already exists <b>IN ARCHIVED</b>! Please try again with a different name.'), Translate('Ok'));
		}
		else{
			alert_dialog(Translate('Alert message'), Translate('No changes / Error occurred while updating data! Please try again.'), Translate('Ok'));
		}
        submit.value = Translate('Add')
        submit.disabled = false;
    }
    return false;
    
}

async function resetForm_lsnipplesizescore(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Nipple Size Score');
	document.querySelector("#lsnipplesizescore_id").value = 0;
	document.querySelector("#lsnipplesizescore_name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

<?php

   
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
	
?>

