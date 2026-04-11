# Stoyan Kolev Video Platform Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the public Stoyan Kolev video experience, protected Filament admin, bucket-first media workflow, local recovery fallback, and invitation-based admin onboarding while removing Laravel starter-kit leftovers.

**Architecture:** Use thin Laravel controllers that delegate to service classes, with repositories owning database persistence and query concerns. Keep storage resolution behind dedicated media services so Inertia/Vue components receive normalized view models and never need to understand bucket versus local fallback behavior.

**Tech Stack:** Laravel 13, PHP 8.4, Inertia v3 + Vue 3 + TypeScript, Filament 5, Fortify, Pest 4, Wayfinder, Flysystem S3-compatible storage, Tailwind CSS 4.

---

## Working Rules

- References: @laravel-best-practices @fortify-development @pest-testing @inertia-vue-development @wayfinder-development
- Assume execution happens in a dedicated git worktree even though this planning session is in the main workspace.
- Keep controllers thin: request validation + service orchestration only.
- Keep query logic out of controllers and services; repositories own read/write details.
- Do not use `RefreshDatabase`; prefer `LazilyRefreshDatabase` or focused database setup helpers.
- Use Wayfinder route helpers instead of hardcoded frontend URLs once routes stabilize.
- Remove starter-kit leftovers early so later tasks do not build on obsolete routes/pages.

## File Structure Map

### Existing files to modify

- `composer.json` — confirm S3 dependency is present and keep scripts aligned.
- `config/filesystems.php` — define the S3-compatible disk and any local recovery disk if needed.
- `config/fortify.php` — disable public registration/reset/email-verification features and keep only the auth surface needed for admins.
- `routes/web.php` — replace starter routes with the public landing route and invitation acceptance routes.
- `app/Providers/FortifyServiceProvider.php` — remove starter-kit auth views no longer needed and register only the remaining auth behavior.
- `app/Providers/Filament/AdminPanelProvider.php` — register panel auth/branding/resources and gate access to admins.
- `app/Models/User.php` — add admin authorization fields/casts/traits required by Filament access.
- `resources/js/app.ts` — update page-layout resolution after removing starter-kit pages.
- `resources/js/pages/Welcome.vue` — replace with the real public experience or redirect to a new public page component.
- `tests/Pest.php` — add shared testing traits/helpers without introducing `RefreshDatabase`.
- `tests/Feature/ExampleTest.php` — replace starter placeholder with real public-home coverage.
- `tests/Feature/DashboardTest.php` — replace or remove starter dashboard assumptions once the public/admin split is in place.

### Backend files to create

- `config/stoyan_kolev.php` — fixed hotspot keys, homepage image path, intro asset path, and recovery storage configuration.
- `database/migrations/xxxx_xx_xx_xxxxxx_add_is_admin_to_users_table.php`
- `database/migrations/xxxx_xx_xx_xxxxxx_create_categories_table.php`
- `database/migrations/xxxx_xx_xx_xxxxxx_create_videos_table.php`
- `database/migrations/xxxx_xx_xx_xxxxxx_create_admin_invitations_table.php`
- `app/Models/Category.php`
- `app/Models/Video.php`
- `app/Models/AdminInvitation.php`
- `app/Enums/HomepageHotspotKey.php`
- `app/Enums/AssetStorageStatus.php`
- `app/Enums/VideoSourceType.php`
- `app/Repositories/Contracts/CategoryRepositoryInterface.php`
- `app/Repositories/Contracts/VideoRepositoryInterface.php`
- `app/Repositories/Contracts/AdminInvitationRepositoryInterface.php`
- `app/Repositories/Contracts/UserRepositoryInterface.php`
- `app/Repositories/EloquentCategoryRepository.php`
- `app/Repositories/EloquentVideoRepository.php`
- `app/Repositories/EloquentAdminInvitationRepository.php`
- `app/Repositories/EloquentUserRepository.php`
- `app/Services/PublicCatalogService.php`
- `app/Services/Media/MediaUrlResolver.php`
- `app/Services/Media/AssetSyncService.php`
- `app/Services/Media/AdminMediaUploadService.php`
- `app/Services/Auth/AdminInvitationService.php`
- `app/Http/Controllers/PublicHomeController.php`
- `app/Http/Controllers/AdminInvitationAcceptanceController.php`
- `app/Http/Requests/Admin/CompleteAdminInvitationRequest.php`
- `app/Console/Commands/SyncStoyanKolevAssetsCommand.php`
- `app/Providers/AppServiceProvider.php` — bind repository interfaces to implementations.

### Filament files to create

- `app/Filament/Resources/CategoryResource.php`
- `app/Filament/Resources/CategoryResource/Pages/ListCategories.php`
- `app/Filament/Resources/CategoryResource/Pages/CreateCategory.php`
- `app/Filament/Resources/CategoryResource/Pages/EditCategory.php`
- `app/Filament/Resources/VideoResource.php`
- `app/Filament/Resources/VideoResource/Pages/ListVideos.php`
- `app/Filament/Resources/VideoResource/Pages/CreateVideo.php`
- `app/Filament/Resources/VideoResource/Pages/EditVideo.php`
- `app/Filament/Resources/AdminInvitationResource.php`
- `app/Filament/Resources/AdminInvitationResource/Pages/ListAdminInvitations.php`
- `app/Filament/Resources/AdminInvitationResource/Pages/CreateAdminInvitation.php`

### Frontend files to create

- `resources/js/pages/Public/Home.vue`
- `resources/js/layouts/PublicLayout.vue`
- `resources/js/components/stoyan/IntroVideoGate.vue`
- `resources/js/components/stoyan/HomeHotspotMap.vue`
- `resources/js/components/stoyan/CategoryVideoModal.vue`
- `resources/js/components/stoyan/VideoPlayerModal.vue`
- `resources/js/components/stoyan/VideoThumbnailCard.vue`
- `resources/js/composables/useIntroVideoPreference.ts`
- `resources/js/types/stoyan.ts`
- `resources/js/pages/Auth/AdminInviteSetup.vue`

### Tests to create

- `tests/Feature/Public/PublicHomeTest.php`
- `tests/Feature/Public/PublicCategoryFlowTest.php`
- `tests/Feature/Admin/AdminInvitationFlowTest.php`
- `tests/Feature/Admin/FilamentAdminAccessTest.php`
- `tests/Feature/Console/SyncStoyanKolevAssetsCommandTest.php`
- `tests/Unit/Repositories/EloquentCategoryRepositoryTest.php`
- `tests/Unit/Repositories/EloquentVideoRepositoryTest.php`
- `tests/Unit/Repositories/EloquentUserRepositoryTest.php`
- `tests/Unit/Services/MediaUrlResolverTest.php`
- `tests/Unit/Services/AssetSyncServiceTest.php`
- `tests/Unit/Services/AdminInvitationServiceTest.php`
- `tests/Unit/Services/AdminMediaUploadServiceTest.php`
- `tests/Browser/PublicExperienceTest.php`

