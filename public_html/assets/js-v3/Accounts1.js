//============Manage Sub-Group=========//
function filter_Accounts_subGroup(){
	var limit = j('#limit').val();
	var page = 1;
	j("#page").val(page);
	
	var jsonData = {};
	jsonData['faccount_type'] = j('#faccount_type').val();			
	jsonData['keyword_search'] = j('#keyword_search').val();			
	jsonData['totalRows'] = j('#totalTableRows').val();
	jsonData['rowHeight'] = j('#rowHeight').val();
	jsonData['limit'] = limit;
	jsonData['page'] = page;
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_subGroup/filter",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#totalTableRows").val(data.totalRows);
			j("#tableRows").html(data.tableRows);
			
			onClickPagination();
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function(){
		connection_dialog(filter_Accounts_subGroup);
	});
}

function loadTableRows_Accounts_subGroup(){
	var jsonData = {};
	jsonData['faccount_type'] = j('#faccount_type').val();			
	jsonData['keyword_search'] = j('#keyword_search').val();			
	jsonData['totalRows'] = j('#totalTableRows').val();
	jsonData['rowHeight'] = j('#rowHeight').val();	
	jsonData['limit'] = j('#limit').val();
	jsonData['page'] = j('#page').val();
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_subGroup",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#tableRows").html(data.tableRows);
			onClickPagination();
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function() {			
		connection_dialog(loadTableRows_Accounts_subGroup);
	});
}

function AJgetSubGroupPopup(sub_group_id){
	
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetSubGroupPopup",
		data: {"sub_group_id":sub_group_id}
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			var formhtml = '<div id="error_subGroup" class="errormsg"></div>'+
							'<form action="#" name="frmSubGroup" id="frmSubGroup" onsubmit="return saveSubGroupForm();" enctype="multipart/form-data" method="post" accept-charset="utf-8">'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="account_type">Group Name<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="account_type" id="account_type">'+
											data.accTypOpt+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="name">Sub-Group Name<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<input autocomplete="off" required="required" type="text" class="form-control" name="name" id="name" value="'+data.name+'" maxlength="50" />'+
									'</div>'+
								'</div>'+
								'<input type="hidden" name="sub_group_id" value="'+sub_group_id+'">'+
							'</form>';
			
			form600dialog('Sub-Group Information', formhtml, 'Save', saveSubGroupForm);
			
			setTimeout(function() {
				j("#account_type").focus();
			}, 500);
			
		}
	})
	.fail(function() {		
		connection_dialog(AJgetSubGroupPopup, sub_group_id);
	});
	
	return true;
}

function saveSubGroupForm(){
	var errorObj = document.getElementById('error_subGroup');
	errorObj.innerHTML = '';
	if(j("#account_type").val()==0){
		errorObj.innerHTML = '<p>Missing Group Name</p>';
		j("#account_type").focus();
		return false;
	}
	errorObj.innerHTML = '';
	if(j("#name").val()==''){
		document.getElementById('error_subGroup').innerHTML = '<p>Missing Sub-Group Name</p>';
		j("#name").focus();
		return false;
	}
	
	errorObj.innerHTML = '';
	j(".btnmodel").html('Saving...').prop('disabled', true);
			
	j.ajax({method: "POST",dataType: "json",
		url: "/Accounts/AJsaveSubGroup/",
		data:j("#frmSubGroup").serialize(),
	})
	.done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else if(data.savemsg !='error'){
			if(j("#pageURI").val()=='Accounts/subGroup')
				checkAndLoadFilterData();
			j( "#form-dialog" ).html('').dialog( "close" );			
		}
		else{						
			errorObj.innerHTML = data.returnStr;
		}		
		j(".btnmodel").html('Save').prop('disabled', false);
	})
	.fail(function() {		
		connection_dialog(saveSubGroupForm);
	});	
	return false;
}

//============Manage Ledger============//
function ledgerData(){
	var fsub_group_id = j('#fsub_group_id').val();
	var fparent_ledger_id = j('#fparent_ledger_id').val();
	var jsonData = {};
	jsonData['factive_ledger'] = j('#factive_ledger').val();			
	jsonData['faccount_type'] = j('#faccount_type').val();			
	jsonData['fsub_group_id'] = fsub_group_id;
	jsonData['fparent_ledger_id'] = fparent_ledger_id;
	jsonData['fvisible_on'] = j('#fvisible_on').val();	
	jsonData['keyword_search'] = j('#keyword_search').val();			
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_ledger",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#fsub_group_id").html(data.subGroOpt);
			j("#fparent_ledger_id").html(data.parLedOpt);
			j("#tableRows").html(data.tableRows);
			j('#fsub_group_id').val(fsub_group_id);
			j('#fparent_ledger_id').val(fparent_ledger_id);
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function(){
		connection_dialog(ledgerData);
	});	
}

function AJgetLedgerPopup(sparent_ledger_id, ledger_id){
	
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetLedgerPopup",
		data: {"ledger_id":ledger_id, sparent_ledger_id:sparent_ledger_id}
	}).done(function( data ){
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			var formhtml = '<div id="error_ledger" class="errormsg"></div>'+
							'<form action="#" name="frmLedger" id="frmLedger" onsubmit="return saveLedgerForm();" enctype="multipart/form-data" method="post" accept-charset="utf-8">'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="account_type">Group Name<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="account_type" id="account_type" onChange="setSubGroOpt(\'\');">'+
											data.accTypOpt+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="sub_group_id">Sub-Group Name<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="sub_group_id" id="sub_group_id" onChange="setParLedOpt();">'+
											data.subGroOpt+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="parent_ledger_id">Parent Ledger: </label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="parent_ledger_id" id="parent_ledger_id" onChange="setParSubLedOpt(\'\')">'+
											data.parLedOpt+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="parent_sub_ledger_id">Sub-Ledger: </label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="parent_sub_ledger_id" id="parent_sub_ledger_id" onChange="setParSub2LedOpt(\'\')">'+
											data.parSubLedOpt+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="parent_sub2_ledger_id">Sub-Sub-Ledger: </label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="parent_sub2_ledger_id" id="parent_sub2_ledger_id">'+
											data.parSub2LedOpt+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="name">Name<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<input autocomplete="off" required="required" type="text" class="form-control" name="name" id="name" value="'+data.name+'" maxlength="50" />'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="visible_on">Visible On<span class="required">*</span></label><br>'+
										'<span style="font-size:10px">Do not check any if visible for all voucher.</span>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										data.visOnOpt+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="debit">Debit<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="debit" id="debit" onChange="checkDebitCredit(\'debit\');">'+
											'<option value="1">Increase</option>'+
											'<option value="-1">Decrease</option>'+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="credit">Credit<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="credit" id="credit" onChange="checkDebitCredit(\'credit\');">'+
											'<option value="1">Increase</option>'+
											'<option value="-1">Decrease</option>'+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="opening_date">Opening Date</label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<input type="text" class="form-control" name="opening_date" id="opening_date" value="'+data.opening_date+'" maxlength="10" />'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="closing_date">Closing Date</label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<input type="text" class="form-control" name="closing_date" id="closing_date" value="'+data.closing_date+'" maxlength="10" />'+
									'</div>'+
								'</div>'+
								'<div class="form-group row" id="opeBalRow">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="opening_balance">Opening Balance</label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<input autocomplete="off" type="text" class="form-control pricefield" name="opening_balance" id="opening_balance" value="'+data.opening_balance+'" maxlength="14" />'+
									'</div>'+
								'</div>'+
								'<input type="hidden" name="ledger_id" value="'+ledger_id+'">'+
							'</form>';
			
			form600dialog('Ledger Information', formhtml, 'Save', saveLedgerForm);
			
			setTimeout(function() {
				j("#account_type").val(data.account_type);
				j('#account_type').val(data.account_type);
				j('#sub_group_id').val(data.sub_group_id);
				j('#parent_ledger_id').val(data.parent_ledger_id);
				j('#parent_sub_ledger_id').val(data.parent_sub_ledger_id);
				j('#parent_sub2_ledger_id').val(data.parent_sub2_ledger_id);
				
				j('#debit').val(data.debit);
				j('#credit').val(data.credit);
				j("#account_type").focus();
				setValidPrice();
				j('#opening_date, #closing_date').datepicker({
					dateFormat: "yy-mm-dd",
					onRender: function(date) {
						//return date.valueOf() < now.valueOf() ? 'disabled' : '';
					},
					autoclose: true
				});
			}, 500);
			
		}
	})
	.fail(function() {		
		connection_dialog(AJgetLedgerPopup, sparent_ledger_id, ledger_id);
	});
	
	return true;
}

function setParLedOpt(preLetter=''){
	var sub_group_id = j("#"+preLetter+"sub_group_id").val();
	if(sub_group_id==0){
		j('#'+preLetter+'parent_ledger_id').html("<option value=\"0\">Parent Ledger</option>");
	}
	else{
		var parent_ledger_id = j("#"+preLetter+"parent_ledger_id").val();
		j.ajax({method: "POST", 
			url: "/Accounts/setParLedOpt/"+sub_group_id+'/1',
			data: {}
		}).done(function( data ){
			j('#'+preLetter+'parent_ledger_id').html(data);
			checkSetSelectValue(preLetter+'parent_ledger_id', parent_ledger_id);
		})
		.fail(function(){
			connection_dialog(setParLedOpt, preLetter);
		});		
	}
}

function setParSubLedOpt(preLetter=''){
	var parent_ledger_id = j("#"+preLetter+"parent_ledger_id").val();
	if(parent_ledger_id==0){
		j('#'+preLetter+'parent_sub_ledger_id').html("<option value=\"0\">Sub-Parent Ledger</option>");
	}
	else{
		var parent_sub_ledger_id = j("#"+preLetter+"parent_sub_ledger_id").val();
		j.ajax({method: "POST",
			url: "/Accounts/setParSubLedOpt/"+parent_ledger_id+'/'+1,
			data:{}
		}).done(function( data ){
			console.log(data);
			j('#'+preLetter+'parent_sub_ledger_id').html(data);
			checkSetSelectValue(preLetter+'parent_sub_ledger_id', parent_sub_ledger_id);
		})
		.fail(function(){
			connection_dialog(setParSubLedOpt, preLetter);
		});
	}
}

