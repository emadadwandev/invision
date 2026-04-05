<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Invision' }} - Invision SaaS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        .sidebar-scrollbar::-webkit-scrollbar { width: 4px; }
        .sidebar-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }

        /* Sidebar width states */
        .sidebar-expanded { width: 16rem; }
        .sidebar-collapsed { width: 4.5rem; }

        /* Sidebar visibility */
        .sidebar-hidden { transform: translateX(-100%); }
        .sidebar-visible { transform: translateX(0); }

        /* On desktop (lg+), sidebar is always visible */
        @media (min-width: 1024px) {
            .sidebar-desktop-visible { transform: translateX(0) !important; }
        }
        /* On mobile, collapsed sidebar still uses full width */
        @media (max-width: 1023px) {
            .sidebar-collapsed { width: 16rem; }
        }

        /* Main content padding */
        @media (min-width: 1024px) {
            .main-sidebar-expanded { padding-left: 16rem; }
            .main-sidebar-collapsed { padding-left: 4.5rem; }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100" x-data="{ sidebarOpen: true, mobileSidebar: false, profileMenu: false }">

    @auth
    {{-- Mobile overlay --}}
    <div x-show="mobileSidebar" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-600/75 z-40 lg:hidden" @click="mobileSidebar = false"></div>

    {{-- Sidebar --}}
    <aside :class="[
        mobileSidebar ? 'sidebar-visible' : 'sidebar-hidden',
        sidebarOpen ? 'sidebar-expanded' : 'sidebar-collapsed',
        'sidebar-desktop-visible'
    ]" class="fixed inset-y-0 left-0 z-50 flex flex-col bg-gray-900 transition-all duration-300 ease-in-out overflow-hidden">
        {{-- Logo area --}}
        <div class="flex h-16 items-center justify-between px-4 border-b border-gray-800">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-indigo-600 flex-shrink-0">
                    <span class="text-sm font-bold text-white">I</span>
                </span>
                <span x-show="sidebarOpen || mobileSidebar" x-transition class="text-lg font-bold text-white whitespace-nowrap">Invision</span>
            </a>
            {{-- Collapse toggle (desktop only) --}}
            <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:flex items-center justify-center h-7 w-7 rounded-md text-gray-400 hover:text-white hover:bg-gray-800 transition-colors">
                <svg x-show="sidebarOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                <svg x-show="!sidebarOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
            </button>
            {{-- Close button (mobile only) --}}
            <button @click="mobileSidebar = false" class="lg:hidden flex items-center justify-center h-7 w-7 rounded-md text-gray-400 hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 min-h-0 overflow-y-auto sidebar-scrollbar px-3 py-4 space-y-6">
            {{-- Main --}}
            <div>
                <p x-show="sidebarOpen || mobileSidebar" class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Main</p>
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Dashboard</span>
                </a>
            </div>

            {{-- Management --}}
            <div>
                <p x-show="sidebarOpen || mobileSidebar" class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Management</p>
                <div class="space-y-1">
                    <a href="{{ route('users.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Users</span>
                    </a>
                    <a href="{{ route('teams.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('teams.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Teams</span>
                    </a>
                </div>
            </div>

            {{-- Operations --}}
            <div>
                <p x-show="sidebarOpen || mobileSidebar" class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Operations</p>
                <div class="space-y-1">
                    <a href="{{ route('stores.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('stores.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Stores</span>
                    </a>
                    <a href="{{ route('geography.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('geography.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Geography</span>
                    </a>
                    <a href="{{ route('products.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('products.*') || request()->routeIs('product-categories.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Products</span>
                    </a>
                    <a href="{{ route('routes.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('routes.*') || request()->routeIs('route-instances.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Routes</span>
                    </a>
                    <a href="{{ route('sales.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('sales.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Sales</span>
                    </a>
                    <a href="{{ route('pos.transactions') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('pos.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">POS</span>
                    </a>
                </div>
            </div>

            {{-- Field --}}
            <div>
                <p x-show="sidebarOpen || mobileSidebar" class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Field</p>
                <div class="space-y-1">
                    <a href="{{ route('tracking.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('tracking.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Tracking</span>
                    </a>
                    <a href="{{ route('command-center.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('command-center.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Command Center</span>
                    </a>
                    <a href="{{ route('campaigns.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('campaigns.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Campaigns</span>
                    </a>
                    <a href="{{ route('campaigns.materials') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('campaigns.material*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">POSM</span>
                    </a>
                    <a href="{{ route('geofence.settings') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('geofence.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Geo-Fence</span>
                    </a>
                </div>
            </div>

            {{-- Analytics --}}
            <div>
                <p x-show="sidebarOpen || mobileSidebar" class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Analytics</p>
                <div class="space-y-1">
                    <a href="{{ route('reports.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('reports.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Reports</span>
                    </a>
                    <a href="{{ route('inquiry.stores') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('inquiry.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Inquiry</span>
                    </a>
                    <a href="{{ route('competitors.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('competitors.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Competitors</span>
                    </a>
                </div>
            </div>

            {{-- System --}}
            <div>
                <p x-show="sidebarOpen || mobileSidebar" class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">System</p>
                <div class="space-y-1">
                    <a href="{{ route('notifications.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('notifications.*') || request()->routeIs('messages.*') || request()->routeIs('task-assignments.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Notifications</span>
                    </a>
                    <a href="{{ route('audit.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('audit.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span x-show="sidebarOpen || mobileSidebar" class="ml-3">Audit Trail</span>
                    </a>
                </div>
            </div>
        </nav>

        {{-- User profile at bottom of sidebar --}}
        <div class="border-t border-gray-800 p-3">
            <div class="relative" @click.outside="profileMenu = false">
                <button @click="profileMenu = !profileMenu" class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-indigo-600 flex-shrink-0">
                        <span class="text-sm font-medium text-white">{{ substr(auth()->user()->first_name, 0, 1) }}</span>
                    </span>
                    <span x-show="sidebarOpen || mobileSidebar" class="ml-3 text-left truncate">
                        <span class="block text-sm font-medium text-white">{{ auth()->user()->full_name }}</span>
                        <span class="block text-xs text-gray-400">{{ auth()->user()->role->label() }}</span>
                    </span>
                </button>
                <div x-show="profileMenu" x-transition class="absolute bottom-full left-0 mb-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                    <div class="py-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </aside>
    @endauth

    {{-- Main content wrapper --}}
    <div @auth :class="sidebarOpen ? 'main-sidebar-expanded' : 'main-sidebar-collapsed'" class="min-h-screen transition-all duration-300" @endauth>
        {{-- Top bar (mobile hamburger + breadcrumb area) --}}
        @auth
        <div class="sticky top-0 z-30 bg-white shadow-sm border-b border-gray-200 lg:hidden">
            <div class="flex items-center h-16 px-4">
                <button @click="mobileSidebar = true" class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="ml-3 text-lg font-bold text-indigo-600">Invision</span>
            </div>
        </div>
        @endauth

        @if(session('success'))
        <div class="px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-md bg-green-50 p-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <main class="py-6">
            <div class="px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
