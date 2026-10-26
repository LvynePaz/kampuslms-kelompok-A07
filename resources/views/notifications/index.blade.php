<x-layout title="Kotak Masuk Notifikasi">
    <style>
        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(45, 74, 62, 0.1);
        }

        .notif-title-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .notif-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a2f25;
            letter-spacing: -0.02em;
        }

        .notif-counter-badge {
            background: #e8f1ec;
            color: #243c32;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 999px;
            border: 1px solid rgba(45, 74, 62, 0.2);
        }

        .btn-mark-all {
            background: #ffffff;
            color: #2d4a3e;
            border: 1px solid rgba(45, 74, 62, 0.25);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-mark-all:hover {
            background: #e8f1ec;
            color: #1a2f25;
            border-color: #2d4a3e;
            transform: translateY(-1px);
        }

        .notif-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            margin-bottom: 2rem;
        }

        .notif-card {
            background: #ffffff;
            border: 1px solid rgba(45, 74, 62, 0.1);
            border-radius: 12px;
            padding: 1.15rem 1.35rem;
            display: flex;
            align-items: flex-start;
            gap: 1.15rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(35, 62, 49, 0.03);
            position: relative;
        }

        .notif-card:hover {
            box-shadow: 0 6px 18px rgba(35, 62, 49, 0.08);
            border-color: rgba(45, 74, 62, 0.25);
        }

        .notif-card.unread {
            background: #f4f9f6;
            border-left: 4px solid #2d4a3e;
            border-color: rgba(45, 74, 62, 0.22);
        }

        .notif-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #e8f1ec;
            color: #2d4a3e;
        }

        .notif-card.unread .notif-icon-box {
            background: #2d4a3e;
            color: #ffffff;
        }

        .notif-content {
            flex: 1;
            min-width: 0;
        }

        .notif-meta-top {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
            flex-wrap: wrap;
        }

        .notif-course-tag {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            background: rgba(45, 74, 62, 0.1);
            color: #243c32;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .notif-badge-new {
            font-size: 0.7rem;
            font-weight: 700;
            background: #ef4444;
            color: #ffffff;
            padding: 1px 6px;
            border-radius: 999px;
            letter-spacing: 0.3px;
        }

        .notif-time {
            font-size: 0.78rem;
            color: #647a6e;
            margin-left: auto;
        }

        .notif-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1a2f25;
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .notif-card-message {
            font-size: 0.88rem;
            color: #4b5e54;
            line-height: 1.5;
            margin-bottom: 0.75rem;
        }

        .notif-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-notif-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 14px;
            border-radius: 7px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-notif-primary {
            background: #2d4a3e;
            color: #ffffff;
        }

        .btn-notif-primary:hover {
            background: #1f352c;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-notif-secondary {
            background: #ffffff;
            color: #4b5e54;
            border: 1px solid rgba(45, 74, 62, 0.2);
        }

        .btn-notif-secondary:hover {
            background: #eef5f1;
            color: #1a2f25;
            border-color: #2d4a3e;
        }

        .empty-notif-state {
            text-align: center;
            padding: 4rem 2rem;
            background: #ffffff;
            border: 1px dashed rgba(45, 74, 62, 0.2);
            border-radius: 16px;
            color: #5e7166;
        }

        .empty-notif-state svg {
            margin: 0 auto 1.25rem;
            color: #8da698;
        }

        .empty-notif-state h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a2f25;
            margin-bottom: 0.5rem;
        }

        .empty-notif-state p {
            font-size: 0.9rem;
            max-width: 420px;
            margin: 0 auto;
        }
    </style>

    <div class="notif-header">
        <div class="notif-title-group">
            <h1 class="notif-title">Kotak Masuk Notifikasi</h1>
            <span class="notif-counter-badge" id="inbox-unread-count">
                {{ $unreadCount }} belum dibaca
            </span>
        </div>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read_all') }}">
                @csrf
                <button type="submit" class="btn-mark-all" title="Tandai semua notifikasi telah dibaca">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <div class="empty-notif-state">
            <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <h3>Kotak Masuk Kosong</h3>
            <p>Anda belum memiliki notifikasi. Pemberitahuan tugas baru atau nilai yang dipublikasikan akan muncul di sini secara otomatis.</p>
        </div>
    @else
        <div class="notif-list">
            @foreach ($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $data = $notification->data ?? [];
                    
                    // Fleksibel menangani payload bahasa Indonesia maupun Inggris dari Nova
                    $title = $data['title'] ?? $data['judul'] ?? 'Pemberitahuan Sistem';
                    $message = $data['message'] ?? $data['pesan'] ?? ($data['description'] ?? '');
                    $courseName = $data['course_name'] ?? $data['nama_mk'] ?? ($data['course_title'] ?? null);
                    $actionUrl = $data['url'] ?? $data['link'] ?? ($data['action_url'] ?? null);

                    $isAssignment = str_contains(strtolower($notification->type . ' ' . $title), 'assignment') 
                                 || str_contains(strtolower($notification->type . ' ' . $title), 'tugas');
                    $isGrade = str_contains(strtolower($notification->type . ' ' . $title), 'grade') 
                            || str_contains(strtolower($notification->type . ' ' . $title), 'nilai');
                @endphp

                <div class="notif-card {{ $isUnread ? 'unread' : 'read' }}">
                    <div class="notif-icon-box">
                        @if ($isGrade)
                            {{-- Ikon Nilai / Grade --}}
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        @elseif ($isAssignment)
                            {{-- Ikon Tugas / Assignment --}}
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        @else
                            {{-- Ikon Lonceng Standar --}}
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        @endif
                    </div>

                    <div class="notif-content">
                        <div class="notif-meta-top">
                            @if ($courseName)
                                <span class="notif-course-tag">{{ $courseName }}</span>
                            @endif

                            @if ($isUnread)
                                <span class="notif-badge-new">Baru</span>
                            @endif

                            <span class="notif-time" title="{{ $notification->created_at->format('d M Y H:i') }}">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <h2 class="notif-card-title">{{ $title }}</h2>

                        @if ($message)
                            <p class="notif-card-message">{{ $message }}</p>
                        @endif

                        <div class="notif-actions">
                            @if ($actionUrl)
                                @if ($isUnread)
                                    {{-- Jika unread dan ada URL: tandai dibaca lalu redirect ke URL --}}
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="redirect" value="{{ $actionUrl }}">
                                        <button type="submit" class="btn-notif-action btn-notif-primary">
                                            Buka Detail
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ $actionUrl }}" class="btn-notif-action btn-notif-primary">
                                        Buka Detail
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @endif
                            @endif

                            @if ($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-notif-action btn-notif-secondary" title="Tandai notifikasi ini telah dibaca">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Tandai Dibaca
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $notifications->links() }}
        </div>
    @endif
</x-layout>
