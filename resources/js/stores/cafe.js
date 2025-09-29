
import { defineStore } from 'pinia';

const KEY = 'cafe_selection_v1';

export const useCafeStore = defineStore('cafe', {
    state: () => ({
        cafe: null,   // { id, name, slug }
        branch: null, // { id, name, slug }
    }),
    getters: {
        hasSelection: (s) => !!(s.cafe && (s.branch || s.branch === null)),
    },
    actions: {
        setCafe(cafe) {
            this.cafe = cafe;
            this.branch = null;
            this._save();
        },
        setBranch(branch) {
            this.branch = branch;
            this._save();
        },
        clear() {
            this.cafe = null; this.branch = null; this._save();
        },
        hydrate() {
            try {
                const raw = localStorage.getItem(KEY);
                if (raw) Object.assign(this, JSON.parse(raw));
            } catch { }
        },
        _save() {
            localStorage.setItem(KEY, JSON.stringify({ cafe: this.cafe, branch: this.branch }));
        },
    },
});
