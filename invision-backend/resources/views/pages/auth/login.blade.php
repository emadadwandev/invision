<x-layouts.guest title="Sign In">
    <div class="bg-white py-8 px-6 shadow rounded-lg sm:px-10">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-indigo-600">Invision</h1>
            <p class="mt-2 text-sm text-gray-600">Sign in to your account</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input id="email" name="email" type="email" required autofocus
                       value="{{ old('email') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
                </div>
            </div>

            <div>
                <button type="submit"
                        :disabled="loading"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                    <span x-show="!loading">Sign in</span>
                    <span x-show="loading">Signing in...</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.guest>