function setParSub2LedOpt(preLetter=''){
	var parent_sub_ledger_id = j("#"+preLetter+"parent_sub_ledger_id").val();
	if(parent_sub_ledger_id==0){
		j('#'+preLetter+'parent_sub2_ledger_id').html("<option value=\"0\">Parent Sub-Sub-Ledger</option>");
	}
	else{
		var parent_sub2_ledger_id = j("#"+preLetter+"parent_sub2_ledger_id").val();
		j.ajax({method: "POST",
			url: "/Accounts/setParSub2LedOpt/"+parent_sub_ledger_id+'/'+1,
			data:{}
		}).done(function( data ){
			console.log(data);
			j('#'+preLetter+'parent_sub2_ledger_id').html(data);
			checkSetSelectValue(preLetter+'parent_sub2_ledger_id', parent_sub2_ledger_id);
		})
		.fail(function(){
			connection_dialog(setParSub2LedOpt, preLetter);
		});
	}
}

function checkSetSelectValue(idName, defaultVal){
    
    var valueExists = 0;
    var optionList = document.querySelectorAll("#"+idName+" option");
    optionList.forEach(option=>{
        if(option.value ==defaultVal){
            valueExists++;
        }
    });
    if(valueExists>0){
        document.getElementById(idName).value = defaultVal;
    }
}

function AJgetLedgerPopup1220(sparent_ledger_id, ledger_id){
	
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetLedgerPopup",
		data: {"ledger_id":ledger_id, sparent_ledger_id:sparent_ledger_id}
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			var formhtml = '<div id="error_ledger" class="errormsg"></div>'+
							'<form action="#" name="frmLedger" id="frmLedger" onsubmit="return saveLedgerForm();" enctype="multipart/form-data" method="post" accept-charset="utf-8">'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="account_type">Group Name<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="account_type" id="account_type" onChange="setSubGroOpt(\'\')">'+
											data.accTypOpt+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="sub_group_id">Sub-Group Name<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="sub_group_id" id="sub_group_id">'+
											data.subGroOpt+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="parent_ledger_id">Parent Ledger: </label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="parent_ledger_id" id="parent_ledger_id" onChange="setSubLedOpt(\'\')">'+
											data.parLedOpt+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="name">Name<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<input autocomplete="off" required="required" type="text" class="form-control" name="name" id="name" value="'+data.name+'" maxlength="50" />'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="visible_on">Visible On<span class="required">*</span></label><br>'+
										'<span style="font-size:10px">Do not check any if visible for all voucher.</span>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										data.visOnOpt+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="debit">Debit<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="debit" id="debit" onChange="checkDebitCredit(\'debit\');">'+
											'<option value="1">Increase</option>'+
											'<option value="-1">Decrease</option>'+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="credit">Credit<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<select required="required" class="form-control" name="credit" id="credit" onChange="checkDebitCredit(\'credit\');">'+
											'<option value="1">Increase</option>'+
											'<option value="-1">Decrease</option>'+
										'</select>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="opening_date">Opening Date</label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<input readonly type="text" class="form-control" name="opening_date" id="opening_date" value="'+data.opening_date+'" maxlength="10" />'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="closing_date">Closing Date</label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<input readonly type="text" class="form-control" name="closing_date" id="closing_date" value="'+data.closing_date+'" maxlength="10" />'+
									'</div>'+
								'</div>'+
								'<div class="form-group row" id="opeBalRow">'+
									'<div class="col-sm-4" align="right">'+ 
										'<label for="opening_balance">Opening Balance</label>'+
									'</div>'+
									'<div class="col-sm-8" align="left">'+
										'<input autocomplete="off" type="text" class="form-control pricefield" name="opening_balance" id="opening_balance" value="'+data.opening_balance+'" maxlength="14" />'+
									'</div>'+
								'</div>'+
								'<input type="hidden" name="ledger_id" value="'+ledger_id+'">'+
							'</form>';
			
			form600dialog('Ledger Information', formhtml, 'Save', saveLedgerForm);
			
			setTimeout(function() {
				if(sparent_ledger_id==0){
					j('#account_type').val(data.account_type);
					setSubGroOpt('');
				}
				if(data.sub_group_id>0){j('#sub_group_id').val(parseInt(data.sub_group_id));}
				if(data.parent_ledger_id>0){j('#parent_ledger_id').val(parseInt(data.parent_ledger_id));}
				j('#debit').val(data.debit);
				j('#credit').val(data.credit);
				j("#account_type").focus();
				setValidPrice();				
				j('#opening_date, #closing_date').datepicker({
					dateFormat: "yy-mm-dd",
					onRender: function(date) {
						//return date.valueOf() < now.valueOf() ? 'disabled' : '';
					},
					autoclose: true
				});
			}, 500);
			
		}
	})
	.fail(function() {		
		connection_dialog(AJgetLedgerPopup, ledger_id);
	});
	
	return true;
}

function checkDebitCredit(fieldName){
	var fieldVal = j("#"+fieldName).val();
	if(fieldName=='credit'){var changeField = 'debit';}
	else{var changeField = 'credit';}	
	if(fieldVal==1){var changeVal = -1;}
	else{var changeVal = 1;}
	j("#"+changeField).val(changeVal);
}

function saveLedgerForm(){
	var errorObj = document.getElementById('error_ledger');
	errorObj.innerHTML = '';
	if(j("#account_type").val()==0){
		errorObj.innerHTML = '<p>Missing Group Name</p>';
		j("#account_type").focus();
		return false;
	}
	errorObj.innerHTML = '';
	if(j("#sub_group_id").val()==0){
		errorObj.innerHTML = '<p>Missing Sub-Group Name</p>';
		j("#sub_group_id").focus();
		return false;
	}
	errorObj.innerHTML = '';
	if(j("#name").val()==''){
		document.getElementById('error_ledger').innerHTML = '<p>Missing Ledger Name</p>';
		j("#name").focus();
		return false;
	}
	errorObj.innerHTML = '';
	if(j("#debit").val()==''){
		document.getElementById('error_ledger').innerHTML = '<p>Missing Debit</p>';
		j("#debit").focus();
		return false;
	}
	errorObj.innerHTML = '';
	if(j("#credit").val()==''){
		document.getElementById('error_ledger').innerHTML = '<p>Missing Credit</p>';
		j("#credit").focus();
		return false;
	}
	
	errorObj.innerHTML = '';
	j(".btnmodel").html('Saving...').prop('disabled', true);
			
	j.ajax({method: "POST",dataType: "json",
		url: "/Accounts/AJsaveLedger/",
		data:j("#frmLedger").serialize(),
	})
	.done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else if(data.savemsg !='error'){
			if(j("#chartContainer").length){
				location.reload();
			}
			else{
				ledgerData();
			}
			j( "#form-dialog" ).html('').dialog( "close" );			
		}
		else{						
			errorObj.innerHTML = data.message;
		}		
		j(".btnmodel").html('Save').prop('disabled', false);
	})
	.fail(function() {		
		connection_dialog(saveLedgerForm);
	});	
	return false;
}

function setSubGroOpt(preLetter){
	var account_type = j("#"+preLetter+"account_type").val();
	var sub_group_id = j("#"+preLetter+"sub_group_id").val();
	if(j("#opeBalRow").length){
		if(j.inArray(account_type, ['1', '2', '3'])>=0){
			j("#opeBalRow").slideDown('fast');			
		}
		else{
			j("#opeBalRow").slideUp('fast');
			j('#opening_balance').val(0);
		}
	}
	
	if(account_type==0){
		j('#'+preLetter+'sub_group_id').html("<option value=\"0\">Select Sub-Group Name</option>");
	}
	else{
		j.ajax({method: "POST", dataType: "json",
			url: "/Accounts/setSubGroOpt",
			data: {"account_type":account_type}
		}).done(function( data ) {
			if(data.login != ''){window.location = '/'+data.login;}
			else{
				j('#'+preLetter+'sub_group_id').html(data.subGroOpt);
				j('#'+preLetter+'sub_group_id').val(sub_group_id);
			}
		})
		.fail(function() {		
			connection_dialog(setSubGroOpt, preLetter);
		});		
	}
}

function filter_Accounts_ledgerView(){
	var limit = j('#limit').val();
	var page = 1;
	j("#page").val(page);
	
	var jsonData = {};
	jsonData['ledger_id'] = j('#table_idValue').val();			
	jsonData['date_range'] = j('#date_range').val();			
	jsonData['totalRows'] = j('#totalTableRows').val();
	jsonData['rowHeight'] = j('#rowHeight').val();
	jsonData['limit'] = limit;
	jsonData['page'] = page;
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetHPageLedger/filter",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#totalTableRows").val(data.totalRows);
			j("#tableRows").html(data.tableRows);
			
			onClickPagination();
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function(){
		connection_dialog(filter_Accounts_ledgerView);
	});
}

function loadTableRows_Accounts_ledgerView(){
	var jsonData = {};
	jsonData['ledger_id'] = j('#table_idValue').val();			
	jsonData['date_range'] = j('#date_range').val();			
	jsonData['totalRows'] = j('#totalTableRows').val();
	jsonData['rowHeight'] = j('#rowHeight').val();	
	jsonData['limit'] = j('#limit').val();
	jsonData['page'] = j('#page').val();
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetHPageLedger",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#tableRows").html(data.tableRows);
			onClickPagination();
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function() {			
		connection_dialog(loadTableRows_Accounts_ledgerView);
	});
}

function printLedger(){
	var divContents = j("#no-more-tables").html();
	var title = stripslashes(j("#ledgerName").html())+' Transactions ('+stripslashes(j("#fromtodata").html())+')';
	var filterby = '';
	if(j("#date_range").val() !=''){
		filterby += 'Date Range: '+j("#date_range").val();
	}
	var now = new Date();
	var todayDate = now.getDate()+'-'+(now.getMonth() + 1)+'-'+now.getFullYear();
	
	var additionaltoprows = '<div class="width100">'+
								'<div class="txtcenter txt20bold">'+stripslashes(COMPANYNAME)+'</div>'+
							'</div>'+
							'<div class="width100 mtop10">'+
								'<div class="floatleft txtleft txt18bold">'+stripslashes(title)+'</div>'+
								'<div class="floatright txtright txt16normal">'+todayDate+'</div>'+
							'</div>'+
							'<div class="width100">'+
								'<hr class="mtop10 mbottom0">'+
							'</div>'+
							'<div class="width100 mtop10" id="filterby">'+filterby+'</div>';
	divContents = divContents.replace('<thead class="cf">', '<thead class="cf"><tr><td class="bgnone" colspan="8">'+additionaltoprows+'</td></tr>');
	
	var day = new Date();
	var id = day.getTime();
	var w = 900;
	var h = 600;
	var scrl = 1;
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
	
	var printWindow = window.open('', '" + id + "', winprops);
	printWindow.document.write('<!DOCTYPE html><html><head><title>'+title+'</title><meta charset="utf-8">');

	printWindow.document.write('<link rel="stylesheet" href="/assets/admin/images/Accounts/print.css">');
	
	printWindow.document.write('</head><body>');
	
	printWindow.document.write(divContents);
	printWindow.document.write('</body></html>');
	printWindow.document.close();
	var is_chrome = Boolean(window.chrome);
	if (is_chrome) {
		printWindow.onload = function () {
			printWindow.window.print();
			document_focus = true;
		};
	}
	else {
		
		var document_focus = false;
		printWindow.document.onreadystatechange = function () {
			var state = document.readyState
			if (state == 'interactive') {}
			else if (state == 'complete') {
				setTimeout(function(){
					document.getElementById('interactive');
					printWindow.print();
					document_focus = true;
				},1000);
			}
		}
	}
	printWindow.setInterval(function() {
		var deviceOpSy = getMobileOperatingSystem();
		if (document_focus === true && deviceOpSy=='unknown') { printWindow.window.close(); }
	}, 500);
}

