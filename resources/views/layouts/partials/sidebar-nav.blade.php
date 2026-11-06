@php
    $propertiesActive = request()->routeIs(
        'organization.properties.*',
        'organization.buildings.*',
        'organization.floors.*',
        'organization.rooms.*',
        'organization.room-types.*',
        'organization.room-amenities.*',
    );

    $tenantsActive = request()->routeIs('organization.tenants.*');
    $reservationsActive = request()->routeIs('organization.reservations.*');
    $listingMessagesActive = request()->routeIs('organization.contact-messages.*');

    $leasingOpen = request()->routeIs('organization.contracts.*', 'organization.contract-templates.*');
    $leasingActive = $leasingOpen;

    $billingOpen = request()->routeIs('organization.invoices.*', 'organization.payments.*', 'organization.payment-submissions.*', 'organization.billing.settings.*');
    $billingActive = $billingOpen;

    $expensesOpen = request()->routeIs('organization.expenses.*', 'organization.expense-categories.*', 'organization.financial-reports.*', 'organization.reports.*');
    $expensesActive = $expensesOpen;

    $utilitiesOpen = request()->routeIs('organization.utility-*');
    $utilitiesActive = $utilitiesOpen;

    $maintenanceOpen = request()->routeIs('organization.maintenance-*', 'organization.work-orders.*');
    $maintenanceActive = $maintenanceOpen;

    $adminActive = request()->routeIs('organization.users.*', 'organization.settings.*');
@endphp

