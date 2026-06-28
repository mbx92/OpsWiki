export function normalizeCurrency(currency = 'USD') {
    return String(currency || 'USD').trim().toUpperCase();
}

export function formatMoney(cents, currency = 'USD', options = {}) {
    const {
        freeLabel = 'Free',
        showZeroAsFree = true,
    } = options;

    if (showZeroAsFree && (cents === 0 || cents === null || cents === undefined)) {
        return freeLabel;
    }

    const code = normalizeCurrency(currency);
    const amount = Number(cents) / 100;

    try {
        return new Intl.NumberFormat(code === 'IDR' ? 'id-ID' : 'en-US', {
            style: 'currency',
            currency: code,
            minimumFractionDigits: code === 'IDR' ? 0 : 2,
            maximumFractionDigits: code === 'IDR' ? 0 : 2,
        }).format(amount);
    } catch {
        const symbol = code === 'IDR' ? 'Rp ' : code === 'EUR' ? '€' : '$';

        return symbol + amount.toLocaleString(code === 'IDR' ? 'id-ID' : 'en-US', {
            minimumFractionDigits: code === 'IDR' ? 0 : 2,
            maximumFractionDigits: code === 'IDR' ? 0 : 2,
        });
    }
}

export function formatPrice(cents, currency = 'USD') {
    return formatMoney(cents, currency);
}
