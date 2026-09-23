@php
    $pd_name = $detailedProduct->getTranslation('name');
    $pd_reviews_count = $detailedProduct->reviews->where('status', 1)->count();
    $pd_is_seller_product = $detailedProduct->added_by == 'seller' && get_setting('vendor_system_activation') == 1;
    $pd_has_external_link =
        ((get_setting('product_external_link_for_seller') == 1) && ($detailedProduct->added_by == 'seller') && ($detailedProduct->external_link != null)) ||
        (($detailedProduct->added_by != 'seller') && ($detailedProduct->external_link != null));
    $pd_can_checkout = Auth::check() || get_setting('guest_checkout_activation') == 1;
    $pd_add_action = $pd_can_checkout ? 'addToCart()' : 'showLoginModal()';
    $pd_buy_action = $pd_can_checkout ? 'buyNow()' : 'showLoginModal()';

    $pd_stock_qty = 0;
    foreach ($detailedProduct->stocks as $stock) {
        $pd_stock_qty += $stock->qty;
    }
    $pd_in_stock = $detailedProduct->digital == 1 || $pd_stock_qty > 0;

    $pd_size_chart_id = ($detailedProduct->main_category && $detailedProduct->main_category->sizeChart) ? $detailedProduct->main_category->sizeChart->id : 0;
    $pd_size_chart_name = ($detailedProduct->main_category && $detailedProduct->main_category->sizeChart) ? $detailedProduct->main_category->sizeChart->name : null;
@endphp

