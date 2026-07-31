{{-- Selector de Institución Aliada con búsqueda por texto (combobox Alpine) --}}
@php
    $instData = $institutions->map(fn($i) => [
        'id' => (int) $i->id,
        'name' => $i->name,
        'country' => $i->country ?? '',
    ])->values()->toArray();
@endphp

<div
    x-data="institutionCombobox({
        institutions: @js($instData),
        selectedId: @js($selectedInstitutionId ?? ''),
        selectedName: @js($selectedInstitutionName ?? '')
    })"
    class="w-full"
    x-on:institution-created.window="addInstitution($event.detail)"
>
    <input type="hidden" name="institution_id" x-model="selectedId" required>

    <div class="relative">
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-zinc-400">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </span>
            <input
                type="text"
                x-ref="searchInput"
                x-model="query"
                x-on:input="onInput()"
                x-on:focus="open = true"
                x-on:keydown.escape="open = false"
                x-on:keydown.down.prevent="highlighted = Math.min(highlighted + 1, filtered.length - 1)"
                x-on:keydown.up.prevent="highlighted = Math.max(highlighted - 1, 0)"
                x-on:keydown.enter.prevent="selectHighlighted()"
                x-on:click.outside="open = false"
                autocomplete="off"
                placeholder="Escribe para buscar institución por texto..."
                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 py-2.5 pl-10 pr-10 text-sm text-zinc-800 dark:text-zinc-200 shadow-sm placeholder-zinc-400 focus:border-blue-500 focus:ring-blue-500 focus:outline-none transition-colors"
            />
            <button type="button" x-show="query" x-cloak x-on:click="clearSelection()"
                    class="absolute inset-y-0 right-3 flex items-center text-zinc-400 hover:text-zinc-600 transition-colors" title="Limpiar selección">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div x-show="open && filtered.length > 0" x-cloak x-transition
             class="absolute z-50 mt-2 w-full bg-white dark:bg-zinc-800 rounded-xl shadow-2xl border border-zinc-200 dark:border-zinc-700 max-h-72 overflow-y-auto">
            <template x-for="(inst, i) in filtered" :key="inst.id">
                <button type="button"
                        x-on:click="selectOption(inst)"
                        x-on:mouseenter="highlighted = i"
                        class="w-full text-left px-4 py-2.5 flex items-center justify-between gap-3 transition-colors cursor-pointer"
                        :class="i === highlighted ? 'bg-blue-50 dark:bg-blue-900/30' : 'bg-white dark:bg-zinc-800'">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-200 truncate" x-text="inst.name"></div>
                        <div class="text-[10px] uppercase tracking-wide text-zinc-400 mt-0.5" x-text="inst.country || 'País no especificado'"></div>
                    </div>
                    <svg x-show="inst.id === selectedId" class="size-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </button>
            </template>
        </div>

        <div x-show="open && query && filtered.length === 0" x-cloak x-transition
             class="absolute z-50 mt-2 w-full bg-white dark:bg-zinc-800 rounded-xl shadow-2xl border border-zinc-200 dark:border-zinc-700 p-4 text-sm text-zinc-500">
            No se encontró ninguna institución con "<span class="font-semibold" x-text="query"></span>".
        </div>
    </div>
</div>

<script>
    window.institutionCombobox = function (config) {
        return {
            institutions: config.institutions || [],
            query: config.selectedName || '',
            selectedId: config.selectedId || '',
            selectedName: config.selectedName || '',
            open: false,
            highlighted: 0,

            get filtered() {
                const q = this.query.toLowerCase().trim();
                if (!q) return this.institutions.slice(0, 60);
                return this.institutions
                    .filter(i =>
                        i.name.toLowerCase().includes(q) ||
                        (i.country && i.country.toLowerCase().includes(q))
                    )
                    .slice(0, 60);
            },

            onInput() {
                this.open = true;
                this.highlighted = 0;
                if (this.query !== this.selectedName) {
                    this.selectedId = '';
                }
            },

            selectOption(inst) {
                this.selectedId = inst.id;
                this.selectedName = inst.name;
                this.query = inst.name;
                this.open = false;
                this.highlighted = 0;
            },

            selectHighlighted() {
                const item = this.filtered[this.highlighted];
                if (item) this.selectOption(item);
            },

            clearSelection() {
                this.selectedId = '';
                this.selectedName = '';
                this.query = '';
                this.open = false;
                this.$refs.searchInput.focus();
            },

            addInstitution(inst) {
                if (!inst || !inst.id) return;
                this.institutions.push({ id: inst.id, name: inst.name, country: inst.country || '' });
                this.selectOption(inst);
            }
        };
    };
</script>
