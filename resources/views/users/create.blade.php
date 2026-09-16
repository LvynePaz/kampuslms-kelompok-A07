<x-app-layout>
    <div class="max-w-xl mx-auto py-6">
        <h1 class="text-xl font-bold mb-4">Tambah Pengguna</h1>
        <form action="{{ route('users.store') }}" method="POST" class="space-y-3">
            @csrf
            @include('users._form')
            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Simpan</button>
        </form>
    </div>
</x-app-layout>
