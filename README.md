# ElectronicSheet

Aplikasi manajemen dokumen berbasis web dengan editor TinyMCE dan integrasi AI menggunakan Google Gemini API.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi Database](#konfigurasi-database)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Struktur Aplikasi](#struktur-aplikasi)
- [Penggunaan](#penggunaan)
- [API Endpoints](#api-endpoints)
- [Troubleshooting](#troubleshooting)

## Fitur Utama

### Manajemen Dokumen
- Editor TinyMCE dengan toolbar lengkap
- Format dokumen: A4, F4, Legal, Letter
- Orientasi: Portrait dan Landscape
- Auto-save konten dengan format HTML
- Export ke format DOCX
- Versioning dokumen otomatis
- Status tracking: Draft, In Progress, Under Review, Completed, Archived

### Integrasi AI
- AI Writing Assistant dengan Google Gemini API
- Model tersedia: Gemini 2.5 Flash, Gemini 2.5 Pro
- Fitur AI: Write, Continue, Improve, Summarize, Translate, Expand
- Knowledge Base kustomizable
- Contextual AI menu saat select text
- Token management (Balance, Maximum, Custom)

### File Manager
- Upload dan download file
- Organisasi folder hierarkis
- Preview file dan thumbnail
- Rename, move, delete operations
- Manajemen user directory terpisah

### Sistem User
- Role-based access control
- User management untuk admin
- Sistem referral
- Activity logging
- Single session middleware

## Persyaratan Sistem

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL/MariaDB
- Web server (Apache/Nginx)

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/risunCode/ElectronicSheet.git
cd ElectronicSheet
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Copy Environment File

```bash
cp .env.example .env
```

## Konfigurasi Database

### 1. Buat Database

```sql
CREATE DATABASE electronic_sheet;
```

### 2. Konfigurasi Database di .env

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=electronic_sheet
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Jalankan Migration dan Seeder

```bash
# Jalankan migration
php artisan migrate

# Jalankan seeder untuk data awal
php artisan db:seed
```

## Konfigurasi Environment

### 1. Gemini AI API (Opsional)

Dapatkan API key dari [Google AI Studio](https://makersuite.google.com/app/apikey):

```env
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-2.5-flash
```

### 2. TinyMCE API Key (Opsional)

Dapatkan API key dari [TinyMCE](https://www.tiny.cloud/):

```env
TINYMCE_API_KEY=your_tinymce_api_key
```

### 3. Storage Configuration

```env
FILESYSTEM_DISK=local
```

### 4. Create Storage Link

```bash
php artisan storage:link
```

## Menjalankan Aplikasi

### Development Server

```bash
# Jalankan Laravel server
php artisan serve

# Compile assets (terminal terpisah)
npm run dev
```

Akses aplikasi di: `http://localhost:8000`

### Production Build

```bash
# Build assets untuk production
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Struktur Aplikasi

### Controllers

- `DocumentController` - CRUD operasi dokumen
- `FileManagerController` - Manajemen file dan folder
- `AIController` - Integrasi Gemini API
- `UserController` - Manajemen user
- `FileController` - Operasi file

### Models

- `Document` - Model dokumen dengan versioning
- `User` - Model user dengan role system
- `File` - Model file management
- `Folder` - Model struktur folder
- `DocumentVersion` - Model versioning dokumen
- `Template` - Model template dokumen
- `ActivityLog` - Model logging aktivitas

### Views

- `documents/` - Interface dokumen
- `filemanager/` - Interface file manager
- `users/` - Interface user management
- `dashboard.blade.php` - Dashboard utama

## Penggunaan

### Login Default

Setelah menjalankan seeder:

**Admin:**
- Email: `admin@example.com`
- Password: `password`

**User:**
- Email: `user@example.com`
- Password: `password`

### Membuat Dokumen

1. Login ke aplikasi
2. Pilih "Documents" di menu
3. Klik "Buat Dokumen"
4. Pilih template (opsional)
5. Isi title dan description
6. Pilih storage path
7. Klik "Buat Dokumen"

### Menggunakan Editor

1. Editor TinyMCE akan terbuka
2. Gunakan toolbar untuk formatting
3. Set format halaman di sidebar kanan
4. Gunakan AI Assistant untuk bantuan penulisan
5. Auto-save aktif secara otomatis

### Export Dokumen

1. Buka dokumen di editor
2. Klik tombol "Export to DOCX" di sidebar
3. File akan didownload otomatis

### File Manager

1. Akses via menu "File Manager"
2. Upload file dengan drag & drop
3. Buat folder dengan klik "Create Folder"
4. Navigate menggunakan breadcrumb

## API Endpoints

### AI Endpoints

```
POST /api/ai/generate
POST /api/ai/models
GET  /api/ai/status
```

### Document Endpoints

```
GET    /documents
POST   /documents
GET    /documents/{id}
PUT    /documents/{id}
DELETE /documents/{id}
```

### File Manager Endpoints

```
GET    /filemanager
POST   /filemanager/upload
POST   /filemanager/folder
PUT    /filemanager/rename
DELETE /filemanager/{path}
```

## Troubleshooting

### Error Permission Denied

```bash
# Set permission untuk storage dan cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Gemini API Error

1. Pastikan `GEMINI_API_KEY` sudah diset di .env
2. Check kuota API di Google AI Studio
3. Pastikan model yang digunakan tersedia

### TinyMCE Loading Issue

1. Check `TINYMCE_API_KEY` di .env
2. Pastikan koneksi internet stabil
3. Check browser console untuk error

### Database Connection Error

1. Pastikan MySQL service running
2. Check kredential database di .env
3. Pastikan database sudah dibuat

### Asset Not Found

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Rebuild assets
npm run build
```

### Storage Permission

```bash
# Set owner untuk web server
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
```

## Kontribusi

1. Fork repository
2. Buat feature branch
3. Commit changes
4. Push ke branch
5. Buat Pull Request

## Lisensi

Aplikasi ini menggunakan lisensi MIT. Lihat file `LICENSE` untuk detail.

## Support

Untuk pertanyaan dan support:
- GitHub Issues: [ElectronicSheet Issues](https://github.com/risunCode/ElectronicSheet/issues)
- Email: support@electronicsheet.com

---

**Version:** 0.0.1  
**Author:** RisunCode  
**Last Updated:** December 2024