//============Manage Voucher1============//
function filter_Accounts_receiptVoucher(){filter_Voucher1();}
function loadTableRows_Accounts_receiptVoucher(){loadTableRows_Voucher1();}

function filter_Accounts_paymentVoucher(){filter_Voucher1();}
function loadTableRows_Accounts_paymentVoucher(){loadTableRows_Voucher1();}

function filter_Accounts_journalVoucher(){filter_Voucher1();}
function loadTableRows_Accounts_journalVoucher(){loadTableRows_Voucher1();}

function filter_Accounts_contraVoucher(){filter_Voucher1();}
function loadTableRows_Accounts_contraVoucher(){loadTableRows_Voucher1();}

function filter_Voucher1(){
	var limit = j('#limit').val();
	var page = 1;
	j("#page").val(page);
	var fsub_group_id = j('#fsub_group_id').val();
	var fledger_id = j('#fledger_id').val();
	var jsonData = {};
	jsonData['fpublish'] = j('#fpublish').val();			
	jsonData['fvoucher_type'] = j('#fvoucher_type').val();			
	jsonData['faccount_type'] = j('#faccount_type').val();			
	jsonData['fsub_group_id'] = fsub_group_id;
	jsonData['fledger_id'] = fledger_id;
	jsonData['keyword_search'] = j('#keyword_search').val();			
	jsonData['totalRows'] = j('#totalTableRows').val();
	jsonData['rowHeight'] = j('#rowHeight').val();
	jsonData['limit'] = limit;
	jsonData['page'] = page;
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_Voucher1/filter",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#fsub_group_id").html(data.subGroOpt);
			j("#fledger_id").html(data.parLedOpt);
			
			j('#fsub_group_id').val(fsub_group_id);
			j('#fledger_id').val(fledger_id);
			
			j("#totalTableRows").val(data.totalRows);
			j("#tableRows").html(data.tableRows);
			
			onClickPagination();
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function(){
		connection_dialog(filter_Voucher1);
	});
}

function loadTableRows_Voucher1(){
	var jsonData = {};
	jsonData['fpublish'] = j('#fpublish').val();			
	jsonData['fvoucher_type'] = j('#fvoucher_type').val();			
	jsonData['faccount_type'] = j('#faccount_type').val();			
	jsonData['fsub_group_id'] = j('#fsub_group_id').val();			
	jsonData['fledger_id'] = j('#fledger_id').val();	
	jsonData['keyword_search'] = j('#keyword_search').val();			
	jsonData['totalRows'] = j('#totalTableRows').val();
	jsonData['rowHeight'] = j('#rowHeight').val();	
	jsonData['limit'] = j('#limit').val();
	jsonData['page'] = j('#page').val();
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_Voucher1",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#tableRows").html(data.tableRows);
			onClickPagination();
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function() {			
		connection_dialog(loadTableRows_Voucher1);
	});
}

function AJgetVoucher1Popup(voucher_id, voucher_type){
	
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetVoucher1Popup",
		data: {"voucher_id":voucher_id, voucher_type:voucher_type}
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			var lastCreRows = [];
			var formhtml = '<div id="error_Voucher1" class="errormsg"></div>'+
							'<form action="#" name="frmVoucher" id="frmVoucher" onsubmit="return AJsaveReceiptVoucher();" enctype="multipart/form-data" method="post" accept-charset="utf-8">'+
								'<div class="form-group row">'+
									'<div class="col-sm-2" align="right">'+ 
										'<label for="voucher_no">Voucher No.<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-3" align="left">'+
										'<input type="hidden" name="voucher_type" value="'+data.voucher_type+'">'+
										'<input type="hidden" name="voucher_id" value="'+data.voucher_id+'">'+
										'<input readonly required="required" type="text" class="form-control" name="voucher_no" id="voucher_no" value="'+data.voucher_no+'" maxlength="11" />'+
									'</div>'+
									'<div class="col-sm-2" align="right">'+ 
										'<label for="voucher_date">Voucher Date<span class="required">*</span></label>'+
									'</div>'+
									'<div class="col-sm-3" align="left">'+
										'<input readonly required="required" type="text" class="form-control" name="voucher_date" id="voucher_date" value="'+data.voucher_date+'" maxlength="10" />'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-12" align="left">'+
										'<ul class="multiplerowlist">'+
											'<li class="innerPage">'+
												'<div class="width100per borderbottom">'+
													'<div class="col-sm-4"><h3>Ledger Name</h3></div>'+
													'<div class="col-sm-2"><h3>Narration</h3></div>'+
													'<div class="col-sm-2" align="left"><h3>Transaction</h3></div>'+
													'<div class="col-sm-2" align="left"><h3>Debit</h3></div>'+
													'<div class="col-sm-2" align="left"><h3>Credit</h3></div>'+
												'</div>'+
												'<ul class="multiplerowlist" style="position:relative;" id="vList">';
													if(data.voucherLists.length>0){
														var lcsl=0;
														var tsl=0;
														j.each( data.voucherLists, function( key, value ) {
															tsl++;
															var debit_credit = value[4];
															if(debit_credit==-1){
																lastCreRows = value;
																lcsl = tsl;
															}
														});
														var sl=0;
														j.each( data.voucherLists, function( key, value ) {
															sl++;
															var voucher_list_id = value[0];
															var ledger_id = value[1];
															var ledgerName = value[2];
															var narration = value[3];
															var debit_credit = value[4];
															var amount = value[5];
															var creSel = '';
															var debDis = '';
															var creDis = ' style="display:none;"';
															var debit = amount;
															var credit = 0;
															if(debit_credit==-1){
																creSel = ' selected';
																var debDis = ' style="display:none;"';
																var creDis = '';
																debit = 0;
																credit = amount;
															}
															if(sl !=lcsl){
																var rowClass = ' lightgreenrow';
																if(sl%2==0){rowClass = '';}
																
																formhtml += '<li class="width100per'+rowClass+'">'+
'<div class="col-sm-3 paddingleft0 paddingright0">'+
'<input type="text" name="ledgerName[]" class="form-control ledgerName" value="'+ledgerName+'">'+
'<input type="hidden" name="voucher_list_id[]" class="voucher_list_id" value="'+voucher_list_id+'">'+														
'<input type="hidden" name="ledger_id[]" class="ledger_id" value="'+ledger_id+'">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="narration[]" class="form-control narration" placeholder="Narration" value="'+narration+'">'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<select required="required" class="form-control debit_credit" name="debit_credit[]">'+
'<option value="1">Debit</option>'+
'<option'+creSel+' value="-1">Credit</option>'+
'</select>'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="debit[]" class="form-control debit pricefield"'+debDis+' placeholder="Amount" value="'+debit+'">'+
'</div>'+
'<div class="col-sm-2" align="left">'+
'<input type="text" name="credit[]" class="form-control credit pricefield"'+creDis+' placeholder="Amount" value="'+credit+'">'+
'</div>'+
'</li>';
															}
														});
													}
													else{
														formhtml += '<li class="width100per lightgreenrow">'+
'<div class="col-sm-3 paddingleft0 paddingright0">'+
'<input type="text" name="ledgerName[]" class="form-control ledgerName" placeholder="Ledger Name">'+
'<input type="hidden" name="voucher_list_id[]" class="voucher_list_id" value="0">'+														
'<input type="hidden" name="ledger_id[]" class="ledger_id" value="0">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="narration[]" class="form-control narration" placeholder="Narration" value="">'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<select required="required" class="form-control debit_credit" name="debit_credit[]">'+
'<option value="1">Debit</option>'+
'<option value="-1">Credit</option>'+
'</select>'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="debit[]" class="form-control debit pricefield" placeholder="Amount" value="0">'+
'</div>'+
'<div class="col-sm-2" align="left">'+
'<input type="text" name="credit[]" class="form-control credit pricefield" placeholder="Amount" style="display:none;" value="0">'+
'</div>'+
'</li>';
													}
													formhtml += '</ul>'+
													'<div class="addnewplusbotrig" style="top:50px;">'+
'<a href="javascript:void(0);" title="Add More Voucher List" onClick="addMoreVList();">'+
'<img align="absmiddle" alt="Add More Voucher List" title="Add More Voucher List" src="/assets/admin/images/Accounts/plus20x25.png">'+
'</a>'+
'</div>';

if(data.voucherLists.length==0 || lastCreRows.length>0){
	var voucher_list_id = 0;
	var ledger_id = 0;
	var ledgerName = '';
	var narration = '';
	var credit = 0;
	if(lastCreRows.length>0){
		voucher_list_id = lastCreRows[0];
		ledger_id = lastCreRows[1];
		ledgerName = lastCreRows[2];
		narration = lastCreRows[3];
		credit = lastCreRows[5];
	}
	
	formhtml += '<div class="col-sm-12">'+
'<div class="col-sm-3 paddingleft0 paddingright0">'+
'<input type="text" name="ledgerName[]" class="form-control ledgerName" placeholder="Ledger Name" value="'+ledgerName+'">'+
'<input type="hidden" name="voucher_list_id[]" class="voucher_list_id" value="'+voucher_list_id+'">'+														
'<input type="hidden" name="ledger_id[]" class="ledger_id" value="'+ledger_id+'">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="narration[]" class="form-control narration" placeholder="Narration" value="'+narration+'">'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<select required="required" class="form-control debit_credit" name="debit_credit[]">'+
'<option value="1">Debit</option>'+
'<option value="-1" selected>Credit</option>'+
'</select>'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="debit[]" class="form-control debit pricefield" placeholder="Amount" style="display:none;" value="0">'+
'</div>'+
'<div class="col-sm-2" align="left">'+
'<input type="text" name="credit[]" class="form-control credit pricefield" placeholder="Amount" value="'+credit+'">'+
'</div>'+
'</div>';
}
formhtml += '<div class="width100per mtop10 ptop10 lightpinkrow">'+
'<input type="hidden" required="required" value="Credit" id="adjustwith">'+
'<div id="error_voucherList" class="col-sm-6 errormsg"></div>'+
'<div class="col-sm-2" align="right"><h3>Total:</h3></div>'+
'<div class="col-sm-2" align="right"><h3 id="totDebit">0.00</h3></div>'+
'<div class="col-sm-2" align="right"><h3 id="totCredit">0.00</h3></div>'+
'</div>'+
'</li>'+
'</ul>'+
'</div>'+
'</div>'+
'</form>';
			
			form1000dialog(data.voucherType+' Voucher Information', formhtml, AJsaveVoucher1);
			
			setTimeout(function() {
				j('#voucher_date').datepicker({
					dateFormat: "yy-mm-dd",
					onRender: function(date) {
						//return date.valueOf() < now.valueOf() ? 'disabled' : '';
					},
					autoclose: true,
					onSelect: function (date) {
						var voucher_type = document.frmVoucher.voucher_type.value;
						getVoucherNo(date, voucher_type, voucher_id);
					}
				});
				
				j("#voucher_no").focus();
				j(".debit_credit").change(function() {
					checkTransactionType(j(this));
				});
				AJautoComplete_Ledger();
				setValidPrice();
				j(".debit, .credit").keyup(function (e) {calDebCre();});
				j(".narration").keyup(function() {
					checkNarration(j(this));
				});
				
				calDebCre();
			}, 500);
			
		}
	})
	.fail(function() {		
		connection_dialog(AJgetVoucher1Popup, voucher_id, voucher_type);
	});
	
	return true;
}

