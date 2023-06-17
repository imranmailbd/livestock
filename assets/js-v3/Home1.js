import {
    cTag, Translate, tooltip, alert_dialog, fetchData, showHelpPopup
} from './common.js';

if(segment2==='') segment2 = 'index';

function help(){
    let prevuri = 'Home'
    const showTableData = document.getElementById('viewPageInfo');

    const FAQs = [
                        {
                            title: 'Are there any usage limits?',
                            sub_text: 'No. With your SK POS ERP account you get unlimited users, invoices, cash registers, repair tickets for each location.'
                        },
                        {
                            title: 'Barcode Scanner is not working with the software',
                            sub_text: 'Try this to see if the barcode scanner can input data into the browser (outside of the software).<br>Go to www.google.com,  put the cursor in the search box and scan any valid barcode.  If the number does NOT show up inside the Google search box the issue is due to your barcode scanner not being setup correctly.'
                        },
                        {
                            title: 'Can I Cancel Anytime?',
                            sub_text: 'Yes. There is no contracts and no commitments. You can cancel anytime without penalty. Just send us a help request.'
                        },
                        {
                            title: 'Can I Enter Customer Notes?',
                            sub_text: 'Yes. We do have a NOTES feature that allows you to add a internal (private) or public note in.'
                        },
                        {
                            title: 'Can I Have Different Taxes Rates?',
                            sub_text: 'Yes. There are virtually no limit to the number of tax rates that you can setup within SK POS ERP Software. This allows you to define discounted tax rates as well as 0% tax rates.<br>Within the "Getting Started" module (icon), you\'ll find the "Manage Taxes" option. This is where you can create and manage the different taxes you define.'
                        },
                        {
                            title: 'Can I Import My Customer Base And History?',
                            sub_text: 'Yes. We can import your customers contact details such as name, address, phone number and other information.<br><br>No, we cannot import your customer purchase history.'
                        },
                        {
                            title: 'Can I Setup Different Tax Rates?',
                            sub_text: 'Yes. Setup as many different tax rates as you need including retail rates, wholesale rates, VAT rates, 0% rates and more. You can also create products that are tax inclusive.'
                        },
                        {
                            title: 'Can I Setup My Local Currency And Language?',
                            sub_text: 'Yes. In the “Getting Started” module you can select your local currency and language. Currently we use Google Translate for language translation.'
                        },
                        {
                            title: 'Can I Take Deposits?',
                            sub_text: 'Yes. Deposits and partial payments can be managed through the Orders module.'
                        },
                        {
                            title: 'Can I Use SK POS ERP Software Offline?',
                            sub_text: 'No. The software is online only.'
                        },
                        {
                            title: 'Can I Use The Software With An IPad?',
                            sub_text: 'Yes. Our software will work on any device that has a browser so your iPad works just fine. Our software uses whatever printers you have installed that your browser can see. There are several ways people do this but the most common is google cloud print.'
                        },
                        {
                            title: 'Can I Use The Software With Different Hardware?',
                            sub_text: 'Yes. Our software will supports popular receipt printers, cash drawers, barcode scanners and other hardware. The cash drawer will connect to your receipt printer and will open when the receipt printer sends the command to open the drawer.'
                        
                        },
                        {
                            title: 'Can Remove Taxes For Some Customer (Wholesalers)?',
                            sub_text: 'Yes. Create a new tax in the "Manage Taxes" feature in the "Getting Started" module. For this example, call the tax "Wholesale 0.00 %". Leave your current tax as the default (do not check default on the new Wholesale tax). Then go into the POS and you will see a drop down that allows you to select which tax you want to apply for that transaction.'
                        },
                        {
                            title: 'Can We Set Up Commissions For Employees?',
                            sub_text: 'Yes, we have a very good commissions module.'
                        },
                        {
                            title: 'Contact Us',
                            sub_text: 'Click on the orange "Contact Us" button on this page to send your question, comments, or feedback to us.'
                        },
                        {
                            title: 'Do You Support Multi-Store Shops (Locations)?',
                            sub_text: 'Yes. SK POS ERP Software can support an unlimited number of shop locations. All locations can share the same product names and SKU. Other features includes:<br><br><br>Ability to transfer stock from one location to another location,<br>Keep individual product inventories for each location,<br>Keep individual sales, expenses, and profits for each location,<br>Have one common set of products and product skus across all locations,<br>See which location has a specific item in stock, and<br>Manage staff for each location separately.'
                        },
                        {
                            title: 'How Can I Delete A Product?',
                            sub_text: 'You can "ARCHIVE" products and other data in the "Manage Data" module by clicking "Archive Data" then the next screen you can see all the data you can ARCHIVE. For products you will need to enter the SKU number to archive them.'
                        },
                        {
                            title: 'How Can I Delete Or Archive A User?',
                            sub_text: 'You can "Archive" a user by clicking on the row with their name, you will then see an "archive" button. Click the Archive button to archive the user. This will remove any access to the system for that user.'
                        },
                        {
                            title: 'How Can I Import My Inventory Into The System?',
                            sub_text: 'Yes. We can import your products for you. Once you have decided that you want to SK POS ERP Software in your shop we can import your files.<br><br>We accept files in Excel, CSV, and TXT formats. Your inventory file should start from the first column and each row is for a single product.'
                        },
                        {
                            title: 'How Many Users Can I Have?',
                            sub_text: 'The number of users you can have depends on the plan you are on.'
                        },
                        {
                            title: 'How To Handle Partial Payments And Balances Due?',
                            sub_text: 'When selling items if the complete transaction happens all at one time use the "Cash Register"/POS for that transaction. This is for cases when a customer comes in, pays in full, and takes the item. If any part of the transaction is delayed such as a balance due or special ordered product or whatever you then should use the "Orders" module. The Orders module will track open orders including outstanding balances and orders where all of the products paid for was not collected at the same time.'
                        },
                        {
                            title: 'I Can\'t Complete An "Order", What Did I Do Wron',
                            sub_text: 'Orders are typically used for sales where the whole order is not provided to the customer at the same time. The Order Module will keep track of open orders and payments until the order is completed. To complete the order you will need to change the "Shipping QTY" to equal the "QTY". To do this click on the edit icon (looks like a pencil at the end of the product row). Once the QTY and Shipping QTY numbers match the "Complete" button can be clicked.'
                        },
                        {
                            title: 'I Need Help Getting Started',
                            sub_text: 'Getting assistance when getting started is as simple as hitting the Contact Us button and submitting a help request. In addition to submitting a ticket, you can browse through our many help videos. I you require a product demonstration we can scheduled one at your convenience.'
                        },
                        {
                            title: 'My Question Is Not Listed',
                            sub_text: 'Click on the orange "Contact Us" button on this page to send your question, comments, or feedback to us.'
                        },
                        {
                            title: 'What If There Is A Feature I Need That Is Missing?',
                            sub_text: 'The SK POS ERP Software application is an evolving system that is updated regularly. A large component of new features come directly from user suggestions. We welcome all feature requests and the ones that would benefit our community of owners are developed by us at no cost to you.'
                        },
                        {
                            title: 'What Is Commissions?',
                            sub_text: 'Many stores will provide additional compensation to their sales staff for selling certain products. The Commissions module allows you to setup the rules around your commissions system (if necessary) and will track the sales and commissions for each staff.'
                        },
                        {
                            title: 'What Is The “Is This For A RETURN?” Option For?',
                            sub_text: 'When creating a Purchase Order, this option is used when returning a product BACK to the supplier (ex. product not functional). This option creates a negative Purchase Order for your tracking.'
                        },
                        {
                            title: 'What Is The “Prices” Feature Used For?',
                            sub_text: 'The "Prices" feature allows you to create promotional prices and discounts. These can be based on Customer Type, Quantity, or date range.'
                        },
                        {
                            title: 'What Is The “Regular Price” Field For?',
                            sub_text: 'In the system, "Regular Price" refers to the price that will be displayed in the Cash Register at the time of sale.'
                        },
                        {
                            title: 'What Kinds Of Payment Do You Accept?',
                            sub_text: 'Currently your SK POS ERP Software subscription can only be paid with bKash.'
                        },
                        {
                            title: 'Where Can I Find My Low Stock Inventory?',
                            sub_text: 'From the "Products" module there is a drop down option for "Low Stock". The default view is "All Products". You can also see the low inventory products from the "Dashboard" module.'
                        },
                        {
                            title: 'Where Can I Manage Suppliers?',
                            sub_text: 'Click on the "Manage Data" module (icon), then click on the "Manage Suppliers" link.'
                        },
                        {
                            title: 'Will SK POS ERP Software Integrate With Websites?',
                            sub_text: 'Yes. Your SK POS ERP Software account comes with a website builder that enables you to share your inventory online. If you have an existing website you can use inline frames to load your SK POS ERP website in your existing site. We are also working on widgets that you can add to your website to display some information on it.'
                        },
                        {
                            title: 'Will My Barcode Scanner And Receipt Printers Work?',
                            sub_text: 'Yes. SK POS ERP Software connects with most popular receipt printers and barcode scanners. SK POS ERP Software will also work with many label printers and cash drawers (through compatible receipt printers).'
                        }
                    ]

    const Tutorials = [
                        {
							sub_text: 'Video review of the point of sale features including receipt printing, discounting, and more...',
                            help_id:'video-9',
                            title: 'Point of Sale',
                            video_url: '212788811'
                        },
                        {
                            sub_text: 'Video review of the repair tracking features including estimates, device checklists, automatic customer email notifications, and more...',
                            help_id:'video-10',
                            title: 'Repair Tracking',
                            video_url: '212788938'
                        },
                        {
                            sub_text: 'Quick steps to get your account started...',
                            help_id:'video-4',
                            title: 'Getting Started',
                            video_url: '212788303'
                        },
                        {
                            sub_text: 'Accounts receivables to give credit to customers',
                            help_id:'video-40',
                            title: 'Customer Credit Terms',
                            video_url: '246144691'
                        },
                        {
                            sub_text: 'Allows you to track your employees time',
                            help_id:'video-39',
                            title: 'Employee Time Clock',
                            video_url: '242947996'
                        },
                        {
                            sub_text: 'You can create appointments for repairs or anything else.',
                            help_id:'video-38',
                            title: 'Appointment Calendar',
                            video_url: '242943820'
                        },
                  ]

    let title, sub_text, tdCol, pTag;
    showTableData.innerHTML = '';
        const titleRow = cTag('div',{ 'class': 'flexSpaBetRow', 'style': "padding: 5px;" });
            const titleHeader = cTag('h2');
            titleHeader.innerHTML = Translate('Help')+' ';
                let infoIcon = cTag('i',{ 'class':'fa fa-info-circle', 'style': "font-size: 16px;",'data-toggle':'tooltip','data-placement':'bottom','title':'','data-original-title':Translate('This page displays the list of helping videos and blog') });
            titleHeader.appendChild(infoIcon);
        titleRow.appendChild(titleHeader);
            let contactUsButton = cTag('a',{class:"btn createButton", title:"Contact Us"});
            contactUsButton.innerHTML = Translate('Contact Us');
            contactUsButton.addEventListener('click',showHelpPopup);
        titleRow.appendChild(contactUsButton);
    showTableData.appendChild(titleRow);
            let inputField = cTag('input',{type:"hidden", name:"prevuri", id:"prevuri", value:prevuri});
        titleRow.appendChild(inputField);
    showTableData.appendChild(titleRow);
        const helpContent = cTag('div',{class:"flexSpaBetRow"});
            let faqColumn = cTag('div',{class:"columnSM6"});
                const faqWidget = cTag('div',{class:"cardContainer"})
                    const faqWidgetHeader = cTag('div',{class:"cardHeader"});
                        const faqHeader = cTag('h3')
                        faqHeader.innerHTML = Translate('FAQ');
                    faqWidgetHeader.appendChild(faqHeader);
                faqWidget.appendChild(faqWidgetHeader)
            faqColumn.appendChild(faqWidget);
                    const faqContent = cTag('div',{class:"cardContent"});
                        const faqTable = cTag('table',{class:"table list"});
                            const faqBody =  cTag('tbody');
                            FAQs.forEach(onerow =>{
                                title = onerow.title;
                                sub_text = onerow.sub_text;
                                const faqHeadRow = cTag('tr');
                                    tdCol = cTag('td');
                                        pTag = cTag('p',{style:"margin-top: 12px; font-weight: bold; color: #a94442; font-size: 20px;"});
                                        pTag.innerHTML = title;
                                    tdCol.appendChild(pTag);
                                faqHeadRow.appendChild(tdCol);
                                        pTag = cTag('p');
                                        pTag.innerHTML = sub_text;
                                    tdCol.appendChild(pTag);
                                faqHeadRow.appendChild(tdCol);
                                faqBody.appendChild(faqHeadRow);
                            });
                        faqTable.appendChild(faqBody);
                    faqContent.appendChild(faqTable);
                faqWidget.appendChild(faqContent)
            faqColumn.appendChild(faqWidget);
        helpContent.appendChild(faqColumn);

            const trainingVideoColumn = cTag('div',{class:"columnSM6"});
                const videoWidget = cTag('div',{class:"cardContainer"})
                    const videoWidgetHeader = cTag('div',{class:"cardHeader"});
                        const videoHeader = cTag('h3')
                        videoHeader.innerHTML = Translate('Training Videos');
                    videoWidgetHeader.appendChild(videoHeader);
                videoWidget.appendChild(videoWidgetHeader)
            trainingVideoColumn.appendChild(videoWidget);
                    const trainingVideoContent = cTag('div',{class:"cardContent"});
                        const videoTable = cTag('table',{class:"table list"});
                            const videoBody =  cTag('tbody');
                            Tutorials.forEach(onerow =>{
                                title = onerow.title;
                                sub_text = onerow.sub_text;
                                let help_id =  onerow.help_id
                                let video_url = onerow.video_url;
                                const videoHeadRow = cTag('tr');
                                    tdCol = cTag('td');
                                        let aHref =  cTag('a',{'data-toggle':"modal", 'data-target':"#modal-"+help_id});
                                            let helpDiv = cTag('div',{class:"flex", 'style': "padding: 10px 0;"});
                                            helpDiv.addEventListener('click',function(){
                                                modalVideo("modal-" + help_id, 1);
                                            });
                                                let circleInfo = cTag('i',{class:"fa fa-play-circle", 'style': " font-size: 3em;"});
                                            helpDiv.appendChild(circleInfo);
                                                let titleSpan = cTag('span',{ 'style': "font-weight: bold; color: #a94442; font-size: 20px; margin-left: 15px; padding-top: 8px; padding-bottom: 4px;"});
                                                titleSpan.innerHTML = title;
                                            helpDiv.appendChild(titleSpan);
                                        aHref.appendChild(helpDiv)
                                    tdCol.appendChild(aHref);
                                            let subTextDiv = cTag('div',{ 'style': "padding-bottom: 10px;"});
                                            subTextDiv.addEventListener('click',function(){
                                                modalVideo("modal-" + help_id, 1);
                                            });
                                            subTextDiv.innerHTML = sub_text;
                                        aHref.appendChild(subTextDiv)
                                    tdCol.appendChild(aHref);
                                videoHeadRow.appendChild(tdCol);
                            videoBody.appendChild(videoHeadRow);
                                let modalVideoDiv = cTag('div',{class:"modal modal-video", id:"modal-"+help_id, tabindex:"-1", role:"dialog", 'aria-labelledby':"videoModalLabel", 'aria-hidden':"true"});
                                    let modalDialog = cTag('div',{class:"modal-dialog", style:"width:750px"});
                                        let modalContent = cTag('div',{class:"modal-content"});
                                            let modalHeader = cTag('div',{class:"flexSpaBetRow modal-header"});
                                                let modalTitle = cTag('h3',{id:"videoModalLabel", 'style': "font-weight: bold;"});
                                                modalTitle.innerHTML = title;
                                            modalHeader.appendChild(modalTitle);
                                                let modalButton = cTag('button',{type:"button", class:"close", 'data-dismiss':"modal", 'aria-hidden':"true"});
                                                modalButton.addEventListener('click',function(){
                                                    modalVideo("modal-" + help_id, 0);
                                                })
                                                modalButton.innerHTML = '&times';
                                            modalHeader.appendChild(modalButton);
                                        modalContent.appendChild(modalHeader)
                                            let modalBody = cTag('div',{class:"modal-body"});
                                                let videoContainer = cTag('div',{class:"video-container"});
                                                    let iFrame = cTag('iframe',{src:"//player.vimeo.com/video/"+ video_url +"?color=ffffff&amp;wmode=transparent;autoplay=0&title=0&byline=0&portrait=0", width:"720", height:"405", frameborder:"0", webkitallowfullscreen: true, mozallowfullscreen: true,  allowfullscreen: true });
                                                videoContainer.appendChild(iFrame);
                                            modalBody.appendChild(videoContainer);
                                        modalContent.appendChild(modalBody);
                                    modalDialog.appendChild(modalContent);
                                modalVideoDiv.appendChild(modalDialog);
                            showTableData.appendChild(modalVideoDiv);
                            });
                        videoTable.appendChild(videoBody);
                    trainingVideoContent.appendChild(videoTable);
                videoWidget.appendChild(trainingVideoContent)
            trainingVideoColumn.appendChild(videoWidget);
        helpContent.appendChild(trainingVideoColumn);
    showTableData.appendChild(helpContent);
}

