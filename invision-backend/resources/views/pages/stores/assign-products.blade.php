<x-layouts.app title="Assign Products — {{ $store->name }}">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('stores.index') }}" class="hover:text-indigo-600">Stores</a>
                <span>/</span>
                <a href="{{ route('stores.show', $store) }}" class="hover:text-indigo-600">{{ $store->name }}</a>
                <span>/</span>
                <span class="text-gray-700">Assign Products</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Assign Products</h1>
            <p class="mt-1 text-sm text-gray-600">Select which products are available at <span class="font-medium">{{ $store->name }}</span>.</p>
        </div>
        <a href="{{ route('stores.show', $store) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">
            Cancel
        </a>
    </div>

    <form method="POST" action="{{ route('stores.products.sync', $store) }}" id="assign-form">
        @csrf

        {{-- Toolbar --}}
        <div class="bg-white shadow rounded-lg px-5 py-4 mb-4 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between"
             x-data="productFilter()">
            <div class="flex gap-3 flex-1 flex-wrap">
                {{-- Search --}}
                <div class="relative flex-1 min-w-48">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
                    </svg>
                    <input type="text" x-model="search" placeholder="Search by name or SKU…"
                           class="pl-9 pr-4 py-2 border border-gray-300 rounded-md text-sm w-full focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                {{-- Category filter --}}
                <select x-model="category" class="border border-gray-300 rounded-md text-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span x-text="selectedCount"></span> of {{ $products->count() }} selected
            </div>
        </div>

        @if($products->isEmpty())
            <div class="bg-white shadow rounded-lg px-6 py-16 text-center text-gray-500">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
                No products available. Please create products first.
            </div>
        @else
        <div class="bg-white shadow rounded-lg overflow-hidden" x-data="productFilter()">
            {{-- Select-all header --}}
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center gap-3">
                <input type="checkbox" id="select-all"
                       @change="toggleAll($event.target.checked)"
                       :checked="allVisibleSelected()"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="select-all" class="text-sm font-medium text-gray-700 cursor-pointer">Select / Deselect all visible</label>
            </div>

            <ul class="divide-y divide-gray-100">
                @foreach($products as $product)
                <li class="px-5 py-4 hover:bg-gray-50 transition-colors"
                    x-show="matchesFilter('{{ addslashes($product->name) }}', '{{ $product->sku }}', '{{ $product->category_id }}')"
                    data-name="{{ strtolower($product->name) }}"
                    data-sku="{{ strtolower($product->sku ?? '') }}"
                    data-category="{{ $product->category_id }}">
                    <label class="flex items-center gap-4 cursor-pointer">
                        <input type="checkbox"
                               name="product_ids[]"
                               value="{{ $product->id }}"
                               {{ in_array($product->id, $assignedIds) ? 'checked' : '' }}
                               @change="updateCount()"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 flex-shrink-0">
                        {{-- Image placeholder --}}
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex-shrink-0 flex items-center justify-center overflow-hidden">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="" class="w-full h-full object-cover">
                            @else
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if($product->sku) <span class="font-mono">{{ $product->sku }}</span> &middot; @endif
                                {{ $product->category?->name ?? 'Uncategorised' }}
                            </p>
                        </div>
                        @if(in_array($product->id, $assignedIds))
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Assigned</span>
                        @endif
                    </label>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('stores.show', $store) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                Save Product Assignment
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
    function productFilter() {
        return {
            search: '',
            category: '',
            selectedCount: {{ count($assignedIds) }},

            matchesFilter(name, sku, categoryId) {
                const q = this.search.toLowerCase();
                if (q && !name.toLowerCase().includes(q) && !sku.toLowerCase().includes(q)) return false;
                if (this.category && String(categoryId) !== String(this.category)) return false;
                return true;
            },

            updateCount() {
                this.selectedCount = document.querySelectorAll('input[name="product_ids[]"]:checked').length;
            },

            toggleAll(checked) {
                document.querySelectorAll('li[x-show]').forEach(li => {
                    if (li.style.display !== 'none') {
                        const cb = li.querySelector('input[type="checkbox"]');
                        if (cb) cb.checked = checked;
                    }
                });
                this.updateCount();
            },

            allVisibleSelected() {
                const visible = [...document.querySelectorAll('li[x-show]')]
                    .filter(li => li.style.display !== 'none')
                    .map(li => li.querySelector('input[type="checkbox"]'))
                    .filter(Boolean);
                return visible.length > 0 && visible.every(cb => cb.checked);
            }
        };
    }
    </script>
    @endpush
</x-layouts.app>
