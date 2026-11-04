import { useShared } from '@/composables/useShared';
import type { AgendaSession } from '@/types/inner';

// Add to calendar opens a prefilled Google Calendar event. Times are Dhaka wall-clock on the summit day (+ day offset),
// so the placeholder date carries a note until config('summit.date_confirmed') is set.
export function useAgendaCalendar() {
    const { summit } = useShared();

    const dhaka = (offset: number, time: string) => {
        const at = new Date(summit.value.startsAt);
        at.setDate(at.getDate() + offset);
        const ymd = at.toLocaleDateString('en-CA', { timeZone: 'Asia/Dhaka' });
        return new Date(`${ymd}T${time.trim()}:00+06:00`).toISOString().replace(/[-:]|\.\d{3}/g, '');
    };
    const gcal = (title: string, offset: number, from: string, to: string, hallName?: string) => {
        const name = `${summit.value.name} ${summit.value.edition}`;
        const note = summit.value.dateConfirmed && summit.value.venue ? '' : ' Date and venue to be confirmed; this entry uses the placeholder date.';
        const q = new URLSearchParams({
            action: 'TEMPLATE',
            text: title ? `${title} · ${name}` : name,
            dates: `${dhaka(offset, from)}/${dhaka(offset, to)}`,
            details: `${title ? 'Session at' : 'Grand Summit day,'} ${name}.${note}`,
            location: [summit.value.venue || 'Venue to be confirmed', hallName, summit.value.city].filter(Boolean).join(', '),
            ctz: 'Asia/Dhaka',
        });
        return `https://calendar.google.com/calendar/render?${q}`;
    };
    // `days` is the agenda's ordered day list; a session on day N sits N-1 days after starts_at.
    const sessionCal = (s: AgendaSession, days: string[]) => {
        const [from, to] = s.time.split('–');
        return gcal(s.title, Math.max(0, days.indexOf(s.day)), from, to ?? from, s.hall);
    };

    return { sessionCal };
}
