@props([
    'name',
    'label' => '',
    'options' => [],
    'wireModel' => null,
])

<div x-data="{
    options: @js($options),
    selected: @entangle($wireModel),
    search: '',
    open: false,

    /* કોઈપण વિકલ્પ હાઈલાઈટ ન હોવો જોઈએ, એટલે  -1 થી શરૂ કરીએ */
    highlightedIndex: -1,

    get filteredOptions() {
        if (this.search === '') return Object.keys(this.options).filter(id => !this.selected.includes(id));
        return Object.keys(this.options)
            .filter(id => !this.selected.includes(id))
            .filter(id => this.options[id].toLowerCase().includes(this.search.toLowerCase()));
    },

    toggleDropdown() {
        this.open = !this.open;
        if (this.open) {
            this.$nextTick(() => this.$refs.search.focus());
        }
    },

    close() {
        this.open = false;
        this.highlightedIndex = -1;   /* બંધ થયાં પછી કોઈ હાઈલાઈટ નહી */
        this.search = '';
    },

    select(id) {
        if (!this.selected.includes(id)) {
            this.selected.push(id);
        }
        this.search = '';
        this.close();
    },

    remove(id) {
        this.selected = this.selected.filter(i => i !== id);
    },

    highlightNext() {
        if (this.highlightedIndex < this.filteredOptions.length - 1) {
            this.highlightedIndex++;
        }
    },

    highlightPrevious() {
        if (this.highlightedIndex > 0) {
            this.highlightedIndex--;
        }
    },

    selectHighlighted() {
        /* ડ્રોપડાઉન ખુલ્લું ન હોય અથવા કોઈ વિકલ્પ હાઈન લાઈટ ન હોય તો કઈ કરીએ નહી */
        if (!this.open || this.highlightedIndex === -1) return;

        const id = this.filteredOptions[this.highlightedIndex];
        if (id) this.select(id);
    }
}" class="w-full">
    @if ($label)
        <label class="block text-sm font-medium mb-1">{{ $label }}</label>
    @endif

    <div @click="toggleDropdown" class="flex flex-wrap border rounded px-2 py-2 bg-white min-h-[40px] cursor-pointer gap-2">
        <template x-for="id in selected" :key="id">
            <span class="bg-blue-100 text-blue-700 px-2 py-1 text-sm rounded flex items-center">
                <span x-text="options[id]"></span>
                <button type="button" @click.stop="remove(id)" class="ml-1 text-red-500 hover:text-red-700">×</button>
            </span>
        </template>
       <input
            x-ref="search"
            x-model="search"
             @input="open = true"  
            @keydown.arrow-down.prevent="highlightNext"
            @keydown.arrow-up.prevent="highlightPrevious"
            @keydown.enter.prevent="selectHighlighted"  
            @keydown.tab.prevent="selectHighlighted"
            @click.stop
            @focus="open = true"
            type="text"
            class="flex-grow border-none outline-none p-1 text-sm"
            placeholder="Search items..."
        />
    </div>

    <ul x-show="open && filteredOptions.length > 0"
        class="absolute z-50 bg-white border mt-1 w-full max-h-60 overflow-y-auto rounded shadow-lg"
        @click.outside="close"
    >
        <template x-for="(id, index) in filteredOptions" :key="id">
            <li :class="{
                    'bg-blue-100': highlightedIndex === index,
                    'px-3 py-2 cursor-pointer hover:bg-blue-50': true
                }"
                @click="select(id)"
                @mouseenter="highlightedIndex = index"
                x-text="options[id]"></li>
        </template>
    </ul>
</div>
