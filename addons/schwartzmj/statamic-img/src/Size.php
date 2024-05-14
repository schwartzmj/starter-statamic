<?php

namespace Schwartzmj\StatamicImg;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Schwartzmj\StatamicImg\Constants\Constants;

class Size
{
    private static array $validSuffixes = ['px', 'vw'];
    public int $widthValue;
    public string $widthUnit;
    public bool $isValid;
    public int $sizeToRender;
    public string $breakpointName;
    public int $breakpointWidth;

    /**
     * @throws Exception
     */
    function __construct(public string $size, public int $maxWidth)
    {
        $this->size = Str::trim($size);
        try {
            $exploded = explode(':', $this->size);
            if (count($exploded) === 2) {
                $this->bootSize($exploded[0], $exploded[1]);
            } else {
                $this->bootSize('default', $this->size);
            }
            $this->sizeToRender = $this->calculateSizeToRender();
            $this->isValid = true;
        } catch (Exception $e) {
            ray('am i erroring', $size, $maxWidth)->red();
            $this->isValid = false;
            $this->widthValue = 100;
            $this->widthUnit = 'vw';
            $this->breakpointName = 'default';
            $this->breakpointWidth = Constants::BREAKPOINTS['default'];
            $this->sizeToRender = $this->calculateSizeToRender();
            Log::error('Unable to parse size', [
                'message' => $e->getMessage(),
                [
                    'this' => $this,
                ]
            ]);
        }
    }

    public function isDefaultBreakpoint(): bool
    {
        return $this->breakpointName === 'default';
    }

    /**
     * @throws Exception
     */
    private function bootSize(string $prefix, string $size): void
    {
        $breakpoint_width = Constants::BREAKPOINTS[$prefix];
        if ($breakpoint_width === null) {
            throw new Exception("Invalid breakpoint name: $prefix");
        }
        $this->breakpointName = $prefix;
        $this->breakpointWidth = $breakpoint_width;
        $this->parseSize($size);
    }

    /**
     * @throws Exception
     */
    private function parseSize(string $size): void
    {
        foreach (self::$validSuffixes as $suffix) {
            if (str_ends_with($size, $suffix)) {
                $this->widthUnit = $suffix;
                $width = (int)Str::before($size, $suffix);
                if ($width <= 0) {
                    throw new Exception("Invalid width value: $width");
                }
                $this->widthValue = $width;
                return;
            }
        }

        if (!isset($this->widthValue) || !isset($this->widthUnit)) {
            throw new Exception("Invalid size: $size");
        }
    }

    private function calculateSizeToRender(): int
    {
        if ($this->widthUnit === 'px') {
            return min($this->breakpointWidth, $this->widthValue, $this->maxWidth);
        }
        // Only other option is 'vw' so we can safely assume it's that.
        $size_to_render = round($this->breakpointWidth * ($this->widthValue / 100));
        return min($size_to_render, $this->maxWidth);
    }

    /**
     * @description Get the sizes attribute value for this size. e.g. "320px" or "100vw"
     */
    public function getSizesAttributeValue(): string
    {
        if ($this->widthUnit === 'px') {
            return "{$this->sizeToRender}px";
        }
        // Only other option is 'vw' so we can safely assume it's that.
        return "{$this->widthValue}vw";
    }
}
