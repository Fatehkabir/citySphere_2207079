document.addEventListener('DOMContentLoaded', () => {
 document.querySelectorAll('form[data-confirm]').forEach(f => {
    f.addEventListener('submit', e => {
      if (!confirm(f.dataset.confirm)) e.preventDefault();
    });
  });
  
  const anon = document.getElementById('is_anonymous');
  if (anon) {
    const hint = document.getElementById('anon-hint');
    const sync = () => { if (hint) hint.hidden = !anon.checked; };
    anon.addEventListener('change', sync); sync();
  }
});