### Starter-kit files likely to delete

- `resources/js/pages/Dashboard.vue`
- `resources/js/pages/auth/Register.vue`
- `resources/js/pages/auth/ForgotPassword.vue`
- `resources/js/pages/auth/ResetPassword.vue`
- `resources/js/pages/auth/VerifyEmail.vue`
- `resources/js/pages/auth/TwoFactorChallenge.vue`
- `resources/js/pages/auth/ConfirmPassword.vue`
- `routes/settings.php`
- `app/Http/Controllers/Settings/ProfileController.php`
- `app/Http/Controllers/Settings/SecurityController.php`
- `resources/js/pages/settings/*`
- `resources/js/layouts/settings/*`
- `resources/js/routes/register/*`
- `resources/js/routes/password/*`
- `resources/js/routes/verification/*`
- `resources/js/routes/two-factor/*`
- `resources/js/routes/security/*`
- `resources/js/routes/profile/*`
- `resources/js/actions/*` files that exist only for removed starter-kit settings/auth flows

The exact delete set should be confirmed during implementation after route cleanup so only genuinely dead starter-kit code is removed.

## Chunk 1: Foundation And Data Layer

### Task 1: Remove Starter Auth Surface And Reframe The App Entry

**Files:**

- Modify: `config/fortify.php`
- Modify: `app/Providers/FortifyServiceProvider.php`
- Modify: `routes/web.php`
- Modify: `resources/js/app.ts`
- Delete: `routes/settings.php`
- Test: `tests/Feature/Public/PublicHomeTest.php`
- Delete: `tests/Feature/ExampleTest.php`
- Delete: `tests/Feature/DashboardTest.php`

- [ ] **Step 1: Write the failing public-home and auth-boundary tests**

```php
<?php

test('the public home route is available without authentication', function () {
    $response = $this->get(route('home'));

    $response->assertInertia(fn ($page) => $page
        ->component('Public/Home')
        ->has('introVideoUrl')
        ->has('homepageImageUrl')
        ->has('hotspots')
    );
});

test('starter auth and settings routes are not exposed publicly', function () {
    $this->get('/login')->assertNotFound();
    $this->get('/register')->assertNotFound();
    $this->get('/forgot-password')->assertNotFound();
    $this->get('/email/verify')->assertNotFound();
    $this->get('/two-factor-challenge')->assertNotFound();
    $this->get('/user/confirm-password')->assertNotFound();
    $this->get('/settings/profile')->assertNotFound();
});

test('the filament login entrypoint remains available to guests', function () {
    $this->get('/admin/login')->assertOk();
});
```

- [ ] **Step 2: Run the feature tests to verify the current route contract is wrong**

Run: `php artisan test --compact tests/Feature/Public/PublicHomeTest.php`
Expected: FAIL because the response still reflects starter-kit behavior and public auth/settings routes are still present.

- [ ] **Step 3: Disable public Fortify features and remove starter settings routing**

```php
'features' => [
    // Keep only features that are intentionally used for admin auth.
],
```

Implementation notes:

- Remove registration, password reset, email verification, and two-factor view flows from `config/fortify.php`.
- Keep `views` disabled once the app no longer uses starter-kit auth pages.
- In `FortifyServiceProvider`, remove Inertia view registrations for deleted auth pages.
- Stop requiring `routes/settings.php` and delete it in this task so the old authenticated settings surface is gone immediately.

- [ ] **Step 4: Replace the starter route shape with a temporary public landing route**

```php
Route::inertia('/', 'Public/Home', [
    'introVideoUrl' => null,
    'homepageImageUrl' => '',
    'hotspots' => [],
])->name('home');
```

Implementation notes:

- Keep this route temporary and inline in Task 1; Task 7 replaces it with the real `PublicHomeController` and `PublicCatalogService` once schema, repositories, and media resolution exist.
- Remove the public dashboard route unless the admin panel still needs a separate app route.
- Remove `verified` middleware assumptions from the public surface.

- [ ] **Step 5: Update Inertia layout resolution to support `Public/*` pages cleanly**

```ts
case name.startsWith('Public/'):
    return PublicLayout
```

- [ ] **Step 6: Re-run the public-home test**

Run: `php artisan test --compact tests/Feature/Public/PublicHomeTest.php`
Expected: PASS.

- [ ] **Step 7: Commit the auth-surface cleanup**

```bash
git add config/fortify.php app/Providers/FortifyServiceProvider.php routes/web.php resources/js/app.ts tests/Feature/Public/PublicHomeTest.php
git commit -m "refactor: remove starter public auth surface"
```

- [ ] **Step 8: Remove starter placeholder feature tests that no longer describe the app**

```bash
git rm tests/Feature/ExampleTest.php tests/Feature/DashboardTest.php
git commit -m "test: remove starter placeholder feature tests"
```

### Task 2: Create Configuration And Schema Foundations

**Files:**

- Create: `config/stoyan_kolev.php`
- Create: `database/migrations/xxxx_xx_xx_xxxxxx_add_is_admin_to_users_table.php`
- Create: `database/migrations/xxxx_xx_xx_xxxxxx_create_categories_table.php`
- Create: `database/migrations/xxxx_xx_xx_xxxxxx_create_videos_table.php`
- Create: `database/migrations/xxxx_xx_xx_xxxxxx_create_admin_invitations_table.php`
- Test: `tests/Feature/Admin/AdminInvitationFlowTest.php`

- [ ] **Step 1: Write the failing schema test for the invitation table**

```php
<?php

use Illuminate\Support\Facades\Schema;

test('the admin invitations table exists with the expected columns', function () {
    expect(Schema::hasTable('admin_invitations'))->toBeTrue()
        ->and(Schema::hasColumns('admin_invitations', [
            'email',
            'token_hash',
            'expires_at',
            'consumed_at',
            'created_by_user_id',
        ]))->toBeTrue();
});
```

- [ ] **Step 2: Run the test to verify missing schema/models**

Run: `php artisan test --compact tests/Feature/Admin/AdminInvitationFlowTest.php --filter=admin\ invitations\ table`
Expected: FAIL because the model/table does not exist yet.

- [ ] **Step 3: Add the fixed app configuration for hotspots and packaged assets**

```php
return [
    'homepage_image' => 'assets/initial screen/initial screen.png',
    'intro_video' => [
        'bucket_key' => 'intro/Intro.mp4',
        'local_path' => 'assets/videos/Intro.mp4',
    ],
    'hotspots' => [
        'qdosan',
        'uchuden',
        'kaish',
        'izmoren',
        'vesel',
        'izpederastql',
    ],
];
```

