<picture>
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
</picture>