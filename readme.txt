# Changelog
## About
Plugin untuk membuat event dan form pendaftaran dengan sistem submission.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2026-02-22
### Added
- **Replay Form**: Form "Watch the Replay" untuk event yang sudah selesai dengan embed YouTube video.
- **Email Notification**: Email konfirmasi otomatis dengan logo site, detail event, dan QR e-ticket untuk pendaftaran & replay.
- **QR E-Ticket System**: Generate QR code tiket untuk setiap pendaftar, halaman verifikasi tiket saat QR di-scan.
- **Pembicara & Moderator**: Meta box repeatable untuk menambah speaker/moderator per event dengan foto, nama, jabatan, dan role (Speaker, Moderator, atau custom role).
- **Target Audience**: Meta box repeatable untuk menambah target audience per event, ditampilkan sebagai pill badges di frontend.
- **Custom Form Fields**: Konfigurasi field formulir dari WP Admin per event — tambah/hapus/ubah label/tipe field. Field Nama & Email wajib ada (locked). Tipe yang didukung: Text, Email, Telepon, Angka, Textarea, Dropdown, Checkbox, Radio Button.
- **Custom Form Title & Subtitle**: Judul dan subtitle formulir bisa dikustomisasi per event dari WP Admin.
- **Google Form Embed**: Opsi menggunakan embed Google Form untuk formulir pendaftaran (hanya registrasi, replay tetap pakai form bawaan).
- **Phone Country Code**: Input telepon dengan country code selector (flag + dial code), default Indonesia (+62), menggunakan intl-tel-input library.
- **Form Placeholders**: Semua field formulir memiliki placeholder text otomatis.
- **Share Buttons**: Tombol bagikan event ke Facebook, LinkedIn, WhatsApp, X (Twitter), dan salin link — menggunakan icon SVG bulat dengan warna brand.
- **CSV Export**: Tombol export data pendaftar ke CSV dari halaman edit event.
- **Pagination**: Pagination di tabel pendaftar di admin (15 per halaman).
- **Delete Submission**: Hapus pendaftar individual dari tabel admin dengan konfirmasi.

### Changed
- Form pendaftaran dan replay ditampilkan inline di bawah halaman event (tidak pakai modal/popup), dengan smooth scroll dari tombol.
- Form fullwidth dengan border, border-radius, dan box shadow.
- Tabel admin pendaftar menampilkan kolom dinamis sesuai konfigurasi field event.
- Halaman tiket menampilkan field dinamis sesuai konfigurasi.
- Tombol share event didesain ulang dari dashicons menjadi icon SVG bulat dengan warna brand masing-masing platform.

### Database
- Kolom `form_type` (VARCHAR) ditambahkan ke tabel `wp_event_submissions` — membedakan submission registration vs replay.
- Kolom `custom_fields` (TEXT, JSON) ditambahkan ke tabel `wp_event_submissions` — menyimpan data custom field.
- Auto-migration pada `init` hook untuk backward compatibility.

## [1.0.1] - 2025-04-17
### Fixed
- Bug fixes on the event registration form.
- Improved email validation for the event page.

### Changed
- Updated UI for the event page.
