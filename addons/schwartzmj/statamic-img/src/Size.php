<?php

namespace Schwartzmj\StatamicImg;

use Exception;
use Illuminate\Support\Str;

class Size
{
//    public string $breakpointName;
//    public int $breakpointWidth;
    public int $widthValue;
    public ?Breakpoint $breakpoint = null;
    public string $widthUnit;
    public bool $isValid;
    private static array $validSuffixes = ['px', 'vw'];

    /**
     * @throws Exception
     */
    function __construct(public string $size)
    {
        $this->size = Str::trim($size);
//        try {
        $exploded = explode(':', $this->size);
        if (count($exploded) === 2) {
            $this->bootPrefixedSize($exploded[0], $exploded[1]);
        } else {
            $this->bootDefaultSize($this->size);
        }
        $this->isValid = true;
//        } catch (Exception $e) {
//            $this->isValid = false;
//            $this->breakpointName = 'default';
//            $this->breakpointWidth = 0;
//            $this->widthValue = 100;
//            $this->widthUnit = 'vw';
//            Log::error('Unable to parse size', ['message' => $e->getMessage()]);
//        }

    }

    /**
     * @throws Exception
     */
    private function bootPrefixedSize(string $prefix, string $size): void
    {
//        $this->breakpointName = $prefix;
        $breakpoint_width = Breakpoint::tryGetValueFromKey($prefix);
        if ($breakpoint_width === null) {
            throw new Exception("Invalid breakpoint name: $prefix");
        }
        $this->breakpoint = $breakpoint_width;
//        $this->breakpointWidth = $breakpoint_width->value;
        $this->parseSize($size);
    }

    /**
     * @throws Exception
     */
    private function bootDefaultSize(string $size): void
    {
//        $this->breakpointName = 'default';
//        $this->breakpointWidth = 0;
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
                break;
            }
        }

        if (!isset($this->widthValue)) {
            throw new Exception("Invalid size suffix: $size");
        }
    }

    public function getSizeToRender(): int
    {
        $bp_width = $this->breakpoint ? $this->breakpoint->value : Breakpoint::sm->value;
        if ($this->widthUnit === 'px') {
            return min($bp_width, $this->widthValue);
        }
        if ($this->widthUnit === 'vw') {
            return round($bp_width * ($this->widthValue / 100));
        }
        // Assume vw TODO: how do we want to handle this
        return round($bp_width * ($this->widthValue / 100));
    }
}