function addMoreVList(){
	var totVlists = j("ul#vList li").length;
	var rowClass = ' lightgreenrow';
	if(totVlists%2 !=0){rowClass = '';}
	//alert(rowClass);
	newrow = '<div class="col-sm-3 paddingleft0 paddingright0">'+
'<input type="text" name="ledgerName[]" class="form-control ledgerName" placeholder="Ledger Name">'+
'<input type="hidden" name="voucher_list_id[]" class="voucher_list_id" value="0">'+														
'<input type="hidden" name="ledger_id[]" class="ledger_id" value="0">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="narration[]" class="form-control narration" placeholder="Narration" value="">'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<select required="required" class="form-control debit_credit" name="debit_credit[]">'+
'<option value="1">Debit</option>'+
'<option value="-1">Credit</option>'+
'</select>'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="debit[]" class="form-control debit pricefield" placeholder="Amount" value="0">'+
'</div>'+
'<div class="col-sm-2" align="left">'+
'<input type="text" name="credit[]" class="form-control credit pricefield" placeholder="Amount" style="display:none;" value="0">'+
'</div>';
	var newmore_list = j('<li class="width100per'+rowClass+'"></li>').html(newrow);										
	j("ul#vList").append(newmore_list.hide());
	newmore_list.slideDown('fast');
	j(".debit_credit").change(function() {
		checkTransactionType(j(this));
	});
	AJautoComplete_Ledger();
	setValidPrice();
	calDebCre();
	j(".debit, .credit").keyup(function (e) {calDebCre();});
}

function AJsaveVoucher1(){
	var voucher_id = document.frmVoucher.voucher_id.value;
	var voucher_type = document.frmVoucher.voucher_type.value;
	var errorObj = document.getElementById('error_Voucher1');
	errorObj.innerHTML = '';
	if(j("#voucher_date").val()==''){
		errorObj.innerHTML = '<p>Missing Voucher Date</p>';
		j("#voucher_date").focus();
		return false;
	}
	if(checkVList()==false){
		return false;
	}
	
	var totDebit = parseFloat(j('#totDebit').html());
	if(isNaN(totDebit) || totDebit==''){totDebit = 0;}
	var totCredit = parseFloat(j('#totCredit').html());
	if(isNaN(totCredit) || totCredit==''){totCredit = 0;}
	
	var errorObj = document.getElementById('error_voucherList');
	errorObj.innerHTML = '';
	if(totDebit==0){
		errorObj.innerHTML = 'Total debit should be > 0';
		return false;
	}
	errorObj.innerHTML = '';
	if(totCredit==0){
		errorObj.innerHTML = 'Total credit should be > 0';
		return false;
	}
	errorObj.innerHTML = '';
	if(totDebit != totCredit){
		errorObj.innerHTML = 'Total debit is not equal to total credit';
		return false;
	}	
	
	errorObj.innerHTML = '';
	j(".btnmodel").html('Saving...').prop('disabled', true);
			
	j.ajax({method: "POST",dataType: "json",
		url: "/Accounts/AJsaveVoucher1",
		data:j("#frmVoucher").serialize(),
	})
	.done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else if(data.savemsg !='error'){
			filter_Voucher1();
			if(voucher_id==0){
				AJgetVoucher1Popup(0, voucher_type);
			}
			
			j( "#form-dialog" ).html('').dialog( "close" );			
		}
		else{						
			errorObj.innerHTML = data.message;
		}		
		j(".btnmodel").html('Save').prop('disabled', false);
	})
	.fail(function() {		
		connection_dialog(AJsaveVoucher1);
	});	
	return false;
}

//============Manage Voucher2============//
function filter_Accounts_purchaseVoucher(){filter_Voucher2();}
function loadTableRows_Accounts_purchaseVoucher(){loadTableRows_Voucher2();}

function filter_Accounts_salesVoucher(){filter_Voucher2();}
function loadTableRows_Accounts_salesVoucher(){loadTableRows_Voucher2();}

function filter_Voucher2(){
	var limit = j('#limit').val();
	var page = 1;
	j("#page").val(page);
	var fsub_group_id = j('#fsub_group_id').val();
	var fledger_id = j('#fledger_id').val();
	var jsonData = {};
	jsonData['fpublish'] = j('#fpublish').val();			
	jsonData['fvoucher_type'] = j('#fvoucher_type').val();			
	jsonData['faccount_type'] = j('#faccount_type').val();			
	jsonData['fsub_group_id'] = fsub_group_id;
	jsonData['fledger_id'] = fledger_id;
	jsonData['keyword_search'] = j('#keyword_search').val();			
	jsonData['totalRows'] = j('#totalTableRows').val();
	jsonData['rowHeight'] = j('#rowHeight').val();
	jsonData['limit'] = limit;
	jsonData['page'] = page;
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_Voucher2/filter",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#fsub_group_id").html(data.subGroOpt);
			j("#fledger_id").html(data.parLedOpt);
			
			j('#fsub_group_id').val(fsub_group_id);
			j('#fledger_id').val(fledger_id);
			
			j("#totalTableRows").val(data.totalRows);
			j("#tableRows").html(data.tableRows);
			
			onClickPagination();
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function(){
		connection_dialog(filter_Voucher2);
	});
}

function loadTableRows_Voucher2(){
	var jsonData = {};
	jsonData['fpublish'] = j('#fpublish').val();			
	jsonData['fvoucher_type'] = j('#fvoucher_type').val();			
	jsonData['faccount_type'] = j('#faccount_type').val();			
	jsonData['fsub_group_id'] = j('#fsub_group_id').val();			
	jsonData['fledger_id'] = j('#fledger_id').val();	
	jsonData['keyword_search'] = j('#keyword_search').val();			
	jsonData['totalRows'] = j('#totalTableRows').val();
	jsonData['rowHeight'] = j('#rowHeight').val();	
	jsonData['limit'] = j('#limit').val();
	jsonData['page'] = j('#page').val();
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_Voucher2",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#tableRows").html(data.tableRows);
			onClickPagination();
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function() {			
		connection_dialog(loadTableRows_Voucher2);
	});
}

