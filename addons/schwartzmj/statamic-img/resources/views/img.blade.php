<!-- 
    TODO: this used to be wrapped in <picture>, but I noticed that things like order-first and order-last don't work with <picture> wrapping it (picture is block so doesn't respect e.g. the continer's grid or flex). Let's just use img srcset and sizes instead of picture for now.
-->
    @foreach (($breakpoints ?? []) as $breakpoint)
        @foreach($breakpoint->sources() ?? [] as $source)
            @php
                $srcSet = $source->getSrcset();
            @endphp

            @if($srcSet !== null)
                <source
                    @if($type = $source->getMimeType()) type="{{ $type }}" @endif
                    @if($media = $source->getMediaString()) media="{{ $media }}" @endif
                    srcset="{{ $srcSet }}"
                >
            @endif
        @endforeach
    @endforeach

    <img
        {!! $attributeString ?? '' !!}
        src="{{ $asset->url() }}"
        alt="{{ $alt }}"
        @isset($width) width="{{ $width }}" @endisset
        @isset($height) height="{{ $height }}" @endisset
        loading="{{ $loading }}"
    >
