<div class="theme-toggle-box inline-flex items-center p-0.5 sm:p-1 rounded-xl bg-slate-900/80 border border-slate-800 text-xs shadow-inner transition-colors duration-200" data-theme-toggle>
    <button type="button" 
        onclick="window.setAppTheme('light')" 
        class="theme-btn-light px-2.5 py-1.5 rounded-lg flex items-center space-x-1.5 transition-all duration-200 text-slate-400 hover:text-amber-500 focus:outline-none"
        title="Switch to Light Theme"
        aria-label="Light Theme">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        <span class="text-[11px] font-semibold hidden md:inline">Light</span>
    </button>

    <button type="button" 
        onclick="window.setAppTheme('dark')" 
        class="theme-btn-dark px-2.5 py-1.5 rounded-lg flex items-center space-x-1.5 transition-all duration-200 text-slate-400 hover:text-amber-400 focus:outline-none"
        title="Switch to Dark Theme"
        aria-label="Dark Theme">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
        </svg>
        <span class="text-[11px] font-semibold hidden md:inline">Dark</span>
    </button>
</div>

<script>
    if (!window.__themeScriptInitialized) {
        window.__themeScriptInitialized = true;

        window.setAppTheme = function(theme) {
            localStorage.setItem('itrove_theme', theme);
            if (theme === 'light') {
                document.documentElement.classList.add('light');
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                document.documentElement.setAttribute('data-theme', 'dark');
            }
            window.syncThemeButtons(theme);
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
        };

        window.syncThemeButtons = function(theme) {
            const isLight = (theme === 'light');
            
            document.querySelectorAll('[data-theme-toggle]').forEach(wrapper => {
                if (isLight) {
                    wrapper.classList.remove('bg-slate-900/80', 'border-slate-800');
                    wrapper.classList.add('bg-slate-100', 'border-slate-300');
                } else {
                    wrapper.classList.remove('bg-slate-100', 'border-slate-300');
                    wrapper.classList.add('bg-slate-900/80', 'border-slate-800');
                }
            });

            document.querySelectorAll('.theme-btn-light').forEach(btn => {
                if (isLight) {
                    btn.className = 'theme-btn-light px-2.5 py-1.5 rounded-lg flex items-center space-x-1.5 transition-all duration-200 bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/30';
                } else {
                    btn.className = 'theme-btn-light px-2.5 py-1.5 rounded-lg flex items-center space-x-1.5 transition-all duration-200 text-slate-400 hover:text-amber-400';
                }
            });

            document.querySelectorAll('.theme-btn-dark').forEach(btn => {
                if (!isLight) {
                    btn.className = 'theme-btn-dark px-2.5 py-1.5 rounded-lg flex items-center space-x-1.5 transition-all duration-200 bg-amber-500/20 text-amber-300 font-bold border border-amber-500/40 shadow-sm';
                } else {
                    btn.className = 'theme-btn-dark px-2.5 py-1.5 rounded-lg flex items-center space-x-1.5 transition-all duration-200 text-slate-500 hover:text-slate-800';
                }
            });
        };

        // Initialize state on load
        document.addEventListener('DOMContentLoaded', function() {
            const currentTheme = document.documentElement.classList.contains('light') ? 'light' : 'dark';
            window.syncThemeButtons(currentTheme);
        });

        // Run immediately if DOM is already ready
        const currentTheme = document.documentElement.classList.contains('light') ? 'light' : 'dark';
        window.syncThemeButtons(currentTheme);
    }
</script>