function AJgetVoucher2Popup(voucher_id, voucher_type){
	
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetVoucher2Popup",
		data: {"voucher_id":voucher_id, voucher_type:voucher_type}
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			var lastCreRows = [];
			if(voucher_type==6){
				var pi_invoiceLabel = 'Invoice';
				var lc_phoneLabel = 'LC No.';
				var lc_dateLabel = 'LC Date';
				var qtyLabel = 'Dollar';
				var UnitPriceLabel = 'Currency Rate';
			}
			else{
				var pi_invoiceLabel = 'PI';
				var lc_phoneLabel = 'LC No.';
				var lc_dateLabel = 'LC Date';
				var qtyLabel = 'Qty';
				var UnitPriceLabel = 'Unit Price';
			}
			var formhtml = '<div id="error_Voucher2" class="errormsg"></div>'+
							'<form action="#" name="frmVoucher" id="frmVoucher" onsubmit="return AJsaveReceiptVoucher();" enctype="multipart/form-data" method="post" accept-charset="utf-8">'+
								'<div class="row">'+
									'<div class="col-sm-4">'+ 
										'<div class="form-group row">'+
											'<div class="col-sm-5" align="right">'+ 
												'<label for="voucher_no">V. No.<span class="required">*</span></label>'+
											'</div>'+
											'<div class="col-sm-7 paddingleft0 paddingright0" align="left">'+
												'<input type="hidden" name="voucher_type" value="'+data.voucher_type+'">'+
												'<input type="hidden" name="voucher_id" value="'+data.voucher_id+'">'+
												'<input readonly required="required" type="text" class="form-control" name="voucher_no" id="voucher_no" value="'+data.voucher_no+'" maxlength="11" />'+
											'</div>'+
										'</div>'+
										'<div class="form-group row">'+
											'<div class="col-sm-5" align="right">'+ 
												'<label for="voucher_date">V. Date<span class="required">*</span></label>'+
											'</div>'+
											'<div class="col-sm-7 paddingleft0 paddingright0" align="left">'+
												'<input readonly required="required" type="text" class="form-control" name="voucher_date" id="voucher_date" value="'+data.voucher_date+'" maxlength="10" />'+
											'</div>'+
										'</div>'+
									'</div>'+
									'<div class="col-sm-4">'+ 
										'<div class="form-group row">'+
											'<div class="col-sm-5" align="right">'+ 
												'<label for="pi_invoice_no">'+pi_invoiceLabel+' No.<span class="required">*</span></label>'+
											'</div>'+
											'<div class="col-sm-7 paddingleft0 paddingright0" align="left">'+
												'<input type="text" class="form-control" name="pi_invoice_no" id="pi_invoice_no" value="'+data.pi_invoice_no+'" maxlength="30" />'+
											'</div>'+
										'</div>'+
										'<div class="form-group row">'+
											'<div class="col-sm-5" align="right">'+ 
												'<label for="pi_invoice_date">'+pi_invoiceLabel+' Date<span class="required">*</span></label>'+
											'</div>'+
											'<div class="col-sm-7 paddingleft0 paddingright0" align="left">'+
												'<input type="text" class="form-control" name="pi_invoice_date" id="pi_invoice_date" value="'+data.pi_invoice_date+'" maxlength="10" />'+
											'</div>'+
										'</div>'+
									'</div>'+
									'<div class="col-sm-4">'+ 
										'<div class="form-group row">'+
											'<div class="col-sm-5" align="right">'+ 
												'<label for="lc_phone_no">'+lc_phoneLabel+'</label>'+
											'</div>'+
											'<div class="col-sm-7 paddingleft0" align="left">'+
												'<input type="text" class="form-control" name="lc_phone_no" id="lc_phone_no" value="'+data.lc_phone_no+'" maxlength="200" />'+
											'</div>'+
										'</div>'+
										'<div class="form-group row">'+
											'<div class="col-sm-5" align="right">'+ 
												'<label for="lc_date">'+lc_dateLabel+'</label>'+
											'</div>'+
											'<div class="col-sm-7 paddingleft0" align="left">'+
												'<input readonly type="text" class="form-control" name="lc_date" id="lc_date" value="'+data.lc_date+'" maxlength="10" />'+
											'</div>'+
										'</div>'+
									'</div>'+
								'</div>'+
								'<div class="form-group row">'+
									'<div class="col-sm-12" align="left">'+
										'<ul class="multiplerowlist">'+
											'<li class="innerPage">'+
												'<div class="width100per borderbottom">'+
													'<div class="col-sm-3"><h3>Ledger Name</h3></div>'+
													'<div class="col-sm-3"><h3>Narration</h3></div>'+													
													'<div class="col-sm-6">'+
														'<div class="row">'+
															'<div class="col-sm-3" align="left"><h3>Transaction</h3></div>'+
															'<div class="col-sm-2" align="left"><h3>'+qtyLabel+'</h3></div>'+
															'<div class="col-sm-3" align="left"><h3>'+UnitPriceLabel+'</h3></div>'+
															'<div class="col-sm-2" align="left"><h3>Debit</h3></div>'+
															'<div class="col-sm-2" align="left"><h3>Credit</h3></div>'+
														'</div>'+
													'</div>'+
												'</div>'+
												'<ul class="multiplerowlist" style="position:relative;" id="vList">';
													if(data.voucherLists.length>0){
														var lcsl=0;
														var tsl=0;
														j.each( data.voucherLists, function( key, value ) {
															tsl++;
															var debit_credit = value[4];
															if(debit_credit==-1){
																lastCreRows = value;
																lcsl = tsl;
															}
														});
														var sl=0;
														j.each( data.voucherLists, function( key, value ) {
															sl++;
															var voucher_list_id = value[0];
															var ledger_id = value[1];
															var ledgerName = value[2];
															var narration = value[3];
															var debit_credit = value[4];
															var qty = value[5];
															var unit_price = value[6];
															var amount = value[7];
															var creSel = '';
															var debDis = '';
															var creDis = ' style="display:none;"';
															var debit = amount;
															var credit = 0;
															if(debit_credit==-1){
																creSel = ' selected';
																var debDis = ' style="display:none;"';
																var creDis = '';
																debit = 0;
																credit = amount;
															}
															
															if(sl !=lcsl){
																var rowClass = ' lightgreenrow';
																if(sl%2==0){rowClass = '';}
																
																formhtml += '<li class="width100per'+rowClass+'">'+
'<div class="col-sm-3 paddingleft0 paddingright0">'+
'<input type="text" name="ledgerName[]" class="form-control ledgerName" value="'+ledgerName+'">'+
'<input type="hidden" name="voucher_list_id[]" class="voucher_list_id" value="'+voucher_list_id+'">'+														
'<input type="hidden" name="ledger_id[]" class="ledger_id" value="'+ledger_id+'">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="narration[]" class="form-control narration" placeholder="Narration" value="'+narration+'">'+
'</div>'+
'<div class="col-sm-6">'+
'<div class="row">'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<select required="required" class="form-control debit_credit" name="debit_credit[]">'+
'<option value="1">Debit</option>'+
'<option'+creSel+' value="-1">Credit</option>'+
'</select>'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="qty[]" class="form-control qty qtyfield" placeholder="'+qtyLabel+'" value="'+qty+'">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="unit_price[]" class="form-control unit_price pricefield" placeholder="'+UnitPriceLabel+'" value="'+unit_price+'">'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="debit[]" class="form-control debit pricefield"'+debDis+' placeholder="Amount" value="'+debit+'">'+
'</div>'+
'<div class="col-sm-2" align="left">'+
'<input type="text" name="credit[]" class="form-control credit pricefield"'+creDis+' placeholder="Amount" value="'+credit+'">'+
'</div>'+
'</div>'+
'</div>'+
'</li>';
															}
														});
													}
													else{
														formhtml += '<li class="width100per lightgreenrow">'+
'<div class="col-sm-3 paddingleft0 paddingright0">'+
'<input type="text" name="ledgerName[]" class="form-control ledgerName" placeholder="Ledger Name">'+
'<input type="hidden" name="voucher_list_id[]" class="voucher_list_id" value="0">'+														
'<input type="hidden" name="ledger_id[]" class="ledger_id" value="0">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="narration[]" class="form-control narration" placeholder="Narration" value="">'+
'</div>'+
'<div class="col-sm-6">'+
'<div class="row">'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<select required="required" class="form-control debit_credit" name="debit_credit[]">'+
'<option value="1">Debit</option>'+
'<option value="-1">Credit</option>'+
'</select>'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="qty[]" class="form-control qty qtyfield" placeholder="'+qtyLabel+'" value="">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="unit_price[]" class="form-control unit_price pricefield" placeholder="'+UnitPriceLabel+'" value="">'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="debit[]" class="form-control debit pricefield" placeholder="Amount" value="0">'+
'</div>'+
'<div class="col-sm-2" align="left">'+
'<input type="text" name="credit[]" class="form-control credit pricefield" placeholder="Amount" style="display:none;" value="0">'+
'</div>'+
'</div>'+
'</div>'+
'</li>';
													}
													formhtml += '</ul>'+
													'<div class="addnewplusbotrig" style="top:50px;">'+
'<a href="javascript:void(0);" title="Add More Voucher List" onClick="addMoreV2List();">'+
'<img align="absmiddle" alt="Add More Voucher List" title="Add More Voucher List" src="/assets/admin/images/Accounts/plus20x25.png">'+
'</a>'+
'</div>';

if(data.voucherLists.length==0 || lastCreRows.length>0){
	var voucher_list_id = 0;
	var ledger_id = 0;
	var ledgerName = '';
	var narration = '';
	var qty = 0;
	var unit_price = 0;
	var credit = 0;
	if(lastCreRows.length>0){
		var voucher_list_id = lastCreRows[0];
		var ledger_id = lastCreRows[1];
		var ledgerName = lastCreRows[2];
		var narration = lastCreRows[3];
		var qty = lastCreRows[5];
		var unit_price = lastCreRows[6];
		var credit = lastCreRows[7];
	}
	
formhtml += '<div class="col-sm-12">'+
'<div class="col-sm-3 paddingleft0 paddingright0">'+
'<input type="text" name="ledgerName[]" class="form-control ledgerName" placeholder="Ledger Name" value="'+ledgerName+'">'+
'<input type="hidden" name="voucher_list_id[]" class="voucher_list_id" value="'+voucher_list_id+'">'+														
'<input type="hidden" name="ledger_id[]" class="ledger_id" value="'+ledger_id+'">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="narration[]" class="form-control narration" placeholder="Narration" value="'+narration+'">'+
'</div>'+
'<div class="col-sm-6">'+
'<div class="row">'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<select required="required" class="form-control debit_credit" name="debit_credit[]">'+
'<option value="1">Debit</option>'+
'<option value="-1" selected>Credit</option>'+
'</select>'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="qty[]" class="form-control qty qtyfield" placeholder="'+qtyLabel+'" value="'+qty+'">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="unit_price[]" class="form-control unit_price pricefield" placeholder="'+UnitPriceLabel+'" value="'+unit_price+'">'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="debit[]" class="form-control debit pricefield" placeholder="Amount" style="display:none;" value="0">'+
'</div>'+
'<div class="col-sm-2" align="left">'+
'<input type="text" name="credit[]" class="form-control credit pricefield" placeholder="Amount" value="'+credit+'">'+
'</div>'+
'</div>'+
'</div>'+
'</div>';
}
formhtml += '<div class="width100per mtop10 ptop10 lightpinkrow">'+
'<input type="hidden" required="required" value="Credit" id="adjustwith">'+
'<div id="error_voucherList" class="col-sm-6 errormsg"></div>'+
'<div class="col-sm-2" align="right"><h3>Total:</h3></div>'+
'<div class="col-sm-2" align="right"><h3 id="totDebit">0.00</h3></div>'+
'<div class="col-sm-2" align="right"><h3 id="totCredit">0.00</h3></div>'+
'</div>'+
'</li>'+
'</ul>'+
'</div>'+
'</div>'+
'</form>';
			
			form1200dialog(data.voucherType+' Voucher Information', formhtml, AJsaveVoucher2);
			
			setTimeout(function() {
				j('#voucher_date, #pi_invoice_date, #lc_date').datepicker({
					dateFormat: "yy-mm-dd",
					onRender: function(date) {
						//return date.valueOf() < now.valueOf() ? 'disabled' : '';
					},
					autoclose: true,
					onSelect: function (date) {
						if(j(this).prop('id')=='voucher_date'){
							var voucher_type = document.frmVoucher.voucher_type.value;
							getVoucherNo(date, voucher_type, voucher_id);
						}
					}
				});
				j("#voucher_no").focus();
				j(".debit_credit").change(function() {
					checkTransactionType(j(this));
				});
				AJautoComplete_Ledger();
				
				setValidPrice();
				j(".qty, .unit_price, .debit, .credit").keyup(function (e) {calDebCre();});
				calDebCre();
				j(".narration").keyup(function() {
					checkNarration(j(this));
				});
				
			}, 500);
			
		}
	})
	.fail(function() {		
		connection_dialog(AJgetVoucher2Popup, voucher_id, voucher_type);
	});
	
	return true;
}

