<div style="max-width: 1100px; margin: 0 auto; padding: 1.5rem; font-family: sans-serif;">
    <h1 style="margin-bottom: 1rem;">Reports</h1>

    <div style="display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:.75rem; margin-bottom:1rem;">
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:.7rem; padding:.8rem;">
            <p style="margin:0; color:#64748b;">Occupancy</p>
            <p style="margin:.25rem 0 0; font-size:1.4rem; font-weight:700;">{{ $occupancy }}%</p>
            <p style="margin:0; color:#64748b; font-size:.85rem;">{{ $activeStays }}/{{ $totalRooms }} rooms occupied</p>
        </div>
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:.7rem; padding:.8rem;">
            <p style="margin:0; color:#64748b;">Revenue today</p>
            <p style="margin:.25rem 0 0; font-size:1.4rem; font-weight:700;">{{ number_format($todayRevenue, 2, '.', ' ') }}</p>
            <p style="margin:0; color:#64748b; font-size:.85rem;">XOF</p>
        </div>
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:.7rem; padding:.8rem;">
            <p style="margin:0; color:#64748b;">Open invoices</p>
            <p style="margin:.25rem 0 0; font-size:1.4rem; font-weight:700;">{{ $openInvoices }}</p>
        </div>
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:.7rem; padding:.8rem;">
            <p style="margin:0; color:#64748b;">Orders today</p>
            <p style="margin:.25rem 0 0; font-size:1.4rem; font-weight:700;">{{ $ordersToday }}</p>
        </div>
    </div>

    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:.7rem; padding:1rem;">
        <h2 style="margin:0 0 .75rem;">Service area load</h2>
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align:left; border-bottom:1px solid #e5e7eb;">
                    <th style="padding:.5rem;">Area</th>
                    <th style="padding:.5rem;">Orders</th>
                </tr>
            </thead>
            <tbody>
                @forelse($serviceAreaLoad as $area)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:.5rem;">{{ $area->name }}</td>
                        <td style="padding:.5rem;">{{ $area->orders_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="padding:1rem;">No data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
