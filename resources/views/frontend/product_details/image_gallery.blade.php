@php
    $photos = $detailedProduct->photos != null ? explode(',', $detailedProduct->photos) : [];
    $pd_alt = $detailedProduct->getTranslation('name');
@endphp

<div class="pd-gallery row gutters-10">
    <!-- Main images -->
    <div class="col-12">
        <div class="aiz-carousel product-gallery dots-inside-bottom arrow-inactive-transparent arrow-lg-none"
            data-nav-for='.product-gallery-thumb' data-fade='true' data-auto-height='true' data-arrows='true' data-dots='true'>
            @if ($detailedProduct->digital == 0)
                @foreach ($detailedProduct->stocks as $stock)
                    @if ($stock->image != null)
                        <div class="carousel-box img-zoom">
                            <img class="lazyload mx-auto"
                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                data-src="{{ uploaded_asset($stock->image) }}"
                                alt="{{ $pd_alt }} {{ $stock->variant }}" width="600" height="600"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </div>
                    @endif
                @endforeach
            @endif

            @foreach ($photos as $photo)
                <div class="carousel-box img-zoom">
                    <img class="lazyload mx-auto"
                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                        data-src="{{ uploaded_asset($photo) }}"
                        alt="{{ $pd_alt }}" width="600" height="600"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                </div>
            @endforeach
        </div>
    </div>

    <!-- Thumbnails (desktop) -->
    <div class="col-12 mt-3 d-none d-lg-block">
        <div class="aiz-carousel half-outside-arrow product-gallery-thumb" data-items='6' data-nav-for='.product-gallery'
            data-focus-select='true' data-arrows='true' data-vertical='false' data-auto-height='true'>
            @if ($detailedProduct->digital == 0)
                @foreach ($detailedProduct->stocks as $stock)
                    @if ($stock->image != null)
                        <div class="carousel-box c-pointer" data-variation="{{ $stock->variant }}">
                            <img class="lazyload mx-auto border"
                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                data-src="{{ uploaded_asset($stock->image) }}"
                                alt="{{ $pd_alt }} {{ $stock->variant }}" width="64" height="64"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </div>
                    @endif
                @endforeach
            @endif

            @foreach ($photos as $photo)
                <div class="carousel-box c-pointer">
                    <img class="lazyload mx-auto border"
                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                        data-src="{{ uploaded_asset($photo) }}"
                        alt="{{ $pd_alt }}" width="64" height="64"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                </div>
            @endforeach
        </div>
    </div>
</div>
