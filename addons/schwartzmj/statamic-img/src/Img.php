<?php

namespace Schwartzmj\StatamicImg;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Assets\Asset;
use Statamic\Tags\Parameters;

class Img
{
    public ImgBreakpoint $defaultBreakpoint;
    /** @var Collection<ImgBreakpoint> */
    public Collection $imgBreakpoints;
    private array $arbitraryParams;

    private array $breakpointMap = [
        'sm' => 640,
        'md' => 768,
        'lg' => 1024,
        'xl' => 1280,
        '2xl' => 1536,
    ];

    private array $reservedImgParams = [
        'src',
        'anim',
        'background',
        'blur',
        'brightness',
        'contrast',
        'dpr',
        'gamma',
        'metadata',
        'quality',
        'rotate',
        'sharpen',
        'trim',
        'fit',
        'gravity',
        'width',
        'height',
        'maxWidth',
        'size',
    ];

    function __construct(
        public Asset      $asset,
        public Parameters $parameters,
    )
    {
        $this->handleParams();
    }

    public function handleParams()
    {
        $default_breakpoint_params = [
            'maxWidth' => $this->parameters->get('maxWidth', 1920),
            'size' => $this->parameters->get('size', 100),
        ];

        $sm_params = [];
        $md_params = [];
        $lg_params = [];
        $xl_params = [];
        $xxl_params = [];

        $this->parameters->each(function ($value, $key) use (&$default_breakpoint_params, &$sm_params, &$md_params, &$lg_params, &$xl_params, &$xxl_params) {
            if (in_array($key, $this->reservedImgParams)) {
                $default_breakpoint_params[$key] = $value;
                return;
            }

            if (Str::startsWith($key, 'sm:')) {
                $sm_params[Str::after($key, 'sm:')] = $value;
            } elseif (Str::startsWith($key, 'md:')) {
                $md_params[Str::after($key, 'md:')] = $value;
            } elseif (Str::startsWith($key, 'lg:')) {
                $lg_params[Str::after($key, 'lg:')] = $value;
            } elseif (Str::startsWith($key, 'xl:')) {
                $xl_params[Str::after($key, 'xl:')] = $value;
            } elseif (Str::startsWith($key, '2xl:')) {
                $xxl_params[Str::after($key, '2xl:')] = $value;
            } else {
                $this->arbitraryParams[$key] = $value;
            }
        });

        $this->defaultBreakpoint = new ImgBreakpoint(
            breakpointLabel: 'default',
            breakpointPx: 0,
            parameters: new Parameters($default_breakpoint_params),
            asset: $this->asset,
        );

        $previous_params = $default_breakpoint_params;

        $this->imgBreakpoints = collect($this->breakpointMap)
            ->map(function ($breakpointPx, $breakpointLabel) use ($default_breakpoint_params, $sm_params, $md_params, $lg_params, $xl_params, $xxl_params, &$previous_params) {
                $params = match ($breakpointLabel) {
                    'sm' => array_merge($previous_params, $sm_params),
                    'md' => array_merge($previous_params, $md_params),
                    'lg' => array_merge($previous_params, $lg_params),
                    'xl' => array_merge($previous_params, $xl_params),
                    '2xl' => array_merge($previous_params, $xxl_params),
                };

                $previous_params = $params;

                return new ImgBreakpoint(
                    breakpointLabel: $breakpointLabel,
                    breakpointPx: $breakpointPx,
                    parameters: new Parameters($params),
                    asset: $this->asset,
                );
            });
    }

    public function getSrcsetString(): string
    {
        return $this->imgBreakpoints->map(function (ImgBreakpoint $imgBreakpoint) {
            return $imgBreakpoint->getSrcsetString();
        })->implode(', ');
    }

    public function getSizesString(): string
    {
        return $this->imgBreakpoints->map(function (ImgBreakpoint $imgBreakpoint) {
            return $imgBreakpoint->getSizesString();
        })->implode(', ');
    }

    public function getArbitraryAttributesString(): string
    {
        return collect($this->arbitraryParams)
            ->map(function ($value, $name) {
                return $name . '="' . $value . '"';
            })->implode(' ');
    }
}
