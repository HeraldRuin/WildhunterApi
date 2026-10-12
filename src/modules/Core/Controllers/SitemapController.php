<?php

namespace Modules\Core\Controllers;

use Illuminate\Http\Response;
use Modules\Core\Services\SitemapService;

class SitemapController
{
    public function __invoke(SitemapService $sitemap): Response
    {
        return response($sitemap->xml(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
