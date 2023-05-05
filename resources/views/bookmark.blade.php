<form class="alps-bookmarker" action="{{ $action }}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="payload" value="{{ urlencode($payload) }}">

    {{ csrf_field() }}

    <div>
        {!! $slot !!}
    </div>
</form>
