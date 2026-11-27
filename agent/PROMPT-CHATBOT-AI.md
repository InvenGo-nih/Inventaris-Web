# Perintah / Prompt: Menambahkan Chatbot AI ke Inventaris-Web

> File: `agent/PROMPT-CHATBOT-AI.md`
> Dipakai sebagai perintah siap tempel untuk agent coding di repository ini.
> Bahasa jawaban chatbot: **Bahasa Indonesia**.
> Provider AI: **Google Gemini API free tier** (Google AI Studio) — tidak perlu pakai kartu kredit untuk kuota gratis.
>
> **Catatan penting:** aturan "hanya menjawab website ini" di bawah adalah **perilaku chatbot yang akan dibuat**, bukan pembatasan bagi agent yang sedang mengerjakan prompt ini.

---

## 0. Konfigurasi manual yang WAJIB dilakukan oleh kamu

Agent coding **tidak bisa** melakukan ini tanpa keputusan/kredensial dari kamu. Lakukan sebelum/seed setelah kode jadi:

| # | Konfigurasi manual | Detail |
|---|---|---|
| 1 | Buat API key Gemini | Buka Google AI Studio → **Get API key** → buat key. Jika diminta, aktifkan **Generative Language API** pada project Google Cloud terkait. |
| 2 | Isi `.env` (lokal) | Tambahkan baris di bawah ke `.env`. **Jangan commit `.env`.** |
| 3 | Isi `.env.example` | Tambahkan **placeholder kosong** saja, bukan key asli. |
| 4 | Vercel | Project → **Settings → Environment Variables**, tambahkan key yang sama, lalu **Redeploy**. |
| 5 | Clear cache | Jalankan `php artisan config:clear && php artisan cache:clear` setelah `.env` berubah. |
| 6 | Permission chatbot | Jalankan migration permission yang dibuat di Prompt 4 (`php artisan migrate`). Jika tidak membuat migration, jalankan `php artisan db:seed --class=RolePermissionSeeder` — **hati-hati**, seeder ini menyamakan ulang role semua user dari `users.role_id`. |
| 7 | Model fallback | Default `gemini-2.5-flash`. Jika ada respons 404/model tidak tersedia di akunmu, ganti `GEMINI_MODEL` ke model `flash` gratis lain yang tersedia di AI Studio. |
| 8 | Kuota | Jika dapat HTTP 429, chatbot harus menampilkan pesan ramah, bukan error mentah. Ini bukan bug kode. |

Isi `.env` / `.env.example`:

```dotenv
GEMINI_API_KEY=
GEMINI_MODEL=gemini-2.5-flash
GEMINI_TIMEOUT=20
CHATBOT_ENABLED=true
```

Dependensi: **Guzzle sudah ada** di `composer.json`, jangan install ulang. Hanya jalankan `composer require guzzlehttp/guzzle` jika benar-benar tidak ada di vendor.

---

## 1. Prompt utama — bangun fitur chatbot AI

Salin seluruh blok di bawah sebagai perintah ke agent coding.

