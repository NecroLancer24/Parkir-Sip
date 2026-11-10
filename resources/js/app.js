document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);

        if (! input) {
            return;
        }

        const isRevealed = input.type === 'password';

        input.type = isRevealed ? 'text' : 'password';

        button.querySelector('[data-eye-open]')?.classList.toggle('hidden', isRevealed);
        button.querySelector('[data-eye-closed]')?.classList.toggle('hidden', ! isRevealed);
        button.setAttribute('aria-label', isRevealed ? 'Hide password' : 'Show password');
    });
});

const liveClock = document.querySelector('[data-live-clock]');
const clockTime = liveClock?.querySelector('[data-clock-time]');
const clockOffset = liveClock?.querySelector('[data-clock-offset]');

if (liveClock && clockTime && clockOffset) {
    const timeFormatter = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });

    const formatUtcOffset = () => {
        const offsetMinutes = -new Date().getTimezoneOffset();

        if (offsetMinutes === 0) {
            return 'UTC';
        }

        const sign = offsetMinutes < 0 ? '-' : '+';
        const absoluteMinutes = Math.abs(offsetMinutes);
        const hours = Math.floor(absoluteMinutes / 60);
        const minutes = absoluteMinutes % 60;

        return `UTC${sign}${hours}${minutes > 0 ? `:${String(minutes).padStart(2, '0')}` : ''}`;
    };

    const updateClock = () => {
        const now = new Date();

        liveClock.dateTime = now.toISOString();
        clockTime.textContent = timeFormatter.format(now);
        clockOffset.textContent = formatUtcOffset();
    };

    updateClock();
    window.setInterval(updateClock, 1000);
}
