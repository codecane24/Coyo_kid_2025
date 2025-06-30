@props([
    'headers' => [],
    'data' => [],
    'actions' => false,
    'searchable' => false,
    'pagination' => false,
    'sortable' => false,
    'responsive' => true,
    'striped' => true,
    'hover' => true,
    'bordered' => false,
    'small' => false,
    'id' => 'commonTable'
])

<div class="table-responsive">
    @if($searchable)
    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
            <input type="text" class="form-control" id="{{ $id }}_search" placeholder="Search...">
        </div>
    </div>
    @endif

    <table class="table {{ $striped ? 'table-striped' : '' }} {{ $hover ? 'table-hover' : '' }} {{ $bordered ? 'table-bordered' : '' }} {{ $small ? 'table-sm' : '' }}"
           id="{{ $id }}">
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th @if($sortable) class="sortable" @endif>
                        {{ $header['label'] ?? $header }}
                        @if($sortable)
                            <i class="mdi mdi-sort"></i>
                        @endif
                    </th>
                @endforeach
                @if($actions)
                    <th class="text-end">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    @foreach($headers as $key => $header)
                        <td>
                            @if(isset($header['format']) && is_callable($header['format']))
                                {!! $header['format']($row[$key]) !!}
                            @else
                                {{ $row[$key] }}
                            @endif
                        </td>
                    @endforeach
                    @if($actions)
                        <td class="text-end">
                            <div class="btn-group">
                                @if(isset($actions['view']))
                                    <a href="{{ $actions['view']($row) }}" class="btn btn-sm btn-info">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                @endif
                                @if(isset($actions['edit']))
                                    <a href="{{ $actions['edit']($row) }}" class="btn btn-sm btn-primary">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                @endif
                                @if(isset($actions['delete']))
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteItem('{{ $actions['delete']($row) }}')">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($pagination)
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() }} entries
            </div>
            <div>
                {{ $data->links() }}
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        @if($searchable)
        // Initialize search functionality
        $('#{{ $id }}_search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#{{ $id }} tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        @endif

        @if($sortable)
        // Initialize sort functionality
        $('.sortable').click(function() {
            var table = $(this).parents('table').eq(0);
            var rows = table.find('tr:gt(0)').toArray().sort(comparator($(this).index()));
            this.asc = !this.asc;
            if (!this.asc) {
                rows = rows.reverse();
            }
            for (var i = 0; i < rows.length; i++) {
                table.append(rows[i]);
            }
        });
        function comparator(index) {
            return function(a, b) {
                var valA = getCellValue(a, index);
                var valB = getCellValue(b, index);
                return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
            }
        }
        function getCellValue(row, index) {
            return $(row).children('td').eq(index).text();
        }
        @endif
    });

    function deleteItem(url) {
        if (confirm('Are you sure you want to delete this item?')) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error deleting item');
                    }
                }
            });
        }
    }
</script>
@endpush