- [ ] **Step 4: Create migrations for users, categories, videos, and admin invitations**

Implementation notes:

- `users`: add `is_admin` boolean with default `false` and index if useful.
- `categories`: include visibility, sort order, unique slug, and a durable uniqueness rule for `homepage_hotspot_key` on visible rows, implemented with a partial unique index or equivalent database-backed guarantee.
- `categories`: include `modal_preview_image_path` so category modals can show an optional preview image.
- `videos`: include independent bucket/local keys and `video_storage_status` / `thumbnail_storage_status`.
- `admin_invitations`: unique token hash, email, creator id, expiry, consumption timestamp.

- [ ] **Step 5: Run migrations and the schema test again**

Run: `php artisan migrate`
Expected: migration batch completes successfully.

Run: `php artisan test --compact tests/Feature/Admin/AdminInvitationFlowTest.php --filter=admin\ invitations\ table`
Expected: PASS.

- [ ] **Step 6: Commit the schema foundation**

```bash
git add config/stoyan_kolev.php database/migrations
git commit -m "feat: add video platform schema foundation"
```

### Task 3: Add Models And Enums

**Files:**

- Create: `app/Enums/HomepageHotspotKey.php`
- Create: `app/Enums/AssetStorageStatus.php`
- Create: `app/Enums/VideoSourceType.php`
- Create: `app/Models/Category.php`
- Create: `app/Models/Video.php`
- Create: `app/Models/AdminInvitation.php`
- Modify: `app/Models/User.php`
- Test: `tests/Unit/Repositories/EloquentVideoRepositoryTest.php`

- [ ] **Step 1: Write the failing model-cast test**

```php
<?php

use App\Enums\AssetStorageStatus;
use App\Models\Video;

test('video storage statuses are cast to enums', function () {
    $video = new Video([
        'video_storage_status' => AssetStorageStatus::BucketAndLocal,
    ]);

    expect($video->video_storage_status)->toBe(AssetStorageStatus::BucketAndLocal);
});
```

- [ ] **Step 2: Run the test to verify models/enums are missing**

Run: `php artisan test --compact tests/Unit/Repositories/EloquentVideoRepositoryTest.php --filter=enums`
Expected: FAIL because the enum/model classes do not exist.

- [ ] **Step 3: Create focused enums and Eloquent models**

```php
enum AssetStorageStatus: string
{
    case Missing = 'missing';
    case LocalOnly = 'local_only';
    case BucketOnly = 'bucket_only';
    case BucketAndLocal = 'bucket_and_local';
}
```

Implementation notes:

- Add guarded/fillable properties explicitly.
- Add relations: `Category hasMany Video`, `Video belongsTo Category`, `AdminInvitation belongsTo creator User`.
- Add casts for enum-backed fields, booleans, and datetimes.
- Update `User` with `is_admin` cast and Filament access method if needed.

- [ ] **Step 4: Re-run the model-cast test**

Run: `php artisan test --compact tests/Unit/Repositories/EloquentVideoRepositoryTest.php --filter=enums`
Expected: PASS.

- [ ] **Step 5: Commit model and enum creation**

```bash
git add app/Enums app/Models
git commit -m "feat: add video platform models and enums"
```

### Task 3A: Protect Filament Access As Soon As Admin State Exists

**Files:**

- Modify: `app/Models/User.php`
- Modify: `app/Providers/Filament/AdminPanelProvider.php`
- Test: `tests/Feature/Admin/FilamentAdminAccessTest.php`

- [ ] **Step 1: Write the failing admin-access tests**

```php
<?php

use App\Models\User;

test('non-admin users cannot access the filament panel', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('admin users can access the filament panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
});

test('admins can authenticate through the filament login form', function () {
    $admin = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'is_admin' => true,
    ]);

    $this->post('/admin/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect('/admin');
});
```

- [ ] **Step 2: Run the Filament access tests to verify authorization is incomplete**

Run: `php artisan test --compact tests/Feature/Admin/FilamentAdminAccessTest.php`
Expected: FAIL because panel access is not yet constrained to admins.

- [ ] **Step 3: Implement `canAccessPanel()` and provider-side admin checks**

```php
public function canAccessPanel(Panel $panel): bool
{
    return $this->is_admin;
}
```

- [ ] **Step 4: Re-run the Filament access tests**

Run: `php artisan test --compact tests/Feature/Admin/FilamentAdminAccessTest.php`
Expected: PASS.

- [ ] **Step 5: Commit early admin-panel protection**

```bash
git add app/Models/User.php app/Providers/Filament/AdminPanelProvider.php tests/Feature/Admin/FilamentAdminAccessTest.php
git commit -m "feat: protect filament panel with admin gate"
```

### Task 4: Implement Repository Contracts And Bindings

**Files:**

- Create: `app/Repositories/Contracts/CategoryRepositoryInterface.php`
- Create: `app/Repositories/Contracts/VideoRepositoryInterface.php`
- Create: `app/Repositories/Contracts/AdminInvitationRepositoryInterface.php`
- Create: `app/Repositories/Contracts/UserRepositoryInterface.php`
- Create: `app/Repositories/EloquentCategoryRepository.php`
- Create: `app/Repositories/EloquentVideoRepository.php`
- Create: `app/Repositories/EloquentAdminInvitationRepository.php`
- Create: `app/Repositories/EloquentUserRepository.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Unit/Repositories/EloquentCategoryRepositoryTest.php`
- Test: `tests/Unit/Repositories/EloquentVideoRepositoryTest.php`
- Test: `tests/Unit/Repositories/EloquentUserRepositoryTest.php`

- [ ] **Step 1: Write the failing repository query test**

```php
<?php

test('the category repository returns only visible hotspot categories', function () {
    \App\Models\Category::factory()->create([
        'name' => 'Hidden',
        'slug' => 'hidden',
        'homepage_hotspot_key' => null,
        'is_visible' => false,
        'sort_order' => 99,
    ]);

    \App\Models\Category::factory()->create([
        'name' => 'Kaish',
        'slug' => 'kaish',
        'homepage_hotspot_key' => 'kaish',
        'is_visible' => true,
        'sort_order' => 2,
    ]);

    \App\Models\Category::factory()->create([
        'name' => 'Qdosan',
        'slug' => 'qdosan',
        'homepage_hotspot_key' => 'qdosan',
        'is_visible' => true,
        'sort_order' => 1,
    ]);

    $repository = app(\App\Repositories\Contracts\CategoryRepositoryInterface::class);

    $categories = $repository->getVisibleHomepageCategories();

    expect($categories->pluck('slug')->all())->toBe(['qdosan', 'kaish'])
        ->and($categories->every(fn ($category) => $category->is_visible))->toBeTrue()
        ->and($categories->pluck('homepage_hotspot_key')->unique()->count())->toBe($categories->count());
});
```