<nav class="da-sidebar-nav nav flex-column px-2 py-2" aria-label="Main">
    @if(auth()->user()?->isSuperAdmin())
        <x-sidebar.nav-item
            :href="route('platform.dashboard')"
            icon="bi-house-door"
            label="Overview"
            :active="request()->routeIs('platform.dashboard')"
        />

        <x-sidebar.nav-section label="System management" />

        <x-sidebar.nav-item
            :href="route('platform.contact-messages.index')"
            icon="bi-envelope"
            label="Contact messages"
            :active="request()->routeIs('platform.contact-messages.*')"
        />

        <x-sidebar.nav-group
            id="nav-platform-orgs"
            label="Organizations"
            icon="bi-buildings"
            :open="request()->routeIs('platform.organizations.*')"
            :active="request()->routeIs('platform.organizations.*')"
        >
            <li>
                <x-sidebar.nav-sublink
                    :href="route('platform.organizations.index')"
                    icon="bi-list-ul"
                    label="All organizations"
                    :active="request()->routeIs('platform.organizations.index')"
                />
            </li>
        </x-sidebar.nav-group>

        <x-sidebar.nav-group
            id="nav-platform-settings"
            label="Settings"
            icon="bi-gear"
            :open="request()->routeIs('platform.settings.*')"
            :active="request()->routeIs('platform.settings.*')"
        >
            <li>
                <x-sidebar.nav-sublink
                    :href="route('platform.settings.listing-map.edit')"
                    icon="bi-map"
                    label="Listing map focus"
                    :active="request()->routeIs('platform.settings.listing-map.*')"
                />
            </li>
            <li>
                <x-sidebar.nav-sublink
                    :href="route('platform.settings.listing-content.edit')"
                    icon="bi-easel"
                    label="Listing page content"
                    :active="request()->routeIs('platform.settings.listing-content.*')"
                />
            </li>
        </x-sidebar.nav-group>
    @elseif(auth()->user()?->isTenant())
        @include('layouts.partials.portal-sidebar')
    @elseif(auth()->user()?->organization_id)
        <x-sidebar.nav-item
            :href="route('organization.dashboard')"
            icon="bi-house-door"
            label="Overview"
            :active="request()->routeIs('organization.dashboard')"
        />

        <x-sidebar.nav-section label="Property & occupancy" />

        @can('viewAny', App\Models\Property::class)
            <x-sidebar.nav-item
                :href="route('organization.properties.index')"
                icon="bi-buildings"
                label="Properties"
                :active="$propertiesActive"
            />
        @endcan

        @can('viewAny', App\Models\Tenant::class)
            <x-sidebar.nav-item
                :href="route('organization.tenants.index')"
                icon="bi-people"
                label="Tenants"
                :active="$tenantsActive"
            />
        @endcan

        @can('viewAny', App\Models\Reservation::class)
            <x-sidebar.nav-item
                :href="route('organization.reservations.index')"
                icon="bi-calendar2-check"
                label="Reservations"
                :active="$reservationsActive"
                :badge-count="$organizationPendingReservationCount ?? 0"
            />
        @endcan

        @can('viewOrganizationInbox', App\Models\PlatformContactMessage::class)
            <x-sidebar.nav-item
                :href="route('organization.contact-messages.index')"
                icon="bi-envelope-paper"
                label="Listing messages"
                :active="$listingMessagesActive"
            />
        @endcan

        @can('viewAny', App\Models\MaintenanceRequest::class)
            <x-sidebar.nav-section label="Maintenance" />
            <x-sidebar.nav-group id="nav-maintenance" label="Maintenance" icon="bi-tools" :open="$maintenanceOpen" :active="$maintenanceActive">
                <li><x-sidebar.nav-sublink :href="route('organization.maintenance-requests.index')" icon="bi-chat-left-text" label="Requests" :active="request()->routeIs('organization.maintenance-requests.*')" /></li>
                <li><x-sidebar.nav-sublink :href="route('organization.work-orders.index')" icon="bi-clipboard-check" label="Work orders" :active="request()->routeIs('organization.work-orders.*')" /></li>
                <li><x-sidebar.nav-sublink :href="route('organization.maintenance-history.index')" icon="bi-clock-history" label="History" :active="request()->routeIs('organization.maintenance-history.*')" /></li>
            </x-sidebar.nav-group>
        @endcan

        @can('viewAny', App\Models\UtilityMeter::class)
            <x-sidebar.nav-section label="Utilities" />
            <x-sidebar.nav-group id="nav-utilities" label="Utilities" icon="bi-lightning-charge" :open="$utilitiesOpen" :active="$utilitiesActive">
                <li><x-sidebar.nav-sublink :href="route('organization.utility-readings.index')" icon="bi-speedometer" label="Readings" :active="request()->routeIs('organization.utility-readings.*')" /></li>
                <li><x-sidebar.nav-sublink :href="route('organization.utility-meters.index')" icon="bi-diagram-3" label="Meters" :active="request()->routeIs('organization.utility-meters.*')" /></li>
                <li><x-sidebar.nav-sublink :href="route('organization.utility-rates.index')" icon="bi-tag" label="Rates" :active="request()->routeIs('organization.utility-rates.*')" /></li>
                <li><x-sidebar.nav-sublink :href="route('organization.utility-reports.index')" icon="bi-bar-chart" label="Reports" :active="request()->routeIs('organization.utility-reports.*')" /></li>
            </x-sidebar.nav-group>
        @endcan

        @if(auth()->user()->can('viewAny', App\Models\Contract::class) || auth()->user()->can('viewAny', App\Models\Invoice::class))
            <x-sidebar.nav-section label="Leasing & billing" />

            @can('viewAny', App\Models\Contract::class)
                <x-sidebar.nav-group id="nav-leasing" label="Leasing" icon="bi-file-earmark-text" :open="$leasingOpen" :active="$leasingActive">
                    <li>
                        <x-sidebar.nav-sublink
                            :href="route('organization.contracts.index')"
                            icon="bi-file-earmark-text"
                            label="Contracts"
                            :active="request()->routeIs('organization.contracts.*')"
                        />
                    </li>
                    <li>
                        <x-sidebar.nav-sublink
                            :href="route('organization.contract-templates.index')"
                            icon="bi-file-earmark-richtext"
                            label="Templates"
                            :active="request()->routeIs('organization.contract-templates.*')"
                        />
                    </li>
                </x-sidebar.nav-group>
            @endcan

            @can('viewAny', App\Models\Invoice::class)
                <x-sidebar.nav-group id="nav-billing" label="Billing" icon="bi-wallet2" :open="$billingOpen" :active="$billingActive">
                    <li>
                        <x-sidebar.nav-sublink
                            :href="route('organization.invoices.index')"
                            icon="bi-receipt"
                            label="Invoices"
                            :active="request()->routeIs('organization.invoices.*')"
                        />
                    </li>
                    <li>
                        <x-sidebar.nav-sublink
                            :href="route('organization.payments.index')"
                            icon="bi-cash-coin"
                            label="Payments"
                            :active="request()->routeIs('organization.payments.*')"
                        />
                    </li>
                    <li>
                        <x-sidebar.nav-sublink
                            :href="route('organization.payment-submissions.index')"
                            icon="bi-inbox"
                            label="Portal submissions"
                            :active="request()->routeIs('organization.payment-submissions.*')"
                        />
                    </li>
                    <li>
                        <x-sidebar.nav-sublink
                            :href="route('organization.billing.settings.edit')"
                            icon="bi-gear"
                            label="Settings"
                            :active="request()->routeIs('organization.billing.settings.*')"
                        />
                    </li>
                </x-sidebar.nav-group>
            @endcan
        @endif

        @if(auth()->user()->can('viewAny', App\Models\Expense::class) || auth()->user()->can('viewFinancialReports') || auth()->user()->can('viewOrganizationReports'))
            <x-sidebar.nav-section label="Finance & reports" />

            @can('viewOrganizationReports')
                <x-sidebar.nav-item
                    :href="route('organization.reports.hub')"
                    icon="bi-pie-chart"
                    label="Reports hub"
                    :active="request()->routeIs('organization.reports.*')"
                />
            @endcan

            <x-sidebar.nav-group id="nav-expenses" label="Expenses" icon="bi-journal-minus" :open="$expensesOpen" :active="$expensesActive">
                @can('viewAny', App\Models\Expense::class)
                    <li>
                        <x-sidebar.nav-sublink
                            :href="route('organization.expenses.index')"
                            icon="bi-list-check"
                            label="All expenses"
                            :active="request()->routeIs('organization.expenses.*')"
                        />
                    </li>
                    <li>
                        <x-sidebar.nav-sublink
                            :href="route('organization.expense-categories.index')"
                            icon="bi-tags"
                            label="Categories"
                            :active="request()->routeIs('organization.expense-categories.*')"
                        />
                    </li>
                @endcan
                @can('viewFinancialReports')
                    <li>
                        <x-sidebar.nav-sublink
                            :href="route('organization.financial-reports.summary')"
                            icon="bi-graph-up"
                            label="Financial summary"
                            :active="request()->routeIs('organization.financial-reports.*')"
                        />
                    </li>
                @endcan
            </x-sidebar.nav-group>
        @endif

        @if(auth()->user()->can('viewAny', App\Models\User::class) || auth()->user()->can('manage organization settings'))
            <x-sidebar.nav-section label="Administration" />

            @can('viewAny', App\Models\User::class)
                <x-sidebar.nav-item
                    :href="route('organization.users.index')"
                    icon="bi-person-badge"
                    label="Team"
                    :active="request()->routeIs('organization.users.*')"
                />
            @endcan

            @can('manage organization settings')
                <x-sidebar.nav-item
                    :href="route('organization.settings.map.edit')"
                    icon="bi-geo-alt"
                    label="Map settings"
                    :active="request()->routeIs('organization.settings.*')"
                />
            @endcan
        @endif

        @can('viewAny', App\Models\Announcement::class)
            <x-sidebar.nav-item
                :href="route('organization.announcements.index')"
                icon="bi-megaphone"
                label="Announcements"
                :active="request()->routeIs('organization.announcements.*')"
            />
        @endcan
    @endif
</nav>
