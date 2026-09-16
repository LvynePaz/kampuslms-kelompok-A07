<x-app-layout>
    <div class="max-w-xl mx-auto py-6">
        <h1 class="text-xl font-bold mb-2">{{ $user->name }}</h1>
        <p>Email: {{ $user->email }}</p>
        <p>Role: {{ $user->role }}</p>
        <p>NIM/NIP: {{ $user->nim_nip }}</p>
        <a href="{{ route('users.index') }}" class="inline-block mt-6 text-blue-600">Kembali</a>
    </div>
</x-app-layout>
