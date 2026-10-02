import ApexCharts from 'apexcharts';

const css = (name, fallback) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;

function renderChart(selector, options) {
    const element = document.querySelector(selector);
    if (!element || element.dataset.chartReady === 'true') return;

    element.dataset.chartReady = 'true';
    const chart = new ApexCharts(element, options);
    chart.render();
}

function renderDashboardCharts() {
    const isDark = document.documentElement.classList.contains('dark');
    const text = isDark ? '#a1a1aa' : '#71717a';
    const grid = isDark ? '#27272a' : '#e4e4e7';
    const primary = isDark ? '#e4e4e7' : '#27272a';

    renderChart('#agreement-status-chart', {
        chart: { type: 'donut', height: 285, toolbar: { show: false }, background: 'transparent' },
        series: [18, 6, 4, 2],
        labels: ['Active', 'Under Review', 'Pending Approval', 'Renewal Due'],
        colors: [primary, '#71717a', '#a1a1aa', '#d4d4d8'],
        stroke: { width: 3, colors: [isDark ? '#18181b' : '#ffffff'] },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', labels: { colors: text }, fontSize: '12px' },
        plotOptions: { pie: { donut: { size: '72%', labels: { show: true, total: { show: true, label: 'Agreements', color: text, formatter: () => '30' }, value: { color: primary, fontSize: '24px', fontWeight: 600 } } } } },
        tooltip: { theme: isDark ? 'dark' : 'light' },
    });

    renderChart('#partnership-activity-chart', {
        chart: { type: 'area', height: 285, toolbar: { show: false }, zoom: { enabled: false }, background: 'transparent' },
        series: [
            { name: 'New MoUs', data: [2, 4, 3, 6, 5, 8, 7, 9, 6] },
            { name: 'Completed Reviews', data: [1, 2, 4, 3, 5, 4, 6, 5, 8] },
        ],
        colors: [primary, '#a1a1aa'],
        stroke: { curve: 'smooth', width: 2.5 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.22, opacityTo: 0.02, stops: [0, 90, 100] } },
        dataLabels: { enabled: false },
        grid: { borderColor: grid, strokeDashArray: 4 },
        xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'], labels: { style: { colors: text } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: text } } },
        legend: { position: 'top', horizontalAlign: 'right', labels: { colors: text } },
        tooltip: { theme: isDark ? 'dark' : 'light' },
    });

    renderChart('#department-chart', {
        chart: { type: 'bar', height: 270, toolbar: { show: false }, background: 'transparent' },
        series: [{ name: 'Agreements', data: [8, 6, 5, 4, 3] }],
        colors: [primary],
        plotOptions: { bar: { borderRadius: 5, columnWidth: '48%' } },
        dataLabels: { enabled: false },
        grid: { borderColor: grid, strokeDashArray: 4 },
        xaxis: { categories: ['Research', 'Academic', 'ICT', 'Student Affairs', 'Legal'], labels: { rotate: -25, style: { colors: text, fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: text } } },
        tooltip: { theme: isDark ? 'dark' : 'light' },
    });
}

document.addEventListener('DOMContentLoaded', renderDashboardCharts);
document.addEventListener('livewire:navigated', renderDashboardCharts);
