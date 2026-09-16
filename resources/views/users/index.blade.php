<x-app-layout>
    <div class="max-w-5xl mx-auto py-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-bold">Pengguna</h1>
            <a href="{{ route('users.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Tambah</a>
        </div>

        @if (session('status'))
            <div class="mb-4 p-2 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-2">Nama</th>
                    <th class="p-2">Email</th>
                    <th class="p-2">Role</th>
                    <th class="p-2">NIM/NIP</th>
                    <th class="p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-t">
                        <td class="p-2">{{ $user->name }}</td>
                        <td class="p-2">{{ $user->email }}</td>
                        <td class="p-2">{{ $user->role }}</td>
                        <td class="p-2">{{ $user->nim_nip }}</td>
                        <td class="p-2 space-x-2">
                            <a href="{{ route('users.show', $user) }}">Lihat</a>
                            <a href="{{ route('users.edit', $user) }}">Edit</a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $users->links() }}
    </div>
</x-app-layout>
