export function paymentStatusPill(status) {
    switch (status) {
        case 'paid': return { label: 'Paid', tone: 'leaf' };
        case 'unpaid': return { label: 'Unpaid', tone: 'danger' };
        case 'refunded': return { label: 'Refunded', tone: 'warning' };
        default: return { label: status || '—', tone: 'ink' };
    }
}
