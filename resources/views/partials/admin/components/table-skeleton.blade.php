@props(['rows' => 5, 'cols' => 5])

@for ($i = 0; $i < $rows; $i++)
    <tr class="nc-skeleton-row">
        @for ($c = 0; $c < $cols; $c++)
            <td style="padding: 14px 16px;">
                @if ($c === 0)
                    <div class="nc-shimmer nc-skeleton-text" style="width: 90px; height: 16px;"></div>
                @elseif ($c === $cols - 1)
                    <div class="nc-shimmer nc-skeleton-pill" style="float: right;"></div>
                @else
                    <div class="nc-shimmer nc-skeleton-text" style="width: {{ 45 + (($i * 17 + $c * 23) % 40) }}%;"></div>
                @endif
            </td>
        @endfor
    </tr>
@endfor
