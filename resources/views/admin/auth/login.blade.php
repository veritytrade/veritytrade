<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white p-8 rounded shadow-md w-96">
    <a href="{{ route('home') }}" class="flex justify-center mb-6">
        <x-application-logo class="h-16 w-16 object-contain" />
    </a>
    <h2 class="text-2xl font-bold text-center mb-6">
        Admin Login
    </h2>

    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <input type="email"
               name="email"
               placeholder="Email"
               class="w-full border p-2 mb-4 rounded"
               required>

        <input type="password"
               name="password"
               placeholder="Password"
               class="w-full border p-2 mb-4 rounded"
               required>

        <button type="submit"
                class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">
            Login
        </button>
    </form>

</div>

</body>
</html>