- [ ] **Step 2: Run the repository test to verify the contract is unbound**

Run: `php artisan test --compact tests/Unit/Repositories/EloquentCategoryRepositoryTest.php`
Expected: FAIL because the interface/implementation binding does not exist.

- [ ] **Step 3: Implement contracts and Eloquent-backed repositories**

```php
interface CategoryRepositoryInterface
{
    public function getVisibleHomepageCategories(): Collection;
}
```

Implementation notes:

- Repositories should return eager-loaded models or DTO-ready collections.
- Keep visibility, hotspot filtering, and sort order inside repositories.
- Keep invitation persistence in the invitation repository and account existence checks in the user repository.
- Seed visible, hidden, hotspot-less, and differently ordered records so the tests prove filtering and ordering instead of only non-crashing behavior.

- [ ] **Step 4: Bind interfaces in `AppServiceProvider`**

```php
$this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
```

- [ ] **Step 5: Re-run repository tests**

Run: `php artisan test --compact tests/Unit/Repositories/EloquentCategoryRepositoryTest.php tests/Unit/Repositories/EloquentVideoRepositoryTest.php tests/Unit/Repositories/EloquentUserRepositoryTest.php`
Expected: PASS.

- [ ] **Step 6: Commit repository layer**

```bash
git add app/Repositories app/Providers/AppServiceProvider.php tests/Unit/Repositories
git commit -m "feat: add repository layer for video platform"
```

### Task 5: Add Media Services And Bucket/Local Disk Configuration

**Files:**

- Modify: `config/filesystems.php`
- Create: `app/Services/Media/MediaUrlResolver.php`
- Test: `tests/Unit/Services/MediaUrlResolverTest.php`

- [ ] **Step 1: Write the failing media-resolution tests**

```php
<?php

use App\Enums\AssetStorageStatus;
use App\Models\Video;
use App\Services\Media\MediaUrlResolver;

test('the resolver prefers bucket urls when metadata says the object is available', function () {
    $resolver = app(MediaUrlResolver::class);

    $video = new Video([
        'bucket_video_key' => 'videos/test.mp4',
        'local_video_path' => 'assets/videos/test.mp4',
        'video_storage_status' => AssetStorageStatus::BucketAndLocal,
    ]);

    expect($resolver->resolveVideoUrl($video))->toContain('videos/test.mp4');
});

test('the resolver falls back to the local asset url when bucket delivery is unavailable', function () {
    $resolver = app(MediaUrlResolver::class);

    $video = new Video([
        'local_video_path' => 'assets/videos/test.mp4',
        'video_storage_status' => AssetStorageStatus::LocalOnly,
    ]);

    expect($resolver->resolveVideoUrl($video))->toContain('assets/videos/test.mp4');
});

test('the resolver returns null for unavailable videos', function () {
    $resolver = app(MediaUrlResolver::class);

    $video = new Video([
        'video_storage_status' => AssetStorageStatus::Missing,
    ]);

    expect($resolver->resolveVideoUrl($video))->toBeNull();
});
```

- [ ] **Step 2: Run the resolver tests to verify the service does not exist**

Run: `php artisan test --compact tests/Unit/Services/MediaUrlResolverTest.php`
Expected: FAIL because the resolver and config are missing.

- [ ] **Step 3: Define filesystem disks and implement the resolver**

```php
public function resolveVideoUrl(Video $video): ?string
{
    return match ($video->video_storage_status) {
        AssetStorageStatus::BucketOnly,
        AssetStorageStatus::BucketAndLocal => Storage::disk('s3')->url($video->bucket_video_key),
        AssetStorageStatus::LocalOnly => asset($video->local_video_path),
        default => null,
    };
}
```

Implementation notes:

- Add a dedicated local recovery disk as mandatory configuration for admin-upload mirroring.
- Keep packaged assets and mirrored admin assets distinguishable in config.
- Do not probe remote object storage in request-time methods.

- [ ] **Step 4: Re-run the resolver tests**

Run: `php artisan test --compact tests/Unit/Services/MediaUrlResolverTest.php`
Expected: PASS.

- [ ] **Step 5: Commit storage service groundwork**

```bash
git add config/filesystems.php app/Services/Media/MediaUrlResolver.php tests/Unit/Services/MediaUrlResolverTest.php
git commit -m "feat: add media url resolver"
```

## Chunk 2: Public Experience And Asset Sync

### Task 6: Build The Asset Sync Service And Console Command

**Files:**

- Create: `app/Services/Media/AssetSyncService.php`
- Create: `app/Console/Commands/SyncStoyanKolevAssetsCommand.php`
- Test: `tests/Unit/Services/AssetSyncServiceTest.php`
- Test: `tests/Feature/Console/SyncStoyanKolevAssetsCommandTest.php`

- [ ] **Step 1: Write failing service and command tests for the recovery contract**

```php
<?php

test('the asset sync service is idempotent for existing imported assets', function () {
    Storage::fake('s3');
    Storage::fake('stoyan_recovery');

    $service = app(\App\Services\Media\AssetSyncService::class);

    $first = $service->sync(dryRun: false);
    $second = $service->sync(dryRun: false);

    expect($second->created)->toBe(0);
});

test('the asset sync service refreshes storage metadata for uploaded media', function () {
    Storage::fake('s3');
    Storage::fake('stoyan_recovery');

    $service = app(\App\Services\Media\AssetSyncService::class);

    $result = $service->sync(dryRun: false);

    expect($result->created + $result->updated + $result->skipped)->toBeGreaterThan(0)
        ->and($result->failed)->toBeGreaterThanOrEqual(0);
});

test('the sync command supports dry-run output', function () {
    $this->artisan('stoyan-kolev:sync-assets --dry-run')
        ->expectsOutputToContain('DRY RUN')
        ->assertExitCode(0);
});

test('the sync command dry run does not mutate database rows or storage objects', function () {
    $categoriesBefore = \App\Models\Category::count();
    $videosBefore = \App\Models\Video::count();

    $this->artisan('stoyan-kolev:sync-assets --dry-run')->assertExitCode(0);

    expect(\App\Models\Category::count())->toBe($categoriesBefore)
        ->and(\App\Models\Video::count())->toBe($videosBefore);
});
```

- [ ] **Step 2: Run the service and command tests to verify the workflow is missing**

Run: `php artisan test --compact tests/Unit/Services/AssetSyncServiceTest.php tests/Feature/Console/SyncStoyanKolevAssetsCommandTest.php`
Expected: FAIL because the service and command do not exist.

