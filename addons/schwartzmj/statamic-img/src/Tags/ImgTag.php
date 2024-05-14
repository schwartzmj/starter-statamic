<?php

namespace Schwartzmj\StatamicImg\Tags;

use Exception;
use Illuminate\Support\Facades\Log;
use Schwartzmj\StatamicImg\Img;
use Statamic\Assets\Asset;
use Statamic\Assets\OrderedQueryBuilder;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Fields\Value;
use Statamic\Tags\Tags;

class ImgTag extends Tags
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
        $is_production_env = app()->environment('production');
        try {
            $this->asset = $this->retrieveAsset($this->params->get('src'));
            // TODO: flesh this out more, and maybe in the Img class?
            if ($this->asset->extension == 'svg' || $this->asset->extension == 'gif') {
                return "<img src=\"{$this->asset->url()}\" alt=\"{$this->asset->alt}\" />";
            }
        } catch (Exception $e) {
            if ($is_production_env) {
                Log::error('Unable to retrieve asset in ImgTag, rendering empty string.', [
                    'message' => $e->getMessage(),
                    'params' => $this->params->all(),
                ]);
                return "";
            }
            throw $e;
        }
        try {
            $img = new Img(
                asset: $this->asset,
                parameters: $this->params,
            );
        } catch (Exception $e) {
            if ($is_production_env) {
                Log::error('Error in Img.php, rendering raw img tag w/ asset url', [
                    'message' => $e->getMessage(),
                    'params' => $this->params->all(),
                ]);
                return "<img src=\"{$this->asset->url()}\" alt=\"{$this->asset->alt}\" />";
            }
            throw $e;
        }
        return view('statamic-img::img', [
            'img' => $img,
        ])->render();
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

            if (!$asset) {
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

            if (!$asset) {
                $asset = AssetFacade::findByPath($assetParam);
            }
        }

        if (is_array($assetParam) && isset($assetParam['url'])) {
            $asset = AssetFacade::findByUrl($assetParam['url']);
        }

        if (!isset($asset)) {
            throw new Exception('Asset not found');
        }

        if ($asset instanceof OrderedQueryBuilder) {
            $asset = $asset->first();
        }

        return $asset;
    }
}