```text
Tambahkan fitur chatbot AI ke aplikasi Laravel 11 ini (Inventaris-Web / InvenGo). Ikuti konvensi yang sudah ada: Blade + Bootstrap 5 + jQuery/fetch, controller di app/Http/Controllers, service di app/Services, config di config/services.php, route di routes/web.php di dalam grup middleware auth, permission memakai middleware `permission:` (Spatie) dan helper `hasPermission()`. Jangan menambahkan Livewire/Inertia/Vue/React. Jangan menambah dependency baru kecuali Guzzle sudah tidak ada.

TUJUAN
Chatbot hanya menjawab pertanyaan yang berkaitan dengan WEBSITE INI: inventaris/barang, kondisi Normal vs Rusak, jumlah & grup inventaris, lokasi, peminjaman/pengembalian, keterlambatan, user/role/permission, dan fitur-fitur halaman website ini. Untuk pertanyaan di luar itu, balas PERSIS: "Maaf, saya hanya menjawab pertanyaan tentang website ini saja." Jangan mencoba menjawab topik umum, dunia, startup, resep, olahraga, dll.

ARSITEKTUR YANG DIMINTA
1. config/services.php: tambah entri `gemini` dengan `key => env('GEMINI_API_KEY')`, `model => env('GEMINI_MODEL', 'gemini-2.5-flash')`, `timeout => (int) env('GEMINI_TIMEOUT', 20)`, `enabled => env('CHATBOT_ENABLED', true)`.
2. app/Services/GeminiChatService.php: client Guzzle ke base URI `https://generativelanguage.googleapis.com/v1beta`. Method `ask(string $systemPrompt, string $question): string`. POST ke `models/{model}:generateContent` dengan query `key`. Body: `systemInstruction` berisi system prompt, `contents` = pertanyaan user dengan role `user`, `generationConfig` temperature 0.2, maxOutputTokens 512. Ambil `candidates[0].content.parts[0].text`. Jika key kosong, lempar RuntimeException dengan pesan "Kunci API Gemini belum dikonfigurasi. Tambahkan GEMINI_API_KEY di .env." Jika HTTP 401/403/429/404/5xx, kembalikan pesan ramah Bahasa Indonesia dan log error TANPA mencatat API key. Jangan pernah mengirim/menaruh API key di response, log, atau view.
3. app/Services/InventoryContextService.php: method `build(?\App\Models\User $user): array` yang menghasilkan ringkasan data (bukan dump seluruh tabel). Pakai `Cache::remember('chatbot:context:'.$user->id, 60, ...)` agar hemat query. Sertakan:
   - `tanggal_hari_ini` dari `now()->toDateString()`.
   - Total barang: `Inventaris::query()` tanpa soft-deleted; total unit = `sum('quantity')`.
   - Kondisi: jumlah baris & total unit untuk `condition = 'Normal'` dan `condition = 'Rusak'`; contoh maksimal 10 `broken_description` dari yang rusak.
   - Group inventaris: `group_inventaris.name`, `type`, jumlah baris, total quantity, jumlah normal, jumlah rusak. Relasi: `inventaris.group_inventaris_id -> group_inventaris.id`.
   - Peminjaman HANYA jika `hasPermission('VIEW_BORROW')`: jumlah `status = 'Dipinjam'`, jumlah `status = 'Dikembalikan'`, jumlah terlambat (`status = 'Dipinjam'` DAN `max_return_date < today`), serta 10 peminjaman terakhir berisi `borrow_by`, nama barang, quantity, `date_borrow`, `max_return_date`, `date_back`, `status`.
   - User HANYA jika `hasPermission('VIEW_USERS')`: daftar `users.name`, `users.email`, dan nama role dari `role_id`/Spatie. Jangan sertakan `password`, `remember_token`, `email_verified_at`, session, token, atau API key.
   - Role yang dikenal: `admin`, `teknisi`, `guru`; grup permission: `inventaris`, `peminjaman`, `scan`, `user`, `role`, `dashboard`, `cek`, `chatbot`.
   - Jika user tidak punya permission terkait, **jangan sertakan** bagian itu sama sekali (jangan kirim "null" atau "terbatas" yang membocorkan existence data).
4. System prompt disimpan sebagai konstanta di service atau config, berisi aturan persis ini (salin teksnya):
   "Kamu adalah asisten internal website Inventaris-Web (InvenGo). Jawab HANYA pertanyaan tentang website ini: inventaris/barang, kondisi Normal/Rusak, jumlah & grup inventaris, lokasi, peminjaman/pengembalian/keterlambatan, user/role/permission, dan fitur halaman website ini. Gunakan HANYA data konteks yang diberikan; jika data tidak ada, katakan tidak ada di konteks, jangan mengarang angka. Jika pertanyaan di luar topik, balas persis: Maaf, saya hanya menjawab pertanyaan tentang website ini saja. Jangan pernah menampilkan password, hash password, token, API key, cookie/session, atau data sensitif lain. Jangan mengubah/menghapus data dan jangan memberi perintah destruktif; cukup arahkan ke menu/route yang relevan. Jawab singkat dalam Bahasa Indonesia, maksimal 5 poin atau satu tabel kecil. Data peminjam ada di borrows.borrow_by (bukan users.user_id); status peminjaman Dipinjam/Dikembalikan; kondisi barang Normal/Rusak."
5. app/Http/Controllers/ChatbotController.php:
   - `index()` return `view('chatbot.index')`.
   - `ask(Request $request)`: validasi `message` => `required|string|max:1000`; cek `config('services.gemini.enabled')`, jika false kembalikan JSON `status => 'error', success => false, data.reply => 'Chatbot sedang dinonaktifkan.'` dengan HTTP 503. Bangun system prompt = aturan di poin 4 + `json_encode(InventoryContextService->build(auth()->user()))`. Panggil GeminiChatService. Kembalikan JSON shape yang konsisten dengan `DasboardController::chartData()` (`status`, `success`, `data`) dengan `data.reply` berisi jawaban. Tangkap Throwable, log tanpa key, kembalikan pesan ramah HTTP 200 agar UI tetap rapi.
