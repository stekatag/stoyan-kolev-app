# 🎬 Stoyan Kolev App

Stoyan Kolev App is a Laravel, Inertia, Vue, and Filament meme-video app built around a hotspot homepage, a one-time intro clip, and a protected admin panel for managing categories and videos.

## 🧱 Stack

- Laravel 13
- PHP 8.4
- Inertia.js v3 + Vue 3 + TypeScript
- Filament 5
- Tailwind CSS 4
- Pest 4

## 🚀 Installation

1. Install PHP dependencies:

```bash
composer install
```

2. Install frontend dependencies:

```bash
npm install
```

3. Create your environment file and app key:

```bash
cp .env.example .env
php artisan key:generate
```

4. Run the database migrations:

```bash
php artisan migrate
```

5. Expose the public storage disk:

```bash
php artisan storage:link
```

6. Import the packaged Stoyan catalog into the database and local public disk:

```bash
php artisan stoyan:import-catalog
```

7. Optionally sync the active product assets to the bucket:

```bash
php artisan stoyan:sync-assets
```

If you change bucket-related environment variables, run this before retrying:

```bash
php artisan config:clear
```

## 🗂️ Media Model

The app now stores one canonical relative path per asset.

- Local public files are derived as `assets/{canonical_path}`.
- Bucket objects use the same `assets/{canonical_path}` path.
- Asset URLs resolve from the configured bucket first; when no bucket URL can be built, the app falls back to local public storage.
- Local videos are served through `/videos/{video}/stream`, while images resolve directly from `/storage/assets/...`.

Configured fixed assets currently resolve through canonical paths like these:

- `homepage/initial screen.png`
- `intro/Intro.mp4`
- `profile/profile.jpg`

Imported media follows the same pattern:

- Category previews use `modal_preview_image_path`.
- Video records store `video_path` and `thumbnail_path`.
- Numbered thumbnails live under paths such as `thumbnails/qdosan/1.jpeg`.

## ☁️ Bucket Setup

Bucket syncing expects the S3-compatible disk configuration to be present. At minimum, set these environment variables:

- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_BUCKET`
- `AWS_ENDPOINT`
- `AWS_DEFAULT_REGION`

The sync command uploads the homepage image, intro video, profile image, category preview images, video files, and video thumbnails.

## 🔐 Admin Flow

There is no public auth flow.

- Filament admin lives at `/admin/login`.
- Admin invitations are created from the Filament panel.
- Invite acceptance happens through signed URLs under `/admin/invitations/{token}`.

## 🧪 Verification

Useful project checks:

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run lint:check
npm run types:check
```

## 🛠️ Development Notes

- If a frontend change does not appear, run `npm run dev` or rebuild with `npm run build`.
- The project is intended to run under Laravel Herd in local development.
