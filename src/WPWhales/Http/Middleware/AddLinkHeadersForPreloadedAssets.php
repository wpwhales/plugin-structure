<?php

namespace WPWhales\Http\Middleware;

use WPWhales\Support\Collection;
use WPWhales\Support\Facades\Vite;

class AddLinkHeadersForPreloadedAssets
{
    /**
     * Handle the incoming request.
     *
     * @param  \WPWhales\Http\Request  $request
     * @param  \Closure  $next
     * @return \WPWhales\Http\Response
     */
    public function handle($request, $next)
    {
        return tap($next($request), function ($response) {
            if (Vite::preloadedAssets() !== []) {
                $response->header('Link', Collection::make(Vite::preloadedAssets())
                    ->map(fn ($attributes, $url) => "<{$url}>; ".implode('; ', $attributes))
                    ->join(', '));
            }
        });
    }
}
