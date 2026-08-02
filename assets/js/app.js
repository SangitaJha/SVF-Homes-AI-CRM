document.addEventListener('DOMContentLoaded', () => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  document.querySelectorAll('.sidebar-toggle').forEach((button) => {
    button.addEventListener('click', () => {
      document.body.classList.toggle('sidebar-collapsed');
      document.body.classList.toggle('sidebar-open');
    });
  });

  document.querySelectorAll('.logout-link').forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      const href = link.getAttribute('href') || '/login';
      window.location.href = href;
    });
  });

  document.querySelectorAll('canvas[data-chart-labels]').forEach((canvas) => {
    const labels = JSON.parse(canvas.dataset.chartLabels || '[]');
    const values = JSON.parse(canvas.dataset.chartValues || '[]');
    const type = canvas.id.includes('Revenue') || canvas.id.includes('Attendance') || canvas.id.includes('Progress') ? 'line' : (canvas.id.includes('Status') || canvas.id.includes('Collection') ? 'doughnut' : 'bar');
    const isDoughnut = type === 'doughnut';

    new Chart(canvas, {
      type,
      data: {
        labels,
        datasets: [{
          label: canvas.id,
          data: values,
          borderColor: isDoughnut ? ['#2563eb', '#60a5fa', '#93c5fd'] : '#2563eb',
          backgroundColor: isDoughnut ? ['rgba(37, 99, 235, 0.88)', 'rgba(96, 165, 250, 0.72)', 'rgba(147, 197, 253, 0.56)'] : ['rgba(37, 99, 235, 0.82)', 'rgba(59, 130, 246, 0.72)', 'rgba(96, 165, 250, 0.58)'],
          borderWidth: 1,
          tension: 0.3,
          fill: !isDoughnut,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: !isDoughnut ? false : true, position: 'bottom' } },
        scales: isDoughnut ? {} : { x: { grid: { display: false } }, y: { grid: { color: 'rgba(15, 23, 42, 0.06)' }, beginAtZero: true } },
      },
    });
  });

  document.querySelectorAll('.password-toggle').forEach((button) => {
    button.addEventListener('click', () => {
      const targetId = button.getAttribute('data-target') || '';
      const input = document.getElementById(targetId);
      if (!input) {
        return;
      }
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      const icon = button.querySelector('i');
      if (icon) {
        icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
      }
      button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });
  });

  document.querySelectorAll('.quick-pill').forEach((button) => {
    button.addEventListener('click', () => {
      const emailInput = document.getElementById('authEmail');
      const passwordInput = document.getElementById('authPassword');
      if (emailInput) {
        emailInput.value = button.dataset.email || '';
      }
      if (passwordInput) {
        passwordInput.value = button.dataset.password || '';
      }
    });
  });

  document.querySelectorAll('.auth-form').forEach((form) => {
    form.addEventListener('submit', () => {
      const button = form.querySelector('.auth-submit-button');
      if (!button) {
        return;
      }
      button.classList.add('is-loading');
      const text = button.querySelector('.button-text');
      const spinner = button.querySelector('.spinner-border');
      if (text) {
        text.textContent = 'Signing in...';
      }
      if (spinner) {
        spinner.classList.remove('d-none');
      }
      button.disabled = true;
    });
  });

  document.querySelectorAll('.ai-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const resultBox = form.parentElement.querySelector('.ai-result');
      const payload = new FormData(form);
      payload.set('_token', token);
      try {
        const response = await fetch(form.dataset.endpoint, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: payload,
        });
        const data = await response.json();
        resultBox.innerHTML = '<pre class="mb-0 text-muted">' + JSON.stringify(data, null, 2) + '</pre>';
      } catch (error) {
        resultBox.innerHTML = '<div class="alert alert-danger mb-0">Request failed.</div>';
      }
    });
  });
});