- [ ] **Step 3: Implement a service-first sync flow and a thin command wrapper**

```php
public function handle(): int
{
    $result = $this->assetSyncService->sync(dryRun: (bool) $this->option('dry-run'));

    $this->line($result->summary());

    return $result->hasFailures() ? self::FAILURE : self::SUCCESS;
}
```

Implementation notes:

- Service responsibilities: scan configured local assets, reconcile categories/videos through repositories, upload missing assets to bucket, update storage metadata through repositories, and produce per-item created/updated/skipped/failed reporting.
- Command responsibilities: CLI arguments, output formatting, exit codes.
- Make the sync idempotent.
- Cover fixed intro asset reconciliation and partial-failure metadata updates in the service tests.
- Add an explicit test for credential/connectivity failure reporting so operational errors do not collapse into silent skips.
- Reserve admin-upload recovery restoration tests until Task 13, when the mirrored upload source of truth exists.

- [ ] **Step 4: Re-run the sync-command test**

Run: `php artisan test --compact tests/Unit/Services/AssetSyncServiceTest.php tests/Feature/Console/SyncStoyanKolevAssetsCommandTest.php`
Expected: PASS.

- [ ] **Step 5: Commit the sync workflow**

```bash
git add app/Services/Media/AssetSyncService.php app/Console/Commands/SyncStoyanKolevAssetsCommand.php tests/Unit/Services/AssetSyncServiceTest.php tests/Feature/Console/SyncStoyanKolevAssetsCommandTest.php
git commit -m "feat: add asset sync command"
```

### Task 7: Build The Public Catalog Service And Thin Public Controller

**Files:**

- Create: `app/Services/PublicCatalogService.php`
- Create: `app/Http/Controllers/PublicHomeController.php`
- Test: `tests/Feature/Public/PublicHomeTest.php`
- Test: `tests/Feature/Public/PublicCategoryFlowTest.php`

- [ ] **Step 1: Expand the failing public-home test to assert the public payload contract**

```php
<?php

test('the public home payload includes intro media and hotspot categories', function () {
    $response = $this->get(route('home'));

    $response->assertInertia(fn ($page) => $page
        ->component('Public/Home')
        ->has('introVideoUrl')
        ->has('homepageImageUrl')
        ->has('hotspots', 6)
        ->has('categoriesByHotspot.qdosan')
        ->where('categoriesByHotspot.qdosan.videoCount', 1)
        ->where('categoriesByHotspot.qdosan.opensDirectly', true)
    );
});
```

- [ ] **Step 2: Run the public feature tests to verify the payload is not implemented yet**

Run: `php artisan test --compact tests/Feature/Public/PublicHomeTest.php tests/Feature/Public/PublicCategoryFlowTest.php`
Expected: FAIL because the controller/service payload does not exist.

- [ ] **Step 3: Implement the service and controller with thin-controller boundaries**

```php
final class PublicHomeController
{
    public function __invoke(PublicCatalogService $catalogService): Response
    {
        return Inertia::render('Public/Home', $catalogService->buildHomePageProps());
    }
}
```

Implementation notes:

- Service should orchestrate repositories + resolver.
- Controller should not query models directly.
- Normalize category/video payloads for Vue consumption, including resolved thumbnail URLs, resolved playback URLs, empty-state flags, and one-versus-many modal behavior so Vue stays storage-agnostic.

- [ ] **Step 4: Re-run the public feature tests**

Run: `php artisan test --compact tests/Feature/Public/PublicHomeTest.php tests/Feature/Public/PublicCategoryFlowTest.php`
Expected: PASS.

- [ ] **Step 5: Commit the public catalog backend**

```bash
git add app/Services/PublicCatalogService.php app/Http/Controllers/PublicHomeController.php tests/Feature/Public
git commit -m "feat: add public catalog backend"
```

### Task 8: Replace The Starter Welcome Page With The Real Public UI

**Files:**

- Create: `resources/js/layouts/PublicLayout.vue`
- Create: `resources/js/pages/Public/Home.vue`
- Create: `resources/js/components/stoyan/IntroVideoGate.vue`
- Create: `resources/js/components/stoyan/HomeHotspotMap.vue`
- Create: `resources/js/components/stoyan/CategoryVideoModal.vue`
- Create: `resources/js/components/stoyan/VideoPlayerModal.vue`
- Create: `resources/js/components/stoyan/VideoThumbnailCard.vue`
- Create: `resources/js/composables/useIntroVideoPreference.ts`
- Create: `resources/js/types/stoyan.ts`
- Delete: `resources/js/pages/Welcome.vue`
- Test: `tests/Browser/PublicExperienceTest.php`
- Test: `tests/Feature/Public/PublicHomeTest.php`

- [ ] **Step 1: Add a failing browser test for intro dismissal and hotspot interaction**

```php
<?php

test('the intro can be skipped and a single-video hotspot opens playback directly', function () {
    $page = visit('/');

    $page->assertSee('Skip')
        ->click('Skip')
        ->assertDontSee('Skip')
        ->click('[data-hotspot="qdosan"]')
        ->assertSee('Close');
});

test('a multi-video hotspot opens a modal grid and missing thumbnails fall back cleanly', function () {
    $page = visit('/');

    $page->click('Skip')
        ->click('[data-hotspot="kaish"]')
        ->assertSee('kaish')
        ->assertSee('Play');
});

test('skip persists and the intro does not reappear on the next visit', function () {
    $page = visit('/');

    $page->click('Skip')
        ->refresh()
        ->assertDontSee('Skip');
});

test('dont show again persists and the intro does not reappear on the next visit', function () {
    $page = visit('/');

    $page->click("Don't show again")
        ->refresh()
        ->assertDontSee('Skip');
});

test('an empty category shows an empty state and closing playback returns to the grid', function () {
    $page = visit('/');

    $page->click('Skip')
        ->click('[data-hotspot="vesel"]')
        ->assertSee('No videos yet')
        ->click('[data-hotspot="kaish"]')
        ->click('Play')
        ->click('Close')
        ->assertSee('kaish');
});
```

- [ ] **Step 2: Run the browser test to verify the public UI is not implemented yet**

Run: `php artisan test --compact tests/Browser/PublicExperienceTest.php`
Expected: FAIL because the UI and browser assertions are not implemented yet.

- [ ] **Step 3: Add a failing backend feature assertion for the `Public/Home` component**

```php
$response->assertInertia(fn ($page) => $page->component('Public/Home'));
```

- [ ] **Step 4: Run the public-home test to verify the old Welcome component is still wired**

