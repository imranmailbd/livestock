import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, checkAndSetSessionData, daterange_picker_dialog, setSelectOpt, 
    setTableHRows, addPaginationRowFlex, fetchData, onClickPagination, addCustomeEventListener
} from './common.js';

if(segment2==='') segment2 = 'lists';
let width80 = '';
if(OS =='unknown'){width80 = "width: 80px;";}

const listsFieldAttributes = [{'valign':'top', 'datatitle':Translate('Date'), 'align':'left', 'style':width80},
                    {'valign':'top', 'datatitle':Translate('Time'), 'align':'right', 'style':width80},
                    {'valign':'top', 'datatitle':Translate('User'), 'align':'left'},
                    {'valign':'top', 'datatitle':Translate('Activity'), 'align':'left'},
                    {'valign':'top', 'datatitle':Translate('Details'), 'align':'left'}];
const uriStr = segment1+'/lists/';

async function filter_Activity_Feed_lists(){
    let page = 1;
    document.getElementById("page").value = page;

	const jsonData = {};
	let sactivity_feed = document.getElementById("sactivity_feed").value;
	jsonData['sactivity_feed'] = sactivity_feed;
	let puser_id = document.getElementById("puser_id").value;

	jsonData['puser_id'] = puser_id;
	jsonData['date_range'] = document.getElementById("date_range").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;   

    const url = '/'+segment1+'/AJgetPage/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);

        setSelectOpt('sactivity_feed', 'All', Translate('All Activities'), data.actFeeTitOpt, 0, data.actFeeTitOpt.length);
        setSelectOpt('puser_id', 0, Translate('All Users'), data.pUserOpt, 1, Object.keys(data.pUserOpt).length);	

        setTableHRows(data.tableRows, listsFieldAttributes);
        document.getElementById("totalTableRows").value = data.totalRows;
        document.getElementById("sactivity_feed").value = sactivity_feed;
        document.getElementById("puser_id").value = puser_id;

        onClickPagination();
    }
}

async function loadTableRows_Activity_Feed_lists(){
	const jsonData = {};
	jsonData['sactivity_feed'] = document.getElementById("sactivity_feed").value;
	jsonData['puser_id'] = document.getElementById("puser_id").value;
	jsonData['date_range'] = document.getElementById("date_range").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;    
	
    const url = '/'+segment1+'/AJgetPage';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        setTableHRows(data.tableRows, listsFieldAttributes);
        onClickPagination();
    }
}

function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        let list_filters, todayDate, sortDropDown;
        //=====Hidden Fields for Pagination======//
        [
            { name: 'pageURI', value: segment1+'/'+segment2},
            { name: 'page', value: page },
            { name: 'rowHeight', value: 30 },
            { name: 'totalTableRows', value: 0 },
        ].forEach(field=>{
            let input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
            showTableData.appendChild(input);
        });

        const titleRow = cTag('div',{class:'outerListsTable'});
            const headerTitle = cTag('h2', {'style': "padding: 5px; text-align: start;"});
            headerTitle.innerHTML = Translate('Activity Report')+' ';
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px ;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Activity Report')});
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);
    showTableData.appendChild(titleRow);

        const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
            sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3 columnLG3"});
                let selectActivity = cTag('select', {class: "form-control", name: "sactivity_feed", id: "sactivity_feed"});
                selectActivity.addEventListener('change', filter_Activity_Feed_lists);
                    let allOption = cTag('option', {'value': "All"});
                    allOption.innerHTML = Translate('All Activities');
                selectActivity.appendChild(allOption);
            sortDropDown.appendChild(selectActivity);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3 columnLG3"});
                let selectUser = cTag('select', {class: "form-control", name: "puser_id", id: "puser_id"});
                selectUser.addEventListener('change', filter_Activity_Feed_lists);
                    let userOption = cTag('option', {'value': 0});
                    userOption.innerHTML = Translate('All Users');
                selectUser.appendChild(userOption);
            sortDropDown.appendChild(selectUser);
        filterRow.appendChild(sortDropDown);

            const searchDiv = cTag('div', {class: "columnXS12 columnSM4 columnMD3 columnLG3"});
                const SearchInGroup = cTag('div', {class: "input-group daterangeContainer"});
                    const inputField = cTag('input', {'type': "text", 'placeholder': Translate('Activity Feed Search'), 'value': "", id: "date_range", name: "date_range", class: "form-control", 'style': "padding-left: 35px;", 'maxlength': 23});
                    daterange_picker_dialog(inputField);
                SearchInGroup.appendChild(inputField);
                    let searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Activity Feed Search')});
                    searchSpan.addEventListener('click', filter_Activity_Feed_lists);
                        const searchIcon = cTag('i', {class: "fa fa-search"});
                    searchSpan.appendChild(searchIcon);
                SearchInGroup.appendChild(searchSpan);
            searchDiv.appendChild(SearchInGroup);
        filterRow.appendChild(searchDiv);
    showTableData.appendChild(filterRow);

        const divTable = cTag('div', {class: "flexSpaBetRow"});
            let tableColumn = cTag('div', {class: "columnXS12"});
                const divNoMore = cTag('div', {id: "no-more-tables"});
                    const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                        const listHead = cTag('thead', {class: "cf"});
                            const columnNames = listsFieldAttributes.map(colObj=>(colObj.datatitle));
                            const listHeadRow = cTag('tr',{class:'outerListsTable'});
                                const thCol0 = cTag('th', {'style': width80});
                                thCol0.innerHTML = columnNames[0];

                                const thCol1 = cTag('th', {'style': width80});
                                thCol1.innerHTML = columnNames[1];

                                const thCol2 = cTag('th', {'width': "20%"});
                                thCol2.innerHTML = columnNames[2];

                                const thCol3 = cTag('th', {'width': "20%"});
                                thCol3.innerHTML = columnNames[3];

                                const thCol4 = cTag('th');
                                thCol4.innerHTML = columnNames[4];
                            listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4);
                        listHead.appendChild(listHeadRow);
                    listTable.appendChild(listHead);
                        const listBody = cTag('tbody', {id: "tableRows"});
                    listTable.appendChild(listBody);
                divNoMore.appendChild(listTable);
            tableColumn.appendChild(divNoMore);
        divTable.appendChild(tableColumn);
    showTableData.appendChild(divTable);
    addPaginationRowFlex(showTableData)

    //======sessionStorage =======//
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{list_filters = {};}
    
    let now = new Date();
	if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
        let dd = now.getDate();
        if(dd<10) dd='0'+dd;
        let mm = now.getMonth()+1;
        if(mm<10) mm='0'+mm;
        todayDate = dd+'-'+mm+'-'+now.getFullYear();
    }
	else{
        let dd = now.getDate();
        if(dd<10) dd='0'+dd;
        let mm = now.getMonth()+1;
        if(mm<10) mm='0'+mm;
        todayDate = mm+'/'+dd+'/'+now.getFullYear();
    }
    let date_range = todayDate+' - '+todayDate;

    checkAndSetSessionData('sactivity_feed', 'All', list_filters);
    checkAndSetSessionData('puser_id', 0, list_filters);

    if(list_filters.hasOwnProperty("date_range"))
        date_range = list_filters.date_range;
    document.getElementById("date_range").value = date_range;

    addCustomeEventListener('filter',filter_Activity_Feed_lists);
    addCustomeEventListener('loadTable',loadTableRows_Activity_Feed_lists);
    filter_Activity_Feed_lists(true);
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists};    
    layoutFunctions[segment2]();
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});