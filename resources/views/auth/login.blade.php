<x-guest-layout>
    <style>
        .split-card {
            width: 100%;
            max-width: 960px;
            background: #ffffff;
            border-radius: 22px;
            box-shadow: 0 20px 48px -10px rgba(25, 45, 35, 0.14), 0 4px 12px rgba(25, 45, 35, 0.04);
            overflow: hidden;
            display: flex;
            flex-direction: row;
            border: 1px solid rgba(45, 74, 62, 0.12);
            margin: 0 auto;
        }

        .split-visual {
            width: 48%;
            position: relative;
            background: 
                linear-gradient(180deg, rgba(240, 246, 252, 0.92) 0%, rgba(240, 246, 252, 0.65) 35%, rgba(22, 45, 35, 0.85) 100%),
                url('{{ asset('images/itk-anime.jpg') }}') center center / cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2.25rem 2rem 1.5rem;
            overflow: hidden;
        }

        .split-form {
            width: 52%;
            padding: 2.75rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .mascot-container {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }

        .mascot-image {
            max-height: 290px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 14px 22px rgba(10, 30, 20, 0.38));
            transition: transform 0.25s ease;
        }

        .mascot-image:hover {
            transform: scale(1.03);
        }

        .demo-account-btn {
            padding: 8px 10px;
            background: #f4f8f6;
            border: 1px solid rgba(45, 74, 62, 0.18);
            border-radius: 9px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #205c48;
            cursor: pointer;
            transition: all 0.18s ease;
            text-align: center;
        }

        .demo-account-btn:hover {
            background: #205c48;
            color: #ffffff;
            border-color: #205c48;
            transform: translateY(-1px);
        }

        @media (max-width: 859px) {
            .split-card {
                flex-direction: column;
                max-width: 480px;
            }

            .split-visual {
                width: 100%;
                box-sizing: border-box;
                padding: 1.75rem 1.5rem 1.25rem;
                min-height: 280px;
            }

            .split-form {
                width: 100%;
                box-sizing: border-box;
                padding: 2rem 1.5rem;
            }

            .mascot-image {
                max-height: 200px;
            }
        }
    </style>

    <div class="split-card">
        
        {{-- SISI KIRI: Visual Kampus ITK & Maskot Karakter --}}
        <div class="split-visual">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(32, 92, 72, 0.14); padding: 4px 12px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; color: #143527; margin-bottom: 0.85rem; border: 1px solid rgba(32, 92, 72, 0.22); backdrop-filter: blur(4px);">
                    <span>Institut Teknologi Kalimantan &middot; KampusLMS A07</span>
                </div>
                
                <h2 style="font-size: 1.45rem; font-weight: 800; color: #162a22; line-height: 1.35; margin: 0; letter-spacing: -0.3px;">
                    Permudah interaksi perkuliahan <span style="color: #d96611;">Dosen</span> dan <span style="color: #1a6cb8;">Mahasiswa</span> secara online!
                </h2>
            </div>

            {{-- Maskot Tampil Penuh di Depan Background Kampus (Bukan Dalam Lingkaran) --}}
            <div class="mascot-container">
                <img src="{{ asset('images/mascot.png') }}" 
                     alt="Maskot Mahasiswa ITK" 
                     class="mascot-image">
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.72rem; color: #e4efe9; font-weight: 500; text-shadow: 0 1px 3px rgba(0,0,0,0.6);">
                <span>Gedung Terpadu Kampus ITK</span>
                <span>SI2514024 Pemrograman Web</span>
            </div>
        </div>

        {{-- SISI KANAN: Form Login Akun --}}
        <div class="split-form">
            
            {{-- Brand Header --}}
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1.25rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #205c48; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 4px 10px rgba(32, 92, 72, 0.25);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: #162d22; letter-spacing: -0.4px; line-height: 1.1;">
                        KampusLMS
                        <span style="font-size: 0.7rem; font-weight: 700; background: #e8f1ec; color: #205c48; padding: 2px 7px; border-radius: 999px; border: 1px solid rgba(32, 92, 72, 0.2); vertical-align: middle;">
                            A07
                        </span>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #162d22; margin: 0 0 4px 0; letter-spacing: -0.3px;">
                    Hai, selamat datang kembali
                </h1>
                <p style="font-size: 0.86rem; color: #62766c; margin: 0;">
                    Masuk untuk mengakses materi, pengumpulan tugas, dan nilai.
                </p>
            </div>

            {{-- Status Sesi & Pesan Sukses --}}
            @if (session('status'))
                <div style="background: #eef7f2; border: 1px solid #bce1ce; color: #1b5330; padding: 9px 12px; border-radius: 9px; font-size: 0.83rem; margin-bottom: 1.15rem;">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Pesan Error Validasi --}}
            @if ($errors->any())
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 9px 12px; border-radius: 9px; font-size: 0.83rem; margin-bottom: 1.15rem;">
                    <ul style="margin: 0; padding-left: 1.1rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form Login Utama --}}
            <form method="POST" action="{{ route('login') }}" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf

                {{-- Input Email --}}
                <div>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           placeholder="Contoh: email@kampuslms.test"
                           style="width: 100%; box-sizing: border-box; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.22); border-radius: 10px; font-family: inherit; font-size: 0.9rem; background: #fbfdfc; color: #1a2f25; outline: none; transition: border-color 0.2s;">
                </div>

                {{-- Input Password --}}
                <div>
                    <div style="position: relative;">
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               placeholder="Masukkan kata sandi kamu"
                               style="width: 100%; box-sizing: border-box; padding: 11px 40px 11px 14px; border: 1px solid rgba(45, 74, 62, 0.22); border-radius: 10px; font-family: inherit; font-size: 0.9rem; background: #fbfdfc; color: #1a2f25; outline: none; transition: border-color 0.2s;">
                        <button type="button" 
                                onclick="togglePasswordVisibility()" 
                                title="Lihat/Sembunyikan Kata Sandi"
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 4px; cursor: pointer; color: #647a6e;">
                            <svg id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Tombol Masuk Utama --}}
                <button type="submit" 
                        style="width: 100%; padding: 11px 16px; background: #205c48; color: #ffffff; border: none; border-radius: 10px; font-size: 0.92rem; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 14px rgba(32, 92, 72, 0.25);">
                    Masuk
                </button>

                {{-- Lupa Kata Sandi --}}
                <div style="display: flex; justify-content: flex-end;">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" style="font-size: 0.8rem; color: #1a6cb8; text-decoration: none; font-weight: 500;">
                            Lupa kata sandi?
                        </a>
                    @endif
                </div>

                {{-- Pilihan Masuk Akun Uji Coba Cepat (Demo) --}}
                <div style="margin-top: 0.4rem;">
                    <div style="font-size: 0.78rem; color: #62766c; margin-bottom: 0.5rem; text-align: center; font-weight: 500;">
                        Atau pilih akun demo:
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
                        <button type="button" 
                                class="demo-account-btn"
                                onclick="fillCredentials('dosen@kampuslms.test')">
                            Dosen
                        </button>
                        <button type="button" 
                                class="demo-account-btn"
                                onclick="fillCredentials('mahasiswa@kampuslms.test')">
                            Mahasiswa
                        </button>
                        <button type="button" 
                                class="demo-account-btn"
                                onclick="fillCredentials('admin@kampuslms.test')">
                            Admin
                        </button>
                    </div>
                </div>

                {{-- Ingat Perangkat Ini --}}
                <div style="display: flex; align-items: center; margin-top: 0.35rem;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.82rem; color: #44584e; cursor: pointer;">
                        <input id="remember_me" 
                               type="checkbox" 
                               name="remember"
                               style="width: 15px; height: 15px; accent-color: #205c48; cursor: pointer;">
                        <span>Ingat perangkat ini</span>
                    </label>
                </div>
            </form>

            {{-- Footer Tautan dan Legalitas --}}
            <div style="margin-top: 1.35rem; font-size: 0.75rem; color: #788d82; text-align: center; line-height: 1.45;">
                Dengan melanjutkan, kamu menyetujui Ketentuan Penggunaan KampusLMS Kelompok A07.
            </div>

            <div style="text-align: center; margin-top: 0.75rem;">
                <a href="{{ url('/') }}" style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.8rem; color: #205c48; text-decoration: none; font-weight: 600;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Kembali ke Halaman Depan</span>
                </a>
            </div>
        </div>

    </div>

    {{-- Script JavaScript Interaktif --}}
    <script>
        function fillCredentials(email) {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            if (emailInput && passInput) {
                emailInput.value = email;
                passInput.value = 'password';
                emailInput.style.borderColor = '#205c48';
                passInput.style.borderColor = '#205c48';
                emailInput.style.backgroundColor = '#ffffff';
                passInput.style.backgroundColor = '#ffffff';
            }
        }

        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (!passInput) return;

            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                passInput.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>
</x-guest-layout>