6. routes/web.php: di dalam grup `Route::middleware('auth')`, tambahkan:
   - `GET /chatbot` name `chatbot.index` middleware `permission:VIEW_CHATBOT`.
   - `POST /chatbot/ask` name `chatbot.ask` middleware `['permission:VIEW_CHATBOT', 'throttle:10,1']`.
   Jangan buat `routes/api.php`, jangan pakai Sanctum. Endpoint memakai session web + CSRF seperti route lain.
7. Permission: buat migration baru `2026_11_27_000003_add_chatbot_permission.php` yang `Permission::updateOrCreate(['name' => 'VIEW_CHATBOT', 'guard_name' => $guard], ['permission_group' => 'chatbot', 'alias' => 'Lihat Chatbot'])` dan berikan ke role `admin`, `teknisi`, `guru` (pakai `givePermissionTo`, jangan `syncPermissions` agar permission lain tidak hilang). Update juga `database/seeders/RolePermissionSeeder.php` dengan entri permission yang sama agar seeder bareng tetap konsisten.
8. UI: buat `resources/views/chatbot/index.blade.php` dan `resources/views/components/chatbot-widget.blade.php`. Widget mengambang di kanan bawah memakai kelas Bootstrap 5 yang sudah ada, ikon Font Awesome, input + tombol Kirim, area riwayat, indikator "mengetik...". Bungkus dengan `@hasPermission('VIEW_CHATBOT')`. Include widget di `resources/views/layouts/app.blade.php` setelah `@include('components.form-modal')` (~baris 148). Kirim POST ke `route('chatbot.ask')` memakai `fetch` dengan header `X-CSRF-TOKEN` dari meta tag `csrf-token` yang sudah ada di layout (~baris 7). Jangan menambah CDN baru. Escape semua teks jawaban saat dirender (pakai `textContent`, bukan `innerHTML`). Batasi riwayat di browser maksimal 20 pesan.
9. Test Pest: `tests/Feature/ChatbotTest.php` dengan kasus: (a) user tanpa VIEW_CHATBOT dapat 403, (b) validasi message kosong/lebih dari 1000 gagal, (c) context TIDAK mengandung `password`, `remember_token`, `api_key`, (d) context user tanpa VIEW_USERS tidak mengandung email user, (e) off-topic dibalas dengan kalimat penolakan persis, (f) saat GeminiChatService di-mock, jawaban muncul di `data.reply`. Mock service via container binding; jangan memanggil API asli di test.
10. Verifikasi: jalankan `php artisan migrate`, `php artisan route:list --name=chatbot`, `php artisan test --filter=Chatbot`, lalu `./vendor/bin/pint --test`. Perbaiki jika gagal. Jangan commit perubahan.

