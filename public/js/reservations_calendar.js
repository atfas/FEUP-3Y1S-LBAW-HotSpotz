function initHostReservationsCalendar(){
  try {
    const el = document.getElementById('calendar');
    if (!el) return;

    const events = window.hostReservationsEvents || [];

    const FC = window.FullCalendar;
    if (!FC || typeof FC.Calendar !== 'function') {
      return false;
    }

    const Calendar = FC.Calendar;

    const opts = {
      initialView: 'dayGridMonth',
      height: 'auto',
      events: events,
      eventDisplay: 'block',
      eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
      eventContent: function(arg) {
        const ev = arg.event;
        const container = document.createElement('div');
        container.style.display = 'flex';
        container.style.alignItems = 'center';
        container.style.gap = '6px';

        const title = document.createElement('div');
        title.textContent = ev.title || '';
        title.style.flex = '1 1 auto';
        title.style.overflow = 'hidden';
        title.style.textOverflow = 'ellipsis';
        title.style.whiteSpace = 'nowrap';
        // Ensure title text is white for visibility on colored badges/backgrounds
        title.style.color = '#ffffff';
        container.appendChild(title);

        // show status if present
        try {
          const status = ev.extendedProps && ev.extendedProps.status ? String(ev.extendedProps.status) : null;
          if (status) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-secondary ms-1';
            // choose color for common statuses
            const s = status.toLowerCase();
            if (s === 'confirmed') badge.className = 'badge bg-success ms-1';
            else if (s === 'completed') badge.className = 'badge bg-secondary ms-1';
            else if (s === 'rejected' || s === 'cancelled' || s === 'canceled') badge.className = 'badge bg-danger ms-1';
            else if (s === 'pending') badge.className = 'badge bg-warning text-dark ms-1';
            badge.textContent = status;
            // Force badge text white for readability
            badge.style.color = '#ffffff';
            // place badge after title
            container.insertBefore(badge, title.nextSibling);
          }
        } catch (e) {}

        try {
          const start = ev.start;
          if (start && start.getTime) {
            const now = new Date();
            const threshold = now.getTime() + 48 * 60 * 60 * 1000;
            if (start.getTime() >= threshold) {
              const btn = document.createElement('button');
              btn.type = 'button';
              btn.className = 'btn btn-sm text-danger fc-host-reject p-0';
              btn.setAttribute('data-booking-id', String(ev.id));
              btn.setAttribute('title', 'Reject & Refund');
              btn.innerText = '🗑';
              btn.style.marginLeft = '6px';
              btn.style.lineHeight = '1';
              btn.style.fontSize = '0.9rem';
              container.appendChild(btn);
            }
          }
        } catch (e) {
        }

        return { domNodes: [container] };
      }
    };

    if (!FC.globalPlugins || FC.globalPlugins.length === 0) {
      const dayGridPlugin = FC.DayGrid || FC.DayGridPlugin || FC.dayGridPlugin || null;
      const interactionPlugin = FC.Interaction || FC.InteractionPlugin || FC.interactionPlugin || null;
      const pluginList = [dayGridPlugin, interactionPlugin].filter(Boolean);
      if (pluginList.length) opts.plugins = pluginList;
    }

    const calendar = new Calendar(el, opts);
    calendar.render();
    window.hostReservationsCalendar = calendar;

    if (!window._hostReservationsCalendarHandlerAdded) {
      window._hostReservationsCalendarHandlerAdded = true;
      document.addEventListener('click', function(e) {
        const target = e.target instanceof Element ? e.target.closest('.fc-host-reject') : null;
        if (!target) return;
        const bookingId = target.getAttribute('data-booking-id');
        if (!bookingId) return;
        if (!confirm('Reject reservation and issue refund?')) return;

        const token = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content')) || '';

        fetch('/bookings/' + encodeURIComponent(bookingId) + '/host-reject', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
          },
          credentials: 'same-origin'
        }).then(function(resp) {
          if (resp.ok) {
            try {
              const ev = window.hostReservationsCalendar && window.hostReservationsCalendar.getEventById(bookingId);
              if (ev) ev.remove();
            } catch (e) {}
          } else {
            resp.text().then(function(txt){ alert('Failed to reject reservation: ' + txt); });
          }
        }).catch(function(err){
          alert('Network error while rejecting reservation.');
        });
      });
    }
    return true;
  } catch (e) {
    return false;
  }
}

function waitForFCAndInit(retries = 20, delay = 150) {
  let attempts = 0;
  const tryInit = () => {
    attempts++;
    const ok = initHostReservationsCalendar();
    if (ok) return;
    if (attempts >= retries) {
      const container = document.getElementById('calendar');
      if (container) container.innerHTML = '<div class="alert alert-warning">Calendar failed to load. Ensure FullCalendar assets are available.</div>';
      return;
    }
    setTimeout(tryInit, delay);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryInit);
  } else {
    tryInit();
  }
}

waitForFCAndInit();
