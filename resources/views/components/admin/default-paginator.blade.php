@if(count($paginator) > 0)
<?php
// config
$link_limit = 7; // maximum number of links (a little bit inaccurate, but will be ok for now)
        $queryString = \Request::server('QUERY_STRING');
        parse_str($queryString, $queryArray);
?>

@if ($paginator->lastPage() > 1)
    <ul class="pagination">
        <li class="{{ ($paginator->currentPage() == 1) ? ' disabled' : '' }}" >
            <a href="{{ $paginator->appends($queryArray)->url(1) }}">اولین صفحه </a>
         </li>
        @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                <?php
                $half_total_links = floor($link_limit / 2);
                $from = $paginator->currentPage() - $half_total_links;
                $to = $paginator->currentPage() + $half_total_links;
                if ($paginator->currentPage() < $half_total_links) {
                    $to += $half_total_links - $paginator->currentPage();
                }
                if ($paginator->lastPage() - $paginator->currentPage() < $half_total_links) {
                    $from -= $half_total_links - ($paginator->lastPage() - $paginator->currentPage()) - 1;
                }
                ?>
            @if ($from < $i && $i < $to)
                <li class="{{ ($paginator->currentPage() == $i) ? ' active' : '' }}" style="margin: 6px">
                    <a href="{{ $paginator->appends($queryArray)->url($i) }}" style="{{ ($paginator->currentPage() == $i) ? 'color:red' : '' }}">{{ $i }}</a>
                </li>
            @endif
        @endfor

        <li class="{{ ($paginator->currentPage() == $paginator->lastPage()) ? ' disabled' : '' }}" style="margin: 6px">
            <a href="{{ $paginator->appends($queryArray)->url($paginator->lastPage()) }}">اخرین صفحه</a>
        </li>
    </ul>
@endif
@push('styles')
<style>
    .pagination{
        justify-content: center;
        margin-top: 0.75rem
    }
    .pagination li:first-child{
        background: unset;
    }
    .pagination li:last-child{
        background: unset;
    }
    .pagination li{
        margin: 4px;
        background: #f0f0f0;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
    }
    .pagination li a{
        color:#000;
    }
    .pagination li.active{
        margin: 4px;
        background: #ff8d81;
        padding: 0.25rem 0.5rem;
        color: #fff;
        border-radius: 4px;
    }
    .pagination li.active a{
        color:#fff;
    }
</style>
@endpush

@endif
