<?php

namespace Schwartzmj\StatamicImg;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Schwartzmj\StatamicImg\Adapters\CloudflareAdapter;
use Schwartzmj\StatamicImg\Adapters\GlideAdapter;
use Statamic\Assets\Asset;
use Statamic\Tags\Parameters;

class Img
{
    /** @var Collection<Size> */
    public Collection $sizes;
    private Parameters $arbitraryParams;
    public int $maxWidth;
    public string $alt;
    public string $loading;

//    private array $breakpointMap = [
//        'sm' => 640,
//        'md' => 768,
//        'lg' => 1024,
//        'xl' => 1280,
//        '2xl' => 1536,
//    ];

    /**
     * Attributes we use and manipulate that we do not want to pass to the img tag directly. In other words, a filter for "arbitrary" attributes.
     */
    private array $reservedImgParams = [
        'src',
        'width',
        'height',
        'maxWidth',
        'sizes',
        'alt',
        'srcset',
        'transforms',
        'loading',
    ];

    /**
     * @throws Exception
     */
    function __construct(
        public Asset      $asset,
        public Parameters $parameters,
    )
    {
        if (!$this->asset->isImage()) {
            throw new Exception("Asset {$this->asset->id()} is not an image. Cannot create responsive variants.");
        }
        $asset_width = $this->asset->width();
        $asset_height = $this->asset->height();
        if (!$asset_width || !$asset_height) {
            throw new Exception("Asset {$this->asset->id()} has 0 width or height. Cannot create responsive variants.");
        }
        $asset_ratio = $asset_width / $asset_height;

        $this->maxWidth = (int)$this->parameters->get('maxWidth', 1600);
        $this->alt = $this->parameters->get('alt', $this->asset->alt ?? '');
        $this->loading = $this->parameters->get('loading', 'lazy');
        $this->arbitraryParams = $this->parameters->except($this->reservedImgParams);

        $this->bootSizes();
    }

    private function bootSizes(): void
    {
        $sizes = Str::squish($this->parameters->get('sizes', '100vw'));
        $this->sizes = collect();
        foreach (explode(' ', $sizes) as $size) {
            $size = new Size(size: $size, maxWidth: $this->maxWidth);
            if ($size->isValid) {
                $this->sizes->push($size);
            }
        }
        $this->sizes->sortBy('breakpointWidth');
        // Fill in any missing sizes
        /** @var Collection<Size> $new_sizes */
        $new_sizes = collect();
        $breakpoints = Breakpoint::cases();
        /** @var Size $previous_size */
        $previous_size = $this->sizes->first();
        foreach ($breakpoints as $bp) {
            $corresponding_size = $this->sizes->first(function ($size) use ($bp) {
                return $size->breakpoint === $bp;
            });
            if (!$corresponding_size) {
                $new_size = new Size(size: "{$bp->name}:{$previous_size->widthValue}{$previous_size->widthUnit}", maxWidth: $this->maxWidth);
                $new_sizes->push($new_size);
            } else {
                $previous_size = $corresponding_size;
            }
        }
        $this->sizes = $this->sizes->merge($new_sizes);
        $this->sizes->sortBy('breakpointWidth');
    }

    public function getArbitraryAttributesString(): string
    {
        return collect($this->arbitraryParams)
            ->map(function ($value, $name) {
                return $name . '="' . $value . '"';
            })->implode(' ');
    }

    public function getSrcsetString(): string
    {
        return $this->sizes->map(function ($size) {
            $size_to_render = $size->getSizeToRender();
            $url = $this->asset->url();
            if (app()->environment('production')) {
                $url = CloudflareAdapter::toUrl($size_to_render, $this);
            } else {
                $url = GlideAdapter::toUrl($size_to_render, $this);
            }
            return "{$url} {$size_to_render}w";
        })->implode(', ');
    }

    public function getSizesString(): string
    {
        $htmlSizes = '';

        foreach (Breakpoint::cases() as $bp) {
            $corresponding_size = $this->sizes->first(function ($size) use ($bp) {
                return $size->breakpoint === $bp;
            });
            if ($corresponding_size) {
                $htmlSizes .= "(max-width: {$bp->value}px) {$corresponding_size->getSizeToRender()}px, ";
            }
        }
        $last_size = $this->sizes->last();
        $htmlSizes .= "{$last_size->getSizeToRender()}px";
        return $htmlSizes;
    }
}
