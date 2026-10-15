function digits(value, max) {
    return String(value ?? '').replace(/\D+/g, '').slice(0, max);
}

export const SabaMasks = {
    phone(value) {
        const v = digits(value, 11);
        if (v.length <= 10) {
            return v.replace(/^(\d{0,2})(\d{0,4})(\d{0,4}).*/, (_, a, b, c) => {
                let out = a ? `(${a}` : '';
                if (a.length === 2) out += ') ';
                out += b;
                if (c) out += `-${c}`;
                return out;
            });
        }
        return v.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
    },
    document(value) {
        const v = digits(value, 14);
        if (v.length <= 11) {
            return v
                .replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1-$2');
        }
        return v
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1/$2')
            .replace(/(\d{4})(\d)/, '$1-$2');
    },
    postalCode(value) {
        const v = digits(value, 8);
        return v.length > 5 ? `${v.slice(0, 5)}-${v.slice(5)}` : v;
    },
};

export function registerBrazilianMasks() {
    window.SabaMasks = SabaMasks;
    document.addEventListener('input', (event) => {
        const input = event.target.closest?.('[data-saba-mask]');
        if (!input) return;
        const formatter = SabaMasks[input.dataset.sabaMask];
        if (typeof formatter !== 'function') return;
        const formatted = formatter(input.value);
        if (formatted !== input.value) input.value = formatted;
    }, true);
}
