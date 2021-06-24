<ul class="col-6 offset-3 h-100 anodizeMenu">
    @foreach($menu as $m)
        <li class="col-12">
            <a href="/anodize/movetag" class="btn btn-primary col-12"><?= $m; ?></a>
        </li>
    @endforeach
</ul>