KONDISI BERHASIL
- Chatbot muncul hanya untuk user dengan VIEW_CHATBOT.
- Pertanyaan seperti "Berapa barang rusak?", "Barang apa saja yang dipinjam?", "Siapa yang meminjam laptop?", "Berapa jumlah inventaris grup Komputer?" dijawab dari data DB.
- Pertanyaan seperti "Cuaca hari ini?", "Bikin resep nasi goreng", "Terjemahkan ini ke Jerman" dijawab persis: Maaf, saya hanya menjawab pertanyaan tentang website ini saja.
- Tidak ada API key, password hash, atau token yang muncul di UI/log/response.
```

---

## 2. Prompt tambahan — aturan penolakan topik (masukkan ke system prompt)

Jika ingin memperkuat bagian penolakan, pastikan system prompt memuat blok ini **tanpa diubah**:

```text
Aturan cakupan (paling penting):
- Topik diperbolehkan: inventaris/barang, kondisi Normal/Rusak, jumlah stok & unit, group inventaris, lokasi, peminjaman/pengembalian/keterlambatan, daftar peminjam (borrow_by), user/role/permission, QR/scan, pengecekan kondisi, dashboard, notifikasi, dan cara pakai halaman website ini.
- Topik dilarang: pertanyaan umum di luar website ini, saran bisnis umum, coding di luar repository ini, berita, hiburan, resep, olahraga, cuaca, translate, matematika bebas, atau permintaan mengubah/menghapus data.
- Untuk semua topik dilarang, jawab PERSIS dan HENTIKAN: "Maaf, saya hanya menjawab pertanyaan tentang website ini saja."
- Jangan menambahkan kalimat setelah penolakan, jangan memberi alternatif jawaban, jangan menjelaskan mengapa ditolak.
```

---

## 3. Contoh pertanyaan & jawaban yang diharapkan

| Pertanyaan user | Perilaku chatbot |
|---|---|
| `Berapa barang yang rusak sekarang?` | Jawab dari `condition = 'Rusak'`, sebut jumlah baris & total unit kalau keduanya ada di konteks. |
| `Barang normal ada berapa?` | Jawab dari `condition = 'Normal'`. |
| `Grup inventaris apa saja dan masing-masing berapa?` | Tampilkan tabel kecil: grup, jumlah barang, total unit, normal, rusak. |
| `Siapa saja yang sedang meminjam?` | Jika user punya `VIEW_BORROW`, sebut `borrow_by` + nama barang + `max_return_date`. Jika tidak, jangan mengaku tahu. |
| `Ada peminjaman yang terlambat?` | Hitung `status = 'Dipinjam'` dan `max_return_date < hari ini`. |
| `User apa saja di website ini?` | Jika punya `VIEW_USERS`, sebut nama + email + role. Tanpa itu, jangan tampilkan daftar. |
| `Siapa password admin?` | Tolak/don't answer: tidak boleh membocorkan credential. Balas penolakan atau "Data tersebut tidak tersedia di website ini." |
| `Cuaca di Jakarta gimana?` | `Maaf, saya hanya menjawab pertanyaan tentang website ini saja.` |
| `Tolong hapus semua barang rusak.` | Jangan eksekusi. Arahkan ke menu `cek`/`inventaris` jika punya permission, atau penolakan. |

---

## 4. Prompt verifikasi cepat (jalankan setelah implementasi)

```text
Periksa implementasi chatbot yang baru dibuat terhadap checklist ini dan perbaiki tanpa mengubah fitur lain:
1. Semua query inventaris mengecualikan `deleted_at` (soft delete).
2. `condition` yang dipakai persis 'Normal' dan 'Rusak'; status peminjaman persis 'Dipinjam' dan 'Dikembalikan'.
3. Tidak ada relasi `borrows.user_id` yang dipakai; peminjam = `borrows.borrow_by`.
4. Konteks dibatasi: maksimal 10 contoh broken_description dan 10 peminjaman terakhir.
5. Cache context dipakai dengan TTL 60 detik dan di-clear otomatis saat data berubah (boleh invalidate manual di controller inventaris/borrow/cek).
6. Tidak ada API key/GEMINI_API_KEY yang terekspos ke view, response JSON, atau log.
7. Penolakan off-topic persis: "Maaf, saya hanya menjawab pertanyaan tentang website ini saja."
8. Route POST punya CSRF (middleware web) + throttle.
9. Widget tidak render untuk user tanpa VIEW_CHATBOT.
10. Jalankan: php artisan route:list --name=chatbot, php artisan test --filter=Chatbot, ./vendor/bin/pint --test. Laporkan hasilnya.
```

---

## 5. Yang TIDAK boleh diminta ke agent

- Jangan memasang model berbayar / langganan tanpa konfirmasi.
- Jangan menyimpan riwayat chat ke database (belum diminta).
- Jangan memberi chatbot akses tulis/delete ke tabel mana pun.
- Jangan mengirim seluruh tabel user/barang ke Gemini; kirim ringkasan + contoh terbatas saja.
- Jangan menaruh API key di `vercel.json`, `config/`, atau file yang ter-commit.
- Jangan mengubah logika stok/peminjaman yang sudah ada; chatbot hanya **membaca** data.

---

## 6. Ringkasan file yang akan dibuat/diubah

| File | Aksi |
|---|---|
| `config/services.php` | Tambah entri `gemini` |
| `.env.example` | Tambah placeholder `GEMINI_*`, `CHATBOT_ENABLED` |
| `app/Services/GeminiChatService.php` | Baru — HTTP client Gemini |
| `app/Services/InventoryContextService.php` | Baru — ringkasan data + guard permission |
| `app/Http/Controllers/ChatbotController.php` | Baru — `index()` & `ask()` |
| `routes/web.php` | Tambah 2 route di grup `auth` |
| `database/migrations/2026_11_27_000003_add_chatbot_permission.php` | Baru — permission `VIEW_CHATBOT` |
| `database/seeders/RolePermissionSeeder.php` | Tambah entri permission |
| `resources/views/chatbot/index.blade.php` | Baru — halaman chat |
| `resources/views/components/chatbot-widget.blade.php` | Baru — widget mengambang |
| `resources/views/layouts/app.blade.php` | Include widget (~setelah baris 148) |
| `tests/Feature/ChatbotTest.php` | Baru — regression test |
