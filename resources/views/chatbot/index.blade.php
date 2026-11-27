@extends('layouts.app')

@section('title', 'Chatbot')

@section('content')
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body py-2">
                    <div class="text-muted small">Request aplikasi hari ini</div>
                    <div class="h5 mb-0" id="pageUsageDaily">-</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body py-2">
                    <div class="text-muted small">Sisa limit lokal</div>
                    <div class="h5 mb-0" id="pageUsageRemaining">-</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body py-2">
                    <div class="text-muted small">Total aplikasi</div>
                    <div class="h5 mb-0" id="pageUsageTotal">-</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body py-2">
                    <div class="text-muted small">Request aplikasi menit ini</div>
                    <div class="h5 mb-0" id="pageUsageMinute">-</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <small class="text-muted" id="pageUsageNote">Memuat statistik request...</small>
        <button type="button" id="pageUsageRefresh" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-rotate me-1"></i>Muat ulang statistik
        </button>
    </div>

    <div class="progress mb-3" style="height: 6px;">
        <div id="pageUsageProgress" class="progress-bar" role="progressbar" style="width: 0%;"
            aria-label="Penggunaan limit harian chatbot" aria-valuemin="0" aria-valuemax="100"
            aria-valuenow="0"></div>
    </div>

    {{-- <div class="alert alert-light border py-2 px-3 small mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span>
                <i class="fas fa-circle-info me-1"></i>
                Angka request dihitung dari aktivitas chatbot aplikasi ini (estimasi), bukan kuota resmi Google AI Studio.
                Reset harian mengikuti tengah malam Pacific (00:00). Isi limit di
                <code>CHATBOT_DAILY_LIMIT</code> / <code>CHATBOT_MINUTE_LIMIT</code> sesuai halaman Rate Limit AI Studio.
            </span>
            <a href="https://aistudio.google.com/rate-limit" target="_blank" rel="noopener noreferrer"
                class="ms-auto text-decoration-none">
                Buka Rate Limit AI Studio <i class="fas fa-up-right-from-square ms-1"></i>
            </a>
        </div>
    </div> --}}

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-1"><i class="fas fa-robot me-2"></i>Chatbot InvenGo</h5>
                <small class="text-muted">Asisten untuk pertanyaan seputar inventaris dan fitur website ini.</small>
            </div>
        </div>
        <div class="card-body d-flex flex-column">
            <div id="chatPageMessages" class="border rounded bg-light p-3 d-flex flex-column gap-2"
                style="height: 45vh; min-height: 260px; overflow-y: auto;" aria-live="polite"></div>

            <div id="chatPageTyping" class="text-muted small mt-2 d-none">
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                mengetik...
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="button" class="btn btn-sm btn-outline-primary chat-page-suggestion"
                    data-question="Berapa barang rusak?">Berapa barang rusak?</button>
                <button type="button" class="btn btn-sm btn-outline-primary chat-page-suggestion"
                    data-question="Barang normal ada berapa?">Barang normal ada berapa?</button>
                <button type="button" class="btn btn-sm btn-outline-primary chat-page-suggestion"
                    data-question="Berapa jumlah grup inventaris?">Jumlah grup inventaris?</button>
                @hasPermission('VIEW_BORROW')
                    <button type="button" class="btn btn-sm btn-outline-primary chat-page-suggestion"
                        data-question="Siapa yang sedang meminjam?">Siapa yang sedang meminjam?</button>
                @endhasPermission
            </div>

            <form id="chatPageForm" class="d-flex gap-2 mt-3">
                <input type="text" id="chatPageInput" class="form-control" maxlength="1000"
                    placeholder="Tanya tentang inventaris..." autocomplete="off" required>
                <button type="submit" id="chatPageSend" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i>Kirim
                </button>
            </form>
        </div>
    </div>

    <style>
        #chatPageMessages .chatbot-bubble {
            max-width: 85%;
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chatbot-markdown.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.getElementById('chatPageMessages');
            const typing = document.getElementById('chatPageTyping');
            const form = document.getElementById('chatPageForm');
            const input = document.getElementById('chatPageInput');
            const send = document.getElementById('chatPageSend');
            const usageDaily = document.getElementById('pageUsageDaily');
            const usageRemaining = document.getElementById('pageUsageRemaining');
            const usageTotal = document.getElementById('pageUsageTotal');
            const usageMinute = document.getElementById('pageUsageMinute');
            const usageProgress = document.getElementById('pageUsageProgress');
            const usageNote = document.getElementById('pageUsageNote');
            const usageRefresh = document.getElementById('pageUsageRefresh');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const endpoint = @json(route('chatbot.ask'));
            const usageEndpoint = @json(route('chatbot.usage'));
            const maxMessages = 20;
            let sending = false;

            function appendMessage(text, fromUser) {
                const row = document.createElement('div');
                row.className = 'd-flex ' + (fromUser ? 'justify-content-end' : 'justify-content-start');

                const bubble = document.createElement('div');
                bubble.className = 'chatbot-bubble px-3 py-2 rounded-3 small ' +
                    (fromUser ? 'bg-primary text-white' : 'bg-white border');

                if (!fromUser && window.InvenGoChatbotMarkdown) {
                    window.InvenGoChatbotMarkdown.render(text, bubble);
                } else {
                    bubble.textContent = text;
                }

                row.appendChild(bubble);
                messages.appendChild(row);

                while (messages.children.length > maxMessages) {
                    messages.removeChild(messages.firstElementChild);
                }

                messages.scrollTop = messages.scrollHeight;
            }

            function formatLimit(limit) {
                return limit > 0 ? String(limit) : '∞';
            }

            function applyUsage(usage) {
                if (!usage) {
                    return;
                }

                usageDaily.textContent = usage.global_hari_ini + ' / ' + formatLimit(usage.limit_harian);
                usageRemaining.textContent = usage.sisa_global_hari_ini === null ? '∞' : usage.sisa_global_hari_ini;
                usageTotal.textContent = usage.global_total;
                usageMinute.textContent = usage.global_menit_ini + ' / ' + usage.limit_per_menit;

                const dailyExceeded = usage.limit_harian > 0 && usage.global_hari_ini >= usage.limit_harian;
                const minuteExceeded = usage.global_menit_ini >= usage.limit_per_menit;

                usageDaily.classList.toggle('text-danger', dailyExceeded);
                usageRemaining.classList.toggle('text-danger', dailyExceeded);
                usageMinute.classList.toggle('text-danger', minuteExceeded);

                const percent = usage.limit_harian > 0
                    ? Math.min(100, Math.round((usage.global_hari_ini / usage.limit_harian) * 100))
                    : 0;

                usageProgress.style.width = percent + '%';
                usageProgress.classList.toggle('bg-danger', dailyExceeded);
                usageProgress.setAttribute('aria-valuenow', String(percent));

                usageNote.textContent = 'Anda hari ini: ' + usage.request_hari_ini +
                    ' • Menit ini (Anda): ' + usage.request_menit_ini + ' / ' + usage.limit_per_menit +
                    ' • Total Anda: ' + usage.request_total +
                    ' • Model: ' + (usage.model || '-') +
                    ' • Reset harian: 00:00 Pacific' +
                    ' • Request terakhir: ' + (usage.terakhir_request || '-');
            }

            async function refreshUsage() {
                try {
                    const response = await fetch(usageEndpoint, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        usageNote.textContent = 'Tidak dapat memuat statistik request.';
                        return;
                    }

                    const payload = await response.json();

                    if (payload?.success) {
                        applyUsage(payload.data);
                    }
                } catch (error) {
                    usageNote.textContent = 'Tidak dapat memuat statistik request.';
                }
            }

            async function sendMessage(text) {
                appendMessage(text, true);
                sending = true;
                send.disabled = true;
                typing.classList.remove('d-none');
                messages.scrollTop = messages.scrollHeight;

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            message: text
                        }),
                    });

                    let payload = null;

                    try {
                        payload = await response.json();
                    } catch (error) {
                        payload = null;
                    }

                    let reply = payload?.data?.reply;

                    if (!reply && response.status === 422) {
                        reply = payload?.errors?.message?.[0] || 'Pesan tidak valid.';
                    }

                    if (!reply) {
                        reply = 'Maaf, chatbot sedang tidak dapat menjawab. Silakan coba lagi.';
                    }

                    if (payload?.data?.usage) {
                        applyUsage(payload.data.usage);
                    }

                    appendMessage(reply, false);
                } catch (error) {
                    appendMessage('Maaf, chatbot tidak dapat terhubung. Periksa koneksi lalu coba lagi.', false);
                } finally {
                    sending = false;
                    send.disabled = false;
                    typing.classList.add('d-none');
                    input.focus();
                }
            }

            usageRefresh.addEventListener('click', refreshUsage);

            form.addEventListener('submit', function(event) {
                event.preventDefault();

                const text = input.value.trim();

                if (text === '' || sending) {
                    return;
                }

                input.value = '';
                sendMessage(text);
            });

            document.querySelectorAll('.chat-page-suggestion').forEach(function(button) {
                button.addEventListener('click', function() {
                    if (sending) {
                        return;
                    }

                    sendMessage(button.getAttribute('data-question') || button.textContent.trim());
                });
            });

            refreshUsage();
            appendMessage('Halo! Saya asisten InvenGo. Tanyakan seputar inventaris, peminjaman, atau fitur website ini.', false);
        });
    </script>
@endpush
