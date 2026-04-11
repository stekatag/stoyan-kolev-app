<?php

namespace App\Console\Commands;

use App\Services\Media\AssetSyncService;
use Illuminate\Console\Command;
use RuntimeException;

class SyncStoyanKolevAssetsCommand extends Command {
    protected $signature = 'stoyan:sync-assets';

    protected $description = 'Upload locally prepared Stoyan Kolev assets to the configured bucket.';

    public function handle(AssetSyncService $assetSyncService): int {
        try {
            $this->line('Starting asset sync...');

            $result = $assetSyncService->sync(function (string $bucketKey): void {
                $this->line('Uploading ' . $bucketKey);
            })->toArray();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(['Metric', 'Count'], collect($result)->map(fn($count, $metric) => [$metric, $count])->all());

        return self::SUCCESS;
    }
}
