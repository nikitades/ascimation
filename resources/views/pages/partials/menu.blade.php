<li>
    <a href="{{ '/latest' != $_SERVER['REQUEST_URI'] ? '/latest' : 'javascript: void(0)' }}">Latest</a>
</li>
@foreach ($menu as $page)
    <li>
        <a href="{{ $page->full_url != $_SERVER['REQUEST_URI'] ? $page->full_url : 'javascript: void(0)' }}">{{ $page->name }}</a>
    </li>
@endforeach