function modalVideo(id,flag){
    if(flag === 1){
        document.getElementById(id).style.display = 'block';
    }
    else if(flag === 0){
        document.getElementById(id).style.display = 'none';
    }
}

async function index(){
    let status, aTag, pTag;
    status = '';
    const url = '/'+segment1+'/AJ_index_MoreInfo';

    fetchData(afterFetch,url,{});

    function afterFetch(data){
        const showTableData = document.getElementById('viewPageInfo');

        let modulesInfo = {
            '1': {label:Translate('Cash Register'),fileName:'POS',icon:'shopping-cart'},
            '2': {label:Translate('Repairs'),fileName:'Repairs',icon:'wrench'},
            '3': {label:Translate('Invoices'),fileName:'Invoices',icon:'folder-open'},
            '4': {label:Translate('Customers'),fileName:'Customers',icon:'address-book'},
            '5': {label:Translate('Products'),fileName:'Products',icon:'barcode'},
            '6': {label:Translate('Purchase Orders'),fileName:'Purchase_orders',icon:'plus-square'},
            '7': {label:Translate('Orders'),fileName:'Orders',icon:'pencil-square-o'},
            '8': {label:Translate('Devices Inventory'),fileName:'IMEI',icon:'tablet'},
            '9': {label:Translate('Stock Take'),fileName:'Stock_Take',icon:'folder-open'},
            '10': {label:Translate('Expenses'),fileName:'Expenses',icon:'money'},
            '11': {label:Translate('Suppliers'),fileName:'Suppliers',icon:'address-book'},
            '13': {label:Translate('Dashboard'),fileName:'Dashboard',icon:'line-chart'},
            '14': {label:Translate('End of Day'),fileName:'End_of_Day',icon:'money'},
            '15': {label:Translate('Appointment Calendar'),fileName:'Appointment_Calendar',icon:'calendar-plus-o'},
            '16': {label:Translate('Accounts Receivables'),fileName:'Accounts_Receivables',icon:'credit-card'},
            '17': {label:Translate('Time Clock Manager'),fileName:'Time_Clock',icon:'clock-o'},
            '18': {label:Translate('Website'),fileName:'Website',icon:'globe'},
            '19': {label:Translate('Commissions'),fileName:'Commissions',icon:'bullhorn'},
            '20': {label:Translate('Sales Reports'),fileName:'Sales_reports',icon:'pie-chart'},
            '21': {label:Translate('Repairs Reports'),fileName:'Repairs_reports',icon:'pie-chart'},
            '22': {label:Translate('Inventory Reports'),fileName:'Inventory_reports',icon:'pie-chart'},
            '23': {label:Translate('Activity Feed'),fileName:'Activity_Feed',icon:'exchange'},
            '24': {label:Translate('Getting Started'),fileName:'Getting_Started',icon:'cog'},
            '25': {label:Translate('Manage Data'),fileName:'Manage_Data',icon:'cog'},
            '26': {label:Translate('Setup'),fileName:'Settings',icon:'cog'},
            '27': {label:Translate('Integrations'),fileName:'Integrations',icon:'compress'},
            '28': {label:Translate('Accounts'),fileName:'Accounts',icon:'money'},
        }
	    if(multipleLocations>0) modulesInfo['12'] = {label:Translate('Inventory Transfer'),fileName:'Inventory_Transfer',icon:'truck'};


        const NumberOfPossibleNavItems = Math.floor((window.innerHeight-document.querySelector('header').getBoundingClientRect().height)/100);
        let allowedModules = modulesInfo;
        if(!Array.isArray(allowed)) allowedModules = allowed;
        const NumberOfPossibleHomeItems = Object.keys(allowedModules).length - NumberOfPossibleNavItems; 
    
        status = data.status;

        if(NumberOfPossibleHomeItems>0){
            const modulesRow = cTag('div',{ 'class':`flexSpaBetRow` });
                const modulesColumn = cTag('div',{ 'class':`columnSM12`, 'style': "margin-top: 0px;" });
                    const modulesWidget = cTag('div',{ 'class':`cardContainer ` });
                        let modulesWidgetHeader = cTag('div',{ 'class':`cardHeader ` });
                            const modulesHeader = cTag('h3');
                            modulesHeader.innerHTML = Translate('Modules list');
                        modulesWidgetHeader.appendChild(modulesHeader);
                    modulesWidget.appendChild(modulesWidgetHeader);
                        let modulesContent = cTag('div',{ 'class':`cardContent` });
                            let ulMenu = cTag('ul',{ 'class':`flexStartRow moduleLists`, 'style': "text-align: center;" });
                            if(accountsInfo[1]>0){
                                for (const key in allowedModules) {
                                    if(NumberOfPossibleNavItems<key && modulesInfo[key]){
                                        let fonticon = modulesInfo[key].icon;
                                        let module = modulesInfo[key].fileName;
                                        let title = modulesInfo[key].label;
                                        let liMenu = cTag('li');
                                            let homeDiv = cTag('div',{ 'class':`homeiconmenu boxshadow `, 'style': "background: #0185b6;" });
                                                let aTag = cTag('a',{ 'class':`firstclild sidebarlink`, 'style': "color: white;", 'href': module , 'title': title  });
                                                    let iconTag = cTag('i',{ 'class':"fa fa-"+ fonticon, 'style': "font-size: 2em;"});
                                                aTag.append(cTag('br'), iconTag, cTag('br'),title);
                                            homeDiv.appendChild(aTag);
                                        liMenu.appendChild(homeDiv);
                                        ulMenu.appendChild(liMenu);
                                    }
                                }
                            }
                        modulesContent.appendChild(ulMenu);
                    modulesWidget.appendChild(modulesContent);
                modulesColumn.appendChild(modulesWidget);
            modulesRow.appendChild(modulesColumn);
            showTableData.appendChild(modulesRow);
        }
        //status = 'Trial'
        let URL = window.location.hostname;

        let OUR_DOMAINNAME = extractRootDomain(URL);

        if(['machousel.com.bd', 'machouse.com.bd'].includes(OUR_DOMAINNAME)){
            const statusRow = cTag('div',{ 'class':`flexSpaBetRow ` });
                const statusColumn = cTag('div',{ 'class':`columnSM6 ` });
                if(status==='Trial'){
                        const statusWidget = cTag('div',{ 'class':`cardContainer ` });
                            const softwareWidget = cTag('div',{ 'class':`cardHeader ` });
                                const softwareWidgetHeader = cTag('h3');
                                softwareWidgetHeader.innerHTML = Translate('This software is/has') + ':';
                            softwareWidget.appendChild(softwareWidgetHeader);
                        statusWidget.appendChild(softwareWidget);
                            const gdprContent = cTag('div',{ 'class':`flexSpaBetRow cardContent`, 'style': "text-align: center;" });
                                let gdprTitle = cTag('div',{ 'class':`columnXS2`});
                                gdprTitle.appendChild(cTag('img',{src:"/assets/images/GDPR-Compliant.png", class:"img-responsive", alt: Translate('GDPR Compliant')}));
                                    const gdprHeader = cTag('h3');
                                    gdprHeader.innerHTML = Translate('GDPR Compliant');
                                gdprTitle.appendChild(gdprHeader);
                            gdprContent.appendChild(gdprTitle);
                                let importingTitle = cTag('div',{ 'class':`columnXS2`});
                                importingTitle.appendChild(cTag('img',{src:"/assets/images/Data-Importing.png", class:"img-responsive", alt:Translate('Data Importing')}));
                                    const importingHeader = cTag('h3');
                                    importingHeader.innerHTML = Translate('Data Importing');
                                importingTitle.appendChild(importingHeader);
                            gdprContent.appendChild(importingTitle);
                                const multiLanguageTitle = cTag('div',{ 'class':`columnXS2`});
                                multiLanguageTitle.appendChild(cTag('img',{src:"/assets/images/Multi-Language.png", class:"img-responsive", alt:Translate('Multi Language')}));
                                    const multiLanguageHeader = cTag('h3');
                                    multiLanguageHeader.innerHTML = Translate('Multi Language');
                                multiLanguageTitle.appendChild(multiLanguageHeader);
                            gdprContent.appendChild(multiLanguageTitle);
                                const multiCurrencyTitle = cTag('div',{ 'class':`columnXS2 `});
                                multiCurrencyTitle.appendChild(cTag('img',{src:"/assets/images/Multi-Currency.png", class:"img-responsive", alt:Translate('Multi Currency')}));
                                    const multiCurrencyHeader = cTag('h3');
                                    multiCurrencyHeader.innerHTML = Translate('Multi Currency');
                                multiCurrencyTitle.appendChild(multiCurrencyHeader);
                            gdprContent.appendChild(multiCurrencyTitle);
                                const easyUseTitle = cTag('div',{ 'class':`columnXS2`});
                                easyUseTitle.appendChild(cTag('img',{src:"/assets/images/Easy-to-Use.png", class:"img-responsive", alt:Translate('Easy to Use')}));
                                    const easyUseHeader = cTag('h3');
                                    easyUseHeader.innerHTML = Translate('Easy to Use');
                                easyUseTitle.appendChild(easyUseHeader);
                            gdprContent.appendChild(easyUseTitle);
                        statusWidget.appendChild(gdprContent);
                    statusColumn.appendChild(statusWidget);

                            const accountColumn = cTag('div',{ 'class':`columnSM6 ` });
                                const accountWidget = cTag('div',{ 'class':`cardContainer ` });
                                    const accountTitle = cTag('div',{ 'class':`cardHeader ` });
                                        const accountHeader = cTag('h3');
                                        accountHeader.innerHTML =  Translate('Your account includes') + ':';
                                    accountTitle.appendChild(accountHeader);
                                accountWidget.appendChild(accountTitle);
                                    const unlimitedContent = cTag('div',{ 'class':`flexSpaBetRow cardContent`, 'style': "text-align: center;" });
                                        const upgradeTitle = cTag('div',{ 'class':`columnXS2 `});
                                        upgradeTitle.appendChild(cTag('img',{src:"/assets/images/Unlimited-Upgrades.png", class:"img-responsive", alt:Translate('Unlimited Upgrades')}));
                                            const upgradeHeader = cTag('h3');
                                            upgradeHeader.innerHTML =  Translate('Unlimited Upgrades') ;
                                        upgradeTitle.appendChild(upgradeHeader);
                                    unlimitedContent.appendChild(upgradeTitle);
                                        const userTitle = cTag('div',{ 'class':`columnXS2 ` });
                                        userTitle.appendChild(cTag('img',{src:"/assets/images/Unlimited-Users.png", class:"img-responsive", alt:Translate('Unlimited Users')}));
                                            const userHeader = cTag('h3');
                                            userHeader.innerHTML =  Translate('Unlimited Users') ;
                                        userTitle.appendChild(userHeader);
                                    unlimitedContent.appendChild(userTitle);
                                        const repairTitle = cTag('div',{ 'class':`columnXS2`});
                                        repairTitle.appendChild(cTag('img',{src:"/assets/images/Unlimited-Repairs.png", class:"img-responsive", alt:Translate('Unlimited Repairs')}));
                                            const repairHeader = cTag('h3');
                                            repairHeader.innerHTML =  Translate('Unlimited Repairs') ;
                                        repairTitle.appendChild(repairHeader);
                                    unlimitedContent.appendChild(repairTitle);
                                        const paymentTitle = cTag('div',{ 'class':`columnXS2 `});
                                        paymentTitle.appendChild(cTag('img',{src:"/assets/images/Unlimited-Payments.png", class:"img-responsive", alt:Translate('Unlimited Payments')}));
                                            const paymentHeader = cTag('h3');
                                            paymentHeader.innerHTML =  Translate('Unlimited Payments') ;
                                        paymentTitle.appendChild(paymentHeader);
                                    unlimitedContent.appendChild(paymentTitle);
                                        const supportTitle = cTag('div',{ 'class':`columnXS2`});
                                        supportTitle.appendChild(cTag('img',{src:"/assets/images/Top-Support.png", class:"img-responsive", alt:Translate('Top Support')}));
                                            const supportHeader = cTag('h3');
                                            supportHeader.innerHTML =  Translate('Top Support') ;
                                        supportTitle.appendChild(supportHeader);
                                    unlimitedContent.appendChild(supportTitle);
                                accountWidget.appendChild(unlimitedContent);
                            accountColumn.appendChild(accountWidget);
                        statusRow.append(statusColumn,accountColumn);
                    showTableData.appendChild(statusRow);

                    const videoRow = cTag('div',{ 'class':`flexSpaBetRow` });
                        const videoColumn = cTag('div',{ 'class':`columnSM6` });
                            const videoWidget = cTag('div',{ 'class':`cardContainer` });
                                const videoOverView = cTag('div',{ 'class':`cardHeader` });
                                    const videoOverViewHeader = cTag('h3');
                                    videoOverViewHeader.innerHTML =  Translate('Video Overviews') ;
                                videoOverView.appendChild(videoOverViewHeader);
                            videoWidget.appendChild(videoOverView);
                                const videoContent = cTag('div',{ 'class':`cardContent`, 'style': "margin-bottom: 10px;" });
                                    let videoTable = cTag('table',{ 'class':`table list` });
                                        let videoBody = cTag('tbody');
                                            const Tutorials = [
                                                {
                                                    sub_text: 'Video review of the point of sale features including receipt printing, discounting, and more...',
                                                    help_id:'video-9',
                                                    title: 'Point of Sale',
                                                    length: '2:02 min',
                                                    video_url: '212788811'
                                                },
                                                {
                                                    sub_text: 'Video review of the repair tracking features including estimates, device checklists, automatic customer email notifications, and more...',
                                                    help_id:'video-10',
                                                    title: 'Repair Tracking',
                                                    length: '2:59 min',
                                                    video_url: '212788938'
                                                },
                                                {
                                                    sub_text: 'Quick steps to get your account started...',
                                                    help_id:'video-4',
                                                    title: 'Getting Started',
                                                    length: '0:49 min',
                                                    video_url: '212788303'
                                                },
                                            ]
                                            Tutorials.forEach(onerow =>{
                                                let title = onerow.title;
                                                let sub_text = onerow.sub_text;
                                                let help_id =  onerow.help_id
                                                let video_url = onerow.video_url;
                                                const videoHeadRow = cTag('tr');
                                                    let tdCol = cTag('td');
                                                        let aHref =  cTag('a',{'data-toggle':"modal", 'data-target':"#modal-"+help_id});
                                                            let helpDiv = cTag('div',{class:"flexSpaBetRow", 'style': "padding: 10px 0;"});
                                                            helpDiv.addEventListener('click',function(){
                                                                modalVideo("modal-" + help_id, 1);
                                                            });
                                                                let circleInfo = cTag('i',{class:"fa fa-play-circle", 'style': " font-size: 3em;"});
                                                            helpDiv.appendChild(circleInfo);
                                                                let titleSpan = cTag('span',{ 'style': "font-weight: bold; color: #a94442; font-size: 20px; margin-left: 15px; padding-top: 8px; padding-bottom: 4px;"});
                                                                titleSpan.innerHTML = title;
                                                            helpDiv.appendChild(titleSpan);
                                                            let tdTime = cTag('td',{ 'width': `120` });
                                                                pTag = cTag('p',{ 'class':`h3`,'style':`text-align: right; margin-top: 12px; color: #a94442;` });
                                                                pTag.innerHTML = onerow.length;
                                                            tdTime.appendChild(pTag);
                                                        helpDiv.appendChild(tdTime);
                                                        aHref.appendChild(helpDiv)
                                                    tdCol.appendChild(aHref);
                                                            let subTextDiv = cTag('div',{ 'style': "padding-bottom: 10px;"});
                                                            subTextDiv.addEventListener('click',function(){
                                                                modalVideo("modal-" + help_id, 1);
                                                            });
                                                            subTextDiv.innerHTML = sub_text;
                                                        aHref.appendChild(subTextDiv)
                                                    tdCol.appendChild(aHref);
                                                videoHeadRow.appendChild(tdCol);
                                                videoBody.appendChild(videoHeadRow);
                                                let modalVideoDiv = cTag('div',{class:"modal modal-video", id:"modal-"+help_id, tabindex:"-1", role:"dialog", 'aria-labelledby':"videoModalLabel", 'aria-hidden':"true"});
                                                    let modalDialog = cTag('div',{class:"modal-dialog", style:"width:750px"});
                                                        let modalContent = cTag('div',{class:"modal-content"});
                                                            let modalHeader = cTag('div',{class:"flexSpaBetRow modal-header"});
                                                                let modalTitle = cTag('h3',{id:"videoModalLabel", 'style': "font-weight: bold;"});
                                                                modalTitle.innerHTML = title;
                                                            modalHeader.appendChild(modalTitle);
                                                                let modalButton = cTag('button',{type:"button", class:"close", 'data-dismiss':"modal", 'aria-hidden':"true"});
                                                                modalButton.addEventListener('click',function(){
                                                                    modalVideo("modal-" + help_id, 0);
                                                                })
                                                                modalButton.innerHTML = '&times';
                                                            modalHeader.appendChild(modalButton);
                                                        modalContent.appendChild(modalHeader)
                                                            let modalBody = cTag('div',{class:"modal-body"});
                                                                let videoContainer = cTag('div',{class:"video-container"});
                                                                    let iFrame = cTag('iframe',{src:"//player.vimeo.com/video/"+ video_url +"?color=ffffff&amp;wmode=transparent;autoplay=0&title=0&byline=0&portrait=0", width:"720", height:"405", frameborder:"0", webkitallowfullscreen: true, mozallowfullscreen: true,  allowfullscreen: true });
                                                                videoContainer.appendChild(iFrame);
                                                            modalBody.appendChild(videoContainer);
                                                        modalContent.appendChild(modalBody);
                                                    modalDialog.appendChild(modalContent);
                                                modalVideoDiv.appendChild(modalDialog);
                                                videoBody.appendChild(modalVideoDiv);
                                            });
                                    videoTable.appendChild(videoBody);
                                videoContent.appendChild(videoTable);
                            videoWidget.appendChild(videoContent);
                        videoColumn.appendChild(videoWidget);
                            const resourceWidget = cTag('div',{ 'class':`cardContainer` });
                                const resourceTitle = cTag('div',{ 'class':`cardHeader` });
                                    const resourceHeader = cTag('h3');
                                    resourceHeader.innerHTML = Translate('Resources');
                                resourceTitle.appendChild(resourceHeader);
                            resourceWidget.appendChild(resourceTitle);
                                const resourceContent = cTag('div',{ 'class':`cardContent` });
                                    let ulHomeContent = cTag('ul',{ 'class':`fa-ul ` });
                                        let liQuestion = cTag('li');
                                            aTag = cTag('a',{ 'title': Translate('Ask a question') });
                                            aTag.addEventListener('click',showHelpPopup);
                                            aTag.appendChild(cTag('i',{ 'class':`fa-li fa fa-life-ring ` }));
                                            aTag.append( Translate('Ask a question') );
                                        liQuestion.appendChild(aTag);
                                    ulHomeContent.appendChild(liQuestion);
                                        let liTutorial = cTag('li');
                                            aTag = cTag('a',{ 'href':`/Home/help `,'title':`Videos & Tutorials ` });
                                            aTag.appendChild(cTag('i',{ 'class':`fa-li fa fa-play-circle ` }));
                                            aTag.append( Translate('Videos & Tutorials') );
                                        liTutorial.appendChild(aTag);
                                    ulHomeContent.appendChild(liTutorial);
                                        let liBlog = cTag('li');
                                            aTag = cTag('a',{ 'target':`_blank `, href:'//' + OUR_DOMAINNAME + '/articles/','title':`Blog ` });
                                            aTag.appendChild(cTag('i',{ 'class':`fa-li fa fa-rss ` }));
                                            aTag.append('Blog');
                                        liBlog.appendChild(aTag);
                                    ulHomeContent.appendChild(liBlog);
                                resourceContent.appendChild(ulHomeContent);
                            resourceWidget.appendChild(resourceContent);
                        videoColumn.appendChild(resourceWidget);
                    videoRow.appendChild(videoColumn);

                            let subscribeColumn = cTag('div',{ 'class':`columnSM6 ` });
                                let subscribeWidget = cTag('div',{ 'class':`cardContainer ` });
                                    const subscribeTitle = cTag('div',{ 'class':`cardHeader` });
                                        const subscribeHeader = cTag('h3');
                                        subscribeHeader.innerHTML = Translate('SUBSCRIBE NOW!') ;
                                    subscribeTitle.appendChild(subscribeHeader);
                                subscribeWidget.appendChild(subscribeTitle);
                                    const subscribeContent = cTag('div',{ 'class':`cardContent` });
                                        pTag = cTag('p',{ 'style':`font-size:18px; text-align: center; ` });
                                        pTag.innerHTML = Translate('Become more efficient and increase your profits TODAY!') ;
                                    subscribeContent.appendChild(pTag);
                                        pTag = cTag('p',{ 'style':`font-size:14px; text-align: center;padding-bottom: 20px; ` });
                                            let bTag = cTag('b');
                                            bTag.innerHTML =  Translate('Subscribe to our')+ ' $'+ data.price_per_location + ' USD '+ Translate('monthly account via bKash.') ;
                                        pTag.appendChild(bTag);
                                    subscribeContent.appendChild(pTag);
                                        let subscribeDiv = cTag('div',{ 'align':`center ` });
                                            aTag = cTag('a',{ 'class':`btn subscribeButton bgyellow `, 'style': "color: #F00;", 'title': Translate('Subscribe to our')+ ' $'+ data.price_per_location + ' USD '+Translate('monthly account via bKash.')});
                                            aTag.addEventListener('click', function(){
                                                if(accountsInfo[3]===1){window.location='/Account/payment_details';}
                                                else{alert_dialog(Translate('You are not admin User'), Translate('You are not the user that created this account. To subscribe please have the account creator log in and click this button'), Translate('Ok'));}
                                            });
                                            aTag.innerHTML = Translate('SUBSCRIBE NOW!') ;
                                        subscribeDiv.appendChild(aTag);
                                    subscribeContent.appendChild(subscribeDiv);
                                        pTag = cTag('p',{ 'style':`padding-top:5px; ` });
                                        pTag.appendChild(cTag('i',{ 'class':`fa fa-check `,'aria-hidden':`true ` }));
                                        pTag.append(' ',Translate('No contracts, cancel any time!') );
                                    subscribeContent.appendChild(pTag);
                                        pTag = cTag('p');
                                        pTag.appendChild(cTag('i',{ 'class':`fa fa-check `,'aria-hidden':`true ` }));
                                        pTag.append(' ',Translate('Only')+ ' $'+ data.price_per_location + ' USD '+Translate('per store/month'));
                                    subscribeContent.appendChild(pTag);
                                        pTag = cTag('p');
                                        pTag.appendChild(cTag('i',{ 'class':`fa fa-check `,'aria-hidden':`true ` }));
                                        pTag.append(' ',Translate('After successful payment, you will receive an email indicating that your accounts has been upgraded (this is a manual process and may take a couble of hours depending on the time of the day)') );
                                    subscribeContent.appendChild(pTag);
                                subscribeWidget.appendChild(subscribeContent);
                            subscribeColumn.appendChild(subscribeWidget);
                        videoRow.append(subscribeColumn);
                    showTableData.appendChild(videoRow);
                }
        }
    }
}

