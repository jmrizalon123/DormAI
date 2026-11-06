<?php

use App\Http\Controllers\Auth\OrganizationRegistrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Organization\AnalyticsReportController;
use App\Http\Controllers\Organization\AnnouncementController;
use App\Http\Controllers\Organization\BedSpaceController;
use App\Http\Controllers\Organization\BillingSettingsController;
use App\Http\Controllers\Organization\BuildingController;
use App\Http\Controllers\Organization\ContractController;
use App\Http\Controllers\Organization\ContactMessageController as OrganizationContactMessageController;
use App\Http\Controllers\Organization\ContractTemplateController;
use App\Http\Controllers\Organization\DashboardController as OrganizationDashboardController;
use App\Http\Controllers\Organization\ExpenseAttachmentController;
use App\Http\Controllers\Organization\ExpenseCategoryController;
use App\Http\Controllers\Organization\ExpenseController;
use App\Http\Controllers\Organization\FinancialReportController;
use App\Http\Controllers\Organization\FloorController;
use App\Http\Controllers\Organization\InvoiceController;
use App\Http\Controllers\Organization\MaintenanceHistoryController;
use App\Http\Controllers\Organization\MaintenanceRequestController;
use App\Http\Controllers\Organization\OrganizationMapSettingsController;
use App\Http\Controllers\Organization\PaymentController;
use App\Http\Controllers\Organization\PropertyController;
use App\Http\Controllers\Organization\PropertyGeocodeController;
use App\Http\Controllers\Organization\PropertyImageController;
use App\Http\Controllers\Organization\PropertyReverseGeocodeController;
use App\Http\Controllers\Organization\ReportExportController;
use App\Http\Controllers\Organization\ReportsHubController;
use App\Http\Controllers\Organization\ReservationController;
use App\Http\Controllers\Organization\RoomAmenityController;
use App\Http\Controllers\Organization\RoomController;
use App\Http\Controllers\Organization\RoomImageController;
use App\Http\Controllers\Organization\RoomTypeController;
use App\Http\Controllers\Organization\TenantAssignmentController;
use App\Http\Controllers\Organization\TenantController;
use App\Http\Controllers\Organization\TenantDocumentController;
use App\Http\Controllers\Organization\TenantEmergencyContactController;
use App\Http\Controllers\Organization\TenantPaymentSubmissionController;
use App\Http\Controllers\Organization\TenantPortalAccessController;
use App\Http\Controllers\Organization\UserController as OrganizationUserController;
use App\Http\Controllers\Organization\UtilityMeterController;
use App\Http\Controllers\Organization\UtilityRateController;
use App\Http\Controllers\Organization\UtilityReadingController;
use App\Http\Controllers\Organization\UtilityReportController;
use App\Http\Controllers\Organization\WorkOrderController;
use App\Http\Controllers\Platform\ContactMessageController as PlatformContactMessageController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\ListingContentSettingsController as PlatformListingContentSettingsController;
use App\Http\Controllers\Platform\ListingMapSettingsController as PlatformListingMapSettingsController;
use App\Http\Controllers\Platform\OrganizationController as PlatformOrganizationController;
use App\Http\Controllers\Portal\AnnouncementController as PortalAnnouncementController;
use App\Http\Controllers\Portal\ContractController as PortalContractController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Portal\DocumentController as PortalDocumentController;
use App\Http\Controllers\Portal\InvoiceController as PortalInvoiceController;
use App\Http\Controllers\Portal\MaintenanceRequestController as PortalMaintenanceRequestController;
use App\Http\Controllers\Portal\PaymentController as PortalPaymentController;
use App\Http\Controllers\Portal\PaymentSubmissionController as PortalPaymentSubmissionController;
use App\Http\Controllers\Portal\ProfileController as PortalProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ListingAssistantController;
use App\Http\Controllers\Public\MarketplaceAssistantController;
use App\Http\Controllers\Public\PropertyListingController;
use App\Http\Controllers\Public\ReservationRequestController;
use App\Http\Controllers\Public\StayController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/listings/{property}', [PropertyListingController::class, 'show'])->name('public.listings.show');
Route::post('/assistant', MarketplaceAssistantController::class)
    ->name('public.marketplace.assistant')
    ->middleware('throttle:20,1');
Route::post('/listings/{property}/assistant', ListingAssistantController::class)
    ->name('public.listings.assistant')
    ->middleware('throttle:20,1');

