<?php
class Italian{
	public function index($index){
		$languageData = array(
			'// Invoice Information //'=>stripslashes('// Informazioni sulla fattura //'),
			'Account Status:'=>stripslashes('Stato dell\'account:'),
			'Accounts Information'=>stripslashes('Informazioni sui conti'),
			'Accounts Receivables'=>stripslashes('Contabilità clienti'),
			'Accounts Receivables Details'=>stripslashes('Dettagli crediti clienti'),
			'Accounts Setup'=>stripslashes('Configurazione degli account'),
			'Accounts State:'=>stripslashes('Conti Stato:'),
			'Accounts SUSPENDED'=>stripslashes('Conti SOSPESI'),
			'Activity Report'=>stripslashes('Rapporto di attività'),
			'Add Customer Information'=>stripslashes('Aggiungi le informazioni sul cliente'),
			'Add Order'=>stripslashes('Aggiungi ordine'),
			'Address'=>stripslashes('Indirizzo'),
			'Address Line 1'=>stripslashes('Indirizzo Linea 1'),
			'Address Line 2'=>stripslashes('indirizzo 2'),
			'Admin of'=>stripslashes('Admin di'),
			'All pages footer'=>stripslashes('Piè di pagina di tutte le pagine'),
			'All pages header'=>stripslashes('Intestazione di tutte le pagine'),
			'Amount Due'=>stripslashes('Importo dovuto'),
			'and check things out. If you have any questions feel free to'=>stripslashes('e controlla le cose. Se avete domande, sentitevi liberi di farlo'),
			'Appointment Calendar'=>stripslashes('Calendario degli appuntamenti'),
			'Archive Data'=>stripslashes('Dati di archiviazione'),
			'Archived'=>stripslashes('Archiviato'),
			'AT COMPETITIVE PRICES'=>stripslashes('A PREZZI COMPETITIVI'),
			'Barcode Labels'=>stripslashes('Etichette di codici a barre'),
			'Based on'=>stripslashes('Basato su'),
			'Bill To'=>stripslashes('Fatturare a'),
			'Bin Location'=>stripslashes('Posizione bin'),
			'Brand and model of device'=>stripslashes('Marca e modello del dispositivo'),
			'Brand Model'=>stripslashes('Modello di marca'),
			'Brand/Model/More Details:'=>stripslashes('Marca / Modello / Altri dettagli:'),
			'By'=>stripslashes('Di'),
			'Carriers'=>stripslashes('I vettori'),
			'Cash Register'=>stripslashes('Registratore di cassa'),
			'Category'=>stripslashes('Categoria'),
			'Category archived'=>stripslashes('Categoria archiviata'),
			'Category Created'=>stripslashes('Categoria creata'),
			'Category was edited'=>stripslashes('La categoria è stata modificata'),
			'CELL PHONE REPAIR'=>stripslashes('RIPARAZIONE DEL TELEFONO CELLULARE'),
			'Change Inventory Transfer'=>stripslashes('Modifica trasferimento inventario'),
			'Changed Status to'=>stripslashes('Stato modificato a'),
			'Check Repair Status'=>stripslashes('Controlla lo stato di riparazione'),
			'Check Repair Status Online'=>stripslashes('Controlla lo stato di riparazione online'),
			'City / Town'=>stripslashes('Città / Città'),
			'Clicked Subscribe'=>stripslashes('Abbonato cliccato'),
			'Clock In Date'=>stripslashes('Orologio in data'),
			'Clock In Time'=>stripslashes('Orologio nel tempo'),
			'Clock Out Date'=>stripslashes('Data di uscita'),
			'Clock Out Time'=>stripslashes('Tempo di uscita'),
			'Commission Details'=>stripslashes('Dettagli della Commissione'),
			'Commission Report'=>stripslashes('Relazione della Commissione'),
			'Company'=>stripslashes('Azienda'),
			'Company customer service email address could not found.'=>stripslashes('Impossibile trovare l&#39;indirizzo e-mail del servizio clienti dell&#39;azienda.'),
			'Company Information'=>stripslashes('Informazioni sull\'azienda'),
			'COMPANYNAME Software is used to manage store activities like POS, Repair Ticketing, Inventory and Staff Management, and more.'=>stripslashes('COMPANYNAME Il software viene utilizzato per gestire le attività del negozio come POS, Repair Ticketing, Inventory e Staff Management e altro.'),
			'Conditions'=>stripslashes('condizioni'),
			'Confirm Purchase Order'=>stripslashes('Conferma l&#39;ordine di acquisto'),
			'Contact Us'=>stripslashes('Contattaci'),
			'Cost'=>stripslashes('Costo'),
			'Count on us for your cell phone purchase and service needs.  Reliable phones, quality repair services, unlocking, accessories, and more.'=>stripslashes('Contattateci per l\'acquisto del telefono cellulare e le esigenze di assistenza. Telefoni affidabili, servizi di riparazione di qualità, sblocco, accessori e altro.'),
			'Counting Cash Til'=>stripslashes('Conteggio dei contanti fino a'),
			'Country'=>stripslashes('Nazione'),
			'Create Inventory Transfer'=>stripslashes('Crea trasferimento di inventario'),
			'Create Purchase Order'=>stripslashes('Creare un ordine d\'acquisto'),
			'Create Stock Take'=>stripslashes('Crea Stock Take'),
			'Custom Fields'=>stripslashes('Campi personalizzati'),
			'Custom Statuses'=>stripslashes('Stati personalizzati'),
			'Customer'=>stripslashes('Cliente'),
			'Customer Created'=>stripslashes('Cliente creato'),
			'Customer Information'=>stripslashes('informazioni per il cliente'),
			'Customer Orders'=>stripslashes('Ordini del cliente'),
			'Customer type was edited'=>stripslashes('Il tipo di cliente è stato modificato'),
			'Customers'=>stripslashes('Clienti'),
			'Customers archived successfully.'=>stripslashes('I clienti sono stati archiviati con successo.'),
			'Dashboard'=>stripslashes('Cruscotto'),
			'Date field is required.'=>stripslashes('Il campo data è obbligatorio.'),
			'Date time to meet'=>stripslashes('Data e ora per incontrarsi'),
			'Day of the Week'=>stripslashes('Giorno della settimana'),
			'Days Free Trial'=>stripslashes('Giorni di prova gratuita'),
			'Days Remaining'=>stripslashes('giorni rimanenti'),
			'days until your account will no longer operate. Please update your payment method.'=>stripslashes('giorni fino a quando il tuo account non funzionerà più. Si prega di aggiornare il metodo di pagamento.'),
			'Dear'=>stripslashes('caro'),
			'DELETE a PAYMENT from'=>stripslashes('CANCELLARE un PAGAMENTO da'),
			'Description'=>stripslashes('Descrizione'),
			'Device Information'=>stripslashes('Informazioni sul dispositivo'),
			'Devices Dashboard'=>stripslashes('Dashboard dei dispositivi'),
			'Devices Inventory'=>stripslashes('Inventario dei dispositivi'),
			'Display Add Customer Information'=>stripslashes('Visualizza Aggiungi informazioni cliente'),
			'Due Date'=>stripslashes('Scadenza'),
			'E-mail'=>stripslashes('E-mail'),
			'Edit Order'=>stripslashes('Modifica ordine'),
			'Email'=>stripslashes('E-mail'),
			'Email Address'=>stripslashes('Indirizzo email'),
			'Email is required.'=>stripslashes('L&#39;e-mail è richiesta.'),
			'Email sent to'=>stripslashes('Email inviata a'),
			'Employee archived successfully.'=>stripslashes('Impiegato archiviato correttamente.'),
			'Employee was edited'=>stripslashes('Il dipendente è stato modificato'),
			'End of Day Closed'=>stripslashes('Fine giornata chiusa'),
			'End of Day Report'=>stripslashes('Rapporto di fine giornata'),
			'End of Day Updated.'=>stripslashes('Fine del giorno aggiornato.'),
			'End of the day Starting Balance added successfully.'=>stripslashes('Fine della giornata Saldo iniziale aggiunto con successo.'),
			'End of the day Starting Balance updated successfully.'=>stripslashes('Fine della giornata Saldo iniziale aggiornato correttamente.'),
			'error occurred while adding new customer! please try again.'=>stripslashes('si è verificato un errore durante l&#39;aggiunta di un nuovo cliente! Per favore riprova.'),
			'error occurred while booking new appointment! please try again.'=>stripslashes('si è verificato un errore durante la prenotazione di un nuovo appuntamento! Per favore riprova.'),
			'Estimate'=>stripslashes('Stima'),
			'Expense type was edited'=>stripslashes('Il tipo di spesa è stato modificato'),
			'Expenses Details'=>stripslashes('Dettagli delle spese'),
			'Export Data'=>stripslashes('Esporta dati'),
			'Fax'=>stripslashes('Fax'),
			'Field is required.'=>stripslashes('Il campo è obbligatiorio.'),
			'First Name is required.'=>stripslashes('Il nome è obbligatorio.'),
			'Form'=>stripslashes('Modulo'),
			'Form Fields'=>stripslashes('Campi modulo'),
			'Form was edited'=>stripslashes('Il modulo è stato modificato'),
			'Forms'=>stripslashes('Forme'),
			'Forms data added successfully.'=>stripslashes('I dati dei moduli sono stati aggiunti con successo.'),
			'from'=>stripslashes('a partire dal'),
			'FULL SERVICE'=>stripslashes('SERVIZIO COMPLETO'),
			'General'=>stripslashes('Generale'),
			'Grand Total'=>stripslashes('Somma totale'),
			'Hardware Info'=>stripslashes('Informazioni sull\'hardware'),
			'Help'=>stripslashes('Aiuto'),
			'HERE FOR YOU'=>stripslashes('QUI PER TE'),
			'Hi'=>stripslashes('Ciao'),
			'Home'=>stripslashes('Casa'),
			'Home Page Body'=>stripslashes('Home Page Corpo'),
			'Hour field is required.'=>stripslashes('Il campo orario è obbligatorio.'),
			'IMEI'=>stripslashes('IMEI'),
			'IMEI Created'=>stripslashes('IMEI creato'),
			'IMEI/Serial No.'=>stripslashes('IMEI / numero di serie'),
			'Import Customers'=>stripslashes('Importa clienti'),
			'Import Products'=>stripslashes('Importa prodotti'),
			'Info'=>stripslashes('Informazioni'),
			'Inventory Purchased'=>stripslashes('Inventario acquistato'),
			'Inventory Reports'=>stripslashes('Report di inventario'),
			'Inventory Transfer'=>stripslashes('Trasferimento di inventario'),
			'Inventory Transfer Print-po #:'=>stripslashes('Inventory Transfer Print-po #:'),
			'Inventory Value'=>stripslashes('Valore di inventario'),
			'Inventory ValueN'=>stripslashes('Valore inventarioN'),
			'Invoice #: s'=>stripslashes('N. di fattura: s'),
			'Invoice Setup'=>stripslashes('Imposta fattura'),
			'Invoices Report'=>stripslashes('Rapporto fatture'),
			'Language information removed successfully.'=>stripslashes('Informazioni sulla lingua rimosse correttamente.'),
			'Languages'=>stripslashes('Le lingue'),
			'Locations'=>stripslashes('sedi'),
			'Login'=>stripslashes('Accesso'),
			'Login Message'=>stripslashes('Messaggio di accesso'),
			'Logo'=>stripslashes('Logo'),
			'Manage Accounts'=>stripslashes('Gestisci gli account'),
			'Manage Brand Model'=>stripslashes('Gestisci il modello di marca'),
			'Manage Categories'=>stripslashes('Gestisci categorie'),
			'Manage Commissions'=>stripslashes('Gestisci commissioni'),
			'Manage CRM'=>stripslashes('Gestisci CRM'),
			'Manage Customer Type'=>stripslashes('Gestisci il tipo di cliente'),
			'Manage Customers'=>stripslashes('Gestisci i clienti'),
			'Manage End of Day'=>stripslashes('Gestisci fine giornata'),
			'Manage EU GDPR'=>stripslashes('Gestire il GDPR dell\'UE'),
			'Manage Expense Type'=>stripslashes('Gestisci tipo di spesa'),
			'Manage Expenses'=>stripslashes('Gestisci le spese'),
			'Manage Label Printer'=>stripslashes('Gestisci stampante per etichette'),
			'Manage Manufacturer'=>stripslashes('Gestisci produttore'),
			'Manage Products'=>stripslashes('Gestisci prodotti'),
			'Manage Repair Problem'=>stripslashes('Gestisci il problema di riparazione'),
			'Manage SMS Messaging'=>stripslashes('Gestisci messaggi SMS'),
			'Manage Suppliers'=>stripslashes('Gestisci i fornitori'),
			'Manage Taxes'=>stripslashes('Gestisci le tasse'),
			'Manage Vendors'=>stripslashes('Gestisci i fornitori'),
			'Manage Website'=>stripslashes('Gestisci sito web'),
			'Manufacturer'=>stripslashes('fabbricante'),
			'Manufacturer archived'=>stripslashes('Produttore archiviato'),
			'Manufacturer was edited'=>stripslashes('Il produttore è stato modificato'),
			'Marked Completed'=>stripslashes('Contrassegnato come completato'),
			'Merge this'=>stripslashes('Unisci questo'),
			'Message is required.'=>stripslashes('Il messaggio è obbligatorio.'),
			'Live Stocks'=>stripslashes('Dispositivi mobili'),
			'Module name is invalid.'=>stripslashes('Il nome del modulo non è valido.'),
			'More Data'=>stripslashes('Più dati'),
			'Multiple Drawers'=>stripslashes('Cassetti multipli'),
			'My Information'=>stripslashes('Le mie informazioni'),
			'Name'=>stripslashes('Nome'),
			'Name is required.'=>stripslashes('Il nome è obbligatorio.'),
			'Name of'=>stripslashes('Nome di'),
			'Need more time?'=>stripslashes('Ho bisogno di più tempo?'),
			'New Expense Type Added'=>stripslashes('Nuovo tipo di spesa aggiunto'),
			'New Manufacturer Added'=>stripslashes('Nuovo produttore aggiunto'),
			'New Repair Ticket'=>stripslashes('Nuovo ticket di riparazione'),
			'New Vendor Added'=>stripslashes('Nuovo fornitore aggiunto'),
			'No users meet the criteria given'=>stripslashes('Nessun utente soddisfa i criteri indicati'),
			'Non Taxable Total'=>stripslashes('Totale non tassabile'),
			'Not Permitted'=>stripslashes('Non consentito'),
			'Note Created'=>stripslashes('Nota creata'),
			'Note History'=>stripslashes('Nota Cronologia'),
			'Note information'=>stripslashes('Nota informazioni'),
			'Notifications'=>stripslashes('notifiche'),
			'Offers Email'=>stripslashes('Offerte e-mail'),
			'Ok'=>stripslashes('Ok'),
			'Open Cash Drawer'=>stripslashes('Apri cassetto contanti'),
			'Order Created'=>stripslashes('Ordine creato'),
			'Order has cancelled.'=>stripslashes('L&#39;ordine è stato annullato.'),
			'Order No.'=>stripslashes('N. ordine'),
			'Order Pick'=>stripslashes('Ordine di scelta'),
			'Orders Print'=>stripslashes('Stampa ordini'),
			'Our Notes'=>stripslashes('Le nostre note'),
			'OUR SERVICES'=>stripslashes('I NOSTRI SERVIZI'),
			'OUR SUPPORT'=>stripslashes('IL NOSTRO SUPPORTO'),
			'P&L Statement'=>stripslashes('Dichiarazione P & L'),
			'Password'=>stripslashes('Parola d\'ordine'),
			'Payment'=>stripslashes('Pagamento'),
			'Payment Details'=>stripslashes('Dettagli di pagamento'),
			'Payment Due'=>stripslashes('Pagamento dovuto'),
			'Payment Options'=>stripslashes('Opzioni di pagamento'),
			'Payment Receipt'=>stripslashes('Ricevuta di pagamento'),
			'Payments Received by Type'=>stripslashes('Pagamenti ricevuti per tipo'),
			'Petty Cash'=>stripslashes('Petty Cash'),
			'Petty cash was edited'=>stripslashes('Il piccolo denaro è stato modificato'),
			'Phone No'=>stripslashes('telefono n'),
			'Please check your account. Go to website module and copy new API code and update current API code.'=>stripslashes('Per favore controlla il tuo account. Vai al modulo del sito Web e copia il nuovo codice API e aggiorna il codice API corrente.'),
			'Please click Buy Now above to continue to use your accounts.'=>stripslashes('Fai clic su Acquista ora qui sopra per continuare a utilizzare i tuoi account.'),
			'Please give change amount of'=>stripslashes('Si prega di fornire un importo di modifica di'),
			'Please setup SMS messaging.'=>stripslashes('Si prega di configurare la messaggistica SMS.'),
			'Please try again, thank you.'=>stripslashes('Per favore, riprova, grazie.'),
			'Please update your payment method'=>stripslashes('Si prega di aggiornare il metodo di pagamento'),
			'PO Number'=>stripslashes('Numero PO'),
			'PO Setup'=>stripslashes('Impostazione PO'),
			'PO was re-open.'=>stripslashes('PO è stato riaperto.'),
			'Popup Message'=>stripslashes('Messaggio a comparsa'),
			'Possible file upload attack. Filename:'=>stripslashes('Possibile attacco di upload di file. Nome del file:'),
			'Print Order'=>stripslashes('Ordine di stampa'),
			'Problem'=>stripslashes('Problema'),
			'Product'=>stripslashes('Prodotto'),
			'Product Barcode Print'=>stripslashes('Stampa del codice a barre del prodotto'),
			'Product Created'=>stripslashes('Prodotto creato'),
			'Product Information'=>stripslashes('Informazioni sul prodotto'),
			'Product price has been added'=>stripslashes('Il prezzo del prodotto è stato aggiunto'),
			'Product Unarchive'=>stripslashes('Annulla archiviazione prodotto'),
			'Product was edited'=>stripslashes('Il prodotto è stato modificato'),
			'Products'=>stripslashes('Prodotti'),
			'Products Report'=>stripslashes('Rapporto sui prodotti'),
			'Property was edited'=>stripslashes('La proprietà è stata modificata'),
			'Purchase Order'=>stripslashes('Ordinazione d\'acquisto'),
			'Purchase Order Created'=>stripslashes('Ordine d\'acquisto creato'),
			'Purchase Orders'=>stripslashes('Ordini d\'acquisto'),
			'QTY'=>stripslashes('Quantità'),
			'Receipt Printer & Cash Drawer'=>stripslashes('Stampante per ricevute e cassetto contanti'),
			'Refund Items'=>stripslashes('Articoli di rimborso'),
			'Remove'=>stripslashes('Rimuovere'),
			'Remove IMEI Number'=>stripslashes('Rimuovi il numero IMEI'),
			'Remove product'=>stripslashes('Rimuovi prodotto'),
			'Remove Serial Number'=>stripslashes('Rimuovi il numero di serie'),
			'Remove Serial/IMEI Number'=>stripslashes('Rimuovi il numero seriale / IMEI'),
			'Remove Time Clock'=>stripslashes('Rimuovi l\'orologio'),
			'REMOVED FROM INVENTORY'=>stripslashes('RIMOSSO DALL\'INVENTARIO'),
			'Repair Appointment'=>stripslashes('Appuntamento di riparazione'),
			'Repair Created'=>stripslashes('Riparazione creata'),
			'Repair Information'=>stripslashes('Informazioni sulla riparazione'),
			'Repair problems was edited'=>stripslashes('I problemi di riparazione sono stati modificati'),
			'REPAIR SERVICES'=>stripslashes('SERVIZI DI RIPARAZIONE'),
			'Repair services you can trust.  From small issues to major repairs our trained technicians are ready to assist.  We are looking forward to serving you!'=>stripslashes('Servizi di riparazione di cui ti puoi fidare. Da piccoli problemi a riparazioni importanti, i nostri tecnici qualificati sono pronti ad assistere. Non vediamo l\'ora di servirti!'),
			'Repair Summary of Ticket'=>stripslashes('Ripara il riepilogo del biglietto'),
			'Repair Ticket'=>stripslashes('Biglietto di riparazione'),
			'Repair Tickets Created'=>stripslashes('Riparare i biglietti creati'),
			'Repairs'=>stripslashes('riparazione'),
			'Repairs by problems'=>stripslashes('Riparazioni per problemi'),
			'Repairs by status'=>stripslashes('Riparazioni per stato'),
			'Repairs Reports'=>stripslashes('Rapporti di riparazioni'),
			'Request a Quote'=>stripslashes('Richiedi un preventivo'),
			'Restrict Access'=>stripslashes('Accesso limitato'),
			'Return'=>stripslashes('Ritorno'),
			'Return Purchase Order'=>stripslashes('Restituire l\'ordine d\'acquisto'),
			's COMPANYNAME'=>stripslashes('s COMPANYNAME'),
			's COMPANYNAME accounts, you have just been granted access.'=>stripslashes('s account COMPANYNAME, ti è appena stato concesso l\'accesso.'),
			'SALES'=>stripslashes('I SALDI'),
			'Sales Ampar'=>stripslashes('Vendite Ampar'),
			'Sales by Category'=>stripslashes('Vendite per categoria'),
			'Sales by Customer'=>stripslashes('Vendite dal cliente'),
			'Sales by Date'=>stripslashes('Vendite per data'),
			'Sales by Product'=>stripslashes('Vendite per prodotto'),
			'Sales by Sales Person'=>stripslashes('Vendite per addetto alle vendite'),
			'Sales by Tax'=>stripslashes('Vendite per imposte'),
			'Sales by Technician'=>stripslashes('Vendite dal tecnico'),
			'Sales Invoice # s'=>stripslashes('Fattura di vendita # s'),
			'Sales invoice archived'=>stripslashes('Fattura di vendita archiviata'),
			'Sales Invoice Created'=>stripslashes('Fattura di vendita creata'),
			'Sales Invoices'=>stripslashes('Fatture di vendita'),
			'Sales Invoices Print-Invoice #: s'=>stripslashes('Fatture di vendita Stampa-fattura #: s'),
			'Sales Person'=>stripslashes('Venditore'),
			'Sales Receipt'=>stripslashes('Ricevuta di vendita'),
			'Sales Register'=>stripslashes('Registro delle vendite'),
			'Sales Reports'=>stripslashes('Rapporti di vendita'),
			'Services'=>stripslashes('Servizi'),
			'Setup Users'=>stripslashes('Imposta utenti'),
			'Shipping Cost'=>stripslashes('Spese di spedizione'),
			'Signature'=>stripslashes('Firma'),
			'Smart Phone'=>stripslashes('Smartphone'),
			'SMS sent to'=>stripslashes('SMS inviato a'),
			'Sorry! system could not find any Cash Register.'=>stripslashes('Scusate! il sistema non ha trovato alcun registratore di cassa.'),
			'Square Credit Card Processing'=>stripslashes('Elaborazione di carte di credito quadrate'),
			'State / Province'=>stripslashes('Stato / Provincia'),
			'Status'=>stripslashes('Stato'),
			'Stock Take Information'=>stripslashes('Informazioni sullo stock take'),
			'Store login address'=>stripslashes('Memorizza l\'indirizzo di accesso'),
			'SUBSCRIBE NOW!'=>stripslashes('ISCRIVITI ORA!'),
			'Subtotal'=>stripslashes('totale parziale'),
			'summary of t'=>stripslashes('riassunto di t'),
			'Supplier Created'=>stripslashes('Fornitore creato'),
			'Supplier Info'=>stripslashes('Informazioni del fornitore'),
			'Suppliers Information'=>stripslashes('Informazioni sui fornitori'),
			'SUSPENDED'=>stripslashes('SOSPESO'),
			'System'=>stripslashes('Sistema'),
			'Tax'=>stripslashes('Imposta'),
			'Tax archived'=>stripslashes('Tasse archiviate'),
			'Tax Created'=>stripslashes('Imposta creata'),
			'Tax has been changed.'=>stripslashes('L&#39;imposta è stata modificata.'),
			'Taxable Total'=>stripslashes('Totale tassabile'),
			'Taxes'=>stripslashes('Le tasse'),
			'Technician'=>stripslashes('Tecnico'),
			'Telephone'=>stripslashes('Telefono'),
			'Thank you for requesting a quote.'=>stripslashes('Grazie per aver richiesto un preventivo.'),
			'Thank you for requesting an appointment.'=>stripslashes('Grazie per aver richiesto un appuntamento.'),
			'Thanks again'=>stripslashes('Grazie ancora'),
			'The COMPANYNAME Team'=>stripslashes('Il team COMPANYNAME'),
			'There is no data found'=>stripslashes('Non ci sono dati trovati'),
			'There is no form submit'=>stripslashes('Non c\'è nessun modulo di invio'),
			'There is no ticket found.'=>stripslashes('Non è stato trovato alcun biglietto.'),
			'This account has been CANCELED. To reopen click'=>stripslashes('Questo account è stato ANNULLATO. Per riaprire clicca'),
			'this date and time already booked. try again with different date and time.'=>stripslashes('questa data e ora già prenotate. riprova con data e ora diverse.'),
			'This invoice has been completed'=>stripslashes('Questa fattura è stata completata'),
			'This name and email already exist. Try again with a different name/email.'=>stripslashes('Questo nome e questo indirizzo email esistono già. Riprova con un nome/e-mail diverso.'),
			'This ticket was created from Ticket #'=>stripslashes('Questo ticket è stato creato da Ticket #'),
			'Ticket'=>stripslashes('Biglietto'),
			'Ticket Info'=>stripslashes('Informazioni sul biglietto'),
			'Ticket Number is required.'=>stripslashes('Il numero del biglietto è obbligatorio.'),
			'Time'=>stripslashes('Tempo'),
			'Time Clock Information'=>stripslashes('Informazioni sull\'orologio'),
			'Time Clock Manager'=>stripslashes('Time Clock Manager'),
			'Time clock was edited.'=>stripslashes('L\'orologio è stato modificato.'),
			'Time Report'=>stripslashes('Rapporto temporale'),
			'To'=>stripslashes('A'),
			'to'=>stripslashes('a'),
			'Total'=>stripslashes('Totale'),
			'Total amount due by'=>stripslashes('Importo totale dovuto da'),
			'Transfer'=>stripslashes('Trasferimento'),
			'Trial Period Ended'=>stripslashes('Periodo di prova terminato'),
			'Unarchive'=>stripslashes('Rimuovi dall&#39;archivio'),
			'Unit Price'=>stripslashes('Prezzo unitario'),
			'Updated End of Day for'=>stripslashes('Data di fine aggiornata per'),
			'User'=>stripslashes('Utente'),
			'User archived'=>stripslashes('Utente archiviato'),
			'User Created'=>stripslashes('Utente creato'),
			'User Logged In'=>stripslashes('Utente connesso'),
			'User was edited'=>stripslashes('L\'utente è stato modificato'),
			'Username'=>stripslashes('Nome utente'),
			'Vendor was edited'=>stripslashes('Il venditore è stato modificato'),
			'View Invoice'=>stripslashes('Visualizza fattura'),
			'View Product Details'=>stripslashes('Visualizza i dettagli del prodotto'),
			'We are here to assist you. Our confident team is available and ready to answer your questions and exceed your expectations. Contact us today.'=>stripslashes('Siamo qui per assisterti. Il nostro team fiducioso è disponibile e pronto a rispondere alle tue domande e superare le tue aspettative. Contattaci oggi.'),
			'we have received your request for an appointment.'=>stripslashes('abbiamo ricevuto la tua richiesta di appuntamento.'),
			'We will be in touch very soon, thank you.'=>stripslashes('Ci metteremo in contatto molto presto, grazie.'),
			'We will reply as soon as possible.'=>stripslashes('Ti risponderemo il prima possibile.'),
			'Website'=>stripslashes('Sito web'),
			'Welcome to'=>stripslashes('Benvenuto a'),
			'What needs to be fixed'=>stripslashes('Cosa deve essere risolto'),
			'You are not admin User'=>stripslashes('Non sei un utente amministratore'),
			'You are not the user that created this account. To subscribe please have the account creator log in and click this button'=>stripslashes('Non sei l&#39;utente che ha creato questo account. Per iscriversi, fai in modo che il creatore dell&#39;account effettui il login e fai clic su questo pulsante'),
			'You could not remove SMS Integration information before accessing Your SMS to COMPANYNAME.'=>stripslashes('Non è stato possibile rimuovere le informazioni sull\'integrazione degli SMS prima di accedere agli SMS in COMPANYNAME.'),
			'you have only'=>stripslashes('hai solo'),
			'You wrote:'=>stripslashes('Hai scritto:'),
			'Your access to'=>stripslashes('Il tuo accesso a'),
			'Your account is invalid.'=>stripslashes('Il tuo account non è valido.'),
			'Your accounts has been suspended. This is most likely do to a billing issue. Please contact us by clicking the help ? at the top right of any page on this application. Thank you'=>stripslashes('I tuoi account sono stati sospesi. Molto probabilmente questo è dovuto a un problema di fatturazione. Vi preghiamo di contattarci facendo clic sull\'aiuto? in alto a destra di qualsiasi pagina su questa applicazione. Grazie'),
			'Your IP is not allowed to access this software. Please contact with admin.'=>stripslashes('Il tuo IP non è autorizzato ad accedere a questo software. Si prega di contattare con l\'amministratore.'),
			'Your login details are below'=>stripslashes('I tuoi dati di accesso sono sotto'),
			'Your message could not send.'=>stripslashes('Impossibile inviare il tuo messaggio.'),
			'Your message has been successfully sent.'=>stripslashes('Il tuo messaggio è stato inviato con successo.'),
			'Your Quote has been successfully sent.'=>stripslashes('Il tuo preventivo è stato inviato con successo.'),
			'Your trial accounts has expired.'=>stripslashes('I tuoi account di prova sono scaduti.'),
			'Zip/Postal Code'=>stripslashes('Zip / Codice postale'),
		);
		if(array_key_exists($index, $languageData)){
			return $languageData[$index];
		}
		return false;
	}
}
?>