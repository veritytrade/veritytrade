<x-layouts.admin>
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold text-blue-700">
                        Admin Dashboard
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Overview of your core system</p>
                </div>
                
                @if(auth()->user()?->hasPermission('manage_deals'))
                <div class="flex gap-3">
                    <a href="{{ route('admin.deals.create') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm whitespace-nowrap text-center text-sm">
                        + Add Hot Deal
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Core stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">

            {{-- Packages in Transit --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Packages in Transit</p>
                        <p class="text-2xl md:text-3xl font-bold text-emerald-700">
                            {{ $packagesInTransit ?? 0 }}
                        </p>
                    </div>
                    <div class="bg-emerald-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('admin.shipments.index') }}"
                   class="text-sm text-emerald-700 hover:text-emerald-900 font-medium mt-3 inline-block">
                    View Shipments →
                </a>
            </div>

            {{-- Hot Deals Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Active Hot Deals</p>
                        <p class="text-2xl md:text-3xl font-bold text-red-700">
                            {{ \App\Models\Deal::available()->count() }}
                        </p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.098a1 1 0 00-.945 1.066c.124.642.367 1.246.67 1.802.488.897.598 1.963.598 3.118 0 2.114-.613 4.086-1.64 5.788 1.52 1.038 3.407 1.634 5.59 1.634 2.183 0 4.07-.596 5.59-1.634.945-.644 1.64-1.64 1.64-2.754 0-1.155-.11-2.122-.598-3.118.303-.556.546-1.16.67-1.802.108-.562.066-1.416-.398-2.654a9.768 9.768 0 00-.613-3.58 2.64 2.64 0 01-.945-1.067c-.214-.33-.403-.713-.57-1.116-.208-.422-.477-.75-.822-.98C12.847 2.93 12.65 2.7 12.395 2.553z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                @if(auth()->user()?->hasPermission('manage_deals'))
                <a href="{{ route('admin.deals.index') }}" 
                   class="text-sm text-red-600 hover:text-red-800 font-medium mt-3 inline-block">
                    View All →
                </a>
                @else
                <span class="text-sm text-red-600 font-medium mt-3 inline-block">View All →</span>
                @endif
            </div>

            {{-- Pending Invoice Requests --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Pending Invoice Requests</p>
                        <p class="text-2xl md:text-3xl font-bold {{ ($pendingInvoiceRequestsCount ?? 0) > 0 ? 'text-amber-700' : 'text-gray-600' }}">
                            {{ $pendingInvoiceRequestsCount ?? 0 }}
                        </p>
                    </div>
                    <div class="p-3 rounded-lg {{ ($pendingInvoiceRequestsCount ?? 0) > 0 ? 'bg-amber-100' : 'bg-gray-100' }}">
                        <svg class="w-6 h-6 {{ ($pendingInvoiceRequestsCount ?? 0) > 0 ? 'text-amber-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('admin.invoice-settings.edit') }}"
                   class="text-sm font-medium mt-3 inline-block {{ ($pendingInvoiceRequestsCount ?? 0) > 0 ? 'text-amber-700 hover:text-amber-900' : 'text-gray-600 hover:text-gray-800' }}">
                    Generate Invoices →
                </a>
            </div>
            @if(auth()->user()?->hasPermission('assign_shipment') && ($ordersWithoutShipment ?? 0) > 0)
                {{-- Orders Without Shipment --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Orders Without Shipment</p>
                            <p class="text-2xl md:text-3xl font-bold text-blue-700">
                                {{ $ordersWithoutShipment }}
                            </p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13L5.4 5M7 13l-2 7h14M10 17h4"/>
                            </svg>
                        </div>
                    </div>
                    <a href="{{ route('admin.orders.index', ['unassigned' => 1]) }}"
                       class="text-sm text-blue-700 hover:text-blue-900 font-medium mt-3 inline-block">
                        Assign Shipments →
                    </a>
                </div>
            @endif
            @if(auth()->user()?->hasPermission('view_tracking') && ($ordersWaitingSupplierLogistics ?? 0) > 0)
                {{-- Orders waiting supplier logistics code --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Waiting Supplier Logistics Code</p>
                            <p class="text-2xl md:text-3xl font-bold text-purple-700">
                                {{ $ordersWaitingSupplierLogistics }}
                            </p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8M8 11h8M8 15h5M6 3h12a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V5a2 2 0 012-2z"/>
                            </svg>
                        </div>
                    </div>
                    <a href="{{ route('admin.orders.index', ['queue' => 'sourcing']) }}"
                       class="text-sm text-purple-700 hover:text-purple-900 font-medium mt-3 inline-block">
                        Complete Mapping →
                    </a>
                </div>
            @endif
            @if(auth()->user()?->hasPermission('approve_orders') && ($ordersPendingApproval ?? 0) > 0)
                {{-- Orders Pending Approval --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Orders Pending Approval</p>
                            <p class="text-2xl md:text-3xl font-bold text-amber-700">
                                {{ $ordersPendingApproval }}
                            </p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                    </div>
                    <a href="{{ route('admin.orders.index') }}?status=pending_approval"
                       class="text-sm text-amber-700 hover:text-amber-900 font-medium mt-3 inline-block">
                        Review Orders →
                    </a>
                </div>
            @endif
            @if(auth()->user() && auth()->user()->hasPermission('approve_users'))
                {{-- Pending Approvals Card --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Pending Customer Approvals</p>
                            <p class="text-2xl md:text-3xl font-bold text-amber-700">
                                {{ \App\Models\User::where('is_approved', false)->whereHas('role', fn ($q) => $q->where('name', 'customer'))->count() }}
                            </p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9a3 3 0 116 0v1a2 2 0 002 2H6a2 2 0 002-2V9zM9 17h6"></path>
                            </svg>
                        </div>
                    </div>
                    <a href="{{ route('admin.registered-users.index') }}"
                       class="text-sm text-amber-700 hover:text-amber-900 font-medium mt-3 inline-block">
                        Review Approvals
                    </a>
                </div>
            @endif
        </div>

        {{-- Info Box & Health --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 p-4 md:p-6 rounded-lg">
            <h3 class="font-semibold text-blue-800 mb-3">📊 Dashboard Tips & Health</h3>
            <ul class="text-sm text-blue-700 space-y-2">
                <li>• Use Feature Flags to gradually roll out new modules</li>
                <li>• Customer approvals and staff permissions control access</li>
                <li>• Pending invoice requests older than 3 days: <span class="font-semibold">{{ $staleInvoiceRequestsCount ?? 0 }}</span></li>
                <li>• Orders on <span class="font-semibold">completed</span> shipments but not delivered: <span class="font-semibold">{{ $ordersOnCompletedShipmentsNotDelivered ?? 0 }}</span></li>
            </ul>
        </div>
    </div>
</x-layouts.admin>
