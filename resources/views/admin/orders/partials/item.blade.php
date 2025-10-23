<div class="list-group list-group-flush">
    @foreach ($items as $item)
        <div class="list-group-item bg-transparent py-1 px-0 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center flex-grow-1 min-w-0"
                 style="font-size: 0.85rem;">
                <span class="text-muted mr-2">{{ $loop->iteration }}.</span>

                <span class="text-truncate">{{ $item->name }}</span>

                @if ($item->product)
                    <a class="ml-2 text-muted small icon-btn"
                       href="{{ route('admin.products.show', $item->product) }}"
                       rel="noopener"
                       target="_blank">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                @endif
            </div>

            <div class="d-flex align-items-center ml-3">
                <span class="text-muted mr-1">&times;</span>
                <span class="badge badge-default badge-pill">{{ $item->quantity }}</span>
            </div>
        </div>
    @endforeach
</div>
