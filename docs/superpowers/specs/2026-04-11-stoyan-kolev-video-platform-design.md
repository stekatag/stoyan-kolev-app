# Stoyan Kolev Video Platform Design

Date: 2026-04-11

## Summary

Build a Laravel 13 + Inertia Vue + Filament application for browsing and managing Stoyan Kolev videos.

The public site is fully open and centered around the existing meme-style landing image in `assets/initial screen/initial screen.png`. Users first see an intro video once per browser via local storage. After that, they land on the main image and interact with six hotspot-mapped categories. Categories with one visible video open directly into playback. Categories with multiple visible videos open a modal with a thumbnail grid first.

The admin experience is a protected Filament panel. Admins manage categories, videos, thumbnails, visibility, sort order, and admin invitations. Media is bucket-first from day one, using the configured S3-compatible storage. Local assets remain the fallback and recovery source. A repeatable Laravel sync/import command can repopulate the bucket and reconcile the database from local assets whenever storage must be rebuilt or replaced.

## Goals

- Keep the public site authentication-free.
- Preserve the existing meme image as the homepage experience.
- Use responsive clickable hotspots mapped to the current six categories.
- Show the intro video on first visit only, with skip and do-not-show-again behavior stored in local storage.
- Manage videos and categories through Filament.
- Support safe admin onboarding without SMTP via shareable invitation links.
- Use the storage bucket as the primary runtime media store from day one.
- Fall back to local assets when bucket access or media availability fails.
- Provide a repeatable recovery command to upload local assets into a new bucket and reconcile records.

## Non-Goals

- Public user accounts or user-specific personalization.
- Email-based invitations or password reset flows that depend on SMTP.
- A fully dynamic public homepage layout in version one.
- Signed media URLs in version one.
- Automatic thumbnail extraction from uploaded videos in version one.

## Architecture

The system has two explicit surfaces.

### Public Surface

The public surface is a single Inertia/Vue application with no authentication. It provides:

- First-visit intro video gate.
- Meme-image homepage with six fixed hotspots.
- Category modal flow for categories with multiple videos.
- Direct video playback flow for categories with exactly one visible video.
- Transparent resolution of bucket-backed or local-fallback media URLs.

The public frontend should receive already-resolved URLs and normalized view data from the backend. Storage concerns should not be embedded in Vue components.

### Admin Surface

The admin surface is a protected Filament panel with Fortify-backed authentication for admins only. It provides:

- Category CRUD.
- Video CRUD with bucket uploads.
- Thumbnail management.
- Visibility and ordering controls.
- Invite management for additional admins.

The public site must not expose any auth UI or registration entry points.

### Service Boundaries

To keep responsibilities clear, storage and media resolution should be centralized in dedicated backend services rather than spread across controllers, models, and frontend code.

Recommended service boundaries:

- `PublicCatalogService`: fetches visible categories and videos for the public site.
- `MediaUrlResolver`: resolves playable and image URLs, preferring the bucket and falling back to local assets.
- `AssetSyncService`: imports local assets, uploads to the bucket, and reconciles database records.
- `AdminInviteService`: creates, signs, validates, and consumes admin invitation links.

Runtime requests should not probe remote object storage on every page load. Availability should be derived from persisted metadata that is updated during uploads, sync runs, and explicit validation routines.

## Data Model

### Categories

Categories should be admin-managed records, but only some categories appear on the public hotspot homepage.

Recommended fields:

- `id`
- `name`
- `slug`
- `description` nullable
- `is_visible`
- `sort_order`
- `homepage_hotspot_key` nullable
- `modal_preview_image_path` nullable
- timestamps

The `homepage_hotspot_key` binds one record to one of the six fixed homepage areas. Categories without a hotspot key can still exist in Filament for future use, but they do not automatically appear on the landing image.

For visible homepage categories, `homepage_hotspot_key` must be unique. The admin panel should prevent assigning the same active hotspot key to multiple visible categories.

Expected current hotspot keys:

- `qdosan`
- `uchuden`
- `kaish`
- `izmoren`
- `vesel`
- `izpederastql`

### Videos

Videos belong to categories and are the primary public content objects.

Recommended fields:

- `id`
- `category_id`
- `title`
- `slug`
- `description` nullable
- `is_visible`
- `sort_order`
- `bucket_video_key` nullable
- `local_video_path` nullable
- `bucket_thumbnail_key` nullable
- `local_thumbnail_path` nullable
- `video_storage_status`
- `thumbnail_storage_status`
- `source_type`
- `original_filename` nullable
- `mime_type` nullable
- `duration_seconds` nullable
- timestamps

Suggested semantics:

- `video_storage_status` and `thumbnail_storage_status` independently describe whether each asset is available in the bucket, local fallback, or both, and are updated during upload, sync, or repair actions instead of through request-time bucket probing.
- `source_type` distinguishes admin-uploaded content from imported local assets.

Both bucket keys and local fallback paths are first-class fields. A single path per asset is not sufficient for the required bucket-first plus local-fallback behavior.

