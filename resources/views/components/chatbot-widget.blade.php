@hasPermission('VIEW_CHATBOT')
    @if (!request()->routeIs('chatbot.index'))
        <div id="chatbotWidget" class="position-fixed" style="right: 1.25rem; bottom: 1.25rem; z-index: 1080;">
            <button type="button" id="chatbotToggle" class="btn btn-primary rounded-circle shadow" aria-label="Buka chatbot"
                style="width: 56px; height: 56px;">
                <i class="fas fa-robot"></i>
            </button>

            <div id="chatbotPanel" class="card shadow d-none"
                style="width: min(380px, calc(100vw - 2rem)); height: min(70vh, 560px); min-height: 320px;">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-robot"></i>
                        <strong>Asisten InvenGo</strong>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="chatbotUsageRefresh" class="btn btn-sm btn-link text-white p-0"
                            title="Muat ulang statistik request" aria-label="Muat ulang statistik request">
                            <i class="fas fa-rotate"></i>
                        </button>
                        <a href="{{ route('chatbot.index') }}" class="text-white" title="Buka halaman chatbot"
                            aria-label="Buka halaman chatbot">
                            <i class="fas fa-up-right-from-square"></i>
                        </a>
                        <button type="button" id="chatbotClose" class="btn btn-sm btn-link text-white p-0"
                            aria-label="Tutup chatbot">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div id="chatbotUsageBar" class="px-3 py-1 border-bottom bg-white small text-muted"
                    title="Angka dari aplikasi ini (estimasi), bukan kuota resmi Google AI Studio.">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span id="chatbotUsageDaily">Hari ini: -</span>
                        <span id="chatbotUsageRemaining">Sisa: -</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span id="chatbotUsageTotal">Total: -</span>
                        <span id="chatbotUsageMinute">Menit ini: -</span>
                    </div>
                    <div class="text-end fst-italic" style="font-size: 0.68rem;">estimasi aplikasi ini</div>
                </div>

                <div id="chatbotMessages" class="card-body overflow-auto d-flex flex-column gap-2"
                    style="flex: 1 1 auto; min-height: 0; background-color: #f8f9fa;" aria-live="polite"></div>

                <div id="chatbotSuggestions" class="px-3 pt-2 d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-sm btn-outline-primary chatbot-suggestion"
                        data-question="Berapa barang rusak?">Berapa barang rusak?</button>
                    <button type="button" class="btn btn-sm btn-outline-primary chatbot-suggestion"
                        data-question="Barang normal ada berapa?">Barang normal ada berapa?</button>
                    <button type="button" class="btn btn-sm btn-outline-primary chatbot-suggestion"
                        data-question="Berapa jumlah grup inventaris?">Jumlah grup inventaris?</button>
                    @hasPermission('VIEW_BORROW')
                        <button type="button" class="btn btn-sm btn-outline-primary chatbot-suggestion"
                            data-question="Siapa yang sedang meminjam?">Siapa yang sedang meminjam?</button>
                    @endhasPermission
                </div>

                <div id="chatbotTyping" class="px-3 pb-2 text-muted small d-none">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    mengetik...
                </div>

                <form id="chatbotForm" class="card-footer bg-white">
                    <div class="input-group">
                        <input type="text" id="chatbotInput" class="form-control" maxlength="1000"
                            placeholder="Tanya tentang inventaris..." autocomplete="off" required>
                        <button type="submit" id="chatbotSend" class="btn btn-primary" aria-label="Kirim pesan">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <style>
            #chatbotMessages .chatbot-bubble {
                max-width: 85%;
                white-space: pre-wrap;
                word-break: break-word;
            }
        </style>

        <script src="{{ asset('assets/js/chatbot-markdown.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const widget = document.getElementById('chatbotWidget');

                if (!widget) {
                    return;
                }

                const toggle = document.getElementById('chatbotToggle');
                const close = document.getElementById('chatbotClose');
                const panel = document.getElementById('chatbotPanel');
                const messages = document.getElementById('chatbotMessages');
                const typing = document.getElementById('chatbotTyping');
                const form = document.getElementById('chatbotForm');
                const input = document.getElementById('chatbotInput');
                const send = document.getElementById('chatbotSend');
                const usageBar = document.getElementById('chatbotUsageBar');
                const usageDaily = document.getElementById('chatbotUsageDaily');
                const usageRemaining = document.getElementById('chatbotUsageRemaining');
                const usageTotal = document.getElementById('chatbotUsageTotal');
                const usageMinute = document.getElementById('chatbotUsageMinute');
                const usageRefresh = document.getElementById('chatbotUsageRefresh');
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

                    usageDaily.textContent = 'Aplikasi hari ini: ' + usage.global_hari_ini + '/' + formatLimit(usage.limit_harian);
                    usageRemaining.textContent = 'Sisa: ' + (usage.sisa_global_hari_ini === null ? '∞' : usage.sisa_global_hari_ini);
                    usageTotal.textContent = 'Total aplikasi: ' + usage.global_total;
                    usageMinute.textContent = 'Aplikasi/menit: ' + usage.global_menit_ini + '/' + usage.limit_per_menit;

                    const dailyExceeded = usage.limit_harian > 0 && usage.global_hari_ini >= usage.limit_harian;
                    const minuteExceeded = usage.global_menit_ini >= usage.limit_per_menit;

                    usageDaily.classList.toggle('text-danger', dailyExceeded);
                    usageRemaining.classList.toggle('text-danger', dailyExceeded);
                    usageMinute.classList.toggle('text-danger', minuteExceeded);

                    usageBar.title = 'Anda hari ini: ' + usage.request_hari_ini +
                        ' • Menit ini (Anda): ' + usage.request_menit_ini + '/' + usage.limit_per_menit +
                        ' • Total Anda: ' + usage.request_total +
                        ' • Limit harian: ' + formatLimit(usage.limit_harian) +
                        ' • Limit per menit: ' + usage.limit_per_menit +
                        ' • Model: ' + (usage.model || '-') +
                        ' • Reset harian: 00:00 Pacific' +
                        ' • Sumber: aplikasi ini (estimasi), bukan kuota resmi Google AI Studio' +
                        (usage.terakhir_request ? ' • Request terakhir: ' + usage.terakhir_request : '');
                }

                async function refreshUsage() {
                    try {
                        const response = await fetch(usageEndpoint, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            return;
                        }

                        const payload = await response.json();

                        if (payload?.success) {
                            applyUsage(payload.data);
                        }
                    } catch (error) {
                        return;
                    }
                }

                function setOpen(open) {
                    panel.classList.toggle('d-none', !open);
                    toggle.classList.toggle('d-none', open);

                    if (open) {
                        input.focus();
                        messages.scrollTop = messages.scrollHeight;
                        refreshUsage();
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

                toggle.addEventListener('click', () => setOpen(true));
                close.addEventListener('click', () => setOpen(false));
                usageRefresh.addEventListener('click', refreshUsage);

                widget.querySelectorAll('.chatbot-suggestion').forEach(function(button) {
                    button.addEventListener('click', function() {
                        if (sending) {
                            return;
                        }

                        sendMessage(button.getAttribute('data-question') || button.textContent.trim());
                    });
                });

                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    const text = input.value.trim();

                    if (text === '' || sending) {
                        return;
                    }

                    input.value = '';
                    sendMessage(text);
                });

                appendMessage('Halo! Saya asisten InvenGo. Tanyakan seputar inventaris, peminjaman, atau fitur website ini.', false);
            });
        </script>
    @endif
@endhasPermission
