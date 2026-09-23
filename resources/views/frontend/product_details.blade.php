@extends('frontend.layouts.app')

@section('meta_title'){{ $detailedProduct->meta_title }}@stop

@section('meta_description'){{ $detailedProduct->meta_description }}@stop

@section('meta_keywords'){{ $detailedProduct->tags }}@stop

@section('meta')
    @php
        $availability = "out of stock";
        $qty = 0;
        if($detailedProduct->variant_product) {
            foreach ($detailedProduct->stocks as $key => $stock) {
                $qty += $stock->qty;
            }
        }
        else {
            $qty = optional($detailedProduct->stocks->first())->qty;
        }
        if($qty > 0){
            $availability = "in stock";
        }
    @endphp
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $detailedProduct->meta_title }}">
    <meta itemprop="description" content="{{ $detailedProduct->meta_description }}">
    <meta itemprop="image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $detailedProduct->meta_title }}">
    <meta name="twitter:description" content="{{ $detailedProduct->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">
    <meta name="twitter:data1" content="{{ single_price($detailedProduct->unit_price) }}">
    <meta name="twitter:label1" content="Price">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $detailedProduct->meta_title }}" />
    <meta property="og:type" content="og:product" />
    <meta property="og:url" content="{{ route('product', $detailedProduct->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}" />
    <meta property="og:description" content="{{ $detailedProduct->meta_description }}" />
    <meta property="og:site_name" content="{{ get_setting('meta_title') }}" />
    <meta property="og:price:amount" content="{{ single_price($detailedProduct->unit_price) }}" />
    <meta property="product:brand" content="{{ $detailedProduct->brand ? $detailedProduct->brand->name : env('APP_NAME') }}">
    <meta property="product:availability" content="{{ $availability }}">
    <meta property="product:condition" content="new">
    <meta property="product:price:amount" content="{{ number_format($detailedProduct->unit_price, 2) }}">
    <meta property="product:retailer_item_id" content="{{ $detailedProduct->slug }}">
    <meta property="product:price:currency"
        content="{{ get_system_default_currency()->code }}" />
    <meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}">
@endsection

@section('content')
    {{-- Loaded here (not in <head>) so it cascades after aiz-core.css --}}
    <link rel="stylesheet" href="{{ static_asset('assets/css/product-details.css') }}?v={{ @filemtime(public_path('assets/css/product-details.css')) }}">

    <div class="pd-page">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="pd-breadcrumb">
                    <li><a href="{{ route('home') }}">{{ translate('Home') }}</a></li>
                    @if ($detailedProduct->main_category)
                        <li class="pd-crumb-sep"><i class="las la-angle-right"></i></li>
                        <li><a href="{{ route('products.category', $detailedProduct->main_category->slug) }}">{{ $detailedProduct->main_category->getTranslation('name') }}</a></li>
                    @endif
                    <li class="pd-crumb-sep"><i class="las la-angle-right"></i></li>
                    <li class="pd-crumb-current" aria-current="page">{{ $detailedProduct->getTranslation('name') }}</li>
                </ol>
            </nav>

            <!-- Gallery + buy box -->
            <section class="pd-hero">
                <div class="row">
                    <div class="col-lg-6 col-xl-5 mb-lg-0">
                        @include('frontend.product_details.image_gallery')
                    </div>
                    <div class="col-lg-6 col-xl-7">
                        @include('frontend.product_details.details')
                    </div>
                </div>
            </section>

            <!-- Description, reviews, seller, related -->
            <section class="pd-lower">
                @if ($detailedProduct->auction_product)
                    @include('frontend.product_details.description')
                    <div id="pd-reviews">@include('frontend.product_details.review_section')</div>
                    @include('frontend.product_details.product_queries')
                @else
                    <div class="row gutters-16">
                        <div class="col-lg-9 order-lg-2">
                            @include('frontend.product_details.description')

                            @include('frontend.product_details.frequently_bought_products')

                            <div id="pd-reviews">@include('frontend.product_details.review_section')</div>

                            @include('frontend.product_details.product_queries')
                        </div>

                        <div class="col-lg-3 order-lg-1">
                            @include('frontend.product_details.seller_info')
                            @include('frontend.product_details.top_selling_products')
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>

    <!-- Sticky mobile buy bar (hidden on desktop via CSS) -->
    @if (!$detailedProduct->auction_product)
        @php
            $pd_bar_external =
                $detailedProduct->digital == 0 && (
                    ((get_setting('product_external_link_for_seller') == 1) && ($detailedProduct->added_by == 'seller') && ($detailedProduct->external_link != null)) ||
                    (($detailedProduct->added_by != 'seller') && ($detailedProduct->external_link != null))
                );
            $pd_bar_can_checkout = Auth::check() || get_setting('guest_checkout_activation') == 1;
        @endphp
        <div class="pd-sticky-cta" id="pd-sticky-cta">
            <div class="pd-sticky-price">
                <small>{{ translate('Price') }}</small>
                <strong id="pd-sticky-price">{{ home_discounted_price($detailedProduct) }}</strong>
            </div>
            @if ($pd_bar_external)
                <a class="btn pd-btn pd-btn-primary buy-now" href="{{ $detailedProduct->external_link }}">
                    <i class="la la-share"></i> {{ translate($detailedProduct->external_link_btn) }}
                </a>
            @else
                <button type="button" class="btn pd-btn pd-btn-outline add-to-cart"
                    onclick="{{ $pd_bar_can_checkout ? 'addToCart()' : 'showLoginModal()' }}">
                    <i class="las la-shopping-bag"></i> {{ translate('Add to cart') }}
                </button>
                <button type="button" class="btn pd-btn pd-btn-primary buy-now"
                    onclick="{{ $pd_bar_can_checkout ? 'buyNow()' : 'showLoginModal()' }}">
                    {{ translate('Buy Now') }}
                </button>
                @if ($detailedProduct->digital == 0)
                    <button type="button" class="btn pd-btn pd-btn-disabled out-of-stock d-none" disabled>
                        {{ translate('Out of Stock') }}
                    </button>
                @endif
            @endif
        </div>
    @endif