### Admin Invitations

Admin access should be controlled through invitation records.

Recommended fields:

- `id`
- `email`
- `token_hash`
- `expires_at`
- `consumed_at` nullable
- `created_by_user_id`
- timestamps

Invitation links should be tied to a specific email and should be single-use.

### Users

Existing users remain the authentication model for admins. Public visitors do not get user records.

Recommended additions:

- `is_admin` boolean or equivalent authorization mechanism.

Version one only needs admin authorization, not role hierarchies.

## Storage Strategy

### Primary Runtime Storage

The configured S3-compatible bucket is the primary runtime store for video and thumbnail delivery.

Filament uploads should target the bucket by default. Public playback and thumbnail display should use direct public bucket URLs in version one.

Admin-uploaded media must also be mirrored into a managed local recovery area so that bucket rebuilds are able to restore both imported assets and later admin-created assets. Recovery cannot rely only on the original checked-in asset folders.

For admin uploads, the bucket write and local recovery mirror write form one logical save operation. If the bucket upload succeeds but the recovery mirror write fails, the upload must be treated as failed, the record must not become visible/published, and the system should attempt cleanup of the remote object or mark the upload incomplete for explicit operator repair. The recovery guarantee cannot depend on best-effort mirroring.

### Local Fallback Strategy

Local assets remain critical for recovery and fallback. The application should be able to resolve media from local files when:

- bucket credentials are invalid
- the bucket is temporarily unavailable
- a specific object is missing remotely
- a new environment has not yet been fully synced

Local assets should be treated as the last-resort source for runtime delivery and the primary source for recovery imports.

### Media Resolution Rules

`MediaUrlResolver` should resolve URLs in this order:

1. Public bucket URL for the stored object path if the object is available.
2. Local asset URL if a mapped local fallback file exists.
3. A safe placeholder image for thumbnails.
4. A non-playable unavailable state for videos that cannot be resolved.

The frontend should not be responsible for probing storage backends.

Object availability should come from persisted storage metadata and repair workflows, not from per-request remote existence checks. Optional validation commands may refresh that metadata when needed. In version one, fallback guarantees apply when configuration is invalid, storage health is known to be degraded, or sync/repair metadata marks an object as unavailable. Unexpected remote deletions that occur after the last validation pass may remain broken until metadata is refreshed by a repair workflow.

## Fixed Public Assets

Version one includes two fixed public assets with explicit lifecycle rules.

- The homepage meme image is a packaged application asset and is not admin-managed in version one.
- The intro video is a fixed application-managed asset, not a Filament-managed video record in version one.

The intro video should still use the same bucket-first and local-fallback resolution strategy as other public media. Its primary bucket object should be a known configured path, with fallback to the existing local source asset when the bucket object is unavailable.

## Local Asset Import And Recovery

The repository already contains local source material under `assets/videos`, `assets/screenshots`, and `assets/initial screen`.

Version one should include an Artisan command that:

- scans local asset directories
- maps folder names to category slugs
- creates or updates categories
- creates or updates video records
- uploads missing media into the configured bucket
- updates database paths and storage metadata
- supports safe re-runs for recovery or migration

This command is part of the operational design, not a one-off development helper. It is how the app recovers from bucket replacement or bucket migration.

Expected command responsibilities:

- idempotent reconciliation rather than blind duplication
- clear console output for created, updated, skipped, and failed items
- mandatory dry-run support
- explicit failure reporting when bucket credentials or connectivity are broken
- support restoring admin-uploaded managed media from the local recovery mirror as well as imported repository assets

## Public Experience

### Intro Video Flow

On the first visit in a browser, the user sees the intro video before entering the homepage.

Required behavior:

- show automatically on first visit
- allow `Skip`
- allow `Don't show again`
- persist dismissal choice in local storage when either `Skip` or `Don't show again` is used
- do not re-show after dismissal unless browser storage is cleared

This is a frontend-only preference and does not require server-side persistence.

### Homepage

The homepage should use `assets/initial screen/initial screen.png` as its visual centerpiece.

The image should not be reimagined into a generic card grid. Instead, the page should overlay six responsive hotspots aligned with the faces in the image.

Requirements:

- desktop and mobile friendly positioning
- obvious interactive affordance on hover and tap
- maintain image fidelity
- keep hotspot mapping stable through category slugs or hotspot keys

### Category Interaction

When a hotspot is clicked:

- if the category has zero visible videos, show an empty state
- if the category has one visible video, open the playback viewer immediately
- if the category has multiple visible videos, open a modal with a thumbnail grid

The modal design may take visual cues from the provided screenshots, but the interaction pattern should remain consistent across categories.

### Video Playback

Video playback should happen in a viewer modal or equivalent focused player experience.

Requirements:

- use direct public bucket URLs when available
- fall back to local URLs when bucket access fails
- show video title and category context
- support closing and returning to the category grid without losing context
- degrade cleanly if a video is unavailable

### Missing Thumbnail Behavior

Thumbnail upload is optional. If a video has no thumbnail:

