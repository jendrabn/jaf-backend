<div class="text-left">
    @php
        $d = $trackingData ?? [];
        $delivered = (bool) ($d['delivered'] ?? false);
        $summary = $d['summary'] ?? [];
        $details = $d['details'] ?? [];
        $delivery = $d['delivery_status'] ?? [];
        $manifest = is_array($d['manifest'] ?? null) ? $d['manifest'] ?? [] : [];
        $safe = fn($val, $fallback = '-') => isset($val) && $val !== '' ? e($val) : $fallback;
    @endphp

    <div class="mb-2">
        <span class="badge badge-{{ $delivered ? 'success' : 'secondary' }} badge-pill">
            {{ $delivered ? 'Delivered' : 'In Transit' }}
        </span>
    </div>

    <table class="table table-sm table-borderless">
        <tbody>
            <tr>
                <th>Courier</th>
                <td>{{ $safe($summary['courier_code'] ?? null) }} - {{ $safe($summary['courier_name'] ?? null) }}
                </td>
            </tr>
            <tr>
                <th>Waybill</th>
                <td>{{ $safe($summary['waybill_number'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Service</th>
                <td>{{ $safe($summary['service_code'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $safe($summary['status'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Origin</th>
                <td>{{ $safe($summary['origin'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Destination</th>
                <td>{{ $safe($summary['destination'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Shipper</th>
                <td>{{ $safe($summary['shipper_name'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Receiver</th>
                <td>{{ $safe($summary['receiver_name'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Waybill Date</th>
                <td>{{ $safe($summary['waybill_date'] ?? null) }}</td>
            </tr>
        </tbody>
    </table>

    <hr class="hr-dashed">

    <div class="text-uppercase small text-muted mb-1">Details</div>
    <table class="table table-sm table-borderless">
        <tbody>
            <tr>
                <th>Waybill Number</th>
                <td>{{ $safe($details['waybill_number'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Waybill Date</th>
                <td>{{ $safe($details['waybill_date'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Waybill Time</th>
                <td>{{ $safe($details['waybill_time'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Weight</th>
                <td>{{ $safe($details['weight'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Origin</th>
                <td>{{ $safe($details['origin'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Destination</th>
                <td>{{ $safe($details['destination'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Shipper Name</th>
                <td>{{ $safe($details['shipper_name'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Shipper Address</th>
                <td>
                    {{ $safe($details['shipper_address1'] ?? null) }}
                    @if (!empty($details['shipper_address2']))
                        <br>{{ $safe($details['shipper_address2'] ?? null) }}
                    @endif
                    @if (!empty($details['shipper_address3']))
                        <br>{{ $safe($details['shipper_address3'] ?? null) }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Shipper City</th>
                <td>{{ $safe($details['shipper_city'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Receiver Name</th>
                <td>{{ $safe($details['receiver_name'] ?? null) }}</td>
            </tr>
            <tr>
                <th>Receiver Address</th>
                <td>
                    {{ $safe($details['receiver_address1'] ?? null) }}
                    @if (!empty($details['receiver_address2']))
                        <br>{{ $safe($details['receiver_address2'] ?? null) }}
                    @endif
                    @if (!empty($details['receiver_address3']))
                        <br>{{ $safe($details['receiver_address3'] ?? null) }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Receiver City</th>
                <td>{{ $safe($details['receiver_city'] ?? null) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="text-uppercase small text-muted mb-1">Delivery Status</div>
    <table class="table table-sm table-borderless">
        <tbody>
            <tr>
                <th>Status</th>
                <td>{{ $safe($delivery['status'] ?? null) }}</td>
            </tr>
            <tr>
                <th>POD Receiver</th>
                <td>{{ $safe($delivery['pod_receiver'] ?? null) }}</td>
            </tr>
            <tr>
                <th>POD Date</th>
                <td>{{ $safe($delivery['pod_date'] ?? null) }}</td>
            </tr>
            <tr>
                <th>POD Time</th>
                <td>{{ $safe($delivery['pod_time'] ?? null) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="text-uppercase small text-muted mb-1">Manifest</div>
    @if (!empty($manifest))
        <ul class="list-group">
            @foreach ($manifest as $m)
                @php
                    $title = $m['manifest_description'] ?? ($m['manifest_code'] ?? '-');
                    $time = trim(($m['manifest_date'] ?? '-') . ' ' . ($m['manifest_time'] ?? ''));
                @endphp
                <li class="list-group-item small d-flex justify-content-between align-items-center">
                    <div>
                        <div class="font-weight-bold">{{ $safe($title) }}</div>
                        <div class="text-muted">{{ $safe($m['city_name'] ?? null) }}</div>
                    </div>
                    <div>{{ $safe($time) }}</div>
                </li>
            @endforeach
        </ul>
    @else
        <div class="text-muted small">No manifest items.</div>
    @endif
</div>
