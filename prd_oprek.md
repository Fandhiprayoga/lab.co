# PRD Open Rekrutmen Asisten Lab per Lab dan Tahun Akademik

## Overview
Fitur Open Rekrutmen Asisten Lab (Oprek) memungkinkan setiap laboratorium membuka rekrutmen berdasarkan tahun akademik dan periode tertentu. Proses berjalan end-to-end mulai dari pembukaan oprek oleh laboran, pendaftaran mahasiswa, verifikasi berkas, seleksi multi-komponen dengan bobot dinamis, verifikasi akhir, hingga pengumpulan berkas onboarding untuk peserta yang dinyatakan diterima.

Tujuan utama:
- Menstandarkan proses rekrutmen asisten lintas lab.
- Menyediakan transparansi status pendaftar dan progres seleksi.
- Memastikan proses penilaian dapat dikonfigurasi sesuai kebutuhan tiap lab.
- Mengurangi proses manual dokumen dan rekap nilai.

Ruang lingkup:
- Rekrutmen per lab, per tahun akademik, per gelombang/periode.
- Aktor utama: laboran, asisten aktif, mahasiswa pendaftar.
- Integrasi dengan akun/otorisasi yang sudah ada pada sistem.

Non-goal (fase awal):
- Integrasi tanda tangan digital pihak ketiga bersertifikasi.
- Pembayaran atau manajemen insentif asisten.
- Otomasi sinkronisasi data akademik eksternal (KHS/KTM dari SIAKAD API).

## Requirements
### Functional Requirements
- Laboran dapat membuat, mengubah, mempublikasikan, menutup, dan mengarsipkan oprek.
- Oprek wajib terikat ke lab dan tahun akademik.
- Oprek memiliki periode pendaftaran (tanggal mulai dan akhir).
- Mahasiswa dapat mendaftar selama periode aktif dengan upload berkas wajib:
- CV.
- KTM.
- KHS.
- Surat pernyataan komitmen.
- Laboran memverifikasi berkas pendaftar (approve/reject/revisi) dengan catatan.
- Sistem mendukung komponen seleksi dinamis yang dapat diatur laboran:
- Contoh: tertulis, praktikum, wawancara, microteaching, test lain.
- komponen seleksi terikat dengan oprek
- Setiap komponen dapat diaktifkan/nonaktifkan.
- Setiap komponen memiliki bobot penilaian.
- Penilaian dapat dilakukan oleh laboran dan asisten aktif (sesuai otorisasi) di lab yang terikat dalam oprek.
- Sistem menghitung nilai akhir berdasarkan bobot komponen.
- Verifikasi akhir kandidat dilakukan oleh laboran dan asisten aktif.
- Kandidat diterima diminta upload berkas kelengkapan:
- Nomor rekening.
- Tanda tangan digital (gambar/berkas).
- Halaman depan buku tabungan.
- Sistem mengelola status kandidat dari awal sampai akhir.
- Sistem menyimpan audit trail perubahan penting (status, verifikasi, nilai).

### Non-Functional Requirements
- Keamanan upload berkas (validasi tipe, ukuran, antivirus hook opsional).
- Kontrol akses berbasis role (laboran, asisten aktif, mahasiswa, admin).
- Ketersediaan jejak audit untuk kepatuhan proses.
- Performa untuk daftar pendaftar dan rekap nilai minimal 500+ pendaftar per oprek.
- Notifikasi status penting (pendaftaran sukses, revisi berkas, hasil seleksi, kelulusan).
- Penyimpanan berkas terstruktur per oprek dan kandidat.

### Business Rules
- Mahasiswa hanya boleh memiliki 1 submission aktif per oprek.
- Mahasiswa boleh mendaftar ke beberapa oprek berbeda jika kebijakan mengizinkan.
- Penutupan oprek otomatis menonaktifkan pendaftaran baru.
- Penilaian hanya dapat dilakukan untuk kandidat yang lolos verifikasi berkas.
- Kelulusan akhir hanya dapat diputuskan jika semua komponen wajib sudah dinilai.

## Core Features
- Manajemen Oprek
- CRUD oprek per lab dan tahun akademik.
- Pengaturan timeline dan kuota.
- Publikasi dan penutupan oprek.