function AJsaveVoucher2(){
	var voucher_id = document.frmVoucher.voucher_id.value;
	var voucher_type = document.frmVoucher.voucher_type.value;
	var errorObj = document.getElementById('error_Voucher2');
	errorObj.innerHTML = '';
	if(j("#voucher_date").val()==''){
		errorObj.innerHTML = '<p>Missing Voucher Date</p>';
		j("#voucher_date").focus();
		return false;
	}
	if(checkVList()==false){
		return false;
	}
	
	var totDebit = parseFloat(j('#totDebit').html());
	if(isNaN(totDebit) || totDebit==''){totDebit = 0;}
	var totCredit = parseFloat(j('#totCredit').html());
	if(isNaN(totCredit) || totCredit==''){totCredit = 0;}
	
	var errorObj = document.getElementById('error_voucherList');
	errorObj.innerHTML = '';
	if(totDebit==0){
		errorObj.innerHTML = 'Total debit should be > 0';
		return false;
	}
	errorObj.innerHTML = '';
	if(totCredit==0){
		errorObj.innerHTML = 'Total credit should be > 0';
		return false;
	}
	errorObj.innerHTML = '';
	if(totDebit != totCredit){
		errorObj.innerHTML = 'Total debit is not equal to total credit';
		return false;
	}	
	
	errorObj.innerHTML = '';
	j(".btnmodel").html('Saving...').prop('disabled', true);
			
	j.ajax({method: "POST",dataType: "json",
		url: "/Accounts/AJsaveVoucher2",
		data:j("#frmVoucher").serialize(),
	})
	.done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else if(data.savemsg !='error'){
			filter_Voucher2();
			if(voucher_id==0){
				AJgetVoucher2Popup(0, voucher_type);
			}
			j( "#form-dialog" ).html('').dialog( "close" );			
		}
		else{						
			errorObj.innerHTML = data.message;
		}		
		j(".btnmodel").html('Save').prop('disabled', false);
	})
	.fail(function() {		
		connection_dialog(AJsaveVoucher2);
	});	
	return false;
}

function addMoreV2List(){
	var totVlists = j("ul#vList li").length;
	var voucher_type = document.frmVoucher.voucher_type.value;
	if(voucher_type==6){
		var qtyLabel = 'Dollar';
		var UnitPriceLabel = 'Currency Rate';
	}
	else{
		var qtyLabel = 'Qty';
		var UnitPriceLabel = 'Unit Price';
	}
	
	var rowClass = ' lightgreenrow';
	if(totVlists%2 !=0){rowClass = '';}
	//alert(rowClass);
	newrow = '<div class="col-sm-3 paddingleft0 paddingright0">'+
'<input type="text" name="ledgerName[]" class="form-control ledgerName" placeholder="Ledger Name">'+
'<input type="hidden" name="voucher_list_id[]" class="voucher_list_id" value="0">'+														
'<input type="hidden" name="ledger_id[]" class="ledger_id" value="0">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="narration[]" class="form-control narration" placeholder="Narration" value="">'+
'</div>'+
'<div class="col-sm-6">'+
'<div class="row">'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<select required="required" class="form-control debit_credit" name="debit_credit[]">'+
'<option value="1">Debit</option>'+
'<option value="-1">Credit</option>'+
'</select>'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="qty[]" class="form-control qty qtyfield" placeholder="'+qtyLabel+'" value="">'+
'</div>'+
'<div class="col-sm-3 paddingright0" align="left">'+
'<input type="text" name="unit_price[]" class="form-control unit_price pricefield" placeholder="'+UnitPriceLabel+'" value="">'+
'</div>'+
'<div class="col-sm-2 paddingright0" align="left">'+
'<input type="text" name="debit[]" class="form-control debit pricefield" placeholder="Amount" value="0">'+
'</div>'+
'<div class="col-sm-2" align="left">'+
'<input type="text" name="credit[]" class="form-control credit pricefield" placeholder="Amount" style="display:none;" value="0">'+
'</div>'+
'</div>'+
'</div>';
	var newmore_list = j('<li class="width100per'+rowClass+'"></li>').html(newrow);										
	j("ul#vList").append(newmore_list.hide());
	newmore_list.slideDown('fast');
	j(".debit_credit").change(function() {
		checkTransactionType(j(this));
	});
	AJautoComplete_Ledger();
	setValidPrice();
	calDebCre();
	j(".debit, .credit").keyup(function (e) {calDebCre();});
}

function checkVList(){
	var errorObj = document.getElementById('error_voucherList');
	var ledgerNames = document.getElementsByName('ledgerName[]');
	var ledger_ids = document.getElementsByName('ledger_id[]');
	var debit_credits = document.getElementsByName('debit_credit[]');
	var debits = document.getElementsByName('debit[]');
	var credits = document.getElementsByName('credit[]');
	if(ledgerNames.length>0){
		for(var l=0; l<ledgerNames.length; l++){
			var ledgerName = ledgerNames[l].value;
			var ledger_id = ledger_ids[l].value;
			var debit_credit = debit_credits[l].value;
			var debit = parseFloat(debits[l].value);
			if(isNaN(debit) || debit==''){debit = 0;}
			var credit = parseFloat(credits[l].value);
			if(isNaN(credit) || credit==''){credit = 0;}
			
			errorObj.innerHTML = '';
			if(ledger_id==0 || ledgerName==''){
				errorObj.innerHTML = 'Missing Ledger Name.';
				ledgerNames[l].focus();
				return false;
			}
			errorObj.innerHTML = '';
			if(debit_credit==0){
				errorObj.innerHTML = 'Missing Transaction.';
				debit_credits[l].focus();
				return false;
			}
			errorObj.innerHTML = '';
			if(debit_credit==1 && debit==0){
				errorObj.innerHTML = 'Missing debit amount';
				debits[l].focus();
				return false;
			}
			errorObj.innerHTML = '';
			if(debit_credit==-1 && credit==0){
				errorObj.innerHTML = 'Missing credit amount';
				credits[l].focus();
				return false;
			}
		}
	}
	
	return true;
}

function calDebCre(){
	
	var countList = j("ul#vList li").length;
	if(countList>1){
		var voucher_id = document.frmVoucher.voucher_id.value;
		var startVal = 1;
		
		if(voucher_id>0){startVal = 1;}
		for(var l = startVal; l<countList; l++){
			if(j("ul#vList li:nth-child("+(l+1)+")").find("a.removeicon").length){}
			else{
				j( "<a class=\"removeicon\" style=\"right:-2px;\" href=\"javascript:void(0);\" title=\"Remove this row\">"+
"<img align=\"absmiddle\" alt=\"Remove this row\" title=\"Remove this row\" src=\"/assets/admin/images/Accounts/minus.gif\">"+
"</a>").appendTo("ul#vList li:nth-child("+(l+1)+")");
			}
		}
		j('.removeicon').click(function(){
			j(this).parent('li').slideUp("slow");
			j(this).parent('li').remove();
			calDebCre();
			return false;
		});
	}

	var voucher_type = document.frmVoucher.voucher_type.value;
	var debit_credits = document.getElementsByName('debit_credit[]');
	if(voucher_type==5 || voucher_type==6){
		var qtys = document.getElementsByName('qty[]');
		var unit_prices = document.getElementsByName('unit_price[]');
	}
	var debits = document.getElementsByName('debit[]');
	var credits = document.getElementsByName('credit[]');
	var totDeb = 0;
	var totCre = 0;
	if(debits.length>0){
		var lastDebitIndex = -1;
		var lastCreditIndex = -1;
		for(var l=0; l<debits.length; l++){
			var debit_credit = debit_credits[l].value;
			if(debit_credit=='1'){lastDebitIndex = l;}
			else{lastCreditIndex = l;}
			//alert('debit_credit:'+debit_credit+', lastDebitIndex:'+lastDebitIndex);
			if((voucher_type==5 || voucher_type==6) && debit_credit=='1'){
				var qty = parseFloat(qtys[l].value);
				if(isNaN(qty) || qty==''){qty = 0;}
				var unit_price = parseFloat(unit_prices[l].value);
				if(isNaN(unit_price) || unit_price==''){unit_price = 0;}
				debits[l].value = qty*unit_price;
			}
			
			var debit = parseFloat(debits[l].value);
			if(isNaN(debit) || debit==''){debit = 0;}
			
			var credit = parseFloat(credits[l].value);
			if(isNaN(credit) || credit==''){credit = 0;}
			
			totDeb += debit;
			totCre += credit;			
		}
		
		var adjustwith = j("#adjustwith").val();
		if(totDeb != totCre){
			var diffVal = parseFloat(totDeb-totCre);
			if(diffVal !=0){
				if(adjustwith=='Debit'){
					if(lastDebitIndex>=0){
						var lastDebit = parseFloat(debits[lastDebitIndex].value);
						var adjustAmount = totDeb-lastDebit;
						//alert(adjustAmount);
						var newLastDebit = parseFloat(totCre-adjustAmount);
						debits[lastDebitIndex].value = newLastDebit;
						totDeb = totCre;
					}
				}
				else if(adjustwith=='Credit'){
					if(lastCreditIndex>=0){
						var lastCredit = parseFloat(credits[lastCreditIndex].value);
						var adjustAmount = totCre-lastCredit;
						var newLastCredit = parseFloat(totDeb-adjustAmount);
						credits[lastCreditIndex].value = newLastCredit;
						totCre = totDeb;
					}
				}
			}
		}
	}
	j('#totDebit').html(totDeb.toFixed(2));
	j('#totCredit').html(totCre.toFixed(2));
}

function  checkTransactionType(debCreObj){
	var devCreVal = debCreObj.val();
	var parIdObj = debCreObj.parent().parent();
	var hideClass = '.credit';
	var showClass = '.debit';
	if(devCreVal ==-1){
		var hideClass = '.debit';
		var showClass = '.credit';
	}
	parIdObj.find(showClass).show();
	parIdObj.find(hideClass).val(0).hide();
}

function checkPaymentType(thisObj){
	var fieldVal = thisObj.val();
	if(fieldName=='credit'){
		
	}
	else{
		
	}
}

function checkNarration(thisObj){
	var narrationVal = thisObj.val();
	var narrObj = document.getElementsByClassName("narration");
	narrObj[parseInt(narrObj.length)-1].value = narrationVal;
}

function getVoucherNo(voucher_date, voucher_type, voucher_id){
	if(voucher_date !=''){
		j.ajax({method: "POST", dataType: "json",
			url: "/Accounts/getVoucherNo/"+voucher_date+'/'+voucher_type+'/'+voucher_id,
			data:{ajaxCall:1},
		})
		.done(function( data ) {
			j("#voucher_no").val(data);
		})
		.fail(function() {
			connection_dialog(getVoucherNo, voucher_date, voucher_type, voucher_id);
		});
	}
}

