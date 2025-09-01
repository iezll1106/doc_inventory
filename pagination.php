<?php
function renderPagination($total, $perPage, $current, $queryParams = []) {
    $totalPages = (int)ceil($total / $perPage);
    if ($totalPages <= 1) return;

    echo '<div class="pagination">';
    if ($current > 1) echo "<a href='?" . http_build_query(array_merge($queryParams, ['page' => $current-1])) . "'>« Prev</a>";

    for ($i=1; $i<=$totalPages; $i++) {
        if ($i==$current) echo "<strong>$i</strong>";
        elseif ($i==1||$i==$totalPages||abs($i-$current)<=2) echo "<a href='?" . http_build_query(array_merge($queryParams, ['page'=>$i])) . "'>$i</a>";
        elseif ($i==2 && $current>4) echo "<span>…</span>";
        elseif ($i==$totalPages-1 && $current<$totalPages-3) echo "<span>…</span>";
    }

    if ($current < $totalPages) echo "<a href='?" . http_build_query(array_merge($queryParams, ['page'=>$current+1])) . "'>Next »</a>";
    echo '</div>';
}