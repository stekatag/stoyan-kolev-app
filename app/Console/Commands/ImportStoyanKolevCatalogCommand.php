<?php

namespace App\Console\Commands;

use App\Services\Media\AssetCatalogImportService;
use Illuminate\Console\Command;

class ImportStoyanKolevCatalogCommand extends Command {
    protected $signature = 'stoyan:import-catalog';

    protected $description = 'Import the Stoyan Kolev catalog from backup assets into the database and public storage.';

    public function handle(AssetCatalogImportService $assetCatalogImportService): int {
        $result = $assetCatalogImportService->import();

        $this->table([
            'Metric',
            'Count',
        ], [
            ['categories_created', $result->categoriesCreated],
            ['videos_created', $result->videosCreated],
            ['videos_updated', $result->videosUpdated],
            ['public_copies', $result->publicCopies],
        ]);

        return self::SUCCESS;
    }
}
