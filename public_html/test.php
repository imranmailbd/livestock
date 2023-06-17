
CREATE TABLE IF NOT EXISTS `lsgroups` (
  `lsgroups_id` int NOT NULL AUTO_INCREMENT,
  `lsgroups_publish` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  `accounts_id` int NOT NULL,
  `user_id` int NOT NULL,
  `lsgroups_name` varchar(35) COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`lsgroups_id`),
  KEY `created_by` (`accounts_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;


/*======lsgroups Module======*/
async function lsgroups(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Categories')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top: 0; background: #fff; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let lsgroupsContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                let lsgroupsRow = cTag('div', {class: "flexSpaBetRow"});
                    const lsgroupsHeaderColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    lsgroupsHeaderColumn.appendChild(subHeader_Search_Bar(Translate('lsgroups'),Translate('Search Categories'),filter_Manage_Data_lsgroups));
                        const lsgroupsTableRow = cTag('div', {class: "flexSpaBetRow"});
                            const lsgroupsTableColumn = cTag('div', {class: "columnXS12"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('lsgroups Name');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            lsgroupsTableColumn.appendChild(listTable);
                        lsgroupsTableRow.appendChild(lsgroupsTableColumn);
                    lsgroupsHeaderColumn.appendChild(lsgroupsTableRow);
                    addPaginationRowFlex(lsgroupsHeaderColumn);
                lsgroupsRow.appendChild(lsgroupsHeaderColumn);

                    const addProductlsgroups = cTag('div', {class: "columnXS12 columnMD5"});
                        let productlsgroupsHeader = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;",  id: "formtitle"});
                        productlsgroupsHeader.innerHTML =Translate('Add New Product lsgroups');
                    addProductlsgroups.appendChild(productlsgroupsHeader);

                        const addProductlsgroupsForm = cTag('form', {'action': "#", name: "frmlsgroups", id: "frmlsgroups", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        addProductlsgroupsForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'lsgroups_name',AJsave_lsgroups));
                            const addProductlsgroupsRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let lsgroupsLabel = cTag('label', {'for': "lsgroups_name"});
                                lsgroupsLabel.innerHTML = Translate('Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                lsgroupsLabel.appendChild(requiredField);
                            addProductlsgroupsRow.appendChild(lsgroupsLabel);
                                let input = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "lsgroups_name", id: "lsgroups_name",  'value': "", 'size': 35, 'maxlength': 35});
                            addProductlsgroupsRow.appendChild(input);
                        addProductlsgroupsForm.appendChild(addProductlsgroupsRow);
                        addProductlsgroupsForm.appendChild(controller_bar('lsgroups_id',resetForm_lsgroups));
                    addProductlsgroups.appendChild(addProductlsgroupsForm);
                lsgroupsRow.appendChild(addProductlsgroups);
            callOutDiv.appendChild(lsgroupsRow);
        lsgroupsContainer.appendChild(callOutDiv);
    parentRow.appendChild(lsgroupsContainer);
    showTableData.appendChild(parentRow);   

    addCustomeEventListener('filter',filter_Manage_Data_lsgroups);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_lsgroups);
    addCustomeEventListener('reset',resetForm_lsgroups);
    getSessionData();    
    filter_Manage_Data_lsgroups(true);
}

async function filter_Manage_Data_lsgroups(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_lsgroups/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('lsgroups Name'), 'align':'left'}],'lsgroups',filter_Manage_Data_lsgroups,resetForm_lsgroups);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_lsgroups(){
    const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_lsgroups';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('lsgroups Name'), 'align':'left'}],'lsgroups',filter_Manage_Data_lsgroups,resetForm_lsgroups);
        onClickPagination();
    }
}

async function AJsave_lsgroups(event=false){
    if(event){event.preventDefault();}

	let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmlsgroups");
    const url = '/'+segment1+'/AJsave_lsgroups';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
            resetForm_lsgroups();
            if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
            }
            else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
            }
            filter_Manage_Data_lsgroups();	
        }
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occurred while adding new lsgroups! Please try again.'), Translate('Ok'));
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

async function resetForm_lsgroups(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Category');
	document.querySelector("#lsgroups_id").value = 0;
	document.querySelector("#lsgroups_name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

<?php

   
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
	
?>

