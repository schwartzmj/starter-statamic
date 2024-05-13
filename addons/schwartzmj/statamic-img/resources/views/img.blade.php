@php /** @var \Schwartzmj\StatamicImg\Img $img */ @endphp
    <!--
    TODO: this used to be wrapped in <picture>, but I noticed that things like order-first and order-last don't work with <picture> wrapping it (picture is block so doesn't respect e.g. the continer's grid or flex). Let's just use img srcset and sizes instead of picture for now.
-->
{{--    @foreach (($breakpoints ?? []) as $breakpoint)--}}
{{--        @foreach($breakpoint->sources() ?? [] as $source)--}}
{{--            @php--}}
{{--                $srcSet = $source->getSrcset();--}}
{{--            @endphp--}}

{{--            @if($srcSet !== null)--}}
{{--                <source--}}
{{--                    @if($type = $source->getMimeType()) type="{{ $type }}" @endif--}}
{{--                    @if($media = $source->getMediaString()) media="{{ $media }}" @endif--}}
{{--                    srcset="{{ $srcSet }}"--}}
{{--                >--}}
{{--            @endif--}}
{{--        @endforeach--}}
{{--    @endforeach--}}

{{--    <img--}}
{{--        {!! $attributeString ?? '' !!}--}}
{{--        src="{{ $asset->url() }}"--}}
{{--        alt="{{ $alt }}"--}}
{{--        @isset($width) width="{{ $width }}" @endisset--}}
{{--        @isset($height) height="{{ $height }}" @endisset--}}
{{--        loading="{{ $loading }}"--}}
{{--    >--}}
@php
    //    ray($img);
@endphp

{{--@if(app()->environment('production'))--}}
{{--    <img--}}
{{--        src="{{ $img->asset->url() }}"--}}
{{--        alt="{{ $img->asset->alt }}"--}}
{{--        {!! $img->getArbitraryAttributesString() !!}--}}
{{--        srcset="{{ $img->getSrcsetString() }}"--}}
{{--        sizes="{{ $img->getSizesString() }}"--}}
{{--    />--}}
{{--@else--}}
{{--    <img--}}
{{--        src="{{ $img->asset->url() }}"--}}
{{--        alt="{{ $img->asset->alt }}"--}}
{{--        {!! $img->getArbitraryAttributesString() !!}--}}
{{--        srcset="{{ $img->getSrcsetString() }}"--}}
{{--        sizes="{{ $img->getSizesString() }}"--}}
{{--    />--}}
{{--@endif--}}

<img
    {!! $img->getArbitraryAttributesString() !!}
    src="{{ $img->asset->url() }}"
    alt="{{ $img->alt }}"
    srcset="{{ $img->getSrcsetString() }}"
    sizes="{{ $img->getSizesString() }}"
    width="{{ $img->asset->width() }}"
    height="{{ $img->asset->height() }}"
    loading="{{ $img->loading }}"
/>


{{--public function getSrcsetString(): string--}}
{{--{--}}
{{--return "{$this->asset->url()} {$this->width}w";--}}
{{--}--}}

{{--public function getSizesString(): string--}}
{{--{--}}
{{--return "(max-width: {$this->breakpointPx}px) {$this->width}px";--}}
{{--}--}}
