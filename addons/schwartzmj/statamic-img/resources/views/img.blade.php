@php /** @var \Schwartzmj\StatamicImg\Img $img */ @endphp

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
