<x-layouts.app title="Reports">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Reports</h1>
            <a href="{{ route('reports.builder') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Dynamic Report Builder
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Sell-Through --}}
            <a href="{{ route('reports.show', 'sell-through') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition p-6">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Sell-Through</h3>
                </div>
                <p class="text-sm text-gray-500">Weekly POS sell-through transactions grouped by product. Track channel performance.</p>
            </a>

            {{-- Sell-Out --}}
            <a href="{{ route('reports.show', 'sell-out') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition p-6">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Sell-Out</h3>
                </div>
                <p class="text-sm text-gray-500">Weekly sell-out from stores to end consumers. Measures demand at retail level.</p>
            </a>

            {{-- Sell-In --}}
            <a href="{{ route('reports.show', 'sell-in') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition p-6">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Sell-In</h3>
                </div>
                <p class="text-sm text-gray-500">Delivered sales orders grouped by product. Track sales rep order volumes.</p>
            </a>

            {{-- Stock Movement --}}
            <a href="{{ route('reports.show', 'stock-movement') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition p-6">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Stock Movement</h3>
                </div>
                <p class="text-sm text-gray-500">Stock ins, outs, adjustments, and returns by store and product.</p>
            </a>

            {{-- Vendor Ranking --}}
            <a href="{{ route('reports.show', 'vendor-ranking') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition p-6">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Vendor Ranking</h3>
                </div>
                <p class="text-sm text-gray-500">Store/vendor rankings by sales volume, order count, and average order value.</p>
            </a>

            {{-- Sales Rep Performance --}}
            <a href="{{ route('reports.show', 'sales-rep-performance') }}" class="block bg-white rounded-lg shadow hover:shadow-md transition p-6">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Sales Rep Performance</h3>
                </div>
                <p class="text-sm text-gray-500">Sales rep rankings with order count, revenue, stores visited, and route completion.</p>
            </a>
        </div>
    </div>
</x-layouts.app>
