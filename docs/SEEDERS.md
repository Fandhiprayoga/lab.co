# Catatan Seeder & Urutan Eksekusi

## Prasyarat

Semua migrasi harus sudah dijalankan sebelum seeder:

```bash
php spark migrate --all
```

## Daftar Seeder

| # | Seeder | Tabel | Dependensi |
|---|--------|-------|------------|
| 1 | `UserSeeder` | `users` (Shield) | - |
| 2 | `FacultySeeder` | `faculties` | - |
| 3 | `StudyProgramSeeder` | `study_programs` | FacultySeeder |
| 4 | `UnitSeeder` | `units` | - |
| 5 | `LabSeeder` | `labs` | - |
| 6 | `AssetCategorySeeder` | `asset_categories` | - |
| 7 | `AssetSeeder` | `lab_assets` | LabSeeder, AssetCategorySeeder |
| 8 | `ConsumableCategorySeeder` | `consumable_categories` | - |
| 9 | `ConsumableItemSeeder` | `consumable_items` | UnitSeeder, LabSeeder, ConsumableCategorySeeder |
| 10 | `ConsumableSeeder` | (orchestrator) | Memanggil #4, #8, #9 secara berurutan |
| 11 | `GenerateConsumableRequestUuids` | `consumable_requests` | Data existing (migration fix) |

## Urutan Eksekusi

### Jalankan satu per satu

```bash
# 1. User & autentikasi
php spark db:seed UserSeeder

# 2. Data akademik
php spark db:seed FacultySeeder
php spark db:seed StudyProgramSeeder

# 3. Lab
php spark db:seed LabSeeder

# 4. Aset (butuh LabSeeder + AssetCategorySeeder)
php spark db:seed AssetCategorySeeder
php spark db:seed AssetSeeder

# 5. BHP / Consumable (butuh UnitSeeder + LabSeeder + ConsumableCategorySeeder)
php spark db:seed ConsumableSeeder

# 6. Fix UUID (opsional, hanya jika ada data lama tanpa UUID)
php spark db:seed GenerateConsumableRequestUuids
```

### Jalankan semua sekaligus (script)

```bash
php spark db:seed UserSeeder && \
php spark db:seed FacultySeeder && \
php spark db:seed StudyProgramSeeder && \
php spark db:seed LabSeeder && \
php spark db:seed AssetCategorySeeder && \
php spark db:seed AssetSeeder && \
php spark db:seed ConsumableSeeder
```

## Dependency Graph

```
UserSeeder
FacultySeeder ──> StudyProgramSeeder
UnitSeeder ──────────────────────────────┐
LabSeeder ──> AssetSeeder                │
AssetCategorySeeder ─> AssetSeeder       │
ConsumableCategorySeeder ────────────────┤
                                         v
                              ConsumableItemSeeder
                              (dipanggil via ConsumableSeeder)
```

## Catatan Penting

- Semua seeder kecuali `UserSeeder` dan `UnitSeeder` hanya jalan di `ENVIRONMENT=development`
- `FacultySeeder` dan `LabSeeder` menggunakan upsert (update jika sudah ada, insert jika belum)
- `AssetCategorySeeder` melakukan **truncate** sebelum insert (reset data)
- `ConsumableSeeder` adalah orchestrator yang memanggil `UnitSeeder` → `ConsumableCategorySeeder` → `ConsumableItemSeeder`
- `GenerateConsumableRequestUuids` bukan seeder data baru, hanya backfill UUID untuk data existing

## Default Login (dari UserSeeder)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@example.com | password123 |
| Laboran | laboran@example.com | password123 |
| Asisten Lab | asisten@example.com | password123 |
| Kepala Lab | kepalab@example.com | password123 |
| Dosen | dosen@example.com | password123 |
| Mahasiswa | mahasiswa@example.com | password123 |
