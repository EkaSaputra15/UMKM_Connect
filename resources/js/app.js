import './bootstrap';
import { createIcons, icons } from 'lucide';
import Chart from 'chart.js/auto';

window.Chart = Chart;

const renderIcons = () => {
    createIcons({ icons });
};

window.renderLucideIcons = renderIcons;

document.addEventListener('DOMContentLoaded', renderIcons);
document.addEventListener('livewire:navigated', renderIcons);
document.addEventListener('livewire:initialized', () => {
    renderIcons();
    if (window.Livewire && typeof window.Livewire.hook === 'function') {
        window.Livewire.hook('morph.updated', () => {
            requestAnimationFrame(renderIcons);
        });
    }
});
