<?php

namespace Schwartzmj\StatamicImg;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Schwartzmj\StatamicImg\Adapters\CloudflareAdapter;
use Schwartzmj\StatamicImg\Adapters\GlideAdapter;
use Schwartzmj\StatamicImg\Constants\Constants;
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

        // Override max width if image is less than the given value or our default
        $provided_max_width = (int)$this->parameters->get('maxWidth', 1600);
        $this->maxWidth = min($provided_max_width, $asset_width);

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
        $this->sizes = $this->sizes->sortBy('breakpointWidth');
        // Check for a "default" first size
        $first_size = $this->sizes->first();
        if (!$first_size->isDefaultBreakpoint()) {
            $default_size = new Size(size: "100vw", maxWidth: $this->maxWidth);
            $this->sizes->prepend($default_size);
        }

        // Fill in any missing sizes
        /** @var Collection<Size> $new_sizes */
        $new_sizes = collect();

        $previous_size = $this->sizes->first();
        foreach (Constants::BREAKPOINTS as $bp_name => $bp_width) {
            $corresponding_size = $this->sizes->first(function ($size) use ($bp_name, $bp_width) {
                return $size->breakpointWidth === $bp_width;
            });
            if (!$corresponding_size) {
                $new_size = new Size(size: "{$bp_name}:{$previous_size->widthValue}{$previous_size->widthUnit}", maxWidth: $this->maxWidth);
                $new_sizes->push($new_size);
                $previous_size = $new_size;
            } else {
                $previous_size = $corresponding_size;
            }
        }
        $this->sizes = $this->sizes->merge($new_sizes);
        $this->sizes = $this->sizes->sortBy('breakpointWidth');
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
        $already_specified_sizes = [];
        return $this->sizes->map(function ($size) use (&$already_specified_sizes) {
            if (in_array($size->sizeToRender, $already_specified_sizes)) {
                return null;
            }
            if (app()->environment('production')) {
                $url = CloudflareAdapter::toUrl($size->sizeToRender, $this);
            } else {
                $url = GlideAdapter::toUrl($size->sizeToRender, $this);
            }
            $already_specified_sizes[] = $size->sizeToRender;
            return "{$url} {$size->sizeToRender}w";
        })
            ->filter()
            ->implode(', ');
    }

    public function getSizesString(): string
    {
        $htmlSizes = '';
        $default_size = null;
        foreach ($this->sizes->sortByDesc('breakpointWidth') as $size) {
            // If it's the default size, we save it for last
            if ($size->isDefaultBreakpoint()) {
                $default_size = $size;
                continue;
            }
            $htmlSizes .= "(min-width: {$size->breakpointWidth}px) {$size->getSizesAttributeValue()}, ";
        }
        // TODO: should always be a default size, but for right now we'll prevent an error in production if we're wrong
        if ($default_size) {
            $htmlSizes .= "{$size->getSizesAttributeValue()}";
        } else {
            $htmlSizes = rtrim($htmlSizes, ', ');
        }
        return $htmlSizes;
    }
}
