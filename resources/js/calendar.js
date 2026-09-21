import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import idLocale from '@fullcalendar/core/locales/id';

export function initializeMeetingCalendar() {
    const element = document.getElementById('meetingCalendar');
    if (!element) return;

    const filter = document.getElementById('calendarRoomFilter');
    const dialog = document.getElementById('calendarEventDialog');
    const eventSource = () => {
        const url = new URL(element.dataset.eventsUrl, window.location.origin);
        const roomId = filter?.value || element.dataset.roomId;
        if (roomId && roomId !== '0') url.searchParams.set('room_id', roomId);
        return url.toString();
    };

    const calendar = new Calendar(element, {
        plugins: [dayGridPlugin, listPlugin, interactionPlugin],
        locale: idLocale,
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth',
        },
        buttonText: { today: 'Hari ini', month: 'Bulan', list: 'Daftar' },
        dayMaxEvents: 3,
        events: eventSource(),
        eventClick(info) {
            if (!dialog) return;
            dialog.querySelector('[data-event-title]').textContent = info.event.title;
            dialog.querySelector('[data-event-time]').textContent = `${info.event.start.toLocaleString('id-ID')} – ${info.event.end?.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) || ''}`;
            dialog.querySelector('[data-event-room]').textContent = info.event.extendedProps.room || '-';
            dialog.querySelector('[data-event-organizer]').textContent = info.event.extendedProps.organizer || '-';
            dialog.querySelector('[data-event-purpose]').textContent = info.event.extendedProps.purpose || 'Tidak ada catatan tambahan.';
            dialog.showModal();
        },
    });
    calendar.render();

    filter?.addEventListener('change', () => {
        calendar.removeAllEventSources();
        calendar.addEventSource(eventSource());
    });

    dialog?.querySelectorAll('[data-dialog-close]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });
}