- Pendaftaran Mahasiswa
- Form pendaftaran terstruktur.
- Upload dokumen wajib.
- Validasi data dan dokumen.
- Tracking status pendaftaran.

- Verifikasi Berkas
- Inbox verifikasi untuk laboran.
- Keputusan: diterima, revisi, ditolak.
- Catatan feedback per dokumen.

- Builder Komponen Seleksi Dinamis
- Tambah/ubah/hapus komponen seleksi.
- Atur bobot, urutan, dan jenis skor.
- Toggle komponen aktif/nonaktif.

- Penilaian Multi-Penilai
- Rubrik nilai per komponen.
- Penilai: laboran dan asisten aktif.
- Rekap nilai otomatis dan ranking kandidat.

- Verifikasi Akhir
- Review komprehensif hasil dan catatan.
- Persetujuan akhir oleh laboran dan asisten aktif.
- Publikasi hasil diterima/tidak.

- Onboarding Dokumen Kandidat Diterima
- Input nomor rekening.
- Upload tanda tangan digital.
- Upload halaman depan buku tabungan.
- Status kelengkapan onboarding.

- Notifikasi dan Audit
- Notifikasi berbasis event.
- Audit log aktivitas kritikal.

## User Flow
1. Laboran membuat oprek baru dengan memilih lab, tahun akademik, periode, dan aturan dasar.
2. Laboran mempublikasikan oprek.
3. Mahasiswa melihat oprek aktif dan mengisi form pendaftaran.
4. Mahasiswa mengunggah CV, KTM, KHS, surat pernyataan komitmen.
5. Sistem menandai pendaftaran sebagai menunggu verifikasi berkas.
6. Laboran memverifikasi berkas.
7. Jika berkas kurang, laboran mengembalikan untuk revisi dan mahasiswa melakukan perbaikan.
8. Jika berkas valid, kandidat lanjut ke tahap seleksi.
9. Laboran menyusun komponen seleksi dinamis (tertulis, praktikum, wawancara, microteaching, test lain) dan bobot.
10. Laboran dan asisten aktif memberi nilai kandidat per komponen.
11. Sistem menghitung nilai akhir dan menyiapkan rekap.
12. Laboran dan asisten aktif melakukan verifikasi akhir.
13. Hasil akhir diumumkan (diterima/tidak diterima).
14. Kandidat diterima mengunggah nomor rekening, tanda tangan digital, dan halaman depan buku tabungan.
15. Laboran memverifikasi kelengkapan onboarding dan menetapkan status final siap aktif.

## Architecture
Arsitektur aplikasi mengikuti pola modular monolith (CodeIgniter 4) dengan pemisahan domain rekrutmen.

Komponen utama:
- Presentation Layer
- Controller untuk manajemen oprek, pendaftaran, verifikasi, seleksi, onboarding.
- View server-rendered + komponen UI dinamis untuk builder seleksi.

- Application Layer
- Service OprekService: lifecycle oprek.
- Service RegistrationService: pendaftaran dan validasi.
- Service VerificationService: verifikasi berkas.
- Service SelectionConfigService: konfigurasi komponen seleksi.
- Service ScoringService: input nilai, agregasi, ranking.
- Service FinalizationService: verifikasi akhir dan publikasi hasil.
- Service OnboardingService: kelengkapan kandidat diterima.

- Domain Layer
- Entity Oprek, OprekRequirement, CandidateApplication, CandidateDocument, SelectionComponent, ScoreEntry, FinalDecision, OnboardingProfile.

- Data Layer
- Model CodeIgniter untuk setiap entity.
- Repository pattern opsional untuk query kompleks rekap dan ranking.

- Supporting Layer
- Authorization gate per role.
- Notification dispatcher (email/in-app).
- Audit logger.
- File storage manager.

Integrasi internal:
- Module auth/role existing.
- Module tahun akademik existing.
- Module lab/fakultas existing.

## Database Schema
### Main Tables
- oprek_campaigns
- id (PK)
- lab_id (FK labs)
- academic_year_id (FK academic_years)
- period_name
- registration_start_at
- registration_end_at
- quota
- status (draft, published, closed, archived)
- created_by
- created_at
- updated_at

