<x-guest-layout>
    <div style="width: 100%; max-width: 460px; margin: 0 auto;">
        
        {{-- Card Utama Login --}}
        <div style="background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(35, 62, 49, 0.08), 0 2px 8px rgba(35, 62, 49, 0.04); border: 1px solid rgba(45, 74, 62, 0.12); padding: 2.25rem 2rem; position: relative; overflow: hidden;">
            
            {{-- Header dengan Maskot & Identitas Brand --}}
            <div style="text-align: center; margin-bottom: 1.75rem;">
                <div style="display: inline-block; position: relative; margin-bottom: 1rem;">
                    <img src="{{ asset('images/mascot.jpg') }}" 
                         alt="Maskot KampusLMS" 
                         style="width: 86px; height: 86px; border-radius: 50%; object-fit: cover; border: 3px solid #2d4a3e; box-shadow: 0 6px 18px rgba(45, 74, 62, 0.18); display: block; margin: 0 auto; background: #ffffff;">
                    <span style="position: absolute; bottom: -4px; right: -4px; background: #2d4a3e; color: #ffffff; font-size: 0.68rem; font-weight: 700; padding: 2px 7px; border-radius: 999px; border: 2px solid #ffffff; letter-spacing: 0.3px;">
                        A07
                    </span>
                </div>
                
                <h1 style="font-size: 1.45rem; font-weight: 700; color: #1a2f25; margin: 0 0 6px 0; letter-spacing: -0.3px;">
                    Masuk ke KampusLMS
                </h1>
                <p style="font-size: 0.86rem; color: #5e7166; margin: 0; line-height: 1.4;">
                    Portal Pembelajaran Terpadu & Evaluasi Perkuliahan
                </p>
            </div>

            {{-- Alert Status Sesi --}}
            @if (session('status'))
                <div style="background: #eef7f2; border: 1px solid #bce1ce; color: #1b5330; padding: 10px 14px; border-radius: 10px; font-size: 0.84rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Alert Error Validasi --}}
            @if ($errors->any())
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 10px 14px; border-radius: 10px; font-size: 0.84rem; margin-bottom: 1.25rem;">
                    <div style="font-weight: 600; margin-bottom: 3px;">Kredensial tidak valid:</div>
                    <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.82rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form Login --}}
            <form method="POST" action="{{ route('login') }}" style="display: flex; flex-direction: column; gap: 1.15rem;">
                @csrf

                {{-- Input Email --}}
                <div>
                    <label for="email" style="display: block; font-size: 0.85rem; font-weight: 600; color: #1f352c; margin-bottom: 6px;">
                        Alamat Email
                    </label>
                    <div style="position: relative;">
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username"
                               placeholder="nama@kampuslms.test"
                               style="width: 100%; box-sizing: border-box; padding: 11px 14px 11px 38px; border: 1px solid rgba(45, 74, 62, 0.22); border-radius: 10px; font-family: inherit; font-size: 0.9rem; background: #fbfdfc; color: #1a2f25; outline: none; transition: all 0.2s;">
                        <svg width="17" height="17" fill="none" stroke="#5e7166" viewBox="0 0 24 24" aria-hidden="true" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                </div>

                {{-- Input Password --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="password" style="font-size: 0.85rem; font-weight: 600; color: #1f352c;">
                            Kata Sandi
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" style="font-size: 0.8rem; color: #2d4a3e; text-decoration: none; font-weight: 500;">
                                Lupa kata sandi?
                            </a>
                        @endif
                    </div>
                    <div style="position: relative;">
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               placeholder="Masukkan kata sandi akun"
                               style="width: 100%; box-sizing: border-box; padding: 11px 14px 11px 38px; border: 1px solid rgba(45, 74, 62, 0.22); border-radius: 10px; font-family: inherit; font-size: 0.9rem; background: #fbfdfc; color: #1a2f25; outline: none; transition: all 0.2s;">
                        <svg width="17" height="17" fill="none" stroke="#5e7166" viewBox="0 0 24 24" aria-hidden="true" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                </div>

                {{-- Remember Me --}}
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.84rem; color: #44584e; cursor: pointer;">
                        <input id="remember_me" 
                               type="checkbox" 
                               name="remember"
                               style="width: 16px; height: 16px; accent-color: #2d4a3e; cursor: pointer; border-radius: 4px;">
                        <span>Ingat sesi saya</span>
                    </label>
                </div>

                {{-- Tombol Submit --}}
                <button type="submit" 
                        style="width: 100%; padding: 12px 18px; background: #2d4a3e; color: #ffffff; border: none; border-radius: 10px; font-size: 0.92rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(45, 74, 62, 0.22); transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
                    <span>Masuk ke Sistem</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </button>
            </form>

            {{-- Helper Akun Demo Cepat (Sangat Membantu Demo / Evaluasi Dosen) --}}
            <div style="margin-top: 1.6rem; padding-top: 1.35rem; border-top: 1px solid rgba(45, 74, 62, 0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                    <span style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #2d4a3e;">
                        Akun Demo Uji Coba:
                    </span>
                    <span style="font-size: 0.75rem; color: #647a6e;">
                        Sandi: <code>password</code>
                    </span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
                    <button type="button" 
                            onclick="fillCredentials('dosen@kampuslms.test')" 
                            style="padding: 7px 8px; background: #eef5f1; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 8px; font-size: 0.78rem; font-weight: 600; color: #205c48; cursor: pointer; transition: all 0.15s; text-align: center;">
                        Dosen
                    </button>
                    <button type="button" 
                            onclick="fillCredentials('mahasiswa@kampuslms.test')" 
                            style="padding: 7px 8px; background: #eef5f1; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 8px; font-size: 0.78rem; font-weight: 600; color: #205c48; cursor: pointer; transition: all 0.15s; text-align: center;">
                        Mahasiswa
                    </button>
                    <button type="button" 
                            onclick="fillCredentials('admin@kampuslms.test')" 
                            style="padding: 7px 8px; background: #eef5f1; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 8px; font-size: 0.78rem; font-weight: 600; color: #205c48; cursor: pointer; transition: all 0.15s; text-align: center;">
                        Admin
                    </button>
                </div>
            </div>

            {{-- Link Kembali ke Beranda --}}
            <div style="text-align: center; margin-top: 1.25rem;">
                <a href="{{ url('/') }}" style="display: inline-flex; align-items: center; gap: 5px; font-size: 0.82rem; color: #647a6e; text-decoration: none; font-weight: 500; transition: color 0.15s;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Kembali ke Halaman Depan</span>
                </a>
            </div>
        </div>

        {{-- Footer Ringkas --}}
        <div style="text-align: center; margin-top: 1.25rem; font-size: 0.78rem; color: #788d82;">
            &copy; {{ date('Y') }} KampusLMS Kelompok A07 &middot; SI2514024 Pemrograman Web
        </div>
    </div>

    {{-- Script Pengisi Otomatis Akun Demo --}}
    <script>
        function fillCredentials(email) {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            if (emailInput && passInput) {
                emailInput.value = email;
                passInput.value = 'password';
                emailInput.style.borderColor = '#2d4a3e';
                passInput.style.borderColor = '#2d4a3e';
                emailInput.style.backgroundColor = '#ffffff';
                passInput.style.backgroundColor = '#ffffff';
            }
        }
    </script>
</x-guest-layout>
