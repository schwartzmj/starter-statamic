<?php

namespace Schwartzmj\StatamicImg\Tags;

use Exception;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Statamic\Assets\Asset;
use Statamic\Tags\Tags;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Assets\OrderedQueryBuilder;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Fields\Value;
use Statamic\Tags\Parameters;

class Img extends Tags
{
    protected static $handle = 'img';
    // See: https://github.com/spatie/statamic-responsive-images/blob/main/src/Tags/ResponsiveTag.php
    // And: https://github.com/spatie/statamic-responsive-images/blob/main/src/Responsive.php

    private array $breakpoints = [
        'sm' => 640,
        'md' => 768,
        'lg' => 1024,
        'xl' => 1280,
        '2xl' => 1536,
    ];
    private Asset $asset;

    // TODO
    // ability to add *any* other attributes to the img tag

    /**
     * @return string|array
     * @throws Exception
     */
    public function index(): array|string
    {
        try {
            $this->asset = $this->retrieveAsset($this->params->get('src'));
        } catch (Exception $e) {
            if (app()->environment('production')) {
                Log::error('Unable to retrieve asset in Img tag', ['message' => $e->getMessage()]);
                return '';
            }
            throw $e;
        }
//        $maybe_asset = $this->params->get('src');
//        // ray()->table([
//        //    'asset' => $asset,
//        //       'asset' => $asset,
//        // ]);
//        // Check if $maybe_asset is a string
//        if (is_string($maybe_asset)) {
//            $maybe_asset = $this->context->get($maybe_asset);
//        }
//
//        $class = match ($maybe_asset::class) {
//            \Statamic\Assets\Asset::class => 'Asset',
//            \Statamic\Fields\Value::class => 'Value',
//            \Statamic\Fieldtypes\Assets\Assets::class => 'AssetsFieldtype',
//            default => 'Not Allowed',
//        };
//        ray()->table([
//           'src' => $this->params->get('src'),
//              'class' => $class,
//        ]);
//        if ($class === 'Not Allowed') {
//            throw new Exception('The {{ img }} tag only accepts an asset or value.');
//        }
//        if ($class === 'Value') {
//            $maybe_asset = $maybe_asset->value();
//        }
//        if (!$maybe_asset instanceof \Statamic\Assets\Asset) {
//            throw new Exception('The {{ img }} tag only accepts an asset.');
//        }
//        $this->asset = $maybe_asset;
        return $this->renderToString();
    }

    /**
     * @param $tag
     * @return string
     */
    public function wildcard($tag): string
    {
        $this->params->put('src', $this->context->get($tag));
        return $this->index();
    }

    private function getAttributeString(): string
    {
        $breakpointPrefixes = collect(array_keys($this->breakpoints))
            ->map(function ($breakpoint) {
                return "{$breakpoint}:";
            })->toArray();

        $attributesToExclude = ['src', 'placeholder', 'webp', 'avif', 'ratio', 'glide:', 'default:', 'quality:', 'loading', 'alt'];

        return collect($this->params)
            ->reject(function ($value, $name) use ($breakpointPrefixes, $attributesToExclude) {
                if (Str::contains($name, array_merge($attributesToExclude, $breakpointPrefixes))) {
                    return true;
                }

                return false;
            })
            ->map(function ($value, $name) {
                return $name . '="' . $value . '"';
            })->implode(' ');
    }

    private function renderToString(): string {
        $asset_alt = $this->asset->get('alt', "");
        $alt = $this->params->get('alt', $asset_alt);
        $width = $this->asset->width() ?? 0;
        $height = $this->asset->height() ?? 0;
        $loading = $this->params->get('loading', 'lazy');

        // return "<img src=\"{$this->asset->url()}\" alt=\"$alt\" width=\"$width\" height=\"$height\" loading=\"$loading\" {$this->getAttributeString()} />";
        return view('statamic-img::img', [
            'asset' => $this->asset,
            'alt' => $alt,
            'width' => $width,
            'height' => $height,
            'loading' => $loading,
            'attributeString' => $this->getAttributeString(),
        ])->render();
    }

    /**
     * @param $assetParam
     * @return Asset
     * @throws Exception
     */
    private function retrieveAsset($assetParam): Asset
    {
        if ($assetParam instanceof Asset) {
            return $assetParam;
        }

        if (is_string($assetParam)) {
            $asset = AssetFacade::findByUrl($assetParam);

            if (! $asset) {
                $asset = AssetFacade::findByPath($assetParam);
            }
        }

        if ($assetParam instanceof Value) {
            $asset = $assetParam->value();

            if (isset($asset) && method_exists($asset, 'first')) {
                $asset = $asset->first();
            }
        }

        if (isset($asset) && is_string($asset)) {
            $asset = AssetFacade::findByUrl($assetParam);

            if (! $asset) {
                $asset = AssetFacade::findByPath($assetParam);
            }
        }

        if (is_array($assetParam) && isset($assetParam['url'])) {
            $asset = AssetFacade::findByUrl($assetParam['url']);
        }

        if (! isset($asset)) {
            throw new Exception('Asset not found');
        }

        if ($asset instanceof OrderedQueryBuilder) {
            $asset = $asset->first();
        }

        return $asset;
    }
}