//============Reports:: dayBook=========//
function filter_Accounts_dayBook(){
	var limit = j('#limit').val();
	var page = 1;
	j("#page").val(page);
	
	var jsonData = {};
	var fvoucher_type = j('#fvoucher_type').val();
	var fsub_group_id = j('#fsub_group_id').val();
	var fledger_id = j('#fledger_id').val();
	var jsonData = {};
	jsonData['date_range'] = j('#date_range').val();			
	jsonData['fpublish'] = j('#fpublish').val();			
	jsonData['fvoucher_type'] = fvoucher_type;			
	jsonData['faccount_type'] = j('#faccount_type').val();			
	jsonData['fsub_group_id'] = fsub_group_id;
	jsonData['fledger_id'] = fledger_id;
	jsonData['keyword_search'] = j('#keyword_search').val();			
	jsonData['totalRows'] = j('#totalTableRows').val();
	jsonData['rowHeight'] = j('#rowHeight').val();
	jsonData['limit'] = limit;
	jsonData['page'] = page;
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_dayBook/filter",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#fvoucher_type").html(data.vouTypOpt);
			j("#fsub_group_id").html(data.subGroOpt);
			j("#fledger_id").html(data.parLedOpt);
			j("#totalTableRows").val(data.totalRows);
			j("#tableRows").html(data.tableRows);
			
			j('#fvoucher_type').val(fvoucher_type);
			j('#fsub_group_id').val(fsub_group_id);
			j('#fledger_id').val(fledger_id);
			
			onClickPagination();
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function(){
		connection_dialog(filter_Accounts_dayBook);
	});
}

function loadTableRows_Accounts_dayBook(){
	var jsonData = {};
	jsonData['date_range'] = j('#date_range').val();			
	jsonData['fpublish'] = j('#fpublish').val();			
	jsonData['fvoucher_type'] = j('#fvoucher_type').val();
	jsonData['faccount_type'] = j('#faccount_type').val();			
	jsonData['fsub_group_id'] = j('#fsub_group_id').val();
	jsonData['fledger_id'] = j('#fledger_id').val();
	jsonData['keyword_search'] = j('#keyword_search').val();
	jsonData['totalRows'] = j('#totalTableRows').val();
	jsonData['rowHeight'] = j('#rowHeight').val();	
	jsonData['limit'] = j('#limit').val();
	jsonData['page'] = j('#page').val();
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_dayBook",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#tableRows").html(data.tableRows);
			onClickPagination();
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function() {			
		connection_dialog(loadTableRows_Accounts_dayBook);
	});
}

//============Reports:: ledgerReport============//
function loadData_Accounts_ledgerReport(){
	var jsonData = {};
	var fvoucher_type = j('#fvoucher_type').val();
	var fsub_group_id = j('#fsub_group_id').val();
	var fledger_id = j('#fledger_id').val();
	var jsonData = {};
	jsonData['date_range'] = j('#date_range').val();			
	jsonData['fvoucher_type'] = fvoucher_type;			
	jsonData['faccount_type'] = j('#faccount_type').val();			
	jsonData['fsub_group_id'] = fsub_group_id;
	jsonData['fledger_id'] = fledger_id;
	jsonData['keyword_search'] = j('#keyword_search').val();			
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_ledgerReport",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#fvoucher_type").html(data.vouTypOpt);
			j("#fsub_group_id").html(data.subGroOpt);
			j("#fledger_id").html(data.parLedOpt);
			j("#tableRows").html(data.tableRows);
			
			j('#fvoucher_type').val(fvoucher_type);
			j('#fsub_group_id').val(fsub_group_id);
			j('#fledger_id').val(fledger_id);
			
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function(){
		connection_dialog(loadData_Accounts_ledgerReport);
	});	
}

//============Reports:: trialBalance============//
function loadData_Accounts_trialBalance(){
	var jsonData = {};
	jsonData['fdate'] = j('#fdate').val();			
	jsonData['fviews_type'] = j('#fviews_type').val();
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_trialBalance",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#tableRows").html(data.tableRows);			
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function(){
		connection_dialog(loadData_Accounts_trialBalance);
	});	
}

function printTrialBalance(){
	var divContents = j("#no-more-tables").html();
	var title = 'Trial Balance for '+j("#fdate").val();
	var filterby = '';
	if(j("#fdate").val() !=''){
		filterby += 'Trial Balance Date : '+j("#fdate").val();
	}
	var now = new Date();
	var todayDate = now.getDate()+'-'+(now.getMonth() + 1)+'-'+now.getFullYear();
	
	var additionaltoprows = '<div class="width100">'+
								'<div class="txtcenter txt20bold">'+stripslashes(COMPANYNAME)+'</div>'+
							'</div>'+
							'<div class="width100 mtop10">'+
								'<div class="floatleft txtleft txt18bold">'+stripslashes(title)+'</div>'+
								'<div class="floatright txtright txt16normal">'+todayDate+'</div>'+
							'</div>'+
							'<div class="width100">'+
								'<hr class="mtop10 mbottom0">'+
							'</div>'+
							'<div class="width100 mtop10" id="filterby">'+filterby+'</div>';
	divContents = divContents.replace('<thead class="cf">', '<thead class="cf"><tr><td class="bgnone" colspan="3">'+additionaltoprows+'</td></tr>');
	
	var day = new Date();
	var id = day.getTime();
	var w = 900;
	var h = 600;
	var scrl = 1;
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
	
	var printWindow = window.open('', '" + id + "', winprops);
	printWindow.document.write('<!DOCTYPE html><html><head><title>'+title+'</title><meta charset="utf-8">');

	printWindow.document.write('<link rel="stylesheet" href="/assets/admin/images/Accounts/print.css">');
	
	printWindow.document.write('</head><body>');
	
	printWindow.document.write(divContents);
	printWindow.document.write('</body></html>');
	printWindow.document.close();
	var is_chrome = Boolean(window.chrome);
	if (is_chrome) {
		printWindow.onload = function () {
			printWindow.window.print();
			document_focus = true;
		};
	}
	else {
		
		var document_focus = false;
		printWindow.document.onreadystatechange = function () {
			var state = document.readyState
			if (state == 'interactive') {}
			else if (state == 'complete') {
				setTimeout(function(){
					document.getElementById('interactive');
					printWindow.print();
					document_focus = true;
				},1000);
			}
		}
	}
	printWindow.setInterval(function() {
		var deviceOpSy = getMobileOperatingSystem();
		if (document_focus === true && deviceOpSy=='unknown') { printWindow.window.close(); }
	}, 500);
}

//============Reports:: receiptPayment============//
function loadRecPayData(){
	var jsonData = {};
	jsonData['date_range'] = j('#date_range').val();			
	jsonData['fviews_type'] = j('#fviews_type').val();
	
	j("body").append('<div class="disScreen"><img src="/assets/admin/images/ajax-loader.gif"></div>');
	j.ajax({method: "POST", dataType: "json",
		url: "/Accounts/AJgetPage_receiptPayment",
		data: jsonData,
	}).done(function( data ) {
		if(data.login != ''){window.location = '/'+data.login;}
		else{
			j("#receiptsRows").html(data.receiptsRows);			
			j("#paymentsRows").html(data.paymentsRows);
		}
		if(j(".disScreen").length){j(".disScreen").remove();}
	})
	.fail(function(){
		connection_dialog(loadData_Accounts_receiptPayment);
	});	
}

function printreceiptPayment(){
	var divContents = j("#no-more-tables").html();
	var title = 'Trial Balance for '+j("#fdate").val();
	var filterby = '';
	if(j("#fdate").val() !=''){
		filterby += 'Trial Balance Date : '+j("#fdate").val();
	}
	var now = new Date();
	var todayDate = now.getDate()+'-'+(now.getMonth() + 1)+'-'+now.getFullYear();
	
	var additionaltoprows = '<div class="width100">'+
								'<div class="txtcenter txt20bold">'+stripslashes(COMPANYNAME)+'</div>'+
							'</div>'+
							'<div class="width100 mtop10">'+
								'<div class="floatleft txtleft txt18bold">'+stripslashes(title)+'</div>'+
								'<div class="floatright txtright txt16normal">'+todayDate+'</div>'+
							'</div>'+
							'<div class="width100">'+
								'<hr class="mtop10 mbottom0">'+
							'</div>'+
							'<div class="width100 mtop10" id="filterby">'+filterby+'</div>';
	divContents = divContents.replace('<thead class="cf">', '<thead class="cf"><tr><td class="bgnone" colspan="3">'+additionaltoprows+'</td></tr>');
	
	var day = new Date();
	var id = day.getTime();
	var w = 900;
	var h = 600;
	var scrl = 1;
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
	
	var printWindow = window.open('', '" + id + "', winprops);
	printWindow.document.write('<!DOCTYPE html><html><head><title>'+title+'</title><meta charset="utf-8">');

	printWindow.document.write('<link rel="stylesheet" href="/assets/admin/images/Accounts/print.css">');
	
	printWindow.document.write('</head><body>');
	
	printWindow.document.write(divContents);
	printWindow.document.write('</body></html>');
	printWindow.document.close();
	var is_chrome = Boolean(window.chrome);
	if (is_chrome) {
		printWindow.onload = function () {
			printWindow.window.print();
			document_focus = true;
		};
	}
	else {
		
		var document_focus = false;
		printWindow.document.onreadystatechange = function () {
			var state = document.readyState
			if (state == 'interactive') {}
			else if (state == 'complete') {
				setTimeout(function(){
					document.getElementById('interactive');
					printWindow.print();
					document_focus = true;
				},1000);
			}
		}
	}
	printWindow.setInterval(function() {
		var deviceOpSy = getMobileOperatingSystem();
		if (document_focus === true && deviceOpSy=='unknown') { printWindow.window.close(); }
	}, 500);
}

//================Commonly Used==============//
function AJautoComplete_Ledger(){
	var voucher_type = document.frmVoucher.voucher_type.value;
	j(".ledgerName").autocomplete({
		minLength:2,
		source: ledAutComRes,
		focus: function( event, ui ) {
			return false;
		},
		select: function( event, ui ) {
			j(this).val( ui.item.label );
			j(this).parent().find('.ledger_id').val(ui.item.lId);
			AJgetLedgerBalance(ui.item.lId);
			j(".btnmodel").prop('disabled', false);
			return false;
		}
	}).keyup(function (e) {
		if(e.which === 13) {
			j(".ui-autocomplete").hide();
		}
	});
}

function AJgetLedgerBalance(ledger_id){
	if(j("#error_voucherList").length){
		j.ajax({method: "POST", dataType: "json",
			url: "/Accounts/AJgetLedgerBalance",
			data:{ledger_id:ledger_id},
		})
		.done(function( data ) {
			if(data.login=='')
				j("#error_voucherList").html('Balance: '+data.balance);
		})
		.fail(function() {
			connection_dialog(AJgetLedgerBalance, ledger_id);
		});
	}
}

