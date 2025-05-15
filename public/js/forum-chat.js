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

            const posts = await response.json();
            const container = document.getElementById(`posts-thread-${threadId}`);

            container.innerHTML = ''; // netejar missatges

            posts.forEach(post => {
                const div = document.createElement('div');
                div.className = 'bg-gray-800 p-3 rounded';
                div.innerHTML = `
                    <p class="text-sm text-gray-400">${post.user.name} - ${post.created_at_human}</p>
                    <p>${post.content}</p>
                `;
                container.appendChild(div);
            });
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