Route::get('/contact', [ContactController::class, 'create'])->name('public.contact.create');
Route::post('/contact', [ContactController::class, 'store'])->name('public.contact.store')->middleware('throttle:6,1');
Route::get('/contact/sent/{reference}', [ContactController::class, 'sent'])->name('public.contact.sent');
Route::get('/contact/track', [ContactController::class, 'trackForm'])->name('public.contact.track');
Route::get('/contact/track/{reference}', [ContactController::class, 'show'])->name('public.contact.show');

Route::prefix('stay')->name('public.stay.')->group(function () {
    Route::get('{publicOrganization}', [StayController::class, 'index'])->name('index');
    Route::get('{publicOrganization}/beds/{bedSpace}', [StayController::class, 'show'])->name('show');
    Route::post('{publicOrganization}/beds/{bedSpace}/reserve', [ReservationRequestController::class, 'store'])->name('reserve');
    Route::get('{publicOrganization}/confirmation/{reference}', [ReservationRequestController::class, 'confirmation'])->name('confirmation');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [OrganizationRegistrationController::class, 'create'])->name('register');
    Route::post('/register', [OrganizationRegistrationController::class, 'store'])->name('register.store');
});

Route::middleware(['auth', 'verified', 'auth.tenancy'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::middleware('tenant.portal')->prefix('portal')->name('portal.')->group(function () {
        Route::get('/dashboard', PortalDashboardController::class)->name('dashboard');
        Route::get('/profile', [PortalProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [PortalProfileController::class, 'update'])->name('profile.update');

        Route::get('/invoices', [PortalInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{portalInvoice}', [PortalInvoiceController::class, 'show'])->name('invoices.show');

        Route::get('/payments', [PortalPaymentController::class, 'index'])->name('payments.index');

        Route::get('/payment-submissions', [PortalPaymentSubmissionController::class, 'index'])->name('payment-submissions.index');
        Route::get('/payment-submissions/create', [PortalPaymentSubmissionController::class, 'create'])->name('payment-submissions.create');
        Route::post('/payment-submissions', [PortalPaymentSubmissionController::class, 'store'])->name('payment-submissions.store');

        Route::get('/maintenance', [PortalMaintenanceRequestController::class, 'index'])->name('maintenance.index');
        Route::get('/maintenance/create', [PortalMaintenanceRequestController::class, 'create'])->name('maintenance.create');
        Route::post('/maintenance', [PortalMaintenanceRequestController::class, 'store'])->name('maintenance.store');
        Route::get('/maintenance/{portalMaintenanceRequest}', [PortalMaintenanceRequestController::class, 'show'])->name('maintenance.show');
        Route::post('/maintenance/{portalMaintenanceRequest}/cancel', [PortalMaintenanceRequestController::class, 'cancel'])->name('maintenance.cancel');

        Route::get('/announcements', [PortalAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/{portalAnnouncement}', [PortalAnnouncementController::class, 'show'])->name('announcements.show');

        Route::get('/contracts', [PortalContractController::class, 'index'])->name('contracts.index');
        Route::get('/contracts/{portalContract}', [PortalContractController::class, 'show'])->name('contracts.show');

        Route::get('/documents', [PortalDocumentController::class, 'index'])->name('documents.index');
        Route::get('/documents/{portalDocument}/download', [PortalDocumentController::class, 'download'])->name('documents.download');
    });

    Route::middleware('super_admin')->prefix('platform')->name('platform.')->group(function () {
        Route::get('/dashboard', PlatformDashboardController::class)->name('dashboard');
        Route::get('/settings/listing-map', [PlatformListingMapSettingsController::class, 'edit'])->name('settings.listing-map.edit');
        Route::put('/settings/listing-map', [PlatformListingMapSettingsController::class, 'update'])->name('settings.listing-map.update');
        Route::post('/settings/listing-map/reset', [PlatformListingMapSettingsController::class, 'reset'])->name('settings.listing-map.reset');
        Route::get('/settings/listing-content', [PlatformListingContentSettingsController::class, 'edit'])->name('settings.listing-content.edit');
        Route::post('/settings/listing-content', [PlatformListingContentSettingsController::class, 'update'])->name('settings.listing-content.update');
        Route::post('/settings/listing-content/reset', [PlatformListingContentSettingsController::class, 'reset'])->name('settings.listing-content.reset');
        Route::delete('/settings/listing-content/hero-images/{index}', [PlatformListingContentSettingsController::class, 'destroyHeroImage'])->name('settings.listing-content.hero-images.destroy');
        Route::get('/contact-messages', [PlatformContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('/contact-messages/{contactMessage}', [PlatformContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::post('/contact-messages/{contactMessage}/reply', [PlatformContactMessageController::class, 'reply'])->name('contact-messages.reply');
        Route::post('/contact-messages/{contactMessage}/close', [PlatformContactMessageController::class, 'close'])->name('contact-messages.close');

        Route::get('/organizations', [PlatformOrganizationController::class, 'index'])->name('organizations.index');
        Route::get('/organizations/{organization}', [PlatformOrganizationController::class, 'show'])->name('organizations.show');
        Route::post('/organizations/{organization}/approve', [PlatformOrganizationController::class, 'approve'])->name('organizations.approve');
        Route::post('/organizations/{organization}/suspend', [PlatformOrganizationController::class, 'suspend'])->name('organizations.suspend');
        Route::post('/organizations/{organization}/reject', [PlatformOrganizationController::class, 'reject'])->name('organizations.reject');
    });

    Route::middleware('org.access')->prefix('organization')->name('organization.')->group(function () {
        Route::get('/dashboard', OrganizationDashboardController::class)->name('dashboard');
        Route::resource('users', OrganizationUserController::class)->except(['show']);

        Route::get('properties/geocode', PropertyGeocodeController::class)->name('properties.geocode');
        Route::get('properties/reverse-geocode', PropertyReverseGeocodeController::class)->name('properties.reverse-geocode');
        Route::resource('properties', PropertyController::class);
        Route::post('properties/{property}/images', [PropertyImageController::class, 'store'])->name('properties.images.store');
        Route::delete('properties/{property}/images/{propertyImage}', [PropertyImageController::class, 'destroy'])->name('properties.images.destroy');

        Route::get('properties/{property}/buildings/create', [BuildingController::class, 'create'])->name('properties.buildings.create');
        Route::post('properties/{property}/buildings', [BuildingController::class, 'store'])->name('properties.buildings.store');
        Route::get('buildings/{building}', [BuildingController::class, 'show'])->name('buildings.show');
        Route::get('buildings/{building}/edit', [BuildingController::class, 'edit'])->name('buildings.edit');
        Route::put('buildings/{building}', [BuildingController::class, 'update'])->name('buildings.update');
        Route::delete('buildings/{building}', [BuildingController::class, 'destroy'])->name('buildings.destroy');

        Route::get('buildings/{building}/floors/create', [FloorController::class, 'create'])->name('buildings.floors.create');
        Route::post('buildings/{building}/floors', [FloorController::class, 'store'])->name('buildings.floors.store');
        Route::get('floors/{floor}', [FloorController::class, 'show'])->name('floors.show');
        Route::get('floors/{floor}/edit', [FloorController::class, 'edit'])->name('floors.edit');
        Route::put('floors/{floor}', [FloorController::class, 'update'])->name('floors.update');
        Route::delete('floors/{floor}', [FloorController::class, 'destroy'])->name('floors.destroy');

        Route::get('floors/{floor}/rooms/create', [RoomController::class, 'create'])->name('floors.rooms.create');
        Route::post('floors/{floor}/rooms', [RoomController::class, 'store'])->name('floors.rooms.store');
        Route::get('rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
        Route::get('rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
        Route::post('rooms/{room}/images', [RoomImageController::class, 'store'])->name('rooms.images.store');
        Route::delete('rooms/{room}/images/{roomImage}', [RoomImageController::class, 'destroy'])->name('rooms.images.destroy');

        Route::post('rooms/{room}/bed-spaces', [BedSpaceController::class, 'store'])->name('rooms.bed-spaces.store');
        Route::put('rooms/{room}/bed-spaces/{bedSpace}', [BedSpaceController::class, 'update'])->name('rooms.bed-spaces.update');
        Route::delete('rooms/{room}/bed-spaces/{bedSpace}', [BedSpaceController::class, 'destroy'])->name('rooms.bed-spaces.destroy');

        Route::resource('room-types', RoomTypeController::class)->except(['show']);
        Route::get('room-amenities', [RoomAmenityController::class, 'index'])->name('room-amenities.index');
        Route::post('room-amenities', [RoomAmenityController::class, 'store'])->name('room-amenities.store');
        Route::delete('room-amenities/{roomAmenity}', [RoomAmenityController::class, 'destroy'])->name('room-amenities.destroy');

        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/assignments', [TenantAssignmentController::class, 'store'])->name('tenants.assignments.store');
        Route::delete('tenants/{tenant}/assignments/{assignment}', [TenantAssignmentController::class, 'destroy'])->name('tenants.assignments.destroy');
        Route::post('tenants/{tenant}/emergency-contacts', [TenantEmergencyContactController::class, 'store'])->name('tenants.emergency-contacts.store');
        Route::delete('tenants/{tenant}/emergency-contacts/{contact}', [TenantEmergencyContactController::class, 'destroy'])->name('tenants.emergency-contacts.destroy');
        Route::post('tenants/{tenant}/portal-access', [TenantPortalAccessController::class, 'store'])->name('tenants.portal-access.store');
        Route::post('tenants/{tenant}/documents', [TenantDocumentController::class, 'store'])->name('tenants.documents.store');
        Route::get('tenants/{tenant}/documents/{document}', [TenantDocumentController::class, 'show'])->name('tenants.documents.show');
        Route::delete('tenants/{tenant}/documents/{document}', [TenantDocumentController::class, 'destroy'])->name('tenants.documents.destroy');

        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::post('reservations/{reservation}/approve', [ReservationController::class, 'approve'])->name('reservations.approve');
        Route::post('reservations/{reservation}/reject', [ReservationController::class, 'reject'])->name('reservations.reject');
        Route::post('reservations/{reservation}/deposit-paid', [ReservationController::class, 'markDepositPaid'])->name('reservations.deposit-paid');
        Route::post('reservations/{reservation}/check-in', [ReservationController::class, 'checkIn'])->name('reservations.check-in');
        Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::put('reservations/{reservation}/staff-notes', [ReservationController::class, 'updateStaffNotes'])->name('reservations.staff-notes');

        Route::get('contact-messages', [OrganizationContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('contact-messages/{contactMessage}', [OrganizationContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::post('contact-messages/{contactMessage}/reply', [OrganizationContactMessageController::class, 'reply'])->name('contact-messages.reply');
        Route::post('contact-messages/{contactMessage}/close', [OrganizationContactMessageController::class, 'close'])->name('contact-messages.close');

        Route::get('contracts', [ContractController::class, 'index'])->name('contracts.index');
        Route::get('contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
        Route::get('contracts/{contract}/print', [ContractController::class, 'print'])->name('contracts.print');
        Route::get('contracts/{contract}/download', [ContractController::class, 'download'])->name('contracts.download');
        Route::post('contracts/{contract}/renew', [ContractController::class, 'renew'])->name('contracts.renew');

        Route::resource('contract-templates', ContractTemplateController::class)->except(['show']);

        Route::get('billing/settings', [BillingSettingsController::class, 'edit'])->name('billing.settings.edit');
        Route::put('billing/settings', [BillingSettingsController::class, 'update'])->name('billing.settings.update');

        Route::get('settings/map', [OrganizationMapSettingsController::class, 'edit'])->name('settings.map.edit');
        Route::put('settings/map', [OrganizationMapSettingsController::class, 'update'])->name('settings.map.update');
        Route::post('settings/map/reset', [OrganizationMapSettingsController::class, 'reset'])->name('settings.map.reset');

        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue'])->name('invoices.issue');
        Route::post('invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

        Route::get('payment-submissions', [TenantPaymentSubmissionController::class, 'index'])->name('payment-submissions.index');
        Route::get('payment-submissions/{paymentSubmission}', [TenantPaymentSubmissionController::class, 'show'])->name('payment-submissions.show');
        Route::post('payment-submissions/{paymentSubmission}/approve', [TenantPaymentSubmissionController::class, 'approve'])->name('payment-submissions.approve');
        Route::post('payment-submissions/{paymentSubmission}/reject', [TenantPaymentSubmissionController::class, 'reject'])->name('payment-submissions.reject');

        Route::get('expense-categories', [ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
        Route::post('expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
        Route::put('expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
        Route::delete('expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');

        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::post('expenses/{expense}/submit', [ExpenseController::class, 'submit'])->name('expenses.submit');
        Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
        Route::post('expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject');
        Route::post('expenses/{expense}/attachments', [ExpenseAttachmentController::class, 'store'])->name('expenses.attachments.store');
        Route::get('expenses/{expense}/attachments/{attachment}', [ExpenseAttachmentController::class, 'show'])->name('expenses.attachments.show');
        Route::delete('expenses/{expense}/attachments/{attachment}', [ExpenseAttachmentController::class, 'destroy'])->name('expenses.attachments.destroy');

        Route::get('reports', ReportsHubController::class)->name('reports.hub');
        Route::get('reports/occupancy', [AnalyticsReportController::class, 'occupancy'])->name('reports.occupancy');
        Route::get('reports/payment-collection', [AnalyticsReportController::class, 'paymentCollection'])->name('reports.payment-collection');
        Route::get('reports/tenants', [AnalyticsReportController::class, 'tenants'])->name('reports.tenants');
        Route::get('reports/reservations', [AnalyticsReportController::class, 'reservations'])->name('reports.reservations');
        Route::get('reports/expenses', [AnalyticsReportController::class, 'expenses'])->name('reports.expenses');
        Route::get('reports/{report}/export', ReportExportController::class)
            ->name('reports.export')
            ->where('report', 'occupancy|payment-collection|tenants|reservations|expenses|financial-summary|utility');

        Route::get('financial-reports/summary', [FinancialReportController::class, 'summary'])->name('financial-reports.summary');
        Route::get('financial-reports/profit-loss', [FinancialReportController::class, 'profitLoss'])->name('financial-reports.profit-loss');
        Route::get('financial-reports/income-expense', [FinancialReportController::class, 'incomeExpense'])->name('financial-reports.income-expense');

        Route::get('utility-rates', [UtilityRateController::class, 'index'])->name('utility-rates.index');
        Route::post('utility-rates', [UtilityRateController::class, 'store'])->name('utility-rates.store');
        Route::put('utility-rates/{utilityRate}', [UtilityRateController::class, 'update'])->name('utility-rates.update');

        Route::get('utility-meters', [UtilityMeterController::class, 'index'])->name('utility-meters.index');
        Route::get('utility-meters/create', [UtilityMeterController::class, 'create'])->name('utility-meters.create');
        Route::post('utility-meters', [UtilityMeterController::class, 'store'])->name('utility-meters.store');
        Route::get('utility-meters/{utilityMeter}/edit', [UtilityMeterController::class, 'edit'])->name('utility-meters.edit');
        Route::put('utility-meters/{utilityMeter}', [UtilityMeterController::class, 'update'])->name('utility-meters.update');

        Route::get('utility-readings', [UtilityReadingController::class, 'index'])->name('utility-readings.index');
        Route::get('utility-readings/create', [UtilityReadingController::class, 'create'])->name('utility-readings.create');
        Route::post('utility-readings', [UtilityReadingController::class, 'store'])->name('utility-readings.store');
        Route::get('utility-readings/{utilityReading}', [UtilityReadingController::class, 'show'])->name('utility-readings.show');
        Route::delete('utility-readings/{utilityReading}', [UtilityReadingController::class, 'destroy'])->name('utility-readings.destroy');
        Route::post('utility-readings/{utilityReading}/allocate', [UtilityReadingController::class, 'allocate'])->name('utility-readings.allocate');
        Route::post('utility-readings/{utilityReading}/bill', [UtilityReadingController::class, 'bill'])->name('utility-readings.bill');

        Route::get('utility-reports', [UtilityReportController::class, 'index'])->name('utility-reports.index');

        Route::get('maintenance-requests', [MaintenanceRequestController::class, 'index'])->name('maintenance-requests.index');
        Route::get('maintenance-requests/create', [MaintenanceRequestController::class, 'create'])->name('maintenance-requests.create');
        Route::post('maintenance-requests', [MaintenanceRequestController::class, 'store'])->name('maintenance-requests.store');
        Route::get('maintenance-requests/{maintenanceRequest}', [MaintenanceRequestController::class, 'show'])->name('maintenance-requests.show');
        Route::post('maintenance-requests/{maintenanceRequest}/cancel', [MaintenanceRequestController::class, 'cancel'])->name('maintenance-requests.cancel');
        Route::post('maintenance-requests/{maintenanceRequest}/work-order', [MaintenanceRequestController::class, 'createWorkOrder'])->name('maintenance-requests.work-order');

        Route::get('work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
        Route::get('work-orders/create', [WorkOrderController::class, 'create'])->name('work-orders.create');
        Route::post('work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
        Route::get('work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
        Route::post('work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])->name('work-orders.assign');
        Route::post('work-orders/{workOrder}/status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.status');
        Route::post('work-orders/{workOrder}/complete', [WorkOrderController::class, 'complete'])->name('work-orders.complete');

        Route::get('maintenance-history', [MaintenanceHistoryController::class, 'index'])->name('maintenance-history.index');

        Route::resource('announcements', AnnouncementController::class)->except(['show']);
    });
});