function AJupdate_Data(tableName, idValue, description, fieldName, updateValue){
    var message = "Are you sure want to <b>'"+description+"'</b>?</center>";	
	message += "<input type=\"hidden\" id=\"tableName\" value=\""+tableName+"\">"+
				"<input type=\"hidden\" id=\"idValue\" value=\""+idValue+"\">"+
				"<input type=\"hidden\" id=\"description\" value=\""+description+"\">"+
				"<input type=\"hidden\" id=\"fieldName\" value=\""+fieldName+"\">"+
				"<input type=\"hidden\" id=\"updateValue\" value=\""+updateValue+"\">";
	
	confirm_dialog(description, message, confirmAJupdate_Data);
}

function confirmAJupdate_Data(){
	
	j(".btnAction").html('Removing...').prop('disabled', true);
	j.ajax({
		method: "POST",
		url: "/Accounts/AJupdate_Data",
		data: {tableName:j("#tableName").val(), idValue:j("#idValue").val(), description:j("#description").val(), fieldName:j("#fieldName").val(), updateValue:j("#updateValue").val()}
	}).done(function( data ) {
		if(data== 'login'){
			window.location = '/login';
		}
		else{
			j("#errorMsg").html('<div class="col-xs-12 col-sm-12 col-md-12"><div class="bs-callout bs-callout-info well success_msg">'+data+'</div></div>').fadeIn(500);
			setTimeout(function() {j("#errorMsg").slideUp(500);}, 5000);			
			checkAndLoadFilterData();
		}
		j( "#dialog-confirm" ).html('').dialog( "close" );
		
	})
	.fail(function() {		
		connection_dialog(confirmArchive);
	});
}

function setValidQty(){		
	j( ".qtyfield" ).focus(function() {
		qtyfield(j(this), 'focus');
	});
	j( ".qtyfield" ).blur(function() {
		qtyfield(j(this), 'blur');
	});
	j( ".qtyfield" ).keyup(function() {
		qtyfield(j(this), 'keyup');
	});
}

function qtyfield(field_id, onkeyname){
	
	if(onkeyname=='focus'){
		var price = field_id.val();
		if(price==0){
			field_id.val('');
		}
	}
	else if(onkeyname=='blur'){
		var price = field_id.val();
		if(price==''){
			field_id.val(0);
		}
	}
	else if(onkeyname=='keyup'){
		var price = field_id.val();
		var ValidChars = "0123456789";
		var IsNumber=true;
		var Char;
		var validint = '';
		for (var i = 0; i < price.length && IsNumber == true; i++){ 
			Char = price.charAt(i);
			if ((i==0 && Char==0) || ValidChars.indexOf(Char) == -1){}
			else{
				validint = validint+Char;
			}
		}
		if(price.length > validint.length){
			field_id.val(validint);
		}
	}
}

function setValidPrice(){	
	j( ".pricefield" ).focus(function() {
		pricefield(j(this), 'focus');
	});
	j( ".pricefield" ).blur(function() {
		pricefield(j(this), 'blur');
	});		
	j( ".pricefield" ).keyup(function() {
		pricefield(j(this), 'keyup');
	});	
}

function pricefield(field_id, onkeyname){
	
	if(onkeyname=='focus'){
		var price = field_id.val();
		if(price==0){
			field_id.val('');
		}
	}
	else if(onkeyname=='blur'){
		var price = field_id.val();
		if(price==''){
			field_id.val(0);
		}
	}
	else if(onkeyname=='keyup'){
		var price = field_id.val();
		var ValidChars = ".0123456789-";
		var IsNumber=true;
		var Char;
		var validint = '';
		for (var i = 0; i < price.length && IsNumber == true; i++){ 
			Char = price.charAt(i);
			if ((i==0 && Char==0) || ValidChars.indexOf(Char) == -1){}
			else{
				validint = validint+Char;
			}
		}
		if(price.length > validint.length){
			field_id.val(validint);
		}
	}
}

function AJarchive_Popup(tableName, idValue, description, activeInActive){
    var title = 'Archive "'+description+'" data';
	var message = "Are you sure want to archive <b>'"+description+"'</b> from all related list data? <br /><br /><center class=\"txtred txt18\">Make sure it will be backed when you will active it again.</center>";
	if(activeInActive==0){
		var title = 'Active "'+description+'" data';
		var message = "Are you sure want to active <b>'"+description+"'</b> for visible all related list data? <br /><br /><center class=\"txtred txt18\">Make sure it will be shown into everywhere(related).</center>";
	}
	
	message += "<input type=\"hidden\" id=\"tableName\" value=\""+tableName+"\">"+
				"<input type=\"hidden\" id=\"idValue\" value=\""+idValue+"\">"+
				"<input type=\"hidden\" id=\"description\" value=\""+description+"\">"+
				"<input type=\"hidden\" id=\"activeInActive\" value=\""+activeInActive+"\">";
	
	confirm_dialog(title, message, confirmArchive);
}

function confirmArchive(){
	
	j(".btnAction").html('Removing...').prop('disabled', true);
	j.ajax({
		method: "POST",
		url: "/Accounts/oneRowArchive/",
		data: {tableName:j("#tableName").val(), idValue:j("#idValue").val(), description:j("#description").val(), activeInActive:j("#activeInActive").val()}
	}).done(function( data ) {
		if(data== 'login'){
			window.location = '/login';
		}
		
		if(data =='Archived successfully.' || data=='Actived successfully.'){
			j("#errorMsg").html('<div class="col-xs-12 col-sm-12 col-md-12"><div class="bs-callout bs-callout-info well success_msg">'+data+'</div></div>').fadeIn(500);
			setTimeout(function() {j("#errorMsg").slideUp(500);}, 5000);
			if(j("#tableName").val()=='ledger'){
				checkAndLoadData();
			}
			else{
				checkAndLoadFilterData();
			}
		}
		else{
			j("#errorMsg").html('<div class="col-xs-12 col-sm-12 col-md-12"><div class="bs-callout bs-callout-info well error_msg">'+data+'</div></div>').fadeIn(500);
			setTimeout(function() {j("#errorMsg").slideUp(500);}, 5000);
		}
		j( "#dialog-confirm" ).html('').dialog( "close" );
		
	})
	.fail(function() {		
		connection_dialog(confirmArchive);
	});
}

//==============For Popup Dialog====================//
function form1200dialog(title, formhtml, callbackfunction){
   	j( "#form-dialog" ).html(formhtml);
	
	j( "#form-dialog" ).dialog({
		title: title,
		resizable: false,
		height: "auto",
		width: j(window).width() > 1200 ? 1200 : j(window).width(),
		modal: true,

		open: function(event, ui) {
        	j(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
    	},
	  	buttons: {
			'Cancel': {
				text: 'Cancel', class: 'btn btn-default', click: function() {
					j( this ).dialog( "close" );
				},
			},
			'Save':{
				text:'Save', class: 'btn btn-primary btnmodel', click: function() {
					callbackfunction();
				},
			}
      	}
    });
}

function leftsideHide(menuID, menuClass){
	if(document.querySelector('#'+menuID)){
		document.querySelector('#'+menuID).addEventListener('click', event=>{
			document.querySelectorAll('.'+menuClass).forEach(oneTag=>{
				oneTag.classList.toggle('settingslefthide');
			});
		});
	}
}

function sanitizer(){
	let input = this.value;
    const ContainsOnEventListener = /<[^>]*on\w+=[^>]*>/gi.test(input)
    const ConstinsScriptTag = /<\/?\s*script/gi.test(input);    
    if(ContainsOnEventListener || ConstinsScriptTag){
        this.value = '';
        showTopMessage('alert_msg', 'Warning: Potential malcious code exists in your input-text'); 
    }
}

function applySanitizer(node){
	node.querySelectorAll('input,textarea').forEach(field=>field.addEventListener('blur',sanitizer));
}

function confirm_dialog(title, message, callbackfunction){
    let p = cTag('p',{"class":"txtleft"});
    p.innerHTML = '';
    if(typeof message =='string'){p.innerHTML = message;}
    else{p.appendChild(message);}
    let dialogConfirm = document.querySelector("#dialog-confirm" );
	dialogConfirm.innerHTML = '';
	dialogConfirm.appendChild(p);
	
	j( "#dialog-confirm" ).dialog({
		title: title,
		resizable: false,
		height: "auto",
		width:400,
		modal: true,
	  	buttons: {
			'Close': {
				text: 'Close', class: 'btn btn-default', click: function() {
					j( this ).dialog( "close" );
				},
			},
			'Confirm':{
				text: 'Confirm', class: 'btn btn-primary archive', click: function() {
					callbackfunction();
				},
			}
      	}
    });
}

function alert_dialog(title, message, btnname){
    
	j( "#dialog-confirm" ).html(message);
	
	j( "#dialog-confirm" ).dialog({
		title: title,
		resizable: false,
		height: "auto",
		width:500,
		modal: true,
	  	buttons: {
			btnname: {
				text: btnname, class: 'btn btn-primary', click: function() {
					j( this ).dialog( "close" );
				},
			}
      	}
    });
}

function form600dialog(title, formhtml, actionbutton, callbackfunction){
   	j( "#form-dialog" ).html(formhtml);
	
	j( "#form-dialog" ).dialog({
		title: title,
		resizable: false,
		height: "auto",
		width: j(window).width() > 600 ? 600 : j(window).width(),
		modal: true,
	  	buttons: {
			'Cancel': {
				text: 'Cancel', class: 'btn btn-default', click: function() {
					j( this ).dialog( "close" );
				},
			},
			actionbutton:{
				text: actionbutton, class: 'btn btn-primary btnmodel', click: function() {
					callbackfunction();
				},
			}
      	}
    });
}

function form1000dialog(title, formhtml, callbackfunction){
   	j( "#form-dialog" ).html(formhtml);
	
	j( "#form-dialog" ).dialog({
		title: title,
		resizable: false,
		height: "auto",
		width: j(window).width() > 1000 ? 1000 : j(window).width(),
		modal: true,
	  	buttons: {
			'Cancel': {
				text: 'Cancel', class: 'btn btn-default', click: function() {
					j( this ).dialog( "close" );
				},
			},
			'Save':{
				text:'Save', class: 'btn btn-primary btnmodel', click: function() {
					callbackfunction();
				},
			}
      	}
    });
}

j(document).ready(function(){
	if(j("#pageURI").length && ['Accounts/ledger'].includes(j("#pageURI").val())){
		document.querySelector("#keyword_search").addEventListener('keyup', e=>{
			if(e.which === 13) {ledgerData();}
		});
	}

	if(j("#pageURI").length && (j.inArray(j("#pageURI").val(), ['Accounts/receiptVoucher']) !=-1)){
		
	}
	
    leftsideHide("secondarySideMenu",'secondaryNavMenu');
    
    applySanitizer(document);
});