<div class="pd-buybox text-left">
    <!-- Brand eyebrow -->
    @if ($detailedProduct->brand != null)
        <a href="{{ route('products.brand', $detailedProduct->brand->slug) }}" class="pd-eyebrow">
            {{ $detailedProduct->brand->name }}
        </a>
    @endif

    <!-- Product name -->
    <h1 class="pd-title">{{ $pd_name }}</h1>

    <!-- Rating / sold by / shipping -->
    <div class="pd-meta-row">
        @if ($detailedProduct->auction_product != 1)
            <a href="#pd-reviews" class="d-inline-flex align-items-center" data-pd-scroll>
                <span class="rating rating-mr-1 mr-2">{{ renderStarRating($detailedProduct->rating) }}</span>
                <span>{{ number_format((float) $detailedProduct->rating, 1) }} · {{ $pd_reviews_count }} {{ translate('reviews') }}</span>
            </a>
            <span class="pd-dot"></span>
        @endif
        @if ($pd_is_seller_product)
            <span>{{ translate('Sold by') }}
                <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="fw-600 text-dark">{{ $detailedProduct->user->shop->name }}</a>
            </span>
        @else
            <span>{{ translate('Sold by') }} <span class="fw-600 text-dark">{{ get_setting('website_name') ?: translate('Inhouse product') }}</span></span>
        @endif
    </div>

    @if ($detailedProduct->auction_product)
        <!-- ================= Auction product ================= -->
        <div class="pd-price-block">
            <div class="pd-facts mt-0 pt-0 border-0">
                <div class="pd-fact">
                    <span class="pd-fact-key">{{ translate('Auction Will End') }}</span>
                    <span class="pd-fact-val">
                        @if ($detailedProduct->auction_end_date > strtotime('now'))
                            <span class="aiz-count-down align-items-center"
                                data-date="{{ date('Y/m/d H:i:s', $detailedProduct->auction_end_date) }}"></span>
                        @else
                            {{ translate('Ended') }}
                        @endif
                    </span>
                </div>
                <div class="pd-fact">
                    <span class="pd-fact-key">{{ translate('Starting Bid') }}</span>
                    <span class="pd-fact-val">
                        {{ single_price($detailedProduct->starting_bid) }}
                        @if ($detailedProduct->unit != null)
                            <span class="pd-price-unit">/{{ $detailedProduct->getTranslation('unit') }}</span>
                        @endif
                    </span>
                </div>
                @if (Auth::check() && Auth::user()->product_bids->where('product_id', $detailedProduct->id)->first() != null)
                    <div class="pd-fact">
                        <span class="pd-fact-key">{{ translate('My Bidded Amount') }}</span>
                        <span class="pd-fact-val">{{ single_price(Auth::user()->product_bids->where('product_id', $detailedProduct->id)->first()->amount) }}</span>
                    </div>
                @endif
                @php $highest_bid = $detailedProduct->bids->max('amount'); @endphp
                <div class="pd-fact">
                    <span class="pd-fact-key">{{ translate('Highest Bid') }}</span>
                    <span class="pd-price">{{ $highest_bid != null ? single_price($highest_bid) : '—' }}</span>
                </div>
            </div>
        </div>

        @if ($detailedProduct->auction_end_date >= strtotime('now'))
            <div class="pd-cta">
                @if (Auth::check() && $detailedProduct->user_id == Auth::user()->id)
                    <span class="badge badge-inline badge-danger">{{ translate('Seller cannot Place Bid to His Own Product') }}</span>
                @else
                    <button type="button" class="btn pd-btn pd-btn-primary buy-now" onclick="bid_modal()">
                        <i class="las la-gavel"></i>
                        @if (Auth::check() && Auth::user()->product_bids->where('product_id', $detailedProduct->id)->first() != null)
                            {{ translate('Change Bid') }}
                        @else
                            {{ translate('Place Bid') }}
                        @endif
                    </button>
                @endif
            </div>
        @endif
    @else
        <!-- ================= Price ================= -->
        @if ($detailedProduct->wholesale_product == 1)
            <table class="pd-wholesale">
                <thead>
                    <tr>
                        <th>{{ translate('Min Qty') }}</th>
                        <th>{{ translate('Max Qty') }}</th>
                        <th>{{ translate('Unit Price') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detailedProduct->stocks->first()->wholesalePrices as $wholesalePrice)
                        <tr>
                            <td>{{ $wholesalePrice->min_qty }}</td>
                            <td>{{ $wholesalePrice->max_qty }}</td>
                            <td class="fw-700">{{ single_price($wholesalePrice->price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="pd-price-block">
                <div class="pd-price-row">
                    <span class="pd-price">{{ home_discounted_price($detailedProduct) }}</span>
                    @if (home_price($detailedProduct) != home_discounted_price($detailedProduct))
                        <span class="pd-price-old">{{ home_price($detailedProduct) }}</span>
                    @endif
                    @if ($detailedProduct->unit != null)
                        <span class="pd-price-unit">/ {{ $detailedProduct->getTranslation('unit') }}</span>
                    @endif
                    @if (home_price($detailedProduct) != home_discounted_price($detailedProduct) && discount_in_percentage($detailedProduct) > 0)
                        <span class="pd-save-pill">{{ translate('Save') }} {{ discount_in_percentage($detailedProduct) }}%</span>
                    @endif
                </div>
                @if ((addon_is_activated('club_point') && $detailedProduct->earn_point > 0) || $detailedProduct->est_shipping_days)
                    <div class="pd-price-note">
                        @if (addon_is_activated('club_point') && $detailedProduct->earn_point > 0)
                            <span class="pd-chip"><i class="las la-coins" style="color:#f3af3d"></i>{{ translate('Club Point') }}: {{ $detailedProduct->earn_point }}</span>
                        @endif
                        @if ($detailedProduct->est_shipping_days)
                            <span class="pd-chip"><i class="las la-shipping-fast"></i>{{ translate('Ships in') }} {{ $detailedProduct->est_shipping_days }} {{ translate('Days') }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- ================= Options, quantity, total (form used by cart JS) ================= -->
        <form id="option-choice-form">
            @csrf
            <input type="hidden" name="id" value="{{ $detailedProduct->id }}">

            @if ($detailedProduct->digital == 0)
                @if ($detailedProduct->choice_options != null)
                    @foreach (json_decode($detailedProduct->choice_options) as $choice)
                        <div class="pd-option">
                            <div class="pd-option-label">
                                {{ get_single_attribute_name($choice->attribute_id) }}:
                                <span class="pd-option-selected" data-pd-selected="attribute_id_{{ $choice->attribute_id }}">{{ $choice->values[0] ?? '' }}</span>
                            </div>
                            <div class="aiz-radio-inline">
                                @foreach ($choice->values as $key => $value)
                                    <label class="aiz-megabox pl-0 mb-0">
                                        <input type="radio" name="attribute_id_{{ $choice->attribute_id }}"
                                            value="{{ $value }}" @if ($key == 0) checked @endif>
                                        <span class="aiz-megabox-elem d-flex align-items-center justify-content-center">{{ $value }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif

                @if ($detailedProduct->colors != null && count(json_decode($detailedProduct->colors)) > 0)
                    @php $pd_colors = json_decode($detailedProduct->colors); @endphp
                    <div class="pd-option pd-option-colors">
                        <div class="pd-option-label">
                            {{ translate('Color') }}:
                            <span class="pd-option-selected" data-pd-selected="color">{{ get_single_color_name($pd_colors[0]) }}</span>
                        </div>
                        <div class="aiz-radio-inline">
                            @foreach ($pd_colors as $key => $color)
                                <label class="aiz-megabox pl-0 mb-0" data-toggle="tooltip" data-title="{{ get_single_color_name($color) }}">
                                    <input type="radio" name="color" value="{{ get_single_color_name($color) }}"
                                        @if ($key == 0) checked @endif>
                                    <span class="aiz-megabox-elem d-flex align-items-center justify-content-center">
                                        <span class="pd-swatch" style="background: {{ $color }};"></span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quantity -->
                <div class="pd-option mb-0">
                    <div class="pd-option-label">{{ translate('Quantity') }}</div>
                    <div class="pd-qty-row product-quantity">
                        <div class="pd-stepper aiz-plus-minus">
                            <button class="btn" type="button" data-type="minus" data-field="quantity" disabled=""
                                aria-label="{{ translate('Decrease quantity') }}">
                                <i class="las la-minus"></i>
                            </button>
                            <input type="number" name="quantity" class="input-number" placeholder="1"
                                value="{{ $detailedProduct->min_qty }}" min="{{ $detailedProduct->min_qty }}" max="10"
                                lang="en" aria-label="{{ translate('Quantity') }}">
                            <button class="btn" type="button" data-type="plus" data-field="quantity"
                                aria-label="{{ translate('Increase quantity') }}">
                                <i class="las la-plus"></i>
                            </button>
                        </div>

                        <span class="pd-stock {{ $pd_in_stock ? '' : 'is-out' }}">
                            @if (!$pd_in_stock)
                                {{ translate('Out of Stock') }}
                            @elseif ($detailedProduct->stock_visibility_state == 'quantity')
                                <span><span id="available-quantity">{{ $pd_stock_qty }}</span> {{ translate('available') }}</span>
                            @elseif ($detailedProduct->stock_visibility_state == 'text')
                                <span id="available-quantity">{{ translate('In Stock') }}</span>
                            @else
                                {{ translate('In Stock') }}
                            @endif
                        </span>
                    </div>
                </div>
            @else
                <input type="hidden" name="quantity" value="1">
            @endif

            <!-- Total price (revealed by getVariantPrice) -->
            <div class="pd-total d-none" id="chosen_price_div">
                <span>{{ translate('Total Price') }}</span>
                <strong id="chosen_price"></strong>
            </div>
        </form>

        <!-- ================= Call to action ================= -->
        <div class="pd-cta">
            @if ($detailedProduct->digital == 0 && $pd_has_external_link)
                <a class="btn pd-btn pd-btn-primary buy-now" href="{{ $detailedProduct->external_link }}">
                    <i class="la la-share"></i> {{ translate($detailedProduct->external_link_btn) }}
                </a>
            @else
                <button type="button" class="btn pd-btn pd-btn-outline add-to-cart" onclick="{{ $pd_add_action }}">
                    <i class="las la-shopping-bag"></i> {{ translate('Add to cart') }}
                </button>
                <button type="button" class="btn pd-btn pd-btn-primary buy-now" onclick="{{ $pd_buy_action }}">
                    <i class="las la-bolt"></i> {{ translate('Buy Now') }}
                </button>
                @if ($detailedProduct->digital == 0)
                    <button type="button" class="btn pd-btn pd-btn-disabled out-of-stock d-none" disabled>
                        <i class="la la-cart-arrow-down"></i> {{ translate('Out of Stock') }}
                    </button>
                @endif
            @endif
            <button type="button" class="pd-icon-btn" onclick="addToWishList({{ $detailedProduct->id }})"
                aria-label="{{ translate('Add to Wishlist') }}" title="{{ translate('Add to Wishlist') }}">
                <i class="la la-heart-o"></i>
            </button>
        </div>

        <!-- Secondary actions -->
        <div class="pd-actions">
            <a href="javascript:void(0)" onclick="addToCompare({{ $detailedProduct->id }})">
                <i class="las la-sync"></i>{{ translate('Compare') }}
            </a>
            @if (get_setting('conversation_system') == 1)
                <button type="button" onclick="show_chat_modal()">
                    <i class="las la-comment-dots"></i>{{ translate('Message Seller') }}
                </button>
            @endif
            @if (get_setting('product_query_activation') == 1)
                <a href="javascript:void(0)" onclick="goToView('product_query')">
                    <i class="las la-question-circle"></i>{{ translate('Product Inquiry') }}
                </a>
            @endif
            @if ($pd_size_chart_id != 0)
                <a href="javascript:void(0)" onclick='showSizeChartDetail({{ $pd_size_chart_id }}, "{{ $pd_size_chart_name }}")'>
                    <i class="las la-ruler"></i>{{ translate('Size guide') }}
                </a>
            @endif
        </div>

        <!-- Affiliate promote link -->
        @if (Auth::check() && addon_is_activated('affiliate_system') && get_affliate_option_status() && Auth::user()->affiliate_user != null && Auth::user()->affiliate_user->status)
            @php
                if (Auth::user()->referral_code == null) {
                    Auth::user()->referral_code = substr(Auth::user()->id . Str::random(10), 0, 10);
                    Auth::user()->save();
                }
                $referral_code = Auth::user()->referral_code;
                $referral_code_url = URL::to('/product') . '/' . $detailedProduct->slug . "?product_referral_code=$referral_code";
            @endphp
            <div class="mt-3">
                <button type="button" id="ref-cpurl-btn" class="btn pd-btn pd-btn-outline w-100"
                    data-attrcpy="{{ translate('Copied') }}" onclick="CopyToClipboard(this)"
                    data-url="{{ $referral_code_url }}">
                    <i class="las la-link"></i>{{ translate('Copy the Promote Link') }}
                </button>
            </div>
        @endif

        <!-- ================= Trust strip (only real, configured guarantees) ================= -->
        <div class="pd-trust">
            <div class="pd-trust-item">
                <span class="pd-trust-icon"><i class="las la-shipping-fast"></i></span>
                <div>
                    <div class="pd-trust-title">{{ translate('Delivery') }}</div>
                    <div class="pd-trust-sub">
                        @if ($detailedProduct->est_shipping_days)
                            {{ translate('Estimated') }} {{ $detailedProduct->est_shipping_days }} {{ translate('Days') }}
                        @else
                            {{ translate('Shipped to your doorstep') }}
                        @endif
                    </div>
                </div>
            </div>

            @if (get_setting('cash_payment') == 1)
                <div class="pd-trust-item">
                    <span class="pd-trust-icon"><i class="las la-money-bill-wave"></i></span>
                    <div>
                        <div class="pd-trust-title">{{ translate('Cash on Delivery') }}</div>
                        <div class="pd-trust-sub">{{ translate('Pay when you receive') }}</div>
                    </div>
                </div>
            @endif

            @if (addon_is_activated('refund_request'))
                <div class="pd-trust-item">
                    <span class="pd-trust-icon"><i class="las la-undo-alt"></i></span>
                    <div>
                        <div class="pd-trust-title">{{ $detailedProduct->refundable == 1 ? translate('Easy Returns') : translate('No Returns') }}</div>
                        <div class="pd-trust-sub">
                            @if ($detailedProduct->refundable == 1)
                                <a href="{{ route('returnpolicy') }}" target="_blank">{{ translate('View Policy') }}</a>
                                @if ($detailedProduct->refund_note_id != null)
                                    · <a href="javascript:void(0)" data-toggle="modal" data-target="#refund-note-modal">{{ translate('Refund Note') }}</a>
                                @endif
                            @else
                                {{ translate('Not Applicable') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($detailedProduct->has_warranty == 1 && $detailedProduct->warranty_id != null)
                <div class="pd-trust-item">
                    <span class="pd-trust-icon"><i class="las la-shield-alt"></i></span>
                    <div>
                        <div class="pd-trust-title">{{ $detailedProduct->warranty->getTranslation('text') }}</div>
                        <div class="pd-trust-sub">
                            @if ($detailedProduct->warranty_note_id != null)
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#warranty-note-modal">{{ translate('View Details') }}</a>
                            @else
                                {{ translate('Warranty') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($detailedProduct->digital == 1 && $detailedProduct->added_by == 'seller')
                <div class="pd-trust-item">
                    <span class="pd-trust-icon"><i class="las la-user-check"></i></span>
                    <div>
                        <div class="pd-trust-title">{{ translate('Seller Guarantees') }}</div>
                        <div class="pd-trust-sub">
                            @if ($detailedProduct->user->shop->verification_status == 1)
                                <span class="text-success fw-600">{{ translate('Verified seller') }}</span>
                            @else
                                <span class="text-danger fw-600">{{ translate('Non verified seller') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- ================= Facts + share ================= -->
    <div class="pd-facts">
        @if ($detailedProduct->brand != null)
            <div class="pd-fact">
                <span class="pd-fact-key">{{ translate('Brand') }}</span>
                <span class="pd-fact-val">
                    <a href="{{ route('products.brand', $detailedProduct->brand->slug) }}">{{ $detailedProduct->brand->name }}</a>
                </span>
            </div>
        @endif
        @if ($detailedProduct->main_category)
            <div class="pd-fact">
                <span class="pd-fact-key">{{ translate('Category') }}</span>
                <span class="pd-fact-val">
                    <a href="{{ route('products.category', $detailedProduct->main_category->slug) }}">{{ $detailedProduct->main_category->getTranslation('name') }}</a>
                </span>
            </div>
        @endif
        <div class="pd-fact">
            <span class="pd-fact-key">{{ translate('Share') }}</span>
            <span class="pd-fact-val pd-share"><span class="aiz-share"></span></span>
        </div>
    </div>
</div>
