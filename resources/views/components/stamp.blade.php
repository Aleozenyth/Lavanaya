@php
    // Map submission/approval/payment status values to stamp style + short label
    $map = [
        'draft' => ['draft', 'Draft'],
        'submitted' => ['pending', 'Submitted'],
        'waiting_spv' => ['pending', 'Wait SPV'],
        'waiting_manager' => ['pending', 'Wait Mgr'],
        'waiting_director' => ['pending', 'Wait Dir'],
        'waiting_finance' => ['finance', 'Wait Fin'],
        'paid' => ['approved', 'Paid'],
        'rejected' => ['rejected', 'Rejected'],
        'approved' => ['approved', 'Approved'],
        'pending' => ['pending', 'Pending'],
    ];
    $key = $map[$status] ?? ['draft', ucfirst($status)];
@endphp
<span class="stamp stamp-{{ $key[0] }}">{{ $label ?? $key[1] }}</span>
