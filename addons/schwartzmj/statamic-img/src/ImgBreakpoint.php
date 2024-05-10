<?php

namespace Schwartzmj\StatamicImg;

use Exception;
use Statamic\Assets\Asset;
use Statamic\Tags\Parameters;

class ImgBreakpoint
{
    public int $width;
    public int $height;

    /**
     * @throws Exception
     */
    function __construct(
        public string     $breakpointLabel,
        public int        $breakpointPx,
        public Parameters $parameters,
        public Asset      $asset,
    )
    {
        // Percent screen width
        $size = $this->parameters->get('size', 100);
        $asset_width = $this->asset->width();
        $asset_height = $this->asset->height();
        if (!$asset_width || !$asset_height) {
            throw new Exception("Asset {$this->asset->id()} has 0 width or height. Cannot create responsive variants.");
        }

        $asset_ratio = $asset_width / $asset_height;

        $this->width = min(round($this->breakpointPx * ($size / 100)), $this->parameters->get('maxWidth', 1920));
        $this->height = round($this->width / $asset_ratio);

    }

    public function getSrcsetString(): string
    {
        return "{$this->asset->url()} {$this->width}w";
    }

    public function getSizesString(): string
    {
        return "(max-width: {$this->breakpointPx}px) {$this->width}px";
    }
}
