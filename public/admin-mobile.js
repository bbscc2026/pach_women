(() => {
    if (window.__pachAdminMobile) return;
    window.__pachAdminMobile = true;
    const phone = () => matchMedia('(max-width: 1023px)').matches;
    let timer, start, held = false;
    const rowFor = target => target.closest('.fi-ta-record');
    const cancel = () => { clearTimeout(timer); timer = null; };
    document.addEventListener('pointerdown', event => {
        if (!phone() || event.button !== 0 || event.target.closest('input,button,select,textarea')) return;
        const row = rowFor(event.target);
        const checkbox = row?.querySelector('.fi-ta-record-checkbox');
        if (!checkbox) return;
        start = [event.clientX, event.clientY];
        held = false;
        timer = setTimeout(() => {
            held = true;
            row.closest('.fi-ta').classList.add('pw-select-mode');
            checkbox.click();
        }, 550);
    });
    document.addEventListener('pointermove', event => {
        if (start && Math.hypot(event.clientX-start[0], event.clientY-start[1]) > 10) cancel();
    });
    document.addEventListener('pointerup', cancel);
    document.addEventListener('pointercancel', () => { cancel(); held = false; start = null; });
    document.addEventListener('click', event => {
        if (!phone() || event.target.closest('input,button,select,textarea,.fi-ta-record-actions')) return;
        const row = rowFor(event.target);
        const checkbox = row?.querySelector('.fi-ta-record-checkbox');
        if (!checkbox || (!held && !row.closest('.pw-select-mode'))) return;
        event.preventDefault(); event.stopImmediatePropagation();
        if (!held) checkbox.click();
        held = false;
    }, true);
    document.addEventListener('contextmenu', event => {
        if (phone() && rowFor(event.target)?.querySelector('.fi-ta-record-checkbox')) event.preventDefault();
    });
    const enhance = () => {
        if (!phone()) return;
        document.querySelectorAll('.fi-ta').forEach(table => {
            if (!table.querySelector('.fi-ta-record-checkbox') || table.querySelector('.pw-select-toggle')) return;
            const button = document.createElement('button');
            button.type = 'button'; button.className = 'pw-select-toggle';
            button.textContent = 'Select items'; button.setAttribute('aria-pressed', 'false');
            button.onclick = () => {
                const enabled = table.classList.toggle('pw-select-mode');
                button.textContent = enabled ? 'Done selecting' : 'Select items';
                button.setAttribute('aria-pressed', String(enabled));
            };
            const toolbar = table.querySelector('.fi-ta-header-toolbar');
            (toolbar || table).prepend(button);
        });
    };
    document.addEventListener('livewire:navigated', enhance);
    new MutationObserver(enhance).observe(document.body, {childList:true, subtree:true});
    enhance();
})();