Run: `php artisan test --compact tests/Feature/Public/PublicHomeTest.php --filter=public\ home`
Expected: FAIL because the response still points to `Welcome` or the new page is missing.

- [ ] **Step 5: Implement the new public page and focused child components**

```vue
<script setup lang="ts">
import IntroVideoGate from '@/components/stoyan/IntroVideoGate.vue';
import HomeHotspotMap from '@/components/stoyan/HomeHotspotMap.vue';

defineProps<{
    introVideoUrl: string | null;
    homepageImageUrl: string;
    hotspots: Array<unknown>;
}>();
</script>
```

Implementation notes:

- Keep the intro local-storage logic in the composable, not in the page.
- Keep modal state in `Public/Home.vue` and render smaller components for hotspot map and player.
- Use the packaged homepage image without redesigning the page into generic cards.

- [ ] **Step 6: Re-run the public feature and browser tests**

Run: `php artisan test --compact tests/Feature/Public/PublicHomeTest.php tests/Browser/PublicExperienceTest.php`
Expected: PASS.

- [ ] **Step 7: Run type and lint checks for the new Vue surface**

Run: `npm run types:check`
Expected: PASS.

Run: `npm run lint:check`
Expected: PASS or only unrelated pre-existing issues.

- [ ] **Step 6: Commit the public Inertia/Vue experience**

```bash
git add resources/js/app.ts resources/js/layouts/PublicLayout.vue resources/js/pages/Public resources/js/components/stoyan resources/js/composables/useIntroVideoPreference.ts resources/js/types/stoyan.ts
git commit -m "feat: add public video experience"
```

### Task 9: Remove Starter-Kit Leftovers After The New Public Flow Is Stable

**Files:**

- Delete: `resources/js/pages/Dashboard.vue`
- Delete: `resources/js/pages/auth/Register.vue`
- Delete: `resources/js/pages/auth/ForgotPassword.vue`
- Delete: `resources/js/pages/auth/ResetPassword.vue`
- Delete: `resources/js/pages/auth/VerifyEmail.vue`
- Delete: `resources/js/pages/auth/TwoFactorChallenge.vue`
- Delete: `resources/js/pages/auth/ConfirmPassword.vue`
- Delete: `app/Http/Controllers/Settings/ProfileController.php`
- Delete: `app/Http/Controllers/Settings/SecurityController.php`
- Delete: starter-kit-only `resources/js/pages/settings/*`, `resources/js/layouts/settings/*`, `resources/js/routes/*`, and `resources/js/actions/*`
- Modify: `resources/js/app.ts`
- Test: `tests/Feature/Public/PublicHomeTest.php`
- Test: `tests/Feature/Admin/FilamentAdminAccessTest.php`

- [ ] **Step 1: Write or update a smoke-style feature test that confirms the public home still renders after starter cleanup**

```php
test('starter cleanup does not break the public home page', function () {
    $this->get(route('home'))->assertOk();
});
```

- [ ] **Step 2: Run the smoke test before deleting files**

Run: `php artisan test --compact tests/Feature/Public/PublicHomeTest.php --filter=starter\ cleanup`
Expected: PASS.

- [ ] **Step 3: Delete only code paths that are now dead**

Implementation notes:

- Remove `resources/js/pages/auth/Login.vue` as well; the plan assumes admins log in through Filament only.
- Remove unused routes/actions generated only for starter auth/settings flows.
- Clean imports in `resources/js/app.ts` after file deletion.

- [ ] **Step 4: Re-run the smoke test and frontend checks**

Run: `php artisan test --compact tests/Feature/Public/PublicHomeTest.php tests/Feature/Admin/FilamentAdminAccessTest.php`
Expected: PASS.

Run: `php artisan test --compact tests/Browser/PublicExperienceTest.php`
Expected: PASS.

Run: `npm run types:check`
Expected: PASS.

Run: `npm run lint:check`
Expected: PASS.

- [ ] **Step 5: Commit the starter-kit cleanup**

```bash
git add -A
git commit -m "refactor: remove starter kit leftovers"
```

## Chunk 3: Admin Panel, Invitations, And Verification

### Task 11: Add Filament Resource For Categories

**Files:**

- Create: `app/Filament/Resources/CategoryResource.php`
- Create: `app/Filament/Resources/CategoryResource/Pages/*`
- Test: `tests/Feature/Admin/FilamentAdminAccessTest.php`

- [ ] **Step 1: Write a failing Filament resource test for category creation**

```php
<?php

use function Pest\Livewire\livewire;

test('admins can create categories in filament', function () {
    $admin = \App\Models\User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin);

    livewire(\App\Filament\Resources\CategoryResource\Pages\CreateCategory::class)
        ->fillForm([
            'name' => 'Qdosan',
            'slug' => 'qdosan',
            'homepage_hotspot_key' => 'qdosan',
            'is_visible' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('admins cannot reuse an occupied hotspot key', function () {
    $admin = \App\Models\User::factory()->create(['is_admin' => true]);
    \App\Models\Category::factory()->create([
        'name' => 'Qdosan',
        'slug' => 'qdosan',
        'homepage_hotspot_key' => 'qdosan',
        'is_visible' => true,
    ]);

    $this->actingAs($admin);

    livewire(\App\Filament\Resources\CategoryResource\Pages\CreateCategory::class)
        ->fillForm([
            'name' => 'Kaish 2',
            'slug' => 'kaish-2',
            'homepage_hotspot_key' => 'qdosan',
            'is_visible' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['homepage_hotspot_key']);
});

test('admins can edit categories and manage the optional preview image', function () {
    $admin = \App\Models\User::factory()->create(['is_admin' => true]);
    $category = \App\Models\Category::factory()->create();

    $this->actingAs($admin);

    livewire(\App\Filament\Resources\CategoryResource\Pages\EditCategory::class, ['record' => $category->getKey()])
        ->fillForm([
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'is_visible' => false,
            'sort_order' => 10,
            'homepage_hotspot_key' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});
```

- [ ] **Step 2: Run the Filament resource test to verify the resource pages do not exist**

Run: `php artisan test --compact tests/Feature/Admin/FilamentAdminAccessTest.php --filter=create\ categories`
Expected: FAIL because the resource/page classes do not exist.

- [ ] **Step 3: Implement Filament resources with service-friendly boundaries**

Implementation notes:

- Use resource forms for validation and presentation only.
- Keep complex persistence and mirroring behavior delegated to services.
- Enforce hotspot-key uniqueness in the category form.
- Include `modal_preview_image_path` / preview-image upload support in the category form.
- Add tests for editing categories before considering this task complete.
- Add a failing form test for hotspot-key conflict messaging before implementing uniqueness rules.

- [ ] **Step 4: Re-run the Filament resource test**

