<ol class="breadcrumb float-sm-right">
    @foreach ($items as $label => $url)
        @if ($loop->last || $url === null)
            <li class="breadcrumb-item active">{{ $label }}</li>
        @else
            <li class="breadcrumb-item">
                <a href="{{ $url }}">{{ $label }}</a>
            </li>
        @endif
    @endforeach
</ol>
