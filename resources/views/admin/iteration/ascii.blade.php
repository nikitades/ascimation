<ul class="list-group" data-entity="{{get_class($items->first())}}">
    @foreach($items as $item)
        <li class="list-group-item clearfix" data-entity-id="{{$item->id}}">
            <div class="item-content">
                <div class="admin-entry-item admin-entry-logo block">
                    <span class="glyphicon glyphicon-file" aria-hidden="true"></span>
                </div>
                <div class="admin-entry-item">
                    <a href="/{{$item->uuid}}">{{$item->uuid}}</a>
                </div>
                <div class="admin-entry-item">
                    @if ($item->file)
                        <p><a class="btn btn-xs btn-link" href="{{$item->file->sourceFile->url()}}">>>Source<<</a></p>
                    @else
                        <p>No file</p>
                    @endif
                </div>
                <div class="admin-entry-item admin-entry-controls">
                    <a href="/ascii/{{ $item->id }}/delete" onclick="return confirm('Вы уверены?')"
                       class="btn btn-xs btn-link red">
                        <i class="glyphicon glyphicon-trash"></i>
                    </a>
                </div>
            </div>
        </li>
    @endforeach
</ul>