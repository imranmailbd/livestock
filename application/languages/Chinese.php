<?php
class Chinese{
	public function index($index){
		$languageData = array(
			'// Invoice Information //'=>stripslashes('//发票信息//'),
			'Account Status:'=>stripslashes('帐户状态：'),
			'Accounts Information'=>stripslashes('账户信息'),
			'Accounts Receivables'=>stripslashes('应收账款'),
			'Accounts Receivables Details'=>stripslashes('应收帐款明细'),
			'Accounts Setup'=>stripslashes('帐户设置'),
			'Accounts State:'=>stripslashes('账户状态：'),
			'Accounts SUSPENDED'=>stripslashes('帐户被暂停'),
			'Activity Report'=>stripslashes('活动报告'),
			'Add Customer Information'=>stripslashes('添加客户信息'),
			'Add Order'=>stripslashes('添加订单'),
			'Address'=>stripslashes('地址'),
			'Address Line 1'=>stripslashes('地址栏1'),
			'Address Line 2'=>stripslashes('地址第2行'),
			'Admin of'=>stripslashes('管理员'),
			'All pages footer'=>stripslashes('所有页面页脚'),
			'All pages header'=>stripslashes('所有页面标题'),
			'Amount Due'=>stripslashes('到期金额'),
			'and check things out. If you have any questions feel free to'=>stripslashes('并检查一下。如果您有任何问题，请随时'),
			'Appointment Calendar'=>stripslashes('预约日历'),
			'Archive Data'=>stripslashes('存档数据'),
			'Archived'=>stripslashes('存档'),
			'AT COMPETITIVE PRICES'=>stripslashes('在竞争价格'),
			'Barcode Labels'=>stripslashes('条形码标签'),
			'Based on'=>stripslashes('基于'),
			'Bill To'=>stripslashes('记账到'),
			'Bin Location'=>stripslashes('Bin位置'),
			'Brand and model of device'=>stripslashes('设备的品牌和型号'),
			'Brand Model'=>stripslashes('品牌模型'),
			'Brand/Model/More Details:'=>stripslashes('品牌/型号/更多详情：'),
			'By'=>stripslashes('经过'),
			'Carriers'=>stripslashes('运营商'),
			'Cash Register'=>stripslashes('收银机'),
			'Category'=>stripslashes('类别'),
			'Category archived'=>stripslashes('归类的类别'),
			'Category Created'=>stripslashes('分类创建'),
			'Category was edited'=>stripslashes('类别已编辑'),
			'CELL PHONE REPAIR'=>stripslashes('手机维修'),
			'Change Inventory Transfer'=>stripslashes('更改库存转移'),
			'Changed Status to'=>stripslashes('状态已更改为'),
			'Check Repair Status'=>stripslashes('检查修复状态'),
			'Check Repair Status Online'=>stripslashes('在线检查修复状态'),
			'City / Town'=>stripslashes('城市/城镇'),
			'Clicked Subscribe'=>stripslashes('点击订阅'),
			'Clock In Date'=>stripslashes('时钟在日期'),
			'Clock In Time'=>stripslashes('时钟'),
			'Clock Out Date'=>stripslashes('时钟输出日期'),
			'Clock Out Time'=>stripslashes('时钟输出时间'),
			'Commission Details'=>stripslashes('委员会详情'),
			'Commission Report'=>stripslashes('委员会报告'),
			'Company'=>stripslashes('公司'),
			'Company customer service email address could not found.'=>stripslashes('找不到公司客户服务电子邮件地址。'),
			'Company Information'=>stripslashes('公司信息'),
			'COMPANYNAME Software is used to manage store activities like POS, Repair Ticketing, Inventory and Staff Management, and more.'=>stripslashes('COMPANYNAME软件用于管理商店活动，如POS，修理票务，库存和员工管理等。'),
			'Conditions'=>stripslashes('条件'),
			'Confirm Purchase Order'=>stripslashes('确认采购订单'),
			'Contact Us'=>stripslashes('联系我们'),
			'Cost'=>stripslashes('成本'),
			'Count on us for your cell phone purchase and service needs.  Reliable phones, quality repair services, unlocking, accessories, and more.'=>stripslashes('依靠我们为您的手机购买和服务需求。可靠的手机，优质的维修服务，解锁，配件等。'),
			'Counting Cash Til'=>stripslashes('计算现金'),
			'Country'=>stripslashes('国家'),
			'Create Inventory Transfer'=>stripslashes('创建库存转移'),
			'Create Purchase Order'=>stripslashes('创建采购订单'),
			'Create Stock Take'=>stripslashes('创建盘点'),
			'Custom Fields'=>stripslashes('自定义字段'),
			'Custom Statuses'=>stripslashes('自定义状态'),
			'Customer'=>stripslashes('顾客'),
			'Customer Created'=>stripslashes('客户创建'),
			'Customer Information'=>stripslashes('客户信息'),
			'Customer Orders'=>stripslashes('客户订单'),
			'Customer type was edited'=>stripslashes('客户类型已编辑'),
			'Customers'=>stripslashes('顾客'),
			'Customers archived successfully.'=>stripslashes('客户已成功存档。'),
			'Dashboard'=>stripslashes('仪表板'),
			'Date field is required.'=>stripslashes('日期字段是必需的。'),
			'Date time to meet'=>stripslashes('约会时间'),
			'Day of the Week'=>stripslashes('一周中的天'),
			'Days Free Trial'=>stripslashes('天免费试用'),
			'Days Remaining'=>stripslashes('剩余天数'),
			'days until your account will no longer operate. Please update your payment method.'=>stripslashes('您的帐户将无法再运行的天数。请更新您的付款方式。'),
			'Dear'=>stripslashes('亲'),
			'DELETE a PAYMENT from'=>stripslashes('删除付款'),
			'Description'=>stripslashes('描述'),
			'Device Information'=>stripslashes('设备信息'),
			'Devices Dashboard'=>stripslashes('设备仪表板'),
			'Devices Inventory'=>stripslashes('设备库存'),
			'Display Add Customer Information'=>stripslashes('显示添加客户信息'),
			'Due Date'=>stripslashes('截止日期'),
			'E-mail'=>stripslashes('电子邮件'),
			'Edit Order'=>stripslashes('编辑订单'),
			'Email'=>stripslashes('电子邮件'),
			'Email Address'=>stripslashes('电子邮件地址'),
			'Email is required.'=>stripslashes('电子邮件是必需的。'),
			'Email sent to'=>stripslashes('电子邮件发送至'),
			'Employee archived successfully.'=>stripslashes('员工存档成功。'),
			'Employee was edited'=>stripslashes('员工已编辑'),
			'End of Day Closed'=>stripslashes('一天结束'),
			'End of Day Report'=>stripslashes('结束日报告'),
			'End of Day Updated.'=>stripslashes('结束了更新。'),
			'End of the day Starting Balance added successfully.'=>stripslashes('一天结束Starting Balance成功添加。'),
			'End of the day Starting Balance updated successfully.'=>stripslashes('一天结束Starting Balance已成功更新。'),
			'error occurred while adding new customer! please try again.'=>stripslashes('添加新客户时出错！请再试一次。'),
			'error occurred while booking new appointment! please try again.'=>stripslashes('预订新约会时出错！请再试一次。'),
			'Estimate'=>stripslashes('估计'),
			'Expense type was edited'=>stripslashes('费用类型已编辑'),
			'Expenses Details'=>stripslashes('费用详情'),
			'Export Data'=>stripslashes('导出数据'),
			'Fax'=>stripslashes('传真'),
			'Field is required.'=>stripslashes('字段是必需的。'),
			'First Name is required.'=>stripslashes('名字是必需的。'),
			'Form'=>stripslashes('形成'),
			'Form Fields'=>stripslashes('表格字段'),
			'Form was edited'=>stripslashes('表格已编辑'),
			'Forms'=>stripslashes('形式'),
			'Forms data added successfully.'=>stripslashes('表单数据已成功添加。'),
			'from'=>stripslashes('从'),
			'FULL SERVICE'=>stripslashes('全面服务'),
			'General'=>stripslashes('一般'),
			'Grand Total'=>stripslashes('累计'),
			'Hardware Info'=>stripslashes('硬件信息'),
			'Help'=>stripslashes('帮助'),
			'HERE FOR YOU'=>stripslashes('在这里，你'),
			'Hi'=>stripslashes('你好'),
			'Home'=>stripslashes('家'),
			'Home Page Body'=>stripslashes('主页正文'),
			'Hour field is required.'=>stripslashes('小时字段是必需的。'),
			'IMEI'=>stripslashes('IMEI'),
			'IMEI Created'=>stripslashes('IMEI创建'),
			'IMEI/Serial No.'=>stripslashes('IMEI /序列号'),
			'Import Customers'=>stripslashes('导入客户'),
			'Import Products'=>stripslashes('进口产品'),
			'Info'=>stripslashes('信息'),
			'Inventory Purchased'=>stripslashes('已购买库存'),
			'Inventory Reports'=>stripslashes('库存报告'),
			'Inventory Transfer'=>stripslashes('库存转移'),
			'Inventory Transfer Print-po #:'=>stripslashes('库存转移Print-po＃：'),
			'Inventory Value'=>stripslashes('库存价值'),
			'Inventory ValueN'=>stripslashes('存货价值N'),
			'Invoice #: s'=>stripslashes('发票＃：s'),
			'Invoice Setup'=>stripslashes('发票设置'),
			'Invoices Report'=>stripslashes('发票报告'),
			'Language information removed successfully.'=>stripslashes('语言信息已成功删除。'),
			'Languages'=>stripslashes('语言'),
			'Locations'=>stripslashes('地点'),
			'Login'=>stripslashes('登录'),
			'Login Message'=>stripslashes('登入留言'),
			'Logo'=>stripslashes('商标'),
			'Manage Accounts'=>stripslashes('管理帐户'),
			'Manage Brand Model'=>stripslashes('管理品牌模型'),
			'Manage Categories'=>stripslashes('管理类别'),
			'Manage Commissions'=>stripslashes('管理佣金'),
			'Manage CRM'=>stripslashes('管理CRM'),
			'Manage Customer Type'=>stripslashes('管理客户类型'),
			'Manage Customers'=>stripslashes('管理客户'),
			'Manage End of Day'=>stripslashes('管理结束日'),
			'Manage EU GDPR'=>stripslashes('管理欧盟GDPR'),
			'Manage Expense Type'=>stripslashes('管理费用类型'),
			'Manage Expenses'=>stripslashes('管理费用'),
			'Manage Label Printer'=>stripslashes('管理标签打印机'),
			'Manage Manufacturer'=>stripslashes('管理制造商'),
			'Manage Products'=>stripslashes('管理产品'),
			'Manage Repair Problem'=>stripslashes('管理维修问题'),
			'Manage SMS Messaging'=>stripslashes('管理SMS消息'),
			'Manage Suppliers'=>stripslashes('管理供应商'),
			'Manage Taxes'=>stripslashes('管理税收'),
			'Manage Vendors'=>stripslashes('管理供应商'),
			'Manage Website'=>stripslashes('管理网站'),
			'Manufacturer'=>stripslashes('生产厂家'),
			'Manufacturer archived'=>stripslashes('制造商存档'),
			'Manufacturer was edited'=>stripslashes('制造商已编辑'),
			'Marked Completed'=>stripslashes('标记为已完成'),
			'Merge this'=>stripslashes('合并这个'),
			'Message is required.'=>stripslashes('需要留言。'),
			'Mobile Devices'=>stripslashes('移动设备'),
			'Module name is invalid.'=>stripslashes('模块名称无效。'),
			'More Data'=>stripslashes('更多数据'),
			'Multiple Drawers'=>stripslashes('多抽屉'),
			'My Information'=>stripslashes('我的信息'),
			'Name'=>stripslashes('名称'),
			'Name is required.'=>stripslashes('姓名是必填项。'),
			'Name of'=>stripslashes('的名字'),
			'Need more time?'=>stripslashes('需要更多时间？'),
			'New Expense Type Added'=>stripslashes('新费用类型已添加'),
			'New Manufacturer Added'=>stripslashes('新制造商已添加'),
			'New Repair Ticket'=>stripslashes('新修理票'),
			'New Vendor Added'=>stripslashes('新供应商已添加'),
			'No users meet the criteria given'=>stripslashes('没有用户符合给定的标准'),
			'Non Taxable Total'=>stripslashes('非应税总额'),
			'Not Permitted'=>stripslashes('不允许'),
			'Note Created'=>stripslashes('注意创建'),
			'Note History'=>stripslashes('注意历史'),
			'Note information'=>stripslashes('笔记信息'),
			'Notifications'=>stripslashes('通知'),
			'Offers Email'=>stripslashes('提供电邮'),
			'Ok'=>stripslashes('好'),
			'Open Cash Drawer'=>stripslashes('打开现金抽屉'),
			'Order Created'=>stripslashes('订单已创建'),
			'Order has cancelled.'=>stripslashes('订单已取消。'),
			'Order No.'=>stripslashes('订单号。'),
			'Order Pick'=>stripslashes('订单拣货'),
			'Orders Print'=>stripslashes('订单打印'),
			'Our Notes'=>stripslashes('我们的笔记'),
			'OUR SERVICES'=>stripslashes('我们的服务'),
			'OUR SUPPORT'=>stripslashes('我们的支持'),
			'P&L Statement'=>stripslashes('损益表'),
			'Password'=>stripslashes('密码'),
			'Payment'=>stripslashes('付款'),
			'Payment Details'=>stripslashes('付款详情'),
			'Payment Due'=>stripslashes('工资税'),
			'Payment Options'=>stripslashes('付款方式'),
			'Payment Receipt'=>stripslashes('付款收据'),
			'Payments Received by Type'=>stripslashes('按类型收到的付款'),
			'Petty Cash'=>stripslashes('小钱'),
			'Petty cash was edited'=>stripslashes('小额现金被编辑'),
			'Phone No'=>stripslashes('电话号码'),
			'Please check your account. Go to website module and copy new API code and update current API code.'=>stripslashes('请检查您的帐户。转到网站模块并复制新的 API 代码并更新当前的 API 代码。'),
			'Please click Buy Now above to continue to use your accounts.'=>stripslashes('请点击上面的“立即购买”继续使用您的帐户。'),
			'Please give change amount of'=>stripslashes('请给出更改金额'),
			'Please setup SMS messaging.'=>stripslashes('请设置短信。'),
			'Please try again, thank you.'=>stripslashes('请再试一次，谢谢。'),
			'Please update your payment method'=>stripslashes('请更新您的付款方式'),
			'PO Number'=>stripslashes('订单号'),
			'PO Setup'=>stripslashes('PO设置'),
			'PO was re-open.'=>stripslashes('PO重新开放。'),
			'Popup Message'=>stripslashes('弹出消息'),
			'Possible file upload attack. Filename:'=>stripslashes('可能的文件上传攻击。文件名：'),
			'Print Order'=>stripslashes('打印订单'),
			'Problem'=>stripslashes('问题'),
			'Product'=>stripslashes('产品'),
			'Product Barcode Print'=>stripslashes('产品条形码打印'),
			'Product Created'=>stripslashes('产品创建'),
			'Product Information'=>stripslashes('产品信息'),
			'Product price has been added'=>stripslashes('产品价格已添加'),
			'Product Unarchive'=>stripslashes('产品取消归档'),
			'Product was edited'=>stripslashes('产品已编辑'),
			'Products'=>stripslashes('制品'),
			'Products Report'=>stripslashes('产品报告'),
			'Property was edited'=>stripslashes('财产被编辑'),
			'Purchase Order'=>stripslashes('采购订单'),
			'Purchase Order Created'=>stripslashes('已创建采购订单'),
			'Purchase Orders'=>stripslashes('订单'),
			'QTY'=>stripslashes('数量'),
			'Receipt Printer & Cash Drawer'=>stripslashes('收据打印机和现金抽屉'),
			'Refund Items'=>stripslashes('退款项目'),
			'Remove'=>stripslashes('去掉'),
			'Remove IMEI Number'=>stripslashes('删除IMEI号码'),
			'Remove product'=>stripslashes('移除产品'),
			'Remove Serial Number'=>stripslashes('删除序列号'),
			'Remove Serial/IMEI Number'=>stripslashes('删除Serial / IMEI号码'),
			'Remove Time Clock'=>stripslashes('删除时钟'),
			'REMOVED FROM INVENTORY'=>stripslashes('从库存中删除'),
			'Repair Appointment'=>stripslashes('修理预约'),
			'Repair Created'=>stripslashes('修复创建'),
			'Repair Information'=>stripslashes('维修信息'),
			'Repair problems was edited'=>stripslashes('修复问题已被编辑'),
			'REPAIR SERVICES'=>stripslashes('维修'),
			'Repair services you can trust.  From small issues to major repairs our trained technicians are ready to assist.  We are looking forward to serving you!'=>stripslashes('您可以信赖的维修服务。从小问题到大修，我们训练有素的技术人员随时准备提供帮助。我们期待为您服务！'),
			'Repair Summary of Ticket'=>stripslashes('维修票据摘要'),
			'Repair Ticket'=>stripslashes('修理机票'),
			'Repair Tickets Created'=>stripslashes('修复故障单'),
			'Repairs'=>stripslashes('维修'),
			'Repairs by problems'=>stripslashes('由问题修理'),
			'Repairs by status'=>stripslashes('按地位进行维修'),
			'Repairs Reports'=>stripslashes('修理报告'),
			'Request a Quote'=>stripslashes('请求报价'),
			'Restrict Access'=>stripslashes('限制访问'),
			'Return'=>stripslashes('返回'),
			'Return Purchase Order'=>stripslashes('退货采购订单'),
			's COMPANYNAME'=>stripslashes('s COMPANYNAME'),
			's COMPANYNAME accounts, you have just been granted access.'=>stripslashes('您的COMPANYNAME帐户，您刚被授予访问权限。'),
			'SALES'=>stripslashes('销售'),
			'Sales Ampar'=>stripslashes('销售安帕尔'),
			'Sales by Category'=>stripslashes('按类别销售'),
			'Sales by Customer'=>stripslashes('客户销售'),
			'Sales by Date'=>stripslashes('按日期销售'),
			'Sales by Product'=>stripslashes('按产品销售'),
			'Sales by Sales Person'=>stripslashes('销售人员的销售额'),
			'Sales by Tax'=>stripslashes('按税收销售'),
			'Sales by Technician'=>stripslashes('技术人员的销售'),
			'Sales Invoice # s'=>stripslashes('销售发票#s'),
			'Sales invoice archived'=>stripslashes('销售发票已存档'),
			'Sales Invoice Created'=>stripslashes('销售发票已创建'),
			'Sales Invoices'=>stripslashes('销售发票'),
			'Sales Invoices Print-Invoice #: s'=>stripslashes('销售发票打印发票＃：s'),
			'Sales Person'=>stripslashes('销售人员'),
			'Sales Receipt'=>stripslashes('销售收据'),
			'Sales Register'=>stripslashes('销售登记'),
			'Sales Reports'=>stripslashes('销售报告'),
			'Services'=>stripslashes('服务'),
			'Setup Users'=>stripslashes('设置用户'),
			'Shipping Cost'=>stripslashes('运输费'),
			'Signature'=>stripslashes('签名'),
			'Smart Phone'=>stripslashes('手机'),
			'SMS sent to'=>stripslashes('短信发送至'),
			'Sorry! system could not find any Cash Register.'=>stripslashes('对不起！系统找不到任何收银机。'),
			'Square Credit Card Processing'=>stripslashes('方形信用卡处理'),
			'State / Province'=>stripslashes('州/省'),
			'Status'=>stripslashes('状态'),
			'Stock Take Information'=>stripslashes('盘点信息'),
			'Store login address'=>stripslashes('存储登录地址'),
			'SUBSCRIBE NOW!'=>stripslashes('现在订阅！'),
			'Subtotal'=>stripslashes('小计'),
			'summary of t'=>stripslashes('t的总结'),
			'Supplier Created'=>stripslashes('供应商创建'),
			'Supplier Info'=>stripslashes('供应商信息'),
			'Suppliers Information'=>stripslashes('供应商信息'),
			'SUSPENDED'=>stripslashes('悬'),
			'System'=>stripslashes('系统'),
			'Tax'=>stripslashes('税'),
			'Tax archived'=>stripslashes('纳税归档'),
			'Tax Created'=>stripslashes('创建税'),
			'Tax has been changed.'=>stripslashes('税项已更改。'),
			'Taxable Total'=>stripslashes('应纳税总额'),
			'Taxes'=>stripslashes('税'),
			'Technician'=>stripslashes('技术员'),
			'Telephone'=>stripslashes('电话'),
			'Thank you for requesting a quote.'=>stripslashes('感谢您索取报价。'),
			'Thank you for requesting an appointment.'=>stripslashes('感谢您要求预约。'),
			'Thanks again'=>stripslashes('再次感谢'),
			'The COMPANYNAME Team'=>stripslashes('COMPANYNAME团队'),
			'There is no data found'=>stripslashes('没有找到数据'),
			'There is no form submit'=>stripslashes('没有表格提交'),
			'There is no ticket found.'=>stripslashes('没有找到票。'),
			'This account has been CANCELED. To reopen click'=>stripslashes('此帐户已取消。要重新打开点击'),
			'this date and time already booked. try again with different date and time.'=>stripslashes('此日期和时间已预订。用不同的日期和时间再试一次。'),
			'This invoice has been completed'=>stripslashes('此发票已完成'),
			'This name and email already exist. Try again with a different name/email.'=>stripslashes('此名称和电子邮件已存在。使用不同的名称/电子邮件重试。'),
			'This ticket was created from Ticket #'=>stripslashes('这张票是从Ticket＃创建的'),
			'Ticket'=>stripslashes('票'),
			'Ticket Info'=>stripslashes('门票信息'),
			'Ticket Number is required.'=>stripslashes('票号是必填项。'),
			'Time'=>stripslashes('时间'),
			'Time Clock Information'=>stripslashes('时钟信息'),
			'Time Clock Manager'=>stripslashes('时钟经理'),
			'Time clock was edited.'=>stripslashes('时钟已编辑。'),
			'Time Report'=>stripslashes('时间报告'),
			'To'=>stripslashes('至'),
			'to'=>stripslashes('至'),
			'Total'=>stripslashes('总'),
			'Total amount due by'=>stripslashes('应付总额'),
			'Transfer'=>stripslashes('转让'),
			'Trial Period Ended'=>stripslashes('试用期结束'),
			'Unarchive'=>stripslashes('取消归档'),
			'Unit Price'=>stripslashes('单价'),
			'Updated End of Day for'=>stripslashes('更新了结束的一天'),
			'User'=>stripslashes('用户'),
			'User archived'=>stripslashes('用户已存档'),
			'User Created'=>stripslashes('用户创建'),
			'User Logged In'=>stripslashes('用户已登录'),
			'User was edited'=>stripslashes('用户已被编辑'),
			'Username'=>stripslashes('用户名'),
			'Vendor was edited'=>stripslashes('供应商已被编辑'),
			'View Invoice'=>stripslashes('查看发票'),
			'View Product Details'=>stripslashes('查看产品详情'),
			'We are here to assist you. Our confident team is available and ready to answer your questions and exceed your expectations. Contact us today.'=>stripslashes('我们在这里为您提供帮助。我们自信的团队随时准备回答您的问题并超越您的期望。今天就联系我们。'),
			'we have received your request for an appointment.'=>stripslashes('我们已收到您的预约请求。'),
			'We will be in touch very soon, thank you.'=>stripslashes('我们会尽快与您联系，谢谢。'),
			'We will reply as soon as possible.'=>stripslashes('我们会尽快回复。'),
			'Website'=>stripslashes('网站'),
			'Welcome to'=>stripslashes('欢迎来到'),
			'What needs to be fixed'=>stripslashes('什么需要修复'),
			'You are not admin User'=>stripslashes('你不是管理员用户'),
			'You are not the user that created this account. To subscribe please have the account creator log in and click this button'=>stripslashes('您不是创建此帐户的用户。要订阅，请让帐户创建者登录并单击此按钮'),
			'You could not remove SMS Integration information before accessing Your SMS to COMPANYNAME.'=>stripslashes('在访问您的SMS到COMPANYNAME之前，您无法删除SMS集成信息。'),
			'you have only'=>stripslashes('你只有'),
			'You wrote:'=>stripslashes('你写了：'),
			'Your access to'=>stripslashes('您的访问权限'),
			'Your account is invalid.'=>stripslashes('您的帐户无效。'),
			'Your accounts has been suspended. This is most likely do to a billing issue. Please contact us by clicking the help ? at the top right of any page on this application. Thank you'=>stripslashes('您的帐户已被暂停。这很可能是针对结算问题。请点击帮助与我们联系？在此应用程序的任何页面的右上角。谢谢'),
			'Your IP is not allowed to access this software. Please contact with admin.'=>stripslashes('您的IP不允许访问此软件。请联系管理员。'),
			'Your login details are below'=>stripslashes('您的登录详细信息如下'),
			'Your message could not send.'=>stripslashes('您的消息无法发送。'),
			'Your message has been successfully sent.'=>stripslashes('您的留言已成功发送。'),
			'Your Quote has been successfully sent.'=>stripslashes('您的报价已成功发送。'),
			'Your trial accounts has expired.'=>stripslashes('您的试用帐户已过期。'),
			'Zip/Postal Code'=>stripslashes('邮编/邮政编码'),
		);
		if(array_key_exists($index, $languageData)){
			return $languageData[$index];
		}
		return false;
	}
}
?>