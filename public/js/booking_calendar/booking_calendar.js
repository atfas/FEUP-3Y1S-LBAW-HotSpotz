(function() {
  function createBookingCalendar(options) {
    const {
      containerId,
      checkinInputId,
      checkoutInputId,
      errorContainerId,
      availabilities,
      bookings,
      onSelect,
    } = options;

    const container = document.getElementById(containerId);
    if (!container) return null;

    const FC = window.FullCalendar;
    if (!FC || typeof FC.Calendar !== 'function') {
      console.error('FullCalendar not loaded.');
      return null;
    }

    const Calendar = FC.Calendar;
    
    const plugins = FC.globalPlugins;
    if (!plugins || plugins.length < 2) {
        console.error('FullCalendar required plugins not found in globalPlugins.');
        const errorEl = document.getElementById(errorContainerId);
        if (errorEl) errorEl.textContent = 'Calendar assets could not be loaded.';
        return null;
    }
    
    function localISODate(d) { return new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().split('T')[0]; }
    function parseLocalDate(s) {
    if (!s) return null;
    const parts = s.split('-');
    if (parts.length !== 3) return null;

    const year = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const day = parseInt(parts[2], 10);

    if (isNaN(year) || isNaN(month) || isNaN(day) || month < 1 || month > 12 || day < 1 || day > 31) {
        return null;
    }

    const d = new Date(year, month - 1, day);
    if (isNaN(d.getTime())) {
        return null;
    }
    return d;
}
    function addDaysToLocal(dateStr, days) { const d=parseLocalDate(dateStr); if(!d)return null; d.setDate(d.getDate()+days); return localISODate(d); }


    const avRanges = (availabilities || []).map(r => ({ ...r, end: r.end_date }));

    function parseDateAsUTC(s) {
      if (!s) return null;
      const parts = s.split('-');
      if (parts.length !== 3) return null;
      const year = parseInt(parts[0], 10);
      const month = parseInt(parts[1], 10);
      const day = parseInt(parts[2], 10);
      if (isNaN(year) || isNaN(month) || isNaN(day)) {
        return null;
      }
      const d = new Date(Date.UTC(year, month - 1, day));
      return isNaN(d.getTime()) ? null : d;
    }

    const bookingEvents = (bookings || [])
      .filter(b => b.status && b.status.toLowerCase() !== 'cancelled' && b.status.toLowerCase() !== 'rejected')
      .map((b, idx) => {
        const endDate = addDaysToLocal(b.end_date, 1);
        if (!b.start_date || !endDate) return null; 
        return { id: `book_${idx}`, start: b.start_date, end: endDate, display: 'background', backgroundColor: '#ff9f89' };
      })
      .filter(Boolean);


    function isDateBooked(dateStr) {
      const currentDay = parseDateAsUTC(dateStr);
      if (!currentDay) return false;
      const currentTime = currentDay.getTime();

      for (const book of bookingEvents) {
        const start = parseDateAsUTC(book.start);
        const end = parseDateAsUTC(book.end);
        if (start && end && currentTime >= start.getTime() && currentTime < end.getTime()) {
          return true;
        }
      }
      return false;
    }

    function isDateAvailable(dateStr) {
      const currentDay = parseDateAsUTC(dateStr);
      if (!currentDay) return false;
      const currentTime = currentDay.getTime();

      for (const av of avRanges) {
        const start = parseDateAsUTC(av.start_date);
        const end = parseDateAsUTC(av.end);
        if (start && end && currentTime >= start.getTime() && currentTime <= end.getTime()) {
          if (isDateBooked(dateStr)) return false;
          return true;
        }
      }
      return false;
    }

    function findAvailabilityForRange(startStr, endStr) {
      const endExclusive = addDaysToLocal(endStr, 1);
      for (const av of avRanges) { if (av.start_date <= startStr && av.end >= endExclusive) return av; }
      return null;
    }

    function isRangeValid(startStr, endStr) {
      const availability = findAvailabilityForRange(startStr, endStr);
      if (!availability) return false;
      const endExclusive = addDaysToLocal(endStr, 1);
      for (const book of bookingEvents) { if (startStr < book.end && endExclusive > book.start) return false; }
      return true;
    }

    const allEvents = [...bookingEvents];
    let selectionState = { mode: 'none', checkin: null };

    function showError(message) {
      const errorEl = document.getElementById(errorContainerId);
      if (errorEl) errorEl.textContent = message;
    }

    function clearHighlights() {
      calendar.getEvents().forEach(event => { if (event.id === 'selection_checkin' || event.id === 'selection_range') event.remove(); });
    }

    const calendar = new Calendar(container, {
      plugins: plugins,
      initialView: 'dayGridMonth',
      headerToolbar: { left: 'prev,next', center: 'title', right: '' },
      height: 'auto',
      validRange: { start: new Date() },
      events: allEvents,
      dayCellClassNames: function(arg) {
        if (isDateAvailable(localISODate(arg.date))) {
          return ['available-day'];
        } else if (isDateBooked(localISODate(arg.date))) {
          return ['booked-day'];
        }
        return [];
      },
      dateClick: function(info) {
        const clickedDate = info.dateStr;
        const errorEl = document.getElementById(errorContainerId);
        if (errorEl) errorEl.textContent = '';
        
        if (selectionState.mode === 'none') {
          clearHighlights();
          if (!isDateAvailable(clickedDate)) { showError('Please select an available check-in date.'); return; }
          selectionState.checkin = clickedDate;
          selectionState.mode = 'checkin-selected';
          calendar.addEvent({ id: 'selection_checkin', start: clickedDate, allDay: true, display: 'background', backgroundColor: '#a2d2ff' });
          const ci = document.getElementById(checkinInputId); const co = document.getElementById(checkoutInputId);
          if (ci) ci.value = ''; if (co) co.value = '';
          if (typeof onSelect === 'function') onSelect({ checkin: '', checkout: '', availability: null });
        } else if (selectionState.mode === 'checkin-selected') {
          const checkinDate = selectionState.checkin;
          const checkoutDate = clickedDate;
          if (checkoutDate < checkinDate) {
            showError('Check-out date must be after check-in date. Selection has been reset.');
            selectionState.mode = 'none'; selectionState.checkin = null; clearHighlights(); return;
          }
          const availability = findAvailabilityForRange(checkinDate, checkoutDate);
          if (!availability || !isRangeValid(checkinDate, checkoutDate)) {
            showError('The selected range is not valid (must be within a single availability and not overlap bookings). Selection has been reset.');
            selectionState.mode = 'none'; selectionState.checkin = null; clearHighlights(); return;
          }
          calendar.addEvent({ id: 'selection_range', start: checkinDate, end: addDaysToLocal(checkoutDate, 1), allDay: true, display: 'background', backgroundColor: '#a2d2ff' });
          const ci = document.getElementById(checkinInputId); const co = document.getElementById(checkoutInputId);
          if (ci) ci.value = checkinDate; if (co) co.value = checkoutDate;
          if (typeof onSelect === 'function') onSelect({ checkin: checkinDate, checkout: checkoutDate, availability: availability });
          selectionState.mode = 'none'; selectionState.checkin = null;
        }
      }
    });
    calendar.render();
    
    return {
      calendarInstance: calendar,
      clearSelection: () => {
        clearHighlights();
        const ci = document.getElementById(checkinInputId); const co = document.getElementById(checkoutInputId);
        if (ci) ci.value = ''; if (co) co.value = '';
        if (typeof onSelect === 'function') onSelect({ checkin: '', checkout: '', availability: null });
      }
    };
  }

  
  let calendarInstance = null;

  function initBookingCalendar() {
    const FC = window.FullCalendar;
    if (!FC || typeof FC.Calendar !== 'function' || !FC.globalPlugins || FC.globalPlugins.length < 2) {
      return false; 
    }

    if (window.availabilities === undefined || window.bookings === undefined) {
      return false;
    }

    let options = null;

    if (document.getElementById('sim_calendar_container')) {
      if (window.updateSimulator === undefined) {
        return false;
      }
      options = {
        containerId: 'sim_calendar_container',
        checkinInputId: 'sim_checkin',
        checkoutInputId: 'sim_checkout',
        errorContainerId: 'calendar_error_message',
        availabilities: window.availabilities,
        bookings: window.bookings,
        onSelect: window.updateSimulator,
      };
      window.clearCalendarSelection = () => { if(calendarInstance) calendarInstance.clearSelection(); };
    } else if (document.getElementById('booking_calendar_container')) {
      if (window.updateBookingPrice === undefined) {
          return false;
      }
      options = {
        containerId: 'booking_calendar_container',
        checkinInputId: 'check_in_date',
        checkoutInputId: 'check_out_date',
        errorContainerId: 'booking_calendar_error_message',
        availabilities: window.availabilities,
        bookings: window.bookings,
        onSelect: window.updateBookingPrice,
      };
    }
    
    if (options) {
      try {
        calendarInstance = createBookingCalendar(options);
        if (!calendarInstance) {
          const errorEl = document.getElementById(options.errorContainerId);
          if (errorEl) errorEl.textContent = 'Calendar failed to initialize.';
          return false;
        }
      } catch (e) {
        console.error("Error creating booking calendar:", e);
        return false;
      }
    }
    return true; 
  }

  function waitForFCAndInit(retries = 20, delay = 150) {
    let attempts = 0;
    const tryInit = () => {
      attempts++;
      if (initBookingCalendar()) return; 

      if (attempts >= retries) {
        const simContainer = document.getElementById('sim_calendar_container');
        const bookContainer = document.getElementById('booking_calendar_container');
        const container = simContainer || bookContainer;
        if (container) container.innerHTML = '<div class="alert alert-warning small">Calendar failed to load. Please refresh the page.</div>';
        return;
      }
      setTimeout(tryInit, delay);
    };
    tryInit();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', waitForFCAndInit);
  } else {
    waitForFCAndInit();
  }

})();
