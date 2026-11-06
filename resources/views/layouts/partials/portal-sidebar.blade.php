<x-sidebar.nav-item
    :href="route('portal.dashboard')"
    icon="bi-house-door"
    label="Dashboard"
    :active="request()->routeIs('portal.dashboard')"
/>
<x-sidebar.nav-section label="My stay" />
<x-sidebar.nav-item :href="route('portal.profile.edit')" icon="bi-person" label="Profile" :active="request()->routeIs('portal.profile.*')" />
<x-sidebar.nav-item :href="route('portal.contracts.index')" icon="bi-file-earmark-text" label="Rental agreement" :active="request()->routeIs('portal.contracts.*')" />
<x-sidebar.nav-item :href="route('portal.documents.index')" icon="bi-folder2-open" label="Documents" :active="request()->routeIs('portal.documents.*')" />
<x-sidebar.nav-section label="Billing" />
<x-sidebar.nav-item :href="route('portal.invoices.index')" icon="bi-receipt" label="Invoices" :active="request()->routeIs('portal.invoices.*')" />
<x-sidebar.nav-item :href="route('portal.payments.index')" icon="bi-cash-stack" label="Payment history" :active="request()->routeIs('portal.payments.*')" />
<x-sidebar.nav-item :href="route('portal.payment-submissions.index')" icon="bi-upload" label="Submit payment" :active="request()->routeIs('portal.payment-submissions.*')" />
<x-sidebar.nav-section label="Support" />
<x-sidebar.nav-item :href="route('portal.maintenance.index')" icon="bi-tools" label="Maintenance" :active="request()->routeIs('portal.maintenance.*')" />
<x-sidebar.nav-item :href="route('portal.announcements.index')" icon="bi-megaphone" label="Announcements" :active="request()->routeIs('portal.announcements.*')" />