Run: `php artisan test --compact tests/Feature/Admin/FilamentAdminAccessTest.php --filter=create\ categories`
Expected: PASS.

- [ ] **Step 5: Commit Filament CRUD resources**

```bash
git add app/Filament/Resources tests/Feature/Admin/FilamentAdminAccessTest.php
git commit -m "feat: add filament resources for video platform"
```

### Task 12: Implement Admin Invitation Service, Request, Controller, Setup Page, And Filament Invite Resource

**Files:**

- Create: `app/Services/Auth/AdminInvitationService.php`
- Create: `app/Http/Controllers/AdminInvitationAcceptanceController.php`
- Create: `app/Http/Requests/Admin/CompleteAdminInvitationRequest.php`
- Create: `app/Filament/Resources/AdminInvitationResource.php`
- Create: `app/Filament/Resources/AdminInvitationResource/Pages/ListAdminInvitations.php`
- Create: `app/Filament/Resources/AdminInvitationResource/Pages/CreateAdminInvitation.php`
- Create: `resources/js/pages/Auth/AdminInviteSetup.vue`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/AdminInvitationFlowTest.php`
- Test: `tests/Unit/Services/AdminInvitationServiceTest.php`

- [ ] **Step 1: Write the failing invitation-acceptance tests**

```php
<?php

test('a valid invitation link can create a new admin account', function () {
    $response = $this->get('/admin/invitations/test-token');

    $response->assertOk();
});

test('expired or tampered invitation links show a clear invalid state', function () {
    $this->get('/admin/invitations/bad-token')
        ->assertInertia(fn ($page) => $page->where('inviteState', 'invalid'));
});

test('invitation links require a valid signature', function () {
    $this->get('/admin/invitations/test-token?signature=bad')
        ->assertInertia(fn ($page) => $page->where('inviteState', 'invalid'));
});

test('invited email is locked on the setup form', function () {
    $response = $this->get('/admin/invitations/test-token');

    $response->assertInertia(fn ($page) => $page->where('emailLocked', true));
});

test('existing user emails cannot be reused for invitations', function () {
    $user = \App\Models\User::factory()->create(['email' => 'admin@example.com']);

    expect(fn () => app(\App\Services\Auth\AdminInvitationService::class)
        ->createInvitation('admin@example.com', $user))
        ->toThrow(\DomainException::class);
});

test('existing admin emails resolve to a clear already-admin rejection', function () {
    $admin = \App\Models\User::factory()->create([
        'email' => 'already-admin@example.com',
        'is_admin' => true,
    ]);

    expect(fn () => app(\App\Services\Auth\AdminInvitationService::class)
        ->createInvitation('already-admin@example.com', $admin))
        ->toThrow(\DomainException::class);
});

