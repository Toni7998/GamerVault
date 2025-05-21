class ForumChat {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        this.refreshInterval = 5000; // refrescar cada 5 segundos
        this.init();
    }

    init() {
        this.bindForms();
        this.startAutoRefresh();
    }

    bindForms() {
        const forms = document.querySelectorAll('.post-form');
        forms.forEach(form => {
            form.addEventListener('submit', e => {
                e.preventDefault();
                this.handleSubmit(form);
            });
            const threadId = form.dataset.threadId;
            this.loadPosts(threadId);
        });
    }

    async handleSubmit(form) {
        const threadId = form.dataset.threadId;
        const textarea = form.querySelector('textarea[name="content"]');
        const content = textarea.value.trim();

        if (!content) return;

        try {
            const response = await fetch(`/forum/${threadId}/posts`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ content }),
            });

            if (!response.ok) throw new Error('Error enviant el missatge');

            textarea.value = '';
            await this.loadPosts(threadId);
        } catch (error) {
            alert(error.message);
        }
    }

    async loadPosts(threadId) {
        try {
            const response = await fetch(`/forum/${threadId}/posts`);
            if (!response.ok) throw new Error('Error carregant els missatges');

            let posts = await response.json();

            // Ordenar del más antiguo al más reciente
            posts.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

            const container = document.getElementById(`posts-thread-${threadId}`);
            const wasAtBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 10;

            container.innerHTML = '';

            posts.forEach(post => {
                const div = document.createElement('div');
                div.className = 'bg-gray-800 p-3 rounded mb-2';
                div.innerHTML = `
                    <p class="text-sm text-gray-400">${post.user.name} - ${post.created_at_human}</p>
                    <p>${post.content}</p>
                `;
                container.appendChild(div);
            });

            // Si estaba al fondo, vuelve a hacer scroll al fondo
            if (wasAtBottom) {
                container.scrollTop = container.scrollHeight;
            }

        } catch (error) {
            console.error(error);
        }
    }

    startAutoRefresh() {
        this.refreshAllPosts();
        setInterval(() => this.refreshAllPosts(), this.refreshInterval);
    }

    refreshAllPosts() {
        const forms = document.querySelectorAll('.post-form');
        forms.forEach(form => {
            const threadId = form.dataset.threadId;
            this.loadPosts(threadId);
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ForumChat();
});
