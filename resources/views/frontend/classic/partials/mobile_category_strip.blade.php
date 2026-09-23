<div class="mobile-category-strip d-xl-none">
    <div class="mobile-category-strip-scroll no-scrollbar">
        @foreach (get_level_zero_categories()->take(12) as $category)
            @php
                $category_name = $category->getTranslation('name');
            @endphp
            <a href="{{ route('products.category', $category->slug) }}" class="mobile-category-item">
                <span class="mobile-category-icon">
                    <img src="{{ isset($category->catIcon->file_name) ? my_asset($category->catIcon->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                        alt="{{ $category_name }}" loading="lazy"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                </span>
                <span class="mobile-category-label">{{ $category_name }}</span>
            </a>
        @endforeach
    </div>
</div>