test('accepting an invite for an already-admin email shows a clear already-admin state', function () {
    $this->post('/admin/invitations/test-token', [
        'name' => 'Admin User',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertInertia(fn ($page) => $page->where('inviteState', 'already_admin'));
});

test('accepting an invite for an existing non-admin email is rejected cleanly', function () {
    $this->post('/admin/invitations/test-token', [
        'name' => 'Existing User',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertInertia(fn ($page) => $page->where('inviteState', 'email_unavailable'));
});

test('admins can create invitations from filament and receive a signed setup url', function () {
    $admin = \App\Models\User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin);

    livewire(\App\Filament\Resources\AdminInvitationResource\Pages\CreateAdminInvitation::class)
        ->fillForm([
            'email' => 'new-admin@example.com',
            'expires_at' => now()->addDay(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('invitation consumption rolls back if admin account creation fails', function () {
    $this->post('/admin/invitations/test-token', [
        'name' => 'Broken User',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertInertia(fn ($page) => $page->where('inviteState', 'invalid'));
});

test('a valid invitation can be consumed only once', function () {
    $response = $this->post('/admin/invitations/test-token', [
        'name' => 'Admin User',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/admin');

    $this->post('/admin/invitations/test-token', [
        'name' => 'Admin User',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertInertia(fn ($page) => $page->where('inviteState', 'consumed'));
});
```

- [ ] **Step 2: Run the invitation tests to verify the flow is absent**

Run: `php artisan test --compact tests/Feature/Admin/AdminInvitationFlowTest.php tests/Unit/Services/AdminInvitationServiceTest.php`
Expected: FAIL because the service, request, controller, and page are missing.

- [ ] **Step 3: Implement the service-first invitation flow**

```php
public function completeInvitation(string $plainToken, array $validated): User
{
    $invitation = $this->invitations->findUsableByTokenHash(hash('sha256', $plainToken));

    throw_if($this->users->emailExists($invitation->email), DomainException::class);

    return DB::transaction(function () use ($invitation, $validated) {
        $user = $this->users->createAdmin([
            'name' => $validated['name'],
            'email' => $invitation->email,
            'password' => $validated['password'],
        ]);

        $this->invitations->markConsumed($invitation);

        return $user;
    });
}
```

Implementation notes:

- Controller stays thin and delegates to the service.
- Request handles password/name validation.
- Setup page should receive a locked/prefilled email and submit via Inertia form helpers.
- Invalid, expired, or consumed invites should render a clear failure state.
- The Filament invitation resource should call the service for link generation instead of performing signing logic inline.
- Generate signed invite URLs and validate the route signature before attempting token consumption.
- Add tests for one-time use, already-admin email rejection, clear invalid/consumed state messaging, and transactional rollback on user-creation failure before closing the task.

- [ ] **Step 4: Re-run the invitation tests**

Run: `php artisan test --compact tests/Feature/Admin/AdminInvitationFlowTest.php tests/Unit/Services/AdminInvitationServiceTest.php`
Expected: PASS.

- [ ] **Step 5: Commit invitation onboarding**

```bash
git add app/Services/Auth/AdminInvitationService.php app/Http/Controllers/AdminInvitationAcceptanceController.php app/Http/Requests/Admin/CompleteAdminInvitationRequest.php app/Filament/Resources/AdminInvitationResource.php app/Filament/Resources/AdminInvitationResource/Pages resources/js/pages/Auth/AdminInviteSetup.vue routes/web.php tests/Feature/Admin/AdminInvitationFlowTest.php tests/Unit/Services/AdminInvitationServiceTest.php
git commit -m "feat: add admin invitation onboarding"
```

### Task 13: Wire Bucket-Backed Uploads Through Services And Filament

**Files:**

- Create: `app/Services/Media/AdminMediaUploadService.php`
- Create: `app/Filament/Resources/VideoResource.php`
- Create: `app/Filament/Resources/VideoResource/Pages/*`
- Modify: `app/Services/Media/MediaUrlResolver.php`
- Test: `tests/Unit/Services/AdminMediaUploadServiceTest.php`
- Test: `tests/Unit/Services/MediaUrlResolverTest.php`
- Test: `tests/Feature/Admin/FilamentAdminAccessTest.php`

- [ ] **Step 1: Add a real failing test for mirrored admin uploads**

```php
<?php

use function Pest\Livewire\livewire;

test('admins can create videos without thumbnails in filament', function () {
    $admin = \App\Models\User::factory()->create(['is_admin' => true]);
    $category = \App\Models\Category::factory()->create();
    $video = UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4');

    $this->actingAs($admin);

    livewire(\App\Filament\Resources\VideoResource\Pages\CreateVideo::class)
        ->fillForm([
            'category_id' => $category->id,
            'title' => 'Clip',
            'video' => $video,
            'is_visible' => true,
            'sort_order' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('admin-uploaded videos are not published when the local recovery mirror fails', function () {
    Storage::fake('s3');
    Storage::shouldReceive('disk->putFileAs')
        ->with('videos-recovery', Mockery::any(), Mockery::any())
        ->andThrow(new RuntimeException('Recovery write failed'));

    $category = \App\Models\Category::factory()->create();
    $upload = UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4');

    $service = app(\App\Services\Media\AdminMediaUploadService::class);

    expect(fn () => $service->storeVideoUpload($upload, categoryId: $category->id, title: 'Clip'))
        ->toThrow(\RuntimeException::class);

    Storage::disk('s3')->assertMissing('videos/clip.mp4');
});

test('repair sync can restore admin-uploaded media from the mirrored recovery store after bucket loss', function () {
    Storage::fake('s3');
    Storage::fake('stoyan_recovery');

    $category = \App\Models\Category::factory()->create();
    $upload = UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4');

    $uploadService = app(\App\Services\Media\AdminMediaUploadService::class);
    $syncService = app(\App\Services\Media\AssetSyncService::class);

    $video = $uploadService->storeVideoUpload($upload, categoryId: $category->id, title: 'Clip');

    Storage::disk('s3')->delete($video->bucket_video_key);

    $result = $syncService->sync(dryRun: false);

    expect($result->updated + $result->created)->toBeGreaterThan(0)
        ->and($video->fresh()->video_storage_status)->not->toBeNull();
});
```

- [ ] **Step 2: Run the targeted tests to confirm mirrored-upload behavior is not implemented**

Run: `php artisan test --compact tests/Feature/Admin/FilamentAdminAccessTest.php tests/Unit/Services/MediaUrlResolverTest.php tests/Unit/Services/AdminMediaUploadServiceTest.php`
Expected: FAIL for the new mirrored-upload scenario.

- [ ] **Step 3: Route upload persistence through a service boundary instead of inline Filament callbacks**

Implementation notes:

- Filament resource should hand uploaded temp files to a service.
- Service should write to the bucket and local recovery mirror as one logical operation.
- If local mirror write fails after bucket success, treat the save as failed and clean up or mark incomplete.
- Do not overload `AssetSyncService` with interactive upload orchestration; use `AdminMediaUploadService` for admin-initiated writes.
- Add a failing Filament-facing assertion that the upload error is surfaced to the admin user before implementing the resource integration.
- Add the video create/edit resource only after `AdminMediaUploadService` exists so CRUD never bypasses the durability contract.
- After the upload service exists, add a repair test proving a later sync run can restore admin-uploaded media from the mirrored recovery source after simulated bucket loss.

- [ ] **Step 4: Re-run the targeted tests**

Run: `php artisan test --compact tests/Feature/Admin/FilamentAdminAccessTest.php tests/Unit/Services/MediaUrlResolverTest.php tests/Unit/Services/AdminMediaUploadServiceTest.php`
Expected: PASS.

- [ ] **Step 5: Commit resilient upload behavior**

```bash
git add app/Services/Media/AdminMediaUploadService.php app/Services/Media/MediaUrlResolver.php app/Filament/Resources/VideoResource.php tests/Feature/Admin/FilamentAdminAccessTest.php tests/Unit/Services/MediaUrlResolverTest.php tests/Unit/Services/AdminMediaUploadServiceTest.php
git commit -m "feat: add resilient admin media uploads"
```

### Task 14: Regenerate Routes, Format, And Run The Final Verification Set

**Files:**

- Modify: generated Wayfinder outputs if present
- Test: all created tests above

- [ ] **Step 1: Regenerate typed route helpers after route/controller changes**

Run: `php artisan wayfinder:generate --with-form --no-interaction`
Expected: PASS and generated route helpers updated.

- [ ] **Step 2: Format PHP files**

Run: `vendor/bin/pint --dirty --format agent`
Expected: PASS with any style corrections applied.

- [ ] **Step 3: Run focused backend test suites**

Run: `php artisan test --compact tests/Feature/Public/PublicHomeTest.php tests/Feature/Public/PublicCategoryFlowTest.php tests/Feature/Admin/FilamentAdminAccessTest.php tests/Feature/Admin/AdminInvitationFlowTest.php tests/Feature/Console/SyncStoyanKolevAssetsCommandTest.php tests/Unit/Repositories/EloquentCategoryRepositoryTest.php tests/Unit/Repositories/EloquentVideoRepositoryTest.php tests/Unit/Repositories/EloquentUserRepositoryTest.php tests/Unit/Services/MediaUrlResolverTest.php tests/Unit/Services/AssetSyncServiceTest.php tests/Unit/Services/AdminInvitationServiceTest.php tests/Unit/Services/AdminMediaUploadServiceTest.php tests/Browser/PublicExperienceTest.php`
Expected: PASS.

- [ ] **Step 4: Run frontend checks**

Run: `npm run types:check`
Expected: PASS.

Run: `npm run lint:check`
Expected: PASS.

Run: `npm run build`
Expected: PASS.

Run: `php artisan route:list --except-vendor | rg '/login|/register|forgot-password|reset-password|email/verify|two-factor-challenge|settings'`
Expected: no matches for removed public auth/settings surfaces.

- [ ] **Step 5: Commit the verified implementation state**

```bash
git add -A
git commit -m "feat: complete stoyan kolev video platform"
```

## Notes For Execution

- Prefer creating migrations, models, tests, and Filament resources with `php artisan make:* --no-interaction` during execution, then refine them manually.
- If `wayfinder:generate` does not produce files automatically through the Vite plugin setup, keep the manual generation step in the workflow.
- If the public page implementation exposes more than one controller method or route concern, split controllers further rather than letting them grow.
- If Filament resource classes start absorbing orchestration logic, move that logic back into services immediately.
- If implementation reveals the need for a dedicated local mirrored-media disk, add it through config and keep the path documented in `config/stoyan_kolev.php`.