- oprek_requirements
- id (PK)
- campaign_id (FK oprek_campaigns)
- requirement_key (cv, ktm, khs, commitment_letter)
- is_required
- created_at
- updated_at

- oprek_applications
- id (PK)
- campaign_id (FK oprek_campaigns)
- student_id (FK users/students)
- form_payload (JSON)
- application_status (submitted, doc_revision, doc_rejected, doc_verified, in_selection, failed, accepted, onboarding_pending, onboarding_complete)
- submitted_at
- updated_at

- oprek_application_documents
- id (PK)
- application_id (FK oprek_applications)
- document_type (cv, ktm, khs, commitment_letter, signature, passbook_front)
- file_path
- file_name
- mime_type
- file_size
- is_verified
- verification_note
- verified_by
- verified_at
- created_at

- oprek_selection_components
- id (PK)
- campaign_id (FK oprek_campaigns)
- component_name
- component_key
- is_required
- is_active
- weight_percentage
- max_score
- sort_order
- created_at
- updated_at

- oprek_component_assessors
- id (PK)
- component_id (FK oprek_selection_components)
- assessor_user_id (FK users)
- assessor_role (laboran, active_assistant)
- created_at

- oprek_scores
- id (PK)
- application_id (FK oprek_applications)
- component_id (FK oprek_selection_components)
- assessor_user_id (FK users)
- score_value
- note
- scored_at
- created_at
- updated_at

- oprek_final_decisions
- id (PK)
- application_id (FK oprek_applications)
- decision_status (accepted, rejected, waitlist)
- final_score
- decided_by
- decision_note
- decided_at
- created_at

- oprek_onboarding_profiles
- id (PK)
- application_id (FK oprek_applications)
- bank_account_number
- bank_account_name
- signature_document_id (FK oprek_application_documents nullable)
- passbook_document_id (FK oprek_application_documents nullable)
- onboarding_status (pending, submitted, verified, revision)
- verified_by
- verified_at
- created_at
- updated_at

- oprek_activity_logs
- id (PK)
- campaign_id (FK oprek_campaigns nullable)
- application_id (FK oprek_applications nullable)
- actor_user_id
- action_type
- action_payload (JSON)
- created_at

### Key Constraints and Indexes
- Unique (campaign_id, student_id) pada oprek_applications.
- Index campaign_id di seluruh tabel turunan.
- Index application_status untuk listing cepat.
- Index (component_id, assessor_user_id) pada oprek_scores.
- Check constraint weight_percentage total per campaign = 100 (divalidasi aplikasi + DB jika memungkinkan).

## Tech Stack
- Backend
- PHP 8.2+.
- CodeIgniter 4.
- MySQL/MariaDB.

- Frontend
- Server-rendered views (CI4) + JavaScript untuk komponen dinamis builder seleksi dan scoring grid.
- Library upload progress opsional.

- Storage
- Local storage pada writable/uploads/oprek atau object storage (S3-compatible) untuk produksi.

- Security
- CSRF protection bawaan CI4.
- File validation (extension, MIME, size).
- Role-based authorization.

- Testing
- PHPUnit (unit + integration).
- Seed data untuk skenario oprek.

- Observability
- Application logging CI4.
- Audit table oprek_activity_logs.

## Deployment
Strategi deployment bertahap:

1. Dev
- Tambah migration tabel oprek.
- Tambah seeder role/permission tambahan.
- Deploy modul oprek ke environment development.

2. Staging
- Jalankan migration dan seed.
- Uji end-to-end flow: publish oprek, pendaftaran, verifikasi, seleksi, finalisasi, onboarding.
- UAT bersama laboran dan perwakilan asisten aktif.

3. Production
- Backup database.
- Jalankan migration dengan maintenance window.
- Aktifkan fitur via feature flag.
- Monitoring log error dan metrik upload pasca go-live.

Kebutuhan konfigurasi:
- Path penyimpanan dokumen oprek.
- Batas ukuran file per tipe dokumen.
- Daftar MIME yang diizinkan.
- Template notifikasi email/in-app.

Rencana rilis:
- Phase 1: Campaign, pendaftaran, verifikasi berkas.
- Phase 2: Builder seleksi dinamis, scoring multi-penilai, final decision.
- Phase 3: Onboarding dokumen kandidat diterima dan penyempurnaan laporan.
