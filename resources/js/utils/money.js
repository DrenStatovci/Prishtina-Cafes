
export function toCents(input) {
    // accepts "2.50", "2,50", 2.5 → returns 250
    if (typeof input === 'number') input = String(input);
    input = (input ?? '').toString().trim().replace(',', '.');
    if (!input) return 0;
    const parts = input.split('.');
    const whole = parts[0].replace(/[^\d-]/g, '') || '0';
    const frac = (parts[1] || '0').padEnd(2, '0').slice(0, 2);
    const sign = whole.startsWith('-') ? -1 : 1;
    const cents = Math.abs(parseInt(whole || '0', 10)) * 100 + parseInt(frac || '0', 10);
    return sign * cents;
}

export function fromCents(cents) {
    const sign = cents < 0 ? '-' : '';
    const abs = Math.abs(cents);
    const euros = Math.floor(abs / 100);
    const dec = String(abs % 100).padStart(2, '0');
    return `${sign}${euros}.${dec}`;
}

export function euro(cents) {
    return `€${fromCents(cents)}`;
}
