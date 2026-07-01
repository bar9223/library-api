const { createApp } = Vue;

createApp({
    data() {
        return {
            books: [],
            total: 0,
            page: 1,
            pages: 1,
            limit: 10,
            search: '',
            searchTimer: null,
            loading: false,
            saving: false,
            form: {
                serialNumber: '',
                title: '',
                author: '',
            },
            cardNumbers: {},
            busy: {},
            confirmState: {
                show: false,
                book: null,
            },
            toast: {
                show: false,
                type: 'success',
                text: '',
            },
            toastTimer: null,
        };
    },
    computed: {
        rangeStart() {
            return this.total === 0 ? 0 : (this.page - 1) * this.limit + 1;
        },
        rangeEnd() {
            return Math.min(this.page * this.limit, this.total);
        },
    },
    watch: {
        search() {
            if (this.searchTimer) {
                clearTimeout(this.searchTimer);
            }
            this.searchTimer = setTimeout(() => {
                this.page = 1;
                this.loadBooks();
            }, 300);
        },
    },
    mounted() {
        this.loadBooks();
    },
    methods: {
        async loadBooks() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.page,
                    limit: this.limit,
                });
                if (this.search.trim() !== '') {
                    params.set('search', this.search.trim());
                }
                const response = await fetch(`/api/books?${params.toString()}`);
                const data = await response.json();
                this.books = data.items;
                this.total = data.total;
                this.pages = data.pages;
                this.page = data.page;
            } catch (error) {
                this.notify('error', 'Nie udało się pobrać listy książek.');
            } finally {
                this.loading = false;
            }
        },
        goToPage(page) {
            if (page < 1 || page > this.pages || page === this.page) {
                return;
            }
            this.page = page;
            this.loadBooks();
        },
        notify(type, text) {
            if (this.toastTimer) {
                clearTimeout(this.toastTimer);
            }
            this.toast = { show: true, type, text };
            this.toastTimer = setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        },
        async readPayload(response) {
            if (response.status === 204) {
                return null;
            }

            const payload = await response.json();

            if (!response.ok) {
                if (Array.isArray(payload.violations) && payload.violations.length) {
                    throw new Error(payload.violations.map((violation) => violation.message).join(' '));
                }
                throw new Error(payload.error || 'Wystąpił błąd.');
            }

            return payload;
        },
        async addBook() {
            this.saving = true;
            try {
                const response = await fetch('/api/books', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.form),
                });
                await this.readPayload(response);
                this.form = { serialNumber: '', title: '', author: '' };
                this.notify('success', 'Dodano książkę.');
                await this.loadBooks();
            } catch (error) {
                this.notify('error', error.message);
            } finally {
                this.saving = false;
            }
        },
        askDelete(book) {
            this.confirmState = { show: true, book };
        },
        cancelDelete() {
            this.confirmState = { show: false, book: null };
        },
        async confirmDelete() {
            const book = this.confirmState.book;
            this.confirmState = { show: false, book: null };
            if (book) {
                await this.deleteBook(book);
            }
        },
        async deleteBook(book) {
            this.busy[book.id] = true;
            try {
                const response = await fetch(`/api/books/${book.id}`, { method: 'DELETE' });
                await this.readPayload(response);
                this.notify('success', 'Usunięto książkę.');
                if (this.books.length === 1 && this.page > 1) {
                    this.page -= 1;
                }
                await this.loadBooks();
            } catch (error) {
                this.notify('error', error.message);
            } finally {
                delete this.busy[book.id];
            }
        },
        async borrowBook(book) {
            this.busy[book.id] = true;
            try {
                const response = await fetch(`/api/books/${book.id}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        borrowed: true,
                        borrowerCardNumber: this.cardNumbers[book.id] || '',
                    }),
                });
                const updated = await this.readPayload(response);
                this.replaceBook(updated);
                this.cardNumbers[book.id] = '';
                this.notify('success', 'Książka wypożyczona.');
            } catch (error) {
                this.notify('error', error.message);
            } finally {
                delete this.busy[book.id];
            }
        },
        async returnBook(book) {
            this.busy[book.id] = true;
            try {
                const response = await fetch(`/api/books/${book.id}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ borrowed: false }),
                });
                const updated = await this.readPayload(response);
                this.replaceBook(updated);
                this.notify('success', 'Książka zwrócona.');
            } catch (error) {
                this.notify('error', error.message);
            } finally {
                delete this.busy[book.id];
            }
        },
        replaceBook(updated) {
            const index = this.books.findIndex((item) => item.id === updated.id);
            if (index !== -1) {
                this.books.splice(index, 1, updated);
            }
        },
        clearSearch() {
            this.search = '';
        },
        formatDate(value) {
            if (!value) {
                return '';
            }
            return new Date(value).toLocaleString('pl-PL', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
    },
    template: `
        <div class="layout">
            <header class="header">
                <div class="header-mark">KIS</div>
                <div>
                    <h1>System biblioteczny</h1>
                    <p>Ewidencja księgozbioru biblioteki</p>
                </div>
            </header>

            <section class="panel">
                <h2>Dodaj książkę</h2>
                <form class="add-form" @submit.prevent="addBook">
                    <div class="field">
                        <label>Numer seryjny</label>
                        <input v-model="form.serialNumber" maxlength="6" inputmode="numeric" placeholder="np. 123456" required>
                    </div>
                    <div class="field field-grow">
                        <label>Tytuł</label>
                        <input v-model="form.title" placeholder="Tytuł książki" required>
                    </div>
                    <div class="field field-grow">
                        <label>Autor</label>
                        <input v-model="form.author" placeholder="Autor" required>
                    </div>
                    <button class="btn btn-primary" type="submit" :disabled="saving">Dodaj</button>
                </form>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h2>Książki</h2>
                    <span class="counter">{{ total }}</span>
                    <div class="search">
                        <input v-model="search" placeholder="Szukaj po tytule, autorze lub numerze...">
                        <button v-if="search" class="search-clear" @click="clearSearch" aria-label="Wyczyść">×</button>
                    </div>
                </div>

                <p v-if="loading" class="muted">Wczytywanie...</p>

                <div v-else class="table-wrap">
                    <table class="books">
                        <colgroup>
                            <col style="width: 100px">
                            <col>
                            <col>
                            <col style="width: 130px">
                            <col style="width: 170px">
                            <col style="width: 280px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Nr seryjny</th>
                                <th>Tytuł</th>
                                <th>Autor</th>
                                <th>Status</th>
                                <th>Wypożyczenie</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="book in books" :key="book.id">
                                <td class="mono">{{ book.serialNumber }}</td>
                                <td>{{ book.title }}</td>
                                <td>{{ book.author }}</td>
                                <td>
                                    <span :class="book.borrowed ? 'badge badge-out' : 'badge badge-in'">
                                        {{ book.borrowed ? 'Wypożyczona' : 'Dostępna' }}
                                    </span>
                                </td>
                                <td>
                                    <template v-if="book.borrowed">
                                        <div class="loan">
                                            <span class="mono">Karta {{ book.borrowerCardNumber }}</span>
                                            <span class="muted small">{{ formatDate(book.borrowedAt) }}</span>
                                        </div>
                                    </template>
                                    <span v-else class="muted">-</span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <div class="card-slot">
                                            <input v-if="!book.borrowed" class="card-input mono" v-model="cardNumbers[book.id]" maxlength="6" inputmode="numeric" placeholder="Nr karty">
                                        </div>
                                        <button v-if="book.borrowed" class="btn btn-toggle btn-return" :disabled="busy[book.id]" @click="returnBook(book)">Zwróć</button>
                                        <button v-else class="btn btn-toggle btn-primary" :disabled="busy[book.id]" @click="borrowBook(book)">Wypożycz</button>
                                        <button class="btn btn-icon btn-icon-danger" :disabled="busy[book.id]" @click="askDelete(book)" title="Usuń" aria-label="Usuń">
                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <path d="M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14"></path>
                                                <path d="M10 11v6"></path>
                                                <path d="M14 11v6"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!books.length">
                                <td colspan="6" class="empty">
                                    {{ search ? 'Brak książek pasujących do wyszukiwania.' : 'Brak książek w bazie.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!loading && total > 0" class="pagination">
                    <span class="muted small">Pozycje {{ rangeStart }}-{{ rangeEnd }} z {{ total }}</span>
                    <div class="pager">
                        <button class="btn btn-ghost" :disabled="page <= 1" @click="goToPage(page - 1)">Poprzednia</button>
                        <span class="page-indicator">Strona {{ page }} z {{ pages }}</span>
                        <button class="btn btn-ghost" :disabled="page >= pages" @click="goToPage(page + 1)">Następna</button>
                    </div>
                </div>
            </section>
        </div>

        <transition name="toast-fade">
            <div v-if="toast.show" class="toast" :class="'toast-' + toast.type">{{ toast.text }}</div>
        </transition>

        <transition name="modal-fade">
            <div v-if="confirmState.show" class="modal-overlay" @click.self="cancelDelete">
                <div class="modal" role="dialog" aria-modal="true">
                    <div class="modal-icon">!</div>
                    <h3 class="modal-title">Usunąć książkę?</h3>
                    <p class="modal-text">
                        Czy na pewno chcesz usunąć pozycję
                        <strong>„{{ confirmState.book && confirmState.book.title }}"</strong>?
                        Tej operacji nie można cofnąć.
                    </p>
                    <div class="modal-actions">
                        <button class="btn btn-ghost" @click="cancelDelete">Anuluj</button>
                        <button class="btn btn-danger" @click="confirmDelete">Usuń</button>
                    </div>
                </div>
            </div>
        </transition>
    `,
}).mount('#app');
