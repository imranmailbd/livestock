<?php
class Russian{
	public function index($index){
		$languageData = array(
			'// Invoice Information //'=>stripslashes('// Информация о счете //'),
			'Account Status:'=>stripslashes('Статус аккаунта:'),
			'Accounts Information'=>stripslashes('Информация об учетных записях'),
			'Accounts Receivables'=>stripslashes('Дебиторская задолженность'),
			'Accounts Receivables Details'=>stripslashes('Сведения о дебиторской задолженности'),
			'Accounts Setup'=>stripslashes('Настройка учетных записей'),
			'Accounts State:'=>stripslashes('Состояние счетов:'),
			'Accounts SUSPENDED'=>stripslashes('Учетные записи ПОДОЗРЕВАЕМЫЕ'),
			'Activity Report'=>stripslashes('Отчет о деятельности'),
			'Add Customer Information'=>stripslashes('Добавить информацию о клиенте'),
			'Add Order'=>stripslashes('Добавить заказ'),
			'Address'=>stripslashes('Адрес'),
			'Address Line 1'=>stripslashes('Адресная строка 1'),
			'Address Line 2'=>stripslashes('Адресная строка 2'),
			'Admin of'=>stripslashes('Администратор'),
			'All pages footer'=>stripslashes('Все нижние колонтитулы страниц'),
			'All pages header'=>stripslashes('Все заголовки страниц'),
			'Amount Due'=>stripslashes('Сумма долга'),
			'and check things out. If you have any questions feel free to'=>stripslashes('и проверить все. Если у вас есть вопросы,'),
			'Appointment Calendar'=>stripslashes('Календарь встреч'),
			'Archive Data'=>stripslashes('Архивные данные'),
			'Archived'=>stripslashes('В архиве'),
			'AT COMPETITIVE PRICES'=>stripslashes('НА КОНКУРЕНТНЫХ ЦЕНАХ'),
			'Barcode Labels'=>stripslashes('Штрих-коды'),
			'Based on'=>stripslashes('На основе'),
			'Bill To'=>stripslashes('Плательщик'),
			'Bin Location'=>stripslashes('Местонахождение бина'),
			'Brand and model of device'=>stripslashes('Марка и модель устройства'),
			'Brand Model'=>stripslashes('Модель бренда'),
			'Brand/Model/More Details:'=>stripslashes('Марка / Модель / Подробнее:'),
			'By'=>stripslashes('По'),
			'Carriers'=>stripslashes('Носители'),
			'Cash Register'=>stripslashes('Кассовый аппарат'),
			'Category'=>stripslashes('категория'),
			'Category archived'=>stripslashes('Категория заархивирована'),
			'Category Created'=>stripslashes('Категория создана'),
			'Category was edited'=>stripslashes('Категория была отредактирована'),
			'CELL PHONE REPAIR'=>stripslashes('СОТОВЫЙ ТЕЛЕФОН РЕМОНТ'),
			'Change Inventory Transfer'=>stripslashes('Изменить перенос инвентаря'),
			'Changed Status to'=>stripslashes('Изменено состояние'),
			'Check Repair Status'=>stripslashes('Проверить статус ремонта'),
			'Check Repair Status Online'=>stripslashes('Проверить статус ремонта онлайн'),
			'City / Town'=>stripslashes('Город / место'),
			'Clicked Subscribe'=>stripslashes('Нажмите «Подписаться»'),
			'Clock In Date'=>stripslashes('Часы в дате'),
			'Clock In Time'=>stripslashes('Часы во времени'),
			'Clock Out Date'=>stripslashes('Дата выезда'),
			'Clock Out Time'=>stripslashes('Время выключения'),
			'Commission Details'=>stripslashes('Информация о комиссии'),
			'Commission Report'=>stripslashes('Отчет комиссии'),
			'Company'=>stripslashes('Компания'),
			'Company customer service email address could not found.'=>stripslashes('Адрес электронной почты службы поддержки клиентов компании не найден.'),
			'Company Information'=>stripslashes('Информация о компании'),
			'COMPANYNAME Software is used to manage store activities like POS, Repair Ticketing, Inventory and Staff Management, and more.'=>stripslashes('Программное обеспечение COMPANYNAME используется для управления такими видами деятельности, как POS, ремонт билетов, инвентаризация и управление персоналом, и многое другое.'),
			'Conditions'=>stripslashes('условия'),
			'Confirm Purchase Order'=>stripslashes('Подтвердить заказ на покупку'),
			'Contact Us'=>stripslashes('Свяжитесь с нами'),
			'Cost'=>stripslashes('Стоимость'),
			'Count on us for your cell phone purchase and service needs.  Reliable phones, quality repair services, unlocking, accessories, and more.'=>stripslashes('Подсчитайте нас за ваши покупки и услуги сотового телефона. Надежные телефоны, качественные услуги по ремонту, разблокировка, аксессуары и многое другое.'),
			'Counting Cash Til'=>stripslashes('Подсчет наличных денег'),
			'Country'=>stripslashes('Страна'),
			'Create Inventory Transfer'=>stripslashes('Создание переноса инвентаря'),
			'Create Purchase Order'=>stripslashes('Создать заказ на поставку'),
			'Create Stock Take'=>stripslashes('Создать учетную запись'),
			'Custom Fields'=>stripslashes('Настраиваемые поля'),
			'Custom Statuses'=>stripslashes('Пользовательские статусы'),
			'Customer'=>stripslashes('Клиент'),
			'Customer Created'=>stripslashes('Созданный клиент'),
			'Customer Information'=>stripslashes('Информация для покупателей'),
			'Customer Orders'=>stripslashes('Заказы клиентов'),
			'Customer type was edited'=>stripslashes('Изменен тип клиента'),
			'Customers'=>stripslashes('Клиенты'),
			'Customers archived successfully.'=>stripslashes('Клиенты архивируются успешно.'),
			'Dashboard'=>stripslashes('Панель приборов'),
			'Date field is required.'=>stripslashes('Поле даты требуется.'),
			'Date time to meet'=>stripslashes('Дата время встречи'),
			'Day of the Week'=>stripslashes('День недели'),
			'Days Free Trial'=>stripslashes('Бесплатная пробная версия'),
			'Days Remaining'=>stripslashes('Осталось дней'),
			'days until your account will no longer operate. Please update your payment method.'=>stripslashes('дней, пока ваш аккаунт больше не будет работать. Обновите свой способ оплаты.'),
			'Dear'=>stripslashes('Уважаемые'),
			'DELETE a PAYMENT from'=>stripslashes('УДАЛИТЬ ПЛАТЕЖ из'),
			'Description'=>stripslashes('Описание'),
			'Device Information'=>stripslashes('Информация об устройстве'),
			'Devices Dashboard'=>stripslashes('Приборная панель приборов'),
			'Devices Inventory'=>stripslashes('Инвентаризация устройств'),
			'Display Add Customer Information'=>stripslashes('Дисплей Добавить информацию о клиенте'),
			'Due Date'=>stripslashes('Срок'),
			'E-mail'=>stripslashes('Эл. почта'),
			'Edit Order'=>stripslashes('Изменить порядок'),
			'Email'=>stripslashes('Эл. адрес'),
			'Email Address'=>stripslashes('Адрес электронной почты'),
			'Email is required.'=>stripslashes('Электронная почта обязательна.'),
			'Email sent to'=>stripslashes('Е-мейл отправлен'),
			'Employee archived successfully.'=>stripslashes('Сотрудник успешно заархивирован.'),
			'Employee was edited'=>stripslashes('Сотрудник был изменен'),
			'End of Day Closed'=>stripslashes('Закрытие дня закрытия'),
			'End of Day Report'=>stripslashes('Отчет о конце дня'),
			'End of Day Updated.'=>stripslashes('Окончание дня.'),
			'End of the day Starting Balance added successfully.'=>stripslashes('Конец дня Начальный баланс добавлен успешно.'),
			'End of the day Starting Balance updated successfully.'=>stripslashes('Окончание дня Начальный баланс обновлен успешно.'),
			'error occurred while adding new customer! please try again.'=>stripslashes('произошла ошибка при добавлении нового клиента! пожалуйста, попробуйте снова.'),
			'error occurred while booking new appointment! please try again.'=>stripslashes('произошла ошибка при бронировании новой встречи! пожалуйста, попробуйте снова.'),
			'Estimate'=>stripslashes('Оценить'),
			'Expense type was edited'=>stripslashes('Тип расхода редактировался'),
			'Expenses Details'=>stripslashes('Информация о расходах'),
			'Export Data'=>stripslashes('Экспорт данных'),
			'Fax'=>stripslashes('факс'),
			'Field is required.'=>stripslashes('Поле, обязательное для заполнения.'),
			'First Name is required.'=>stripslashes('Требуется имя.'),
			'Form'=>stripslashes('форма'),
			'Form Fields'=>stripslashes('Поля формы'),
			'Form was edited'=>stripslashes('Форма была отредактирована'),
			'Forms'=>stripslashes('формы'),
			'Forms data added successfully.'=>stripslashes('Формированные данные успешно добавлены.'),
			'from'=>stripslashes('из'),
			'FULL SERVICE'=>stripslashes('ПОЛНЫЙ КОМПЛЕКС УСЛУГ'),
			'General'=>stripslashes('Генеральная'),
			'Grand Total'=>stripslashes('Общая сумма'),
			'Hardware Info'=>stripslashes('Информация об оборудовании'),
			'Help'=>stripslashes('Помощь'),
			'HERE FOR YOU'=>stripslashes('ЗДЕСЬ ДЛЯ ВАС'),
			'Hi'=>stripslashes('Здравствуй'),
			'Home'=>stripslashes('Главная'),
			'Home Page Body'=>stripslashes('Главная Тело'),
			'Hour field is required.'=>stripslashes('Поле «Час» требуется.'),
			'IMEI'=>stripslashes('IMEI'),
			'IMEI Created'=>stripslashes('IMEI Created'),
			'IMEI/Serial No.'=>stripslashes('IMEI / серийный номер'),
			'Import Customers'=>stripslashes('Импорт клиентов'),
			'Import Products'=>stripslashes('Импортные продукты'),
			'Info'=>stripslashes('Информация'),
			'Inventory Purchased'=>stripslashes('Покупка инвентаря'),
			'Inventory Reports'=>stripslashes('Отчеты о запасах'),
			'Inventory Transfer'=>stripslashes('Передача инвентаря'),
			'Inventory Transfer Print-po #:'=>stripslashes('Перенос инвентаря Print-po #:'),
			'Inventory Value'=>stripslashes('Стоимость инвентаря'),
			'Inventory ValueN'=>stripslashes('Стоимость инвентаряN'),
			'Invoice #: s'=>stripslashes('Счет-фактура: s'),
			'Invoice Setup'=>stripslashes('Настройка счета'),
			'Invoices Report'=>stripslashes('Отчет о счетах'),
			'Language information removed successfully.'=>stripslashes('Информация о языке удалена успешно.'),
			'Languages'=>stripslashes('Языки'),
			'Locations'=>stripslashes('Места'),
			'Login'=>stripslashes('Авторизоваться'),
			'Login Message'=>stripslashes('Сообщение для входа'),
			'Logo'=>stripslashes('логотип'),
			'Manage Accounts'=>stripslashes('Управление учетными записями'),
			'Manage Brand Model'=>stripslashes('Управление моделью бренда'),
			'Manage Categories'=>stripslashes('Управление категориями'),
			'Manage Commissions'=>stripslashes('Управление комиссиями'),
			'Manage CRM'=>stripslashes('Управление CRM'),
			'Manage Customer Type'=>stripslashes('Управление типом клиента'),
			'Manage Customers'=>stripslashes('Управление клиентами'),
			'Manage End of Day'=>stripslashes('Управление окончанием дня'),
			'Manage EU GDPR'=>stripslashes('Управление ВВП'),
			'Manage Expense Type'=>stripslashes('Управление ценой'),
			'Manage Expenses'=>stripslashes('Управление расходами'),
			'Manage Label Printer'=>stripslashes('Управление принтером этикеток'),
			'Manage Manufacturer'=>stripslashes('Управление производителем'),
			'Manage Products'=>stripslashes('Управление продуктами'),
			'Manage Repair Problem'=>stripslashes('Управление проблемой ремонта'),
			'Manage SMS Messaging'=>stripslashes('Управление SMS-сообщениями'),
			'Manage Suppliers'=>stripslashes('Управление поставщиками'),
			'Manage Taxes'=>stripslashes('Управление налогами'),
			'Manage Vendors'=>stripslashes('Управление поставщиками'),
			'Manage Website'=>stripslashes('Управление сайтом'),
			'Manufacturer'=>stripslashes('производитель'),
			'Manufacturer archived'=>stripslashes('Производитель в архиве'),
			'Manufacturer was edited'=>stripslashes('Производитель был отредактирован'),
			'Marked Completed'=>stripslashes('Отмечено как выполненное'),
			'Merge this'=>stripslashes('Объединить это'),
			'Message is required.'=>stripslashes('Требуется сообщение.'),
			'Live Stocks'=>stripslashes('Мобильные устройства'),
			'Module name is invalid.'=>stripslashes('Недопустимое имя модуля.'),
			'More Data'=>stripslashes('Дополнительные данные'),
			'Multiple Drawers'=>stripslashes('Несколько ящиков'),
			'My Information'=>stripslashes('Моя информация'),
			'Name'=>stripslashes('имя'),
			'Name is required.'=>stripslashes('Требуется имя.'),
			'Name of'=>stripslashes('Имя'),
			'Need more time?'=>stripslashes('Нужно больше времени?'),
			'New Expense Type Added'=>stripslashes('Добавлен новый тип расхода'),
			'New Manufacturer Added'=>stripslashes('Новый изготовитель добавлен'),
			'New Repair Ticket'=>stripslashes('Новый ремонтный билет'),
			'New Vendor Added'=>stripslashes('Добавлен новый поставщик'),
			'No users meet the criteria given'=>stripslashes('Ни один пользователь не отвечает критериям, указанным'),
			'Non Taxable Total'=>stripslashes('Non Taxable Total'),
			'Not Permitted'=>stripslashes('Не разрешено'),
			'Note Created'=>stripslashes('Примечание.'),
			'Note History'=>stripslashes('История примечаний'),
			'Note information'=>stripslashes('Обратите внимание на информацию'),
			'Notifications'=>stripslashes('Уведомления'),
			'Offers Email'=>stripslashes('Предложения по электронной почте'),
			'Ok'=>stripslashes('ОК'),
			'Open Cash Drawer'=>stripslashes('Открытый денежный ящик'),
			'Order Created'=>stripslashes('Созданный заказ'),
			'Order has cancelled.'=>stripslashes('Заказ отменен.'),
			'Order No.'=>stripslashes('№ заказа.'),
			'Order Pick'=>stripslashes('Выбор заказа'),
			'Orders Print'=>stripslashes('Распечатать заказы'),
			'Our Notes'=>stripslashes('Наши заметки'),
			'OUR SERVICES'=>stripslashes('НАШИ УСЛУГИ'),
			'OUR SUPPORT'=>stripslashes('НАША ПОДДЕРЖКА'),
			'P&L Statement'=>stripslashes('Отчет о прибылях и убытках'),
			'Password'=>stripslashes('пароль'),
			'Payment'=>stripslashes('Оплата'),
			'Payment Details'=>stripslashes('Детали платежа'),
			'Payment Due'=>stripslashes('Заплатить до'),
			'Payment Options'=>stripslashes('Варианты оплаты'),
			'Payment Receipt'=>stripslashes('Квитанция об оплате'),
			'Payments Received by Type'=>stripslashes('Платежи, полученные по типу'),
			'Petty Cash'=>stripslashes('Мелкие наличные деньги'),
			'Petty cash was edited'=>stripslashes('Изменена мелкая наличность'),
			'Phone No'=>stripslashes('Телефонный номер'),
			'Please check your account. Go to website module and copy new API code and update current API code.'=>stripslashes('Пожалуйста, проверьте свой аккаунт. Перейдите в модуль веб-сайта и скопируйте новый код API и обновите текущий код API.'),
			'Please click Buy Now above to continue to use your accounts.'=>stripslashes('Чтобы продолжить использовать свои аккаунты, нажмите «Купить сейчас» выше.'),
			'Please give change amount of'=>stripslashes('Пожалуйста, дайте сумму изменения'),
			'Please setup SMS messaging.'=>stripslashes('Пожалуйста, настройте SMS-сообщение.'),
			'Please try again, thank you.'=>stripslashes('Пожалуйста, попробуйте еще раз, спасибо.'),
			'Please update your payment method'=>stripslashes('Обновите способ оплаты'),
			'PO Number'=>stripslashes('Номер заказа'),
			'PO Setup'=>stripslashes('Настройка PO'),
			'PO was re-open.'=>stripslashes('PO был вновь открыт.'),
			'Popup Message'=>stripslashes('Всплывающее сообщение'),
			'Possible file upload attack. Filename:'=>stripslashes('Возможная атака загрузки файлов. Имя файла:'),
			'Print Order'=>stripslashes('Заказ печати'),
			'Problem'=>stripslashes('проблема'),
			'Product'=>stripslashes('Продукт'),
			'Product Barcode Print'=>stripslashes('Печать штрих-кода продукта'),
			'Product Created'=>stripslashes('Созданный продукт'),
			'Product Information'=>stripslashes('Информация о товаре'),
			'Product price has been added'=>stripslashes('Цена продукта добавлена'),
			'Product Unarchive'=>stripslashes('Разархивировать продукт'),
			'Product was edited'=>stripslashes('Продукт был отредактирован'),
			'Products'=>stripslashes('Продукты'),
			'Products Report'=>stripslashes('Отчет о продукции'),
			'Property was edited'=>stripslashes('Недвижимость была отредактирована'),
			'Purchase Order'=>stripslashes('Заказ на покупку'),
			'Purchase Order Created'=>stripslashes('Созданный заказ на поставку'),
			'Purchase Orders'=>stripslashes('Заказы'),
			'QTY'=>stripslashes('КОЛ'),
			'Receipt Printer & Cash Drawer'=>stripslashes('Принтер чеков и кассовый ящик'),
			'Refund Items'=>stripslashes('Возврат товара'),
			'Remove'=>stripslashes('Удалить'),
			'Remove IMEI Number'=>stripslashes('Удалить номер IMEI'),
			'Remove product'=>stripslashes('Удалить товар'),
			'Remove Serial Number'=>stripslashes('Удалить серийный номер'),
			'Remove Serial/IMEI Number'=>stripslashes('Удалить серийный номер / IMEI'),
			'Remove Time Clock'=>stripslashes('Удалить время'),
			'REMOVED FROM INVENTORY'=>stripslashes('УДАЛЕНЫ ИЗ ИНВЕНТАРИЗАЦИИ'),
			'Repair Appointment'=>stripslashes('Назначение ремонта'),
			'Repair Created'=>stripslashes('Восстановленный ремонт'),
			'Repair Information'=>stripslashes('Информация о ремонте'),
			'Repair problems was edited'=>stripslashes('Исправлены проблемы с ремонтом'),
			'REPAIR SERVICES'=>stripslashes('РЕМОНТНЫЕ УСЛУГИ'),
			'Repair services you can trust.  From small issues to major repairs our trained technicians are ready to assist.  We are looking forward to serving you!'=>stripslashes('Услуги по ремонту, которым вы можете доверять. От небольших вопросов до капитального ремонта наши подготовленные специалисты готовы помочь. Мы с нетерпением ждем вас!'),
			'Repair Summary of Ticket'=>stripslashes('Резюме ремонта билета'),
			'Repair Ticket'=>stripslashes('Ремонтный билет'),
			'Repair Tickets Created'=>stripslashes('Ремонтные билеты созданы'),
			'Repairs'=>stripslashes('Ремонт'),
			'Repairs by problems'=>stripslashes('Ремонт по проблемам'),
			'Repairs by status'=>stripslashes('Ремонт по статусу'),
			'Repairs Reports'=>stripslashes('Ремонт отчетов'),
			'Request a Quote'=>stripslashes('Запрос цитаты'),
			'Restrict Access'=>stripslashes('Ограничить доступ'),
			'Return'=>stripslashes('Вернуть'),
			'Return Purchase Order'=>stripslashes('Возврат заказа на поставку'),
			's COMPANYNAME'=>stripslashes('s COMPANYNAME'),
			's COMPANYNAME accounts, you have just been granted access.'=>stripslashes('s COMPANYNAME, вам только что был предоставлен доступ.'),
			'SALES'=>stripslashes('ПРОДАЖИ'),
			'Sales Ampar'=>stripslashes('Продажи Ампар'),
			'Sales by Category'=>stripslashes('Продажа по категориям'),
			'Sales by Customer'=>stripslashes('Продажи Клиентом'),
			'Sales by Date'=>stripslashes('Продажи по дате'),
			'Sales by Product'=>stripslashes('Продажи по продуктам'),
			'Sales by Sales Person'=>stripslashes('Продажа продавцом'),
			'Sales by Tax'=>stripslashes('Продажа по налогу'),
			'Sales by Technician'=>stripslashes('Продажа техники'),
			'Sales Invoice # s'=>stripslashes('Счет-фактура #'),
			'Sales invoice archived'=>stripslashes('Сбытый счет-фактура'),
			'Sales Invoice Created'=>stripslashes('Создан счет-фактура'),
			'Sales Invoices'=>stripslashes('Счета-фактуры'),
			'Sales Invoices Print-Invoice #: s'=>stripslashes('Счета-фактуры на продажу Счета-фактуры №: s'),
			'Sales Person'=>stripslashes('Продавец'),
			'Sales Receipt'=>stripslashes('Квитанция продажи'),
			'Sales Register'=>stripslashes('Реестр продаж'),
			'Sales Reports'=>stripslashes('Отчеты по продажам'),
			'Services'=>stripslashes('Сервисы'),
			'Setup Users'=>stripslashes('Пользователи установки'),
			'Shipping Cost'=>stripslashes('Стоимость доставки'),
			'Signature'=>stripslashes('Подпись'),
			'Smart Phone'=>stripslashes('Смартфон'),
			'SMS sent to'=>stripslashes('SMS отправлено на'),
			'Sorry! system could not find any Cash Register.'=>stripslashes('Извиняюсь! система не может найти кассовый аппарат.'),
			'Square Credit Card Processing'=>stripslashes('Обработка квадратных кредитных карт'),
			'State / Province'=>stripslashes('Государство / Провинция'),
			'Status'=>stripslashes('Положение дел'),
			'Stock Take Information'=>stripslashes('Информация о запасах'),
			'Store login address'=>stripslashes('Адрес для входа в магазин'),
			'SUBSCRIBE NOW!'=>stripslashes('ПОДПИШИСЬ СЕЙЧАС!'),
			'Subtotal'=>stripslashes('Промежуточный итог'),
			'summary of t'=>stripslashes('резюме t'),
			'Supplier Created'=>stripslashes('Созданный поставщик'),
			'Supplier Info'=>stripslashes('Информация о поставщике'),
			'Suppliers Information'=>stripslashes('Информация о поставщиках'),
			'SUSPENDED'=>stripslashes('ПОДВЕСНЫЕ'),
			'System'=>stripslashes('система'),
			'Tax'=>stripslashes('налог'),
			'Tax archived'=>stripslashes('Налоговый архив'),
			'Tax Created'=>stripslashes('Налог создан'),
			'Tax has been changed.'=>stripslashes('Налог был изменен.'),
			'Taxable Total'=>stripslashes('Налогооблагаемая сумма'),
			'Taxes'=>stripslashes('налоги'),
			'Technician'=>stripslashes('техник'),
			'Telephone'=>stripslashes('телефон'),
			'Thank you for requesting a quote.'=>stripslashes('Благодарим Вас за запрос цитаты.'),
			'Thank you for requesting an appointment.'=>stripslashes('Благодарим вас за запрос о встрече.'),
			'Thanks again'=>stripslashes('еще раз спасибо'),
			'The COMPANYNAME Team'=>stripslashes('Команда COMPANYNAME'),
			'There is no data found'=>stripslashes('Данных не найдено'),
			'There is no form submit'=>stripslashes('Нет формы отправить'),
			'There is no ticket found.'=>stripslashes('Билет не найден.'),
			'This account has been CANCELED. To reopen click'=>stripslashes('Эта учетная запись была ОТМЕНА. Чтобы снова открыть клик'),
			'this date and time already booked. try again with different date and time.'=>stripslashes('эта дата и время уже забронированы. попробуйте еще раз с другой датой и временем.'),
			'This invoice has been completed'=>stripslashes('Этот счет был завершен'),
			'This name and email already exist. Try again with a different name/email.'=>stripslashes('Это имя и адрес электронной почты уже существуют. Попробуйте еще раз с другим именем/электронной почтой.'),
			'This ticket was created from Ticket #'=>stripslashes('Этот билет был создан из Ticket #'),
			'Ticket'=>stripslashes('Билет'),
			'Ticket Info'=>stripslashes('Информация о билетах'),
			'Ticket Number is required.'=>stripslashes('Требуется номер билета.'),
			'Time'=>stripslashes('Время'),
			'Time Clock Information'=>stripslashes('Информация о часах'),
			'Time Clock Manager'=>stripslashes('Менеджер часовых поясов'),
			'Time clock was edited.'=>stripslashes('Временные часы были отредактированы.'),
			'Time Report'=>stripslashes('Отчет о времени'),
			'To'=>stripslashes('к'),
			'to'=>stripslashes('в'),
			'Total'=>stripslashes('Всего'),
			'Total amount due by'=>stripslashes('Общая сумма, причитающаяся'),
			'Transfer'=>stripslashes('Перевод'),
			'Trial Period Ended'=>stripslashes('Пробный период закончился'),
			'Unarchive'=>stripslashes('Разархивировать'),
			'Unit Price'=>stripslashes('Цена за единицу'),
			'Updated End of Day for'=>stripslashes('Обновленный конец дня для'),
			'User'=>stripslashes('пользователь'),
			'User archived'=>stripslashes('Пользователь архивирован'),
			'User Created'=>stripslashes('Пользователь создал'),
			'User Logged In'=>stripslashes('Пользователь'),
			'User was edited'=>stripslashes('Пользователь был отредактирован'),
			'Username'=>stripslashes('имя пользователя'),
			'Vendor was edited'=>stripslashes('Продавец был отредактирован'),
			'View Invoice'=>stripslashes('Посмотреть счет'),
			'View Product Details'=>stripslashes('Подробнее о продукте'),
			'We are here to assist you. Our confident team is available and ready to answer your questions and exceed your expectations. Contact us today.'=>stripslashes('Мы здесь, чтобы помочь вам. Наша уверенная в себе команда доступна и готова ответить на ваши вопросы и превзойти ваши ожидания. Свяжитесь с нами сегодня.'),
			'we have received your request for an appointment.'=>stripslashes('мы получили ваш запрос на встречу.'),
			'We will be in touch very soon, thank you.'=>stripslashes('Мы свяжемся с вами в ближайшее время, спасибо.'),
			'We will reply as soon as possible.'=>stripslashes('Мы ответим как можно скорее.'),
			'Website'=>stripslashes('Веб-сайт'),
			'Welcome to'=>stripslashes('Добро пожаловать в'),
			'What needs to be fixed'=>stripslashes('Что нужно исправить'),
			'You are not admin User'=>stripslashes('Вы не администратор'),
			'You are not the user that created this account. To subscribe please have the account creator log in and click this button'=>stripslashes('Вы не пользователь, который создал эту учетную запись. Чтобы подписаться, пожалуйста, войдите в аккаунт создателя аккаунта и нажмите эту кнопку'),
			'You could not remove SMS Integration information before accessing Your SMS to COMPANYNAME.'=>stripslashes('Вы не смогли удалить информацию об интеграции с SMS, прежде чем получать доступ к своему SMS-сообщению в компанию COMPANYNAME.'),
			'you have only'=>stripslashes('у вас есть только'),
			'You wrote:'=>stripslashes('Вы написали:'),
			'Your access to'=>stripslashes('Ваш доступ к'),
			'Your account is invalid.'=>stripslashes('Ваша учетная запись недействительна.'),
			'Your accounts has been suspended. This is most likely do to a billing issue. Please contact us by clicking the help ? at the top right of any page on this application. Thank you'=>stripslashes('Ваши аккаунты заблокированы. Скорее всего, это связано с проблемой выставления счетов. Пожалуйста, свяжитесь с нами, нажав кнопку «Справка». в правом верхнем углу любой страницы этого приложения. спасибо'),
			'Your IP is not allowed to access this software. Please contact with admin.'=>stripslashes('Ваш IP-адрес не имеет доступа к этому программному обеспечению. Пожалуйста, свяжитесь с администратором.'),
			'Your login details are below'=>stripslashes('Ваши данные для входа указаны ниже'),
			'Your message could not send.'=>stripslashes('Ваше сообщение не может быть отправлено.'),
			'Your message has been successfully sent.'=>stripslashes('Ваше сообщение было успешно отправлено.'),
			'Your Quote has been successfully sent.'=>stripslashes('Ваше предложение успешно отправлено.'),
			'Your trial accounts has expired.'=>stripslashes('Срок действия пробных учетных записей истек.'),
			'Zip/Postal Code'=>stripslashes('Почтовый индекс'),
		);
		if(array_key_exists($index, $languageData)){
			return $languageData[$index];
		}
		return false;
	}
}
?>