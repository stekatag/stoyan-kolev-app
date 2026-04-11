<?php

namespace App\Http\Controllers;

use App\Services\PublicCatalogService;
use Inertia\Inertia;
use Inertia\Response;

class PublicHomeController extends Controller {
    public function __invoke(PublicCatalogService $publicCatalogService): Response {
        return Inertia::render('Public/Home', $publicCatalogService->buildHomepagePayload());
    }
}