@endsection

@section('modal')
    <!-- Image Modal -->
    <div class="modal fade" id="image_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="p-4">
                    <div class="size-300px size-lg-450px">
                        <img class="img-fit h-100 lazyload"
                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                            data-src=""
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Modal -->
    <div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title fw-600 h5">{{ translate('Any query about this product') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" action="{{ route('conversations.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="form-group">
                            <input type="text" class="form-control mb-3 rounded-0" name="title"
                                value="{{ $detailedProduct->name }}" placeholder="{{ translate('Product Name') }}"
                                required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control rounded-0" rows="8" name="message" required
                                placeholder="{{ translate('Your Question') }}">{{ route('product', $detailedProduct->slug) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary fw-600 rounded-0"
                            data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary fw-600 rounded-0 w-100px">{{ translate('Send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bid Modal -->
    @if($detailedProduct->auction_product == 1)
        @php 
            $highest_bid = $detailedProduct->bids->max('amount');
            $min_bid_amount = $highest_bid != null ? $highest_bid+1 : $detailedProduct->starting_bid; 
        @endphp
        <div class="modal fade" id="bid_for_detail_product" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ translate('Bid For Product') }} <small>({{ translate('Min Bid Amount: ').$min_bid_amount }})</small> </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="form-horizontal" action="{{ route('auction_product_bids.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                            <div class="form-group">
                                <label class="form-label">
                                    {{translate('Place Bid Price')}}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="form-group">
                                    <input type="number" step="0.01" class="form-control form-control-sm" name="amount" min="{{ $min_bid_amount }}" placeholder="{{ translate('Enter Amount') }}" required>
                                </div>
                            </div>
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-sm btn-primary transition-3d-hover mr-1">{{ translate('Submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Product Review Modal -->
    <div class="modal fade" id="product-review-modal">
        <div class="modal-dialog">
            <div class="modal-content" id="product-review-modal-content">

            </div>
        </div>
    </div>

    <!-- Size chart show Modal -->
    @include('modals.size_chart_show_modal')

    <!-- Product Warranty Modal -->
    <div class="modal fade" id="warranty-note-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ translate('Warranty Note') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body c-scrollbar-light">
                    @if($detailedProduct->warranty_note_id != null)
                        <p>{{ $detailedProduct->warrantyNote->getTranslation('description') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Product Refund Modal -->
    <div class="modal fade" id="refund-note-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ translate('Refund Note') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body c-scrollbar-light">
                    @if($detailedProduct->refund_note_id != null)
                        <p>{{ $detailedProduct->refundNote->getTranslation('description') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            getVariantPrice();
        });

        // Smooth-scroll for in-page anchors (e.g. rating -> reviews)
        $(document).on('click', '[data-pd-scroll]', function(e) {
            var target = document.querySelector($(this).attr('href'));
            if (target) {
                e.preventDefault();
                var top = target.getBoundingClientRect().top + window.pageYOffset - 80;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });

        // Reflect chosen option value in the "Selected: x" label
        $(document).on('change', '#option-choice-form input[type=radio]', function() {
            var name = $(this).attr('name');
            var label = document.querySelector('[data-pd-selected="' + name + '"]');
            if (label) {
                var title = $(this).closest('.aiz-megabox').data('title');
                label.textContent = title ? title : $(this).val();
            }
        });

        // Keep the sticky mobile bar's price in sync with the variant price
        (function() {
            var stickyPrice = document.getElementById('pd-sticky-price');
            var chosen = document.getElementById('chosen_price');
            if (stickyPrice && chosen) {
                new MutationObserver(function() {
                    var t = chosen.textContent.trim();
                    if (t) stickyPrice.textContent = t;
                }).observe(chosen, { childList: true, characterData: true, subtree: true });
            }

            // Hide the sticky bar while scrolling up (reveals the top of the page),
            // show it while reading down the page.
            var bar = document.getElementById('pd-sticky-cta');
            if (bar) {
                var lastY = window.pageYOffset;
                var ticking = false;
                window.addEventListener('scroll', function() {
                    if (!ticking) {
                        window.requestAnimationFrame(function() {
                            var y = window.pageYOffset;
                            if (y < 300) {
                                bar.classList.add('is-hidden');
                            } else {
                                bar.classList.remove('is-hidden');
                            }
                            lastY = y;
                            ticking = false;
                        });
                        ticking = true;
                    }
                }, { passive: true });
                if (window.pageYOffset < 300) bar.classList.add('is-hidden');
            }
        })();

        function CopyToClipboard(e) {
            var url = $(e).data('url');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(url).select();
            try {
                document.execCommand("copy");
                AIZ.plugins.notify('success', '{{ translate('Link copied to clipboard') }}');
            } catch (err) {
                AIZ.plugins.notify('danger', '{{ translate('Oops, unable to copy') }}');
            }
            $temp.remove();
            // if (document.selection) {
            //     var range = document.body.createTextRange();
            //     range.moveToElementText(document.getElementById(containerid));
            //     range.select().createTextRange();
            //     document.execCommand("Copy");

            // } else if (window.getSelection) {
            //     var range = document.createRange();
            //     document.getElementById(containerid).style.display = "block";
            //     range.selectNode(document.getElementById(containerid));
            //     window.getSelection().addRange(range);
            //     document.execCommand("Copy");
            //     document.getElementById(containerid).style.display = "none";

            // }
            // AIZ.plugins.notify('success', 'Copied');
        }

        function show_chat_modal() {
            @if (Auth::check())
                $('#chat_modal').modal('show');
            @else
                $('#login_modal').modal('show');
            @endif
        }

        // Pagination using ajax
        $(window).on('hashchange', function() {
            if(window.history.pushState) {
                window.history.pushState('', '/', window.location.pathname);
            } else {
                window.location.hash = '';
            }
        });

        $(document).ready(function() {
            $(document).on('click', '.product-queries-pagination .pagination a', function(e) {
                getPaginateData($(this).attr('href').split('page=')[1], 'query', 'queries-area');
                e.preventDefault();
            });
        });

        $(document).ready(function() {
            $(document).on('click', '.product-reviews-pagination .pagination a', function(e) {
                getPaginateData($(this).attr('href').split('page=')[1], 'review', 'reviews-area');
                e.preventDefault();
            });
        });

        function getPaginateData(page, type, section) {
            $.ajax({
                url: '?page=' + page,
                dataType: 'json',
                data: {type: type},
            }).done(function(data) {
                $('.'+section).html(data);
                location.hash = page;
            }).fail(function() {
                alert('Something went worng! Data could not be loaded.');
            });
        }
        // Pagination end

        function showImage(photo) {
            $('#image_modal img').attr('src', photo);
            $('#image_modal img').attr('data-src', photo);
            $('#image_modal').modal('show');
        }

        function bid_modal(){
            @if (isCustomer() || isSeller())
                $('#bid_for_detail_product').modal('show');
          	@elseif (isAdmin())
                AIZ.plugins.notify('warning', '{{ translate("Sorry, Only customers & Sellers can Bid.") }}');
            @else
                $('#login_modal').modal('show');
            @endif
        }

        function product_review(product_id) {
            @if (isCustomer())
                @if ($review_status == 1)
                    $.post('{{ route('product_review_modal') }}', {
                        _token: '{{ @csrf_token() }}',
                        product_id: product_id
                    }, function(data) {
                        $('#product-review-modal-content').html(data);
                        $('#product-review-modal').modal('show', {
                            backdrop: 'static'
                        });
                        AIZ.extra.inputRating();
                    });
                @else
                    AIZ.plugins.notify('warning', '{{ translate("Sorry, You need to buy this product to give review.") }}');
                @endif
            @elseif (Auth::check() && !isCustomer())
                AIZ.plugins.notify('warning', '{{ translate("Sorry, Only customers can give review.") }}');
            @else
                $('#login_modal').modal('show');
            @endif
        }

        function showSizeChartDetail(id, name){
            $('#size-chart-show-modal .modal-title').html('');
            $('#size-chart-show-modal .modal-body').html('');
            if (id == 0) {
                AIZ.plugins.notify('warning', '{{ translate("Sorry, There is no size guide found for this product.") }}');
                return false;
            }
            $.ajax({
                type: "GET",
                url: "{{ route('size-charts-show', '') }}/"+id,
                data: {},
                success: function(data) {
                    $('#size-chart-show-modal .modal-title').html(name);
                    $('#size-chart-show-modal .modal-body').html(data);
                    $('#size-chart-show-modal').modal('show');
                }
            });
        }
    </script>
@endsection