- use a category placeholder or neutral fallback tile
- do not block the user from opening the video

## Admin Experience

### Filament Categories

Admins should be able to:

- create categories
- edit names, slugs, visibility, and sort order
- assign or clear hotspot keys
- manage an optional preview image for modal presentation

Only categories with one of the six hotspot keys appear on the homepage in version one.

### Filament Videos

Admins should be able to:

- upload a required video file
- upload an optional thumbnail
- assign a category
- control visibility
- define sort order
- edit title and description

Uploads should target the bucket first. If bucket upload fails, the admin UI should surface a real error rather than silently saving a broken record.

### Admin Authentication

The public site has no authentication, but Filament must be protected.

Admin auth requirements:

- admins log in to Filament only
- no public registration route
- no public password reset or email-verification flows
- no SMTP dependency for onboarding
- existing admins can invite new admins

### Admin Invitation Flow

Admin invitations should be created in Filament.

Flow:

1. Existing admin creates an invite with a target email and expiration.
2. The system generates a signed shareable URL.
3. The recipient opens the link and sees a password setup form.
4. The email is prefilled or locked to the invited email.
5. On success, a new user account is created as an admin and the invite is consumed.

Constraints:

- no email sending
- one-time use
- tied to a specific email
- expires automatically
- invalid or consumed tokens show a clear error state

Edge-case rules:

- if the invited email already belongs to an admin, the invite cannot be used and should resolve to a clear already-admin state
- if the invited email already belongs to any existing non-admin user, version one rejects the invite creation or acceptance flow rather than attempting account takeover or privilege upgrade
- invite consumption and new admin creation must happen atomically

## Routing And Backend Flow

### Public Routes

The public app should expose only the minimal routes needed for the Inertia homepage and category/video data retrieval.

Likely needs:

- homepage route returning the initial public catalog and hotspot mapping
- optional public endpoints for category payloads if lazy loading is preferred

### Admin Routes

Admin routes should be limited to Filament and the invite completion flow.

Likely needs:

- Filament auth and panel routes
- invite acceptance route
- password setup submission route

### Public Data Delivery

The public controller layer should deliver normalized view models to Vue, such as:

- homepage image URL
- intro video URL
- hotspot definitions
- visible categories keyed by hotspot
- video cards with resolved thumbnail and playback URLs

This should come from service classes rather than direct storage logic in controllers.

## Error Handling

### Public Errors

Public users should not see infrastructure-level details.

Required behavior:

- if a thumbnail is missing, show a fallback image
- if a video is missing, show a graceful unavailable state
- if a category is empty, show a simple empty modal state
- if bucket access fails, serve local fallback media when available

### Admin Errors

Admins need actionable failures.

Required behavior:

- bucket upload failures are shown clearly in Filament
- invalid invitation tokens show explicit consumed/expired messaging
- sync/import command reports failures item by item
- hotspot-key conflicts are prevented in admin validation and reported clearly

## Testing Strategy

Testing should focus on the boundaries that are easy to break.

### Backend Tests

Feature and unit coverage should include:

- homepage catalog only includes visible categories/videos
- single-video categories bypass the grid modal flow in the delivered payload
- invite creation and invite consumption rules
- token expiration and one-time-use behavior
- media URL resolution preferring bucket and falling back to local
- importer/sync reconciliation behavior
- rejection behavior for invites targeting already-existing users
- hotspot-key uniqueness behavior
- storage metadata behavior for bucket-backed, local-only, and dual-available media
- repair behavior after partial bucket loss or bucket migration

### Filament Tests

Filament-oriented tests should cover:

- admin access protection
- category creation and editing
- video creation with optional thumbnail
- upload failure behavior
- invite creation from the admin panel

### Frontend Tests

Frontend tests should cover:

- intro local-storage gating
- hotspot click behavior
- modal rendering for multi-video categories
- direct-open behavior for single-video categories
- missing-thumbnail fallbacks
- intro dismissal persistence after `Skip`

## Incremental Delivery Plan

The implementation can be staged, but the design stays cohesive.

Suggested order:

1. Create schema and models for categories, videos, and admin invites.
2. Configure bucket-backed storage and local fallback resolution.
3. Build the importer/sync command for existing assets.
4. Build the public homepage, intro flow, hotspots, and modal/player experience.
5. Build Filament resources for categories and videos.
6. Add admin invitation flow and admin-only access controls.
7. Add tests around storage fallback, invites, and public behavior.

## Open Constraints To Preserve

These decisions are locked by the approved design:

- public site remains auth-free
- admin panel is protected
- admin onboarding uses shareable invite links, not email delivery
- homepage is the original image with hotspots, not a redesign
- categories with one video open playback directly
- categories with multiple videos open a thumbnail grid modal
- intro is first-visit only via local storage
- bucket is primary runtime media storage
- local assets remain the fallback and recovery source
- public playback uses direct public bucket URLs in version one
- categories are admin-managed, but only hotspot-keyed categories appear on the homepage
- thumbnails are optional