function notpermitted(){
    const showTableData = document.getElementById('viewPageInfo');
    showTableData.innerHTML = '';
        const notPermittedRow = cTag('div',{ 'class': 'flexSpaBetRow' });
            let notPermittedColumn = cTag('div',{ 'class':'columnXS12'});
                let calloutDiv = cTag('div',{ 'class':'innerContainer' });
                    let notPermittedHeader = cTag('h4');
                    notPermittedHeader.innerHTML = Translate('Not Permitted');
                    let pTag = cTag('p');
                    pTag.innerHTML = Translate('Sorry, you do not have permission to view this page');
                calloutDiv.append(notPermittedHeader, pTag);
            notPermittedColumn.appendChild(calloutDiv);
        notPermittedRow.appendChild(notPermittedColumn);
    showTableData.appendChild(notPermittedRow);
}

function extractRootDomain(url) {
    let domain = url;
    let splitArr = domain.split('.');
    let arrLen = splitArr.length;

    if (arrLen > 2) {
        domain = splitArr[arrLen - 2] + '.' + splitArr[arrLen - 1];
        //check to see if it's using a Country Code Top Level Domain (ccTLD) (i.e. ".me.uk")
        if (splitArr[arrLen - 2].length === 2 && splitArr[arrLen - 1].length === 2) {
            domain = splitArr[arrLen - 3] + '.' + domain;
        }
    }
    return domain;
}

document.addEventListener('DOMContentLoaded', async()=>{    
    let layoutFunctions = {help,index,notpermitted};
    layoutFunctions[segment2]();      
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));    
});