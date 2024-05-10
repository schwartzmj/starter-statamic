<?php

namespace Schwartzmj\StatamicImg;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Schwartzmj\StatamicImg\Exceptions\InvalidAssetException;
use Statamic\Assets\Asset;
use Statamic\Assets\OrderedQueryBuilder;
use Statamic\Exceptions\AssetNotFoundException;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Fields\Value;
use Statamic\Support\Str;
use Statamic\Tags\Parameters;

class Responsive
{
    /** @var Asset */
    public Asset $asset;

    /** @var \Statamic\Tags\Parameters */
    public $parameters;

    /** @var Collection<Breakpoint> */
    private Collection $breakpoints;

    /**
     * @param $assetParam
     * @param Parameters $parameters
     * @throws AssetNotFoundException
     * @throws InvalidAssetException
     */
    public function __construct($assetParam, Parameters $parameters)
    {
        $this->parameters = $parameters;

//    if ($assetParam instanceof Value && $assetParam->fieldtype() instanceof ResponsiveFieldtype) {
//    $assetParam = $assetParam->value();
//    }

        if (is_array($assetParam) && isset($assetParam['src'])) {
            $this->parameters = collect($assetParam)->map(function ($value) {
                return $value instanceof Value ? $value->value() : $value;
            })->merge($this->parameters->toArray())->except('src');

            $assetParam = $assetParam['src'];
        }

        $this->asset = $this->retrieveAsset($assetParam)->hydrate();

        if ((int)$this->asset->width() === 0 || (int)$this->asset->height() === 0) {
            throw InvalidAssetException::zeroWidthOrHeight($this->asset);
        }
    }

    /**
     * @param $assetParam
     * @return Asset
     * @throws AssetNotFoundException
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
            throw AssetNotFoundException::create($assetParam);
        }

        if ($asset instanceof OrderedQueryBuilder) {
            $asset = $asset->first();
        }

        return $asset;
    }

    /**
     * @return Collection<Breakpoint>
     */
    public function breakPoints(): Collection
    {
        if (isset($this->breakpoints)) {
            return $this->breakpoints;
        }

        $parametersByBreakpoint = $this->parametersByBreakpoint();

        $defaultParams = $parametersByBreakpoint->get('default') ?? collect();
        $currentParams = array_merge([
            'src' => $this->asset,
        ], $defaultParams->mapWithKeys(function ($param) {
            return [$param['key'] => $param['value']];
        })->toArray());

        $breakpoints = $parametersByBreakpoint
            ->map(function (Collection $parameters, string $breakpoint) use (&$currentParams) {
                $value = config("statamic.responsive-images.breakpoints.$breakpoint");

                if (!$value && $breakpoint !== 'default') {
                    return null;
                }

                foreach ($parameters as $parameter) {
                    if ($parameter['key'] === 'src' && !$parameter['value'] instanceof Asset) {
                        try {
                            $parameter['value'] = $this->retrieveAsset($parameter['value'])->hydrate();
                        } catch (AssetNotFoundException $e) {
                            logger()->error($e->getMessage());
                            $parameter['value'] = $this->asset;
                        }

                        if ((int)$parameter['value']->width() === 0 || (int)$parameter['value']->height() === 0) {
                            throw InvalidAssetException::zeroWidthOrHeight($parameter['value']);
                        }
                    }

                    if (Str::contains($parameter['key'], 'ratio') && Str::contains($parameter['value'], '/')) {
                        [$width, $height] = explode('/', $parameter['value']);
                        $parameter['value'] = (float)$width / (float)$height;
                    }

                    $currentParams[$parameter['key']] = $parameter['value'];
                }

                return new Breakpoint(
                    $currentParams['src'],
                    $breakpoint,
                    $value ?? 0,
                    Arr::except($currentParams, ['src'])
                );
            })
            ->filter();

        $defaultBreakpoint = $breakpoints->first(function (Breakpoint $breakpoint) {
            return $breakpoint->label === 'default';
        });

        if (!$defaultBreakpoint) {
            $breakpoints->prepend(new Breakpoint($this->asset, 'default', 0, [
                'ratio' => $this->asset->width() / $this->asset->height(),
            ]));
        }

        return $this->breakpoints = $breakpoints
            ->sortByDesc('minWidth')
            ->values();
    }

    public function defaultBreakpoint(): Breakpoint
    {
        return $this->breakPoints()->first(function (Breakpoint $breakpoint) {
            return $breakpoint->label === 'default';
        });
    }

    private function parametersByBreakpoint(): Collection
    {
        $breakpoints = collect(config('statamic.responsive-images.breakpoints'));

        return collect($this->parameters)
            ->map(function ($value, $key) use ($breakpoints) {
                $prefix = explode(':', $key)[0];

                if (!$breakpoints->keys()->contains($prefix)) {
                    $prefix = 'default';
                }

                return [
                    'prefix' => $prefix,
                    'key' => str_replace($prefix . ':', '', $key),
                    'value' => $value,
                    'breakpoint' => $breakpoints->get($prefix) ?? 0,
                ];
            })
            ->values()
            ->sortBy('breakpoint')
            ->groupBy('prefix');
    }
}
